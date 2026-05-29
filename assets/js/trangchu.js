// File: js/trangchu.js

document.addEventListener("DOMContentLoaded", function() {
    let liveProducts = window.dbProducts || [];

    const products    = liveProducts.filter(p => p.category === 'camera');
    const productGrid = document.getElementById('productGrid');

    function getBrandClass(brand) {
        if (!brand) return 'product-card__brand--default';
        const b = brand.toLowerCase();
        if (b.includes('fujifilm'))   return 'product-card__brand--fuji';
        if (b.includes('sony'))       return 'product-card__brand--sony';
        if (b.includes('nikon'))      return 'product-card__brand--nikon';
        if (b.includes('canon'))      return 'product-card__brand--canon';
        return 'product-card__brand--default';
    }

    function formatPrice(priceStr) {
        if (!priceStr) return '';
        const digits = String(priceStr).replace(/\D/g, '');
        if (digits) return parseInt(digits, 10).toLocaleString('vi-VN') + ' ₫';
        return priceStr;
    }

    if (productGrid) {
        productGrid.innerHTML = products.map(product => `
            <div class="product-card">
                <button onclick="handleFavorite('${product.id}', this)" class="product-card__fav-btn" title="Yêu thích">
                    <span class="material-symbols-outlined" style="font-size:1.25rem; color: ${isFavorited(product.id) ? 'var(--error)' : 'inherit'}; font-variation-settings: 'FILL' ${isFavorited(product.id) ? '1' : '0'};">favorite</span>
                </button>

                <a href="index.php?page=chitietsanpham&id=${product.id}" class="product-card__img-wrap">
                    <img src="${product.image}" alt="${product.name}" class="product-card__img" loading="lazy">
                </a>

                <div class="product-card__body">
                    <span class="product-card__brand ${getBrandClass(product.brand)}">${product.brand}</span>
                    <a href="index.php?page=chitietsanpham&id=${product.id}" class="product-card__name">${product.name}</a>
                    <div class="product-card__footer">
                        <span class="product-card__price">${formatPrice(product.price)}</span>
                        <a href="index.php?page=chitietsanpham&id=${product.id}" class="product-card__link">
                            Xem <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        `).join('');
    }

    updateCartBadge();
});

// ── Yêu thích ────────────────────────────────────────────────
// (Global handleFavorite from auth.js is used)

// ── Giỏ hàng ─────────────────────────────────────────────────
window.getCart = function() {
    const user = getCurrentUser();
    if (!user) return [];
    return JSON.parse(localStorage.getItem(`cart_${user.username}`)) || [];
};

window.updateCartBadge = function() {
    const cart       = getCart();
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const badge      = document.getElementById('cartBadge');
    if (badge) {
        if (totalItems > 0) {
            badge.innerText = totalItems;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
};

