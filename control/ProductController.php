<?php
// Load product model layer
require_once __DIR__ . '/../model/ProductModel.php';

class ProductController {
    /**
     * Fetch active database products and clean/format them for frontend JSON consumption.
     */
    public static function getAllActiveProducts($conn) {
        $rawProducts = ProductModel::getActiveProducts($conn);
        $db_products = [];

        foreach ($rawProducts as $row) {
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
                'image' => $row['image'],
                'category' => $cat
            ];
        }

        return $db_products;
    }

    /**
     * Handle fetching reviews via AJAX GET
     */
    public static function getReviews() {
        global $conn;
        if (!isset($_GET['ma_hh'])) return;
        $ma_hh = (int)$_GET['ma_hh'];
        
        require_once __DIR__ . '/../model/ReviewModel.php';
        $reviews = ReviewModel::getReviewsByProduct($conn, $ma_hh);
        echo json_encode(['success' => true, 'reviews' => $reviews]);
    }

    /**
     * Handle submitting a review via AJAX POST
     */
    public static function handleAddReview() {
        global $conn;
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

        // Get ma_tk
        $stmt = $conn->prepare("SELECT ma_tk FROM tai_khoan WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $ma_tk = null;
        if ($row = $res->fetch_assoc()) {
            $ma_tk = $row['ma_tk'];
        }

        if (!$ma_tk) {
            echo json_encode(['success' => false, 'message' => 'Tài khoản không hợp lệ.']);
            return;
        }

        require_once __DIR__ . '/../model/ReviewModel.php';
        $success = ReviewModel::addReview($conn, $ma_tk, (int)$data['ma_hh'], (int)$data['so_sao'], trim($data['noi_dung']));
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Cảm ơn bạn đã đánh giá!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể thêm đánh giá lúc này.']);
        }
    }
}
?>

