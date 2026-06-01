<?php $activeNav = 'mayanh'; ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LENS &amp; LIGHT - Chi Tiết Sản Phẩm</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link href="assets/css/base.css" rel="stylesheet" />
    <link href="assets/css/client.css?v=<?php echo time(); ?>" rel="stylesheet" />
    <link href="assets/css/responsive.css?v=<?php echo time(); ?>" rel="stylesheet" />
</head>

<body>

    <?php include 'view/client/layout/_navbar.php'; ?>

    <main class="section-wrap" style="padding-top:4rem; padding-bottom:var(--section-gap);">

        <!-- Error state -->
        <div id="errorState" class="detail-error hidden">
            <span class="material-symbols-outlined text-error"
                style="font-size:4rem;display:block;margin-bottom:1rem;">error</span>
            <h2>Không tìm thấy sản phẩm</h2>
            <p>Sản phẩm bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>
            <a href="index.php?page=trangchu" class="btn-back">Quay lại trang chủ</a>
        </div>

        <div id="productDetailContainer" class="hidden">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="index.php?page=trangchu">TRANG CHỦ</a>
                <span class="material-symbols-outlined">chevron_right</span>
                <a href="#" id="breadcrumbBrand">THƯƠNG HIỆU</a>
                <span class="material-symbols-outlined">chevron_right</span>
                <span id="breadcrumbName">TÊN SẢN PHẨM</span>
            </nav>

            <!-- Detail layout -->
            <div class="detail-layout">
                <!-- Gallery -->
                <div style="display: flex; flex-direction: column;">
                    <div class="detail-image-wrap">
                        <img id="detailImage" class="detail-image" alt="Product Image" src="" />
                    </div>
                    <!-- Thumbnails -->
                    <div id="detailThumbnails" class="detail-thumbnails-container">
                        <!-- JS injected thumbnails will go here -->
                    </div>
                </div>

                <!-- Info -->
                <div class="detail-info">
                    <span id="detailBrand" class="detail-brand">THƯƠNG HIỆU</span>
                    <h1 id="detailName" class="detail-name">Tên Sản Phẩm</h1>
                    <div id="detailPrice" class="detail-price">0 ₫</div>
                    <div class="detail-stock">
                        <span class="material-symbols-outlined"
                            style="font-size:1rem;margin-right:0.25rem;">inventory_2</span>
                        Tồn kho: <strong id="detailStock">0</strong>
                    </div>

                    <div id="detailDescription" class="detail-desc">Đang tải mô tả sản phẩm...</div>

                    <div class="detail-actions">
                        <button id="btnAddToCart" class="btn-add-cart">THÊM VÀO GIỎ HÀNG</button>
                        <button id="btnFavorite" class="btn-favorite">
                            <span class="material-symbols-outlined">favorite</span> YÊU THÍCH
                        </button>
                    </div>

                    <div class="detail-shipping">
                        <div class="detail-shipping__item">
                            <span class="material-symbols-outlined">local_shipping</span>
                            Giao hàng miễn phí toàn quốc
                        </div>
                        <div class="detail-shipping__item">
                            <span class="material-symbols-outlined">verified</span>
                            Bảo hành chính hãng 24 tháng
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specs -->
            <div class="detail-specs-section">
                <h2>THÔNG SỐ KỸ THUẬT</h2>
                <div id="detailSpecs" class="detail-specs-box">Đang tải thông số kỹ thuật...</div>
            </div>

            <!-- Reviews -->
            <div class="reviews-section">
                <h2>ĐÁNH GIÁ SẢN PHẨM</h2>

                <div id="reviewFormContainer" class="review-form-container">
                    <h3 class="review-form-title">Viết đánh giá của bạn</h3>
                    <div class="review-form-group">
                        <label class="review-form-label">Số sao:</label>
                        <select id="reviewStars" class="review-stars-select">
                            <option value="5">5 Sao (Tuyệt vời)</option>
                            <option value="4">4 Sao (Tốt)</option>
                            <option value="3">3 Sao (Khá)</option>
                            <option value="2">2 Sao (Tệ)</option>
                            <option value="1">1 Sao (Rất tệ)</option>
                        </select>
                    </div>
                    <div class="review-form-group">
                        <label class="review-form-label">Nội dung:</label>
                        <textarea id="reviewContent" rows="4" placeholder="Nhập đánh giá của bạn..." class="review-content-textarea"></textarea>
                    </div>
                    <button id="btnSubmitReview" class="review-submit-btn">GỬI ĐÁNH GIÁ</button>
                </div>

                <div id="reviewsList" class="reviews-list">
                    <p style="color: var(--on-surface-variant); opacity: 0.7;">Đang tải đánh giá...</p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'view/client/layout/_footer_dark.php'; ?>

    <script src="assets/js/auth.js?v=2.0"></script>
    <script src="assets/js/chitietsanpham.js?v=<?php echo time(); ?>"></script>
</body>

</html>