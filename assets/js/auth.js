// File: js/auth.js

// Khởi tạo dữ liệu mặc định khi script load
(function initAuthData() {
    let users = [];
    try {
        users = JSON.parse(localStorage.getItem('users')) || [];
    } catch(e) {}
    
    const hasAdmin = users.some(u => u.username === 'admin');
    if (!hasAdmin) {
        users.push({ username: 'admin', password: '123', role: 'admin', fullname: 'Administrator' });
        localStorage.setItem('users', JSON.stringify(users));
    }
})();

function login(username, password) {
    let users = [];
    try {
        users = JSON.parse(localStorage.getItem('users')) || [];
    } catch(e) {}
    
    let uName = String(username).trim();
    let pWord = String(password).trim();

    // Bắt buộc xử lý cứng tài khoản admin để tránh lỗi dữ liệu cũ trong localStorage
    if (uName === 'admin' && pWord === '123') {
        const adminUser = { username: 'admin', password: '123', role: 'admin', fullname: 'Administrator' };
        localStorage.setItem('currentUser', JSON.stringify(adminUser));
        
        // Đảm bảo cập nhật lại hoặc tạo mới admin trong mảng users luôn nếu bị sai
        const existingAdminIndex = users.findIndex(u => u && u.username === 'admin');
        if (existingAdminIndex !== -1) {
            users[existingAdminIndex] = adminUser;
        } else {
            users.push(adminUser);
        }
        localStorage.setItem('users', JSON.stringify(users));
        return true;
    }

    const user = users.find(u => u && u.username === uName && u.password === pWord);
    if (user) {
        localStorage.setItem('currentUser', JSON.stringify(user));
        return true;
    }
    return false;
}

function register(fullname, username, password) {
    const users = JSON.parse(localStorage.getItem('users')) || [];
    if (users.some(u => u.username === username)) {
        return false; // Tài khoản đã tồn tại
    }
    const newUser = { username, password, role: 'user', fullname };
    users.push(newUser);
    localStorage.setItem('users', JSON.stringify(users));
    
    // Tự động login sau khi đăng ký
    localStorage.setItem('currentUser', JSON.stringify(newUser));
    return true;
}

function logout() {
    localStorage.removeItem('currentUser');
    window.location.href = 'index.php?page=trangchu';
}

function getCurrentUser() {
    const userStr = localStorage.getItem('currentUser');
    if (!userStr || userStr === '[object Object]') {
        localStorage.removeItem('currentUser');
        return null;
    }
    try {
        return JSON.parse(userStr);
    } catch (e) {
        localStorage.removeItem('currentUser');
        return null;
    }
}

// Cập nhật giao diện thanh Navbar dựa theo trạng thái Đăng nhập
function updateAuthUI() {
    const currentUser = getCurrentUser();
    const loginBtns = document.querySelectorAll('.auth-login-btn'); 
    const adminBtns = document.querySelectorAll('.auth-admin-btn');
    const profileBtns = document.querySelectorAll('.auth-profile-btn');
    const logoutBtns = document.querySelectorAll('.auth-logout-btn');
    
    if (currentUser) {
        // Trạng thái đã đăng nhập: ẩn nút Đăng nhập, hiện nút Tài khoản & Đăng xuất
        loginBtns.forEach(btn => {
            btn.classList.add('hidden');
        });
        profileBtns.forEach(btn => {
            btn.classList.remove('hidden');
            btn.title = `Tài khoản của ${currentUser.fullname}`;
        });
        logoutBtns.forEach(btn => {
            btn.classList.remove('hidden');
            btn.title = `Đang đăng nhập: ${currentUser.fullname}`;
            btn.onclick = (e) => {
                e.preventDefault();
                logout();
            };
        });
        
        // Kiểm tra quyền Admin
        adminBtns.forEach(btn => {
            if (currentUser.role === 'admin') {
                btn.classList.remove('hidden'); // Hiện
            } else {
                btn.classList.add('hidden'); // Ẩn
            }
        });
    } else {
        // Trạng thái chưa đăng nhập: hiện nút Đăng nhập, ẩn các nút còn lại
        loginBtns.forEach(btn => {
            btn.classList.remove('hidden');
        });
        profileBtns.forEach(btn => {
            btn.classList.add('hidden');
        });
        logoutBtns.forEach(btn => {
            btn.classList.add('hidden');
        });
        
        // Luôn ẩn nút Quản trị nếu chưa login
        adminBtns.forEach(btn => {
            btn.classList.add('hidden');
        });
    }
}

// Chạy updateAuthUI() ngay khi DOM sẵn sàng.
// Nếu script load sau DOMContentLoaded (cuối body), gọi trực tiếp luôn.
if (document.readyState === 'loading') {
    document.addEventListener("DOMContentLoaded", updateAuthUI);
} else {
    updateAuthUI();
}

