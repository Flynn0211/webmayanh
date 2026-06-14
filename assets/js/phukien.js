// Tệp tin: assets/js/ongkinh.js

document.addEventListener("DOMContentLoaded", function() {
    const productGrid   = document.getElementById('productGrid');
    const noProductsMsg = document.getElementById('noProductsMsg');
    const searchInput   = document.getElementById('searchInput');
    const sortSelect    = document.getElementById('sortSelect');
    const brandBtns     = document.querySelectorAll('.brand-btn');

    // ── Complete Catalog Dataset (Cameras & Lenses) ───────────
    let allProducts = window.dbProducts || [];
    let currentBrand = 'all';

    updateCartBadge();

    // ── Hàm Hỗ Trợ (Helpers) ──────────────────────────────
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

    // ── Render Giao Diện ────────────────────────────────
    // ── Khai báo Biến Phân Trang ──────────────────────────
    let currentPage = 1;
    const itemsPerPage = 8;

    window.changePage = function(page) {
        currentPage = page;
        renderProducts(false);
        const grid = document.getElementById('productGrid');
        if (grid) {
            const headerOffset = 100;
            const elementPosition = grid.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            window.scrollTo({
                 top: offsetPosition,
                 behavior: "smooth"
            });
        }
    };

    // ── Render Giao Diện ────────────────────────────────
    function renderProducts(resetPage = true) {
        if (resetPage) currentPage = 1;
        // Exclude camera items and show only accessories precisely
        let filtered = allProducts.filter(p => p.category === 'accessory');

        // 1. Filter accessory type
        if (currentBrand !== 'all') {
            filtered = filtered.filter(p => {
                const b = (p.brand || '').toLowerCase();
                const n = (p.name || '').toLowerCase();
                if (currentBrand === 'day-deo') return b.includes('dây đeo') || n.includes('dây đeo');
                if (currentBrand === 'balo-tui') return b.includes('balo') || b.includes('ba lô') || b.includes('túi') || n.includes('balo') || n.includes('ba lô') || n.includes('túi');
                if (currentBrand === 'filter') return b.includes('filter') || n.includes('filter');
                if (currentBrand === 'bao-da') return b.includes('bao da') || n.includes('bao da');
                if (currentBrand === 'grip') return b.includes('grip') || n.includes('grip');
                if (currentBrand === 'khac') {
                    const types = ['dây đeo', 'balo', 'ba lô', 'túi', 'filter', 'bao da', 'grip'];
                    return !types.some(t => b.includes(t) || n.includes(t));
                }
                return true;
            });
        }

        // 2. Filter search
        const term = searchInput.value.toLowerCase().trim();
        if (term) filtered = filtered.filter(p => p.name.toLowerCase().includes(term));

        // 3. Sort
        if (sortSelect.value === 'price_asc')  filtered.sort((a, b) => getRawPrice(a.price) - getRawPrice(b.price));
        if (sortSelect.value === 'price_desc') filtered.sort((a, b) => getRawPrice(b.price) - getRawPrice(a.price));

        

        const existingPagination = document.getElementById('paginationControls');
        if (existingPagination) existingPagination.remove();

        

        if (filtered.length === 0) {
            noProductsMsg.classList.remove('hidden');
            productGrid.innerHTML = '';
            productGrid.appendChild(noProductsMsg);
            return;
        }
        noProductsMsg.classList.add('hidden');

        const totalPages = Math.ceil(filtered.length / itemsPerPage);
        const start = (currentPage - 1) * itemsPerPage;
        const currentProducts = filtered.slice(start, start + itemsPerPage);

        productGrid.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        productGrid.style.opacity = '0';
        productGrid.style.transform = 'translateY(10px)';

        setTimeout(() => {
            productGrid.innerHTML = currentProducts.map(product => {
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
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                        <p class="catalog-card__price" style="margin: 0;">
                            ${product.raw_original_price > product.raw_price ? `<span style="text-decoration: line-through; color: #888; font-size: 0.85em; margin-right: 8px;">${product.original_price}</span>` : ''}
                            ${formatPrice(product.price)}
                        </p>
                        <button onclick="addToCartFast('${product.id}')" title="Thêm vào giỏ" style="background:none; border:none; color:var(--primary); cursor:pointer; display:flex; align-items:center; padding:0;">
                            <span class="material-symbols-outlined">add_shopping_cart</span>
                        </button>
                    </div>
                </div>
            </div>`;
        }).join('');

            if (totalPages > 1) {
                let paginationHTML = '<div id="paginationControls" style="display: flex; justify-content: center; gap: 0.5rem; width: 100%; margin-top: 2rem; grid-column: 1 / -1;">';
                for (let i = 1; i <= totalPages; i++) {
                    paginationHTML += `<button onclick="window.changePage(${i})" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid ${i === currentPage ? 'var(--primary)' : 'var(--border-color)'}; background: ${i === currentPage ? 'var(--primary)' : 'transparent'}; color: ${i === currentPage ? '#fff' : 'inherit'}; cursor: pointer; transition: 0.3s; border-radius: 4px; font-family: 'Geist', sans-serif;">${i}</button>`;
                }
                paginationHTML += '</div>';
                productGrid.insertAdjacentHTML('beforeend', paginationHTML);
            }

            productGrid.style.opacity = '1';
            productGrid.style.transform = 'translateY(0)';
        }, 300);
    }

    // ── Lắng nghe Sự kiện ───────────────────────────────
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
