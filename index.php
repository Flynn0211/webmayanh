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
}

// Basic routing mapping
$clientPages = ['trangchu', 'mayanh', 'ongkinh', 'chitietsanpham', 'giohang', 'donhang', 'login', 'taikhoan', 'baiviet', 'chitietbaiviet'];

if ($page === 'admin') {
    header("Location: admin/");
    exit;
} elseif (in_array($page, $clientPages)) {
    include "view/client/{$page}.php";
} else {
    // 404
    echo "404 Not Found";
}
?>
