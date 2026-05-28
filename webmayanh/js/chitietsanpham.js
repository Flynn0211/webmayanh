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

    // Get products from localStorage
    const products = JSON.parse(localStorage.getItem('products')) || [];
    
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
        document.getElementById('detailPrice').innerText = formatPrice(product.price);
        document.getElementById('detailImage').src = product.image;
        document.getElementById('detailImage').alt = product.name;
        
        const stockElem = document.getElementById('detailStock');
        if (stockElem) {
            stockElem.innerText = product.stock !== undefined ? product.stock : 10;
        }

        // Populate Description and Specs (with fallback if empty)
        const descElem = document.getElementById('detailDescription');
        descElem.innerText = product.description || 'Chưa có mô tả cho sản phẩm này.';
        
        const specsElem = document.getElementById('detailSpecs');
        specsElem.innerText = product.specs || 'Chưa có thông số kỹ thuật cho sản phẩm này.';
        
        // Update document title for SEO
        document.title = `${product.name} | LENS & LIGHT`;
        
        // Add To Cart Logic
        document.getElementById('btnAddToCart').onclick = function() {
            const user = getCurrentUser();
            if (!user) {
                alert("Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!");
                window.location.href = 'login.html';
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
        document.getElementById('btnFavorite').onclick = function() {
            const user = getCurrentUser();
            if (!user) {
                alert("Vui lòng đăng nhập để thêm vào Yêu thích!");
                window.location.href = 'login.html';
                return;
            }
            
            const favKey = `favorites_${user.username}`;
            let favs = JSON.parse(localStorage.getItem(favKey)) || [];
            
            if (!favs.includes(String(product.id))) {
                favs.push(String(product.id));
                localStorage.setItem(favKey, JSON.stringify(favs));
                alert("Đã thêm sản phẩm vào danh sách yêu thích!");
            } else {
                alert("Sản phẩm này đã có trong danh sách yêu thích của bạn!");
            }
        };
        
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
});
