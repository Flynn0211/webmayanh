<?php
/**
 * Lớp OrderController quản lý toàn bộ các quy trình liên quan đến Đơn hàng (Checkout, Lịch sử đơn hàng, Cập nhật trạng thái)
 * Sử dụng cơ chế Transaction (giao dịch CSDL) an toàn tuyệt đối khi thanh toán, tự động trừ kho đa kho thông minh, tính điểm thành viên, áp dụng voucher và gửi thư thông báo tự động.
 */

// Nạp kết nối cơ sở dữ liệu
require_once __DIR__ . '/../model/database.php';

class OrderController {
    /**
     * Xử lý quy trình Checkout (Thanh toán & Tạo đơn hàng mới).
     */
    public static function handleCheckout() {
        global $conn;
        if ($conn === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
            return;
        }

        // Đọc dữ liệu giỏ hàng và khách hàng được gửi dạng JSON từ Frontend
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
        
        // 1. Tìm thông tin khách hàng từ username để lấy ma_tk và hạng thành viên hiện tại
        $ma_khach_hang = null;
        $hang_thanh_vien = 'None';
        $email_khach_hang = null;
        if (!empty($customerUsername)) {
            $stmt = $conn->prepare("SELECT ma_tk, hang_thanh_vien, diem_tich_luy, email FROM tai_khoan WHERE username = ?");
            if ($stmt) {
                $stmt->execute([$customerUsername]);
                if ($row = $stmt->fetch()) {
                    $ma_khach_hang = $row['ma_tk'];
                    $email_khach_hang = $row['email'];
                    
                    // Phân loại lại hạng thành viên thực tế dựa trên điểm phòng ngừa dữ liệu cũ lệch lạc
                    $pts = (int)$row['diem_tich_luy'];
                    $hang_thanh_vien = 'Thường';
                    if ($pts >= 10000) $hang_thanh_vien = 'Diamond';
                    elseif ($pts >= 5000) $hang_thanh_vien = 'Gold';
                    elseif ($pts >= 1000) $hang_thanh_vien = 'Silver';
                }
            }
        }

        // 2. Xác thực và tính toán giá trị khấu trừ của Mã giảm giá (Voucher) nếu có áp dụng
        $giam_gia_tong = 0;
        $ma_voucher = null;
        $voucherType = '';
        $voucherValue = 0;
        if (!empty($voucherCode)) {
            require_once __DIR__ . '/../model/VoucherModel.php';
            $voucherData = VoucherModel::validateVoucher($conn, $voucherCode, $totalRaw);
            if ($voucherData['valid']) {
                $giam_gia_tong = $voucherData['discount'];
                $ma_voucher = $voucherData['id'];
                $voucherType = $voucherData['type'];
                $voucherValue = (float)$voucherData['value'];
            }
        }

        // Tiền xử lý phân bổ giảm giá tổng vào từng mặt hàng theo thứ tự (Giao diện yêu cầu áp vào 1 sản phẩm)
        $discountRemaining = $giam_gia_tong;
        $newTotalRaw = 0;
        $calculatedItems = [];
        foreach ($items as $item) {
            $priceStr = strval($item['price']);
            $priceRaw = (float)preg_replace('/[^0-9.]/', '', $priceStr);
            $qty = (int)$item['quantity'];
            
            $itemTotal = $priceRaw * $qty;
            if ($discountRemaining > 0) {
                $deduct = min($itemTotal, $discountRemaining);
                $itemTotal -= $deduct;
                $discountRemaining -= $deduct;
            }
            
            $discountedPrice = $itemTotal / $qty;
            $newTotalRaw += $itemTotal;
            
            $calculatedItems[] = [
                'id' => (int)$item['id'],
                'quantity' => $qty,
                'priceRaw' => $discountedPrice
            ];
        }

        // 3. Tính chiết khấu ưu đãi của Hạng thành viên (Silver 2%, Gold 5%, Diamond 10% tính trên tổng tiền sau voucher)
        $membership_discount_percent = 0;
        if ($hang_thanh_vien === 'Silver') $membership_discount_percent = 2;
        if ($hang_thanh_vien === 'Gold') $membership_discount_percent = 5;
        if ($hang_thanh_vien === 'Diamond') $membership_discount_percent = 10;
        
        $membership_discount_amount = $newTotalRaw * ($membership_discount_percent / 100);
        
        // Tính tổng tiền thanh toán cuối cùng của đơn hàng (Không được nhỏ hơn 0)
        $totalThanhToan = max(0, $newTotalRaw - $membership_discount_amount);

        // --- KHỞI CHẠY GIAO DỊCH DATABASE TRANSACTION AN TOÀN ---
        $conn->beginTransaction();
        try {
            // A. Ghi thông tin đơn hàng chung vào bảng `don_hang`
            $phi_vc = 0;
            $trang_thai = 'ChoXacNhan';
            
            $stmt_order = $conn->prepare("INSERT INTO don_hang (ma_khach_hang, ma_voucher, ten_nguoi_nhan, sdt_nguoi_nhan, tong_tien_hang, phi_van_chuyen, giam_gia_voucher, tong_thanh_toan, phuong_thuc_thanh_toan, trang_thai_don) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'COD', ?)");
            $stmt_order->execute([$ma_khach_hang, $ma_voucher, $customerName, $customerPhone, $totalRaw, $phi_vc, $giam_gia_tong, $totalThanhToan, $trang_thai]);
            
            $ma_dh = $conn->lastInsertId();

            // B. Ghi lịch sử hành trình giao hàng đầu tiên
            $stmt_history = $conn->prepare("INSERT INTO lich_su_giao_hang (ma_dh, trang_thai, mo_ta) VALUES (?, ?, 'Đơn hàng mới được tạo thành công')");
            $stmt_history->execute([$ma_dh, $trang_thai]);

            // C. Thêm chi tiết từng mặt hàng và thực hiện trừ kho thông minh đa kho
            $stmt_item = $conn->prepare("INSERT INTO chi_tiet_don_hang (ma_dh, ma_hh, so_luong, gia_luc_mua) VALUES (?, ?, ?, ?)");
            
            // Lấy danh sách các kho có chứa sản phẩm hiện tại, sắp xếp ưu tiên kho nhiều hàng nhất để trừ trước
            $stmt_get_stock = $conn->prepare("SELECT ma_kho, so_luong_ton FROM ton_kho_chi_tiet WHERE ma_hh = ? AND so_luong_ton > 0 ORDER BY so_luong_ton DESC");
            $stmt_update_stock = $conn->prepare("UPDATE ton_kho_chi_tiet SET so_luong_ton = ? WHERE ma_hh = ? AND ma_kho = ?");

            foreach ($calculatedItems as $item) {
                $ma_hh = $item['id'];
                $qty = $item['quantity'];
                $priceRaw = $item['priceRaw'];

                // Lưu chi tiết đơn hàng
                $stmt_item->execute([$ma_dh, $ma_hh, $qty, $priceRaw]);

                // Thuật toán trừ kho đa kho động (Smart Warehouse Deduction)
                $stmt_get_stock->execute([$ma_hh]);
                $qty_to_deduct = $qty;
                
                while ($qty_to_deduct > 0 && $row = $stmt_get_stock->fetch()) {
                    $kho_id = $row['ma_kho'];
                    $stock_avail = $row['so_luong_ton'];
                    
                    if ($stock_avail >= $qty_to_deduct) {
                        $new_stock = $stock_avail - $qty_to_deduct;
                        $stmt_update_stock->execute([$new_stock, $ma_hh, $kho_id]);
                        $qty_to_deduct = 0;
                    } else {
                        $new_stock = 0;
                        $stmt_update_stock->execute([$new_stock, $ma_hh, $kho_id]);
                        $qty_to_deduct -= $stock_avail;
                    }
                }
            }

            // Thực hiện COMMIT ghi nhận toàn bộ thay đổi thành công xuống ổ đĩa cứng CSDL
            $conn->commit();

            // 4. Các nghiệp vụ sau khi Transaction thành công: Cộng điểm thành viên, lưu thông báo & gửi email
            if ($ma_khach_hang) {
                // Tỷ lệ quy đổi điểm tích lũy: Mỗi 10,000 VND thanh toán thực tế = cộng 1 điểm tích lũy
                $points = floor($totalThanhToan / 10000);
                
                // Lấy điểm hiện có và tính tổng
                $stmt_pt = $conn->prepare("SELECT diem_tich_luy FROM tai_khoan WHERE ma_tk = ?");
                $stmt_pt->execute([$ma_khach_hang]);
                $res_pt = $stmt_pt;
                $curr_pt = $res_pt->fetch()['diem_tich_luy'] + $points;
                
                // Phân cấp hạng thành viên mới
                $new_tier = 'None';
                if ($curr_pt >= 10000) $new_tier = 'Diamond';
                elseif ($curr_pt >= 5000) $new_tier = 'Gold';
                elseif ($curr_pt >= 1000) $new_tier = 'Silver';
                
                // Cập nhật lại tài khoản
                $stmt_upd = $conn->prepare("UPDATE tai_khoan SET diem_tich_luy = ?, hang_thanh_vien = ? WHERE ma_tk = ?");
                $stmt_upd->execute([$curr_pt, $new_tier, $ma_khach_hang]);
                
                // Lưu thông báo dạng email nội bộ để hiển thị trên web
                $msg = "Đơn hàng #$ma_dh của bạn đã được đặt thành công. Tổng thanh toán: " . number_format($totalThanhToan) . " VND.";
                $stmt_email = $conn->prepare("INSERT INTO thong_bao_email (ma_tk_nhan, tieu_de, noi_dung) VALUES (?, 'Đặt hàng thành công', ?)");
                if ($stmt_email) {
                    $stmt_email->execute([$ma_khach_hang, $msg]);
                }

                // Thực hiện gửi Email thực tế thông qua SMTP Socket nếu khách hàng có email
                if ($email_khach_hang && file_exists(__DIR__ . '/../model/SmtpMailer.php')) {
                    require_once __DIR__ . '/../model/SmtpMailer.php';
                    SmtpMailer::sendMail($email_khach_hang, "Đặt hàng thành công #$ma_dh", $msg);
                }
            }

            // 5. Khấu trừ số lượng lượt sử dụng còn lại của Voucher mã giảm giá
            if ($ma_voucher) {
                $stmt_v = $conn->prepare("UPDATE voucher SET so_luong = GREATEST(0, so_luong - 1) WHERE ma_voucher = ?");
                $stmt_v->execute([$ma_voucher]);
            }

            echo json_encode(['success' => true, 'order_id' => $ma_dh]);
        } catch (Exception $e) {
            // Nếu có bất kỳ lỗi nào xảy ra trong Transaction, tiến hành ROLLBACK khôi phục lại trạng thái ban đầu của CSDL
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi tạo đơn hàng: ' . $e->getMessage()]);
        }
    }

