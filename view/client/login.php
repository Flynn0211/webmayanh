<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load database connection
require_once __DIR__ . '/../../model/database.php';

// If already logged in via PHP session, redirect home immediately
if (!empty($_SESSION['client_logged_in'])) {
    header("Location: index.php?page=trangchu");
    exit;
}

// Load Auth Controller to comply with MVC structure
require_once __DIR__ . '/../../control/AuthController.php';

$login_error = "";
$js_login_success = "";

// 1. Process client login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_login'])) {
    $res = (new AuthController($conn))->handleLogin($conn);
    $login_error = $res['error'];
    $js_login_success = $res['success'];
}

// 2. Process client registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_register'])) {
    $res = (new AuthController($conn))->handleRegister($conn);
    $login_error = $res['error'];
    $js_login_success = $res['success'];
}
?>
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

    <?php if (!empty($js_login_success)) echo $js_login_success; ?>

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
            <form id="loginForm" action="" method="POST" class="auth-form">
                <div class="form-group">
                    <label class="form-label" for="loginUsername">Tên đăng nhập</label>
                    <input type="text" name="username" id="loginUsername" required placeholder="admin hoặc user" class="form-input"/>
                </div>
                <div class="form-group">
                    <label class="form-label" for="loginPassword">Mật khẩu</label>
                    <input type="password" name="password" id="loginPassword" required placeholder="Nhập mật khẩu" class="form-input"/>
                </div>
                <button type="submit" name="action_login" class="btn-auth btn-auth--primary">ĐĂNG NHẬP</button>
            </form>

            <!-- Form Đăng ký -->
            <form id="registerForm" action="" method="POST" class="auth-form hidden">
                <div class="form-group">
                    <label class="form-label" for="regFullname">Họ và tên</label>
                    <input type="text" name="fullname" id="regFullname" required placeholder="Nguyễn Văn A" class="form-input"/>
                </div>
                <div class="form-group">
                    <label class="form-label" for="regUsername">Tên đăng nhập</label>
                    <input type="text" name="username" id="regUsername" required placeholder="Nhập tên đăng nhập viết liền" class="form-input"/>
                </div>
                <div class="form-group">
                    <label class="form-label" for="regPassword">Mật khẩu</label>
                    <input type="password" name="password" id="regPassword" required placeholder="Tạo mật khẩu" class="form-input"/>
                </div>
                <div class="form-group">
                    <label class="form-label" for="regPhone">Số điện thoại giao hàng</label>
                    <input type="text" name="phone" id="regPhone" required placeholder="Ví dụ: 0912345678" class="form-input"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Địa chỉ giao hàng mặc định</label>
                    <select id="provinceSelect" class="form-input" style="margin-bottom: 0.5rem;" required>
                        <option value="" disabled selected>Chọn Tỉnh / Thành phố</option>
                    </select>
                    <select id="districtSelect" class="form-input" style="margin-bottom: 0.5rem;" required disabled>
                        <option value="" disabled selected>Chọn Quận / Huyện</option>
                    </select>
                    <select id="wardSelect" class="form-input" style="margin-bottom: 0.5rem;" required disabled>
                        <option value="" disabled selected>Chọn Phường / Xã</option>
                    </select>
                    <input type="text" id="addressDetail" placeholder="Số nhà, tên đường..." class="form-input" required>
                    <input type="hidden" name="address" id="regAddress">
                </div>
                <button type="submit" name="action_register" class="btn-auth btn-auth--secondary">TẠO TÀI KHOẢN</button>
            </form>

            <!-- Thông báo lỗi -->
            <?php if (!empty($login_error)): ?>
                <div id="authError" class="auth-error"><?php echo htmlspecialchars($login_error); ?></div>
            <?php else: ?>
                <div id="authError" class="auth-error hidden">Sai tên đăng nhập hoặc mật khẩu!</div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/auth.js?v=2.0"></script>
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
            
            // Do not hide php generated error on initial load unless tab switches
            if (event && event.type === 'click') {
                authError.classList.add('hidden');
            }
        }
        tabLogin.onclick    = (e) => setTab('login');
        tabRegister.onclick = (e) => setTab('register');

        // Handle error states preservation
        <?php if (isset($_POST['action_register'])): ?>
        setTab('register');
        <?php endif; ?>

        // Address API logic (using local JSON for speed and reliability)
        const provinceSelect = document.getElementById('provinceSelect');
        const districtSelect = document.getElementById('districtSelect');
        const wardSelect = document.getElementById('wardSelect');
        let vnData = [];
        
        if (provinceSelect) {
            fetch('https://provinces.open-api.vn/api/?depth=3')
                .then(res => res.json())
                .then(data => {
                    vnData = data;
                    data.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.code;
                        opt.text = p.name;
                        provinceSelect.add(opt);
                    });
                })
                .catch(err => console.error(err));

            provinceSelect.addEventListener('change', function() {
                const code = this.value;
                districtSelect.innerHTML = '<option value="" disabled selected>Chọn Quận / Huyện</option>';
                wardSelect.innerHTML = '<option value="" disabled selected>Chọn Phường / Xã</option>';
                districtSelect.disabled = true;
                wardSelect.disabled = true;
                
                const p = vnData.find(x => String(x.code) === String(code));
                if (p && p.districts) {
                    p.districts.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.code;
                        opt.text = d.name;
                        districtSelect.add(opt);
                    });
                    districtSelect.disabled = false;
                }
            });

            districtSelect.addEventListener('change', function() {
                const code = this.value;
                wardSelect.innerHTML = '<option value="" disabled selected>Chọn Phường / Xã</option>';
                wardSelect.disabled = true;

                const pCode = provinceSelect.value;
                const p = vnData.find(x => String(x.code) === String(pCode));
                if (p && p.districts) {
                    const d = p.districts.find(x => String(x.code) === String(code));
                    if (d && d.wards) {
                        d.wards.forEach(w => {
                            const opt = document.createElement('option');
                            opt.value = w.code;
                            opt.text = w.name;
                            wardSelect.add(opt);
                        });
                        wardSelect.disabled = false;
                    }
                }
            });
        }

        // On register form submit, build address JSON
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                const addressDetail = document.getElementById('addressDetail');
                if (provinceSelect && districtSelect && wardSelect && addressDetail) {
                    const addressObj = {
                        provinceCode: provinceSelect.value,
                        provinceName: provinceSelect.options[provinceSelect.selectedIndex].text,
                        districtCode: districtSelect.value,
                        districtName: districtSelect.options[districtSelect.selectedIndex].text,
                        wardCode: wardSelect.value,
                        wardName: wardSelect.options[wardSelect.selectedIndex].text,
                        detail: addressDetail.value.trim(),
                        full: addressDetail.value.trim() + ", " + wardSelect.options[wardSelect.selectedIndex].text + ", " + districtSelect.options[districtSelect.selectedIndex].text + ", " + provinceSelect.options[provinceSelect.selectedIndex].text
                    };
                    document.getElementById('regAddress').value = JSON.stringify(addressObj);
                }
            });
        }

    });
    </script>
</body>
</html>



