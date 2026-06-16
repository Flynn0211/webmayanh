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
    <link href="assets/css/client.css?v=1.3" rel="stylesheet"/>
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
                    <span id="cartShippingFee" class="text-mono-spec" style="font-weight: 500; color: var(--primary);">CHƯA TÍNH</span>
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

                <div class="cart-summary__form-group" style="margin-top: 1rem;">
                    <label for="customerPhone" style="font-size: 0.85rem; font-weight: 500; display: block; margin-bottom: 0.5rem; letter-spacing: 0.5px;">SỐ ĐIỆN THOẠI GIAO HÀNG *</label>
                    <input type="text" id="customerPhone" placeholder="Nhập số điện thoại..." style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; font-size: 0.95rem; background: rgba(255,255,255,0.05); color: inherit;" value="<?= htmlspecialchars($_SESSION['client_phone'] ?? '') ?>" required>
                </div>

                <div class="cart-summary__form-group" style="margin-top: 1rem;">
                    <label style="font-size: 0.85rem; font-weight: 500; display: block; margin-bottom: 0.5rem; letter-spacing: 0.5px;">ĐỊA CHỈ GIAO HÀNG *</label>
                    
                    <select id="provinceSelect" style="width: 100%; padding: 0.75rem; margin-bottom: 0.5rem; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; font-size: 0.95rem; background: rgba(255,255,255,0.05); color: inherit;" required>
                        <option value="" disabled selected>Chọn Tỉnh / Thành phố</option>
                    </select>
                    
                    <select id="districtSelect" style="width: 100%; padding: 0.75rem; margin-bottom: 0.5rem; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; font-size: 0.95rem; background: rgba(255,255,255,0.05); color: inherit;" required disabled>
                        <option value="" disabled selected>Chọn Quận / Huyện</option>
                    </select>
                    
                    <select id="wardSelect" style="width: 100%; padding: 0.75rem; margin-bottom: 0.5rem; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; font-size: 0.95rem; background: rgba(255,255,255,0.05); color: inherit;" required disabled>
                        <option value="" disabled selected>Chọn Phường / Xã</option>
                    </select>
                    
                    <input type="text" id="addressDetail" placeholder="Số nhà, tên đường..." style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; font-size: 0.95rem; background: rgba(255,255,255,0.05); color: inherit;" required>
                </div>

                <div class="cart-summary__form-group" style="margin-top: 1rem;">
                    <label style="font-size: 0.85rem; font-weight: 500; display: block; margin-bottom: 0.5rem; letter-spacing: 0.5px;">PHƯƠNG THỨC THANH TOÁN *</label>
                    
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.95rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="radio" name="paymentMethod" value="COD" checked>
                            <span>Thanh toán khi nhận hàng (COD)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="radio" name="paymentMethod" value="BankTransfer">
                            <span>Chuyển khoản qua Ngân hàng</span>
                        </label>
                    </div>
                </div>

                <div class="cart-summary__form-group" style="margin-top: 1rem; margin-bottom: 1.5rem;">
                    <label for="voucherCode" style="font-size: 0.85rem; font-weight: 500; display: block; margin-bottom: 0.5rem; letter-spacing: 0.5px;">MÃ GIẢM GIÁ (NẾU CÓ)</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="voucherCode" placeholder="Nhập mã voucher..." style="flex: 1; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; font-size: 0.95rem; background: rgba(255,255,255,0.05); color: inherit; text-transform: uppercase;">
                        <button type="button" id="btnApplyVoucher" style="padding: 0 1rem; border: none; border-radius: 4px; background: var(--primary); color: white; cursor: pointer; font-weight: 600;">ÁP DỤNG</button>
                    </div>
                    <div id="voucherMessage" style="margin-top: 0.5rem; font-size: 0.85rem;"></div>
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

<?php include 'view/client/layout/_footer_dark.php'; ?>

<script>
window.clientAddressStr = <?= isset($_SESSION['client_address']) && $_SESSION['client_address'] ? json_encode($_SESSION['client_address']) : 'null' ?>;
</script>
<script src="assets/js/auth.js?v=2.0"></script>
<script src="assets/js/giohang.js"></script>
</body>
</html>

