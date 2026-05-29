// File: assets/js/taikhoan.js

document.addEventListener("DOMContentLoaded", () => {
    const user = getCurrentUser();
    
    // 1. Kiểm soát phiên đăng nhập
    if (!user) {
        alert("Vui lòng đăng nhập để truy cập trang tài khoản!");
        window.location.href = 'index.php?page=login';
        return;
    }

    // 2. Điền thông tin cá nhân
    const profileName = document.getElementById('profileName');
    const profileEmail = document.getElementById('profileEmail');
    const profilePhone = document.getElementById('profilePhone');
    const profileTier = document.getElementById('profileTier');
    const profileAddress = document.getElementById('profileAddress');

    if (profileName) profileName.textContent = user.fullname || user.username;
    if (profileEmail) profileEmail.textContent = user.email || 'Chưa cập nhật email';
    if (profilePhone) profilePhone.textContent = user.phone || 'Chưa cập nhật SĐT';
    
    // Tự động phân hạng thành viên theo role
    if (profileTier) {
        if (user.role === 'admin') {
            profileTier.textContent = 'Leica Professional';
        } else {
            profileTier.textContent = 'Leica Member';
        }
    }

    // Nếu đã đăng nhập, thiết lập địa chỉ demo cho đồng bộ thiết kế
    if (profileAddress) {
        profileAddress.innerHTML = `
            <strong>Studio của ${user.fullname || user.username}</strong><br/>
            15 Lê Lợi, Phường Bến Nghé<br/>
            Quận 1, Thành phố Hồ Chí Minh<br/>
            Việt Nam
        `;
    }

    // 3. Render danh sách 3 đơn hàng gần đây nhất
    const recentOrdersBody = document.getElementById('recentOrdersBody');
    const recentOrdersContainer = document.getElementById('recentOrdersContainer');

    if (recentOrdersBody) {
        const allOrders = JSON.parse(localStorage.getItem('orders')) || [];
        const userOrders = allOrders.filter(o => o.customerUsername === user.username);

        if (userOrders.length === 0) {
            recentOrdersBody.innerHTML = `
                <div style="padding: 3rem; text-align: center; color: var(--on-surface-variant); font-size: 0.875rem;">
                    <span class="material-symbols-outlined" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; color: var(--outline);">receipt_long</span>
                    Bạn chưa có đơn hàng nào gần đây.
                </div>
            `;
        } else {
            // Lấy 3 đơn hàng mới nhất (đảo ngược mảng)
            const recentOrders = [...userOrders].reverse().slice(0, 3);
            let html = '';

            recentOrders.forEach(order => {
                const status = order.status || 'Đang Xử Lý';
                html += `
                    <div class="order-spec-table__row">
                        <div class="order-spec-table__cell">
                            <span class="order-spec-table__cell-label">Mã đơn</span>
                            <span class="order-spec-table__cell-val" style="font-family: 'Geist', sans-serif; font-weight: 500;">#${order.id}</span>
                        </div>
                        <div class="order-spec-table__cell">
                            <span class="order-spec-table__cell-label">Ngày đặt</span>
                            <span class="order-spec-table__cell-val">${order.date}</span>
                        </div>
                        <div class="order-spec-table__cell">
                            <span class="order-spec-table__cell-label">Người nhận</span>
                            <span class="order-spec-table__cell-val order-spec-table__cell-val--name">${order.customerName || user.fullname}</span>
                        </div>
                        <div class="order-spec-table__cell right">
                            <span class="order-spec-table__cell-label">Trạng thái</span>
                            <span class="order-spec-table__cell-val order-spec-table__cell-val--status">${status}</span>
                        </div>
                    </div>
                `;
            });

            recentOrdersBody.innerHTML = html;
        }
    }

    // 4. Gắn sự kiện nút Đăng xuất sidebar
    const logoutSidebarBtn = document.getElementById('logoutSidebarBtn');
    if (logoutSidebarBtn) {
        logoutSidebarBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm("Bạn có chắc chắn muốn đăng xuất tài khoản?")) {
                logout();
            }
        });
    }
});
