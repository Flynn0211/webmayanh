<?php
$activeNav = 'baiviet';
require_once __DIR__ . '/../../control/ArticleController.php';

$articles = ArticleController::getAllArticles();
$publishedArticles = array_filter($articles, function($a) {
    return $a['status'] === 'XuatBan';
});
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
    <style>
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            padding: 4rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }
        .blog-card {
            background: var(--surface-color);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }
        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }
        .blog-card__img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }
        .blog-card__body {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .blog-card__date {
            font-size: 0.85rem;
            color: var(--text-color-light);
            margin-bottom: 0.5rem;
        }
        .blog-card__title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        .blog-card__summary {
            font-size: 0.95rem;
            color: var(--text-color-muted);
            line-height: 1.6;
            margin-bottom: 1.5rem;
            flex: 1;
        }
        .blog-card__readmore {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--primary-color);
            text-transform: uppercase;
            font-size: 0.85rem;
        }
    </style>
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
                    <img src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="blog-card__img" loading="lazy"/>
                    <div class="blog-card__body">
                        <span class="blog-card__date"><?= date('d/m/Y', strtotime($article['date'])) ?></span>
                        <h2 class="blog-card__title"><?= htmlspecialchars($article['title']) ?></h2>
                        <p class="blog-card__summary"><?= htmlspecialchars($article['summary']) ?></p>
                        <div class="blog-card__readmore">
                            ĐỌC TIẾP <span class="material-symbols-outlined" style="font-size: 18px;">arrow_forward</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1 / -1; padding: 3rem; color: var(--text-color-light);">Hiện chưa có bài viết nào.</p>
        <?php endif; ?>
    </div>
</main>

<?php include 'view/client/layout/_footer_dark.php'; ?>

<script src="assets/js/auth.js?v=2.0"></script>
</body>
</html>
