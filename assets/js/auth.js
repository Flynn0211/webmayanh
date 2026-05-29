// File: js/auth.js
// Fake login and register have been removed because they are handled securely by backend PHP (login.php)



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
window.isFavorited = function(productId) {
    const user = getCurrentUser();
    if (!user) return false;
    const favKey = `favorites_${user.username}`;
    let favs = [];
    try { favs = JSON.parse(localStorage.getItem(favKey)) || []; } catch(e){}
    return favs.includes(String(productId));
};

window.handleFavorite = function(productId, btnElement) {
    const user = getCurrentUser();
    if (!user) {
        alert("Vui lòng đăng nhập để thêm vào Yêu thích!");
        window.location.href = 'index.php?page=login';
        return;
    }
    const favKey = `favorites_${user.username}`;
    let favs = [];
    try { favs = JSON.parse(localStorage.getItem(favKey)) || []; } catch(e){}
    
    let isFav = false;
    const idx = favs.indexOf(String(productId));
    if (idx > -1) {
        favs.splice(idx, 1);
        isFav = false;
    } else {
        favs.push(String(productId));
        isFav = true;
    }
    localStorage.setItem(favKey, JSON.stringify(favs));
    
    if (btnElement) {
        const icon = btnElement.querySelector('.material-symbols-outlined') || btnElement;
        if (icon && icon.classList.contains('material-symbols-outlined')) {
            icon.style.color = isFav ? 'var(--error)' : 'inherit';
            icon.style.fontVariationSettings = `"FILL" ${isFav ? 1 : 0}`;
        }
    }
};
