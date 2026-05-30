<?php
// Load config
require_once __DIR__ . '/../config.php';

// Cấu hình thông tin cơ sở dữ liệu
$servername = defined('DB_HOST') ? DB_HOST : "localhost";
$username = defined('DB_USER') ? DB_USER : "root";
$password = defined('DB_PASS') ? DB_PASS : "";
$dbname = defined('DB_NAME') ? DB_NAME : "webmayanh";

// Tắt cảnh báo lỗi mặc định để tránh hiển thị lỗi thô lên màn hình
mysqli_report(MYSQLI_REPORT_OFF);

// Thực hiện kết nối
$conn = @new mysqli($servername, $username, $password, $dbname);

// Nếu kết nối bị lỗi, gán $conn = false để web không bị sập
if ($conn->connect_error) {
    $conn = false;
    // Log the error secretly if needed, but don't output directly to user
} else {
    $conn->set_charset("utf8mb4");
}
?>