<?php
// Môi trường hoạt động (development / production)
define('APP_ENV', 'production');

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
define('DB_HOST', 'sql202.infinityfree.com');
define('DB_USER', 'if0_42187545');
define('DB_PASS', 'Tuananh021104');
define('DB_NAME', 'if0_42187545_webmayanh');

// Cấu hình SMTP Email 
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'leduongtuananh.nhc@gmail.com'); // Email 
define('SMTP_PASS', 'riri tzyh lxen yspc'); // App Password 
define('SMTP_FROM_NAME', 'Lens & Light Shop');
?>