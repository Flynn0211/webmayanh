// File: js/donhang.js

document.addEventListener("DOMContentLoaded", () => {
    const user = getCurrentUser();
    if (!user) {
        alert("Vui lòng đăng nhập để xem đơn hàng!");
        window.location.href = 'index.php?page=login';
        return;
    }

    const ordersContainer   = document.getElementById('ordersContainer');
    const favoritesContainer= document.getElementById('favoritesContainer');

    // ── Remove favorite ───────────────────────────────────────
    window.removeFavorite = function(productId) {
        const favKey = `favorites_${user.username}`;
        let favs = JSON.parse(localStorage.getItem(favKey)) || [];
        favs = favs.filter(id => id !== String(productId));
        localStorage.setItem(favKey, JSON.stringify(favs));
        renderFavorites();
    };

    // ── Render Favorites ──────────────────────────────────────
    function renderFavorites() {
        const favKey  = `favorites_${user.username}`;
        let favs      = JSON.parse(localStorage.getItem(favKey)) || [];
        const products= window.dbProducts || JSON.parse(localStorage.getItem('products')) || [];

        if (favs.length === 0) {
            favoritesContainer.innerHTML = `
            <div class="empty-state">
                <span class="material-symbols-outlined">favorite_border</span>
                <p>Bạn chưa có sản phẩm yêu thích nào.</p>
                <a href="index.php?page=trangchu" class="btn-cta">Khám phá ngay</a>
            </div>`;
            return;
        }

        let html = '<div class="fav-grid">';
        favs.forEach(favId => {
            const product = products.find(p => p.id == favId);
            if (product) {
                html += `
                <div class="fav-card">
                    <a href="index.php?page=chitietsanpham&id=${product.id}" class="fav-card__img">
                        <img src="${product.image}" alt="${product.name}">
                    </a>
                    <div class="fav-card__info">
                        <h4 class="fav-card__name">
                            <a href="index.php?page=chitietsanpham&id=${product.id}">${product.name}</a>
                        </h4>
                        <div class="fav-card__price">${product.price}</div>
                        <span class="fav-card__brand">${product.brand}</span>
                    </div>
                    <button onclick="removeFavorite('${product.id}')" class="fav-card__remove" title="Bỏ yêu thích">
                        <span class="material-symbols-outlined" style="font-size:1.25rem;">heart_broken</span>
                    </button>
                </div>`;
            }
        });
        html += '</div>';
        favoritesContainer.innerHTML = html;
    }

    // ── Fetch and Render Orders from Database ─────────────────────────────────────────
    function renderOrders() {
        ordersContainer.innerHTML = '<div style="text-align:center; padding: 2rem;">Đang tải đơn hàng...</div>';

        fetch('index.php?action=get_orders')
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    ordersContainer.innerHTML = `<div class="empty-state">
                        <p>${data.message || 'Có lỗi xảy ra khi tải đơn hàng.'}</p>
                    </div>`;
                    return;
                }

                let userOrders = data.orders || [];

                if (userOrders.length === 0) {
                    ordersContainer.innerHTML = `
                    <div class="empty-state">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <p>Bạn chưa có đơn hàng nào.</p>
                        <a href="index.php?page=trangchu" class="btn-cta">Mua sắm ngay</a>
                    </div>`;
                    return;
                }

                let html = '';

                userOrders.forEach(order => {
                    const st = (order.status || '').toLowerCase();
                    let badgeClass = 'status-badge--warn';
                    let badgeText  = order.status || 'Chờ Xác Nhận';
                    if (st.includes('thành công') || st.includes('đã giao') || st.includes('hoàn thành')) { 
                        badgeClass = 'status-badge--success'; 
                    } else if (st.includes('đang giao')) { 
                        badgeClass = 'status-badge--info'; 
                    } else if (st.includes('hủy')) {
                        badgeClass = 'status-badge--danger';
                    }

                    html += `
                    <div class="order-card">
                        <div class="order-card__header">
                            <div>
                                <div class="order-card__id">Mã ĐH: #${order.id}</div>
                                <div class="order-card__date">Ngày đặt: ${order.date}</div>
                            </div>
                            <span class="status-badge ${badgeClass}">${badgeText}</span>
                        </div>
                        <div class="order-card__body">`;

                    if (order.items && order.items.length > 0) {
                        order.items.forEach(item => {
                            const displayImage = item.image || '';
                            html += `
                            <div class="order-card__item">
                                <div class="order-card__item-img">
                                    <img src="${displayImage}" alt="${item.name}">
                                </div>
                                <div class="order-card__item-info">
                                    <div class="order-card__item-name">${item.name}</div>
                                    <div class="order-card__item-brand">Thương hiệu: ${item.brand || 'N/A'}</div>
                                </div>
                                <div class="order-card__item-right">
                                    <div class="order-card__item-qty">SL: ${item.quantity}</div>
                                    <div class="order-card__item-price">${item.price}</div>
                                </div>
                            </div>`;
                        });
                    }

                    html += `
                        </div>
                        <div class="order-card__footer">
                            <span class="order-card__total-label">Tổng cộng:</span>
                            <span class="order-card__total-val">${order.total}</span>
                        </div>
                    </div>`;
                });

                ordersContainer.innerHTML = html;
            })
            .catch(error => {
                console.error("Error fetching orders:", error);
                ordersContainer.innerHTML = '<div style="text-align:center; padding: 2rem;">Lỗi tải dữ liệu.</div>';
            });
    }

    renderOrders();
    renderFavorites();
});

