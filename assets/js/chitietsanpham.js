/**
 * Tệp tin: chitietsanpham.js
 * Chức năng: Xử lý logic và nghiệp vụ liên quan đến chitietsanpham
 * Tác giả: Nhóm Lập Trình Web Nâng Cao
 */

document.addEventListener("DOMContentLoaded", function() {
    // Utilities format from trangchu.js
    function getBrandColor(brand) {
        if (!brand) return 'text-primary';
        const b = brand.toLowerCase();
        if (b.includes('fujifilm')) return 'text-black';
        if (b.includes('sony')) return 'text-orange-500';
        if (b.includes('nikon')) return 'text-yellow-500';
        if (b.includes('canon')) return 'text-red-600';
        return 'text-primary';
    }

    function formatPrice(priceStr) {
        if (!priceStr) return '';
        const digits = String(priceStr).replace(/\D/g, '');
        if (digits) {
            const number = parseInt(digits, 10);
            return number.toLocaleString('vi-VN') + ' ₫';
        }
        return priceStr;
    }

    // Get product ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');

    const products = window.dbProducts || [];
    
    const errorState = document.getElementById('errorState');
    const detailContainer = document.getElementById('productDetailContainer');

    // Find the product
    const product = products.find(p => String(p.id) === String(productId));

    if (!product) {
        // Show error state
        errorState.classList.remove('hidden');
        detailContainer.classList.add('hidden');
    } else {
        // Show detail state
        errorState.classList.add('hidden');
        detailContainer.classList.remove('hidden');

        // Populate breadcrumbs
        document.getElementById('breadcrumbBrand').innerText = product.brand;
        document.getElementById('breadcrumbName').innerText = product.name;

        // Populate Main Info
        const brandElem = document.getElementById('detailBrand');
        brandElem.innerText = product.brand;
        // Reset old brand colors and apply new one
        brandElem.className = `font-label-caps text-label-caps block mb-2 tracking-[0.2em] ${getBrandColor(product.brand)}`;
        
        document.getElementById('detailName').innerText = product.name;
        
        const priceElem = document.getElementById('detailPrice');
        if (product.raw_original_price > product.raw_price) {
            priceElem.innerHTML = `<span style="text-decoration: line-through; color: #888; font-size: 0.7em; margin-right: 12px; font-weight: normal;">${product.original_price}</span>${formatPrice(product.price)}`;
        } else {
            priceElem.innerText = formatPrice(product.price);
        }

        // Populate Gallery & Thumbnails
        const mainImage = document.getElementById('detailImage');
        if (mainImage) {
            mainImage.src = product.image;
            mainImage.alt = product.name;
        }

        const thumbsContainer = document.getElementById('detailThumbnails');
        if (thumbsContainer) {
            thumbsContainer.innerHTML = '';
            
            // Build the list of images including the main image at the front
            let imagesList = [];
            if (product.image) {
                imagesList.push(product.image);
            }
            if (product.additional_images) {
                try {
                    let additionalList = typeof product.additional_images === 'string' 
                        ? JSON.parse(product.additional_images) 
                        : product.additional_images;
                    if (Array.isArray(additionalList)) {
                        // filter out empty values
                        additionalList = additionalList.filter(Boolean);
                        imagesList = imagesList.concat(additionalList);
                    }
                } catch(e) {
                    console.error("Error parsing additional images:", e);
                }
            }

            // Render thumbnails if there's more than 1 image
            if (imagesList.length > 1) {
                imagesList.forEach((imgUrl, index) => {
                    const thumb = document.createElement('div');
                    thumb.className = 'detail-thumbnail' + (index === 0 ? ' detail-thumbnail--active' : '');
                    thumb.innerHTML = `<img src="${imgUrl}" alt="Thumbnail ${index + 1}"/>`;
                    
                    const switchImg = () => {
                        mainImage.src = imgUrl;
                        document.querySelectorAll('.detail-thumbnail').forEach(t => t.classList.remove('detail-thumbnail--active'));
                        thumb.classList.add('detail-thumbnail--active');
                    };
                    
                    thumb.addEventListener('click', switchImg);
                    thumb.addEventListener('mouseenter', switchImg);
                    
                    thumbsContainer.appendChild(thumb);
                });
            }
        }
        
        const stockElem = document.getElementById('detailStock');
        if (stockElem) {
            stockElem.innerText = product.stock !== undefined ? product.stock : 10;
        }

        // Populate Description and Specs (with fallback if empty)
        const descElem = document.getElementById('detailDescription');
        descElem.innerHTML = product.description || 'Chưa có mô tả cho sản phẩm này.';
        
        const specsElem = document.getElementById('detailSpecs');
        specsElem.innerText = product.specs || 'Chưa có thông số kỹ thuật cho sản phẩm này.';
        
        // Update document title for SEO
        document.title = `${product.name} | LENS & LIGHT`;
        
        // Add To Cart Logic
        document.getElementById('btnAddToCart').onclick = function() {
            const user = getCurrentUser();
            if (!user) {
                alert("Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!");
                window.location.href = 'index.php?page=login';
                return;
            }
            
            const currentStock = product.stock !== undefined ? product.stock : 10;
            if (currentStock <= 0) {
                alert("Sản phẩm này đã hết hàng!");
                return;
            }

            const cartKey = `cart_${user.username}`;
            let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
            
            // Check if product already in cart
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
                    stock: currentStock // save stock to cart for checkout checking
                });
            }
            localStorage.setItem(cartKey, JSON.stringify(cart));
            alert("Đã thêm sản phẩm vào giỏ hàng!");
            updateCartBadgeDetail();
        };

        // Favorite Logic
        const btnFavorite = document.getElementById('btnFavorite');
        if (btnFavorite) {
            btnFavorite.onclick = function() {
                handleFavorite(product.id, this);
            };
            // Setup initial color
            if (isFavorited(product.id)) {
                const icon = btnFavorite.querySelector('.material-symbols-outlined') || btnFavorite;
                if (icon) {
                    icon.style.color = 'var(--error)';
                    icon.style.fontVariationSettings = `"FILL" 1`;
                }
            }
        }
        
        updateCartBadgeDetail();
    }
    
    function updateCartBadgeDetail() {
        const user = getCurrentUser();
        if (!user) return;
        const cartKey = `cart_${user.username}`;
        const cart = JSON.parse(localStorage.getItem(cartKey)) || [];
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        
        const badge = document.getElementById('cartBadgeDetail');
        if (badge) {
            if (totalItems > 0) {
                badge.innerText = totalItems;
                badge.classList.remove('hidden');
                badge.classList.add('flex', 'items-center', 'justify-center', 'text-[10px]', 'text-on-primary', 'w-4', 'h-4', '-top-1', '-right-1');
            } else {
                badge.classList.add('hidden');
            }
        }
    }
    
    // Reviews Logic
    function loadReviews() {
        fetch(`index.php?action=get_reviews&ma_hh=${product.id}`)
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('reviewsList');
            if (data.success && data.reviews.length > 0) {
                list.innerHTML = data.reviews.map(r => `
                    <div class="review-card">
                        <div class="review-card-header">
                            <div class="review-user-info">
                                <span class="review-user-name">${r.ho_ten}</span>
                                <span class="review-username">@${r.username}</span>
                            </div>
                            <span class="review-date">${r.ngay_bl}</span>
                        </div>
                        <div class="review-stars">${'★'.repeat(r.so_sao)}${'☆'.repeat(5 - r.so_sao)}</div>
                        <p class="review-text">${r.noi_dung}</p>
                    </div>
                `).join('');
            } else {
                list.innerHTML = '<p style="color: var(--on-surface-variant); opacity: 0.7;">Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá sản phẩm này!</p>';
            }
        })
        .catch(err => {
            document.getElementById('reviewsList').innerHTML = '<p style="color: red;">Lỗi tải đánh giá.</p>';
        });
    }

    if (product) {
        loadReviews();

        document.getElementById('btnSubmitReview').onclick = function() {
            const content = document.getElementById('reviewContent').value.trim();
            const stars = document.getElementById('reviewStars').value;

            if (!content) {
                alert("Vui lòng nhập nội dung đánh giá!");
                return;
            }

            const payload = {
                ma_hh: product.id,
                so_sao: stars,
                noi_dung: content
            };

            fetch('index.php?action=add_review', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    document.getElementById('reviewContent').value = '';
                    loadReviews();
                } else {
                    alert(data.message || "Lỗi khi gửi đánh giá.");
                    if (data.message && data.message.includes("đăng nhập")) {
                        window.location.href = 'index.php?page=login';
                    }
                }
            })
            .catch(err => alert("Lỗi mạng. Vui lòng thử lại sau!"));
        };
    }
});

