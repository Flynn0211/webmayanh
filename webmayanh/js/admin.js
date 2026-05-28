// ====== DATA ======
let products = JSON.parse(localStorage.getItem('products')) || [];

// Mock Data cho các mục khác để UI không trống
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
    'revenue': 'Quản lý doanh thu',
    'orders': 'Quản lý đơn hàng',
    'products': 'Quản lý sản phẩm',
    'promotions': 'Khuyến mãi sản phẩm',
    'vouchers': 'Quản lý voucher',
    'customers': 'Quản lý khách hàng',
    'employees': 'Quản lý nhân viên',
    'reviews': 'Quản lý đánh giá'
};

function switchTab(tabId) {
    // Cập nhật Title
    document.getElementById('pageTitle').innerText = tabNames[tabId];

    // Ẩn tất cả tabs
    document.querySelectorAll('.admin-tab').forEach(el => el.classList.remove('active'));
    // Hiện tab được chọn
    document.getElementById('tab-' + tabId).classList.add('active');

    // Xóa active class ở menu items
    document.querySelectorAll('.menu-item').forEach(el => {
        el.classList.remove('bg-primary/10', 'text-primary', 'font-medium');
    });
    // Thêm active cho menu hiện tại
    const activeMenu = document.getElementById('menu-' + tabId);
    activeMenu.classList.add('bg-primary/10', 'text-primary', 'font-medium');

    // Render lại dữ liệu cho tab đó
    renderTab(tabId);
}
window.switchTab = switchTab; // Expose to HTML

// ====== RENDERING ======
function renderTab(tabId) {
    if (tabId === 'products') renderAdminProducts();
    else if (tabId === 'orders') renderAdminOrders();
    else if (tabId === 'customers') renderAdminCustomers();
    else if (tabId === 'employees') renderAdminEmployees();
    else if (tabId === 'reviews') renderAdminReviews();
    else if (tabId === 'vouchers') renderAdminVouchers();
    else if (tabId === 'promotions') renderAdminPromotions();
    else if (tabId === 'revenue') renderAdminRevenue();
}

