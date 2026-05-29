<?php

class ArticleController {
    private static $dataFile = __DIR__ . '/../data/articles.json';

    public static function initDefaultArticles() {
        if (!file_exists(dirname(self::$dataFile))) {
            mkdir(dirname(self::$dataFile), 0777, true);
        }

        if (!file_exists(self::$dataFile)) {
            $defaultArticles = [
                [
                    "id" => "BV01",
                    "title" => "Đánh giá chi tiết Sony A7R V: Siêu phẩm độ phân giải cao",
                    "slug" => "danh-gia-chi-tiet-sony-a7r-v",
                    "image" => "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=1000&auto=format&fit=crop",
                    "summary" => "Khám phá sức mạnh của cảm biến 61MP, hệ thống lấy nét AI mới nhất và khả năng quay video 8K trên chiếc máy ảnh mirrorless flagship của Sony.",
                    "content" => "<p>Sony A7R V mang đến một bước nhảy vọt về công nghệ lấy nét tự động nhờ vi xử lý AI chuyên dụng. Cảm biến 61MP vẫn giữ nguyên từ thế hệ trước nhưng chi tiết và dải tương phản động đã được tối ưu hóa xuất sắc. Đặc biệt, màn hình xoay lật 4 trục mới mang lại trải nghiệm chụp ảnh chưa từng có.</p><p>Hệ thống chống rung IBIS giờ đây đạt mức 8 stop, giúp bạn chụp cầm tay ở tốc độ màn trập rất thấp mà vẫn sắc nét.</p>",
                    "date" => date("Y-m-d H:i:s", strtotime("-2 days")),
                    "status" => "XuatBan"
                ],
                [
                    "id" => "BV02",
                    "title" => "Leica M11 Monochrom: Nghệ thuật nhiếp ảnh đen trắng",
                    "slug" => "leica-m11-monochrom-nghe-thuat-den-trang",
                    "image" => "https://images.unsplash.com/photo-1589410141973-10eb0a6231d6?q=80&w=1000&auto=format&fit=crop",
                    "summary" => "Chiếc máy ảnh rangefinder sinh ra chỉ để chụp ảnh đen trắng với cảm biến BSI CMOS 60MP loại bỏ hoàn toàn bộ lọc màng màu (Bayer filter).",
                    "content" => "<p>Leica M11 Monochrom là minh chứng cho sự theo đuổi sự hoàn hảo trong nhiếp ảnh thuần túy. Bằng cách loại bỏ bộ lọc màu, cảm biến hấp thụ nhiều ánh sáng hơn, mang lại dải nhạy sáng cực rộng và độ nhiễu cực thấp ở ISO cao.</p><p>Thiết kế tối giản, loại bỏ logo chấm đỏ quen thuộc, máy toát lên vẻ đẹp cổ điển và bí ẩn.</p>",
                    "date" => date("Y-m-d H:i:s", strtotime("-5 days")),
                    "status" => "XuatBan"
                ],
                [
                    "id" => "BV03",
                    "title" => "Top 5 ống kính Canon RF đáng mua nhất năm nay",
                    "slug" => "top-5-ong-kinh-canon-rf-dang-mua",
                    "image" => "https://images.unsplash.com/photo-1617005082833-1eb58ec8d1a1?q=80&w=1000&auto=format&fit=crop",
                    "summary" => "Hệ sinh thái ngàm RF của Canon đang ngày càng phong phú. Dưới đây là những ống kính 'must-have' dành cho người dùng EOS R.",
                    "content" => "<p>1. Canon RF 24-70mm f/2.8L IS USM: Ống kính đa dụng hoàn hảo cho mọi nhu cầu.<br>2. Canon RF 50mm f/1.2L USM: Sức mạnh xóa phông tuyệt đối.<br>3. Canon RF 70-200mm f/2.8L IS USM: Nhỏ gọn bất ngờ so với phiên bản ngàm EF.<br>4. Canon RF 85mm f/1.2L USM DS: Chân dung đỉnh cao với hiệu ứng bokeh mượt mà.<br>5. Canon RF 15-35mm f/2.8L IS USM: Lựa chọn số 1 cho phong cảnh và kiến trúc.</p>",
                    "date" => date("Y-m-d H:i:s", strtotime("-10 days")),
                    "status" => "XuatBan"
                ]
            ];
            file_put_contents(self::$dataFile, json_encode($defaultArticles, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }

    public static function getAllArticles() {
        self::initDefaultArticles();
        $json = file_get_contents(self::$dataFile);
        return json_decode($json, true) ?: [];
    }

    public static function getArticleBySlug($slug) {
        $articles = self::getAllArticles();
        foreach ($articles as $article) {
            if ($article['slug'] === $slug) {
                return $article;
            }
        }
        return null;
    }

    public static function saveArticles($articles) {
        file_put_contents(self::$dataFile, json_encode($articles, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    public static function generateId() {
        $articles = self::getAllArticles();
        $max = 0;
        foreach ($articles as $a) {
            $num = intval(str_replace('BV', '', $a['id']));
            if ($num > $max) $max = $num;
        }
        return 'BV' . str_pad($max + 1, 2, '0', STR_PAD_LEFT);
    }

    public static function handleAdminAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $action = isset($_POST['article_action']) ? $_POST['article_action'] : '';

        if ($action === 'add' || $action === 'edit') {
            $id = isset($_POST['id']) ? $_POST['id'] : '';
            $title = isset($_POST['title']) ? trim($_POST['title']) : '';
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'] ?? '')));
            $summary = isset($_POST['summary']) ? trim($_POST['summary']) : '';
            $content = isset($_POST['content']) ? trim($_POST['content']) : '';
            $status = isset($_POST['status']) ? trim($_POST['status']) : 'XuatBan';
            $imagePath = isset($_POST['old_image']) ? $_POST['old_image'] : '';

            // Handle image upload
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

            $articles = self::getAllArticles();
            if ($action === 'add') {
                $newArticle = [
                    "id" => self::generateId(),
                    "title" => $title,
                    "slug" => $slug,
                    "image" => $imagePath,
                    "summary" => $summary,
                    "content" => $content,
                    "date" => date("Y-m-d H:i:s"),
                    "status" => $status
                ];
                array_unshift($articles, $newArticle);
            } else {
                foreach ($articles as &$a) {
                    if ($a['id'] === $id) {
                        $a['title'] = $title;
                        $a['slug'] = $slug;
                        if ($imagePath !== '') $a['image'] = $imagePath;
                        $a['summary'] = $summary;
                        $a['content'] = $content;
                        $a['status'] = $status;
                        break;
                    }
                }
            }
            self::saveArticles($articles);
            echo "<script>window.location.href='index.php?tab=articles';</script>";
            exit;
        }

        if ($action === 'delete') {
            $id = isset($_POST['id']) ? $_POST['id'] : '';
            $articles = self::getAllArticles();
            $articles = array_filter($articles, function($a) use ($id) {
                return $a['id'] !== $id;
            });
            self::saveArticles(array_values($articles));
            echo "<script>window.location.href='index.php?tab=articles';</script>";
            exit;
        }
    }
}
?>
