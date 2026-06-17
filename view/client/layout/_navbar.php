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
    (new AuthController($conn))->handleClientLogout();
}

// Load database connection
require_once __DIR__ . '/../../../model/database.php';

// Load Product Controller to comply with MVC structure
require_once __DIR__ . '/../../../control/ProductController.php';

$db_products = [];
if (isset($conn) && $conn) {
    $db_products = (new ProductController($conn))->getAllActiveProducts($conn);
}
?>
<!-- Màn hình Preloader chào mừng Premium -->
<div id="preloader" class="preloader--hidden">
    <div class="preloader-spinner"></div>
    <div class="preloader-brand">LENS &amp; LIGHT</div>
</div>

<script>
window.dbProducts = <?php echo json_encode($db_products, JSON_UNESCAPED_UNICODE); ?>;
<?php if ($_nav_logged_in): ?>
    localStorage.setItem('currentUser', JSON.stringify({
        username: "<?= addslashes($_SESSION['client_username'] ?? '') ?>",
        fullname: "<?= addslashes($_SESSION['client_fullname'] ?? '') ?>",
        role: "<?= addslashes($_SESSION['client_role'] ?? 'user') ?>",
        email: "<?= addslashes($_SESSION['client_email'] ?? '') ?>",
        phone: "<?= addslashes($_SESSION['client_phone'] ?? '') ?>"
    }));
<?php else: ?>
    localStorage.removeItem('currentUser');
<?php endif; ?>

// Bản đồ chỉ số thứ tự các trang trên Thanh Menu điều hướng
const PAGE_INDICES = {
    'trangchu': 0,
    'mayanh': 1,
    'ongkinh': 2,
    'phukien': 3,
    'baiviet': 4,
    'lienhe': 5,
    'giohang': 6,
    'taikhoan': 7,
    'login': 8
};

// Hàm trợ giúp lấy tên trang từ chuỗi URL
const getPageName = (urlStr) => {
    if (!urlStr) return 'trangchu';
    if (urlStr === 'index.php' || urlStr === './' || urlStr === '/') {
        return 'trangchu';
    }
    if (urlStr.includes('page=')) {
        const parts = urlStr.split('page=');
        if (parts.length > 1) {
            return parts[1].split('&')[0].split('#')[0];
        }
    }
    return 'trangchu';
};

// Thực hiện áp dụng ngay lập tức lớp CSS tương ứng để hoạt động trước khi render giao diện
(function() {
    // 1. Chỉ hiển thị màn hình Preloader trong lần truy cập đầu tiên (First Load của website)
    const firstLoadDone = sessionStorage.getItem('first_load_done');
    const preloader = document.getElementById('preloader');
    if (!firstLoadDone && preloader) {
        preloader.classList.remove('preloader--hidden');
    }

    // 2. Tự động thêm lớp CSS trượt theo hướng chỉ mục menu hoặc trang chi tiết sản phẩm
    const search = window.location.search || window.location.href;
    let currPage = 'trangchu';
    if (search.includes('page=')) {
        const parts = search.split('page=');
        if (parts.length > 1) {
            currPage = parts[1].split('&')[0].split('#')[0];
        }
    }

    if (currPage === 'chitietsanpham') {
        document.body.classList.add('entry-product');
    } else {
        const direction = sessionStorage.getItem('nav_direction');
        if (direction === 'to-left') {
            document.body.classList.add('entry-from-right');
        } else if (direction === 'to-right') {
            document.body.classList.add('entry-from-left');
        } else {
            document.body.classList.add('entry-default');
        }
    }
    // Xóa dấu vết chuyển hướng để tránh lặp lại hiệu ứng khi tải lại trang (reload)
    sessionStorage.removeItem('nav_direction');
})();

