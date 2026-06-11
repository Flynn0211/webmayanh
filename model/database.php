<?php
/**
 * Tệp kết nối Cơ sở dữ liệu MySQL sử dụng PDO
 * Đảm bảo hệ thống hoạt động ổn định, bảo mật và không bị rò rỉ thông tin máy chủ khi mất kết nối.
 */

// Nạp các hằng số cấu hình từ config.php
require_once __DIR__ . '/../config.php';

// Cấu hình thông tin kết nối cơ sở dữ liệu
$servername = defined('DB_HOST') ? DB_HOST : "localhost";
$username   = defined('DB_USER') ? DB_USER : "root";
$password   = defined('DB_PASS') ? DB_PASS : "";
$dbname     = defined('DB_NAME') ? DB_NAME : "webmayanh";

// Thực hiện khởi tạo kết nối cơ sở dữ liệu PDO
try {
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    // Thiết lập chế độ báo lỗi exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Thiết lập chế độ fetch mặc định là array kết hợp
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Nếu kết nối bị lỗi, gán $conn = false để bảo vệ ứng dụng không bị sập hoàn toàn
    $conn = false;
}