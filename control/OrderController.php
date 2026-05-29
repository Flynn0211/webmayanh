<?php
// Load database
require_once __DIR__ . '/../model/database.php';

class OrderController {
    public static function handleCheckout() {
        global $conn;
        if ($conn === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            return;
        }

        // Must be POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
            return;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || !isset($data['items']) || empty($data['items'])) {
            echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống hoặc dữ liệu không hợp lệ.']);
            return;
        }

        $items = $data['items'];
        $customerName = isset($data['customerName']) ? trim($data['customerName']) : 'Khách hàng';
        $customerUsername = isset($data['customerUsername']) ? trim($data['customerUsername']) : '';
        $totalRaw = isset($data['totalRaw']) ? (float)$data['totalRaw'] : 0;
        
        // Lookup customer ID from tai_khoan based on username
        $ma_khach_hang = null;
        if (!empty($customerUsername)) {
            $stmt = $conn->prepare("SELECT ma_tk FROM tai_khoan WHERE username = ?");
            if ($stmt) {
                $stmt->bind_param("s", $customerUsername);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $ma_khach_hang = $row['ma_tk'];
                }
            }
        }

        // Begin Transaction
        $conn->begin_transaction();
        try {
            // 1. Insert into don_hang
            // ma_dh, ma_khach_hang, ten_nguoi_nhan, sdt_nguoi_nhan, tong_tien_hang, phi_van_chuyen, giam_gia_voucher, tong_thanh_toan, phuong_thuc_thanh_toan, trang_thai_don, dia_chi_giao
            $sdt = '0900000000'; // Default or fetch from user
            $phi_vc = 0;
            $giam_gia = 0;
            $trang_thai = 'Chờ Xác Nhận';
            
            $stmt_order = $conn->prepare("INSERT INTO don_hang (ma_khach_hang, ten_nguoi_nhan, sdt_nguoi_nhan, tong_tien_hang, phi_van_chuyen, giam_gia_voucher, tong_thanh_toan, phuong_thuc_thanh_toan, trang_thai_don) VALUES (?, ?, ?, ?, ?, ?, ?, 'COD', ?)");
            $stmt_order->bind_param("issdddds", $ma_khach_hang, $customerName, $sdt, $totalRaw, $phi_vc, $giam_gia, $totalRaw, $trang_thai);
            $stmt_order->execute();
            
            $ma_dh = $conn->insert_id;

            // 2. Insert items into chi_tiet_don_hang and deduct stock
            $stmt_item = $conn->prepare("INSERT INTO chi_tiet_don_hang (ma_dh, ma_hh, so_luong, gia_luc_mua) VALUES (?, ?, ?, ?)");
            $stmt_stock = $conn->prepare("UPDATE ton_kho_chi_tiet SET so_luong_ton = GREATEST(0, so_luong_ton - ?) WHERE ma_hh = ?");

            foreach ($items as $item) {
                $ma_hh = (int)$item['id'];
                $qty = (int)$item['quantity'];
                
                // Parse price (remove non-digits if passed as string)
                $priceStr = strval($item['price']);
                $priceRaw = (float)preg_replace('/[^0-9.]/', '', $priceStr);

                // Insert detail
                $stmt_item->bind_param("iiid", $ma_dh, $ma_hh, $qty, $priceRaw);
                $stmt_item->execute();

                // Deduct stock
                $stmt_stock->bind_param("ii", $qty, $ma_hh);
                $stmt_stock->execute();
            }

            $conn->commit();
            echo json_encode(['success' => true, 'order_id' => $ma_dh]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi tạo đơn hàng: ' . $e->getMessage()]);
        }
    }

    public static function getClientOrders() {
        global $conn;
        if ($conn === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            return;
        }

        // Must be logged in
        if (session_status() === PHP_SESSION_NONE) session_start();
        $username = isset($_SESSION['client_username']) ? $_SESSION['client_username'] : '';
        if (!$username) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập.']);
            return;
        }

        // Fetch ma_tk
        $ma_kh = null;
        $stmt = $conn->prepare("SELECT ma_tk FROM tai_khoan WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $ma_kh = $row['ma_tk'];
        }

        if (!$ma_kh) {
            echo json_encode(['success' => false, 'message' => 'Khách hàng không tồn tại.']);
            return;
        }

        // Fetch orders
        $orders = [];
        $stmt_order = $conn->prepare("SELECT ma_dh as id, ngay_dat as date_val, tong_thanh_toan as total_val, trang_thai_don as status FROM don_hang WHERE ma_khach_hang = ? ORDER BY ma_dh DESC");
        $stmt_order->bind_param("i", $ma_kh);
        $stmt_order->execute();
        $res_order = $stmt_order->get_result();

        while ($o = $res_order->fetch_assoc()) {
            $order = [
                'id' => $o['id'],
                'date' => date('d/m/Y', strtotime($o['date_val'])),
                'total' => number_format($o['total_val']) . ' ₫',
                'status' => $o['status'],
                'items' => []
            ];

            // Fetch items
            $stmt_item = $conn->prepare("SELECT c.ma_hh as id, h.ten_hang_hoa as name, h.anh as image, c.so_luong as quantity, c.gia_luc_mua as price_val, n.ten_ncc as brand FROM chi_tiet_don_hang c JOIN hang_hoa h ON c.ma_hh = h.ma_hh LEFT JOIN nha_cung_cap n ON h.ma_ncc = n.ma_ncc WHERE c.ma_dh = ?");
            $stmt_item->bind_param("i", $o['id']);
            $stmt_item->execute();
            $res_item = $stmt_item->get_result();
            while ($i = $res_item->fetch_assoc()) {
                $i['price'] = number_format($i['price_val']) . ' ₫';
                $order['items'][] = $i;
            }
            $orders[] = $order;
        }

        echo json_encode(['success' => true, 'orders' => $orders]);
    }

    public static function handleUpdateStatus() {
        global $conn;
        if ($conn === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_logged_in'])) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập quyền quản trị.']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || !isset($data['order_id']) || !isset($data['status'])) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }

        $orderId = (int)$data['order_id'];
        $status = trim($data['status']);

        $stmt = $conn->prepare("UPDATE don_hang SET trang_thai_don = ? WHERE ma_dh = ?");
        $stmt->bind_param("si", $status, $orderId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái.']);
        }
    }
}
?>
