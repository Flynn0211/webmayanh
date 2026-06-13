<?php
/**
 * Lớp OrderController quản lý toàn bộ các quy trình liên quan đến Đơn hàng (Checkout, Lịch sử đơn hàng, Cập nhật trạng thái)
 * Sử dụng cơ chế Transaction (giao dịch CSDL) an toàn tuyệt đối khi thanh toán, tự động trừ kho đa kho thông minh, tính điểm thành viên, áp dụng voucher và gửi thư thông báo tự động.
 */

// Nạp kết nối cơ sở dữ liệu
require_once __DIR__ . '/../model/database.php';

class OrderController {
    private $conn;
    private $voucherModel;

    public function __construct($conn) {
        $this->conn = $conn;
        
        $this->voucherModel = new VoucherModel($conn);
    }

    /**
     * Xử lý quy trình Checkout (Thanh toán & Tạo đơn hàng mới).
     */
    public function handleCheckout() {
        
        if ($this->conn === false) {
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
        $customerAddress = isset($data['customerAddress']) ? trim($data['customerAddress']) : 'Chưa cung cấp';
        $paymentMethod = isset($data['paymentMethod']) ? trim($data['paymentMethod']) : 'COD';
        
        // 1. Tìm thông tin khách hàng từ username để lấy ma_tk và hạng thành viên hiện tại
        $ma_khach_hang = null;
        $hang_thanh_vien = 'None';
        $email_khach_hang = null;
        if (!empty($customerUsername)) {
            $stmt = $this->conn->prepare("SELECT ma_tk, hang_thanh_vien, diem_tich_luy, email FROM tai_khoan WHERE username = ?");
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
            
            $voucherData = $this->voucherModel->validateVoucher($voucherCode, $totalRaw);
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
            $ma_hh = (int)$item['id'];
            $priceStr = strval($item['price']);
            $priceRaw = (float)preg_replace('/[^0-9.]/', '', $priceStr);
            $qty = (int)$item['quantity'];
            
            // --- BỔ SUNG: KIỂM TRA TỒN KHO TRƯỚC KHI THANH TOÁN ---
            $stmt_stock_check = $this->conn->prepare("SELECT SUM(so_luong_ton) as total_stock FROM ton_kho_chi_tiet WHERE ma_hh = ?");
            $stmt_stock_check->execute([$ma_hh]);
            $stockData = $stmt_stock_check->fetch();
            $availableStock = $stockData ? (int)$stockData['total_stock'] : 0;
            
            if ($availableStock < $qty) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Sản phẩm "' . (isset($item['name']) ? $item['name'] : 'Mã ' . $ma_hh) . '" chỉ còn ' . $availableStock . ' sản phẩm trong kho. Vui lòng giảm số lượng!'
                ]);
                exit;
            }
            
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
                'name' => isset($item['name']) ? $item['name'] : 'Sản phẩm',
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
        
        // Phí vận chuyển xác thực từ server
        $phi_vc = 40000;
        if (stripos($customerAddress, 'Hà Nội') !== false || stripos($customerAddress, 'Hồ Chí Minh') !== false) {
            $phi_vc = 20000;
        }

        // Tính tổng tiền thanh toán cuối cùng của đơn hàng (Không được nhỏ hơn 0)
        $totalThanhToan = max(0, $newTotalRaw - $membership_discount_amount) + $phi_vc;

