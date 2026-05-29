<?php
// Bật session bảo mật
session_start();

// Nạp kết nối cơ sở dữ liệu và controller
require_once __DIR__ . '/../model/database.php';
require_once __DIR__ . '/../control/AuthController.php';

if ($conn === false) {
    die("Kết nối cơ sở dữ liệu thất bại.");
}

// Xử lý đăng xuất
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    AuthController::handleAdminLogout();
}

$login_error = "";

// Xử lý đăng nhập POST qua Controller
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_admin'])) {
    $login_error = AuthController::handleAdminLogin($conn);
}

// Kiểm tra xem đã đăng nhập chưa
$is_authenticated = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Nếu đã authenticated, nhúng trang dashboard admin, ngược lại hiển thị trang login
if ($is_authenticated) {
    include __DIR__ . '/../view/admin/admin.php';
} else {
    include __DIR__ . '/../view/admin/login.php';
}
?>