// Xử lý màn hình Preloader & Hiệu ứng chuyển tiếp trang mượt mà (Transitions)
document.addEventListener('DOMContentLoaded', () => {
    // 1. Ẩn màn hình Preloader sau khi trang đã tải xong (nếu đang hiển thị)
    const preloader = document.getElementById('preloader');
    if (preloader && !preloader.classList.contains('preloader--hidden')) {
        setTimeout(() => {
            preloader.classList.add('preloader--hidden');
            sessionStorage.setItem('first_load_done', 'true');
        }, 300); // Chỉ chạy độ trễ 300ms trong lần truy cập đầu tiên
    }

    // Lấy chỉ mục vị trí trang hiện tại
    const currentPage = getPageName(window.location.search || window.location.href);
    const currentIndex = PAGE_INDICES[currentPage] !== undefined ? PAGE_INDICES[currentPage] : 0;

    // 2. Chặn các liên kết nội bộ để tạo hiệu ứng biến mất trước khi chuyển hướng (Snappy Exit Transition)
    const links = document.querySelectorAll('a');
    links.forEach(link => {
        const href = link.getAttribute('href');
        const target = link.getAttribute('target');
        
        // Bỏ qua các đường dẫn neo, javascript, tab mới
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || target === '_blank') {
            return;
        }

        // Chỉ chặn các đường dẫn nội bộ
        const isLocal = href.includes('index.php') || href.startsWith('./') || !href.includes('://');
        if (isLocal) {
            link.addEventListener('click', (e) => {
                // Vẫn cho phép mở tab mới bằng Ctrl/Cmd Click
                if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) {
                    return;
                }
                e.preventDefault();
                
                // Lấy chỉ mục vị trí trang đích
                const targetPage = getPageName(href);
                const targetIndex = PAGE_INDICES[targetPage];

                // Lưu lại vị trí tọa độ của nút Menu đang chọn làm mốc xuất phát trượt cho trang sau
                const currentActiveLink = document.querySelector('.site-nav__links .nav-link--active');
                if (currentActiveLink) {
                    sessionStorage.setItem('prev_nav_left', currentActiveLink.offsetLeft);
                    sessionStorage.setItem('prev_nav_width', currentActiveLink.offsetWidth);
                }

                // CHỈ áp dụng hiệu ứng trượt trái/phải nếu CẢ trang hiện tại và trang đích đều thuộc Menu chính nằm ngang có gạch dưới (Chỉ số 0 đến 5)
                if (targetPage === 'chitietsanpham') {
                    // Dùng hiệu ứng mờ và thu nhỏ nhẹ nhàng chuyên biệt khi vào xem chi tiết sản phẩm
                    document.body.classList.add('exit-to-product');
                } else if (targetIndex !== undefined && targetIndex <= 5 && currentIndex <= 5) {
                    if (targetIndex > currentIndex) {
                        // Di chuyển qua phải -> Ghi nhận và trượt giao diện qua trái nhanh
                        sessionStorage.setItem('nav_direction', 'to-left');
                        document.body.classList.add('exit-to-left');
                    } else if (targetIndex < currentIndex) {
                        // Di chuyển qua trái -> Ghi nhận và trượt giao diện qua phải nhanh
                        sessionStorage.setItem('nav_direction', 'to-right');
                        document.body.classList.add('exit-to-right');
                    } else {
                        // Cùng vị trí -> Mờ dần mặc định nhanh
                        document.body.classList.add('exit-default');
                    }
                } else {
                    // Đối với Giỏ hàng, Tài khoản, Đăng nhập, Đăng xuất (Không có gạch dưới) -> Dùng hiệu ứng mờ dần & trượt dọc sang trọng mặc định
                    document.body.classList.add('exit-default');
                }
                
                // Thực hiện điều hướng sau khi hiệu ứng cực kỳ nhanh kết thúc (120ms)
                setTimeout(() => {
                    window.location.href = href;
                }, 120);
            });
        }
    });

    // 3. Xử lý đường gạch chân đỏ chạy theo Menu chính một cách mượt mà bằng kỹ thuật FLIP (GPU-Accelerated Transition)
    const activeLink = document.querySelector('.site-nav__links .nav-link--active');
    const indicator = document.querySelector('.nav-indicator');
    if (indicator && activeLink) {
        // Luôn đặt vị trí mặc định tại mục hiện tại trước
        indicator.style.left = activeLink.offsetLeft + 'px';
        indicator.style.width = activeLink.offsetWidth + 'px';

        const prevLeft = sessionStorage.getItem('prev_nav_left');
        const prevWidth = sessionStorage.getItem('prev_nav_width');
        
        if (prevLeft && prevWidth) {
            const activeLeft = activeLink.offsetLeft;
            const activeWidth = activeLink.offsetWidth;
            
            // Tính toán khoảng cách (delta) và tỷ lệ co giãn (scale) từ vị trí trang trước đó
            const deltaX = parseFloat(prevLeft) - activeLeft;
            const scaleX = parseFloat(prevWidth) / activeWidth;
            
            // Đặt điểm gốc của biến đổi là góc bên trái (để scaleX giãn đúng chiều ngang)
            indicator.style.transformOrigin = 'left center';
            
            // Khóa hoạt họa tạm thời để dịch chuyển tức thời thanh kẻ đỏ về vị trí trang trước đó
            indicator.style.transition = 'none';
            indicator.style.transform = `translateX(${deltaX}px) scaleX(${scaleX})`;
            
            // Kích hoạt ép buộc trình duyệt Render lại khung hình xuất phát (Force Reflow)
            indicator.offsetHeight;
            
            // Áp dụng transition mượt mà qua GPU (sử dụng bezier nhanh và mượt hơn)
            indicator.style.transition = 'transform 260ms cubic-bezier(0.16, 1, 0.3, 1)';
            indicator.style.transform = 'translateX(0) scaleX(1)';
        } else {
            indicator.style.transform = 'none';
        }
        
        // Dọn dẹp bộ nhớ đệm
        sessionStorage.removeItem('prev_nav_left');
        sessionStorage.removeItem('prev_nav_width');
    }

    // Tự động tính toán lại vị trí khi người dùng thu phóng/thay đổi kích thước trình duyệt
    window.addEventListener('resize', () => {
        if (indicator && activeLink) {
            indicator.style.transition = 'none';
            indicator.style.transform = 'none';
            indicator.style.left = activeLink.offsetLeft + 'px';
            indicator.style.width = activeLink.offsetWidth + 'px';
        }
    });

    // 4. Xử lý Mobile Menu (Hamburger Drawer)
    const menuBtn = document.querySelector('.nav-menu-btn');
    const mobileDrawer = document.getElementById('mobileDrawer');
    const drawerOverlay = document.getElementById('drawerOverlay');
    const closeDrawerBtn = document.getElementById('closeDrawerBtn');

    if (menuBtn && mobileDrawer && drawerOverlay && closeDrawerBtn) {
        const toggleDrawer = () => {
            mobileDrawer.classList.toggle('mobile-drawer--open');
            drawerOverlay.classList.toggle('drawer-overlay--open');
            document.body.style.overflow = mobileDrawer.classList.contains('mobile-drawer--open') ? 'hidden' : '';
        };

        menuBtn.addEventListener('click', toggleDrawer);
        closeDrawerBtn.addEventListener('click', toggleDrawer);
        drawerOverlay.addEventListener('click', toggleDrawer);
    }
});
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
            <a class="nav-link <?= $activeNav === 'phukien' ? 'nav-link--active' : '' ?>" href="index.php?page=phukien">Phụ kiện</a>
            <a class="nav-link <?= $activeNav === 'baiviet' ? 'nav-link--active' : '' ?>" href="index.php?page=baiviet">Bài viết</a>
            <a class="nav-link <?= $activeNav === 'lienhe' ? 'nav-link--active' : '' ?>" href="index.php?page=lienhe">Liên hệ</a>
            <div class="nav-indicator"></div>
        </div>

        <!-- Actions — visibility controlled by PHP session (server-side) -->
        <div class="site-nav__actions">
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

            <?php if ($_nav_is_admin): ?>
            <a class="auth-admin-btn nav-link" href="index.php?page=admin">Quản trị</a>
            <?php endif; ?>

            <button class="nav-menu-btn" aria-label="menu">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>

    </div>
