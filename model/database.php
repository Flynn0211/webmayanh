<?php
/**
 * Tệp kết nối Cơ sở dữ liệu MySQL sử dụng MySQLi
 * Đảm bảo hệ thống hoạt động ổn định, bảo mật và không bị rò rỉ thông tin máy chủ khi mất kết nối.
 */

// Nạp các hằng số cấu hình từ config.php
require_once __DIR__ . '/../config.php';

// Cấu hình thông tin kết nối cơ sở dữ liệu
$servername = defined('DB_HOST') ? DB_HOST : "localhost";
$username   = defined('DB_USER') ? DB_USER : "root";
$password   = defined('DB_PASS') ? DB_PASS : "";
$dbname     = defined('DB_NAME') ? DB_NAME : "webmayanh";

// Tắt chế độ báo cáo lỗi mặc định của mysqli để tránh hiển thị các lỗi hệ thống thô ra màn hình
mysqli_report(MYSQLI_REPORT_OFF);

// Thực hiện khởi tạo kết nối cơ sở dữ liệu
$conn = @new mysqli($servername, $username, $password, $dbname);

// Kiểm tra lỗi kết nối
if ($conn->connect_error) {
    // Nếu kết nối bị lỗi, gán $conn = false để bảo vệ ứng dụng không bị sập hoàn toàn
    $conn = false;
} else {
    // Thiết lập bảng mã UTF-8 để hiển thị tiếng Việt chính xác
    $conn->set_charset("utf8mb4");
}