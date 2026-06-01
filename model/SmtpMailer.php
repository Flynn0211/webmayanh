<?php
/**
 * Lớp SmtpMailer thực hiện gửi thư điện tử bằng Socket kết nối trực tiếp đến SMTP Server (Gmail/Yahoo...)
 * Thiết kế tinh gọn, độc lập và không phụ thuộc vào các thư viện nặng nề như PHPMailer.
 */

// Nạp cấu hình hệ thống
require_once __DIR__ . '/../config.php';

class SmtpMailer {
    /**
     * Gửi email định dạng HTML thông qua Socket thô.
     *
     * @param string $to Địa chỉ nhận thư (VD: khachhang@gmail.com)
     * @param string $subject Tiêu đề thư tiếng Việt
     * @param string $body Nội dung thư viết bằng HTML
     * @return bool Trạng thái gửi thành công hay thất bại
     */
    public static function sendMail($to, $subject, $body) {
        // Lấy thông số từ config.php hoặc sử dụng mặc định
        $smtpHost = defined('SMTP_HOST') ? 'tls://' . str_replace(['tls://', 'ssl://'], '', SMTP_HOST) : 'tls://smtp.gmail.com';
        $smtpPort = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $username = defined('SMTP_USER') ? SMTP_USER : 'YOUR_EMAIL@gmail.com';
        $password = defined('SMTP_PASS') ? SMTP_PASS : 'YOUR_APP_PASSWORD';
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'LENS & LIGHT';

        // Bỏ qua nếu email chưa được cấu hình tài khoản thực tế
        if ($username === 'YOUR_EMAIL@gmail.com' || $username === 'your_email@gmail.com') {
            error_log("SmtpMailer: Chưa cấu hình tài khoản email thực tế. Bỏ qua gửi email tới $to.");
            return false;
        }

        try {
            // Mở kết nối Socket TCP
            $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 15);
            if (!$socket) {
                return false;
            }

            self::readRes($socket);

            // Gửi lời chào EHLO
            fputs($socket, "EHLO localhost\r\n");
            self::readRes($socket);

            // Bắt đầu quá trình xác thực AUTH
            fputs($socket, "AUTH LOGIN\r\n");
            self::readRes($socket);

            // Gửi tài khoản base64
            fputs($socket, base64_encode($username) . "\r\n");
            self::readRes($socket);

            // Gửi mật khẩu base64 (Mật khẩu ứng dụng - App Password)
            fputs($socket, base64_encode($password) . "\r\n");
            self::readRes($socket);

            // Xác định người gửi MAIL FROM
            fputs($socket, "MAIL FROM: <" . $username . ">\r\n");
            self::readRes($socket);

            // Xác định người nhận RCPT TO
            fputs($socket, "RCPT TO: <$to>\r\n");
            self::readRes($socket);

            // Bắt đầu truyền dữ liệu DATA
            fputs($socket, "DATA\r\n");
            self::readRes($socket);

            // Thiết lập các header email định dạng MIME chuẩn UTF-8
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $fromName . " <" . $username . ">\r\n";
            $headers .= "To: $to\r\n";
            $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";

            // Ghép tiêu đề, nội dung và kết thúc dữ liệu bằng ký tự đặc biệt [.\r\n]
            $message = $headers . "\r\n" . $body . "\r\n.\r\n";
            fputs($socket, $message);
            self::readRes($socket);

            // Thoát kết nối QUIT
            fputs($socket, "QUIT\r\n");
            fclose($socket);

            return true;
        } catch (Exception $e) {
            error_log("SmtpMailer Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Đọc phản hồi từ SMTP server thông qua socket
     */
    private static function readRes($socket) {
        $data = "";
        while ($str = fgets($socket, 515)) {
            $data .= $str;
            if (substr($str, 3, 1) == " ") {
                break;
            }
        }
        return $data;
    }
}