</nav>

<!-- Mobile Drawer Overlay -->
<div class="drawer-overlay" id="drawerOverlay"></div>

<!-- Mobile Drawer Menu -->
<div class="mobile-drawer" id="mobileDrawer">
    <div class="mobile-drawer__header">
        <span class="mobile-drawer__brand">LENS &amp; LIGHT</span>
        <button class="mobile-drawer__close" id="closeDrawerBtn" aria-label="Đóng menu">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    
    <div class="mobile-drawer__content">
        <a class="drawer-link <?= $activeNav === 'trangchu' ? 'drawer-link--active' : '' ?>" href="index.php?page=trangchu">Trang chủ</a>
        <a class="drawer-link <?= $activeNav === 'mayanh'  ? 'drawer-link--active' : '' ?>" href="index.php?page=mayanh">Máy ảnh</a>
        <a class="drawer-link <?= $activeNav === 'ongkinh' ? 'drawer-link--active' : '' ?>" href="index.php?page=ongkinh">Ống kính</a>
        <a class="drawer-link <?= $activeNav === 'phukien' ? 'drawer-link--active' : '' ?>" href="index.php?page=phukien">Phụ kiện</a>
        <a class="drawer-link <?= $activeNav === 'baiviet' ? 'drawer-link--active' : '' ?>" href="index.php?page=baiviet">Bài viết</a>
        <a class="drawer-link <?= $activeNav === 'lienhe' ? 'drawer-link--active' : '' ?>" href="index.php?page=lienhe">Liên hệ</a>
    </div>

    <div class="mobile-drawer__footer">
        <?php if ($_nav_logged_in): ?>
            <div class="drawer-user-info">
                <span class="material-symbols-outlined">account_circle</span>
                <span><?= $_nav_fullname ?></span>
            </div>
            <a class="drawer-btn drawer-btn--outline" href="index.php?page=taikhoan">Quản lý tài khoản</a>
            <?php if ($_nav_is_admin): ?>
                <a class="drawer-btn drawer-btn--primary" href="index.php?page=admin">Vào trang Quản trị</a>
            <?php endif; ?>
            <a class="drawer-btn drawer-btn--danger" href="index.php?action=client_logout">Đăng xuất</a>
        <?php else: ?>
            <a class="drawer-btn drawer-btn--primary" href="index.php?page=login">Đăng nhập / Đăng ký</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($_nav_logged_in && (empty($_SESSION['client_phone']) || empty($_SESSION['client_address'])) && $activeNav !== 'taikhoan'): ?>
