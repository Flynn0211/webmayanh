<?php
require_once 'config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'trangchu';

// Basic routing mapping
$clientPages = ['trangchu', 'mayanh', 'ongkinh', 'chitietsanpham', 'giohang', 'donhang', 'login'];

if ($page === 'admin') {
    include 'view/admin/admin.php';
} elseif (in_array($page, $clientPages)) {
    include "view/client/{$page}.php";
} else {
    // 404
    echo "404 Not Found";
}
?>
