<?php
require 'model/database.php';
$stmt = $conn->query('SELECT ma_bv, tieu_de, slug, trang_thai FROM bai_viet');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
