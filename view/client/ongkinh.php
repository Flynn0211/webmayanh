<?php $activeNav = 'ongkinh'; ?>
<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>LENS & LIGHT - Danh mục Ống Kính</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="assets/js/tailwind.config.js"></script>
    <link href="assets/css/base.css" rel="stylesheet" />
    <link href="assets/css/client.css" rel="stylesheet" />
    <link href="assets/css/responsive.css" rel="stylesheet" />
</head>
<body class="bg-surface text-on-surface font-body-md antialiased selection:bg-primary selection:text-on-primary">

<?php include 'view/client/layout/_navbar.php'; ?>


<main>
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header__inner">
            <h1 class="page-header__title">BỘ SƯU TẬP ỐNG KÍNH</h1>
            <p class="page-header__desc">Định nghĩa lại độ sắc nét và màu sắc với những ống kính đỉnh cao thế giới.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-bar__inner">
            <!-- Brand Filters -->
            <div class="brand-filters" id="brandFilterContainer">
                <button class="brand-btn brand-btn--active" data-brand="all">Tất cả</button>
                <button class="brand-btn" data-brand="sony">Sony</button>
                <button class="brand-btn" data-brand="canon">Canon</button>
                <button class="brand-btn" data-brand="nikon">Nikon</button>
                <button class="brand-btn" data-brand="fujifilm">Fujifilm</button>
                <button class="brand-btn" data-brand="leica">Leica</button>
                <button class="brand-btn" data-brand="hasselblad">Hasselblad</button>
            </div>

            <div class="filter-controls">
                <!-- Search -->
                <div class="filter-search">
                    <span class="material-symbols-outlined filter-search__icon">search</span>
                    <input type="text" id="searchInput" placeholder="Tìm tên ống kính..." class="filter-search__input"/>
                </div>
                <!-- Sort -->
                <select id="sortSelect" class="filter-sort">
                    <option value="default">Mặc định</option>
                    <option value="price_asc">Giá: Thấp - Cao</option>
                    <option value="price_desc">Giá: Cao - Thấp</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="catalog-grid">
        <div id="productGrid" class="catalog-grid__inner">
            <p class="no-products-msg hidden" id="noProductsMsg">Không tìm thấy sản phẩm nào phù hợp.</p>
        </div>
    </div>
</main>

<?php include 'view/client/layout/_footer_dark.php'; ?>

<script src="assets/js/auth.js?v=2.0"></script>
<script src="assets/js/ongkinh.js"></script>
</body>
</html>


