<?php
/**
 * Lớp ArticleController quản lý toàn bộ các bài viết (blog/tin tức) của hệ thống CMS
 * Sử dụng ArticleModel để tương tác với cơ sở dữ liệu MySQL thay vì JSON như trước.
 */

require_once __DIR__ . '/../model/database.php';
require_once __DIR__ . '/../model/ArticleModel.php';

class ArticleController {
    /**
     * Lấy toàn bộ danh sách bài viết từ Database.
     *
     * @param bool $onlyPublished Nếu true, chỉ lấy các bài viết có trạng thái XuatBan.
     * @return array Danh sách bài viết
     */
    public static function getAllArticles($onlyPublished = false) {
        global $conn;
        $status = $onlyPublished ? 'XuatBan' : null;
        return ArticleModel::getAllArticles($conn, $status);
    }

    /**
     * Tìm bài viết dựa trên đường dẫn tĩnh thân thiện (slug).
     *
     * @param string $slug Đường dẫn tĩnh
     * @return array|false Trả về thông tin bài viết nếu tìm thấy, ngược lại trả về false
     */
    public static function getArticleBySlug($slug) {
        global $conn;
        return ArticleModel::getArticleBySlug($conn, $slug);
    }

    /**
     * Tìm bài viết dựa trên ID.
     *
     * @param int $id ID bài viết
     * @return array|false
     */
    public static function getArticleById($id) {
        global $conn;
        return ArticleModel::getArticleById($conn, $id);
    }

    /**
     * Xử lý các hành động nghiệp vụ của quản trị viên (POST) cho bài viết.
     */
    public static function handleAdminAction() {
        global $conn;
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
            // Mặc định user đăng bài, trong thực tế sẽ lấy từ session
            $ma_tk = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; 

            // Xử lý upload tệp hình ảnh đại diện của bài viết
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/articles/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $imagePath = 'uploads/articles/' . $filename;
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
                ArticleModel::addArticle($conn, $data);
            } else {
                ArticleModel::updateArticle($conn, $id, $data);
            }
            
            echo "<script>window.location.href='index.php?tab=articles';</script>";
            exit;
        }

        // --- HÀNH ĐỘNG XÓA BÀI VIẾT ---
        if ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id > 0) {
                ArticleModel::deleteArticle($conn, $id);
            }
            echo "<script>window.location.href='index.php?tab=articles';</script>";
            exit;
        }
    }

    /**
     * Xử lý API Upload hình ảnh từ CKEditor
     * CKEditor yêu cầu trả về JSON có dạng { "url": "..." } khi thành công.
     */
    public static function handleCKEditorUpload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload'])) {
            $file = $_FILES['upload'];
            
            // Kiểm tra lỗi
            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['error' => ['message' => 'Lỗi upload file.']]);
                exit;
            }

            $uploadDir = __DIR__ . '/../uploads/articles/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Tạo tên file duy nhất tránh trùng lặp
            $filename = time() . '_' . basename($file['name']);
            $targetFile = $uploadDir . $filename;

            // Di chuyển file
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                // Đường dẫn trả về cho CKEditor (có thể là đường dẫn tuyệt đối hoặc tương đối)
                // Phụ thuộc vào cấu trúc URL của dự án. Ở đây trả về relative path từ thư mục gốc.
                
                // Lấy URL base hiện tại (thường thì admin đang ở /admin/, nên trả về ../uploads/articles/...)
                // Tuy nhiên, để linh hoạt, ta dùng đường dẫn tuyệt đối tĩnh dựa trên HTTP_HOST
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/webmayanh/";
                $url = $base_url . 'uploads/articles/' . $filename;

                echo json_encode([
                    'url' => $url
                ]);
            } else {
                echo json_encode(['error' => ['message' => 'Không thể lưu file.']]);
            }
            exit;
        }
    }
}
