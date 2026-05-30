<?php
ob_start();
require_once __DIR__ . '/../../model/database.php';
require_once __DIR__ . '/../../control/ArticleController.php';

ArticleController::handleAdminAction();
if ($conn === false) {
    die("Kết nối cơ sở dữ liệu thất bại.");
}

require_once __DIR__ . '/../../control/AdminController.php';

// Route AJAX calls to AdminController
AdminController::handleAjaxAction();

// ── Load database collections ──────────────────────────────────
// 0. Categories (Seed if empty)
$res_check = $conn->query("SELECT COUNT(*) as cnt FROM danh_muc");
$row_check = $res_check->fetch_assoc();
if ($row_check['cnt'] == 0) {
    $conn->query("INSERT INTO danh_muc (ten_danh_muc, slug) VALUES ('Máy ảnh', 'may-anh'), ('Ống kính', 'ong-kinh'), ('Phụ kiện', 'phu-kien')");
}
$dbCategories = [];
$res_c = $conn->query("SELECT ma_dm as id, ten_danh_muc as name FROM danh_muc");
while ($row = $res_c->fetch_assoc()) {
    $dbCategories[] = $row;
}

// 1. Products
$dbProducts = [];
$res_p = $conn->query("SELECT h.ma_hh as id, h.ma_dm as category_id, h.ten_hang_hoa as name, h.anh as image, n.ten_ncc as brand, h.gia_hien_tai as price_val, IFNULL(SUM(t.so_luong_ton), 0) as stock, h.mo_ta as description, h.thong_so_ky_thuat as specs
FROM hang_hoa h
LEFT JOIN nha_cung_cap n ON h.ma_ncc = n.ma_ncc
LEFT JOIN ton_kho_chi_tiet t ON h.ma_hh = t.ma_hh
GROUP BY h.ma_hh
ORDER BY h.ma_hh DESC");
while ($row = $res_p->fetch_assoc()) {
    $row['price'] = number_format($row['price_val']) . ' ₫';
    $dbProducts[] = $row;
}

// 2. Vouchers
$dbVouchers = [];
$res_v = $conn->query("SELECT ma_code as code, loai_giam_gia, gia_tri_giam, so_luong, ngay_het_han as expire, trang_thai FROM voucher ORDER BY ma_voucher DESC");
while ($row = $res_v->fetch_assoc()) {
    $discount_symbol = ($row['loai_giam_gia'] === 'PhanTram') ? '%' : ' ₫';
    if ($row['loai_giam_gia'] === 'PhanTram') {
        $row['discount'] = intval($row['gia_tri_giam']) . $discount_symbol;
    } else {
        $row['discount'] = number_format($row['gia_tri_giam']) . $discount_symbol;
    }
    $row['expire'] = date('d/m/Y', strtotime($row['expire']));
    $dbVouchers[] = $row;
}

// 3. Orders
$dbOrders = [];
$res_o = $conn->query("SELECT d.ma_dh as id, d.ten_nguoi_nhan as customerName, t.username as customerUsername, d.tong_thanh_toan as total_val, d.ngay_dat as date_val, d.trang_thai_don as status
FROM don_hang d
LEFT JOIN tai_khoan t ON d.ma_khach_hang = t.ma_tk
ORDER BY d.ma_dh DESC");
while ($row = $res_o->fetch_assoc()) {
    $row['total'] = number_format($row['total_val']) . ' ₫';
    $row['date'] = date('d/m/Y', strtotime($row['date_val']));
    $row['items'] = [];
    
    // Fetch items for this order
    $stmt_item = $conn->prepare("SELECT c.ma_hh as id, h.ten_hang_hoa as name, h.anh as image, c.so_luong as quantity, c.gia_luc_mua as price_val, n.ten_ncc as brand FROM chi_tiet_don_hang c JOIN hang_hoa h ON c.ma_hh = h.ma_hh LEFT JOIN nha_cung_cap n ON h.ma_ncc = n.ma_ncc WHERE c.ma_dh = ?");
    $stmt_item->bind_param("i", $row['id']);
    $stmt_item->execute();
    $res_item = $stmt_item->get_result();
    while ($i = $res_item->fetch_assoc()) {
        $i['price'] = number_format($i['price_val']) . ' ₫';
        $row['items'][] = $i;
    }
    
    $dbOrders[] = $row;
}

// 4. Customers and Employees
$dbCustomers = [];
$dbEmployees = [];
$res_u = $conn->query("SELECT ma_tk as id, ho_ten as name, username, email, sdt as phone, loai_tk as role, hang_thanh_vien as tier, trang_thai as status FROM tai_khoan");
while ($row = $res_u->fetch_assoc()) {
    $row['active'] = ($row['status'] === 'HoatDong');
    if ($row['role'] === 'User') {
        $dbCustomers[] = $row;
    } else {
        $dbEmployees[] = $row;
    }
}

// 4. BI Stats Calculations
$week_rev = 0;
$res_w = $conn->query("SELECT SUM(c.so_luong * c.gia_luc_mua) as rev FROM chi_tiet_don_hang c JOIN don_hang d ON c.ma_dh = d.ma_dh WHERE d.trang_thai_don != 'Đã hủy' AND d.ngay_dat >= DATE_SUB(NOW(), INTERVAL 1 WEEK)");
if ($row = $res_w->fetch_assoc()) { $week_rev = (float)$row['rev']; }

$month_rev = 0;
$res_m = $conn->query("SELECT SUM(c.so_luong * c.gia_luc_mua) as rev FROM chi_tiet_don_hang c JOIN don_hang d ON c.ma_dh = d.ma_dh WHERE d.trang_thai_don != 'Đã hủy' AND d.ngay_dat >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
if ($row = $res_m->fetch_assoc()) { $month_rev = (float)$row['rev']; }

$year_rev = 0;
$res_yr = $conn->query("SELECT SUM(c.so_luong * c.gia_luc_mua) as rev FROM chi_tiet_don_hang c JOIN don_hang d ON c.ma_dh = d.ma_dh WHERE d.trang_thai_don != 'Đã hủy' AND d.ngay_dat >= DATE_SUB(NOW(), INTERVAL 1 YEAR)");
if ($row = $res_yr->fetch_assoc()) { $year_rev = (float)$row['rev']; }

$total_orders = 0;
$res_to = $conn->query("SELECT COUNT(*) as cnt FROM don_hang WHERE trang_thai_don != 'Đã hủy'");
if ($row = $res_to->fetch_assoc()) { $total_orders = (int)$row['cnt']; }

$products_sold = 0;
$res_ps = $conn->query("SELECT SUM(so_luong) as cnt FROM chi_tiet_don_hang c JOIN don_hang d ON c.ma_dh = d.ma_dh WHERE d.trang_thai_don != 'Đã hủy'");
if ($row = $res_ps->fetch_assoc()) { $products_sold = (int)$row['cnt']; }

$new_customers = 0;
$res_nc = $conn->query("SELECT COUNT(*) as cnt FROM tai_khoan WHERE loai_tk = 'User' AND ngay_tao >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
if ($row = $res_nc->fetch_assoc()) { $new_customers = (int)$row['cnt']; }

// 5. Chart Data (Past 12 Months)
$chart_data = [];
$res_ch = $conn->query("SELECT MONTH(ngay_dat) as m, YEAR(ngay_dat) as y, SUM(so_luong * gia_luc_mua) as revenue FROM chi_tiet_don_hang c JOIN don_hang d ON c.ma_dh = d.ma_dh WHERE d.trang_thai_don != 'Đã hủy' AND ngay_dat >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY YEAR(ngay_dat), MONTH(ngay_dat) ORDER BY y, m");
while ($row = $res_ch->fetch_assoc()) {
    $chart_data[] = [
        'label' => 'T' . $row['m'],
        'revenue' => (float)$row['revenue'] / 1000000.0 // Unit: Million VND
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <?php
    $base_url = (basename(dirname($_SERVER['PHP_SELF'])) === 'admin') ? '../' : './';
    ?>
    <base href="<?php echo $base_url; ?>"/>
    <title>Admin - LENS &amp; LIGHT</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="assets/css/base.css" rel="stylesheet"/>
    <link href="assets/css/admin.css" rel="stylesheet"/>
    <link href="assets/css/responsive.css" rel="stylesheet"/>
</head>
<body class="admin-body">

    <!-- ── Sidebar ─────────────────────────────────────────── -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar__logo">
            <a href="index.php?page=trangchu" class="admin-sidebar__logo-link">
                <span class="material-symbols-outlined">camera</span>
                LENS &amp; LIGHT
            </a>
        </div>

        <nav class="admin-sidebar__nav">
            <span class="admin-sidebar__group-label">Báo cáo</span>
            <button onclick="switchTab('revenue')" id="menu-revenue" class="menu-item">
                <span class="material-symbols-outlined">bar_chart</span>
                <span class="truncate">Quản lý doanh thu</span>
            </button>

            <span class="admin-sidebar__group-label">Nghiệp vụ</span>
            <button onclick="switchTab('orders')" id="menu-orders" class="menu-item">
                <span class="material-symbols-outlined">shopping_cart</span>
                <span class="truncate">Quản lý đơn hàng</span>
                <span class="menu-badge" id="newOrdersBadge"><?php echo count($dbOrders); ?></span>
            </button>
            <button onclick="switchTab('products')" id="menu-products" class="menu-item active-menu">
                <span class="material-symbols-outlined">inventory_2</span>
                <span class="truncate">Quản lý sản phẩm</span>
            </button>
            <button onclick="switchTab('promotions')" id="menu-promotions" class="menu-item">
                <span class="material-symbols-outlined">sell</span>
                <span class="truncate">Khuyến mãi sản phẩm</span>
            </button>
            <button onclick="switchTab('vouchers')" id="menu-vouchers" class="menu-item">
                <span class="material-symbols-outlined">confirmation_number</span>
                <span class="truncate">Quản lý voucher</span>
            </button>

            <span class="admin-sidebar__group-label">Nội dung</span>
            <button onclick="switchTab('articles')" id="menu-articles" class="menu-item">
                <span class="material-symbols-outlined">article</span>
                <span class="truncate">Quản lý Bài viết</span>
            </button>

            <span class="admin-sidebar__group-label">Người dùng</span>
            <button onclick="switchTab('customers')" id="menu-customers" class="menu-item">
                <span class="material-symbols-outlined">groups</span>
                <span class="truncate">Quản lý khách hàng</span>
            </button>
            <button onclick="switchTab('employees')" id="menu-employees" class="menu-item">
                <span class="material-symbols-outlined">badge</span>
                <span class="truncate">Quản lý nhân viên</span>
            </button>
            <button onclick="switchTab('reviews')" id="menu-reviews" class="menu-item">
                <span class="material-symbols-outlined">reviews</span>
                <span class="truncate">Quản lý đánh giá</span>
            </button>
        </nav>

        <div class="admin-sidebar__footer">
            <div class="admin-sidebar__user" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div class="admin-sidebar__avatar">
                        <img src="https://i.pravatar.cc/150?u=admin" alt="Admin"/>
                    </div>
                    <div>
                        <p class="admin-sidebar__user-name"><?php echo isset($_SESSION['admin_fullname']) ? htmlspecialchars($_SESSION['admin_fullname']) : 'Admin Manager'; ?></p>
                        <p class="admin-sidebar__user-role">Quản trị viên</p>
                    </div>
                </div>
                <a href="admin/index.php?action=logout" class="btn-table-action btn-table-action--delete" title="Đăng xuất" style="display: flex; align-items: center; justify-content: center; padding: 0.25rem;">
                    <span class="material-symbols-outlined" style="font-size: 1.5rem;">logout</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- ── Main ────────────────────────────────────────────── -->
    <main class="admin-main">
        <!-- Header -->
        <header class="admin-header">
            <h1 id="pageTitle" class="admin-header__title">Quản lý sản phẩm</h1>
            <div class="admin-header__actions">
                <div class="admin-search">
                    <span class="material-symbols-outlined admin-search__icon">search</span>
                    <input type="text" placeholder="Tìm kiếm..." class="admin-search__input"/>
                </div>
                <button class="admin-notif-btn">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="admin-notif-btn__dot"></span>
                </button>
            </div>
        </header>

        <div class="admin-content">
            <div class="admin-content__inner">

                <!-- ── REVENUE TAB ──────────────────────────── -->
                <div id="tab-revenue" class="admin-tab">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <span class="stat-card__label">DOANH THU TUẦN</span>
                            <span class="stat-card__value"><?php echo number_format($week_rev); ?> ₫</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-card__label">DOANH THU THÁNG</span>
                            <span class="stat-card__value"><?php echo number_format($month_rev); ?> ₫</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-card__label">DOANH THU NĂM</span>
                            <span class="stat-card__value"><?php echo number_format($year_rev); ?> ₫</span>
                        </div>
                    </div>
                    <div class="revenue-charts">
                        <div class="revenue-chart-card">
                            <h3 class="revenue-chart-card__title revenue-chart-card__title--up">
                                <span class="material-symbols-outlined">trending_up</span> Bán Chạy Nhất
                            </h3>
                            <ul class="seller-list" id="bestSellersList"></ul>
                        </div>
                        <div class="revenue-chart-card">
                            <h3 class="revenue-chart-card__title revenue-chart-card__title--down">
                                <span class="material-symbols-outlined">trending_down</span> Bán Ế Nhất
                            </h3>
                            <ul class="seller-list" id="worstSellersList"></ul>
                        </div>
                    </div>
                </div>

                <!-- ── ORDERS TAB ───────────────────────────── -->
                <div id="tab-orders" class="admin-tab">
                    <div class="admin-card">
                        <div class="table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Mã ĐH</th>
                                        <th>Khách hàng</th>
                                        <th>Tổng tiền</th>
                                        <th>Ngày đặt</th>
                                        <th class="center">Trạng thái</th>
                                        <th class="center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="adminOrderTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ── PRODUCTS TAB ─────────────────────────── -->
                <div id="tab-products" class="admin-tab active">
                    <div class="admin-card">
                        <div class="admin-section-header">
                            <h2 class="admin-section-title">Sản phẩm</h2>
                            <button onclick="openProductModal()" class="btn-admin-add">
                                <span class="material-symbols-outlined">add</span> Thêm
                            </button>
                        </div>
                        <div class="table-wrap">
                            <table class="admin-table" style="min-width:800px;">
                                <thead>
                                    <tr>
                                        <th class="center">ID</th>
                                        <th>Hình ảnh</th>
                                        <th>Thương hiệu</th>
                                        <th>Tên sản phẩm</th>
                                        <th class="right">Giá tiền</th>
                                        <th class="center">Tồn kho</th>
                                        <th class="center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="adminProductTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ── PROMOTIONS TAB ───────────────────────── -->
                <div id="tab-promotions" class="admin-tab">
                    <div class="admin-card">
                        <div class="admin-section-header">
                            <h2 class="admin-section-title">Khuyến mãi</h2>
                            <button class="btn-admin-add"><span class="material-symbols-outlined">add</span> Thêm</button>
                        </div>
                        <div class="table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Mức giảm (%)</th>
                                        <th class="center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="adminPromoTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ── VOUCHERS TAB ─────────────────────────── -->
                <div id="tab-vouchers" class="admin-tab">
                    <div class="admin-card">
                        <div class="admin-section-header">
                            <h2 class="admin-section-title">Voucher</h2>
                            <button onclick="addVoucherPrompt()" class="btn-admin-add"><span class="material-symbols-outlined">add</span> Thêm</button>
                        </div>
                        <div class="table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Mã Voucher</th>
                                        <th>Mức giảm</th>
                                        <th>Số lượng</th>
                                        <th>Thời hạn</th>
                                        <th class="center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="adminVoucherTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ── ARTICLES TAB ─────────────────────────── -->
                <div id="tab-articles" class="admin-tab">
                    <div class="admin-card">
                        <div class="admin-section-header">
                            <h2 class="admin-section-title">Danh sách bài viết</h2>
                            <button onclick="openArticleModal()" class="btn-admin-add"><span class="material-symbols-outlined">add</span> Đăng bài mới</button>
                        </div>
                        <div class="table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Hình ảnh</th>
                                        <th>Mã BV</th>
                                        <th>Tiêu đề</th>
                                        <th>Ngày đăng</th>
                                        <th>Trạng thái</th>
                                        <th class="center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="adminArticleTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ── CUSTOMERS TAB ────────────────────────── -->
                <div id="tab-customers" class="admin-tab">
                    <div class="admin-card">
                        <div class="table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Khách hàng</th>
                                        <th>Phân hạng</th>
                                        <th class="center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="adminCustomerTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ── EMPLOYEES TAB ────────────────────────── -->
                <div id="tab-employees" class="admin-tab">
                    <div class="admin-card">
                        <div class="admin-section-header">
                            <h2 class="admin-section-title">Nhân viên</h2>
                            <button class="btn-admin-add"><span class="material-symbols-outlined">add</span> Thêm</button>
                        </div>
                        <div class="table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên nhân viên</th>
                                        <th>Chức vụ</th>
                                        <th class="center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="adminEmployeeTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ── REVIEWS TAB ──────────────────────────── -->
                <div id="tab-reviews" class="admin-tab">
                    <div class="admin-card">
                        <div class="table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Người đánh giá</th>
                                        <th>Nội dung</th>
                                        <th class="center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="adminReviewTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- ── Article Modal ───────────────────────────────────── -->
    <div id="articleModal" class="admin-modal-overlay">
        <div class="admin-modal admin-modal--large" id="articleModalContent" style="max-width:800px;">
            <div class="admin-modal__header">
                <h3 id="articleModalTitle" class="admin-modal__title">Thêm Bài Viết</h3>
                <button onclick="closeArticleModal()" class="admin-modal__close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="admin-modal__body">
                <form id="articleForm" method="POST" action="index.php?page=admin" enctype="multipart/form-data">
                    <input type="hidden" name="article_action" id="articleAction" value="add"/>
                    <input type="hidden" name="id" id="articleId" value=""/>
                    <input type="hidden" name="old_image" id="articleOldImage" value=""/>

                    <div style="display:flex; flex-direction:column; gap:1rem;">
                        <div>
                            <label class="admin-label">Tiêu đề</label>
                            <input type="text" name="title" id="articleTitle" required class="admin-input" onkeyup="document.getElementById('articleSlug').value = this.value.toLowerCase().replace(/ /g,'-').replace(/[^\w-]+/g,'')"/>
                        </div>
                        <div>
                            <label class="admin-label">Đường dẫn (Slug)</label>
                            <input type="text" name="slug" id="articleSlug" required class="admin-input admin-input--mono"/>
                        </div>
                        <div>
                            <label class="admin-label">Ảnh bìa</label>
                            <input type="file" name="image" id="articleImageFile" accept="image/*" class="admin-input"/>
                            <div id="articleImagePreview" style="margin-top:0.5rem; max-width:200px;"></div>
                        </div>
                        <div>
                            <label class="admin-label">Tóm tắt</label>
                            <textarea name="summary" id="articleSummary" required class="admin-input" rows="3"></textarea>
                        </div>
                        <div>
                            <label class="admin-label">Nội dung HTML</label>
                            <textarea name="content" id="articleContent" required class="admin-input" rows="8"></textarea>
                        </div>
                        <div>
                            <label class="admin-label">Trạng thái</label>
                            <select name="status" id="articleStatus" required class="admin-input">
                                <option value="XuatBan">Xuất bản</option>
                                <option value="Nhao">Bản nháp</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="admin-modal__footer" style="margin-top:1.5rem; justify-content:flex-end; display:flex; gap:1rem;">
                        <button type="button" onclick="closeArticleModal()" class="btn-admin-cancel">Hủy</button>
                        <button type="submit" class="btn-admin-save">Lưu Bài Viết</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Article Form -->
    <form id="deleteArticleForm" method="POST" action="index.php?page=admin" style="display:none;">
        <input type="hidden" name="article_action" value="delete"/>
        <input type="hidden" name="id" id="deleteArticleId" value=""/>
    </form>

    <!-- ── Order Details Modal ──────────────────────────────── -->
    <div id="orderModal" class="admin-modal-overlay">
        <div class="admin-modal admin-modal--large" id="orderModalContent" style="max-width:700px;">
            <div class="admin-modal__header">
                <h3 id="orderModalTitle" class="admin-modal__title">Chi tiết Đơn Hàng</h3>
                <button onclick="closeOrderModal()" class="admin-modal__close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="admin-modal__body" id="orderModalBody" style="max-height: 60vh; overflow-y:auto; padding-bottom: 2rem;">
                <!-- Nội dung đơn hàng sẽ được render bằng JS -->
            </div>
            <div class="admin-modal__footer" style="padding-top:1rem; justify-content:flex-end; display:flex;">
                <button type="button" onclick="closeOrderModal()" class="btn-ghost" style="background-color: var(--error); color: white;">Đóng</button>
            </div>
        </div>
    </div>

    <!-- ── Product Modal ───────────────────────────────────── -->
    <div id="productModal" class="admin-modal-overlay">
        <div class="admin-modal" id="productModalContent">
            <div class="admin-modal__header">
                <h3 id="modalTitle" class="admin-modal__title">Thêm Sản Phẩm</h3>
                <button onclick="closeProductModal()" class="admin-modal__close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="admin-modal__body">
                <form id="productForm">
                    <input type="hidden" id="originalProductId" value=""/>

                    <div class="modal-form-grid">
                        <!-- Cột trái: Upload hình -->
                        <div class="modal-form-col">
                            <label class="admin-label">Hình ảnh sản phẩm</label>
                            <div class="img-upload-zone">
                                <div class="img-upload-placeholder" id="imagePlaceholder">
                                    <span class="material-symbols-outlined">add_photo_alternate</span>
                                    <span class="img-upload-placeholder__title">Nhấn để tải ảnh lên</span>
                                    <span class="img-upload-placeholder__hint">PNG, JPG, WEBP</span>
                                </div>
                                <div class="img-preview" id="imagePreview">
                                    <img src="" id="imagePreviewImg" class="img-preview__img"/>
                                    <div class="img-preview__overlay">
                                        <span class="material-symbols-outlined">edit</span>
                                    </div>
                                </div>
                                <input type="file" id="productImageFile" accept="image/*" class="img-upload-input" title="Chọn ảnh sản phẩm"/>
                            </div>
                            <input type="hidden" id="productImage" value=""/>
                        </div>

                        <!-- Cột phải: Thông tin -->
                        <div class="modal-form-col modal-form-col--center">
                            <div>
                                <label class="admin-label" for="productId">Mã sản phẩm (ID)</label>
                                <input type="text" id="productId" required placeholder="Ví dụ: SP001" class="admin-input admin-input--mono"/>
                            </div>
                            <div>
                                <label class="admin-label" for="productCategory">Danh mục</label>
                                <select id="productCategory" required class="admin-input">
                                    <?php foreach ($dbCategories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="admin-label" for="productBrand">Thương hiệu</label>
                                <input type="text" id="productBrand" required placeholder="Ví dụ: Sony, Canon..." class="admin-input"/>
                            </div>
                            <div>
                                <label class="admin-label" for="productName">Tên sản phẩm</label>
                                <input type="text" id="productName" required placeholder="Nhập tên sản phẩm..." class="admin-input"/>
                            </div>
                            <div>
                                <label class="admin-label" for="productPrice">Giá tiền (kèm ký hiệu ₫)</label>
                                <input type="text" id="productPrice" required placeholder="VD: 43,000,000 ₫" class="admin-input"/>
                            </div>
                            <div>
                                <label class="admin-label" for="productStock">Số lượng tồn kho</label>
                                <input type="number" id="productStock" required placeholder="VD: 10" min="0" value="10" class="admin-input admin-input--mono"/>
                            </div>
                        </div>
                    </div>

                    <div class="modal-form-full">
                        <div>
                            <label class="admin-label" for="productDescription">Mô tả sản phẩm</label>
                            <textarea id="productDescription" rows="4" placeholder="Nhập mô tả sản phẩm..." class="admin-textarea"></textarea>
                        </div>
                        <div>
                            <label class="admin-label" for="productSpecs">Thông số kỹ thuật</label>
                            <textarea id="productSpecs" rows="5" placeholder="Cảm biến: Full-Frame 60MP&#10;Kết nối: Wi-Fi, Bluetooth..." class="admin-textarea admin-textarea--mono"></textarea>
                        </div>
                    </div>

                    <div class="admin-modal__footer">
                        <button type="button" onclick="closeProductModal()" class="btn-ghost">Hủy</button>
                        <button type="submit" class="btn-save">
                            <span class="material-symbols-outlined">save</span> Lưu Sản Phẩm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script src="assets/js/auth.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const currentUser = getCurrentUser();
        if (!currentUser || currentUser.role !== 'admin') {
            alert('Bạn không có quyền truy cập trang Quản Trị!');
            window.location.href = 'index.php?page=trangchu';
        }
    });
</script>
<script>
    // Live DB Injections
    window.dbCategories = <?php echo json_encode($dbCategories); ?>;
    window.dbProducts = <?php echo json_encode($dbProducts); ?>;
    window.dbOrders = <?php echo json_encode($dbOrders); ?>;
    window.dbVouchers = <?php echo json_encode($dbVouchers); ?>;
    window.dbArticles = <?php echo json_encode(ArticleController::getAllArticles()); ?>;
    window.dbCustomers = <?php echo json_encode($dbCustomers); ?>;
    window.dbEmployees = <?php echo json_encode($dbEmployees); ?>;
    window.dbStats = {
        totalOrders: <?php echo $total_orders; ?>,
        productsSold: <?php echo $products_sold; ?>,
        newCustomers: <?php echo $new_customers; ?>
    };
    window.dbMonthlyRevenue = <?php echo json_encode($chart_data); ?>;
</script>
<script src="assets/js/admin.js?v=<?php echo time(); ?>"></script>
</body>
</html>


