// File: js/mayanh.js

document.addEventListener("DOMContentLoaded", function() {
    const productGrid = document.getElementById('productGrid');
    const noProductsMsg = document.getElementById('noProductsMsg');
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    const brandBtns = document.querySelectorAll('.brand-btn');
    
    let allProducts = JSON.parse(localStorage.getItem('products')) || [];
    let currentBrand = 'all';
    
    // Khởi tạo Auth & Badge
    updateCartBadge();

    // Helper functions
    function getBrandColor(brand) {
        if (!brand) return 'text-primary';
        const b = brand.toLowerCase();
        if (b.includes('fujifilm')) return 'text-black';
        if (b.includes('sony')) return 'text-orange-500';
        if (b.includes('nikon')) return 'text-yellow-500';
        if (b.includes('canon')) return 'text-red-600';
        return 'text-primary';
    }

    function formatPrice(priceStr) {
        if (!priceStr) return '';
        const digits = String(priceStr).replace(/\D/g, '');
        if (digits) {
            const number = parseInt(digits, 10);
            return number.toLocaleString('vi-VN') + ' ₫';
        }
        return priceStr;
    }

    function getRawPrice(priceStr) {
        return parseInt(String(priceStr).replace(/\D/g, '')) || 0;
    }

    // Render function
    function renderProducts() {
        // 1. Filter by Brand
        let filtered = allProducts;
        if (currentBrand !== 'all') {
            filtered = filtered.filter(p => p.brand.toLowerCase() === currentBrand);
        }

        // 2. Filter by Search
        const searchTerm = searchInput.value.toLowerCase().trim();
        if (searchTerm) {
            filtered = filtered.filter(p => p.name.toLowerCase().includes(searchTerm));
        }

        // 3. Sort by Price
        const sortVal = sortSelect.value;
        if (sortVal === 'price_asc') {
            filtered.sort((a, b) => getRawPrice(a.price) - getRawPrice(b.price));
        } else if (sortVal === 'price_desc') {
            filtered.sort((a, b) => getRawPrice(b.price) - getRawPrice(a.price));
        }

        // Generate HTML
        if (filtered.length === 0) {
            noProductsMsg.classList.remove('hidden');
            productGrid.innerHTML = '';
        } else {
            noProductsMsg.classList.add('hidden');
            let html = '';
            filtered.forEach(product => {
                const badgeHtml = product.brand.toLowerCase() === 'leica' 
                    ? '<div class="absolute top-4 right-4 z-10 font-label-caps text-[10px] tracking-widest bg-primary text-on-primary px-3 py-1 uppercase shadow-sm">Limited</div>' 
                    : '';
                
                const brandColor = getBrandColor(product.brand);
                
                html += `
                <div class="group relative flex flex-col h-full bg-surface">
                    ${badgeHtml}
                    <!-- Hình ảnh -->
                    <a class="relative aspect-square w-full bg-surface-container-low flex items-center justify-center p-6 overflow-hidden mb-6" href="chitietsanpham.html?id=${product.id}">
                        <img alt="${product.name}" class="object-contain w-full h-full mix-blend-multiply transition-transform duration-700 ease-out group-hover:scale-110" src="${product.image}"/>
                        <div class="absolute inset-0 bg-surface-container/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </a>
                    
                    <!-- Thông tin -->
                    <div class="flex flex-col flex-grow px-2">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-label-caps text-[11px] tracking-[0.2em] uppercase ${brandColor}">${product.brand}</span>
                            <button onclick="handleFavorite('${product.id}')" aria-label="Favorite" class="text-on-surface-variant hover:text-error transition-colors focus:outline-none">
                                <span class="material-symbols-outlined text-[20px]">favorite</span>
                            </button>
                        </div>
                        <h3 class="font-display-md text-xl text-on-surface leading-tight mb-2 uppercase tracking-tight flex-grow">
                            <a class="hover:text-primary transition-colors" href="chitietsanpham.html?id=${product.id}">${product.name}</a>
                        </h3>
                        <p class="font-mono-spec text-lg text-on-surface mb-6">${formatPrice(product.price)}</p>
                    </div>
                </div>
                `;
            });
            productGrid.innerHTML = html;
        }
    }

    // Event Listeners
    brandBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Update active state of buttons
            brandBtns.forEach(b => {
                b.classList.remove('bg-primary', 'text-on-primary');
                b.classList.add('bg-surface-container', 'text-on-surface-variant');
            });
            e.target.classList.add('bg-primary', 'text-on-primary');
            e.target.classList.remove('bg-surface-container', 'text-on-surface-variant');
            
            currentBrand = e.target.getAttribute('data-brand');
            renderProducts();
        });
    });

    searchInput.addEventListener('input', renderProducts);
    sortSelect.addEventListener('change', renderProducts);

    // Initial render
    renderProducts();
});

// Hàm xử lý Yêu thích (dùng chung giống trangchu.js)
window.handleFavorite = function(productId) {
    const user = getCurrentUser();
    if (!user) {
        alert("Vui lòng đăng nhập để thêm vào Yêu thích!");
        window.location.href = 'login.html';
        return;
    }
    
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

// Hàm lấy giỏ hàng
window.getCart = function() {
    const user = getCurrentUser();
    if (!user) return [];
    const cartKey = `cart_${user.username}`;
    return JSON.parse(localStorage.getItem(cartKey)) || [];
};

// Hàm cập nhật Badge
window.updateCartBadge = function() {
    const cart = getCart();
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const badge = document.getElementById('cartBadge');
    if (badge) {
        if (totalItems > 0) {
            badge.innerText = totalItems;
            badge.classList.remove('hidden');
            badge.classList.add('flex', 'items-center', 'justify-center', 'text-[10px]', 'text-on-primary', 'w-4', 'h-4', '-top-1', '-right-1');
        } else {
            badge.classList.add('hidden');
        }
    }
};
