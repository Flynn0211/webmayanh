// File: js/giohang.js

document.addEventListener("DOMContentLoaded", () => {
    const user = getCurrentUser();
    if (!user) {
        alert("Vui lòng đăng nhập để xem giỏ hàng!");
        window.location.href = 'login.html';
        return;
    }

    const cartKey = `cart_${user.username}`;
    const cartContainer = document.getElementById('cartItemsContainer');
    const subtotalElem = document.getElementById('cartSubtotal');
    const totalElem = document.getElementById('cartTotal');
    const badge = document.getElementById('cartBadgeCart');

    function formatPriceLocal(priceStr) {
        const digits = String(priceStr).replace(/\D/g, '');
        if (digits) {
            const number = parseInt(digits, 10);
            return number.toLocaleString('vi-VN') + ' ₫';
        }
        return priceStr;
    }

    // Sanitize cart to remove huge base64 images that cause quota errors
    try {
        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
        let modified = false;
        cart.forEach(item => {
            if (item.image && item.image.length > 500) {
                delete item.image;
                modified = true;
            }
        });
        if (modified) localStorage.setItem(cartKey, JSON.stringify(cart));
    } catch(e) {}

    function renderCart() {
        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
        
        // Update badge
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        if (badge) {
            if (totalItems > 0) {
                badge.innerText = totalItems;
                badge.classList.remove('hidden');
                badge.classList.add('flex', 'items-center', 'justify-center', 'text-[10px]', 'text-on-primary', 'w-4', 'h-4', '-top-1', '-right-1');
            } else {
                badge.classList.add('hidden');
            }
        }

        if (cart.length === 0) {
            cartContainer.innerHTML = `<div class="text-center py-20 bg-surface-container-low rounded-xl border border-outline-variant/30">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant mb-4">production_quantity_limits</span>
                <p class="font-body-md text-on-surface-variant">Giỏ hàng của bạn đang trống.</p>
            </div>`;
            subtotalElem.innerText = '0 ₫';
            totalElem.innerText = '0 ₫';
            return;
        }

        let html = '';
        let total = 0;

        const products = JSON.parse(localStorage.getItem('products')) || [];

        cart.forEach((item, index) => {
            const rawPrice = parseInt(String(item.price).replace(/\D/g, '')) || 0;
            total += rawPrice * item.quantity;
            
            // Lấy ảnh từ products gốc để tránh lưu mảng ảnh khổng lồ trong cart
            const originalProd = products.find(p => p.id == item.id);
            const displayImage = originalProd ? originalProd.image : (item.image || '');

            html += `
            <div class="flex flex-col sm:flex-row items-center gap-6 py-6 border-b border-outline-variant/30 relative">
                <!-- Hình ảnh -->
                <div class="w-32 h-32 bg-surface-container-low flex-shrink-0 flex items-center justify-center p-2 rounded-lg">
                    <img src="${displayImage}" alt="${item.name}" class="w-full h-full object-contain mix-blend-multiply">
                </div>
                
                <!-- Thông tin -->
                <div class="flex-grow flex flex-col items-center sm:items-start text-center sm:text-left">
                    <h3 class="font-body-lg text-lg text-on-surface mb-1">${item.name}</h3>
                    <span class="font-label-caps text-[10px] tracking-widest text-on-surface-variant uppercase">${item.brand} FINISH</span>
                </div>

                <!-- Chỉnh số lượng -->
                <div class="flex items-center border border-outline-variant/50 rounded-sm">
                    <button onclick="changeQty(${index}, -1)" class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-primary transition-colors cursor-pointer">-</button>
                    <span class="w-8 text-center font-mono-spec text-sm">${item.quantity}</span>
                    <button onclick="changeQty(${index}, 1)" class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-primary transition-colors cursor-pointer">+</button>
                </div>

                <!-- Giá -->
                <div class="w-32 text-center sm:text-right font-mono-spec font-medium text-sm">
                    ${formatPriceLocal(rawPrice * item.quantity)}
                </div>

                <!-- Xóa -->
                <button onclick="removeItem(${index})" class="absolute top-6 right-0 sm:static sm:ml-4 text-on-surface-variant hover:text-error transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            `;
        });

        cartContainer.innerHTML = html;
        subtotalElem.innerText = formatPriceLocal(total);
        totalElem.innerText = formatPriceLocal(total);
    }

    // Gắn vào window để gọi từ inline onclick
    window.changeQty = function(index, delta) {
        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
        if (cart[index]) {
            const currentStock = cart[index].stock !== undefined ? cart[index].stock : 10;
            const newQty = cart[index].quantity + delta;
            
            if (newQty > currentStock) {
                alert(`Xin lỗi, sản phẩm này chỉ còn tối đa ${currentStock} cái trong kho.`);
                return;
            }
            
            cart[index].quantity = newQty;
            
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1); // Xóa nếu sl = 0
            }
            localStorage.setItem(cartKey, JSON.stringify(cart));
            renderCart();
        }
    };

    window.removeItem = function(index) {
        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
        if (cart[index]) {
            cart.splice(index, 1);
            localStorage.setItem(cartKey, JSON.stringify(cart));
            renderCart();
        }
    };

    // Checkout Logic
    document.getElementById('btnCheckout').onclick = function() {
        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
        if (cart.length === 0) {
            alert("Giỏ hàng trống! Không thể thanh toán.");
            return;
        }

        // Tính tổng tiền lại
        let total = 0;
        cart.forEach(item => {
            const rawPrice = parseInt(String(item.price).replace(/\D/g, '')) || 0;
            total += rawPrice * item.quantity;
        });

        // Tạo order
        const order = {
            id: 'ORD' + Date.now(),
            customerName: user.fullname,
            customerUsername: user.username,
            items: cart,
            total: formatPriceLocal(total),
            status: 'Đang xử lý', // 'Đang xử lý', 'Đã giao'
            date: new Date().toLocaleString('vi-VN')
        };

        let orders = JSON.parse(localStorage.getItem('orders')) || [];
        orders.push(order);
        localStorage.setItem('orders', JSON.stringify(orders));

        // Trừ số lượng tồn kho trong database
        let products = JSON.parse(localStorage.getItem('products')) || [];
        cart.forEach(cartItem => {
            const pIndex = products.findIndex(p => p.id == cartItem.id);
            if (pIndex > -1) {
                let currentStock = products[pIndex].stock !== undefined ? products[pIndex].stock : 10;
                products[pIndex].stock = Math.max(0, currentStock - cartItem.quantity);
            }
        });
        localStorage.setItem('products', JSON.stringify(products));

        // Xóa giỏ hàng
        localStorage.removeItem(cartKey);

        alert("Đặt hàng thành công! Cảm ơn bạn đã mua sắm tại LENS & LIGHT.");
        window.location.href = 'trangchu.html';
    };

    renderCart();
});
