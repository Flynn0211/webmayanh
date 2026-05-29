<?php
require 'model/database.php';
$orders = $conn->query('SELECT * FROM don_hang')->fetch_all(MYSQLI_ASSOC);
$details = $conn->query('SELECT * FROM chi_tiet_don_hang')->fetch_all(MYSQLI_ASSOC);
echo json_encode(['orders' => $orders, 'details' => $details], JSON_PRETTY_PRINT);
?>
