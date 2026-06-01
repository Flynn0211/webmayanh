<?php
/**
 * Lớp AdminController xử lý toàn bộ các hành động Quản trị (Admin) thông qua AJAX POST
 * bao gồm: quản lý sản phẩm (thêm/sửa/xóa), chuyển đổi thông số kỹ thuật sang JSON, upload ảnh base64, thêm mã voucher và cập nhật trạng thái đơn hàng.
 */

// Nạp kết nối cơ sở dữ liệu
require_once __DIR__ . '/../model/database.php';

class AdminController {
    /**
     * Hàm trợ giúp giải mã và lưu hình ảnh dạng Base64 được gửi từ Frontend lên máy chủ.
     * Hỗ trợ lưu trữ dạng bất đồng bộ từ các widget kéo thả hình ảnh.
     *
     * @param string $base64_string Chuỗi dữ liệu ảnh Base64
     * @param string $output_dir Thư mục đích lưu ảnh
     * @return string|false Đường dẫn tương đối lưu tệp ảnh thành công, ngược lại trả về false
     */
    private static function save_base64_image($base64_string, $output_dir) {
        // Sử dụng Regex tách định dạng ảnh và nội dung đã mã hóa Base64
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_string, $type)) {
            $data = substr($base64_string, strpos($base64_string, ',') + 1);
            $type = strtolower($type[1]);
            
            // Giới hạn các định dạng ảnh được phép lưu để đảm bảo an toàn máy chủ
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                return false;
            }
            
            $data = base64_decode($data);
            if ($data === false) {
                return false;
            }
            
            // Tạo thư mục nếu chưa tồn tại
            if (!file_exists($output_dir)) {
                mkdir($output_dir, 0777, true);
            }
            
            // Đặt tên tệp ngẫu nhiên chống trùng lặp và lưu file xuống đĩa cứng
            $file_name = uniqid() . '.' . $type;
            $file_path = $output_dir . '/' . $file_name;
            file_put_contents($file_path, $data);
            
            return 'uploads/products/' . $file_name;
        }
        return false;
    }

    /**
     * Xử lý các yêu cầu nghiệp vụ AJAX Quản trị được gửi từ Panel quản lý.
     */
    public static function handleAjaxAction() {
        global $conn;
        if ($conn === false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Database connection failed']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
            // Dọn sạch bộ đệm trước khi trả về dữ liệu JSON
            ob_clean();
            header('Content-Type: application/json');
            $action = $_GET['action'];
            
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
                $stmt = $conn->prepare("SELECT ma_ncc FROM nha_cung_cap WHERE LOWER(ten_ncc) = LOWER(?)");
                $stmt->bind_param("s", $brand);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $ma_ncc = $row['ma_ncc'];
                } else {
                    $stmt_ins = $conn->prepare("INSERT INTO nha_cung_cap (ten_ncc, sdt_lien_he, dia_chi) VALUES (?, '0900000000', 'Unknown')");
                    $stmt_ins->bind_param("s", $brand);
                    $stmt_ins->execute();
                    $ma_ncc = $conn->insert_id;
                }
                
                // 1.5. Đảm bảo danh mục tồn tại an toàn
                $ma_dm = 1;
                $stmt_cat = $conn->query("SELECT ma_dm FROM danh_muc LIMIT 1");
                if ($row_cat = $stmt_cat->fetch_assoc()) {
                    $ma_dm = $row_cat['ma_dm'];
                } else {
                    $conn->query("INSERT INTO danh_muc (ten_danh_muc, slug) VALUES ('Máy ảnh', 'may-anh')");
                    $ma_dm = $conn->insert_id;
                }
                
                // 2. Chuẩn hóa giá bán (loại bỏ ký tự phân tách nghìn)
                $price_cleaned = (float)preg_replace('/[^0-9.]/', '', $price);
                
                // 3. Xử lý ảnh chính (Base64 hoặc giữ nguyên đường dẫn cũ)
                $saved_image = $image;
                if (strpos($image, 'data:image') === 0) {
                    $upload_dir = __DIR__ . '/../uploads/products';
                    $saved_image = self::save_base64_image($image, $upload_dir);
                    if (!$saved_image) {
                        $saved_image = $image;
                    }
                }

                // 3.5. Xử lý mảng ảnh phụ (Option 2) lưu trữ dạng mảng JSON
                $additional_images = isset($_POST['additional_images']) ? trim($_POST['additional_images']) : '[]';
                $add_images_arr = json_decode($additional_images, true);
                $saved_add_images = [];
                if (is_array($add_images_arr)) {
                    $upload_dir = __DIR__ . '/../uploads/products';
                    foreach ($add_images_arr as $img_val) {
                        if (strpos($img_val, 'data:image') === 0) {
                            $saved_path = self::save_base64_image($img_val, $upload_dir);
                            if ($saved_path) {
                                $saved_add_images[] = $saved_path;
                            }
                        } else if (!empty($img_val)) {
                            $saved_add_images[] = $img_val;
                        }
                    }
                }
                $anh_phu_json = json_encode($saved_add_images, JSON_UNESCAPED_SLASHES);
                
                // Tạo slug thân thiện với SEO
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                
                // Thực thi thêm mới sản phẩm
                if ($action === 'add_product') {
                    $stmt_add = $conn->prepare("INSERT INTO hang_hoa (ma_dm, ma_ncc, ten_hang_hoa, slug, anh, anh_phu, mo_ta, thong_so_ky_thuat, gia_hien_tai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt_add->bind_param("iissssssd", $categoryId, $ma_ncc, $name, $slug, $saved_image, $anh_phu_json, $desc, $specs_json, $price_cleaned);
                    if ($stmt_add->execute()) {
                        $new_id = $conn->insert_id;
                        // Lưu số lượng tồn kho mặc định vào kho số 1
                        $stmt_stock = $conn->prepare("INSERT INTO ton_kho_chi_tiet (ma_kho, ma_hh, so_luong_ton) VALUES (1, ?, ?)");
                        $stmt_stock->bind_param("ii", $new_id, $stock);
                        $stmt_stock->execute();
                        echo json_encode(['success' => true, 'id' => $new_id]);
                    } else {
                        echo json_encode(['success' => false, 'error' => $conn->error]);
                    }
                } else {
                    // Cập nhật thông tin sản phẩm hiện tại
                    $original_id = (int)$id;
                    $stmt_edit = $conn->prepare("UPDATE hang_hoa SET ma_dm = ?, ma_ncc = ?, ten_hang_hoa = ?, slug = ?, anh = ?, anh_phu = ?, mo_ta = ?, thong_so_ky_thuat = ?, gia_hien_tai = ? WHERE ma_hh = ?");
                    $stmt_edit->bind_param("iissssssdi", $categoryId, $ma_ncc, $name, $slug, $saved_image, $anh_phu_json, $desc, $specs_json, $price_cleaned, $original_id);
                    if ($stmt_edit->execute()) {
                        // Xóa cũ thêm mới số lượng tồn kho
                        $conn->query("DELETE FROM ton_kho_chi_tiet WHERE ma_hh = $original_id");
                        $stmt_stock = $conn->prepare("INSERT INTO ton_kho_chi_tiet (ma_kho, ma_hh, so_luong_ton) VALUES (1, ?, ?)");
                        $stmt_stock->bind_param("ii", $original_id, $stock);
                        $stmt_stock->execute();
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => $conn->error]);
                    }
                }
            } 
            // --- XỬ LÝ XÓA SẢN PHẨM ---
            elseif ($action === 'delete_product') {
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                // Xóa các bảng liên quan trước để tránh lỗi ràng buộc khóa ngoại (Constraint)
                $conn->query("DELETE FROM ton_kho_chi_tiet WHERE ma_hh = $id");
                $conn->query("DELETE FROM chi_tiet_don_hang WHERE ma_hh = $id");
                $res = $conn->query("DELETE FROM hang_hoa WHERE ma_hh = $id");
                if ($res) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $conn->error]);
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
                
                $ngay_bat_dau = date('Y-m-d H:i:s');
                $ngay_het_han = date('Y-m-d H:i:s', strtotime($expire));
                
                $stmt_v = $conn->prepare("INSERT INTO voucher (ma_code, loai_giam_gia, gia_tri_giam, don_toi_thieu, so_luong, ngay_bat_dau, ngay_het_han, trang_thai) VALUES (?, ?, ?, 0.00, ?, ?, ?, 'HoatDong')");
                $stmt_v->bind_param("ssdiss", $code, $loai_giam_gia, $discount_val, $quantity, $ngay_bat_dau, $ngay_het_han);
                if ($stmt_v->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                }
            } 
            // --- XỬ LÝ CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG ---
            elseif ($action === 'update_order') {
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                $status = isset($_POST['status']) ? trim($_POST['status']) : '';
                
                $stmt_o = $conn->prepare("UPDATE don_hang SET trang_thai_don = ? WHERE ma_dh = ?");
                $stmt_o->bind_param("si", $status, $id);
                if ($stmt_o->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                }
            }
            exit;
        }
    }
}
