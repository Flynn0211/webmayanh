<?php $activeNav = 'taikhoan'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Hồ Sơ Cá Nhân - LENS &amp; LIGHT</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="assets/css/base.css" rel="stylesheet"/>
    <link href="assets/css/client.css?v=1.3" rel="stylesheet"/>
    <link href="assets/css/responsive.css" rel="stylesheet"/>
</head>
<body style="min-height:100vh; display:flex; flex-direction:column; background-color: var(--surface);">

<?php include 'view/client/layout/_navbar.php'; ?>

<main class="account-layout" style="flex:1;">
    <!-- Sidebar Left -->
    <aside class="account-sidebar">
        <h2 class="account-sidebar__title">TÀI KHOẢN</h2>
        <nav class="account-sidebar__nav">
            <a class="account-sidebar__link account-sidebar__link--active" href="#" id="navProfile">
                Hồ sơ cá nhân
            </a>
            <a class="account-sidebar__link" href="#" id="navOrders">
                Lịch sử đơn hàng
            </a>
            <a class="account-sidebar__link" href="#" id="navFavorites">
                Sản phẩm yêu thích
            </a>
            <button class="account-sidebar__link account-sidebar__link--logout" id="logoutSidebarBtn">
                Đăng xuất
            </button>
        </nav>
    </aside>

    <!-- Main Canvas Right -->
    <section class="account-canvas">
        <div class="account-canvas-container">
            <div class="account-slider" id="accountSlider">
                
                <!-- Panel 1: Profile -->
                <div class="account-panel" id="panel-profile">
                    <header class="account-canvas__header">
                        <h1 class="account-canvas__title">Hồ sơ cá nhân</h1>
                    </header>

                    <!-- Bento Grid Layout -->
                    <div class="profile-bento">
            <!-- Contact Card -->
            <div class="profile-card">
                <div class="profile-card__header">
                    <h3 class="profile-card__title">Thông tin liên hệ</h3>
                    <button class="profile-card__edit-btn" title="Chỉnh sửa thông tin">
                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                    </button>
                </div>
                <div class="profile-info-group">
                    <div class="profile-info-item">
                        <span class="profile-info-label">Họ và Tên</span>
                        <span class="profile-info-val" id="profileName">-</span>
                    </div>
                    <div class="profile-info-item">
                        <span class="profile-info-label">Email</span>
                        <span class="profile-info-val" id="profileEmail">-</span>
                    </div>
                    <div class="profile-info-item">
                        <span class="profile-info-label">Số điện thoại</span>
                        <span class="profile-info-val" id="profilePhone">-</span>
                    </div>
                </div>
            </div>

            <!-- Membership Card -->
            <div class="profile-card">
                <h3 class="profile-card__title" style="margin-bottom: 2rem;">Hạng thành viên</h3>
                <div id="membershipCard" style="margin-bottom: 1.5rem; padding: 1.5rem; border-radius: 12px; background: linear-gradient(135deg, #434343 0%, #000000 100%); color: white; position: relative; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                    <!-- Decorative background element -->
                    <div style="position: absolute; top: -50%; right: -20%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%); border-radius: 50%;"></div>
                    
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; position: relative; z-index: 1;">
                        <div id="tierIconContainer" style="width: 48px; height: 48px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                            <span id="tierIcon" class="material-symbols-outlined" style="font-size: 24px; color: #fff;">workspace_premium</span>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; color: rgba(255,255,255,0.7); margin-bottom: 0.25rem;">Thẻ thành viên</div>
                            <div id="profileTier" style="font-size: 1.25rem; font-weight: 700; font-family: 'Geist', sans-serif; letter-spacing: 1px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">NONE MEMBER (0 PTS)</div>
                        </div>
                    </div>
                    
                    <div style="position: relative; z-index: 1;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem;">
                            <span style="color: rgba(255,255,255,0.8);">Tổng chi tiêu</span>
                            <strong id="profileSpent" style="font-family: 'Geist', sans-serif;">0đ</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.875rem;">
                            <span id="nextTierLabel" style="color: rgba(255,255,255,0.8);">Thăng hạng tiếp theo</span>
                            <strong id="profileNextTier" style="font-family: 'Geist', sans-serif; color: #fff;">-</strong>
                        </div>
                        
                        <!-- Progress bar -->
                        <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.2); border-radius: 3px; overflow: hidden; box-shadow: inset 0 1px 3px rgba(0,0,0,0.3);">
                            <div id="tierProgressBar" style="height: 100%; width: 0%; background: linear-gradient(90deg, #fff, #ccc); border-radius: 3px; transition: width 1s ease-in-out;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; font-size: 0.75rem; color: rgba(255,255,255,0.6); font-family: 'Geist', sans-serif; text-transform: uppercase; letter-spacing: 1px;">
                            <span id="currentTierName">None</span>
                            <span id="nextTierName">Silver</span>
                        </div>
                    </div>
                </div>

                <p class="membership-desc" style="margin-top: 1rem;">
                    Tận hưởng đặc quyền ưu đãi thành viên, tích lũy điểm thưởng sau mỗi giao dịch mua sắm máy ảnh và ống kính, cùng các quà tặng độc quyền định kỳ từ hệ thống.
                </p>
                <div class="membership-link">
                    <a class="membership-link__btn" href="#" id="btnOpenPerks">Xem chi tiết đặc quyền</a>
                </div>
            </div>
        </div>

        <!-- Recent Orders Preview -->
        <div class="recent-orders">
            <div class="recent-orders__header">
                <h3 class="recent-orders__title">Đơn hàng gần đây</h3>
                <a class="recent-orders__link" href="#" id="linkViewAllOrders">Xem tất cả</a>
            </div>

            <!-- Table Spec Style for Orders -->
            <div class="order-spec-table" id="recentOrdersContainer">
                <!-- Table Header -->
                <div class="order-spec-table__header">
                    <div class="order-spec-table__th">Mã đơn</div>
                    <div class="order-spec-table__th">Ngày đặt</div>
                    <div class="order-spec-table__th">Người nhận</div>
                    <div class="order-spec-table__th right">Trạng thái</div>
                </div>

                <!-- Table Rows will be rendered dynamically -->
                <div id="recentOrdersBody"></div>
            </div>
        </div>
                </div> <!-- End Panel Profile -->

                <!-- Panel 2: Orders & Favorites -->
                <div class="account-panel account-panel--orders" id="panel-orders">
                    <header class="account-canvas__header">
                        <h1 class="account-canvas__title">Lịch sử đơn hàng</h1>
                    </header>
                    
                    <div class="tab-panel" style="margin-top: 2rem;">
                        <!-- Đơn Hàng -->
                        <div id="ordersContainer" class="block"></div>
                    </div>
                </div> <!-- End Panel Orders -->
                
                <!-- Panel 3: Favorites -->
                <div class="account-panel account-panel--favorites" id="panel-favorites">
                    <header class="account-canvas__header">
                        <h1 class="account-canvas__title">Sản phẩm yêu thích</h1>
                    </header>
                    
                    <div class="tab-panel" style="margin-top: 2rem;">
                        <!-- Yêu Thích -->
                        <div id="favoritesContainer" class="block"></div>
                    </div>
                </div> <!-- End Panel Favorites -->
                
            </div> <!-- End Slider -->
        </div> <!-- End Canvas Container -->
    </section>
