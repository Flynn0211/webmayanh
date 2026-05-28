<?php
/**
 * Shared TopNavBar. Usage: <?php include 'view/client/layout/_navbar.php'; ?>
 * Optional: define $activeNav = 'trangchu'|'mayanh'|'giohang'|'donhang' before including.
 */
if (!isset($activeNav)) $activeNav = '';
?>
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
            <a class="nav-link" href="#">Bài viết</a>
            <a class="nav-link" href="#">Liên hệ</a>
        </div>

        <!-- Actions -->
        <div class="site-nav__actions">
            <a class="auth-admin-btn nav-link hidden" href="index.php?page=admin">Quản trị</a>
            <a class="auth-login-btn nav-link" href="index.php?page=login">Đăng nhập</a>

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

