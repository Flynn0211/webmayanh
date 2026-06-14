// Tệp tin: assets/js/taikhoan.js

document.addEventListener("DOMContentLoaded", () => {
    const user = getCurrentUser();
    
    // 1. Kiểm soát phiên đăng nhập
    if (!user) {
        alert("Vui lòng đăng nhập để truy cập trang tài khoản!");
        window.location.href = 'index.php?page=login';
        return;
    }

    const profileName = document.getElementById('profileName');
    const profileEmail = document.getElementById('profileEmail');
    const profilePhone = document.getElementById('profilePhone');
    const profileTier = document.getElementById('profileTier');

    // 2. Fetch Profile from Backend
    function loadProfile() {
        fetch('index.php?action=get_profile')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const p = data.profile;
                    if (profileName) profileName.textContent = p.ho_ten || user.username;
                    if (profileEmail) profileEmail.textContent = p.email || 'Chưa cập nhật email';
                    if (profilePhone) profilePhone.textContent = p.sdt || 'Chưa cập nhật SĐT';

                    // Render Total Spent and Next Tier
                    const profileSpent = document.getElementById('profileSpent');
                    const profileNextTier = document.getElementById('profileNextTier');
                    const nextTierLabel = document.getElementById('nextTierLabel');
                    
                    // New Premium Card Elements
                    const membershipCard = document.getElementById('membershipCard');
                    const tierProgressBar = document.getElementById('tierProgressBar');
                    const currentTierName = document.getElementById('currentTierName');
                    const nextTierName = document.getElementById('nextTierName');
                    
                    if (profileSpent && profileNextTier && nextTierLabel) {
                        const pts = parseInt(p.diem_tich_luy) || 0;
                        const totalSpent = pts * 10000;
                        profileSpent.textContent = new Intl.NumberFormat('vi-VN').format(totalSpent) + 'đ';

                        let nextTierPts = 0;
                        let progress = 0;

                        if (pts < 1000) {
                            nextTierLabel.textContent = 'Lên hạng Silver:';
                            const needed = (1000 - pts) * 10000;
                            profileNextTier.textContent = `Cần mua thêm ${new Intl.NumberFormat('vi-VN').format(needed)}đ`;
                            
                            // Card Style: None -> Silver
                            if (membershipCard) membershipCard.style.background = 'linear-gradient(135deg, #434343 0%, #000000 100%)';
                            if (tierProgressBar) {
                                progress = (pts / 1000) * 100;
                                tierProgressBar.style.width = `${progress}%`;
                                tierProgressBar.style.background = 'linear-gradient(90deg, #e0e0e0, #9e9e9e)';
                            }
                            if (currentTierName) currentTierName.textContent = 'NONE';
                            if (nextTierName) nextTierName.textContent = 'SILVER (1k)';
                            if (profileTier) profileTier.textContent = 'MEMBER';
                            
                        } else if (pts < 5000) {
                            nextTierLabel.textContent = 'Lên hạng Gold:';
                            const needed = (5000 - pts) * 10000;
                            profileNextTier.textContent = `Cần mua thêm ${new Intl.NumberFormat('vi-VN').format(needed)}đ`;
                            
                            // Card Style: Silver -> Gold
                            if (membershipCard) membershipCard.style.background = 'linear-gradient(135deg, #757f9a 0%, #d7dde8 100%)';
                            if (tierProgressBar) {
                                progress = ((pts - 1000) / 4000) * 100;
                                tierProgressBar.style.width = `${progress}%`;
                                tierProgressBar.style.background = 'linear-gradient(90deg, #ffd700, #ff8c00)';
                            }
                            if (currentTierName) currentTierName.textContent = 'SILVER';
                            if (nextTierName) nextTierName.textContent = 'GOLD (5k)';
                            if (profileTier) profileTier.textContent = 'SILVER MEMBER';
                            // Change text color for light card
                            if (membershipCard) membershipCard.style.color = '#333';
                            
                        } else if (pts < 10000) {
                            nextTierLabel.textContent = 'Lên hạng Diamond:';
                            const needed = (10000 - pts) * 10000;
                            profileNextTier.textContent = `Cần mua thêm ${new Intl.NumberFormat('vi-VN').format(needed)}đ`;
                            
                            // Card Style: Gold -> Diamond
                            if (membershipCard) {
                                membershipCard.style.background = 'linear-gradient(135deg, #ffd700 0%, #ff8c00 100%)';
                                membershipCard.style.color = '#000';
                            }
                            if (tierProgressBar) {
                                progress = ((pts - 5000) / 5000) * 100;
                                tierProgressBar.style.width = `${progress}%`;
                                tierProgressBar.style.background = 'linear-gradient(90deg, #b9f2ff, #00d2ff)';
                            }
                            if (currentTierName) currentTierName.textContent = 'GOLD';
                            if (nextTierName) nextTierName.textContent = 'DIAMOND (10k)';
                            if (profileTier) profileTier.textContent = 'GOLD MEMBER';
                            
                        } else {
                            nextTierLabel.textContent = 'Thăng hạng tiếp theo:';
                            profileNextTier.textContent = 'Đã đạt cấp bậc tối đa';
                            
                            // Card Style: Diamond
                            if (membershipCard) {
                                membershipCard.style.background = 'linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%)';
                                membershipCard.style.color = '#fff';
                            }
                            if (tierProgressBar) {
                                tierProgressBar.style.width = '100%';
                                tierProgressBar.style.background = 'linear-gradient(90deg, #fff, #f0f0f0)';
                            }
                            if (currentTierName) currentTierName.textContent = 'DIAMOND';
                            if (nextTierName) nextTierName.textContent = 'MAX LEVEL';
                            if (profileTier) profileTier.textContent = 'DIAMOND MEMBER';
                        }
                        
                        // Force sub-elements color adjustment if light background
                        if (membershipCard && pts >= 1000 && pts < 10000) {
                            document.querySelectorAll('#membershipCard span:not(#tierIcon), #membershipCard strong').forEach(el => {
                                el.style.color = '#1c1b1b';
                                el.style.textShadow = 'none';
                            });
                        }
                    }
                    
                    // Update user info in localStorage just in case
                    user.fullname = p.ho_ten;
                    user.email = p.email;
                    user.phone = p.sdt;
                    localStorage.setItem('currentUser', JSON.stringify(user));
                } else {
                    // Fallback
                    if (profileName) profileName.textContent = user.fullname || user.username;
                    if (profileTier) profileTier.textContent = 'MEMBER';
                }
            });
    }

    loadProfile();

    // Edit Profile Logic
    const editBtn = document.querySelector('.profile-card__edit-btn');
    const editModal = document.getElementById('editProfileModal');
    const cancelEditBtn = document.getElementById('btnCancelEdit');
    const editForm = document.getElementById('editProfileForm');

    if (editBtn && editModal) {
        editBtn.addEventListener('click', () => {
            document.getElementById('editName').value = user.fullname || '';
            document.getElementById('editEmail').value = user.email || '';
            document.getElementById('editPhone').value = user.phone || '';
            editModal.classList.add('active');
        });

        cancelEditBtn.addEventListener('click', () => {
            editModal.classList.remove('active');
        });

        editForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const payload = {
                ho_ten: document.getElementById('editName').value,
                email: document.getElementById('editEmail').value,
                sdt: document.getElementById('editPhone').value
            };

            fetch('index.php?action=update_profile', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    editModal.classList.remove('active');
                    loadProfile();
                } else {
                    alert(data.message || 'Lỗi cập nhật');
                }
            })
            .catch(err => alert('Lỗi kết nối'));
        });
    }



    // Perks Modal Logic
    const btnOpenPerks = document.getElementById('btnOpenPerks');
    const perksModal = document.getElementById('perksModal');
    const btnClosePerks = document.getElementById('btnClosePerks');

    if (btnOpenPerks && perksModal) {
        btnOpenPerks.addEventListener('click', (e) => {
            e.preventDefault();
            perksModal.classList.add('active');
        });

        btnClosePerks.addEventListener('click', () => {
            perksModal.classList.remove('active');
        });
    }

    // 3. Render danh sách 3 đơn hàng gần đây nhất từ Backend
    const recentOrdersBody = document.getElementById('recentOrdersBody');
    if (recentOrdersBody) {
        fetch('index.php?action=get_orders')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.orders.length > 0) {
                    const recentOrders = data.orders.slice(0, 3);
                    let html = '';
                    recentOrders.forEach(order => {
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
                                    <span class="order-spec-table__cell-label">Tổng tiền</span>
                                    <span class="order-spec-table__cell-val">${order.total}</span>
                                </div>
                                <div class="order-spec-table__cell right">
                                    <span class="order-spec-table__cell-label">Trạng thái</span>
                                    <span class="order-spec-table__cell-val order-spec-table__cell-val--status">${order.status}</span>
                                </div>
                            </div>
                        `;
                    });
                    recentOrdersBody.innerHTML = html;
                } else {
                    recentOrdersBody.innerHTML = `
                        <div style="padding: 3rem; text-align: center; color: var(--on-surface-variant); font-size: 0.875rem;">
                            <span class="material-symbols-outlined" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; color: var(--outline);">receipt_long</span>
                            Bạn chưa có đơn hàng nào gần đây.
                        </div>
                    `;
                }
            })
            .catch(() => {
                recentOrdersBody.innerHTML = '<p style="color:red; text-align:center; padding:1rem;">Lỗi tải dữ liệu đơn hàng.</p>';
            });
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

    // 5. Slider Navigation Logic
    const navProfile = document.getElementById('navProfile');
    const navOrders = document.getElementById('navOrders');
    const navFavorites = document.getElementById('navFavorites');
    const linkViewAllOrders = document.getElementById('linkViewAllOrders');
    const accountSlider = document.getElementById('accountSlider');

    function slideTo(panel) {
        if (!accountSlider) return;
        
        // Remove active classes
        if (navProfile) navProfile.classList.remove('account-sidebar__link--active');
        if (navOrders) navOrders.classList.remove('account-sidebar__link--active');
        if (navFavorites) navFavorites.classList.remove('account-sidebar__link--active');
        
        // Reset slider classes
        accountSlider.classList.remove('show-orders', 'show-favorites');

        if (panel === 'orders') {
            accountSlider.classList.add('show-orders');
            if (navOrders) navOrders.classList.add('account-sidebar__link--active');
            window.location.hash = 'orders';
        } else if (panel === 'favorites') {
            accountSlider.classList.add('show-favorites');
            if (navFavorites) navFavorites.classList.add('account-sidebar__link--active');
            window.location.hash = 'favorites';
        } else {
            // Profile (default)
            if (navProfile) navProfile.classList.add('account-sidebar__link--active');
            window.location.hash = 'profile';
        }
    }

    if (navProfile) {
        navProfile.addEventListener('click', (e) => {
            e.preventDefault();
            slideTo('profile');
        });
    }

    if (navOrders) {
        navOrders.addEventListener('click', (e) => {
            e.preventDefault();
            slideTo('orders');
        });
    }

    if (navFavorites) {
        navFavorites.addEventListener('click', (e) => {
            e.preventDefault();
            slideTo('favorites');
        });
    }

    if (linkViewAllOrders) {
        linkViewAllOrders.addEventListener('click', (e) => {
            e.preventDefault();
            slideTo('orders');
        });
    }

    // Auto-slide based on hash on load
    if (window.location.hash === '#orders') {
        setTimeout(() => slideTo('orders'), 50);
    } else if (window.location.hash === '#favorites') {
        setTimeout(() => slideTo('favorites'), 50);
    }

});
