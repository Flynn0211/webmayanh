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
    return userStr ? JSON.parse(userStr) : null;
}

// Cập nhật giao diện thanh Navbar dựa theo trạng thái Đăng nhập
function updateAuthUI() {
    const currentUser = getCurrentUser();
    // Các phần tử cần gắn class 'auth-login-btn' để JS tự tìm và thay đổi nội dung
    const loginBtns = document.querySelectorAll('.auth-login-btn'); 
    // Các phần tử cần gắn class 'auth-admin-btn' để JS tự tìm và ẩn/hiện
    const adminBtns = document.querySelectorAll('.auth-admin-btn');
    
    if (currentUser) {
        // Trạng thái đã đăng nhập
        loginBtns.forEach(btn => {
            btn.innerHTML = `<span class="material-symbols-outlined text-[16px]">logout</span> Đăng xuất`;
            btn.title = `Đang đăng nhập: ${currentUser.fullname}`;
            btn.href = "#";
            btn.onclick = (e) => {
                e.preventDefault();
                logout();
            };
        });
        
        // Kiểm tra quyền Admin
        adminBtns.forEach(btn => {
            if (currentUser.role === 'admin') {
                btn.style.display = ''; // Hiện
            } else {
                btn.style.display = 'none'; // Ẩn
            }
        });
    } else {
        // Trạng thái chưa đăng nhập
        loginBtns.forEach(btn => {
            btn.innerHTML = "Đăng nhập";
            btn.href = "index.php?page=login";
            btn.onclick = null;
        });
        
        // Luôn ẩn nút Quản trị nếu chưa login
        adminBtns.forEach(btn => {
            btn.style.display = 'none';
        });
    }
}

// Tự động chạy cập nhật UI khi Load xong HTML
document.addEventListener("DOMContentLoaded", updateAuthUI);

