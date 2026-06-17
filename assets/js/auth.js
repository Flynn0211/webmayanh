/**
 * Tệp tin: auth.js
 * Chức năng: Xử lý logic và nghiệp vụ liên quan đến auth
 * Tác giả: Nhóm Lập Trình Web Nâng Cao
 */

// Tệp tin: js/auth.js
// Fake login and register have been removed because they are handled securely by backend PHP (login.php)



// Lấy thông tin người dùng đang đăng nhập hiện tại từ LocalStorage
// Trả về object chứa thông tin hoặc null nếu chưa đăng nhập
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
// ── Quản Lý Yêu Thích (Favorites) ────────────────────────────────────────────────
// Hàm kiểm tra xem sản phẩm đã có trong danh sách yêu thích của người dùng hay chưa
window.isFavorited = function(productId) {
    const user = getCurrentUser();
    if (!user) return false;
    const favKey = `favorites_${user.username}`;
    let favs = [];
    try { favs = JSON.parse(localStorage.getItem(favKey)) || []; } catch(e){}
    return favs.includes(String(productId));
};

// Hàm xử lý việc thêm/xóa sản phẩm vào danh sách yêu thích
// Cập nhật lại LocalStorage và đổi màu icon (trái tim đỏ/trắng) ngay lập tức
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

// ── Mua Nhanh (Fast Add to Cart) ─────────────────────────────────────────────────
// Thêm trực tiếp sản phẩm vào giỏ hàng từ trang danh sách (trang chủ, máy ảnh,...)
// Có kiểm tra trạng thái đăng nhập và số lượng tồn kho (stock)
window.addToCartFast = function(productId) {
    const user = getCurrentUser();
    if (!user) {
        alert("Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!");
        window.location.href = 'index.php?page=login';
        return;
    }
    
    const products = window.dbProducts || JSON.parse(localStorage.getItem('products')) || [];
    const product = products.find(p => String(p.id) === String(productId));
    if (!product) return;

    const currentStock = product.stock !== undefined ? product.stock : 10;
    if (currentStock <= 0) {
        alert("Sản phẩm này đã hết hàng!");
        return;
    }

    const cartKey = `cart_${user.username}`;
    let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
    
    const existingItem = cart.find(item => item.id == product.id);
    if (existingItem) {
        if (existingItem.quantity >= currentStock) {
            alert(`Rất tiếc, bạn chỉ có thể mua tối đa ${currentStock} sản phẩm này!`);
            return;
        }
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            brand: product.brand,
            quantity: 1,
            stock: currentStock,
            image: product.image
        });
    }
    localStorage.setItem(cartKey, JSON.stringify(cart));
    alert("Đã thêm sản phẩm vào giỏ hàng!");
    if (window.updateCartBadge) window.updateCartBadge();
};
