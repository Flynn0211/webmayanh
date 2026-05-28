// ====== DATA ======
let products = JSON.parse(localStorage.getItem('products')) || [];

let mockOrders = [
    { id: 'DH001', customer: 'Nguyễn Văn A', total: '239,000,000 ₫', date: '28/05/2026', status: 'chờ xác nhận' },
    { id: 'DH002', customer: 'Trần Thị B', total: '43,000,000 ₫', date: '27/05/2026', status: 'đang giao' },
    { id: 'DH003', customer: 'Lê Văn C', total: '90,000,000 ₫', date: '26/05/2026', status: 'thành công' }
];
let mockCustomers = [
    { id: 'KH01', name: 'Nguyễn Văn A', tier: 'diamond', active: true },
    { id: 'KH02', name: 'Trần Thị B', tier: 'gold', active: true },
    { id: 'KH03', name: 'Lê Văn C', tier: 'silver', active: false }
];
let mockEmployees = [
    { id: 'NV01', name: 'Admin Tối Cao', role: 'Quản trị viên', active: true },
    { id: 'NV02', name: 'Phạm Nhân Sự', role: 'Nhân sự', active: true },
    { id: 'NV03', name: 'Lê Kế Toán', role: 'Kế toán', active: true }
];
let mockReviews = [
    { id: 1, product: 'Leica M11 Monochrom', user: 'Nguyễn Văn A', content: 'Chất lượng ảnh đen trắng tuyệt vời, thiết kế rất cổ điển.' },
    { id: 2, product: 'Sony Alpha 7R V', user: 'Trần Thị B', content: 'Lấy nét tự động cực nhanh, rất đáng tiền!' }
];
let mockVouchers = [
    { code: 'WELCOME10', discount: '10%', quantity: 100, expire: '31/12/2026' },
    { code: 'SUMMER20', discount: '20%', quantity: 50, expire: '30/06/2026' }
];
let mockPromos = [
    { product: 'Sony Alpha 7R V', discount: '15%' },
    { product: 'Fujifilm X-T5 Body', discount: '5%' }
];

// ====== TAB SWITCHING ======
const tabNames = {
    'revenue':    'Quản lý doanh thu',
    'orders':     'Quản lý đơn hàng',
    'products':   'Quản lý sản phẩm',
    'promotions': 'Khuyến mãi sản phẩm',
    'vouchers':   'Quản lý voucher',
    'customers':  'Quản lý khách hàng',
    'employees':  'Quản lý nhân viên',
    'reviews':    'Quản lý đánh giá'
};

function switchTab(tabId) {
    document.getElementById('pageTitle').innerText = tabNames[tabId];

    // Ẩn tất cả tabs
    document.querySelectorAll('.admin-tab').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tabId).classList.add('active');

    // Reset menu active
    document.querySelectorAll('.menu-item').forEach(el => el.classList.remove('active-menu'));
    document.getElementById('menu-' + tabId).classList.add('active-menu');

    renderTab(tabId);
}
window.switchTab = switchTab;

// ====== RENDERING ======
function renderTab(tabId) {
    if (tabId === 'products')   renderAdminProducts();
    else if (tabId === 'orders')      renderAdminOrders();
    else if (tabId === 'customers')   renderAdminCustomers();
    else if (tabId === 'employees')   renderAdminEmployees();
    else if (tabId === 'reviews')     renderAdminReviews();
    else if (tabId === 'vouchers')    renderAdminVouchers();
    else if (tabId === 'promotions')  renderAdminPromotions();
    else if (tabId === 'revenue')     renderAdminRevenue();
}

