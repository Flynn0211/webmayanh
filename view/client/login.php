<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>LENS &amp; LIGHT - Đăng nhập</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="assets/css/base.css" rel="stylesheet"/>
    <link href="assets/css/client.css" rel="stylesheet"/>
</head>
<body style="min-height:100vh;display:flex;flex-direction:column;position:relative;">

    <a href="index.php?page=trangchu" class="auth-back-btn">
        <span class="material-symbols-outlined" style="font-size:1.125rem;">arrow_back</span>
        Quay lại trang chủ
    </a>

    <div class="auth-center">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-card__header">
                <span class="material-symbols-outlined auth-card__icon">camera</span>
                <h1 class="auth-card__title">LENS &amp; LIGHT</h1>
                <p class="auth-card__subtitle">Đăng nhập để quản lý đơn hàng và lưu yêu thích.</p>
            </div>

            <!-- Tabs -->
            <div class="auth-tab-bar">
                <button id="tabLogin"    class="auth-tab-btn auth-tab-btn--active">Đăng nhập</button>
                <button id="tabRegister" class="auth-tab-btn">Đăng ký</button>
            </div>

            <!-- Form Đăng nhập -->
            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label class="form-label" for="loginUsername">Tên đăng nhập</label>
                    <input type="text" id="loginUsername" required placeholder="admin hoặc user" class="form-input"/>
                </div>
                <div class="form-group">
                    <label class="form-label" for="loginPassword">Mật khẩu</label>
                    <input type="password" id="loginPassword" required placeholder="Nhập mật khẩu" class="form-input"/>
                </div>
                <button type="submit" class="btn-auth btn-auth--primary">ĐĂNG NHẬP</button>
            </form>

            <!-- Form Đăng ký -->
            <form id="registerForm" class="auth-form hidden">
                <div class="form-group">
                    <label class="form-label" for="regFullname">Họ và tên</label>
                    <input type="text" id="regFullname" required placeholder="Nguyễn Văn A" class="form-input"/>
                </div>
                <div class="form-group">
                    <label class="form-label" for="regUsername">Tên đăng nhập</label>
                    <input type="text" id="regUsername" required placeholder="Nhập tên đăng nhập viết liền" class="form-input"/>
                </div>
                <div class="form-group">
                    <label class="form-label" for="regPassword">Mật khẩu</label>
                    <input type="password" id="regPassword" required placeholder="Tạo mật khẩu" class="form-input"/>
                </div>
                <button type="submit" class="btn-auth btn-auth--secondary">TẠO TÀI KHOẢN</button>
            </form>

            <!-- Thông báo lỗi -->
            <div id="authError" class="auth-error hidden">Sai tên đăng nhập hoặc mật khẩu!</div>
        </div>
    </div>

    <script src="assets/js/auth.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const tabLogin     = document.getElementById('tabLogin');
        const tabRegister  = document.getElementById('tabRegister');
        const loginForm    = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const authError    = document.getElementById('authError');

        function setTab(active) {
            const isLogin = (active === 'login');
            tabLogin.classList.toggle('auth-tab-btn--active', isLogin);
            tabRegister.classList.toggle('auth-tab-btn--active', !isLogin);
            loginForm.classList.toggle('hidden', !isLogin);
            registerForm.classList.toggle('hidden', isLogin);
            authError.classList.add('hidden');
        }
        tabLogin.onclick    = () => setTab('login');
        tabRegister.onclick = () => setTab('register');

        loginForm.onsubmit = (e) => {
            e.preventDefault();
            const u = document.getElementById('loginUsername').value.trim();
            const p = document.getElementById('loginPassword').value.trim();
            if (login(u, p)) {
                const user = getCurrentUser();
                window.location.href = (user && user.role === 'admin') ? 'index.php?page=admin' : 'index.php?page=trangchu';
            } else {
                authError.innerText = "Sai tên đăng nhập hoặc mật khẩu!";
                authError.classList.remove('hidden');
            }
        };

        registerForm.onsubmit = (e) => {
            e.preventDefault();
            const fn = document.getElementById('regFullname').value;
            const u  = document.getElementById('regUsername').value;
            const p  = document.getElementById('regPassword').value;
            if (register(fn, u, p)) {
                window.location.href = 'index.php?page=trangchu';
            } else {
                authError.innerText = "Tên đăng nhập đã tồn tại!";
                authError.classList.remove('hidden');
            }
        };

        if (getCurrentUser()) window.location.href = 'index.php?page=trangchu';
    });
    </script>
</body>
</html>