        // --- KHỞI CHẠY GIAO DỊCH DATABASE TRANSACTION AN TOÀN ---
        $this->conn->beginTransaction();
        try {
            // A. Ghi thông tin đơn hàng chung vào bảng `don_hang`
            $trang_thai = 'ChoXacNhan';
            
            $stmt_order = $this->conn->prepare("INSERT INTO don_hang (ma_khach_hang, ma_voucher, ten_nguoi_nhan, sdt_nguoi_nhan, dia_chi_giao_hang, tong_tien_hang, phi_van_chuyen, giam_gia_voucher, tong_thanh_toan, phuong_thuc_thanh_toan, trang_thai_don) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_order->execute([$ma_khach_hang, $ma_voucher, $customerName, $customerPhone, $customerAddress, $totalRaw, $phi_vc, $giam_gia_tong, $totalThanhToan, $paymentMethod, $trang_thai]);
            
            $ma_dh = $this->conn->lastInsertId();

            // B. Ghi lịch sử hành trình giao hàng đầu tiên
            $stmt_history = $this->conn->prepare("INSERT INTO lich_su_giao_hang (ma_dh, trang_thai, mo_ta) VALUES (?, ?, 'Đơn hàng mới được tạo thành công')");
            $stmt_history->execute([$ma_dh, $trang_thai]);

            // C. Thêm chi tiết từng mặt hàng và thực hiện trừ kho thông minh đa kho
            $stmt_item = $this->conn->prepare("INSERT INTO chi_tiet_don_hang (ma_dh, ma_hh, so_luong, gia_luc_mua) VALUES (?, ?, ?, ?)");
            
            // Lấy danh sách các kho có chứa sản phẩm hiện tại, sắp xếp ưu tiên kho nhiều hàng nhất để trừ trước
            $stmt_get_stock = $this->conn->prepare("SELECT ma_kho, so_luong_ton FROM ton_kho_chi_tiet WHERE ma_hh = ? AND so_luong_ton > 0 ORDER BY so_luong_ton DESC");
            $stmt_update_stock = $this->conn->prepare("UPDATE ton_kho_chi_tiet SET so_luong_ton = ? WHERE ma_hh = ? AND ma_kho = ?");

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
            $this->conn->commit();

            // 4. Các nghiệp vụ sau khi Transaction thành công: Cộng điểm thành viên, lưu thông báo & gửi email
            if ($ma_khach_hang) {
                // Tỷ lệ quy đổi điểm tích lũy: Mỗi 10,000 VND thanh toán thực tế = cộng 1 điểm tích lũy
                $points = floor($totalThanhToan / 10000);
                
                // Lấy điểm hiện có và tính tổng
                $stmt_pt = $this->conn->prepare("SELECT diem_tich_luy FROM tai_khoan WHERE ma_tk = ?");
                $stmt_pt->execute([$ma_khach_hang]);
                $res_pt = $stmt_pt;
                $curr_pt = $res_pt->fetch()['diem_tich_luy'] + $points;
                
                // Phân cấp hạng thành viên mới
                $new_tier = 'None';
                if ($curr_pt >= 10000) $new_tier = 'Diamond';
                elseif ($curr_pt >= 5000) $new_tier = 'Gold';
                elseif ($curr_pt >= 1000) $new_tier = 'Silver';
                
                // Cập nhật lại tài khoản
                $stmt_upd = $this->conn->prepare("UPDATE tai_khoan SET diem_tich_luy = ?, hang_thanh_vien = ? WHERE ma_tk = ?");
                $stmt_upd->execute([$curr_pt, $new_tier, $ma_khach_hang]);
                
                // --- TẠO MẪU EMAIL HTML CHUẨN SHOPEE ---
                $htmlEmail = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
                    <div style='background: #ea580c; color: white; padding: 20px; text-align: center;'>
                        <h2 style='margin: 0;'>XÁC NHẬN ĐƠN HÀNG LENS & LIGHT</h2>
                    </div>
                    <div style='padding: 20px;'>
                        <p>Xin chào <strong>$customerName</strong>,</p>
                        <p>Cảm ơn bạn đã mua sắm tại LENS & LIGHT! Đơn hàng <strong>#$ma_dh</strong> của bạn đã được tiếp nhận và đang trong quá trình xử lý.</p>
                        
                        <h3 style='border-bottom: 2px solid #ea580c; padding-bottom: 5px; color: #ea580c;'>THÔNG TIN GIAO HÀNG</h3>
                        <p><strong>Người nhận:</strong> $customerName ($customerPhone)<br/>
                        <strong>Địa chỉ:</strong> $customerAddress<br/>
                        <strong>Phương thức thanh toán:</strong> " . ($paymentMethod === 'BankTransfer' ? 'Chuyển khoản Ngân hàng' : 'Thanh toán khi nhận hàng (COD)') . "</p>
                        
                        <h3 style='border-bottom: 2px solid #ea580c; padding-bottom: 5px; color: #ea580c;'>CHI TIẾT ĐƠN HÀNG</h3>
                        <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                            <tr style='background: #f9f9f9;'>
                                <th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Sản phẩm</th>
                                <th style='padding: 10px; border: 1px solid #ddd; text-align: center;'>SL</th>
                                <th style='padding: 10px; border: 1px solid #ddd; text-align: right;'>Thành tiền</th>
                            </tr>";
                foreach ($calculatedItems as $it) {
                    $htmlEmail .= "<tr>
                                <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($it['name']) . "</td>
                                <td style='padding: 10px; border: 1px solid #ddd; text-align: center;'>" . $it['quantity'] . "</td>
                                <td style='padding: 10px; border: 1px solid #ddd; text-align: right;'>" . number_format($it['priceRaw'] * $it['quantity']) . "đ</td>
                            </tr>";
                }
                $htmlEmail .= "
                        </table>
                        
                        <table style='width: 100%; border-collapse: collapse; font-weight: bold;'>
                            <tr><td style='padding: 5px; text-align: right;'>Tổng tiền hàng:</td><td style='padding: 5px; text-align: right; width: 120px;'>" . number_format($totalRaw) . "đ</td></tr>
                            <tr><td style='padding: 5px; text-align: right;'>Phí vận chuyển:</td><td style='padding: 5px; text-align: right;'>" . number_format($phi_vc) . "đ</td></tr>";
                if ($giam_gia_tong > 0) {
                    $htmlEmail .= "<tr><td style='padding: 5px; text-align: right; color: green;'>Giảm giá Voucher:</td><td style='padding: 5px; text-align: right; color: green;'>-" . number_format($giam_gia_tong) . "đ</td></tr>";
                }
                if ($membership_discount_amount > 0) {
                    $htmlEmail .= "<tr><td style='padding: 5px; text-align: right; color: green;'>Giảm Thành Viên:</td><td style='padding: 5px; text-align: right; color: green;'>-" . number_format($membership_discount_amount) . "đ</td></tr>";
                }
                $htmlEmail .= "
                            <tr style='font-size: 18px; color: #ea580c;'><td style='padding: 15px 5px; text-align: right;'>TỔNG THANH TOÁN:</td><td style='padding: 15px 5px; text-align: right;'>" . number_format($totalThanhToan) . "đ</td></tr>
                        </table>";
                        
                if ($paymentMethod === 'BankTransfer') {
                    $htmlEmail .= "
                        <div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-top: 20px; border: 1px solid #ffeeba;'>
                            <h4 style='margin-top: 0;'>HƯỚNG DẪN CHUYỂN KHOẢN</h4>
                            <p style='margin-bottom: 0;'>Vui lòng chuyển khoản số tiền <strong>" . number_format($totalThanhToan) . "đ</strong> theo thông tin sau hoặc quét mã QR Code:</p>
                            
                            <div style='display: flex; flex-direction: column; align-items: center; margin-top: 15px;'>
                                <img src='https://img.vietqr.io/image/970422-0523134391-compact2.png?amount=" . $totalThanhToan . "&addInfo=THANH%20TOAN%20DH%20" . $ma_dh . "&accountName=LE%20DUONG%20TUAN%20ANH' alt='QR Code Thanh Toán' style='max-width: 250px; border-radius: 10px; border: 1px solid #ccc; box-shadow: 0 4px 8px rgba(0,0,0,0.1);' />
                            </div>

                            <ul style='margin-top: 15px; background: rgba(255,255,255,0.5); padding: 10px 10px 10px 30px; border-radius: 5px;'>
                                <li>Ngân hàng: <strong>MB Bank</strong></li>
                                <li>Chủ tài khoản: <strong>LE DUONG TUAN ANH</strong></li>
                                <li>Số tài khoản: <strong>0523134391</strong></li>
                                <li>Nội dung CK: <strong>THANH TOAN DH $ma_dh</strong></li>
                            </ul>
                        </div>";
                }
                
                $htmlEmail .= "
                    </div>
                </div>";

                // Lưu thông báo dạng text ngắn nội bộ để hiển thị trên web
                $msg = "Đơn hàng #$ma_dh của bạn đã được đặt thành công. Tổng thanh toán: " . number_format($totalThanhToan) . " VND.";
                $stmt_email = $this->conn->prepare("INSERT INTO thong_bao_email (ma_tk_nhan, tieu_de, noi_dung) VALUES (?, 'Đặt hàng thành công', ?)");
                if ($stmt_email) {
                    $stmt_email->execute([$ma_khach_hang, $msg]);
                }

                // Thực hiện gửi Email thực tế thông qua SMTP Socket nếu khách hàng có email
                if ($email_khach_hang && file_exists(__DIR__ . '/../model/SmtpMailer.php')) {
                    require_once __DIR__ . '/../model/SmtpMailer.php';
                    SmtpMailer::sendMail($email_khach_hang, "LENS & LIGHT - Xác nhận đơn hàng #$ma_dh", $htmlEmail);
                }
            }

            // 5. Khấu trừ số lượng lượt sử dụng còn lại của Voucher mã giảm giá
            if ($ma_voucher) {
                $stmt_v = $this->conn->prepare("UPDATE voucher SET so_luong = GREATEST(0, so_luong - 1) WHERE ma_voucher = ?");
                $stmt_v->execute([$ma_voucher]);
            }
            
            // 6. Xóa giỏ hàng local và trả về thông báo thành công
            echo json_encode(['success' => true, 'order_id' => $ma_dh, 'totalThanhToan' => $totalThanhToan, 'paymentMethod' => $paymentMethod]);
            exit;
        } catch (Exception $e) {
            // Nếu có bất kỳ lỗi nào xảy ra trong Transaction, tiến hành ROLLBACK khôi phục lại trạng thái ban đầu của CSDL
            $this->conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi tạo đơn hàng: ' . $e->getMessage()]);
        }
    }