// ── Products ──────────────────────────────────────────────────
function renderAdminProducts() {
    const tbody = document.getElementById('adminProductTableBody');
    if (!tbody) return;
    if (products.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="td-muted" style="text-align:center;padding:2rem;">Không có sản phẩm nào.</td></tr>`;
        return;
    }
    tbody.innerHTML = products.map(p => `
        <tr>
            <td class="td-id center">#${p.id}</td>
            <td>
                <div class="product-thumb">
                    <img src="${p.image}" alt="${p.name}"/>
                </div>
            </td>
            <td class="td-primary text-label-caps">${p.brand}</td>
            <td class="td-bold td-truncate">${p.name}</td>
            <td class="td-mono right">${p.price}</td>
            <td class="td-mono center">${p.stock || 0}</td>
            <td class="center">
                <div class="action-group">
                    <button type="button" onclick="editProduct('${p.id}')" class="btn-table-action btn-table-action--edit" title="Sửa">
                        <span class="material-symbols-outlined" style="font-size:1.25rem;">edit_square</span>
                    </button>
                    <button type="button" onclick="deleteProduct('${p.id}')" class="btn-table-action btn-table-action--delete" title="Xóa">
                        <span class="material-symbols-outlined" style="font-size:1.25rem;">delete</span>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ── Orders ────────────────────────────────────────────────────
window.updateOrderStatus = function(orderId, newStatus) {
    let orders = JSON.parse(localStorage.getItem('orders')) || [];
    const index = orders.findIndex(o => o.id === orderId);
    if (index > -1) {
        orders[index].status = newStatus;
        localStorage.setItem('orders', JSON.stringify(orders));
        alert('Đã cập nhật trạng thái đơn hàng!');
        renderAdminOrders();
    }
};

function renderAdminOrders() {
    let orders   = JSON.parse(localStorage.getItem('orders')) || [];
    const statuses = ['Đang xử lý', 'Đã xác nhận', 'Đang giao', 'Đã giao', 'Thành công', 'Đã hủy'];

    if (orders.length === 0) {
        document.getElementById('adminOrderTableBody').innerHTML = `<tr><td colspan="5" class="td-muted" style="text-align:center;padding:2rem;">Chưa có đơn hàng nào.</td></tr>`;
        document.getElementById('newOrdersBadge').innerText = '0';
        return;
    }

    const sortedOrders = [...orders].reverse();
    document.getElementById('adminOrderTableBody').innerHTML = sortedOrders.map(o => `
        <tr>
            <td class="td-primary td-mono">${o.id}</td>
            <td>
                ${o.customerName}
                <div class="td-muted" style="margin-top:0.25rem;">@${o.customerUsername}</div>
            </td>
            <td class="td-mono">${o.total}</td>
            <td class="td-muted">${o.date}</td>
            <td class="center">
                <select onchange="updateOrderStatus('${o.id}', this.value)" class="order-status-select">
                    ${statuses.map(s => `<option ${s === o.status ? 'selected' : ''}>${s}</option>`).join('')}
                </select>
            </td>
        </tr>
    `).join('');

    document.getElementById('newOrdersBadge').innerText = orders.filter(o => o.status === 'Đang xử lý').length;
}

// ── Customers ─────────────────────────────────────────────────
function renderAdminCustomers() {
    document.getElementById('adminCustomerTableBody').innerHTML = mockCustomers.map(c => `
        <tr class="${!c.active ? 'disabled' : ''}">
            <td class="td-id">${c.id}</td>
            <td class="td-bold">${c.name}</td>
            <td><span class="tier-badge tier-badge--${c.tier}">${c.tier}</span></td>
            <td class="center">
                <div class="action-group">
                    <button class="btn-table-action btn-table-action--view" title="Xem">
                        <span class="material-symbols-outlined" style="font-size:1.25rem;">visibility</span>
                    </button>
                    <button class="btn-table-action ${c.active ? 'btn-table-action--lock' : 'btn-table-action--unlock'}"
                            title="${c.active ? 'Khóa' : 'Mở khóa'}"
                            onclick="alert('Đã thay đổi trạng thái khách hàng!')">
                        <span class="material-symbols-outlined" style="font-size:1.25rem;">${c.active ? 'lock' : 'lock_open'}</span>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ── Employees ─────────────────────────────────────────────────
function renderAdminEmployees() {
    document.getElementById('adminEmployeeTableBody').innerHTML = mockEmployees.map(e => `
        <tr class="${!e.active ? 'disabled' : ''}">
            <td class="td-id">${e.id}</td>
            <td class="td-bold">${e.name}</td>
            <td class="td-muted">${e.role}</td>
            <td class="center">
                <div class="action-group">
                    <button class="btn-table-action btn-table-action--view" title="Xem">
                        <span class="material-symbols-outlined" style="font-size:1.25rem;">visibility</span>
                    </button>
                    <button class="btn-table-action ${e.active ? 'btn-table-action--lock' : 'btn-table-action--unlock'}"
                            title="${e.active ? 'Khóa' : 'Mở khóa'}"
                            onclick="alert('Đã thay đổi trạng thái nhân viên!')">
                        <span class="material-symbols-outlined" style="font-size:1.25rem;">${e.active ? 'lock' : 'lock_open'}</span>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ── Reviews ───────────────────────────────────────────────────
function renderAdminReviews() {
    document.getElementById('adminReviewTableBody').innerHTML = mockReviews.map(r => `
        <tr>
            <td class="td-primary td-bold">${r.product}</td>
            <td class="td-muted">${r.user}</td>
            <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${r.content}</td>
            <td class="center">
                <button class="btn-table-action btn-table-action--delete" title="Xóa" onclick="alert('Đã xóa đánh giá!')">
                    <span class="material-symbols-outlined" style="font-size:1.25rem;">delete</span>
                </button>
            </td>
        </tr>
    `).join('');
}

// ── Vouchers ──────────────────────────────────────────────────
function renderAdminVouchers() {
    document.getElementById('adminVoucherTableBody').innerHTML = mockVouchers.map(v => `
        <tr>
            <td class="td-primary td-mono td-bold">${v.code}</td>
            <td>${v.discount}</td>
            <td class="td-muted">${v.quantity}</td>
            <td class="td-error">${v.expire}</td>
            <td class="center">
                <button class="btn-table-action btn-table-action--edit" title="Sửa">
                    <span class="material-symbols-outlined" style="font-size:1.25rem;">edit_square</span>
                </button>
            </td>
        </tr>
    `).join('');
}

// ── Promotions ────────────────────────────────────────────────
function renderAdminPromotions() {
    document.getElementById('adminPromoTableBody').innerHTML = mockPromos.map(p => `
        <tr>
            <td class="td-bold">${p.product}</td>
            <td class="td-primary td-bold">Giảm ${p.discount}</td>
            <td class="center">
                <button class="btn-table-action btn-table-action--delete" title="Xóa" onclick="alert('Đã gỡ khuyến mãi!')">
                    <span class="material-symbols-outlined" style="font-size:1.25rem;">delete</span>
                </button>
            </td>
        </tr>
    `).join('');
}

// ── Revenue ───────────────────────────────────────────────────
function renderAdminRevenue() {
    const bestSellers  = products.slice(0, 2);
    const worstSellers = products.slice(-2);

    document.getElementById('bestSellersList').innerHTML = bestSellers.map(p => `
        <li class="seller-item">
            <img src="${p.image}" class="seller-item__thumb" alt="${p.name}">
            <div class="seller-item__info">
                <p class="seller-item__name">${p.name}</p>
                <p class="seller-item__brand">${p.brand}</p>
            </div>
            <span class="seller-item__count seller-item__count--up">+150</span>
        </li>
    `).join('');

    document.getElementById('worstSellersList').innerHTML = worstSellers.map(p => `
        <li class="seller-item">
            <img src="${p.image}" class="seller-item__thumb" alt="${p.name}">
            <div class="seller-item__info">
                <p class="seller-item__name">${p.name}</p>
                <p class="seller-item__brand">${p.brand}</p>
            </div>
            <span class="seller-item__count seller-item__count--down">+2</span>
        </li>
    `).join('');
}

// ====== PRODUCT MODAL LOGIC ======
let modal, modalContent, form;
function initModal() {
    modal        = document.getElementById('productModal');
    modalContent = document.getElementById('productModalContent');
    form         = document.getElementById('productForm');

    if (form) {
        const imageInput     = document.getElementById('productImageFile');
        const imagePreview   = document.getElementById('imagePreview');
        const imagePreviewImg= document.getElementById('imagePreviewImg');
        const hiddenImageInput = document.getElementById('productImage');

        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        hiddenImageInput.value = ev.target.result;
                        imagePreviewImg.src    = ev.target.result;
                        imagePreview.classList.remove('hidden');
                        imagePreview.classList.add('visible');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const originalId = document.getElementById('originalProductId').value;
            const id         = document.getElementById('productId').value.trim();
            const brand      = document.getElementById('productBrand').value;
            const name       = document.getElementById('productName').value;
            const price      = document.getElementById('productPrice').value;
            const stock      = parseInt(document.getElementById('productStock').value) || 0;
            const image      = document.getElementById('productImage').value;
            const desc       = document.getElementById('productDescription').value;
            const specs      = document.getElementById('productSpecs').value;

            if (!image) { alert('Vui lòng chọn hình ảnh cho sản phẩm!'); return; }
            if (!id)    { alert('Vui lòng nhập Mã sản phẩm!'); return; }

            if (originalId) {
                const index = products.findIndex(p => p.id == originalId);
                if (index > -1) {
                    products[index] = { ...products[index], id, brand, name, price, stock, description: desc, specs, image };
                    alert('Cập nhật sản phẩm thành công!');
                }
            } else {
                if (products.some(p => p.id == id)) { alert('Mã sản phẩm này đã tồn tại!'); return; }
                products.unshift({ id, brand, name, price, stock, description: desc, specs, image });
                alert('Thêm sản phẩm thành công!');
            }
            localStorage.setItem('products', JSON.stringify(products));
            closeProductModal();
            renderAdminProducts();
        });
    }
}

function openProductModal(id = null) {
    const imageInput      = document.getElementById('productImageFile');
    const imagePreview    = document.getElementById('imagePreview');
    const imagePreviewImg = document.getElementById('imagePreviewImg');
    const idInput         = document.getElementById('productId');
    const originalIdInput = document.getElementById('originalProductId');

    // Reset
    imageInput.value = '';
    imagePreview.classList.remove('visible');
    imagePreview.classList.add('hidden');
    document.getElementById('productImage').value = '';
    originalIdInput.value = '';

    const brandInput = document.getElementById('productBrand');
    const nameInput  = document.getElementById('productName');
    [brandInput, nameInput, idInput].forEach(el => {
        el.readOnly = false;
        el.classList.remove('admin-input--disabled');
    });
    document.getElementById('productDescription').value = '';
    document.getElementById('productSpecs').value = '';

    document.getElementById('modalTitle').innerText = id ? 'Sửa Sản Phẩm' : 'Thêm Sản Phẩm';

    if (id) {
        const p = products.find(x => x.id == id);
        originalIdInput.value = p.id;
        idInput.value         = p.id;
        brandInput.value      = p.brand;
        nameInput.value       = p.name;
        document.getElementById('productPrice').value       = p.price || '';
        document.getElementById('productStock').value       = p.stock !== undefined ? p.stock : 10;
        document.getElementById('productImage').value       = p.image;
        document.getElementById('productDescription').value = p.description || '';
        document.getElementById('productSpecs').value       = p.specs || '';

        idInput.readOnly = true;
        idInput.classList.add('admin-input--disabled');

        if (p.image) {
            imagePreviewImg.src = p.image;
            imagePreview.classList.remove('hidden');
            imagePreview.classList.add('visible');
        }
    } else {
        form.reset();
        document.getElementById('productId').value = '';
    }

    modal.classList.add('open');
}

function closeProductModal() {
    modal.classList.remove('open');
}

function deleteProduct(id) {
    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
        products = products.filter(p => String(p.id) !== String(id));
        localStorage.setItem('products', JSON.stringify(products));
        renderAdminProducts();
    }
}
function editProduct(id) { openProductModal(id); }

window.openProductModal  = openProductModal;
window.closeProductModal = closeProductModal;
window.deleteProduct     = deleteProduct;
window.editProduct       = editProduct;

// ====== INITIALIZATION ======
document.addEventListener("DOMContentLoaded", function() {
    initModal();
    switchTab('products');
});

