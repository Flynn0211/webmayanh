<?php
// Cấu hình thời gian sống của session (30 ngày = 2592000 giây)
$session_lifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $session_lifetime);
session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Bật session bảo mật
session_start();

// Nạp kết nối cơ sở dữ liệu và controller
require_once __DIR__ . '/../model/database.php';
require_once __DIR__ . '/../control/AuthController.php';

if ($conn === false) {
    die("Kết nối cơ sở dữ liệu thất bại.");
}

// ==========================================
// ĐỊNH TUYẾN (ROUTING) GIAO DIỆN QUẢN TRỊ (ADMIN)
// ==========================================

// Xử lý đăng xuất phiên quản trị (Admin Logout)
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    (new AuthController($conn))->handleAdminLogout();
}

// Xử lý upload ảnh từ CKEditor
if (isset($_GET['action']) && $_GET['action'] === 'upload_image') {
    require_once __DIR__ . '/../control/ArticleController.php';
    (new ArticleController($conn))->handleCKEditorUpload();
}

$login_error = "";

// Xử lý đăng nhập POST qua Controller
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_admin'])) {
    $login_error = (new AuthController($conn))->handleAdminLogin($conn);
}

// Kiểm tra xem đã đăng nhập chưa
$is_authenticated = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Nếu đã xác thực (authenticated) thành công, nhúng giao diện trang tổng quan Admin (Dashboard)
// Ngược lại, chặn truy cập và hiển thị màn hình đăng nhập (Login) an toàn
if ($is_authenticated) {
    include __DIR__ . '/../view/admin/admin.php';
} else {
    include __DIR__ . '/../view/admin/login.php';
}
?>