    /**
     * API kiểm tra mã voucher qua AJAX
     */
    public function checkVoucher() {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        $code = isset($data['code']) ? trim($data['code']) : '';
        $totalRaw = isset($data['totalRaw']) ? (float)$data['totalRaw'] : 0;
        
        
        $res = $this->voucherModel->validateVoucher($code, $totalRaw);
        echo json_encode($res);
    }

    /**
     * Lấy toàn bộ lịch sử đơn hàng của tài khoản đang đăng nhập (AJAX GET).
     */
    public function getClientOrders() {
        
        if ($this->conn === false) {
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
        $stmt = $this->conn->prepare("SELECT ma_tk FROM tai_khoan WHERE username = ?");
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
        $stmt_order = $this->conn->prepare("SELECT ma_dh as id, ngay_dat as date_val, tong_thanh_toan as total_val, trang_thai_don as status FROM don_hang WHERE ma_khach_hang = ? ORDER BY ma_dh DESC");
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
            $stmt_item = $this->conn->prepare("SELECT c.ma_hh as id, h.ten_hang_hoa as name, h.anh as image, c.so_luong as quantity, c.gia_luc_mua as price_val, n.ten_ncc as brand FROM chi_tiet_don_hang c JOIN hang_hoa h ON c.ma_hh = h.ma_hh LEFT JOIN nha_cung_cap n ON h.ma_ncc = n.ma_ncc WHERE c.ma_dh = ?");
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
    public function handleUpdateStatus() {
        
        if ($this->conn === false) {
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
        $stmt = $this->conn->prepare("UPDATE don_hang SET trang_thai_don = ? WHERE ma_dh = ?");
        
        if ($stmt->execute([$status, $orderId])) {
            // Đồng thời ghi nhận hành trình cập nhật vào lịch sử giao dịch đơn hàng
            $stmt_history = $this->conn->prepare("INSERT INTO lich_su_giao_hang (ma_dh, trang_thai, mo_ta) VALUES (?, ?, 'Trạng thái đơn hàng được cập nhật bởi Ban quản lý')");
            if ($stmt_history) {
                $stmt_history->execute([$orderId, $status]);
            }
            
            // --- BỔ SUNG: GỬI EMAIL CẬP NHẬT CHO NGƯỜI MUA ---
            $stmt_info = $this->conn->prepare("SELECT tk.email, dh.ten_nguoi_nhan FROM don_hang dh JOIN tai_khoan tk ON dh.ma_khach_hang = tk.ma_tk WHERE dh.ma_dh = ?");
            $stmt_info->execute([$orderId]);
            $info = $stmt_info->fetch();
            
            if ($info && !empty($info['email'])) {
                // Map status to human-readable format
                $statusMap = [
                    'ChoXacNhan' => 'Chờ Xác Nhận',
                    'DangGiao'   => 'Đang Giao Hàng',
                    'HoanThanh'  => 'Đã Hoàn Thành',
                    'DaHuy'      => 'Đã Hủy'
                ];
                $readableStatus = isset($statusMap[$status]) ? $statusMap[$status] : $status;
                
                $htmlEmail = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
                    <div style='background: #ea580c; color: white; padding: 20px; text-align: center;'>
                        <h2 style='margin: 0;'>CẬP NHẬT ĐƠN HÀNG LENS & LIGHT</h2>
                    </div>
                    <div style='padding: 20px;'>
                        <p>Xin chào <strong>{$info['ten_nguoi_nhan']}</strong>,</p>
                        <p>Đơn hàng <strong>#{$orderId}</strong> của bạn vừa được cập nhật trạng thái mới.</p>
                        <p style='font-size: 1.2rem; margin: 20px 0; text-align: center;'>Trạng thái hiện tại: <strong style='color: #ea580c; padding: 10px 15px; border: 1px solid #ea580c; border-radius: 5px; display: inline-block;'>{$readableStatus}</strong></p>
                        <p>Bạn có thể theo dõi chi tiết hành trình đơn hàng tại mục Tài Khoản -> Đơn Hàng trên website LENS & LIGHT.</p>
                        <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi.</p>
                        <p>Cảm ơn bạn đã tin tưởng mua sắm tại LENS & LIGHT!</p>
                    </div>
                    <div style='background: #f9f9f9; padding: 15px; text-align: center; font-size: 0.9em; color: #777;'>
                        Đây là email tự động, vui lòng không phản hồi.
                    </div>
                </div>";
                
                if (file_exists(__DIR__ . '/../model/SmtpMailer.php')) {
                    require_once __DIR__ . '/../model/SmtpMailer.php';
                    SmtpMailer::sendMail($info['email'], "LENS & LIGHT - Cập nhật trạng thái đơn hàng #$orderId", $htmlEmail);
                }
            }
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cập nhật trạng thái đơn hàng thất bại.']);
        }
    }
}
