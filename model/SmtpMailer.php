<?php
// Load config
require_once __DIR__ . '/../config.php';

class SmtpMailer {
    public static function sendMail($to, $subject, $body) {
        $smtpHost = defined('SMTP_HOST') ? 'tls://' . str_replace(['tls://', 'ssl://'], '', SMTP_HOST) : 'tls://smtp.gmail.com';
        $smtpPort = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $username = defined('SMTP_USER') ? SMTP_USER : 'YOUR_EMAIL@gmail.com';
        $password = defined('SMTP_PASS') ? SMTP_PASS : 'YOUR_APP_PASSWORD';
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'LENS & LIGHT';

        if ($username === 'YOUR_EMAIL@gmail.com' || $username === 'your_email@gmail.com') {
            // Chưa cấu hình email, bỏ qua gửi thực tế để tránh lỗi treo hệ thống.
            error_log("SmtpMailer: Chưa cấu hình tài khoản email. Bỏ qua gửi email tới $to.");
            return false;
        }

        try {
            $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 15);
            if (!$socket) {
                return false;
            }

            self::readRes($socket);

            fputs($socket, "EHLO localhost\r\n");
            self::readRes($socket);

            fputs($socket, "AUTH LOGIN\r\n");
            self::readRes($socket);

            fputs($socket, base64_encode($username) . "\r\n");
            self::readRes($socket);

            fputs($socket, base64_encode($password) . "\r\n");
            self::readRes($socket);

            fputs($socket, "MAIL FROM: <" . $username . ">\r\n");
            self::readRes($socket);

            fputs($socket, "RCPT TO: <$to>\r\n");
            self::readRes($socket);

            fputs($socket, "DATA\r\n");
            self::readRes($socket);

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $fromName . " <" . $username . ">\r\n";
            $headers .= "To: $to\r\n";
            $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";

            $message = $headers . "\r\n" . $body . "\r\n.\r\n";
            fputs($socket, $message);
            self::readRes($socket);

            fputs($socket, "QUIT\r\n");
            fclose($socket);

            return true;
        } catch (Exception $e) {
            error_log("SmtpMailer Error: " . $e->getMessage());
            return false;
        }
    }

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
?>
