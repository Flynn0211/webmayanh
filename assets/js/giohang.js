/**
 * Tệp tin: giohang.js
 * Chức năng: Xử lý logic và nghiệp vụ liên quan đến giohang
 * Tác giả: Nhóm Lập Trình Web Nâng Cao
 */

// Tệp tin: js/giohang.js

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

    window.appliedVoucher = null;
    window.shippingFee = 0;

    function renderCart() {
        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];

        // Update navbar badge
        const badge      = document.getElementById('cartBadge');
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        if (badge) {
            if (totalItems > 0) { badge.innerText = totalItems; badge.classList.remove('hidden'); }
            else { badge.classList.add('hidden'); }
        }

        const summaryElem = document.querySelector('.cart-summary');

        if (cart.length === 0) {
            cartContainer.innerHTML = `
            <div class="cart-empty">
                <span class="material-symbols-outlined">production_quantity_limits</span>
                <p>Giỏ hàng của bạn đang trống.</p>
            </div>`;
            subtotalElem.innerText = '0 ₫';
            totalElem.innerText    = '0 ₫';
            if (summaryElem) summaryElem.style.display = 'none';
            return;
        } else {
            if (summaryElem) summaryElem.style.display = 'block';
        }

        const products = window.dbProducts || JSON.parse(localStorage.getItem('products')) || [];
        
        let cartUpdated = false;
        cart.forEach(item => {
            const currentProduct = products.find(p => String(p.id) === String(item.id));
            if (currentProduct) {
                if (currentProduct.price !== item.price) {
                    item.price = currentProduct.price;
                    cartUpdated = true;
                }
                if (currentProduct.stock !== undefined && currentProduct.stock !== item.stock) {
                    item.stock = currentProduct.stock;
                    cartUpdated = true;
                }
            }
        });
        if (cartUpdated) {
            localStorage.setItem(cartKey, JSON.stringify(cart));
        }

        let html  = '';
        let total = 0;
        let totalRawAll = 0;
        cart.forEach(item => {
            totalRawAll += (parseInt(String(item.price).replace(/\D/g, '')) || 0) * item.quantity;
        });

        let discountRemaining = 0;
        if (window.appliedVoucher) {
            if (window.appliedVoucher.type === 'PhanTram') {
                discountRemaining = totalRawAll * (window.appliedVoucher.value / 100);
            } else {
                discountRemaining = window.appliedVoucher.value;
            }
        }

        cart.forEach((item, index) => {
            const rawPrice = parseInt(String(item.price).replace(/\D/g, '')) || 0;
            const itemTotalOriginal = rawPrice * item.quantity;
            let itemTotalDiscounted = itemTotalOriginal;
            
            if (discountRemaining > 0) {
                const deduct = Math.min(itemTotalOriginal, discountRemaining);
                itemTotalDiscounted -= deduct;
                discountRemaining -= deduct;
            }
            
            const origProd     = products.find(p => String(p.id) === String(item.id));
            const displayImage = origProd ? origProd.image : (item.image || '');

            let displayPriceHtml = '';
            if (itemTotalDiscounted < itemTotalOriginal) {
                const currentUnitPrice = itemTotalDiscounted / item.quantity;
                displayPriceHtml = `<div style="display:flex; flex-direction:column; align-items:flex-end;">
                                        <span style="text-decoration: line-through; font-size: 0.85em; color: #888;">${formatPriceLocal(rawPrice * item.quantity)}</span>
                                        <span style="color: var(--primary); font-weight: bold;">${formatPriceLocal(itemTotalDiscounted)}</span>
                                    </div>`;
            } else {
                if (origProd && origProd.raw_original_price > origProd.raw_price) {
                    const originalTotal = origProd.raw_original_price * item.quantity;
                    displayPriceHtml = `<div style="display:flex; flex-direction:column; align-items:flex-end;">
                                            <span style="text-decoration: line-through; font-size: 0.85em; color: #888;">${formatPriceLocal(originalTotal)}</span>
                                            <span style="color: var(--primary); font-weight: bold;">${formatPriceLocal(rawPrice * item.quantity)}</span>
                                        </div>`;
                } else {
                    displayPriceHtml = `<span>${formatPriceLocal(rawPrice * item.quantity)}</span>`;
                }
            }
            
            total += itemTotalDiscounted;

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
                <div class="cart-item__price">${displayPriceHtml}</div>
                <button onclick="removeItem(${index})" class="cart-item__remove">
                    <span class="material-symbols-outlined" style="font-size:1.25rem;">close</span>
                </button>
            </div>`;
        });

        cartContainer.innerHTML  = html;
        subtotalElem.innerText   = formatPriceLocal(total);
        
        let finalTotal = total;
        const discountRow = document.getElementById('cartDiscountRow');
        
        if (window.discountPercent && window.discountPercent > 0) {
            const discountAmt = total * (window.discountPercent / 100);
            finalTotal = total - discountAmt;
            
            if (!discountRow) {
                const row = document.createElement('div');
                row.className = 'cart-summary__row';
                row.id = 'cartDiscountRow';
                row.innerHTML = `<span class="cart-summary__row-label" style="color: var(--primary);">GIẢM THÀNH VIÊN (${window.userTier} -${window.discountPercent}%)</span>
                                 <span class="text-mono-spec" style="color: var(--primary);">- ${formatPriceLocal(discountAmt)}</span>`;
                const divider = document.querySelector('.cart-summary__divider');
                divider.parentNode.insertBefore(row, divider);
            } else {
                discountRow.innerHTML = `<span class="cart-summary__row-label" style="color: var(--primary);">GIẢM THÀNH VIÊN (${window.userTier} -${window.discountPercent}%)</span>
                                         <span class="text-mono-spec" style="color: var(--primary);">- ${formatPriceLocal(discountAmt)}</span>`;
                discountRow.style.display = 'flex';
            }
        }
        
        // Cộng thêm phí ship
        finalTotal += window.shippingFee || 0;
        const feeElem = document.getElementById('cartShippingFee');
        if (feeElem) {
            feeElem.innerText = (window.shippingFee > 0) ? formatPriceLocal(window.shippingFee) : 'CHƯA TÍNH';
        }
        
        totalElem.innerText = formatPriceLocal(finalTotal);
    }
    
    // Fetch user profile to get membership tier before initial render
    window.discountPercent = 0;
    window.userTier = 'None';
    fetch('index.php?action=get_profile')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.profile) {
                window.userTier = data.profile.hang_thanh_vien;
                if (window.userTier === 'Silver') window.discountPercent = 2;
                if (window.userTier === 'Gold') window.discountPercent = 5;
                if (window.userTier === 'Diamond') window.discountPercent = 10;
            }
            renderCart();
        })
        .catch(() => renderCart());

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

    const btnApplyVoucher = document.getElementById('btnApplyVoucher');
    if (btnApplyVoucher) {
        btnApplyVoucher.onclick = function() {
            const voucherInput = document.getElementById('voucherCode');
            const msgElem = document.getElementById('voucherMessage');
            const code = voucherInput.value.trim();
            
            if (!code) {
                msgElem.innerHTML = '<span style="color:red">Vui lòng nhập mã giảm giá.</span>';
                window.appliedVoucher = null;
                renderCart();
                return;
            }
            
            let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
            let totalRaw = 0;
            cart.forEach(item => { totalRaw += (parseInt(String(item.price).replace(/\D/g, '')) || 0) * item.quantity; });
            
            fetch('index.php?action=check_voucher', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code: code, totalRaw: totalRaw })
            })
            .then(res => res.json())
            .then(data => {
                if (data.valid) {
                    window.appliedVoucher = {
                        code: code,
                        type: data.type,
                        value: data.value
                    };
                    msgElem.innerHTML = `<span style="color:green">${data.message}</span>`;
                    renderCart();
                } else {
                    window.appliedVoucher = null;
                    msgElem.innerHTML = `<span style="color:red">${data.message}</span>`;
                    renderCart();
                }
            })
            .catch(err => {
                msgElem.innerHTML = '<span style="color:red">Lỗi kết nối.</span>';
                console.error(err);
            });
        };
    }

    document.getElementById('btnCheckout').onclick = function() {
        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
        if (cart.length === 0) { alert("Giỏ hàng trống! Không thể thanh toán."); return; }

        let total = 0;
        cart.forEach(item => { total += (parseInt(String(item.price).replace(/\D/g, '')) || 0) * item.quantity; });

        const phoneInput = document.getElementById('customerPhone');
        const voucherInput = document.getElementById('voucherCode');
        const addressDetail = document.getElementById('addressDetail');
        const provinceSel = document.getElementById('provinceSelect');
        const districtSel = document.getElementById('districtSelect');
        const wardSel = document.getElementById('wardSelect');
        
        let phone = phoneInput ? phoneInput.value.trim() : '';
        if (!phone) {
            alert("Vui lòng nhập số điện thoại giao hàng.");
            if (phoneInput) phoneInput.focus();
            return;
        }

        if (!provinceSel.value || !districtSel.value || !wardSel.value || !addressDetail.value.trim()) {
            alert("Vui lòng nhập đầy đủ địa chỉ giao hàng (Tỉnh, Quận, Phường, Số nhà).");
            return;
        }
        
        const fullAddress = addressDetail.value.trim() + ", " 
            + wardSel.options[wardSel.selectedIndex].text + ", " 
            + districtSel.options[districtSel.selectedIndex].text + ", " 
            + provinceSel.options[provinceSel.selectedIndex].text;
            
        let paymentMethod = 'COD';
        const paymentRadios = document.getElementsByName('paymentMethod');
        if (paymentRadios) {
            for (let radio of paymentRadios) {
                if (radio.checked) {
                    paymentMethod = radio.value;
                    break;
                }
            }
        }

        const checkoutData = {
            customerName: user.fullname,
            customerUsername: user.username,
            customerPhone: phone,
            customerAddress: fullAddress,
            paymentMethod: paymentMethod,
            shippingFee: window.shippingFee || 0,
            voucherCode: window.appliedVoucher ? window.appliedVoucher.code : (voucherInput ? voucherInput.value.trim() : ''),
            items: cart,
            totalRaw: total
        };

        const btnCheckout = document.getElementById('btnCheckout');
        const originalText = btnCheckout.innerText;
        btnCheckout.innerText = 'ĐANG XỬ LÝ...';
        btnCheckout.disabled = true;
        btnCheckout.style.opacity = '0.7';
        btnCheckout.style.cursor = 'not-allowed';

        fetch('index.php?action=checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(checkoutData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Trừ tồn kho local (vì trang chủ vẫn dùng localProducts)
                let prods = window.dbProducts || JSON.parse(localStorage.getItem('products')) || [];
                cart.forEach(ci => {
                    const pi = prods.findIndex(p => p.id == ci.id);
                    if (pi > -1) prods[pi].stock = Math.max(0, (prods[pi].stock || 10) - ci.quantity);
                });
                localStorage.setItem('products', JSON.stringify(prods));

                localStorage.removeItem(cartKey);
                
                if (data.paymentMethod === 'BankTransfer' && data.totalThanhToan > 0) {
                    const qrUrl = `https://img.vietqr.io/image/970422-0523134391-compact2.png?amount=${data.totalThanhToan}&addInfo=THANH%20TOAN%20DH%20${data.order_id}&accountName=LE%20DUONG%20TUAN%20ANH`;
                    const overlay = document.createElement('div');
                    overlay.style.position = 'fixed';
                    overlay.style.top = '0';
                    overlay.style.left = '0';
                    overlay.style.width = '100vw';
                    overlay.style.height = '100vh';
                    overlay.style.backgroundColor = 'rgba(0,0,0,0.8)';
                    overlay.style.display = 'flex';
                    overlay.style.alignItems = 'center';
                    overlay.style.justifyContent = 'center';
                    overlay.style.zIndex = '999999';
                    
                    const modal = document.createElement('div');
                    modal.style.backgroundColor = '#fff';
                    modal.style.padding = '30px';
                    modal.style.borderRadius = '12px';
                    modal.style.textAlign = 'center';
                    modal.style.maxWidth = '400px';
                    modal.innerHTML = `
                        <h2 style="color: #ea580c; margin-top: 0; font-size: 1.5rem;">Đặt Hàng Thành Công!</h2>
                        <p style="color: #333; margin-bottom: 20px;">Mã đơn: <strong>${data.order_id}</strong>. Vui lòng quét mã bên dưới để thanh toán.</p>
                        <img src="${qrUrl}" alt="QR Code" style="max-width: 100%; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        <button id="btnCloseQR" style="margin-top: 20px; background: var(--primary, #ea580c); color: #fff; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;">Hoàn tất & Về Trang Chủ</button>
                    `;
                    overlay.appendChild(modal);
                    document.body.appendChild(overlay);

                    document.getElementById('btnCloseQR').onclick = () => {
                        window.location.href = 'index.php?page=trangchu';
                    };
                } else {
                    alert("Đặt hàng thành công! Cảm ơn bạn đã mua sắm tại LENS & LIGHT. (Mã đơn: " + data.order_id + ")");
                    window.location.href = 'index.php?page=trangchu';
                }
            } else {
                alert("Lỗi khi thanh toán: " + data.message);
                btnCheckout.innerText = originalText;
                btnCheckout.disabled = false;
                btnCheckout.style.opacity = '1';
                btnCheckout.style.cursor = 'pointer';
            }
        })
        .catch(err => {
            alert("Đã xảy ra lỗi mạng. Vui lòng thử lại!");
            console.error(err);
            btnCheckout.innerText = originalText;
            btnCheckout.disabled = false;
            btnCheckout.style.opacity = '1';
            btnCheckout.style.cursor = 'pointer';
        });
    };

    // --- ĐỊA GIỚI HÀNH CHÍNH & PHÍ SHIP ---
    const provinceSelect = document.getElementById('provinceSelect');
    const districtSelect = document.getElementById('districtSelect');
    const wardSelect = document.getElementById('wardSelect');
    
    if (provinceSelect) {
        let userAddrObj = null;
        if (window.clientAddressStr) {
            try { userAddrObj = JSON.parse(window.clientAddressStr); } catch(e){}
        }

        let vnData = [];

        function loadDistricts(provCode, selDist, selWard) {
            districtSelect.innerHTML = '<option value="" disabled selected>Chọn Quận / Huyện</option>';
            wardSelect.innerHTML = '<option value="" disabled selected>Chọn Phường / Xã</option>';
            districtSelect.disabled = true;
            wardSelect.disabled = true;
            if(!provCode) return;

            // Tính phí ship
            const pName = provinceSelect.options[provinceSelect.selectedIndex]?.text || '';
            if (pName.includes('Hà Nội') || pName.includes('Hồ Chí Minh')) {
                window.shippingFee = 20000;
            } else {
                window.shippingFee = 40000;
            }
            renderCart();

            const p = vnData.find(x => String(x.code) === String(provCode));
            if (p && p.districts) {
                p.districts.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.code;
                    opt.text = d.name;
                    districtSelect.add(opt);
                });
                districtSelect.disabled = false;
                if(selDist) {
                    districtSelect.value = selDist;
                    loadWards(selDist, selWard);
                }
            }
        }

        function loadWards(distCode, selWard) {
            wardSelect.innerHTML = '<option value="" disabled selected>Chọn Phường / Xã</option>';
            wardSelect.disabled = true;
            if(!distCode) return;
            
            const pCode = provinceSelect.value;
            const p = vnData.find(x => String(x.code) === String(pCode));
            if (p && p.districts) {
                const d = p.districts.find(x => String(x.code) === String(distCode));
                if (d && d.wards) {
                    d.wards.forEach(w => {
                        const opt = document.createElement('option');
                        opt.value = w.code;
                        opt.text = w.name;
                        wardSelect.add(opt);
                    });
                    wardSelect.disabled = false;
                    if(selWard) {
                        wardSelect.value = selWard;
                    }
                }
            }
        }

        // Fetch Tỉnh/Thành từ local JSON
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

                if (userAddrObj && userAddrObj.provinceCode) {
                    provinceSelect.value = userAddrObj.provinceCode;
                    loadDistricts(userAddrObj.provinceCode, userAddrObj.districtCode, userAddrObj.wardCode);
                    const addressDetail = document.getElementById('addressDetail');
                    if (addressDetail && userAddrObj.detail) {
                        addressDetail.value = userAddrObj.detail;
                    }
                }
            })
            .catch(err => console.error("API Tỉnh Thành Lỗi: ", err));

        provinceSelect.addEventListener('change', function() {
            loadDistricts(this.value);
        });

        districtSelect.addEventListener('change', function() {
            loadWards(this.value);
        });
    }

    // Initial render is now called after fetching profile data (see above)
});

