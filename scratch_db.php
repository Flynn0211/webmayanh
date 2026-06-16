<?php
require 'config.php';
require 'model/database.php';
$stmt = $conn->query("SELECT ma_hh, ten_hang_hoa, anh FROM hang_hoa WHERE anh LIKE '%img_%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
