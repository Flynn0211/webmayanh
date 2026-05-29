// File: js/trangchu.js

document.addEventListener("DOMContentLoaded", function() {
    // Fetch products directly from live MySQL database records injected via window.dbProducts
    let liveProducts = (window.dbProducts && window.dbProducts.length > 0) ? window.dbProducts : (JSON.parse(localStorage.getItem('products')) || []);
    
    // Sync local storage with latest database records for consistent detail pages, cart, and wishlist behavior
    localStorage.setItem('products', JSON.stringify(liveProducts));

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
                <button onclick="handleFavorite('${product.id}')" class="product-card__fav-btn" title="Yêu thích">
                    <span class="material-symbols-outlined" style="font-size:1.25rem;">favorite</span>
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
window.handleFavorite = function(productId) {
    const user = getCurrentUser();
    if (!user) { alert("Vui lòng đăng nhập để thêm vào Yêu thích!"); window.location.href = 'index.php?page=login'; return; }
    const favKey = `favorites_${user.username}`;
    let favs = JSON.parse(localStorage.getItem(favKey)) || [];
    if (!favs.includes(String(productId))) {
        favs.push(String(productId));
        localStorage.setItem(favKey, JSON.stringify(favs));
        alert("Đã thêm sản phẩm vào danh sách yêu thích!");
    } else {
        alert("Sản phẩm này đã có trong danh sách yêu thích của bạn!");
    }
};

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

