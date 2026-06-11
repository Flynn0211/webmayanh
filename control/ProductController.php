<?php
/**
 * Lớp ProductController điều phối toàn bộ các xử lý nghiệp vụ liên quan đến hàng hóa
 * bao gồm: hiển thị sản phẩm, nạp đánh giá (AJAX GET) và thêm mới đánh giá (AJAX POST) kiểm tra trùng lặp.
 */

// Nạp tầng nghiệp vụ Model của Hàng hóa
require_once __DIR__ . '/../model/ProductModel.php';

class ProductController {
    /**
     * Lấy toàn bộ danh sách sản phẩm đang bán, chuẩn hóa và định dạng dữ liệu cho Frontend tiêu thụ dưới dạng JSON.
     *
     * @param PDO|false $conn Kết nối CSDL
     * @return array Danh sách sản phẩm được định dạng chuẩn
     */
    public static function getAllActiveProducts($conn) {
        if ($conn === false) {
            return [];
        }
        
        // Gọi tầng Model lấy danh sách gốc từ CSDL
        $rawProducts = ProductModel::getActiveProducts($conn);
        $db_products = [];

        foreach ($rawProducts as $row) {
            $main_image = $row['image'];
            // Phục vụ cơ chế đa ảnh (Option 2): Phân tích mảng JSON ảnh phụ lưu trữ trong cột anh_phu
            $additional_images = $row['additional_images'] ? $row['additional_images'] : '[]';

            // Phân loại danh mục máy ảnh, ống kính, phụ kiện dựa trên slug
            $slug = $row['category_slug'];
            if ($slug === 'ong-kinh') {
                $cat = 'lens';
            } elseif ($slug === 'phu-kien') {
                $cat = 'accessory';
            } else {
                $cat = 'camera';
            }
            
            // Định dạng hiển thị tiền tệ VNĐ (VD: 34,900,000 ₫)
            $price_formatted = number_format($row['price'], 0, '', ',') . ' ₫';
            $original_price_formatted = isset($row['original_price']) ? number_format($row['original_price'], 0, '', ',') . ' ₫' : $price_formatted;

            // Xử lý chuyển đổi thông số kỹ thuật dạng JSON lưu trữ trong CSDL sang chuỗi dễ đọc
            $specs_raw = $row['specs'];
            $specs_formatted = '';
            $specs_arr = json_decode($specs_raw, true);
            if (is_array($specs_arr)) {
                $parts = [];
                foreach ($specs_arr as $key => $val) {
                    $parts[] = "$key: $val";
                }
                $specs_formatted = implode(', ', $parts);
            } else {
                $specs_formatted = $specs_raw;
            }

            // Đóng gói mảng dữ liệu hoàn thiện
            $db_products[] = [
                'id' => (int)$row['id'],
                'brand' => $row['brand'],
                'name' => $row['name'],
                'price' => $price_formatted,
                'original_price' => $original_price_formatted,
                'raw_price' => $row['price'],
                'raw_original_price' => isset($row['original_price']) ? $row['original_price'] : $row['price'],
                'description' => $row['description'],
                'specs' => $specs_formatted,
                'image' => $main_image,
                'additional_images' => $additional_images,
                'category' => $cat
            ];
        }

        return $db_products;
    }

    /**
     * Xử lý lấy danh sách đánh giá của sản phẩm qua AJAX GET
     */
    public static function getReviews() {
        global $conn;
        if ($conn === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            return;
        }
        if (!isset($_GET['ma_hh'])) return;
        $ma_hh = (int)$_GET['ma_hh'];
        
        // Nạp tầng Model Đánh giá
        require_once __DIR__ . '/../model/ReviewModel.php';
        $reviews = ReviewModel::getReviewsByProduct($conn, $ma_hh);
        echo json_encode(['success' => true, 'reviews' => $reviews]);
    }

    /**
     * Xử lý gửi đánh giá sản phẩm mới qua AJAX POST
     * Có tích hợp cơ chế ngăn chặn mỗi tài khoản chỉ được đánh giá mỗi sản phẩm 1 lần duy nhất.
     */
    public static function handleAddReview() {
        global $conn;
        if ($conn === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        // Bật session nếu chưa có
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Kiểm tra xem khách hàng đã đăng nhập chưa
        $username = isset($_SESSION['client_username']) ? $_SESSION['client_username'] : '';
        if (!$username) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá.']);
            return;
        }

        // Đọc dữ liệu JSON từ body request
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || !isset($data['ma_hh']) || !isset($data['so_sao']) || !isset($data['noi_dung'])) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }

        // Lấy mã tài khoản (ma_tk) từ tên đăng nhập đang đăng nhập
        $stmt = $conn->prepare("SELECT ma_tk FROM tai_khoan WHERE username = ?");
        $stmt->execute([$username]);
        $ma_tk = null;
        if ($row = $stmt->fetch()) {
            $ma_tk = $row['ma_tk'];
        }

        if (!$ma_tk) {
            echo json_encode(['success' => false, 'message' => 'Tài khoản không hợp lệ.']);
            return;
        }

        // --- CƠ CHẾ CHẶN ĐÁNH GIÁ TRÙNG LẶP ---
        // Truy vấn trực tiếp từ bảng binh_luan_danh_gia để đếm số lượng đánh giá của tài khoản này cho sản phẩm này
        $stmt_check = $conn->prepare("SELECT COUNT(*) as cnt FROM binh_luan_danh_gia WHERE ma_tk = ? AND ma_hh = ?");
        $product_id = (int)$data['ma_hh'];
        $stmt_check->execute([$ma_tk, $product_id]);
        if ($row_check = $stmt_check->fetch()) {
            if ($row_check['cnt'] > 0) {
                // Đã tồn tại đánh giá, trả về từ chối lịch sự
                echo json_encode(['success' => false, 'message' => 'Bạn đã đánh giá sản phẩm này rồi! Mỗi sản phẩm chỉ được đánh giá một lần.']);
                return;
            }
        }

        // Nạp tầng Model Đánh giá và lưu bản ghi mới
        require_once __DIR__ . '/../model/ReviewModel.php';
        $success = ReviewModel::addReview($conn, $ma_tk, (int)$data['ma_hh'], (int)$data['so_sao'], trim($data['noi_dung']));
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Cảm ơn bạn đã đánh giá!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể thêm đánh giá lúc này.']);
        }
    }
}
