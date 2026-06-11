<?php
// Môi trường hoạt động (development / production)
define('APP_ENV', 'development');

if (APP_ENV === 'development') {
    // Hiện thị lỗi nếu là môi trường dev
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Ẩn lỗi nếu là môi trường production
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Cấu hình Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'webmayanh');

// Cấu hình SMTP Email (Dùng cho SmtpMailer)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // hoặc 465 cho SSL
define('SMTP_USER', 'leduongtuananh.nhc@gmail.com'); // Thay email thật vào đây
define('SMTP_PASS', 'riri tzyh lxen yspc'); // Thay App Password vào đây
define('SMTP_FROM_NAME', 'Lens & Light Shop');
?>