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
        // Cập nhật stock cho các sản phẩm cũ chưa có
        let prods = JSON.parse(localStorage.getItem('products'));
        let updated = false;
        prods.forEach(p => {
            if (p.stock === undefined) {
                p.stock = 10;
                updated = true;
            }
        });
        if (updated) localStorage.setItem('products', JSON.stringify(prods));
    }

    const products = JSON.parse(localStorage.getItem('products')) || [];
    const productGrid = document.getElementById('productGrid');
    
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
        // Trích xuất các chữ số từ chuỗi nhập vào (loại bỏ chữ, khoảng trắng, dấu phẩy, dấu chấm cũ)
        const digits = String(priceStr).replace(/\D/g, '');
        if (digits) {
            // Định dạng lại theo chuẩn VN (có dấu chấm phân cách hàng nghìn)
            const number = parseInt(digits, 10);
            return number.toLocaleString('vi-VN') + ' ₫';
        }
        return priceStr;
    }
    
    if (productGrid) {
        productGrid.innerHTML = products.map(product => `
            <div class="group relative flex flex-col bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300 border border-outline-variant/30">
                <div class="absolute top-4 right-4 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="handleFavorite('${product.id}')" class="bg-surface/80 backdrop-blur-sm p-2 rounded-full hover:bg-surface text-on-surface-variant hover:text-primary transition-colors shadow-sm" title="Yêu thích">
                        <span class="material-symbols-outlined text-[20px]">favorite</span>
                    </button>
                </div>
                
                <a href="chitietsanpham.html?id=${product.id}" class="aspect-square bg-surface-container-low p-6 flex items-center justify-center relative overflow-hidden group-hover:bg-white transition-colors duration-500 block">
                    <img src="${product.image}" alt="${product.name}" class="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-700 ease-out" loading="lazy">
                </a>
                
                <div class="p-6 flex flex-col flex-grow bg-surface-container-lowest">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="font-label-caps tracking-widest text-[10px] ${getBrandColor(product.brand)} uppercase font-bold">${product.brand}</span>
                    </div>
                    <a href="chitietsanpham.html?id=${product.id}" class="block">
                        <h3 class="font-headline-md text-lg text-on-surface leading-tight mb-3 group-hover:text-primary transition-colors">${product.name}</h3>
                    </a>
                    <div class="mt-auto flex items-end justify-between">
                        <span class="font-mono-spec text-lg font-medium tracking-tight text-on-surface">${formatPrice(product.price)}</span>
                        <a href="chitietsanpham.html?id=${product.id}" class="flex items-center gap-2 text-[12px] font-label-caps tracking-widest text-on-surface hover:text-primary transition-colors uppercase font-bold">
                            Xem <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    // Cập nhật số lượng giỏ hàng trên Navbar (nếu có)
    updateCartBadge();
});

// Hàm xử lý Yêu thích
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
