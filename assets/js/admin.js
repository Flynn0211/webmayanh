let adminApiBase = window.location.pathname;
if (adminApiBase.match(/\/admin\/?$/)) {
    adminApiBase = adminApiBase.replace(/\/admin\/?$/, '/admin/index.php');
}
let clientApiBase = adminApiBase.replace(/\/admin\/[^\/]*$/, '/index.php');

// ====== DỮ LIỆU TẠM (MOCK DATA) ======
let products = window.dbProducts || [];
let orders = window.dbOrders || [];
let vouchers = window.dbVouchers || [];
let articles = window.dbArticles || [];

let mockCustomers = window.dbCustomers || [];
let mockEmployees = window.dbEmployees || [];
let mockReviews = [
    { id: 1, product: 'Leica M11 Monochrom', user: 'Nguyễn Văn A', content: 'Chất lượng ảnh đen trắng tuyệt vời, thiết kế rất cổ điển.' },
    { id: 2, product: 'Sony Alpha 7R V', user: 'Trần Thị B', content: 'Lấy nét tự động cực nhanh, rất đáng tiền!' }
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
    'categories': 'Quản lý danh mục',
    'promotions': 'Khuyến mãi sản phẩm',
    'vouchers':   'Quản lý voucher',
    'articles':   'Quản lý Bài viết',
    'customers':  'Quản lý khách hàng',
    'employees':  'Quản lý nhân viên',
    'reviews':    'Quản lý đánh giá'
};

function switchTab(tabId) {
    window.currentAdminTab = tabId;
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
    else if (tabId === 'categories')  renderAdminCategories();
    else if (tabId === 'orders')      renderAdminOrders();
    else if (tabId === 'customers')   renderAdminCustomers();
    else if (tabId === 'employees')   renderAdminEmployees();
    else if (tabId === 'reviews')     renderAdminReviews();
    else if (tabId === 'vouchers')    renderAdminVouchers();
    else if (tabId === 'articles')    renderAdminArticles();
    else if (tabId === 'promotions')  renderAdminPromotions();
    else if (tabId === 'revenue')     renderAdminRevenue();
}