// -- Products
function renderAdminProducts() {
    const tbody = document.getElementById('adminProductTableBody');
    if (!tbody) return;
    if (products.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">Không có sản phẩm nào.</td></tr>`;
        return;
    }
    tbody.innerHTML = products.map(p => `
        <tr class="hover:bg-surface-container-low/30 transition-colors">
            <td class="px-6 py-4 text-center font-mono-spec text-on-surface-variant">#${p.id}</td>
            <td class="px-6 py-4">
                <div class="w-12 h-12 bg-white rounded-md border border-outline-variant/20 flex items-center justify-center overflow-hidden p-1">
                    <img src="${p.image}" class="max-w-full max-h-full object-contain mix-blend-multiply" alt="${p.name}">
                </div>
            </td>
            <td class="px-6 py-4 font-label-caps text-primary tracking-widest">${p.brand}</td>
            <td class="px-6 py-4 font-medium text-on-surface truncate max-w-[200px]">${p.name}</td>
            <td class="px-6 py-4 font-mono-spec text-right text-on-surface">${p.price}</td>
            <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                    <button type="button" onclick="editProduct('${p.id}')" class="text-tertiary-container hover:text-tertiary transition-colors" title="Sửa">
                        <span class="material-symbols-outlined text-[20px]">edit_square</span>
                    </button>
                    <button type="button" onclick="deleteProduct('${p.id}')" class="text-error hover:text-error-container transition-colors" title="Xóa">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// -- Orders
function renderAdminOrders() {
    const statuses = ['chờ xác nhận', 'xác nhận đơn hàng', 'đang giao', 'đã giao', 'thành công'];
    document.getElementById('adminOrderTableBody').innerHTML = mockOrders.map((o, i) => `
        <tr class="hover:bg-surface-container-low/30 transition-colors">
            <td class="px-6 py-4 font-mono-spec text-primary font-medium">${o.id}</td>
            <td class="px-6 py-4 text-on-surface">${o.customer}</td>
            <td class="px-6 py-4 font-mono-spec text-on-surface">${o.total}</td>
            <td class="px-6 py-4 text-on-surface-variant">${o.date}</td>
            <td class="px-6 py-4 text-center">
                <select class="bg-surface-container-lowest border border-outline-variant/50 text-sm rounded-md px-2 py-1 focus:ring-0 focus:border-primary w-full" onchange="alert('Đã cập nhật trạng thái đơn hàng!')">
                    ${statuses.map(s => `<option ${s === o.status ? 'selected' : ''}>${s}</option>`).join('')}
                </select>
            </td>
        </tr>
    `).join('');
    document.getElementById('newOrdersBadge').innerText = mockOrders.filter(o => o.status === 'chờ xác nhận').length;
}

// -- Customers
function renderAdminCustomers() {
    const tierColors = {
        'diamond': 'bg-[#e5e4e2] text-on-surface border border-outline-variant/30',
        'gold': 'bg-[#ffd700]/20 text-[#b8860b] border border-[#ffd700]/30',
        'silver': 'bg-surface-container-high text-on-surface-variant border border-outline-variant/30'
    };
    document.getElementById('adminCustomerTableBody').innerHTML = mockCustomers.map(c => `
        <tr class="hover:bg-surface-container-low/30 transition-colors ${!c.active ? 'opacity-50' : ''}">
            <td class="px-6 py-4 font-mono-spec text-on-surface-variant">${c.id}</td>
            <td class="px-6 py-4 font-medium text-on-surface">${c.name}</td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-[10px] font-label-caps uppercase tracking-widest ${tierColors[c.tier]}">${c.tier}</span>
            </td>
            <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                    <button class="text-tertiary-container hover:text-tertiary transition-colors" title="Xem">
                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                    </button>
                    <button class="${c.active ? 'text-error hover:text-error-container' : 'text-primary hover:text-primary-container'} transition-colors" title="${c.active ? 'Khóa' : 'Mở khóa'}" onclick="alert('Đã thay đổi trạng thái khách hàng!')">
                        <span class="material-symbols-outlined text-[20px]">${c.active ? 'lock' : 'lock_open'}</span>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// -- Employees
function renderAdminEmployees() {
    document.getElementById('adminEmployeeTableBody').innerHTML = mockEmployees.map(e => `
        <tr class="hover:bg-surface-container-low/30 transition-colors ${!e.active ? 'opacity-50' : ''}">
            <td class="px-6 py-4 font-mono-spec text-on-surface-variant">${e.id}</td>
            <td class="px-6 py-4 font-medium text-on-surface">${e.name}</td>
            <td class="px-6 py-4 text-on-surface-variant">${e.role}</td>
            <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                    <button class="text-tertiary-container hover:text-tertiary transition-colors" title="Xem">
                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                    </button>
                    <button class="${e.active ? 'text-error hover:text-error-container' : 'text-primary hover:text-primary-container'} transition-colors" title="${e.active ? 'Khóa' : 'Mở khóa'}" onclick="alert('Đã thay đổi trạng thái nhân viên!')">
                        <span class="material-symbols-outlined text-[20px]">${e.active ? 'lock' : 'lock_open'}</span>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// -- Reviews
function renderAdminReviews() {
    document.getElementById('adminReviewTableBody').innerHTML = mockReviews.map(r => `
        <tr class="hover:bg-surface-container-low/30 transition-colors">
            <td class="px-6 py-4 font-medium text-primary">${r.product}</td>
            <td class="px-6 py-4 text-on-surface-variant">${r.user}</td>
            <td class="px-6 py-4 text-on-surface line-clamp-2">${r.content}</td>
            <td class="px-6 py-4 text-center">
                <button class="text-error hover:text-error-container transition-colors" title="Xóa" onclick="alert('Đã xóa đánh giá!')">
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                </button>
            </td>
        </tr>
    `).join('');
}

// -- Vouchers
function renderAdminVouchers() {
    document.getElementById('adminVoucherTableBody').innerHTML = mockVouchers.map(v => `
        <tr class="hover:bg-surface-container-low/30 transition-colors">
            <td class="px-6 py-4 font-mono-spec text-primary font-bold">${v.code}</td>
            <td class="px-6 py-4 text-on-surface">${v.discount}</td>
            <td class="px-6 py-4 text-on-surface-variant">${v.quantity}</td>
            <td class="px-6 py-4 text-error">${v.expire}</td>
            <td class="px-6 py-4 text-center">
                <button class="text-tertiary-container hover:text-tertiary transition-colors" title="Sửa">
                    <span class="material-symbols-outlined text-[20px]">edit_square</span>
                </button>
            </td>
        </tr>
    `).join('');
}

// -- Promotions
function renderAdminPromotions() {
    document.getElementById('adminPromoTableBody').innerHTML = mockPromos.map(p => `
        <tr class="hover:bg-surface-container-low/30 transition-colors">
            <td class="px-6 py-4 font-medium text-on-surface">${p.product}</td>
            <td class="px-6 py-4 text-primary font-bold">Giảm ${p.discount}</td>
            <td class="px-6 py-4 text-center">
                <button class="text-error hover:text-error-container transition-colors" title="Xóa" onclick="alert('Đã gỡ khuyến mãi!')">
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                </button>
            </td>
        </tr>
    `).join('');
}

// -- Revenue
function renderAdminRevenue() {
    // Populate best/worst sellers based on current products if any
    const bestSellers = products.slice(0, 2);
    const worstSellers = products.slice(-2);
    
    document.getElementById('bestSellersList').innerHTML = bestSellers.map(p => `
        <li class="flex items-center gap-4 p-3 rounded-lg bg-surface-container-low/50">
            <img src="${p.image}" class="w-10 h-10 object-contain mix-blend-multiply rounded bg-white p-1">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-on-surface truncate">${p.name}</p>
                <p class="text-xs text-on-surface-variant">${p.brand}</p>
            </div>
            <span class="text-sm font-mono-spec text-primary font-medium">+150</span>
        </li>
    `).join('');

    document.getElementById('worstSellersList').innerHTML = worstSellers.map(p => `
        <li class="flex items-center gap-4 p-3 rounded-lg bg-surface-container-low/50">
            <img src="${p.image}" class="w-10 h-10 object-contain mix-blend-multiply rounded bg-white p-1">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-on-surface truncate">${p.name}</p>
                <p class="text-xs text-on-surface-variant">${p.brand}</p>
            </div>
            <span class="text-sm font-mono-spec text-error font-medium">+2</span>
        </li>
    `).join('');
}

// ====== PRODUCT MODAL LOGIC ======
let modal, modalContent, form;
function initModal() {
    modal = document.getElementById('productModal');
    modalContent = document.getElementById('productModalContent');
    form = document.getElementById('productForm');

    if (form) {
        // Handle file input for image
        const imageInput = document.getElementById('productImageFile');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewImg = document.getElementById('imagePreviewImg');
        const hiddenImageInput = document.getElementById('productImage');

        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        hiddenImageInput.value = e.target.result;
                        imagePreviewImg.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                        imagePreview.classList.add('flex');
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const originalId = document.getElementById('originalProductId').value;
            const id = document.getElementById('productId').value.trim();
            const brand = document.getElementById('productBrand').value;
            const name = document.getElementById('productName').value;
            const price = document.getElementById('productPrice').value;
            const image = document.getElementById('productImage').value;

            if (!image) {
                alert('Vui lòng chọn hình ảnh cho sản phẩm!');
                return;
            }
            if (!id) {
                alert('Vui lòng nhập Mã sản phẩm!');
                return;
            }

            if (originalId) {
                // Đang trong chế độ sửa
                const index = products.findIndex(p => p.id == originalId);
                if(index > -1) {
                    // Cập nhật sản phẩm, có thể ID được thay đổi nếu cần (dù hiện tại ta khóa)
                    products[index] = { id: id, brand, name, price, image };
                }
            } else {
                // Thêm mới
                // Kiểm tra xem ID có trùng không
                if (products.some(p => p.id == id)) {
                    alert('Mã sản phẩm này đã tồn tại! Vui lòng chọn mã khác.');
                    return;
                }
                products.push({ id: id, brand, name, price, image });
            }
            localStorage.setItem('products', JSON.stringify(products));
            closeProductModal();
            renderAdminProducts(); // Chỉ render lại table sản phẩm
        });
    }
}

function openProductModal(id = null) {
    const imageInput = document.getElementById('productImageFile');
    const imagePreview = document.getElementById('imagePreview');
    const imagePreviewImg = document.getElementById('imagePreviewImg');
    
    const idInput = document.getElementById('productId');
    const originalIdInput = document.getElementById('originalProductId');
    
    // Reset form states
    imageInput.value = '';
    imagePreview.classList.add('hidden');
    imagePreview.classList.remove('flex');
    document.getElementById('productImage').value = '';
    originalIdInput.value = '';
    
    // Reset readonly states (removing them)
    const brandInput = document.getElementById('productBrand');
    const nameInput = document.getElementById('productName');
    brandInput.readOnly = false;
    nameInput.readOnly = false;
    brandInput.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-surface-container-high');
    nameInput.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-surface-container-high');
    
    // Reset ID readonly
    idInput.readOnly = false;
    idInput.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-surface-container-high');

    document.getElementById('modalTitle').innerText = id ? 'Sửa Sản Phẩm' : 'Thêm Sản Phẩm';
    if (id) {
        const p = products.find(x => x.id == id);
        originalIdInput.value = p.id;
        idInput.value = p.id;
        brandInput.value = p.brand;
        nameInput.value = p.name;
        document.getElementById('productPrice').value = p.price;
        document.getElementById('productImage').value = p.image;
        
        // Khóa mã ID khi sửa để tránh lỗi trùng lặp/đổi mã
        idInput.readOnly = true;
        idInput.classList.add('opacity-60', 'cursor-not-allowed', 'bg-surface-container-high');
        
        if (p.image) {
            imagePreviewImg.src = p.image;
            imagePreview.classList.remove('hidden');
            imagePreview.classList.add('flex');
        }
    } else {
        form.reset();
        document.getElementById('productId').value = '';
    }
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
    }, 10);
}

function closeProductModal() {
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }, 200);
}

function deleteProduct(id) {
    if(confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
        // Lọc giữ lại những sản phẩm có id khác với id truyền vào
        products = products.filter(p => String(p.id) !== String(id));
        localStorage.setItem('products', JSON.stringify(products));
        renderAdminProducts();
    }
}

function editProduct(id) {
    openProductModal(id);
}

window.openProductModal = openProductModal;
window.closeProductModal = closeProductModal;
window.deleteProduct = deleteProduct;
window.editProduct = editProduct;

// ====== INITIALIZATION ======
document.addEventListener("DOMContentLoaded", function() {
    initModal();
    // Khởi tạo tab đầu tiên (Sản phẩm)
    switchTab('products');
});
