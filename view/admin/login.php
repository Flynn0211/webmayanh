<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <?php
    $base_url = (basename(dirname($_SERVER['PHP_SELF'])) === 'admin') ? '../' : './';
    ?>
    <base href="<?php echo $base_url; ?>"/>
    <title>Đăng nhập Admin - LENS &amp; LIGHT</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="assets/css/admin.css" rel="stylesheet"/>
</head>
<body class="admin-login-body">

    <!-- Ambient elements -->
    <div class="ambient-glow"></div>
    <div class="ambient-glow-2"></div>

    <!-- Login Container -->
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <span class="material-symbols-outlined">camera</span>
                LENS &amp; LIGHT
            </div>
            <div class="login-subtitle">Hệ thống quản trị</div>
        </div>

        <?php if (!empty($login_error)): ?>
            <div class="error-message">
                <span class="material-symbols-outlined">error</span>
                <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label class="form-label" for="username">Tên đăng nhập</label>
                <div class="input-wrapper">
                    <input class="form-input" type="text" name="username" id="username" placeholder="Nhập tên đăng nhập..." required autocomplete="off"/>
                    <span class="material-symbols-outlined input-icon">person</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mật khẩu</label>
                <div class="input-wrapper">
                    <input class="form-input" type="password" name="password" id="password" placeholder="Nhập mật khẩu..." required/>
                    <span class="material-symbols-outlined input-icon">lock</span>
                </div>
            </div>

            <button type="submit" name="login_admin" class="btn-login">
                <span class="material-symbols-outlined">login</span>
                Đăng nhập hệ thống
            </button>
        </form>

        <div class="footer-links">
            <a href="index.php?page=trangchu" class="footer-link">
                <span class="material-symbols-outlined">arrow_back</span>
                Quay lại trang chủ cửa hàng
            </a>
        </div>
    </div>

</body>
</html>
