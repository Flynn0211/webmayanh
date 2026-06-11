<?php
$activeNav = 'baiviet';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="Khám phá những tin tức, đánh giá, thủ thuật nhiếp ảnh mới nhất từ LENS & LIGHT."/>
    <title>LENS &amp; LIGHT - Blog Nhiếp Ảnh</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="assets/css/base.css" rel="stylesheet"/>
    <link href="assets/css/client.css" rel="stylesheet"/>
    <link href="assets/css/responsive.css" rel="stylesheet"/>
</head>
<body>

<?php include 'view/client/layout/_navbar.php'; ?>

<main>
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header__inner">
            <h1 class="page-header__title">TIN TỨC &amp; ĐÁNH GIÁ</h1>
            <p class="page-header__desc">Khám phá thế giới nhiếp ảnh qua lăng kính chuyên gia.</p>
        </div>
    </div>

    <!-- Articles Grid -->
    <div class="blog-grid">
        <?php if (count($publishedArticles) > 0): ?>
            <?php foreach ($publishedArticles as $article): ?>
                <a href="index.php?page=chitietbaiviet&slug=<?= urlencode($article['slug']) ?>" class="blog-card">
                    <img src="<?= htmlspecialchars($article['anh_bia'] ?? $article['anh_dai_dien'] ?? $article['image'] ?? '') ?>" alt="<?= htmlspecialchars($article['tieu_de'] ?? $article['title'] ?? '') ?>" class="blog-card__img" loading="lazy"/>
                    <div class="blog-card__body">
                        <span class="blog-card__date"><?= date('d/m/Y', strtotime($article['ngay_dang'] ?? $article['ngay_tao'] ?? $article['date'])) ?></span>
                        <h2 class="blog-card__title"><?= htmlspecialchars($article['tieu_de'] ?? $article['title'] ?? '') ?></h2>
                        <p class="blog-card__summary"><?= htmlspecialchars($article['tom_tat'] ?? $article['mo_ta_ngan'] ?? $article['summary'] ?? '') ?></p>
                        <div class="blog-card__readmore">
                            ĐỌC TIẾP <span class="material-symbols-outlined blog-icon-sm">arrow_forward</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="blog-empty-msg">Hiện chưa có bài viết nào.</p>
        <?php endif; ?>
    </div>
</main>

<?php include 'view/client/layout/_footer_dark.php'; ?>

<script src="assets/js/auth.js?v=2.0"></script>
</body>
</html>