// ── Articles ──────────────────────────────────────────────────
function renderAdminArticles() {
    const tbody = document.getElementById('adminArticleTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    
    articles.forEach(art => {
        const id = art.ma_bv || art.id;
        const title = art.tieu_de || art.title;
        const image = art.anh_bia || art.anh_dai_dien || art.image;
        const date = art.ngay_dang || art.ngay_tao || art.date;
        const status = art.trang_thai || art.status;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <img src="${image}" alt="${title}" style="width:60px; height:60px; object-fit:cover; border-radius:4px;"/>
            </td>
            <td><strong>${id}</strong></td>
            <td>
                <div style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${title}">
                    ${title}
                </div>
            </td>
            <td>${date}</td>
            <td><span class="badge badge--${status === 'XuatBan' ? 'success' : 'warning'}">${status === 'XuatBan' ? 'Xuất bản' : 'Bản nháp'}</span></td>
            <td class="center">
                <button class="btn-icon" onclick="editArticle('${id}')" title="Sửa bài viết">
                    <span class="material-symbols-outlined">edit</span>
                </button>
                <button class="btn-icon delete-icon" onclick="deleteArticle('${id}')" title="Xóa">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

let articleEditorInstance;

function syncCKEditor() {
    if (articleEditorInstance) {
        document.getElementById('articleContent').value = articleEditorInstance.getData();
    }
    return true;
}

window.openArticleModal = function(id = null) {
    const imageInput      = document.getElementById('articleImageFile');
    const imagePreview    = document.getElementById('articleImagePreviewContainer');
    const imagePreviewImg = document.getElementById('articleImagePreviewImg');
    const oldImageInput   = document.getElementById('articleOldImage');
    const idInput         = document.getElementById('articleId');
    const actionInput     = document.getElementById('articleAction');
    
    document.getElementById('articleForm').reset();
    imageInput.value = '';
    oldImageInput.value = '';
    imagePreview.classList.remove('visible');
    imagePreview.classList.add('hidden');
    idInput.value = '';
    actionInput.value = id ? 'edit' : 'add';
    
    document.getElementById('articleModalTitle').innerText = id ? 'Sửa Bài Viết' : 'Thêm Bài Viết';

    let initialContent = '';

    if (id) {
        const art = articles.find(a => String(a.ma_bv) === String(id) || String(a.id) === String(id));
        if (art) {
            idInput.value = art.ma_bv || art.id;
            document.getElementById('articleTitle').value = art.tieu_de || art.title;
            document.getElementById('articleSlug').value = art.slug;
            document.getElementById('articleSummary').value = art.tom_tat || art.mo_ta_ngan || art.summary;
            initialContent = art.noi_dung || art.content;
            document.getElementById('articleStatus').value = art.trang_thai || art.status;
            oldImageInput.value = art.anh_bia || art.anh_dai_dien || art.image;
            
            if (art.anh_bia || art.anh_dai_dien || art.image) {
                imagePreviewImg.src = art.anh_bia || art.anh_dai_dien || art.image;
                imagePreview.classList.remove('hidden');
                imagePreview.classList.add('visible');
            }
        }
    }
    
    document.getElementById('articleModal').classList.add('open');

    if (articleEditorInstance) {
        articleEditorInstance.destroy()
            .then(() => initCKEditor(initialContent))
            .catch(error => console.log(error));
    } else {
        initCKEditor(initialContent);
    }
};

function initCKEditor(initialContent) {
    ClassicEditor
        .create(document.querySelector('#articleContent'), {
            simpleUpload: {
                uploadUrl: adminApiBase + '?action=upload_image'
            }
        })
        .then(editor => {
            articleEditorInstance = editor;
            editor.setData(initialContent);
        })
        .catch(error => {
            console.error(error);
        });
}

window.closeArticleModal = function() {
    document.getElementById('articleModal').classList.remove('open');
};

window.editArticle = function(id) {
    window.openArticleModal(id);
};

window.deleteArticle = function(id) {
    if (confirm('Bạn có chắc muốn xóa bài viết này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = adminApiBase + '?tab=articles';
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'article_action';
        actionInput.value = 'delete';
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
};

function initArticleModal() {
    const imageInput = document.getElementById('articleImageFile');
    const imagePreview = document.getElementById('articleImagePreviewContainer');
    const imagePreviewImg = document.getElementById('articleImagePreviewImg');

    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    imagePreviewImg.src = ev.target.result;
                    imagePreview.classList.remove('hidden');
                    imagePreview.classList.add('visible');
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('tab')) {
        switchTab(urlParams.get('tab'));
    } else {
        switchTab('revenue');
    }
});

// ── Products ──────────────────────────────────────────────────
let categories = window.dbCategories || [];

function renderAdminCategories() {
    const tbody = document.getElementById('adminCategoryTableBody');
    if (!tbody) return;
    if (categories.length === 0) {
        tbody.innerHTML = `<tr><td colspan="3" class="td-muted" style="text-align:center;padding:2rem;">Không có danh mục nào.</td></tr>`;
        return;
    }
    tbody.innerHTML = categories.map(c => `
        <tr>
            <td class="td-id center">#${c.id}</td>
            <td class="td-bold">${c.name}</td>
            <td class="center">
                <button class="btn-icon" onclick="editCategory(${c.id})" title="Sửa danh mục">
                    <span class="material-symbols-outlined">edit</span>
                </button>
                <button class="btn-icon delete-icon" onclick="deleteCategory(${c.id})" title="Xóa">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            </td>
        </tr>
    `).join('');
}

window.openCategoryModal = function(id = null) {
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = id || '';
    document.getElementById('categoryModalTitle').innerText = id ? 'Sửa Danh Mục' : 'Thêm Danh Mục';

    if (id) {
        const cat = categories.find(c => String(c.id) === String(id));
        if (cat) {
            document.getElementById('categoryName').value = cat.name;
        }
    }
    document.getElementById('categoryModal').classList.add('open');
};

window.closeCategoryModal = function() {
    document.getElementById('categoryModal').classList.remove('open');
};

window.editCategory = function(id) {
    window.openCategoryModal(id);
};

window.deleteCategory = function(id) {
    if (confirm('Bạn có chắc muốn xóa danh mục này? Hệ thống sẽ chặn nếu có sản phẩm bên trong!')) {
        const formData = new FormData();
        formData.append('action', 'delete_category');
        formData.append('id', id);

        fetch(adminApiBase + '?action=delete_category', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Xóa danh mục thành công!');
                window.location.href = adminApiBase + '?tab=' + (window.currentAdminTab || 'revenue');
            } else {
                alert('Lỗi: ' + (data.error || 'Không thể xóa danh mục!'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Lỗi kết nối!');
        });
    }
};

document.getElementById('categoryForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('categoryId').value;
    const name = document.getElementById('categoryName').value;

    const formData = new FormData();
    const action = id ? 'edit_category' : 'add_category';
    formData.append('action', action);
    if (id) formData.append('id', id);
    formData.append('name', name);

    fetch(adminApiBase + '?action=' + action, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Lưu danh mục thành công!');
            window.location.href = adminApiBase + '?tab=' + (window.currentAdminTab || 'revenue');
        } else {
            alert('Lỗi: ' + (data.error || 'Lưu thất bại!'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối!');
    });
});

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
    fetch(clientApiBase + '?action=update_order_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderId, status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // alert('Đã cập nhật trạng thái đơn hàng!');
            // Update local dbOrders data to reflect change immediately without reload
            const idx = window.dbOrders.findIndex(o => String(o.id) === String(orderId));
            if (idx > -1) {
                window.dbOrders[idx].status = newStatus;
                renderAdminOrders();
            }
        } else {
            alert('Lỗi cập nhật: ' + (data.message || 'Unknown'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi mạng không thể cập nhật đơn hàng.');
    });
};

window.showOrderDetails = function(orderId) {
    const order = window.dbOrders.find(o => String(o.id) === String(orderId));
    if (!order) return;
    
    document.getElementById('orderModalTitle').innerText = 'Chi tiết Đơn Hàng #' + order.id;
    let html = `
        <div style="background: var(--surface-container-lowest); padding: 1.5rem 2rem; border-radius: 1rem; margin-bottom: 2rem; border: 1px solid var(--outline-variant); display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
            <div>
                <div style="font-size: 0.75rem; color: var(--on-surface-variant); text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700;">Khách hàng</div>
                <div style="font-size: 1.125rem; font-weight: 600; color: var(--on-surface); margin-top: 0.25rem;">${order.customerName}</div>
                <div style="font-size: 0.875rem; color: var(--on-surface-variant); margin-top: 0.25rem;">@${order.customerUsername || 'guest'}</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.75rem; color: var(--on-surface-variant); text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700;">Ngày đặt</div>
                <div style="font-size: 1rem; font-weight: 500; color: var(--on-surface); margin-top: 0.5rem;">${order.date}</div>
            </div>
        </div>
        
        <h4 style="font-family: 'Geist', sans-serif; font-size: 1.125rem; margin-bottom: 1.25rem; color: var(--on-surface);">Sản phẩm đã mua</h4>
        <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2.5rem;">
    `;
    
    if (order.items && order.items.length > 0) {
        order.items.forEach(item => {
            html += `
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; padding: 1.25rem; background: #fff; border: 1px solid var(--outline-variant); border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.02); transition: transform 0.2s; cursor: default;" onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">
                <div style="display: flex; align-items: center; gap: 1.5rem; flex-grow: 1;">
                    <div style="width: 4.5rem; height: 4.5rem; background: var(--surface-container-low); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; padding: 0.5rem; flex-shrink: 0;">
                        <img src="${item.image || ''}" style="max-width: 100%; max-height: 100%; object-fit: contain; mix-blend-mode: multiply;" />
                    </div>
                    <div style="text-align: left;">
                        <div style="font-size: 1rem; font-weight: 600; color: var(--on-surface); margin-bottom: 0.25rem; line-height: 1.4;">${item.name}</div>
                        <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--on-surface-variant);">${item.brand || 'N/A'}</div>
                    </div>
                </div>
                <div style="text-align: right; min-width: 120px; flex-shrink: 0;">
                    <div style="font-size: 0.875rem; color: var(--on-surface-variant); margin-bottom: 0.25rem;">SL: <strong style="color: var(--on-surface);">${item.quantity}</strong></div>
                    <div style="font-family: 'Geist', sans-serif; font-size: 1.125rem; font-weight: 600; color: var(--on-surface);">${item.price}</div>
                </div>
            </div>
            `;
        });
    } else {
        html += `<div style="text-align:center; padding: 2rem; color: var(--on-surface-variant); background: var(--surface-container-lowest); border-radius: 0.75rem; border: 1px dashed var(--outline-variant);">Không có chi tiết sản phẩm.</div>`;
    }
    
    html += `
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 2rem; background: var(--surface-container-lowest); border-radius: 1rem; border: 1px solid var(--outline-variant); box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
            <div style="font-size: 0.875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--on-surface-variant);">Tổng thanh toán</div>
            <div style="font-family: 'Geist', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--primary);">${order.total}</div>
        </div>
    `;
    
    document.getElementById('orderModalBody').innerHTML = html;
    document.getElementById('orderModal').classList.add('open');
};

window.closeOrderModal = function() {
    document.getElementById('orderModal').classList.remove('open');
};

window.currentOrderTab = 'processing';

window.switchOrderTab = function(tab) {
    window.currentOrderTab = tab;
    document.getElementById('orderTabProcessing').className = tab === 'processing' ? 'btn-hero-primary' : 'btn-hero-ghost';
    document.getElementById('orderTabCompleted').className = tab === 'completed' ? 'btn-hero-primary' : 'btn-hero-ghost';
    
    if(tab === 'processing') {
        document.getElementById('orderTabProcessing').style.cssText = 'padding: 0.5rem 1.5rem;';
        document.getElementById('orderTabCompleted').style.cssText = 'padding: 0.5rem 1.5rem; color:var(--on-surface); border-color:rgba(0,0,0,0.1);';
    } else {
        document.getElementById('orderTabCompleted').style.cssText = 'padding: 0.5rem 1.5rem;';
        document.getElementById('orderTabProcessing').style.cssText = 'padding: 0.5rem 1.5rem; color:var(--on-surface); border-color:rgba(0,0,0,0.1);';
    }
    
    renderAdminOrders();
};

function renderAdminOrders() {
    let allOrders = window.dbOrders || [];
    const statuses = ['Chờ Xác Nhận', 'Đang Xử Lý', 'Đang Giao', 'Đã Giao', 'Hoàn Thành', 'Đã hủy'];

    let orders = [];
    if (window.currentOrderTab === 'completed') {
        orders = allOrders.filter(o => o.status === 'Hoàn Thành' || o.status === 'Đã hủy');
    } else {
        orders = allOrders.filter(o => o.status !== 'Hoàn Thành' && o.status !== 'Đã hủy');
    }

    if (orders.length === 0) {
        document.getElementById('adminOrderTableBody').innerHTML = `<tr><td colspan="6" class="td-muted" style="text-align:center;padding:2rem;">Chưa có đơn hàng nào trong mục này.</td></tr>`;
        // Only update badge if we are looking at processing tab, or total processing
        const processingCount = allOrders.filter(o => o.status !== 'Hoàn Thành' && o.status !== 'Đã hủy').length;
        document.getElementById('newOrdersBadge').innerText = processingCount.toString();
        return;
    }

    const sortedOrders = [...orders];
    document.getElementById('adminOrderTableBody').innerHTML = sortedOrders.map(o => `
        <tr>
            <td class="td-primary td-mono">#${o.id}</td>
            <td>
                ${o.customerName || 'Khách vãng lai'}
                <div class="td-muted" style="margin-top:0.25rem;">@${o.customerUsername || 'guest'}</div>
            </td>
            <td class="td-mono">${o.total}</td>
            <td class="td-muted">${o.date}</td>
            <td class="center">
                <select onchange="updateOrderStatus('${o.id}', this.value)" class="order-status-select" style="padding: 0.25rem; border-radius:4px; border:1px solid var(--border-color);">
                    ${statuses.map(s => `<option ${s === o.status ? 'selected' : ''}>${s}</option>`).join('')}
                </select>
            </td>
            <td class="center">
                <button class="btn-table-action btn-table-action--view" title="Xem chi tiết" onclick="showOrderDetails('${o.id}')">
                    <span class="material-symbols-outlined" style="font-size:1.25rem;">visibility</span>
                </button>
            </td>
        </tr>
    `).join('');

    const processingCount = allOrders.filter(o => o.status !== 'Hoàn Thành' && o.status !== 'Đã hủy').length;
    document.getElementById('newOrdersBadge').innerText = processingCount.toString();
}

// ── Customers ─────────────────────────────────────────────────
window.toggleUserStatus = function(id) {
    if(!confirm('Bạn có chắc muốn thay đổi trạng thái tài khoản này?')) return;
    fetch(`${adminApiBase}?action=toggle_user_status&id=${id}`)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Cập nhật trạng thái thành công!');
            window.location.href = adminApiBase + '?tab=' + (window.currentAdminTab || 'revenue');
        } else {
            alert('Lỗi: ' + (data.error || 'Không thể cập nhật'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi mạng khi cập nhật trạng thái');
    });
};

window.closeUserModal = function() {
    document.getElementById('userModal').classList.remove('open');
};

window.viewUser = function(id, type) {
    const userList = type === 'customer' ? window.dbCustomers : window.dbEmployees;
    const user = userList.find(u => String(u.id) === String(id));
    if (!user) return;
    
    document.getElementById('userModalTitle').innerText = type === 'customer' ? 'Chi tiết Khách hàng' : 'Chi tiết Nhân viên';
    
    let html = `
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--outline-variant); padding-bottom: 0.5rem;">
                <div style="font-weight: 600; color: var(--on-surface-variant);">ID</div>
                <div style="font-family: 'Geist', sans-serif;">#${user.id}</div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--outline-variant); padding-bottom: 0.5rem;">
                <div style="font-weight: 600; color: var(--on-surface-variant);">Họ Tên</div>
                <div>${user.name}</div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--outline-variant); padding-bottom: 0.5rem;">
                <div style="font-weight: 600; color: var(--on-surface-variant);">Username</div>
                <div style="font-family: 'Geist', sans-serif; color: var(--primary);">@${user.username}</div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--outline-variant); padding-bottom: 0.5rem;">
                <div style="font-weight: 600; color: var(--on-surface-variant);">Email</div>
                <div>${user.email || 'Không có'}</div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--outline-variant); padding-bottom: 0.5rem;">
                <div style="font-weight: 600; color: var(--on-surface-variant);">Số điện thoại</div>
                <div>${user.phone || 'Không có'}</div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--outline-variant); padding-bottom: 0.5rem;">
                <div style="font-weight: 600; color: var(--on-surface-variant);">Vai trò</div>
                <div>${user.role}</div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--outline-variant); padding-bottom: 0.5rem;">
                <div style="font-weight: 600; color: var(--on-surface-variant);">Trạng thái</div>
                <div><span class="inline-block px-2 py-1 text-xs border border-tertiary text-tertiary rounded" style="${user.active ? 'border-color: green; color: green;' : 'border-color: red; color: red;'}">${user.active ? 'Hoạt động' : 'Bị khóa'}</span></div>
            </div>
        </div>
    `;
    
    document.getElementById('userModalBody').innerHTML = html;
    document.getElementById('userModal').classList.add('open');
};

function renderAdminCustomers() {
    let customers = window.dbCustomers || [];
    if (customers.length === 0) {
        document.getElementById('adminCustomerTableBody').innerHTML = `<tr><td colspan="4" class="center td-muted">Chưa có khách hàng nào.</td></tr>`;
        return;
    }
    document.getElementById('adminCustomerTableBody').innerHTML = customers.map(c => `
        <tr class="${!c.active ? 'disabled' : ''}">
            <td class="td-id">${c.id}</td>
            <td class="td-bold">${c.name}</td>
            <td><span class="tier-badge tier-badge--${c.tier}">${c.tier}</span></td>
            <td class="center">
                <div class="action-group">
                    <button class="btn-table-action btn-table-action--view" title="Xem" onclick="viewUser('${c.id}', 'customer')">
                        <span class="material-symbols-outlined" style="font-size:1.25rem;">visibility</span>
                    </button>
                    <button class="btn-table-action ${c.active ? 'btn-table-action--lock' : 'btn-table-action--unlock'}"
                            title="${c.active ? 'Khóa' : 'Mở khóa'}"
                            onclick="toggleUserStatus('${c.id}')">
                        <span class="material-symbols-outlined" style="font-size:1.25rem;">${c.active ? 'lock' : 'lock_open'}</span>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ── Employees ─────────────────────────────────────────────────
function renderAdminEmployees() {
    let employees = window.dbEmployees || [];
    if (employees.length === 0) {
        document.getElementById('adminEmployeeTableBody').innerHTML = `<tr><td colspan="4" class="center td-muted">Chưa có nhân viên nào.</td></tr>`;
        return;
    }
    document.getElementById('adminEmployeeTableBody').innerHTML = employees.map(e => `
        <tr class="${!e.active ? 'disabled' : ''}">
            <td class="td-id">${e.id}</td>
            <td class="td-bold">${e.name}</td>
            <td class="td-muted">${e.role}</td>
            <td class="center">
                <div class="action-group">
                    <button class="btn-table-action btn-table-action--view" title="Xem" onclick="viewUser('${e.id}', 'employee')">
                        <span class="material-symbols-outlined" style="font-size:1.25rem;">visibility</span>
                    </button>
                    <button class="btn-table-action ${e.active ? 'btn-table-action--lock' : 'btn-table-action--unlock'}"
                            title="${e.active ? 'Khóa' : 'Mở khóa'}"
                            onclick="toggleUserStatus('${e.id}')">
                        <span class="material-symbols-outlined" style="font-size:1.25rem;">${e.active ? 'lock' : 'lock_open'}</span>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ── Reviews ───────────────────────────────────────────────────
function renderAdminReviews() {
    fetch(adminApiBase + '?action=get_reviews')
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.reviews.length === 0) {
                document.getElementById('adminReviewTableBody').innerHTML = `<tr><td colspan="4" class="center">Chưa có đánh giá nào.</td></tr>`;
                return;
            }
            document.getElementById('adminReviewTableBody').innerHTML = data.reviews.map(r => `
                <tr>
                    <td class="td-primary td-bold">${r.product}</td>
                    <td class="td-muted">@${r.user}</td>
                    <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${r.content}</td>
                    <td class="center">
                        <button class="btn-table-action btn-table-action--delete" title="Xóa" onclick="deleteReview(${r.id})">
                            <span class="material-symbols-outlined" style="font-size:1.25rem;">delete</span>
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            document.getElementById('adminReviewTableBody').innerHTML = `<tr><td colspan="4" class="center">Không thể tải đánh giá</td></tr>`;
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('adminReviewTableBody').innerHTML = `<tr><td colspan="4" class="center">Lỗi mạng</td></tr>`;
    });
}

window.deleteReview = function(id) {
    if (confirm('Bạn có chắc chắn muốn xóa đánh giá này?')) {
        const formData = new FormData();
        formData.append('id', id);
        
        fetch(adminApiBase + '?action=delete_review', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Xóa đánh giá thành công!');
                renderAdminReviews();
            } else {
                alert('Lỗi: ' + (data.error || 'Không thể xóa'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Lỗi mạng không thể xóa đánh giá.');
        });
    }
};

// ── Vouchers ──────────────────────────────────────────────────
function renderAdminVouchers() {
    document.getElementById('adminVoucherTableBody').innerHTML = vouchers.map(v => `
        <tr>
            <td class="td-primary td-mono td-bold">${v.code}</td>
            <td>${v.discount}</td>
            <td class="td-muted">${v.so_luong !== undefined ? v.so_luong : v.quantity}</td>
            <td class="td-error">${v.expire}</td>
            <td class="center">
                <span class="inline-block px-2 py-1 text-xs border border-tertiary text-tertiary rounded font-label-caps" style="text-transform: uppercase;">
                    ${v.trang_thai || 'HoatDong'}
                </span>
            </td>
        </tr>
    `).join('');
}

// ── Promotions ────────────────────────────────────────────────
function renderAdminPromotions() {
    fetch(adminApiBase + '?action=get_promotions')
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('adminPromoTableBody').innerHTML = data.promotions.map(p => `
                <tr>
                    <td class="td-bold">${p.product}</td>
                    <td class="td-primary td-bold">Giảm ${p.discount}</td>
                    <td class="td-muted">${p.start} - ${p.end}</td>
                    <td class="center">
                        <span class="inline-block px-2 py-1 text-xs border border-tertiary text-tertiary rounded font-label-caps" style="text-transform: uppercase;">
                            ${p.status}
                        </span>
                    </td>
                    <td class="center">
                        <button class="btn-table-action btn-table-action--delete" title="Xóa" onclick="deletePromotion(${p.id})">
                            <span class="material-symbols-outlined" style="font-size:1.25rem;">delete</span>
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            document.getElementById('adminPromoTableBody').innerHTML = `<tr><td colspan="5" class="center">Không thể tải khuyến mãi</td></tr>`;
        }
    })
    .catch(err => {
        document.getElementById('adminPromoTableBody').innerHTML = `<tr><td colspan="5" class="center">Lỗi mạng</td></tr>`;
    });
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
let currentAdditionalImages = [];

function renderAdditionalImages() {
    const previewContainer = document.getElementById('additionalImagesPreview');
    if (!previewContainer) return;
    previewContainer.innerHTML = '';
    currentAdditionalImages.forEach((imgSrc, idx) => {
        const item = document.createElement('div');
        item.style.position = 'relative';
        item.style.width = '60px';
        item.style.height = '60px';
        item.style.border = '1px solid rgba(0,0,0,0.1)';
        item.style.borderRadius = '4px';
        item.style.overflow = 'hidden';
        item.style.display = 'flex';
        item.style.alignItems = 'center';
        item.style.justifyContent = 'center';
        item.style.background = '#f9f9f9';

        const img = document.createElement('img');
        img.src = imgSrc;
        img.style.maxWidth = '100%';
        img.style.maxHeight = '100%';
        img.style.objectFit = 'contain';
        item.appendChild(img);

        // Delete button
        const delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.innerHTML = '×';
        delBtn.style.position = 'absolute';
        delBtn.style.top = '2px';
        delBtn.style.right = '2px';
        delBtn.style.width = '16px';
        delBtn.style.height = '16px';
        delBtn.style.borderRadius = '50%';
        delBtn.style.background = 'rgba(230, 57, 70, 0.8)';
        delBtn.style.color = '#fff';
        delBtn.style.border = 'none';
        delBtn.style.fontSize = '12px';
        delBtn.style.lineHeight = '12px';
        delBtn.style.cursor = 'pointer';
        delBtn.style.display = 'flex';
        delBtn.style.alignItems = 'center';
        delBtn.style.justifyContent = 'center';
        delBtn.onclick = (e) => {
            e.stopPropagation();
            currentAdditionalImages.splice(idx, 1);
            renderAdditionalImages();
        };
        item.appendChild(delBtn);

        previewContainer.appendChild(item);
    });
}

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

        const additionalImagesInput = document.getElementById('productAdditionalImagesFile');
        if (additionalImagesInput) {
            additionalImagesInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        currentAdditionalImages.push(ev.target.result);
                        renderAdditionalImages();
                    };
                    reader.readAsDataURL(file);
                });
                additionalImagesInput.value = ''; // Reset input selection
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const originalId = document.getElementById('originalProductId').value;
            const id         = document.getElementById('productId').value.trim();
            const category   = document.getElementById('productCategory').value;
            const brand      = document.getElementById('productBrand').value;
            const name       = document.getElementById('productName').value;
            const price      = document.getElementById('productPrice').value;
            const stock      = parseInt(document.getElementById('productStock').value) || 0;
            const image      = document.getElementById('productImage').value;
            const desc       = document.getElementById('productDescription').value;
            const specs      = document.getElementById('productSpecs').value;
            const additional_images = JSON.stringify(currentAdditionalImages);

            if (!image) { alert('Vui lòng chọn hình ảnh cho sản phẩm!'); return; }

            const action = originalId ? 'edit_product' : 'add_product';
            const formData = new FormData();
            formData.append('id', id);
            formData.append('category', category);
            formData.append('brand', brand);
            formData.append('name', name);
            formData.append('price', price);
            formData.append('stock', stock);
            formData.append('image', image);
            formData.append('description', desc);
            formData.append('specs', specs);
            formData.append('additional_images', additional_images);

            fetch(`${adminApiBase}?action=${action}`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(originalId ? 'Cập nhật sản phẩm thành công!' : 'Thêm sản phẩm thành công!');
                    closeProductModal();
                    window.location.href = adminApiBase + '?tab=' + (window.currentAdminTab || 'revenue');
                } else {
                    alert('Lỗi: ' + (data.error || 'Không thể lưu sản phẩm'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi mạng không thể lưu sản phẩm.');
            });
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
    currentAdditionalImages = [];
    renderAdditionalImages();

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
        document.getElementById('productCategory').value = p.category_id || 1;
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

        // Load additional images
        if (p.additional_images) {
            try {
                currentAdditionalImages = typeof p.additional_images === 'string'
                    ? JSON.parse(p.additional_images)
                    : p.additional_images;
                if (!Array.isArray(currentAdditionalImages)) {
                    currentAdditionalImages = [];
                }
            } catch(e) {
                console.error("Error parsing product additional images:", e);
                currentAdditionalImages = [];
            }
        }
        renderAdditionalImages();
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
        fetch(`${adminApiBase}?action=delete_product&id=${id}`, {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Xóa sản phẩm thành công!');
                window.location.href = adminApiBase + '?tab=' + (window.currentAdminTab || 'revenue');
            } else {
                alert('Lỗi: ' + (data.error || 'Không thể xóa'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Lỗi mạng không thể xóa sản phẩm.');
        });
    }
}
function editProduct(id) { openProductModal(id); }

window.openProductModal  = openProductModal;
window.closeProductModal = closeProductModal;
window.deleteProduct     = deleteProduct;
window.editProduct       = editProduct;

window.addVoucherPrompt = function() {
    const code = prompt("Nhập mã Voucher mới (ví dụ: SALE50):");
    if (!code) return;
    const discount = prompt("Nhập mức giảm (ví dụ: 15% hoặc 500000):");
    if (!discount) return;
    const qty = prompt("Nhập số lượng phát hành (ví dụ: 100):");
    if (!qty) return;
    const expire = prompt("Nhập ngày hết hạn (định dạng: YYYY-MM-DD):", "2026-12-31");
    if (!expire) return;
    
    const formData = new FormData();
    formData.append('code', code.trim().toUpperCase());
    formData.append('discount', discount.trim());
    formData.append('quantity', qty);
    formData.append('expire', expire);
    
    fetch(adminApiBase + '?action=add_voucher', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Thêm voucher thành công!');
            window.location.href = adminApiBase + '?tab=' + (window.currentAdminTab || 'revenue');
        } else {
            alert('Lỗi: ' + (data.error || 'Không thể tạo voucher'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi mạng không thể tạo voucher.');
    });
};

window.addPromotionPrompt = function() {
    const promoModal = document.getElementById('promoModal');
    const productSelect = document.getElementById('promoProduct');
    const form = document.getElementById('promoForm');
    
    // Populate select
    productSelect.innerHTML = products.map(p => `<option value="${p.id}">${p.id} - ${p.name}</option>`).join('');
    
    promoModal.classList.add('open');
    
    form.onsubmit = function(e) {
        e.preventDefault();
        
        const productId = document.getElementById('promoProduct').value;
        const discount = document.getElementById('promoDiscount').value;
        const expire = document.getElementById('promoExpire').value;
        
        const formData = new FormData();
        formData.append('product_id', productId.trim());
        formData.append('discount', discount.trim());
        formData.append('expire', expire);
        
        fetch(adminApiBase + '?action=add_promotion', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Thêm khuyến mãi thành công!');
                closePromoModal();
                form.reset();
                renderAdminPromotions();
            } else {
                alert('Lỗi: ' + (data.error || 'Không thể tạo khuyến mãi'));
            }
        })
        .catch(err => {
            console.error(err);
        });
    };
};

window.closePromoModal = function() {
    document.getElementById('promoModal').classList.remove('open');
};

window.deletePromotion = function(id) {
    if (confirm('Bạn có chắc chắn muốn gỡ khuyến mãi này?')) {
        const formData = new FormData();
        formData.append('id', id);
        
        fetch(adminApiBase + '?action=delete_promotion', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Xóa khuyến mãi thành công!');
                renderAdminPromotions();
            } else {
                alert('Lỗi: ' + (data.error || 'Không thể xóa khuyến mãi'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Lỗi mạng không thể xóa khuyến mãi.');
        });
    }
};

window.addUserPrompt = function() {
    const name = prompt("Nhập họ tên nhân viên mới:");
    if (!name) return;
    const user = prompt("Nhập username:");
    if (!user) return;
    const pass = prompt("Nhập mật khẩu (password):");
    if (!pass) return;
    
    const formData = new FormData();
    formData.append('name', name.trim());
    formData.append('username', user.trim());
    formData.append('password', pass);
    formData.append('role', 'Admin');
    
    fetch(adminApiBase + '?action=add_user', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Thêm nhân viên thành công!');
            window.location.href = adminApiBase + '?tab=' + (window.currentAdminTab || 'revenue');
        } else {
            alert('Lỗi: ' + (data.error || 'Không thể thêm nhân viên'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi mạng không thể tạo nhân viên.');
    });
};

// ====== INITIALIZATION ======
document.addEventListener("DOMContentLoaded", function() {
    initModal();
    initArticleModal();
});
