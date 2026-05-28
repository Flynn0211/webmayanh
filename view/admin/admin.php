<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
                <span class="menu-badge" id="newOrdersBadge">0</span>
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
            <div class="admin-sidebar__user">
                <div class="admin-sidebar__avatar">
                    <img src="https://i.pravatar.cc/150?u=admin" alt="Admin"/>
                </div>
                <div>
                    <p class="admin-sidebar__user-name">Admin Manager</p>
                    <p class="admin-sidebar__user-role">Quản trị viên</p>
                </div>
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
                            <span class="stat-card__value">125,000,000 ₫</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-card__label">DOANH THU THÁNG</span>
                            <span class="stat-card__value">850,000,000 ₫</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-card__label">DOANH THU NĂM</span>
                            <span class="stat-card__value">5,420,000,000 ₫</span>
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
                            <button class="btn-admin-add"><span class="material-symbols-outlined">add</span> Thêm</button>
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
<script src="assets/js/admin.js"></script>
</body>
</html>


