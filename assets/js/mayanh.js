// File: js/mayanh.js

document.addEventListener("DOMContentLoaded", function() {
    const productGrid   = document.getElementById('productGrid');
    const noProductsMsg = document.getElementById('noProductsMsg');
    const searchInput   = document.getElementById('searchInput');
    const sortSelect    = document.getElementById('sortSelect');
    const brandBtns     = document.querySelectorAll('.brand-btn');

    let allProducts = window.dbProducts || [];

    let currentBrand = 'all';

    updateCartBadge();

    // ── Helpers ──────────────────────────────────────────────
    function getBrandClass(brand) {
        if (!brand) return 'catalog-card__brand--default';
        const b = brand.toLowerCase();
        if (b.includes('fujifilm')) return 'catalog-card__brand--fuji';
        if (b.includes('sony'))     return 'catalog-card__brand--sony';
        if (b.includes('nikon'))    return 'catalog-card__brand--nikon';
        if (b.includes('canon'))    return 'catalog-card__brand--canon';
        return 'catalog-card__brand--default';
    }

    function formatPrice(priceStr) {
        if (!priceStr) return '';
        const digits = String(priceStr).replace(/\D/g, '');
        if (digits) return parseInt(digits, 10).toLocaleString('vi-VN') + ' ₫';
        return priceStr;
    }

    function getRawPrice(priceStr) {
        return parseInt(String(priceStr).replace(/\D/g, '')) || 0;
    }

    // ── Render ────────────────────────────────────────────────
    function renderProducts() {
        // Exclude lens items and show only cameras precisely
        let filtered = allProducts.filter(p => p.category === 'camera');

        // 1. Filter brand
        if (currentBrand !== 'all') {
            filtered = filtered.filter(p => p.brand.toLowerCase() === currentBrand);
        }

        // 2. Filter search
        const term = searchInput.value.toLowerCase().trim();
        if (term) filtered = filtered.filter(p => p.name.toLowerCase().includes(term));

        // 3. Sort
        if (sortSelect.value === 'price_asc')  filtered.sort((a, b) => getRawPrice(a.price) - getRawPrice(b.price));
        if (sortSelect.value === 'price_desc') filtered.sort((a, b) => getRawPrice(b.price) - getRawPrice(a.price));

        if (filtered.length === 0) {
            noProductsMsg.classList.remove('hidden');
            productGrid.innerHTML = '';
            productGrid.appendChild(noProductsMsg);
            return;
        }

        noProductsMsg.classList.add('hidden');

        productGrid.innerHTML = filtered.map(product => {
            const badge = product.brand.toLowerCase() === 'leica'
                ? '<div class="catalog-card__badge">Limited</div>'
                : '';
            return `
            <div class="catalog-card">
                ${badge}
                <a class="catalog-card__img-link" href="index.php?page=chitietsanpham&id=${product.id}">
                    <div class="catalog-card__overlay"></div>
                    <img alt="${product.name}" class="catalog-card__img" src="${product.image}"/>
                </a>
                <div class="catalog-card__info">
                    <div class="catalog-card__header">
                        <span class="catalog-card__brand ${getBrandClass(product.brand)}">${product.brand}</span>
                        <button onclick="handleFavorite('${product.id}', this)" class="catalog-card__fav-btn" aria-label="Yêu thích">
                            <span class="material-symbols-outlined" style="font-size:1.25rem; color: ${isFavorited(product.id) ? 'var(--error)' : 'inherit'}; font-variation-settings: 'FILL' ${isFavorited(product.id) ? '1' : '0'};">favorite</span>
                        </button>
                    </div>
                    <h3 class="catalog-card__name">
                        <a href="index.php?page=chitietsanpham&id=${product.id}">${product.name}</a>
                    </h3>
                    <p class="catalog-card__price">
                        ${product.raw_original_price > product.raw_price ? `<span style="text-decoration: line-through; color: #888; font-size: 0.85em; margin-right: 8px;">${product.original_price}</span>` : ''}
                        ${formatPrice(product.price)}
                    </p>
                </div>
            </div>`;
        }).join('');
    }

    // ── Events ────────────────────────────────────────────────
    brandBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            brandBtns.forEach(b => b.classList.remove('brand-btn--active'));
            e.currentTarget.classList.add('brand-btn--active');
            currentBrand = e.currentTarget.getAttribute('data-brand');
            renderProducts();
        });
    });

    searchInput.addEventListener('input', renderProducts);
    sortSelect.addEventListener('change', renderProducts);

    renderProducts();
});

// ── Shared helpers ────────────────────────────────────────────
// (Global handleFavorite from auth.js is used)

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
        if (totalItems > 0) { badge.innerText = totalItems; badge.classList.remove('hidden'); }
        else { badge.classList.add('hidden'); }
    }
};

