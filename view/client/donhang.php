<?php $activeNav = 'donhang'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>LENS &amp; LIGHT - Đơn hàng của tôi</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="assets/css/base.css" rel="stylesheet"/>
    <link href="assets/css/client.css" rel="stylesheet"/>
    <link href="assets/css/responsive.css" rel="stylesheet"/>
</head>
<body style="min-height:100vh;display:flex;flex-direction:column;">

<?php include 'view/client/layout/_navbar.php'; ?>

<main class="section-wrap" style="flex:1;padding-top:3rem;padding-bottom:3rem;">
    <h1 class="order-page__title">Đơn hàng của tôi</h1>

    <div class="tab-panel">
        <!-- Tabs -->
        <div class="tab-bar">
            <button id="tabOrders"    class="tab-btn tab-btn--active">Đơn Hàng Của Tôi</button>
            <button id="tabFavorites" class="tab-btn">Sản Phẩm Yêu Thích</button>
        </div>

        <!-- Đơn Hàng -->
        <div id="ordersContainer" class="block"></div>

        <!-- Yêu Thích -->
        <div id="favoritesContainer" class="hidden"></div>
    </div>
</main>

<?php include 'view/client/layout/_footer_light.php'; ?>

<script src="assets/js/auth.js?v=2.0"></script>
<script src="assets/js/donhang.js"></script>
</body>
</html>

