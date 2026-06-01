<?php
// Cấu hình thông tin cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "webmayanh";

// Tắt cảnh báo lỗi mặc định để tránh hiển thị lỗi thô lên màn hình
mysqli_report(MYSQLI_REPORT_OFF);

// Thực hiện kết nối
$conn = @new mysqli($servername, $username, $password, $dbname);

// Nếu kết nối bị lỗi, gán $conn = false để web không bị sập
if ($conn->connect_error) {
    $conn = false;
} else {
    $conn->set_charset("utf8mb4");
}
?>