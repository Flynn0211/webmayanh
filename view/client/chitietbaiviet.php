<?php
$activeNav = 'baiviet';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= htmlspecialchars($article['tieu_de'] ?? $article['title'] ?? '') ?> - LENS &amp; LIGHT</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="assets/css/base.css" rel="stylesheet"/>
    <link href="assets/css/client.css" rel="stylesheet"/>
    <link href="assets/css/responsive.css" rel="stylesheet"/>
</head>
<body>

<?php include 'view/client/layout/_navbar.php'; ?>

<main>
    <article class="article-detail">
        <h1 class="article-detail__title"><?= htmlspecialchars($article['tieu_de'] ?? $article['title'] ?? '') ?></h1>
        <div class="article-detail__meta">
            <span class="material-symbols-outlined blog-icon-sm">calendar_today</span>
            <?= date('d/m/Y', strtotime($article['ngay_dang'] ?? $article['ngay_tao'] ?? $article['date'])) ?>
            <span class="article-detail__author">Tác giả: <?= htmlspecialchars($article['tac_gia'] ?? 'Admin') ?></span>
        </div>
        
        <?php 
        $img = $article['anh_bia'] ?? $article['anh_dai_dien'] ?? $article['image'] ?? '';
        if (!empty($img)): 
        ?>
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($article['tieu_de'] ?? $article['title'] ?? '') ?>" class="article-detail__img"/>
        <?php endif; ?>

        <div class="article-detail__content">
            <?= html_entity_decode($article['noi_dung'] ?? $article['content'] ?? '') // Decode HTML từ CKEditor ?>
        </div>
    </article>
</main>

<?php include 'view/client/layout/_footer_dark.php'; ?>

<script src="assets/js/auth.js?v=2.0"></script>
</body>
</html>
