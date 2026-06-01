<?php $activeNav = 'trangchu'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="LENS & LIGHT – Công cụ quang học đỉnh cao dành cho những người thợ ảnh khắt khe nhất thế giới."/>
    <title>LENS &amp; LIGHT - Capturing Precision</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="assets/css/base.css" rel="stylesheet"/>
    <link href="assets/css/client.css?v=<?php echo time(); ?>" rel="stylesheet"/>
    <link href="assets/css/responsive.css?v=<?php echo time(); ?>" rel="stylesheet"/>
</head>
<body>

<?php include 'view/client/layout/_navbar.php'; ?>

<main>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-bg">
            <img class="hero-bg__img" alt="High-end camera equipment"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuC2Ob2lK3yLki_Od5qKHrUU-up0z-xQYobXAGzc7gtJdVH7PVmMY_7jzO7-L8aUG0_fKw2R-5T9qTW55uGNkpDguORG8Au01GoFojZeVGKDdtGAg6VSXlcxsIkqZcSc4SwR7hv9CY7AMm1ZOfq2okLDgyD_7Tqn95IdeVtZ96gPSKmeDr30IjX9WDr6iFgCpTAquSr9umZoXWPpFUCYgVZxSh4v_qxHVUPcmwYkMHPYV2YB0CqPc73ZRdb0_l5w9py-4dgLjF0aDhY"/>
            <div class="hero-bg__overlay"></div>
        </div>
        <div class="hero-content glass-dark">
            <span class="hero-eyebrow">THE ART OF OPTICS</span>
            <h1 class="hero-title">GÓI TRỌN TINH XẢO</h1>
            <p class="hero-desc">Công cụ quang học đỉnh cao dành cho những người thợ ảnh khắt khe nhất thế giới.</p>
            <div class="hero-actions">
                <button class="btn-hero-primary" onclick="window.location.href='index.php?page=ongkinh'">Khám Phá Bộ Sưu Tập</button>
                <button class="btn-hero-ghost" onclick="window.location.href='index.php?page=ongkinh'">Tìm hiểu thêm</button>
            </div>
        </div>
    </section>

    <div class="section-wrap">
        <!-- Featured Cameras -->
        <section class="py-section">
            <div class="section-heading-row">
                <div>
                    <h2>Máy Ảnh Nổi Bật</h2>
                    <div class="section-heading-bar"></div>
                </div>
                <a class="section-see-all" href="index.php?page=mayanh">
                    XEM TẤT CẢ
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
            <div id="productGrid" class="product-grid">
                <!-- Rendered by trangchu.js -->
            </div>
        </section>

        <!-- Editorial Teaser -->
        <section class="editorial-section mb-section">
            <div class="editorial-section__text">
                <p class="editorial-section__journal">JOURNAL NO. 12</p>
                <h2 class="editorial-section__title">Nghệ Thuật Của Sự Tối Giản Trong Nhiếp Ảnh Phong Cảnh</h2>
                <p class="editorial-section__body">Khám phá cách sử dụng không gian âm và những thiết bị tinh gọn nhất để truyền tải cảm xúc mạnh mẽ qua từng khung hình tối giản.</p>
                <a class="editorial-section__link" href="index.php?page=chitietbaiviet&slug=nghe-thuat-cua-su-toi-gian-trong-nhiep-anh-phong-canh">
                    ĐỌC BÀI VIẾT
                    <span class="material-symbols-outlined">east</span>
                </a>
            </div>
            <div class="editorial-section__img-wrap">
                <img class="editorial-section__img" alt="Minimalist Landscape"
                     src="https://lh3.googleusercontent.com/aida-public/AB6AXuCvwtqKfVUO-KG3R_hyQtLexRLS0JX4yyD2aftNRoFcYwSLxVHJRTc7bDRIi0Ci0JV81Onp7WoJda39Sh-tWLlos8EjGWSenCRZCwYc1W66fUIpTr1KiCV4nVfb1m_1U6NJNhTdi9y-B6U3Tana3IwIa4CPApN9a-kYp12jeJ2ALAZE-PmXHZ0-6yRpCZycuTdEm2kdzyqd24l4mJ0Q2p9wiogArzcQW6LR85tSaRbohrC4QOnPlEOaw2QMpyoWgIcZw2s6SP7O8bI"/>
            </div>
        </section>
    </div>

    <!-- Newsletter / CTA -->
    <section class="newsletter-section">
        <div class="newsletter-section__inner">
            <span class="material-symbols-outlined newsletter-section__icon">camera</span>
            <h2 class="newsletter-section__title">GIA NHẬP CỘNG ĐỒNG LENS &amp; LIGHT</h2>
            <p class="newsletter-section__desc">Nhận các thông tin sớm nhất về các sản phẩm giới hạn và kiến thức nhiếp ảnh chuyên sâu.</p>
            <form class="newsletter-form">
                <input class="newsletter-form__input" placeholder="Email của bạn" type="email" required/>
                <button class="newsletter-form__btn" type="submit">ĐĂNG KÝ</button>
            </form>
        </div>
    </section>
</main>

<?php include 'view/client/layout/_footer_dark.php'; ?>

<script src="assets/js/auth.js?v=2.0"></script>
<script src="assets/js/trangchu.js"></script>
</body>
</html>

