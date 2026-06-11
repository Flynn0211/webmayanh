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
        productGrid.innerHTML = products.map(product => {
            const badge = product.brand.toLowerCase() === 'leica'
                ? '<div class="product-card__badge">Limited</div>'
                : '';
            return `
            <div class="product-card">
                ${badge}
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
                        <span class="product-card__price">
                            ${product.raw_original_price > product.raw_price ? `<span style="text-decoration: line-through; color: #888; font-size: 0.85em; margin-right: 8px;">${product.original_price}</span>` : ''}
                            ${formatPrice(product.price)}
                        </span>
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <button onclick="addToCartFast('${product.id}')" title="Thêm vào giỏ" style="background:none; border:none; color:var(--primary); cursor:pointer; display:flex; align-items:center; padding: 0;">
                                <span class="material-symbols-outlined">add_shopping_cart</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            `;
        }).join('');
    }

    updateCartBadge();

    // ── Đăng ký nhận bản tin (Newsletter) ─────────────────────────
    const newsletterForm = document.getElementById('homeNewsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = document.getElementById('homeNewsletterEmail');
            const email = emailInput.value;
            const btn = document.getElementById('homeNewsletterBtn');
            const originalText = btn.textContent;

            // Đổi trạng thái nút
            btn.textContent = 'ĐANG GỬI...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('email', email);

            // Gửi request ngầm (Fire and forget - Optimistic UI)
            fetch('index.php?action=subscribe_newsletter', {
                method: 'POST',
                body: formData
            }).catch(err => console.error(err));

            // Hiển thị thành công ngay lập tức
            alert('Cảm ơn bạn đã đăng ký! LENS & LIGHT đã gửi một email xác nhận đến hòm thư của bạn.');
            
            // Cập nhật lại session local nếu user đang đăng nhập nhưng chưa có email
            const user = getCurrentUser();
            if (user && !user.email) {
                user.email = email;
                localStorage.setItem('currentUser', JSON.stringify(user));
            }

            // Reset form
            newsletterForm.reset();
            btn.textContent = originalText;
            btn.disabled = false;
        });
    }
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

