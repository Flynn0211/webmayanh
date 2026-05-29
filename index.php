<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'trangchu';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle standalone actions (e.g., logout)
if ($action === 'client_logout') {
    require_once 'model/database.php';
    require_once 'control/AuthController.php';
    AuthController::handleClientLogout();
    exit;
}

// Basic routing mapping
$clientPages = ['trangchu', 'mayanh', 'ongkinh', 'chitietsanpham', 'giohang', 'donhang', 'login', 'taikhoan'];

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
