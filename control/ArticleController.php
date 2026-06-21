<?php
/**
 * Tệp tin: ArticleController.php
 * Chức năng: Xử lý logic và nghiệp vụ liên quan đến ArticleController
 * Tác giả: Nhóm Lập Trình Web Nâng Cao
 */

/**
 * Lớp ArticleController quản lý toàn bộ các bài viết (blog/tin tức) của hệ thống CMS
 */
require_once __DIR__ . '/../model/database.php';
require_once __DIR__ . '/../model/ArticleModel.php';

class ArticleController {
    private $conn;
    private $articleModel;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->articleModel = new ArticleModel($conn);
    }

    /**
     * Lấy toàn bộ danh sách bài viết từ Database.
     *
     * @param bool $onlyPublished Nếu true, chỉ lấy các bài viết có trạng thái XuatBan.
     * @return array Danh sách bài viết
     */
    public function getAllArticles($onlyPublished = false) {
        $status = $onlyPublished ? 'XuatBan' : null;
        return $this->articleModel->getAllArticles($status);
    }

    /**
     * Tìm bài viết dựa trên đường dẫn tĩnh thân thiện (slug).
     *
     * @param string $slug Đường dẫn tĩnh
     * @return array|false Trả về thông tin bài viết nếu tìm thấy, ngược lại trả về false
     */
    public function getArticleBySlug($slug) {
        return $this->articleModel->getArticleBySlug($slug);
    }

    /**
     * Tìm bài viết dựa trên ID.
     *
     * @param int $id ID bài viết
     * @return array|false
     */
    public function getArticleById($id) {
        return $this->articleModel->getArticleById($id);
    }

    /**
     * Xử lý các hành động nghiệp vụ của quản trị viên (POST) cho bài viết.
     * Bao gồm: Thêm mới, Cập nhật, Xóa bài viết và tải lên (upload) ảnh bìa đại diện.
     */
    public function handleAdminAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $action = isset($_POST['article_action']) ? $_POST['article_action'] : '';

        // --- HÀNH ĐỘNG THÊM HOẶC SỬA BÀI VIẾT ---
        if ($action === 'add' || $action === 'edit') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $title = isset($_POST['title']) ? trim($_POST['title']) : '';
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'] ?? '')));
            $summary = isset($_POST['summary']) ? trim($_POST['summary']) : '';
            // Nhận nội dung mã HTML từ CKEditor
            $content = isset($_POST['content']) ? trim($_POST['content']) : '';
            $status = isset($_POST['status']) ? trim($_POST['status']) : 'XuatBan';
            $imagePath = isset($_POST['old_image']) ? $_POST['old_image'] : '';
            $ma_tk = 1;
            if (isset($_SESSION['admin_username'])) {
                $stmt_tk = $this->conn->prepare("SELECT ma_tk FROM tai_khoan WHERE username = ?");
                $stmt_tk->execute([$_SESSION['admin_username']]);
                if ($row_tk = $stmt_tk->fetch()) {
                    $ma_tk = $row_tk['ma_tk'];
                }
            }

            // Xử lý upload tệp hình ảnh đại diện (Thumbnail/Cover) của bài viết
            // Tải file vật lý vào thư mục máy chủ (uploads/articles/) thay vì lưu mã hóa tĩnh vào Database
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    die("Lỗi tải ảnh lên máy chủ (Mã lỗi: " . $_FILES['image']['error'] . "). Vui lòng kiểm tra lại kích thước ảnh hoặc cấu hình PHP.");
                }
                
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                
                // --- BẢO MẬT: Kiểm tra loại file tải lên để chống tải lên mã độc (RCE) ---
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($ext, $allowedExtensions)) {
                    die("Lỗi: Chỉ cho phép tải lên các tệp hình ảnh định dạng JPG, PNG, GIF, WEBP.");
                }
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);
                if (strpos($mimeType, 'image/') !== 0) {
                    die("Lỗi: Tệp tin không phải là hình ảnh hợp lệ. Phát hiện gian lận định dạng tệp.");
                }
                
                $filename = uniqid('art_') . '.' . $ext;
                $targetDir = __DIR__ . '/../uploads/articles/';
                
                // Tự động tạo thư mục uploads/articles nếu chưa có
                if (!is_dir($targetDir)) {
                    if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                        die("Lỗi: Không thể tạo thư mục lưu trữ ảnh bài viết tại {$targetDir}. Vui lòng phân quyền ghi (CHMOD) cho thư mục cha.");
                    }
                }
                
                $targetPath = $targetDir . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imagePath = 'uploads/articles/' . $filename;
                } else {
                    die("Lỗi: Không thể ghi file ảnh vật lý lên máy chủ tại {$targetPath}. Vui lòng kiểm tra dung lượng ổ đĩa và cấp quyền ghi (CHMOD 755 hoặc 777) cho thư mục uploads/articles/.");
                }
            }

            $data = [
                'tieu_de' => $title,
                'slug' => $slug,
                'anh_bia' => $imagePath,
                'tom_tat' => $summary,
                'noi_dung' => $content,
                'ma_tk_dang' => $ma_tk,
                'trang_thai' => $status
            ];

            if ($action === 'add') {
                $success = $this->articleModel->addArticle($data);
            } else {
                $success = $this->articleModel->updateArticle($id, $data);
            }
            
            if (!$success) {
                // In ra lỗi
                die("Lỗi thao tác CSDL. Vui lòng thử lại sau.");
            }
            
            echo "<script>window.location.href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?tab=articles';</script>";
            exit;
        }

        // --- HÀNH ĐỘNG XÓA BÀI VIẾT ---
        if ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id > 0) {
                $this->articleModel->deleteArticle($id);
            }
            echo "<script>window.location.href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?tab=articles';</script>";
            exit;
        }
    }

    /**
     * Xử lý API Upload hình ảnh nội dung từ trình soạn thảo văn bản phong phú CKEditor.
     * Tự động kiểm tra bảo mật phần mở rộng (extension) và định dạng thực (MIME type) chống tải lên mã độc.
     * CKEditor yêu cầu kết quả trả về bằng JSON có dạng { "url": "..." } khi xử lý thành công.
     */
    public function handleCKEditorUpload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload'])) {
            // Xác thực bắt buộc: chỉ Admin mới được upload ảnh
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (empty($_SESSION['admin_logged_in'])) {
                echo json_encode(['error' => ['message' => 'Lỗi: Không có quyền truy cập.']]);
                exit;
            }

            $file = $_FILES['upload'];
            
            // Kiểm tra lỗi
            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['error' => ['message' => 'Lỗi upload file.']]);
                exit;
            }

            // Kiểm tra định dạng file an toàn (Security Fix)
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Kiểm tra MIME type thực tế của file để chống giả mạo đuôi
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!in_array($fileExtension, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
                echo json_encode(['error' => ['message' => 'Lỗi: Định dạng file không được phép! Chỉ chấp nhận ảnh JPG, PNG, GIF, WEBP.']]);
                exit;
            }

            // Lưu file vào thư mục tĩnh
            $filename = uniqid('ck_') . '.' . $fileExtension;
            $targetDir = __DIR__ . '/../uploads/articles/';
            
            // Tự động tạo thư mục uploads/articles nếu chưa có
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                    echo json_encode(['error' => ['message' => "Lỗi: Không thể tạo thư mục lưu trữ tại {$targetDir}. Vui lòng phân quyền ghi (CHMOD) cho thư mục cha."]]);
                    exit;
                }
            }
            
            $targetPath = $targetDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                echo json_encode([
                    'uploaded' => 1,
                    'fileName' => $filename,
                    'url' => 'uploads/articles/' . $filename
                ]);
            } else {
                echo json_encode(['error' => ['message' => "Lỗi: Không thể lưu file tại {$targetPath}. Vui lòng kiểm tra quyền ghi (CHMOD 755 hoặc 777) của thư mục uploads/articles/."]]);
            }
            exit;
        }
    }
}
