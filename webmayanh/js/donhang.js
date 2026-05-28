// File: js/donhang.js

document.addEventListener("DOMContentLoaded", () => {
    const user = getCurrentUser();
    if (!user) {
        alert("Vui lòng đăng nhập để xem đơn hàng!");
        window.location.href = 'login.html';
        return;
    }

    const ordersContainer = document.getElementById('ordersContainer');
    const favoritesContainer = document.getElementById('favoritesContainer');
    const tabOrders = document.getElementById('tabOrders');
    const tabFavorites = document.getElementById('tabFavorites');

    // Tab Switching Logic
    tabOrders.onclick = () => {
        tabOrders.classList.replace('border-transparent', 'border-primary');
        tabOrders.classList.replace('text-on-surface-variant', 'text-primary');
        tabFavorites.classList.replace('border-primary', 'border-transparent');
        tabFavorites.classList.replace('text-primary', 'text-on-surface-variant');
        ordersContainer.classList.remove('hidden');
        ordersContainer.classList.add('block');
        favoritesContainer.classList.remove('block');
        favoritesContainer.classList.add('hidden');
        renderOrders();
    };

    tabFavorites.onclick = () => {
        tabFavorites.classList.replace('border-transparent', 'border-primary');
        tabFavorites.classList.replace('text-on-surface-variant', 'text-primary');
        tabOrders.classList.replace('border-primary', 'border-transparent');
        tabOrders.classList.replace('text-primary', 'text-on-surface-variant');
        favoritesContainer.classList.remove('hidden');
        favoritesContainer.classList.add('block');
        ordersContainer.classList.remove('block');
        ordersContainer.classList.add('hidden');
        renderFavorites();
    };

    // Remove favorite globally
    window.removeFavorite = function(productId) {
        const favKey = `favorites_${user.username}`;
        let favs = JSON.parse(localStorage.getItem(favKey)) || [];
        favs = favs.filter(id => id !== String(productId));
        localStorage.setItem(favKey, JSON.stringify(favs));
        renderFavorites();
    };

    function renderFavorites() {
        const favKey = `favorites_${user.username}`;
        let favs = JSON.parse(localStorage.getItem(favKey)) || [];
        const products = JSON.parse(localStorage.getItem('products')) || [];

        if (favs.length === 0) {
            favoritesContainer.innerHTML = `<div class="text-center py-20 bg-surface-container-low rounded-xl border border-outline-variant/30">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant mb-4">favorite_border</span>
                <p class="font-body-md text-on-surface-variant">Bạn chưa có sản phẩm yêu thích nào.</p>
                <a href="trangchu.html" class="inline-block mt-4 bg-primary text-white font-label-caps px-6 py-2 rounded-lg text-sm uppercase tracking-widest hover:bg-inverse-surface transition-colors">Khám phá ngay</a>
            </div>`;
            return;
        }

        let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
        
        favs.forEach(favId => {
            const product = products.find(p => p.id == favId);
            if (product) {
                html += `
                <div class="flex items-center gap-4 py-4 px-4 border border-outline-variant/30 rounded-xl relative group hover:shadow-md transition-shadow">
                    <a href="chitietsanpham.html?id=${product.id}" class="w-20 h-20 bg-surface-container-lowest rounded-md flex items-center justify-center p-2 flex-shrink-0">
                        <img src="${product.image}" alt="${product.name}" class="max-w-full max-h-full object-contain mix-blend-multiply">
                    </a>
                    <div class="flex-grow">
                        <h4 class="font-medium text-on-surface mb-1"><a href="chitietsanpham.html?id=${product.id}" class="hover:text-primary transition-colors">${product.name}</a></h4>
                        <div class="font-mono-spec text-sm text-primary mb-1">${product.price}</div>
                        <span class="text-[10px] uppercase font-label-caps tracking-widest text-on-surface-variant">${product.brand}</span>
                    </div>
                    <button onclick="removeFavorite('${product.id}')" class="absolute top-4 right-4 text-on-surface-variant hover:text-error transition-colors" title="Bỏ yêu thích">
                        <span class="material-symbols-outlined text-[20px]">heart_broken</span>
                    </button>
                </div>
                `;
            }
        });

        html += '</div>';
        favoritesContainer.innerHTML = html;
    }

    function renderOrders() {
        let allOrders = JSON.parse(localStorage.getItem('orders')) || [];
        // Lọc các đơn hàng của user hiện tại
        let userOrders = allOrders.filter(o => o.customerUsername === user.username);

        if (userOrders.length === 0) {
            ordersContainer.innerHTML = `<div class="text-center py-20 bg-surface-container-low rounded-xl border border-outline-variant/30">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant mb-4">receipt_long</span>
                <p class="font-body-md text-on-surface-variant">Bạn chưa có đơn hàng nào.</p>
                <a href="trangchu.html" class="inline-block mt-4 bg-primary text-white font-label-caps px-6 py-2 rounded-lg text-sm uppercase tracking-widest hover:bg-inverse-surface transition-colors">Mua sắm ngay</a>
            </div>`;
            return;
        }

        let html = '';
        const products = JSON.parse(localStorage.getItem('products')) || [];

        // Hiển thị đơn mới nhất lên đầu
        userOrders.reverse().forEach(order => {
            // Định dạng badge trạng thái
            let statusBadge = '';
            const st = order.status ? order.status.toLowerCase() : '';
            if (st.includes('thành công') || st.includes('đã giao')) {
                statusBadge = '<span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest">Đã Giao</span>';
            } else if (st.includes('đang giao')) {
                statusBadge = '<span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest">Đang Giao</span>';
            } else {
                statusBadge = '<span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest">Đang Xử Lý</span>';
            }

            html += `
            <div class="mb-8 border border-outline-variant/30 rounded-xl overflow-hidden shadow-sm">
                <div class="bg-surface-container-low px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-outline-variant/30">
                    <div>
                        <div class="font-mono-spec font-bold text-on-surface">Mã ĐH: ${order.id}</div>
                        <div class="text-xs text-on-surface-variant mt-1">Ngày đặt: ${order.date}</div>
                    </div>
                    <div>
                        ${statusBadge}
                    </div>
                </div>
                <div class="p-6">
            `;

            if (order.items && order.items.length > 0) {
                order.items.forEach(item => {
                    const originalProd = products.find(p => p.id == item.id);
                    const displayImage = originalProd ? originalProd.image : (item.image || '');

                    html += `
                    <div class="flex items-center gap-4 py-4 border-b border-outline-variant/10 last:border-0">
                        <div class="w-16 h-16 bg-surface-container-lowest rounded-md flex items-center justify-center p-1">
                            <img src="${displayImage}" alt="${item.name}" class="max-w-full max-h-full object-contain mix-blend-multiply">
                        </div>
                        <div class="flex-grow">
                            <h4 class="font-medium text-on-surface">${item.name}</h4>
                            <p class="text-xs text-on-surface-variant">Thương hiệu: ${item.brand}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium">SL: ${item.quantity}</div>
                            <div class="font-mono-spec text-sm text-primary">${item.price}</div>
                        </div>
                    </div>
                    `;
                });
            }

            html += `
                </div>
                <div class="bg-surface-container-lowest px-6 py-4 border-t border-outline-variant/30 text-right">
                    <span class="text-sm text-on-surface-variant mr-4">Tổng cộng:</span>
                    <span class="font-mono-spec text-xl font-bold text-primary">${order.total}</span>
                </div>
            </div>
            `;
        });

        ordersContainer.innerHTML = html;
    }

    renderOrders();
});
