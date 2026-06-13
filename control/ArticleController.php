<?php
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

            // Xử lý upload tệp hình ảnh đại diện của bài viết
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // Mã hóa trực tiếp sang Base64
                $fileTmp = $_FILES['image']['tmp_name'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $fileTmp);
                finfo_close($finfo);
                
                $data = file_get_contents($fileTmp);
                $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($data);
                $imagePath = $base64;
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
                // In ra lỗi để debug
                $errorInfo = $this->conn->errorInfo();
                die("Lỗi thao tác CSDL: " . print_r($errorInfo, true));
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
     * Xử lý API Upload hình ảnh từ CKEditor
     * CKEditor yêu cầu trả về JSON có dạng { "url": "..." } khi thành công.
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

            // Xử lý ghi trực tiếp ảnh dưới dạng Base64
            $data = file_get_contents($file['tmp_name']);
            $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($data);

            echo json_encode([
                'uploaded' => 1,
                'fileName' => $file['name'],
                'url' => $base64
            ]);
            exit;
        }
    }
}
