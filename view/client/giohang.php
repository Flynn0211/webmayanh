<?php $activeNav = 'giohang'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>LENS &amp; LIGHT - Giỏ Hàng</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="assets/css/base.css" rel="stylesheet"/>
    <link href="assets/css/client.css" rel="stylesheet"/>
    <link href="assets/css/responsive.css" rel="stylesheet"/>
</head>
<body style="min-height:100vh;display:flex;flex-direction:column;">

<?php include 'view/client/layout/_navbar.php'; ?>

<main class="cart-page">
    <h1 class="cart-page__title">GIỎ HÀNG CỦA BẠN</h1>

    <div class="cart-layout">
        <!-- Left: items -->
        <div class="cart-items">
            <div id="cartItemsContainer">
                <div class="cart-empty">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <p>Đang tải giỏ hàng...</p>
                </div>
            </div>
            <a href="index.php?page=trangchu" class="cart-continue">
                <span class="material-symbols-outlined" style="font-size:1rem;">arrow_back</span>
                TIẾP TỤC MUA SẮM
            </a>
        </div>

        <!-- Right: summary -->
        <div class="cart-summary">
            <div class="cart-summary__inner">
                <h2 class="cart-summary__title">TÓM TẮT ĐƠN HÀNG</h2>

                <div class="cart-summary__row">
                    <span class="cart-summary__row-label">TỔNG PHỤ</span>
                    <span id="cartSubtotal" class="text-mono-spec">0 ₫</span>
                </div>
                <div class="cart-summary__row">
                    <span class="cart-summary__row-label">GIAO HÀNG</span>
                    <span class="cart-summary__row-val cart-summary__row-val--free text-mono-spec">MIỄN PHÍ</span>
                </div>
                <div class="cart-summary__row">
                    <span class="cart-summary__row-label">THUẾ (EST.)</span>
                    <span class="text-mono-spec">0 ₫</span>
                </div>

                <hr class="cart-summary__divider"/>

                <div class="cart-summary__total-row">
                    <span class="cart-summary__total-label">TỔNG CỘNG</span>
                    <span id="cartTotal" class="cart-summary__total-val text-mono-spec">0 ₫</span>
                </div>

                <button id="btnCheckout" class="btn-checkout">TIẾN HÀNH THANH TOÁN</button>

                <p class="cart-secure-note">THANH TOÁN AN TOÀN VỚI SSL 256-BIT</p>

                <div class="cart-payment-icons">
                    <span class="material-symbols-outlined">payments</span>
                    <span class="material-symbols-outlined">credit_card</span>
                    <span class="material-symbols-outlined">account_balance</span>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'view/client/layout/_footer_light.php'; ?>

<script src="assets/js/auth.js"></script>
<script src="assets/js/giohang.js"></script>
</body>
</html>