<div id="missingInfoPopup" class="modal-overlay" style="position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 10000; opacity: 1; pointer-events: auto;">
    <div class="modal-content" style="background: var(--surface); padding: 2rem; border-radius: 8px; width: 90%; max-width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); text-align: center;">
        <span class="material-symbols-outlined" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem; display: block;">info</span>
        <h2 style="margin-top: 0; margin-bottom: 1rem;">Cập nhật thông tin giao hàng</h2>
        <p style="margin-bottom: 1.5rem; color: var(--on-surface-variant); font-size: 0.95rem;">
            Chào <b><?= $_nav_fullname ?></b>, bạn chưa cập nhật đầy đủ số điện thoại hoặc địa chỉ giao hàng. Vui lòng cập nhật để trải nghiệm thanh toán nhanh chóng hơn!
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <button type="button" onclick="document.getElementById('missingInfoPopup').style.display='none'" style="padding: 0.5rem 1.5rem; background: transparent; border: 1px solid var(--outline); color: var(--on-surface); cursor: pointer; border-radius: 4px;">Bỏ qua</button>
            <a href="index.php?page=taikhoan" style="padding: 0.5rem 1.5rem; background: var(--primary); border: none; color: var(--on-primary); cursor: pointer; border-radius: 4px; font-weight: 500; text-decoration: none;">Cập nhật ngay</a>
        </div>
    </div>
</div>
<?php endif; ?>
