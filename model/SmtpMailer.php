<?php
class SmtpMailer {
    // Để cấu hình tài khoản gửi mail thực tế, hãy điền thông tin vào 2 biến dưới đây:
    private static $smtpHost = 'tls://smtp.gmail.com';
    private static $smtpPort = 587; // or 465 with ssl://
    private static $username = 'YOUR_EMAIL@gmail.com'; // TODO: Thay thế bằng email của bạn
    private static $password = 'YOUR_APP_PASSWORD'; // TODO: Thay thế bằng mật khẩu ứng dụng Gmail của bạn

    public static function sendMail($to, $subject, $body) {
        if (self::$username === 'YOUR_EMAIL@gmail.com') {
            // Chưa cấu hình email, bỏ qua gửi thực tế để tránh lỗi treo hệ thống.
            error_log("SmtpMailer: Chưa cấu hình tài khoản email. Bỏ qua gửi email tới $to.");
            return false;
        }

        try {
            $socket = fsockopen(self::$smtpHost, self::$smtpPort, $errno, $errstr, 15);
            if (!$socket) {
                return false;
            }

            self::readRes($socket);

            fputs($socket, "EHLO localhost\r\n");
            self::readRes($socket);

            fputs($socket, "AUTH LOGIN\r\n");
            self::readRes($socket);

            fputs($socket, base64_encode(self::$username) . "\r\n");
            self::readRes($socket);

            fputs($socket, base64_encode(self::$password) . "\r\n");
            self::readRes($socket);

            fputs($socket, "MAIL FROM: <" . self::$username . ">\r\n");
            self::readRes($socket);

            fputs($socket, "RCPT TO: <$to>\r\n");
            self::readRes($socket);

            fputs($socket, "DATA\r\n");
            self::readRes($socket);

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: LENS & LIGHT <" . self::$username . ">\r\n";
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