    /**
     * API kiểm tra mã voucher qua AJAX
     */
    public static function checkVoucher() {
        global $conn;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        $code = isset($data['code']) ? trim($data['code']) : '';
        $totalRaw = isset($data['totalRaw']) ? (float)$data['totalRaw'] : 0;
        
        require_once __DIR__ . '/../model/VoucherModel.php';
        $res = VoucherModel::validateVoucher($conn, $code, $totalRaw);
        echo json_encode($res);
    }

    /**
     * Lấy toàn bộ lịch sử đơn hàng của tài khoản đang đăng nhập (AJAX GET).
     */
    public static function getClientOrders() {
        global $conn;
        if ($conn === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $username = isset($_SESSION['client_username']) ? $_SESSION['client_username'] : '';
        if (!$username) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập.']);
            return;
        }

        // Lấy mã tài khoản
        $ma_kh = null;
        $stmt = $conn->prepare("SELECT ma_tk FROM tai_khoan WHERE username = ?");
        $stmt->execute([$username]);
        if ($row = $stmt->fetch()) {
            $ma_kh = $row['ma_tk'];
        }

        if (!$ma_kh) {
            echo json_encode(['success' => false, 'message' => 'Khách hàng không tồn tại.']);
            return;
        }

        // Truy vấn lấy các đơn hàng của khách hàng sắp xếp mới nhất lên đầu
        $orders = [];
        $stmt_order = $conn->prepare("SELECT ma_dh as id, ngay_dat as date_val, tong_thanh_toan as total_val, trang_thai_don as status FROM don_hang WHERE ma_khach_hang = ? ORDER BY ma_dh DESC");
        $stmt_order->execute([$ma_kh]);
        
        while ($o = $stmt_order->fetch()) {
            $order = [
                'id' => $o['id'],
                'date' => date('d/m/Y', strtotime($o['date_val'])),
                'total' => number_format($o['total_val']) . ' ₫',
                'status' => $o['status'],
                'items' => []
            ];

            // Truy vấn lấy danh sách chi tiết các sản phẩm trong đơn hàng tương ứng
            $stmt_item = $conn->prepare("SELECT c.ma_hh as id, h.ten_hang_hoa as name, h.anh as image, c.so_luong as quantity, c.gia_luc_mua as price_val, n.ten_ncc as brand FROM chi_tiet_don_hang c JOIN hang_hoa h ON c.ma_hh = h.ma_hh LEFT JOIN nha_cung_cap n ON h.ma_ncc = n.ma_ncc WHERE c.ma_dh = ?");
            $stmt_item->execute([$o['id']]);
            while ($i = $stmt_item->fetch()) {
                $i['price'] = number_format($i['price_val']) . ' ₫';
                $order['items'][] = $i;
            }
            $orders[] = $order;
        }

        echo json_encode(['success' => true, 'orders' => $orders]);
    }

    /**
     * Cập nhật trạng thái đơn hàng (Sử dụng bởi Ban Quản Trị trong Admin Panel).
     */
    public static function handleUpdateStatus() {
        global $conn;
        if ($conn === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_logged_in'])) {
            echo json_encode(['success' => false, 'message' => 'Không đủ quyền truy cập.']);
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

        // Cập nhật trạng thái đơn hàng
        $stmt = $conn->prepare("UPDATE don_hang SET trang_thai_don = ? WHERE ma_dh = ?");
        
        if ($stmt->execute([$status, $orderId])) {
            // Đồng thời ghi nhận hành trình cập nhật vào lịch sử giao dịch đơn hàng
            $stmt_history = $conn->prepare("INSERT INTO lich_su_giao_hang (ma_dh, trang_thai, mo_ta) VALUES (?, ?, 'Trạng thái đơn hàng được cập nhật bởi Ban quản lý')");
            if ($stmt_history) {
                $stmt_history->execute([$orderId, $status]);
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cập nhật trạng thái đơn hàng thất bại.']);
        }
    }
}
