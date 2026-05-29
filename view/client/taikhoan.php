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
            <a class="account-sidebar__link account-sidebar__link--active" href="index.php?page=taikhoan">
                Hồ sơ cá nhân
            </a>
            <a class="account-sidebar__link" href="index.php?page=donhang">
                Lịch sử đơn hàng
            </a>
            <a class="account-sidebar__link" href="#">
                Sổ địa chỉ
            </a>
            <a class="account-sidebar__link" href="#">
                Cài đặt tài khoản
            </a>
            <button class="account-sidebar__link account-sidebar__link--logout" id="logoutSidebarBtn">
                Đăng xuất
            </button>
        </nav>
    </aside>

    <!-- Main Canvas Right -->
    <section class="account-canvas">
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
                <div class="membership-tier">
                    <span class="material-symbols-outlined">photo_camera</span>
                    <span class="membership-tier__name" id="profileTier">Leica Member</span>
                </div>
                <p class="membership-desc">
                    Tận hưởng đặc quyền ưu đãi thành viên, tích lũy điểm thưởng sau mỗi giao dịch mua sắm máy ảnh và ống kính, cùng các quà tặng độc quyền định kỳ từ hệ thống.
                </p>
                <div class="membership-link">
                    <a class="membership-link__btn" href="#">Xem chi tiết đặc quyền</a>
                </div>
            </div>

            <!-- Address Card -->
            <div class="profile-card profile-card--full">
                <div class="profile-card__header">
                    <h3 class="profile-card__title">Địa chỉ mặc định</h3>
                    <button class="membership-link__btn" style="border: 1px solid var(--on-surface); padding: 0.5rem 1rem; border-bottom: 1px solid var(--on-surface); cursor:pointer; background:none;">
                        Quản lý địa chỉ
                    </button>
                </div>
                <div>
                    <span class="profile-info-label" style="display:block;">Địa chỉ giao hàng</span>
                    <p class="profile-address" id="profileAddress">
                        Chưa thiết lập địa chỉ giao hàng mặc định.
                    </p>
                </div>
            </div>
        </div>

        <!-- Recent Orders Preview -->
        <div class="recent-orders">
            <div class="recent-orders__header">
                <h3 class="recent-orders__title">Đơn hàng gần đây</h3>
                <a class="recent-orders__link" href="index.php?page=donhang">Xem tất cả</a>
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
    </section>
</main>

<?php include 'view/client/layout/_footer_light.php'; ?>

<script src="assets/js/auth.js?v=2.0"></script>
<script src="assets/js/taikhoan.js"></script>
</body>
</html>
