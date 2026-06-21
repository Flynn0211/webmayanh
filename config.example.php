<?php
// Môi trường hoạt động (development / production)
define('APP_ENV', 'production');

if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Cấu hình Database
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'webmayanh');

// Cấu hình SMTP Email 
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com'); // Email 
define('SMTP_PASS', 'your_app_password'); // App Password 
define('SMTP_FROM_NAME', 'Lens & Light Shop');
?>
