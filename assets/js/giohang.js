// File: js/giohang.js

document.addEventListener("DOMContentLoaded", () => {
    const user = getCurrentUser();
    if (!user) {
        alert("Vui lòng đăng nhập để xem giỏ hàng!");
        window.location.href = 'index.php?page=login';
        return;
    }

    const cartKey       = `cart_${user.username}`;
    const cartContainer = document.getElementById('cartItemsContainer');
    const subtotalElem  = document.getElementById('cartSubtotal');
    const totalElem     = document.getElementById('cartTotal');

    function formatPriceLocal(priceStr) {
        const digits = String(priceStr).replace(/\D/g, '');
        if (digits) return parseInt(digits, 10).toLocaleString('vi-VN') + ' ₫';
        return priceStr;
    }

    // Dọn ảnh base64 quá nặng
    try {
        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
        let modified = false;
        cart.forEach(item => { if (item.image && item.image.length > 500) { delete item.image; modified = true; } });
        if (modified) localStorage.setItem(cartKey, JSON.stringify(cart));
    } catch(e) {}

    function renderCart() {
        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];

        // Update navbar badge
        const badge      = document.getElementById('cartBadge');
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        if (badge) {
            if (totalItems > 0) { badge.innerText = totalItems; badge.classList.remove('hidden'); }
            else { badge.classList.add('hidden'); }
        }

        if (cart.length === 0) {
            cartContainer.innerHTML = `
            <div class="cart-empty">
                <span class="material-symbols-outlined">production_quantity_limits</span>
                <p>Giỏ hàng của bạn đang trống.</p>
            </div>`;
            subtotalElem.innerText = '0 ₫';
            totalElem.innerText    = '0 ₫';
            return;
        }

        let html  = '';
        let total = 0;
        const products = JSON.parse(localStorage.getItem('products')) || [];

        cart.forEach((item, index) => {
            const rawPrice = parseInt(String(item.price).replace(/\D/g, '')) || 0;
            total += rawPrice * item.quantity;
            const origProd     = products.find(p => p.id == item.id);
            const displayImage = origProd ? origProd.image : (item.image || '');

            html += `
            <div class="cart-item">
                <div class="cart-item__img-wrap">
                    <img src="${displayImage}" alt="${item.name}" class="cart-item__img">
                </div>
                <div class="cart-item__info">
                    <h3 class="cart-item__name">${item.name}</h3>
                    <span class="cart-item__brand">${item.brand} FINISH</span>
                </div>
                <div class="cart-item__qty">
                    <button onclick="changeQty(${index}, -1)" class="cart-item__qty-btn">−</button>
                    <span class="cart-item__qty-val">${item.quantity}</span>
                    <button onclick="changeQty(${index}, 1)"  class="cart-item__qty-btn">+</button>
                </div>
                <div class="cart-item__price">${formatPriceLocal(rawPrice * item.quantity)}</div>
                <button onclick="removeItem(${index})" class="cart-item__remove">
                    <span class="material-symbols-outlined" style="font-size:1.25rem;">close</span>
                </button>
            </div>`;
        });

        cartContainer.innerHTML  = html;
        subtotalElem.innerText   = formatPriceLocal(total);
        totalElem.innerText      = formatPriceLocal(total);
    }

    window.changeQty = function(index, delta) {
        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
        if (cart[index]) {
            const maxStock = cart[index].stock !== undefined ? cart[index].stock : 10;
            const newQty   = cart[index].quantity + delta;
            if (newQty > maxStock) { alert(`Xin lỗi, sản phẩm này chỉ còn tối đa ${maxStock} cái trong kho.`); return; }
            cart[index].quantity = newQty;
            if (cart[index].quantity <= 0) cart.splice(index, 1);
            localStorage.setItem(cartKey, JSON.stringify(cart));
            renderCart();
        }
    };

    window.removeItem = function(index) {
        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
        if (cart[index]) { cart.splice(index, 1); localStorage.setItem(cartKey, JSON.stringify(cart)); renderCart(); }
    };

    document.getElementById('btnCheckout').onclick = function() {
        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
        if (cart.length === 0) { alert("Giỏ hàng trống! Không thể thanh toán."); return; }

        let total = 0;
        cart.forEach(item => { total += (parseInt(String(item.price).replace(/\D/g, '')) || 0) * item.quantity; });

        const checkoutData = {
            customerName: user.fullname,
            customerUsername: user.username,
            items: cart,
            totalRaw: total
        };

        fetch('index.php?action=checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(checkoutData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Trừ tồn kho local (vì trang chủ vẫn dùng localProducts)
                let prods = JSON.parse(localStorage.getItem('products')) || [];
                cart.forEach(ci => {
                    const pi = prods.findIndex(p => p.id == ci.id);
                    if (pi > -1) prods[pi].stock = Math.max(0, (prods[pi].stock || 10) - ci.quantity);
                });
                localStorage.setItem('products', JSON.stringify(prods));

                localStorage.removeItem(cartKey);
                alert("Đặt hàng thành công! Cảm ơn bạn đã mua sắm tại LENS & LIGHT. (Mã đơn: " + data.order_id + ")");
                window.location.href = 'index.php?page=trangchu';
            } else {
                alert("Lỗi khi thanh toán: " + data.message);
            }
        })
        .catch(err => {
            alert("Đã xảy ra lỗi mạng. Vui lòng thử lại!");
            console.error(err);
        });
    };

    renderCart();
});

