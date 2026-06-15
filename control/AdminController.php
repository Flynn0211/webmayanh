<?php
/**
 * Tệp tin: AdminController.php
 * Chức năng: Xử lý logic và nghiệp vụ liên quan đến AdminController
 * Tác giả: Nhóm Lập Trình Web Nâng Cao
 */

/**
 * Lớp AdminController xử lý toàn bộ các hành động Quản trị (Admin) thông qua AJAX POST
 * bao gồm: quản lý sản phẩm (thêm/sửa/xóa), chuyển đổi thông số kỹ thuật sang JSON, upload ảnh base64, thêm mã voucher và cập nhật trạng thái đơn hàng.
 */

// Nạp kết nối cơ sở dữ liệu
require_once __DIR__ . '/../model/database.php';

class AdminController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Xử lý các yêu cầu nghiệp vụ AJAX Quản trị được gửi từ Panel quản lý.
     */
    public function handleAjaxAction() {
        
        if ($this->conn === false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Database connection failed']);
            exit;
        }

        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            
            // Allow GET for specific actions, require POST for others
            $isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
            
            // --- XỬ LÝ GET KHUYẾN MÃI ---
            if ($action === 'get_promotions') {
                ob_clean();
                header('Content-Type: application/json');
                $sql = "SELECT km.ma_km, km.loai_giam_gia, km.gia_tri_giam, km.ngay_bat_dau, km.ngay_het_han, km.trang_thai, hh.ten_hang_hoa, ct.ma_hh 
                        FROM khuyen_mai km
                        LEFT JOIN chi_tiet_khuyen_mai ct ON km.ma_km = ct.ma_km
                        LEFT JOIN hang_hoa hh ON ct.ma_hh = hh.ma_hh
                        ORDER BY km.ma_km DESC";
                $res = $this->conn->query($sql);
                $promos = [];
                if ($res) {
                    while ($row = $res->fetch()) {
                        $discountStr = ($row['loai_giam_gia'] === 'PhanTram') ? $row['gia_tri_giam'] . '%' : number_format($row['gia_tri_giam']) . 'đ';
                        $promos[] = [
                            'id' => $row['ma_km'],
                            'product' => $row['ten_hang_hoa'] ? $row['ten_hang_hoa'] : 'Tất cả sản phẩm',
                            'discount' => $discountStr,
                            'start' => date('d/m/Y', strtotime($row['ngay_bat_dau'])),
                            'end' => date('d/m/Y', strtotime($row['ngay_het_han'])),
                            'status' => $row['trang_thai']
                        ];
                    }
                }
                echo json_encode(['success' => true, 'promotions' => $promos]);
                exit;
            }

            // --- XỬ LÝ GET ĐÁNH GIÁ ---
            if ($action === 'get_reviews') {
                ob_clean();
                header('Content-Type: application/json');
                $sql = "SELECT b.ma_bl, b.noi_dung, b.so_sao, b.ngay_bl, t.username, h.ten_hang_hoa 
                        FROM binh_luan_danh_gia b 
                        LEFT JOIN tai_khoan t ON b.ma_tk = t.ma_tk 
                        LEFT JOIN hang_hoa h ON b.ma_hh = h.ma_hh 
                        ORDER BY b.ngay_bl DESC";
                $res = $this->conn->query($sql);
                $reviews = [];
                if ($res) {
                    while ($row = $res->fetch()) {
                        $reviews[] = [
                            'id' => $row['ma_bl'],
                            'product' => $row['ten_hang_hoa'],
                            'user' => $row['username'],
                            'content' => $row['noi_dung'] . ' (' . $row['so_sao'] . ' sao)',
                            'date' => $row['ngay_bl']
                        ];
                    }
                }
                echo json_encode(['success' => true, 'reviews' => $reviews]);
                exit;
            }

            // Dọn sạch bộ đệm trước khi trả về dữ liệu JSON
            ob_clean();
            header('Content-Type: application/json');
            
            // --- XỬ LÝ KHÓA/MỞ KHÓA TÀI KHOẢN ---
            if ($action === 'toggle_user_status') {
                $id = isset($_GET['id']) ? trim($_GET['id']) : '';
                if ($id === '') {
                    echo json_encode(['success' => false, 'error' => 'Thiếu ID người dùng']);
                    exit;
                }
                
                $stmt = $this->conn->prepare("SELECT trang_thai FROM tai_khoan WHERE ma_tk = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch();
                if ($user) {
                    $new_status = ($user['trang_thai'] === 'BiKhoa') ? 'HoatDong' : 'BiKhoa';
                    $stmt_up = $this->conn->prepare("UPDATE tai_khoan SET trang_thai = ? WHERE ma_tk = ?");
                    if ($stmt_up->execute([$new_status, $id])) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Lỗi CSDL']);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'Không tìm thấy người dùng']);
                }
                exit;
            }
            
            if (!$isPost) {
                // Ignore other GET actions if they are supposed to be POST
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
                exit;
            }
            
            // --- XỬ LÝ THÊM HOẶC CẬP NHẬT SẢN PHẨM ---
            if ($action === 'add_product' || $action === 'edit_product') {
                $id = isset($_POST['id']) ? trim($_POST['id']) : '';
                $categoryId = isset($_POST['category']) ? (int)$_POST['category'] : 1;
                $brand = isset($_POST['brand']) ? trim($_POST['brand']) : 'Generic';
                $name = isset($_POST['name']) ? trim($_POST['name']) : '';
                $price = isset($_POST['price']) ? trim($_POST['price']) : '0';
                $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
                $image = isset($_POST['image']) ? trim($_POST['image']) : '';
                $desc = isset($_POST['description']) ? trim($_POST['description']) : '';
                $specs = isset($_POST['specs']) ? trim($_POST['specs']) : '';
                
                // --- XỬ LÝ TỰ ĐỘNG CHUYỂN THÔNG SỐ TỪ PLAIN TEXT SANG MẢNG JSON ---
                $specs_json = '{}';
                if (!empty($specs)) {
                    json_decode($specs);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $specs_json = $specs;
                    } else {
                        // Người dùng nhập dạng thô: "Cảm biến: 61MP \n Chống rung: Có"
                        $specs_array = [];
                        $lines = explode("\n", str_replace("\r", "", $specs));
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if (!empty($line)) {
                                if (strpos($line, ':') !== false) {
                                    list($key, $val) = explode(':', $line, 2);
                                    $specs_array[trim($key)] = trim($val);
                                } else {
                                    $specs_array[$line] = "Có";
                                }
                            }
                        }
                        $specs_json = json_encode($specs_array, JSON_UNESCAPED_UNICODE);
                    }
                }
                
                // 1. Tìm hoặc tạo mới Nhà cung cấp (ma_ncc) dựa trên tên Thương hiệu nhập vào
                $ma_ncc = 1;
                $stmt = $this->conn->prepare("SELECT ma_ncc FROM nha_cung_cap WHERE LOWER(ten_ncc) = LOWER(?)");
                $stmt->execute([$brand]);
                if ($row = $stmt->fetch()) {
                    $ma_ncc = $row['ma_ncc'];
                } else {
                    $stmt_ins = $this->conn->prepare("INSERT INTO nha_cung_cap (ten_ncc, sdt_lien_he, dia_chi) VALUES (?, '0900000000', 'Unknown')");
                    $stmt_ins->execute([$brand]);
                    $ma_ncc = $this->conn->lastInsertId();
                }
                
                // 1.5. Đảm bảo danh mục tồn tại an toàn
                $ma_dm = 1;
                $stmt_cat = $this->conn->query("SELECT ma_dm FROM danh_muc LIMIT 1");
                if ($row_cat = $stmt_cat->fetch()) {
                    $ma_dm = $row_cat['ma_dm'];
                } else {
                    $this->conn->query("INSERT INTO danh_muc (ten_danh_muc, slug) VALUES ('Máy ảnh', 'may-anh')");
                    $ma_dm = $this->conn->lastInsertId();
                }

                // 1.7 Đảm bảo kho hàng mặc định tồn tại an toàn
                try {
                    $stmt_kho = $this->conn->query("SELECT ma_kho FROM kho_hang WHERE ma_kho = 1");
                    if ($stmt_kho && !$stmt_kho->fetch()) {
                        $this->conn->query("INSERT INTO kho_hang (ma_kho, ten_kho) VALUES (1, 'Kho Tổng')");
                    }
                } catch (Exception $e) {
                    // Ignore if table doesn't exist
                }
                
                // 2. Chuẩn hóa giá bán (loại bỏ ký tự phân tách nghìn)
                $price_cleaned = (float)preg_replace('/[^0-9.]/', '', $price);
                
                // Giải mã Base64 và lưu thành File tĩnh thay vì lưu thẳng vào DB
                $saved_image = $this->saveBase64Image($image, "main");

                // 3.5. Xử lý mảng ảnh phụ
                $additional_images = isset($_POST['additional_images']) ? trim($_POST['additional_images']) : '[]';
                $add_images_arr = json_decode($additional_images, true);
                $saved_add_images = [];
                if (is_array($add_images_arr)) {
                    foreach ($add_images_arr as $idx => $img_val) {
                        if (!empty($img_val)) {
                            $saved_add_images[] = $this->saveBase64Image($img_val, "sub_$idx");
                        }
                    }
                }
                $anh_phu_json = json_encode($saved_add_images, JSON_UNESCAPED_SLASHES);
                
                // Tạo slug thân thiện với SEO
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                
                // Thực thi thêm mới sản phẩm
                if ($action === 'add_product') {
                    $stmt_add = $this->conn->prepare("INSERT INTO hang_hoa (ma_dm, ma_ncc, ten_hang_hoa, slug, anh, anh_phu, mo_ta, thong_so_ky_thuat, gia_hien_tai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt_add->execute([$categoryId, $ma_ncc, $name, $slug, $saved_image, $anh_phu_json, $desc, $specs_json, $price_cleaned])) {
                        $new_id = $this->conn->lastInsertId();
                        // Lưu số lượng tồn kho mặc định vào kho số 1
                        $stmt_stock = $this->conn->prepare("INSERT INTO ton_kho_chi_tiet (ma_kho, ma_hh, so_luong_ton) VALUES (1, ?, ?)");
                        $stmt_stock->execute([$new_id, $stock]);
                        echo json_encode(['success' => true, 'id' => $new_id]);
                    } else {
                        echo json_encode(['success' => false, 'error' => $stmt_add->errorInfo()[2]]);
                    }
                } else {
                    // Cập nhật thông tin sản phẩm hiện tại
                    $original_id = (int)$id;
                    $stmt_edit = $this->conn->prepare("UPDATE hang_hoa SET ma_dm = ?, ma_ncc = ?, ten_hang_hoa = ?, slug = ?, anh = ?, anh_phu = ?, mo_ta = ?, thong_so_ky_thuat = ?, gia_hien_tai = ? WHERE ma_hh = ?");
                    if ($stmt_edit->execute([$categoryId, $ma_ncc, $name, $slug, $saved_image, $anh_phu_json, $desc, $specs_json, $price_cleaned, $original_id])) {
                        // Xóa cũ thêm mới số lượng tồn kho
                        $stmt_del = $this->conn->prepare("DELETE FROM ton_kho_chi_tiet WHERE ma_hh = ?");
                        $stmt_del->execute([$original_id]);
                        $stmt_stock = $this->conn->prepare("INSERT INTO ton_kho_chi_tiet (ma_kho, ma_hh, so_luong_ton) VALUES (1, ?, ?)");
                        $stmt_stock->execute([$original_id, $stock]);
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => $stmt_edit->errorInfo()[2]]);
                    }
                }
            } 
            elseif ($action === 'delete_product') {
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                // Xóa các bảng liên quan trước để tránh lỗi ràng buộc khóa ngoại (Constraint)
                $stmt1 = $this->conn->prepare("DELETE FROM ton_kho_chi_tiet WHERE ma_hh = ?");
                $stmt1->execute([$id]);
                $stmt2 = $this->conn->prepare("DELETE FROM chi_tiet_don_hang WHERE ma_hh = ?");
                $stmt2->execute([$id]);
                $stmt_km = $this->conn->prepare("DELETE FROM chi_tiet_khuyen_mai WHERE ma_hh = ?");
                $stmt_km->execute([$id]);
                $stmt_bl = $this->conn->prepare("DELETE FROM binh_luan_danh_gia WHERE ma_hh = ?");
                $stmt_bl->execute([$id]);
                
                $stmt3 = $this->conn->prepare("DELETE FROM hang_hoa WHERE ma_hh = ?");
                $res = $stmt3->execute([$id]);
                if ($res) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $this->conn->errorInfo()[2]]);
                }
            } 
            // --- XỬ LÝ THÊM MỚI VOUCHER GIẢM GIÁ ---
            elseif ($action === 'add_voucher') {
                $code = isset($_POST['code']) ? trim($_POST['code']) : '';
                $discount = isset($_POST['discount']) ? trim($_POST['discount']) : '0';
                $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
                $expire = isset($_POST['expire']) ? trim($_POST['expire']) : '';
                
                // Tách loại giảm giá phần trăm hoặc tiền mặt từ ký tự '%'
                $discount_val = (float)preg_replace('/[^0-9.]/', '', $discount);
                $loai_giam_gia = (strpos($discount, '%') !== false) ? 'PhanTram' : 'TienMat';
                
                $ngay_het_han = $expire . ' 23:59:59';
                
                $stmt_v = $this->conn->prepare("INSERT INTO voucher (ma_code, loai_giam_gia, gia_tri_giam, don_toi_thieu, so_luong, ngay_bat_dau, ngay_het_han, trang_thai) VALUES (?, ?, ?, 0.00, ?, NOW(), ?, 'HoatDong')");
                if ($stmt_v->execute([$code, $loai_giam_gia, $discount_val, $quantity, $ngay_het_han])) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $stmt_v->errorInfo()[2]]);
                }
            } 
            // --- XỬ LÝ CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG ---
            elseif ($action === 'update_order') {
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                $status = isset($_POST['status']) ? trim($_POST['status']) : '';
                
                $stmt_o = $this->conn->prepare("UPDATE don_hang SET trang_thai_don = ? WHERE ma_dh = ?");
                if ($stmt_o->execute([$status, $id])) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $stmt_o->errorInfo()[2]]);
                }
            } 
            // --- XỬ LÝ THÊM KHUYẾN MÃI ---
            elseif ($action === 'add_promotion') {
                $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
                $discount = isset($_POST['discount']) ? trim($_POST['discount']) : '0';
                $expire = isset($_POST['expire']) ? trim($_POST['expire']) : '';
                
                $discount_val = (float)preg_replace('/[^0-9.]/', '', $discount);
                $loai_giam_gia = (strpos($discount, '%') !== false) ? 'PhanTram' : 'TienMat';
                
                $ngay_het_han = $expire . ' 23:59:59';
                
                $this->conn->beginTransaction();
                try {
                    $stmt_km = $this->conn->prepare("INSERT INTO khuyen_mai (loai_giam_gia, gia_tri_giam, ngay_bat_dau, ngay_het_han, trang_thai) VALUES (?, ?, NOW(), ?, 'HoatDong')");
                    $stmt_km->execute([$loai_giam_gia, $discount_val, $ngay_het_han]);
                    $ma_km = $this->conn->lastInsertId();
                    
                    if ($product_id > 0) {
                        $stmt_ct = $this->conn->prepare("INSERT INTO chi_tiet_khuyen_mai (ma_km, ma_hh) VALUES (?, ?)");
                        $stmt_ct->execute([$ma_km, $product_id]);
                    } else {
                        // Áp dụng cho tất cả sản phẩm đang bán
                        $stmt_all = $this->conn->query("SELECT ma_hh FROM hang_hoa WHERE trang_thai = 'DangBan'");
                        $stmt_ct = $this->conn->prepare("INSERT INTO chi_tiet_khuyen_mai (ma_km, ma_hh) VALUES (?, ?)");
                        while ($row = $stmt_all->fetch()) {
                            $stmt_ct->execute([$ma_km, $row['ma_hh']]);
                        }
                    }
                    
                    $this->conn->commit();
                    
                    // --- GỬI EMAIL THÔNG BÁO CHO TẤT CẢ KHÁCH HÀNG ĐÃ ĐĂNG KÝ ---
                    try {
                        // Lấy toàn bộ email từ bảng người đăng ký VÀ các tài khoản có khai báo email
                        $sql = "SELECT email FROM email_dang_ky UNION SELECT email FROM tai_khoan WHERE email IS NOT NULL AND email != ''";
                        $stmt_emails = $this->conn->query($sql);
                        $bccList = [];
                        while ($row = $stmt_emails->fetch()) {
                            if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                                $bccList[] = $row['email'];
                            }
                        }

                        if (!empty($bccList)) {
                            require_once __DIR__ . '/../model/SmtpMailer.php';
                            // Nếu có áp dụng cho 1 sản phẩm cụ thể thì lấy tên sản phẩm
                            $prodName = "Tất cả sản phẩm";
                            if ($product_id > 0) {
                                $stmt_pn = $this->conn->prepare("SELECT ten_hang_hoa FROM hang_hoa WHERE ma_hh = ?");
                                $stmt_pn->execute([$product_id]);
                                if ($r = $stmt_pn->fetch()) {
                                    $prodName = $r['ten_hang_hoa'];
                                }
                            }

                            $discountStr = ($loai_giam_gia === 'PhanTram') ? $discount_val . '%' : number_format($discount_val) . 'đ';
                            
                            $subject = "Khuyến mãi mới từ LENS & LIGHT: Giảm tới $discountStr";
                            $body = "
                                <html>
                                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                                        <h2 style='color: #e63946;'>Tin vui dành cho bạn!</h2>
                                        <p>LENS & LIGHT vừa tung ra chương trình khuyến mãi cực sốc dành riêng cho cộng đồng yêu nhiếp ảnh.</p>
                                        <div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #e63946; margin-top: 20px;'>
                                            <p>🎁 <strong>Sản phẩm áp dụng:</strong> $prodName</p>
                                            <p>🔥 <strong>Mức giảm giá:</strong> <span style='font-size:1.2rem; color:#e63946;'>$discountStr</span></p>
                                            <p>⏳ <strong>Hạn sử dụng:</strong> " . date('d/m/Y H:i', strtotime($expire)) . "</p>
                                        </div>
                                        <p style='margin-top: 20px;'><a href='http://" . $_SERVER['HTTP_HOST'] . "' style='background: #e63946; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px; display: inline-block;'>Mua sắm ngay</a></p>
                                        <br/>
                                        <p>Trân trọng,<br/><strong>Đội ngũ LENS & LIGHT</strong></p>
                                    </div>
                                </body>
                                </html>
                            ";
                            
                            SmtpMailer::sendNewsletter($bccList, $subject, $body);
                        }
                    } catch (Exception $em) {
                        // Bỏ qua lỗi gửi mail để không làm gián đoạn thêm khuyến mãi
                        error_log("Failed to send promo email: " . $em->getMessage());
                    }

                    echo json_encode(['success' => true]);
                } catch(Exception $e) {
                    $this->conn->rollBack();
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
            }
            // --- XỬ LÝ XÓA KHUYẾN MÃI ---
            elseif ($action === 'delete_promotion') {
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                $stmt1 = $this->conn->prepare("DELETE FROM chi_tiet_khuyen_mai WHERE ma_km = ?");
                $stmt1->execute([$id]);
                $stmt2 = $this->conn->prepare("DELETE FROM khuyen_mai WHERE ma_km = ?");
                $res = $stmt2->execute([$id]);
                if ($res) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $this->conn->errorInfo()[2]]);
                }
            }
            // --- XỬ LÝ XÓA ĐÁNH GIÁ ---
            elseif ($action === 'delete_review') {
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                $stmt_del = $this->conn->prepare("DELETE FROM binh_luan_danh_gia WHERE ma_bl = ?");
                $res = $stmt_del->execute([$id]);
                if ($res) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $this->conn->errorInfo()[2]]);
                }
            }
            // --- XỬ LÝ THÊM DANH MỤC ---
            elseif ($action === 'add_category') {
                $name = isset($_POST['name']) ? trim($_POST['name']) : '';
                if ($name === '') {
                    echo json_encode(['success' => false, 'error' => 'Tên danh mục không được để trống']);
                    exit;
                }
                
                // Tạo slug đơn giản
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
                
                $stmt = $this->conn->prepare("INSERT INTO danh_muc (ten_danh_muc, slug) VALUES (?, ?)");
                if ($stmt->execute([$name, $slug])) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $stmt->errorInfo()[2]]);
                }
            }
            // --- XỬ LÝ CẬP NHẬT DANH MỤC ---
            elseif ($action === 'edit_category') {
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                $name = isset($_POST['name']) ? trim($_POST['name']) : '';
                if ($name === '' || $id <= 0) {
                    echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ']);
                    exit;
                }
                
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
                
                $stmt = $this->conn->prepare("UPDATE danh_muc SET ten_danh_muc = ?, slug = ? WHERE ma_dm = ?");
                if ($stmt->execute([$name, $slug, $id])) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $stmt->errorInfo()[2]]);
                }
            }
            // --- XỬ LÝ XÓA DANH MỤC ---
            elseif ($action === 'delete_category') {
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                if ($id <= 0) {
                    echo json_encode(['success' => false, 'error' => 'ID danh mục không hợp lệ']);
                    exit;
                }
                
                // KIỂM TRA RÀNG BUỘC SẢN PHẨM TRƯỚC KHI XÓA
                $stmt_check = $this->conn->prepare("SELECT COUNT(*) as count FROM hang_hoa WHERE ma_dm = ?");
                $stmt_check->execute([$id]);
                $row = $stmt_check->fetch();
                if ($row && $row['count'] > 0) {
                    echo json_encode(['success' => false, 'error' => 'Không thể xóa danh mục đang có sản phẩm (' . $row['count'] . ' sản phẩm)! Vui lòng xóa hoặc di chuyển các sản phẩm này trước.']);
                    exit;
                }
                
                // Tiến hành xóa nếu an toàn
                $stmt_del = $this->conn->prepare("DELETE FROM danh_muc WHERE ma_dm = ?");
                if ($stmt_del->execute([$id])) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $stmt_del->errorInfo()[2]]);
                }
            }
            exit;
        }
    }

    /**
     * Hàm hỗ trợ giải mã Base64 thành File ảnh vật lý và lưu vào uploads/products/
     */
    private function saveBase64Image($base64String, $prefix = 'img') {
        // Nếu không phải định dạng base64 (ví dụ đã là URL tĩnh), giữ nguyên
        if (strpos($base64String, 'data:image/') !== 0) {
            return $base64String;
        }

        // Tách header type và phần dữ liệu data
        list($type, $data) = explode(';', $base64String);
        list(, $data)      = explode(',', $data);
        
        $ext = 'png';
        if (strpos($type, 'jpeg') !== false || strpos($type, 'jpg') !== false) {
            $ext = 'jpg';
        } elseif (strpos($type, 'webp') !== false) {
            $ext = 'webp';
        } elseif (strpos($type, 'gif') !== false) {
            $ext = 'gif';
        }

        $filename = "img_" . uniqid() . "_{$prefix}.{$ext}";
        $filepath = "uploads/products/{$filename}";

        $decodedData = base64_decode($data);
        if ($decodedData !== false) {
            file_put_contents(__DIR__ . '/../' . $filepath, $decodedData);
            return $filepath;
        }

        return $base64String;
    }
}
