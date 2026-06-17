<?php
// Cấu hình thời gian sống của session (30 ngày = 2592000 giây)
$session_lifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $session_lifetime);
session_set_cookie_params($session_lifetime);

// Bắt đầu session nếu chưa có (dùng để quản lý đăng nhập, giỏ hàng...)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Gọi file cấu hình hệ thống và kết nối CSDL (Database)
require_once 'config.php';
require_once 'model/database.php';

// Lấy tham số 'page' và 'action' từ URL (Mặc định trang chủ)
$page = isset($_GET['page']) ? $_GET['page'] : 'trangchu';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// ==========================================
// XỬ LÝ CÁC HÀNH ĐỘNG (ACTIONS) KHÔNG GIAO DIỆN
// ==========================================
// Các action này thường được gọi thông qua AJAX hoặc Form submit và không trả về giao diện đầy đủ (chỉ xử lý logic)

if ($action === 'client_logout') {
    // Xử lý đăng xuất phía Khách hàng
    require_once 'control/AuthController.php';
    (new AuthController($conn))->handleClientLogout();
    exit;
} elseif ($action === 'checkout') {
    // Xử lý thanh toán đơn hàng
    require_once 'control/OrderController.php';
    (new OrderController($conn))->handleCheckout();
    exit;
} elseif ($action === 'get_orders') {
    // Lấy danh sách đơn hàng của khách hàng (dành cho API nội bộ)
    require_once 'control/OrderController.php';
    (new OrderController($conn))->getClientOrders();
    exit;
} elseif ($action === 'update_order_status') {
    // Cập nhật trạng thái đơn hàng (Dành cho Admin)
    require_once 'control/OrderController.php';
    (new OrderController($conn))->handleUpdateStatus();
    exit;
} elseif ($action === 'check_voucher') {
    // Kiểm tra mã giảm giá
    require_once 'control/OrderController.php';
    (new OrderController($conn))->checkVoucher();
    exit;
} elseif ($action === 'get_reviews') {
    // Lấy danh sách đánh giá sản phẩm
    require_once 'control/ProductController.php';
    (new ProductController($conn))->getReviews();
    exit;
} elseif ($action === 'add_review') {
    // Thêm đánh giá sản phẩm mới
    require_once 'control/ProductController.php';
    (new ProductController($conn))->handleAddReview();
    exit;
} elseif ($action === 'get_profile') {
    // Lấy thông tin tài khoản Khách hàng
    require_once 'control/AuthController.php';
    (new AuthController($conn))->getProfile();
    exit;
} elseif ($action === 'update_profile') {
    // Cập nhật thông tin tài khoản Khách hàng
    require_once 'control/AuthController.php';
    (new AuthController($conn))->updateProfile();
    exit;
} elseif ($action === 'change_password') {
    // Đổi mật khẩu
    require_once 'control/AuthController.php';
    (new AuthController($conn))->changePassword();
    exit;
} elseif ($action === 'submit_contact') {
    // Gửi form liên hệ
    require_once 'control/ContactController.php';
    (new ContactController($conn))->submitContact();
    exit;
} elseif ($action === 'subscribe_newsletter') {
    // Đăng ký nhận bản tin (Newsletter)
    require_once 'control/ContactController.php';
    (new ContactController($conn))->handleNewsletter();
    exit;
}

// ==========================================
// BỘ ĐỊNH TUYẾN (ROUTING) HIỂN THỊ GIAO DIỆN FRONTEND
// Đóng vai trò như một Front Controller cốt lõi, điều hướng (route) các luồng truy cập URL (trang) về đúng file hiển thị (View)
// ==========================================

// Danh sách các trang hợp lệ thuộc khu vực Khách hàng (Client)
$clientPages = ['trangchu', 'mayanh', 'ongkinh', 'phukien', 'chitietsanpham', 'giohang', 'donhang', 'login', 'taikhoan', 'lienhe'];

if ($page === 'admin') {
    // Chuyển hướng sang khu vực Quản trị viên
    header("Location: admin/");
    exit;
} elseif ($page === 'donhang') {
    // Nếu vào trang đơn hàng cũ, chuyển hướng sang phần Đơn hàng bên trong trang Tài Khoản
    header("Location: index.php?page=taikhoan#orders");
    exit;
} elseif ($page === 'baiviet') {
    // Xử lý hiển thị trang Danh sách bài viết (Chỉ lấy bài đã Xuất bản)
    require_once 'control/ArticleController.php';
    $articles = (new ArticleController($conn))->getAllArticles();
    $publishedArticles = array_filter($articles, function($a) {
        return $a['trang_thai'] === 'XuatBan';
    });
    include "view/client/baiviet.php";
} elseif ($page === 'chitietbaiviet') {
    // Xử lý hiển thị chi tiết một bài viết cụ thể dựa theo slug (đường dẫn rút gọn)
    require_once 'control/ArticleController.php';
    $slug = isset($_GET['slug']) ? $_GET['slug'] : '';
    $article = (new ArticleController($conn))->getArticleBySlug($slug);
    if (!$article || $article['trang_thai'] !== 'XuatBan') {
        echo "Bài viết không tồn tại hoặc đã bị ẩn.";
        exit;
    }
    include "view/client/chitietbaiviet.php";
} elseif (in_array($page, $clientPages)) {
    // Nếu page nằm trong danh sách hợp lệ, gọi file giao diện (View) tương ứng
    include "view/client/{$page}.php";
} else {
    // Nếu không khớp với bất kỳ route nào, trả về lỗi 404
    echo "404 Not Found";
}
?>
