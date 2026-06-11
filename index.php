<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'trangchu';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle standalone actions (e.g., logout, checkout)
if ($action === 'client_logout') {
    require_once 'model/database.php';
    require_once 'control/AuthController.php';
    AuthController::handleClientLogout();
    exit;
} elseif ($action === 'checkout') {
    require_once 'control/OrderController.php';
    OrderController::handleCheckout();
    exit;
} elseif ($action === 'get_orders') {
    require_once 'control/OrderController.php';
    OrderController::getClientOrders();
    exit;
} elseif ($action === 'update_order_status') {
    require_once 'control/OrderController.php';
    OrderController::handleUpdateStatus();
    exit;
} elseif ($action === 'check_voucher') {
    require_once 'control/OrderController.php';
    OrderController::checkVoucher();
    exit;
} elseif ($action === 'get_reviews') {
    require_once 'model/database.php';
    require_once 'control/ProductController.php';
    ProductController::getReviews();
    exit;
} elseif ($action === 'add_review') {
    require_once 'model/database.php';
    require_once 'control/ProductController.php';
    ProductController::handleAddReview();
    exit;
} elseif ($action === 'get_profile') {
    require_once 'model/database.php';
    require_once 'control/AuthController.php';
    AuthController::getProfile();
    exit;
} elseif ($action === 'update_profile') {
    require_once 'model/database.php';
    require_once 'control/AuthController.php';
    AuthController::updateProfile();
    exit;
} elseif ($action === 'change_password') {
    require_once 'model/database.php';
    require_once 'control/AuthController.php';
    AuthController::changePassword();
    exit;
}

// Basic routing mapping
$clientPages = ['trangchu', 'mayanh', 'ongkinh', 'phukien', 'chitietsanpham', 'giohang', 'donhang', 'login', 'taikhoan', 'lienhe'];

if ($page === 'admin') {
    header("Location: admin/");
    exit;
} elseif ($page === 'donhang') {
    header("Location: index.php?page=taikhoan#orders");
    exit;
} elseif ($page === 'baiviet') {
    require_once 'control/ArticleController.php';
    $articles = ArticleController::getAllArticles();
    $publishedArticles = array_filter($articles, function($a) {
        return $a['trang_thai'] === 'XuatBan';
    });
    include "view/client/baiviet.php";
} elseif ($page === 'chitietbaiviet') {
    require_once 'control/ArticleController.php';
    $slug = isset($_GET['slug']) ? $_GET['slug'] : '';
    $article = ArticleController::getArticleBySlug($slug);
    if (!$article || $article['trang_thai'] !== 'XuatBan') {
        echo "Bài viết không tồn tại hoặc đã bị ẩn.";
        exit;
    }
    include "view/client/chitietbaiviet.php";
} elseif (in_array($page, $clientPages)) {
    include "view/client/{$page}.php";
} else {
    // 404
    echo "404 Not Found";
}
?>
