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
        
        $customerPhone = isset($data['customerPhone']) && !empty(trim($data['customerPhone'])) ? trim($data['customerPhone']) : '0900000000';
        $voucherCode = isset($data['voucherCode']) ? trim($data['voucherCode']) : '';
        
        // Lookup customer ID from tai_khoan based on username
        $ma_khach_hang = null;
        $hang_thanh_vien = 'None';
        $email_khach_hang = null;
        if (!empty($customerUsername)) {
            $stmt = $conn->prepare("SELECT ma_tk, hang_thanh_vien, email FROM tai_khoan WHERE username = ?");
            if ($stmt) {
                $stmt->bind_param("s", $customerUsername);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $ma_khach_hang = $row['ma_tk'];
                    $hang_thanh_vien = $row['hang_thanh_vien'];
                    $email_khach_hang = $row['email'];
                }
            }
        }

        // Validate Voucher
        $giam_gia = 0;
        $ma_voucher = null;
        if (!empty($voucherCode)) {
            require_once __DIR__ . '/../model/VoucherModel.php';
            $voucherData = VoucherModel::validateVoucher($conn, $voucherCode, $totalRaw);
            if ($voucherData['valid']) {
                $giam_gia = $voucherData['discount'];
                $ma_voucher = $voucherData['id'];
            }
        }

        // Membership Discount
        $membership_discount_percent = 0;
        if ($hang_thanh_vien === 'Silver') $membership_discount_percent = 2;
        if ($hang_thanh_vien === 'Gold') $membership_discount_percent = 5;
        if ($hang_thanh_vien === 'Diamond') $membership_discount_percent = 10;
        
        $membership_discount_amount = ($totalRaw - $giam_gia) * ($membership_discount_percent / 100);
        $totalThanhToan = max(0, $totalRaw - $giam_gia - $membership_discount_amount);

        // Begin Transaction
        $conn->begin_transaction();
        try {
            // 1. Insert into don_hang
            $phi_vc = 0;
            $trang_thai = 'ChoXacNhan';
            
            $stmt_order = $conn->prepare("INSERT INTO don_hang (ma_khach_hang, ma_voucher, ten_nguoi_nhan, sdt_nguoi_nhan, tong_tien_hang, phi_van_chuyen, giam_gia_voucher, tong_thanh_toan, phuong_thuc_thanh_toan, trang_thai_don) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'COD', ?)");
            $stmt_order->bind_param("iissdddds", $ma_khach_hang, $ma_voucher, $customerName, $customerPhone, $totalRaw, $phi_vc, $giam_gia, $totalThanhToan, $trang_thai);
            $stmt_order->execute();
            
            $ma_dh = $conn->insert_id;

            // 1b. Insert initial history
            $stmt_history = $conn->prepare("INSERT INTO lich_su_giao_hang (ma_dh, trang_thai, mo_ta) VALUES (?, ?, 'Đơn hàng mới được tạo')");
            $stmt_history->bind_param("is", $ma_dh, $trang_thai);
            $stmt_history->execute();

            // 2. Insert items into chi_tiet_don_hang and deduct stock smartly
            $stmt_item = $conn->prepare("INSERT INTO chi_tiet_don_hang (ma_dh, ma_hh, so_luong, gia_luc_mua) VALUES (?, ?, ?, ?)");
            $stmt_get_stock = $conn->prepare("SELECT ma_kho, so_luong_ton FROM ton_kho_chi_tiet WHERE ma_hh = ? AND so_luong_ton > 0 ORDER BY so_luong_ton DESC");
            $stmt_update_stock = $conn->prepare("UPDATE ton_kho_chi_tiet SET so_luong_ton = ? WHERE ma_hh = ? AND ma_kho = ?");

            foreach ($items as $item) {
                $ma_hh = (int)$item['id'];
                $qty = (int)$item['quantity'];
                
                // Parse price (remove non-digits if passed as string)
                $priceStr = strval($item['price']);
                $priceRaw = (float)preg_replace('/[^0-9.]/', '', $priceStr);

                // Insert detail
                $stmt_item->bind_param("iiid", $ma_dh, $ma_hh, $qty, $priceRaw);
                $stmt_item->execute();

                // Smart Multi-warehouse Deduct stock
                $stmt_get_stock->bind_param("i", $ma_hh);
                $stmt_get_stock->execute();
                $res_stock = $stmt_get_stock->get_result();
                $qty_to_deduct = $qty;
                
                while ($qty_to_deduct > 0 && $row = $res_stock->fetch_assoc()) {
                    $kho_id = $row['ma_kho'];
                    $stock_avail = $row['so_luong_ton'];
                    
                    if ($stock_avail >= $qty_to_deduct) {
                        $new_stock = $stock_avail - $qty_to_deduct;
                        $stmt_update_stock->bind_param("iii", $new_stock, $ma_hh, $kho_id);
                        $stmt_update_stock->execute();
                        $qty_to_deduct = 0;
                    } else {
                        $new_stock = 0;
                        $stmt_update_stock->bind_param("iii", $new_stock, $ma_hh, $kho_id);
                        $stmt_update_stock->execute();
                        $qty_to_deduct -= $stock_avail;
                    }
                }
            }

            $conn->commit();

            // After commit: Add user points and insert email notification
            if ($ma_khach_hang) {
                $points = floor($totalThanhToan / 10000); // 1 point for every 10,000 VND
                
                // Get current points
                $res_pt = $conn->query("SELECT diem_tich_luy FROM tai_khoan WHERE ma_tk = $ma_khach_hang");
                $curr_pt = $res_pt->fetch_assoc()['diem_tich_luy'] + $points;
                
                // Determine tier
                $new_tier = 'None';
                if ($curr_pt >= 10000) $new_tier = 'Diamond';
                elseif ($curr_pt >= 5000) $new_tier = 'Gold';
                elseif ($curr_pt >= 1000) $new_tier = 'Silver';
                
                $conn->query("UPDATE tai_khoan SET diem_tich_luy = $curr_pt, hang_thanh_vien = '$new_tier' WHERE ma_tk = $ma_khach_hang");
                
                $msg = "Đơn hàng #$ma_dh của bạn đã được đặt thành công. Tổng thanh toán: " . number_format($totalThanhToan) . " VND.";
                $stmt_email = $conn->prepare("INSERT INTO thong_bao_email (ma_tk_nhan, tieu_de, noi_dung) VALUES (?, 'Đặt hàng thành công', ?)");
                if ($stmt_email) {
                    $stmt_email->bind_param("is", $ma_khach_hang, $msg);
                    $stmt_email->execute();
                }

                // Call SmtpMailer if available
                if ($email_khach_hang && file_exists(__DIR__ . '/../model/SmtpMailer.php')) {
                    require_once __DIR__ . '/../model/SmtpMailer.php';
                    SmtpMailer::sendMail($email_khach_hang, "Đặt hàng thành công #$ma_dh", $msg);
                }
            }

            // Deduct voucher usage if applied
            if ($ma_voucher) {
                $conn->query("UPDATE voucher SET so_luong = GREATEST(0, so_luong - 1) WHERE ma_voucher = $ma_voucher");
            }

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
            // Log to lich_su_giao_hang
            $stmt_history = $conn->prepare("INSERT INTO lich_su_giao_hang (ma_dh, trang_thai, mo_ta) VALUES (?, ?, 'Cập nhật bởi Admin')");
            if ($stmt_history) {
                $stmt_history->bind_param("is", $orderId, $status);
                $stmt_history->execute();
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái.']);
        }
    }
}
?>