</main>

<?php include 'view/client/layout/_footer_dark.php'; ?>

<!-- Edit Profile Modal -->
<div id="editProfileModal" class="modal-overlay" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; opacity: 0; pointer-events: none; transition: opacity 0.3s;">
    <div class="modal-content" style="background: var(--surface); padding: 2rem; border-radius: 8px; width: 100%; max-width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
        <h2 style="margin-top: 0; margin-bottom: 1.5rem;">Chỉnh sửa thông tin</h2>
        <form id="editProfileForm">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Họ và Tên</label>
                <input type="text" id="editName" style="width: 100%; padding: 0.75rem; border: 1px solid var(--outline); background: transparent; color: var(--on-surface); border-radius: 4px;" required>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Email</label>
                <input type="email" id="editEmail" style="width: 100%; padding: 0.75rem; border: 1px solid var(--outline); background: transparent; color: var(--on-surface); border-radius: 4px;" required>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Số điện thoại</label>
                <input type="text" id="editPhone" style="width: 100%; padding: 0.75rem; border: 1px solid var(--outline); background: transparent; color: var(--on-surface); border-radius: 4px;" required>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" id="btnCancelEdit" style="padding: 0.5rem 1rem; background: transparent; border: 1px solid var(--outline); color: var(--on-surface); cursor: pointer; border-radius: 4px;">Hủy</button>
                <button type="submit" style="padding: 0.5rem 1rem; background: var(--primary); border: none; color: var(--on-primary); cursor: pointer; border-radius: 4px; font-weight: 500;">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- Perks Modal -->
<div id="perksModal" class="modal-overlay" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; opacity: 0; pointer-events: none; transition: opacity 0.3s;">
    <div class="modal-content" style="background: var(--surface); padding: 2rem; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
        <h2 style="margin-top: 0; margin-bottom: 1.5rem; border-bottom: 1px solid var(--outline); padding-bottom: 1rem;">Đặc quyền hạng thành viên</h2>
        
        <div style="margin-bottom: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 4px;">
            <h3 style="margin: 0 0 0.5rem 0; color: #a1a1aa;">Silver Member</h3>
            <p style="margin: 0; font-size: 0.9rem;">Từ 1.000 điểm tích lũy. Tự động <strong style="color:var(--primary);">giảm 2%</strong> trên tổng hóa đơn thanh toán.</p>
        </div>
        <div style="margin-bottom: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 4px;">
            <h3 style="margin: 0 0 0.5rem 0; color: #fbbf24;">Gold Member</h3>
            <p style="margin: 0; font-size: 0.9rem;">Từ 5.000 điểm tích lũy. Tự động <strong style="color:var(--primary);">giảm 5%</strong> trên tổng hóa đơn thanh toán.</p>
        </div>
        <div style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 4px;">
            <h3 style="margin: 0 0 0.5rem 0; color: #38bdf8;">Diamond Member</h3>
            <p style="margin: 0; font-size: 0.9rem;">Từ 10.000 điểm tích lũy. Tự động <strong style="color:var(--primary);">giảm 10%</strong> trên tổng hóa đơn thanh toán.</p>
        </div>

        <div style="text-align: right;">
            <button type="button" id="btnClosePerks" style="padding: 0.5rem 1.5rem; background: var(--primary); border: none; color: var(--on-primary); cursor: pointer; border-radius: 4px; font-weight: 500;">Đã hiểu</button>
        </div>
    </div>
</div>

<style>
    .modal-overlay.active {
        opacity: 1 !important;
        pointer-events: auto !important;
    }
</style>

<script src="assets/js/auth.js?v=2.0"></script>
<script src="assets/js/donhang.js"></script>
<script src="assets/js/taikhoan.js"></script>
</body>
</html>
