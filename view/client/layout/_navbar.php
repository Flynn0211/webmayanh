<?php
/**
 * Shared TopNavBar. Usage: <?php include 'view/client/layout/_navbar.php'; ?>
 * Optional: define $activeNav = 'trangchu'|'mayanh'|'giohang'|'donhang' before including.
 */
if (!isset($activeNav)) $activeNav = '';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Auth state from PHP session (reliable, not dependent on browser localStorage) ---
$_nav_logged_in  = !empty($_SESSION['client_logged_in']);
$_nav_is_admin   = $_nav_logged_in && ($_SESSION['client_role'] ?? '') === 'admin';
$_nav_fullname   = $_nav_logged_in ? htmlspecialchars($_SESSION['client_fullname'] ?? '') : '';

// Handle client logout action
if (isset($_GET['action']) && $_GET['action'] === 'client_logout') {
    require_once __DIR__ . '/../../../control/AuthController.php';
    AuthController::handleClientLogout();
}

// Load database connection
require_once __DIR__ . '/../../../model/database.php';

// Load Product Controller to comply with MVC structure
require_once __DIR__ . '/../../../control/ProductController.php';

$db_products = [];
if (isset($conn) && $conn) {
    $db_products = ProductController::getAllActiveProducts($conn);
}
?>
<script>
window.dbProducts = <?php echo json_encode($db_products, JSON_UNESCAPED_UNICODE); ?>;
</script>


<nav class="site-nav">
    <div class="site-nav__inner">

        <!-- Brand -->
        <a class="site-nav__brand" href="index.php?page=trangchu">LENS &amp; LIGHT</a>

        <!-- Desktop links -->
        <div class="site-nav__links">
            <a class="nav-link <?= $activeNav === 'trangchu' ? 'nav-link--active' : '' ?>" href="index.php?page=trangchu">Trang chủ</a>
            <a class="nav-link <?= $activeNav === 'mayanh'  ? 'nav-link--active' : '' ?>" href="index.php?page=mayanh">Máy ảnh</a>
            <a class="nav-link <?= $activeNav === 'ongkinh' ? 'nav-link--active' : '' ?>" href="index.php?page=ongkinh">Ống kính</a>
            <a class="nav-link" href="#">Phụ kiện</a>
            <a class="nav-link <?= $activeNav === 'baiviet' ? 'nav-link--active' : '' ?>" href="index.php?page=baiviet">Bài viết</a>
            <a class="nav-link" href="#">Liên hệ</a>
        </div>

        <!-- Actions — visibility controlled by PHP session (server-side) -->
        <div class="site-nav__actions">
            <?php if ($_nav_is_admin): ?>
            <a class="auth-admin-btn nav-link" href="index.php?page=admin">Quản trị</a>
            <?php endif; ?>

            <?php if ($_nav_logged_in): ?>
            <a class="auth-profile-btn nav-link" href="index.php?page=taikhoan"
               title="Tài khoản của <?= $_nav_fullname ?>">Tài khoản</a>
            <a class="auth-logout-btn nav-link" href="index.php?action=client_logout"
               title="Đang đăng nhập: <?= $_nav_fullname ?>">
                <span class="material-symbols-outlined" style="font-size:16px">logout</span> Đăng xuất
            </a>
            <?php else: ?>
            <a class="auth-login-btn nav-link" href="index.php?page=login">Đăng nhập</a>
            <?php endif; ?>

            <a href="index.php?page=donhang"
               class="nav-icon-btn <?= $activeNav === 'donhang' ? 'nav-icon-btn--active' : '' ?>"
               title="Đơn hàng của tôi" aria-label="order_history">
                <span class="material-symbols-outlined" data-icon="receipt_long">receipt_long</span>
            </a>

            <a href="index.php?page=giohang"
               class="nav-icon-btn <?= $activeNav === 'giohang' ? 'nav-icon-btn--active' : '' ?>"
               aria-label="shopping_cart">
                <span class="material-symbols-outlined" data-icon="shopping_cart">shopping_cart</span>
                <span class="nav-cart-badge hidden" id="cartBadge"></span>
            </a>

            <button class="nav-menu-btn" aria-label="menu">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>

    </div>
</nav>

