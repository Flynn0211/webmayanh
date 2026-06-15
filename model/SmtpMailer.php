<?php
/**
 * Tệp tin: SmtpMailer.php
 * Chức năng: Xử lý logic và nghiệp vụ liên quan đến SmtpMailer
 * Tác giả: Nhóm Lập Trình Web Nâng Cao
 */

/**
 * Lớp SmtpMailer đã được cập nhật để sử dụng thư viện PHPMailer
 */

// Nạp cấu hình hệ thống
require_once __DIR__ . '/../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';

class SmtpMailer {
    private static $mailInstance = null;

    /**
     * Gửi email định dạng HTML thông qua PHPMailer
     *
     * @param string $to Địa chỉ nhận thư
     * @param string $subject Tiêu đề thư tiếng Việt
     * @param string $body Nội dung thư viết bằng HTML
     * @return bool Trạng thái gửi thành công hay thất bại
     */
    public static function sendMail($to, $subject, $body) {
        try {
            if (self::$mailInstance === null) {
                // Khởi tạo và thiết lập kết nối SMTP 1 lần duy nhất để tái sử dụng (giảm thời gian chờ)
                $smtpHost = defined('SMTP_HOST') ? str_replace(['tls://', 'ssl://'], '', SMTP_HOST) : 'smtp.gmail.com';
                $smtpPort = defined('SMTP_PORT') ? SMTP_PORT : 587;
                $username = defined('SMTP_USER') ? SMTP_USER : 'YOUR_EMAIL@gmail.com';
                $raw_password = defined('SMTP_PASS') ? SMTP_PASS : 'YOUR_APP_PASSWORD';
                $password = str_replace(' ', '', $raw_password);
                $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'LENS & LIGHT';

                if ($username === 'YOUR_EMAIL@gmail.com' || $username === 'your_email@gmail.com') {
                    error_log("SmtpMailer: Chưa cấu hình tài khoản email thực tế.");
                    return false;
                }

                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->SMTPKeepAlive = true; // Giữ kết nối mở để gửi nhiều email nhanh hơn
                $mail->Host       = $smtpHost;
                $mail->SMTPAuth   = true;
                $mail->Username   = $username;
                $mail->Password   = $password;
                
                if ($smtpPort == 465) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } else {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
                $mail->Port       = $smtpPort;
                $mail->CharSet    = 'UTF-8';
                $mail->setFrom($username, $fromName);
                
                self::$mailInstance = $mail;
            }

            $mail = self::$mailInstance;
            
            // Xóa địa chỉ cũ để gửi thư mới
            $mail->clearAddresses();
            $mail->clearAttachments();

            // Thiết lập người nhận và nội dung
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            
            // AltBody (Văn bản thuần) RẤT QUAN TRỌNG để chống bị đánh dấu là Spam
            $altBody = strip_tags(str_replace(['<br/>', '<br>', '</p>'], "\n", $body));
            $mail->AltBody = trim($altBody);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("SmtpMailer Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi email hàng loạt qua BCC (dùng cho khuyến mãi, bản tin)
     * @param array $bccList Danh sách email nhận
     * @param string $subject Tiêu đề
     * @param string $body Nội dung HTML
     */
    public static function sendNewsletter($bccList, $subject, $body) {
        if (empty($bccList)) return false;
        try {
            if (self::$mailInstance === null) {
                // Tạm gọi hàm sendMail với 1 tham số giả để khởi tạo $mailInstance
                self::sendMail('dummy@lenslight.com', 'Init', 'Init');
            }
            $mail = self::$mailInstance;
            $mail->clearAddresses();
            $mail->clearBCCs();
            $mail->clearAttachments();

            // Chỉ dùng 1 địa chỉ nhận chính (noreply hoặc chính email người gửi) để ẩn danh sách
            $mail->addAddress(defined('SMTP_USER') ? SMTP_USER : 'noreply@lenslight.com', 'LENS & LIGHT Newsletter');

            foreach ($bccList as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $mail->addBCC($email);
                }
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $altBody = strip_tags(str_replace(['<br/>', '<br>', '</p>'], "\n", $body));
            $mail->AltBody = trim($altBody);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("SmtpMailer Newsletter Error: " . $e->getMessage());
            return false;
        }
    }
}
