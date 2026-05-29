<?php
$activeNav = 'baiviet';
require_once __DIR__ . '/../../control/ArticleController.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$article = ArticleController::getArticleBySlug($slug);

if (!$article || $article['status'] !== 'XuatBan') {
    echo "Bài viết không tồn tại hoặc đã bị ẩn.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= htmlspecialchars($article['title']) ?> - LENS &amp; LIGHT</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="assets/css/base.css" rel="stylesheet"/>
    <link href="assets/css/client.css" rel="stylesheet"/>
    <link href="assets/css/responsive.css" rel="stylesheet"/>
    <style>
        .article-detail {
            max-width: 800px;
            margin: 4rem auto;
            padding: 0 5%;
        }
        .article-detail__title {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
        }
        .article-detail__meta {
            color: var(--text-color-light);
            font-size: 0.95rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .article-detail__img {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 3rem;
        }
        .article-detail__content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-color);
        }
        .article-detail__content p {
            margin-bottom: 1.5rem;
        }
        .article-detail__content h2, .article-detail__content h3 {
            margin-top: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<?php include 'view/client/layout/_navbar.php'; ?>

<main>
    <article class="article-detail">
        <h1 class="article-detail__title"><?= htmlspecialchars($article['title']) ?></h1>
        <div class="article-detail__meta">
            <span class="material-symbols-outlined" style="font-size: 18px;">calendar_today</span>
            <?= date('d/m/Y', strtotime($article['date'])) ?>
        </div>
        
        <?php if (!empty($article['image'])): ?>
            <img src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="article-detail__img"/>
        <?php endif; ?>

        <div class="article-detail__content">
            <?= $article['content'] // Raw HTML from Editor ?>
        </div>
    </article>
</main>

<?php include 'view/client/layout/_footer_dark.php'; ?>

<script src="assets/js/auth.js?v=2.0"></script>
</body>
</html>
