<?php
require_once __DIR__ . '/../model/SmtpMailer.php';

class ContactController {
    public static function submitContact() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';

            if (empty($fullname) || empty($email) || empty($phone) || empty($message)) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin.']);
                return;
            }

            // Gửi email cho người quản trị
            $adminEmail = defined('SMTP_USER') ? SMTP_USER : 'admin@example.com';
            $subjectAdmin = "Liên hệ mới từ khách hàng: $fullname";
            $bodyAdmin = "
                <h3>Thông tin liên hệ:</h3>
                <p><strong>Họ tên:</strong> $fullname</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Số điện thoại:</strong> $phone</p>
                <p><strong>Nội dung:</strong><br/>" . nl2br(htmlspecialchars($message)) . "</p>
            ";

            $isSent = SmtpMailer::sendMail($adminEmail, $subjectAdmin, $bodyAdmin);

            if ($isSent) {
                // Tùy chọn: Gửi email tự động trả lời cho khách hàng
                $subjectClient = "Cảm ơn bạn đã liên hệ LENS & LIGHT";
                $bodyClient = "
                    <html>
                    <head>
                    <title>Cảm ơn bạn đã liên hệ</title>
                    </head>
                    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                            <h2 style='color: #e63946;'>Xin chào $fullname,</h2>
                            <p>Cảm ơn bạn đã liên hệ với <strong>LENS & LIGHT</strong>. Chúng tôi đã nhận được lời nhắn của bạn và sẽ phản hồi trong thời gian sớm nhất.</p>
                            <div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #e63946; margin-top: 20px;'>
                                <p><strong>Nội dung lời nhắn của bạn:</strong></p>
                                <p>" . nl2br(htmlspecialchars($message)) . "</p>
                            </div>
                            <br/>
                            <p>Trân trọng,<br/><strong>Đội ngũ LENS & LIGHT</strong></p>
                        </div>
                    </body>
                    </html>
                ";
                SmtpMailer::sendMail($email, $subjectClient, $bodyClient);

                echo json_encode(['success' => true, 'message' => 'Cảm ơn bạn đã liên hệ! Lời nhắn của bạn đã được ghi nhận.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Đã có lỗi xảy ra khi gửi lời nhắn hoặc cấu hình email chưa được thiết lập. Vui lòng thử lại sau.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        }
    }

    /**
     * Xử lý đăng ký nhận bản tin (Newsletter)
     * Kèm theo tính năng tự động cập nhật email cho tài khoản nếu đang trống
     */
    public static function handleNewsletter() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid Request']);
            return;
        }

        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email không hợp lệ.']);
            return;
        }

        // 1. Lưu email vào bảng những người đăng ký (nếu chưa có)
        global $conn;
        $stmt_ins = $conn->prepare("INSERT IGNORE INTO email_dang_ky (email) VALUES (?)");
        $stmt_ins->execute([$email]);

        // 2. Nếu user đang đăng nhập mà chưa có email, tự động cập nhật email vào CSDL
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user']['username'])) {
            $username = $_SESSION['user']['username'];
            
            // Kiểm tra email hiện tại của tài khoản
            $stmt = $conn->prepare("SELECT email FROM tai_khoan WHERE username = ?");
            $stmt->execute([$username]);
            $row = $stmt->fetch();
            
            if ($row && empty($row['email'])) {
                $update = $conn->prepare("UPDATE tai_khoan SET email = ? WHERE username = ?");
                $update->execute([$email, $username]);
                // Cập nhật lại session
                $_SESSION['user']['email'] = $email;
            }
        }

        // 2. Gửi thư chào mừng (Welcome Email)
        require_once __DIR__ . '/../model/SmtpMailer.php';
        $subject = "Chào mừng bạn đến với cộng đồng LENS & LIGHT";
        $body = "
            <html>
            <head><title>Chào mừng bạn</title></head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                    <h2 style='color: #e63946;'>Xin chào!</h2>
                    <p>Cảm ơn bạn đã đăng ký nhận bản tin từ <strong>LENS & LIGHT</strong>.</p>
                    <p>Từ nay, bạn sẽ là một trong những người đầu tiên nhận được tin tức về các dòng máy ảnh cao cấp, thủ thuật nhiếp ảnh chuyên sâu và các chương trình khuyến mãi độc quyền từ shop.</p>
                    <br/>
                    <p>Trân trọng,<br/><strong>Đội ngũ LENS & LIGHT</strong></p>
                </div>
            </body>
            </html>
        ";
        
        // Gửi ngầm (không quan tâm kết quả trả về để không làm gián đoạn luồng UI)
        SmtpMailer::sendMail($email, $subject, $body);

        echo json_encode(['success' => true, 'message' => 'Đăng ký thành công!']);
    }
}
