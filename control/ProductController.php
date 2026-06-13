<?php
/**
 * Lớp ProductController điều phối toàn bộ các xử lý nghiệp vụ liên quan đến hàng hóa
 */

require_once __DIR__ . '/../model/ProductModel.php';
require_once __DIR__ . '/../model/ReviewModel.php';

class ProductController {
    private $conn;
    private $productModel;
    private $reviewModel;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->productModel = new ProductModel($conn);
        $this->reviewModel = new ReviewModel($conn);
    }

    /**
     * Lấy toàn bộ danh sách sản phẩm đang bán
     *
     * @return array Danh sách sản phẩm được định dạng chuẩn
     */
    public function getAllActiveProducts() {
        if ($this->conn === false) {
            return [];
        }
        
        $rawProducts = $this->productModel->getActiveProducts();
        $db_products = [];

        foreach ($rawProducts as $row) {
            $main_image = $row['image'];
            $additional_images = $row['additional_images'] ? $row['additional_images'] : '[]';

            $slug = $row['category_slug'];
            if ($slug === 'ong-kinh') {
                $cat = 'lens';
            } elseif ($slug === 'phu-kien') {
                $cat = 'accessory';
            } else {
                $cat = 'camera';
            }
            
            $price_formatted = number_format($row['price'], 0, '', ',') . ' ₫';
            $original_price_formatted = isset($row['original_price']) ? number_format($row['original_price'], 0, '', ',') . ' ₫' : $price_formatted;

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
    public function getReviews() {
        if ($this->conn === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            return;
        }
        if (!isset($_GET['ma_hh'])) return;
        $ma_hh = (int)$_GET['ma_hh'];
        
        $reviews = $this->reviewModel->getReviewsByProduct($ma_hh);
        echo json_encode(['success' => true, 'reviews' => $reviews]);
    }

    /**
     * Xử lý gửi đánh giá sản phẩm mới qua AJAX POST
     */
    public function handleAddReview() {
        if ($this->conn === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $username = isset($_SESSION['client_username']) ? $_SESSION['client_username'] : '';
        if (!$username) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá.']);
            return;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || !isset($data['ma_hh']) || !isset($data['so_sao']) || !isset($data['noi_dung'])) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }

        $stmt = $this->conn->prepare("SELECT ma_tk FROM tai_khoan WHERE username = ?");
        $stmt->execute([$username]);
        $ma_tk = null;
        if ($row = $stmt->fetch()) {
            $ma_tk = $row['ma_tk'];
        }

        if (!$ma_tk) {
            echo json_encode(['success' => false, 'message' => 'Tài khoản không hợp lệ.']);
            return;
        }

        $stmt_check = $this->conn->prepare("SELECT COUNT(*) as cnt FROM binh_luan_danh_gia WHERE ma_tk = ? AND ma_hh = ?");
        $product_id = (int)$data['ma_hh'];
        $stmt_check->execute([$ma_tk, $product_id]);
        if ($row_check = $stmt_check->fetch()) {
            if ($row_check['cnt'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Bạn đã đánh giá sản phẩm này rồi! Mỗi sản phẩm chỉ được đánh giá một lần.']);
                return;
            }
        }

        $success = $this->reviewModel->addReview($ma_tk, (int)$data['ma_hh'], (int)$data['so_sao'], trim($data['noi_dung']));
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Cảm ơn bạn đã đánh giá!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể thêm đánh giá lúc này.']);
        }
    }
}
