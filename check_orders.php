<?php
require_once __DIR__ . '/model/database.php';

$stmt = $conn->query("SELECT ma_dh, trang_thai_don, tong_thanh_toan, ngay_dat FROM don_hang LIMIT 10");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($orders);

$stmt = $conn->query("SHOW COLUMNS FROM don_hang LIKE 'trang_thai_don'");
$col = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($col);
?>
