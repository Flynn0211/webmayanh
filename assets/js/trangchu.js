// File: js/trangchu.js

document.addEventListener("DOMContentLoaded", function() {
    const defaultProducts = [
        { id: 1, brand: 'Leica', name: 'Leica M11 Monochrom', price: '239,000,000 ₫', stock: 10, image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuABV-XTe-vFpU53s2sTQEXzuCqivZEsb9F8spF0sA1gE_hcx1EJqBVD1wlHL9CSbeOzdLZbm4eP2EUBYwHCWYJGam7sMDOFde_HDqjIwdfUzAcQh_yaxd8USG7ICBLW0gw5S6zL_VL3O5RXD4hZ6Lxs0t56YUuNnbaoZymLvHHuULPmIyJ5RMGzwmtB60zmG9VW1pEoHMeNjQg88MLotOICFVJnIWrnmhQHQeQhaogxdIIQLjpM5kcOBiaTKfh8Hx9MOvcvVb8YICc' },
        { id: 2, brand: 'Sony', name: 'Sony Alpha 7R V', price: '90,000,000 ₫', stock: 5, image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCLoCYShKrk94bl_ZVJhTbHUwmDE2oVSHqd1KKVTcT8Bm-ejTy5YGT7Zb1s5G0qMqZ6anS-lJQ2elz8PT_Cw8UzwGugXglTvyZQZvZt6-Ja5mQDzci-urOhLnOFkKrvuZW0OecHBAHi-CFcOe6WfXaGGIFdnmASviUBWn1mZ2tYTg6WHfmlFLGmzlOEBzeF0OD2K-KCz-uD5l4ZQ--k_SmaUYfIEAjZs62fTlJf1EomIlg_534rHFS9-IYUW8YyaTdQ56QFM9KHQzE' },
        { id: 3, brand: 'Fujifilm', name: 'Fujifilm X-T5 Body', price: '43,000,000 ₫', stock: 8, image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDl6r5lt_hmgSZ8skeRJHkAfIEdVgAiVwx3Jg0RnhisE1bvLnOSZzUjIy5RKTxUflmNoLSuUvQedC-WG3UFXtw_7-wz4twPhs1l9kqMrv1yO3raVAPeRm3SNQzb1waYtqhwuVd9hDHy1dIoxO1SSFKoWzmPH5z92gerhsm-_t3qgXS55jPUdiFIq_q6RL0Pd2g_3owCF8gjSdyjl5LwkkHlIQpT09kUQXrgIbdSDvmaghvg9FJrdwD8IoHL54ERn_BMxaZiRD3NcRE' },
        { id: 4, brand: 'Hasselblad', name: 'Hasselblad X2D 100C', price: '205,000,000 ₫', stock: 3, image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDQm8a7UaM8Tf1r0bA76uF6W3fW04u0M_H-h1Q1E9_a4Kqf3Bqf3A2_wzKz-a5n91Yv74x-yv9_Kq7b2-O72-fU9sT-Z0vWf8vMhT2e697_7G8J8g_18tE9-4y0d13G85Zz8Oa3y8pZ610f4Xg_h0d1a49E5O4uXzK79eO1bW4M9lK3_wzZz84xUq10H200G4D9_0pT9k4-A82b' }
    ];

    if (!localStorage.getItem('products')) {
        localStorage.setItem('products', JSON.stringify(defaultProducts));
    } else {
        let prods = JSON.parse(localStorage.getItem('products'));
        let updated = false;
        prods.forEach(p => {
            if (p.stock === undefined) { p.stock = 10; updated = true; }
        });
        if (updated) localStorage.setItem('products', JSON.stringify(prods));
    }

    const products    = JSON.parse(localStorage.getItem('products')) || [];
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

