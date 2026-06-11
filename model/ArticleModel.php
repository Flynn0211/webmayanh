<?php
class ArticleModel {
    /**
     * Lấy toàn bộ danh sách bài viết
     */
    public static function getAllArticles($conn, $status = null) {
        if ($conn === false) return [];
        $sql = "SELECT bv.*, tk.ho_ten AS tac_gia 
                FROM bai_viet bv 
                LEFT JOIN tai_khoan tk ON bv.ma_tk_dang = tk.ma_tk ";
        if ($status) {
            $sql .= "WHERE bv.trang_thai = :status ";
        }
        $sql .= "ORDER BY bv.ngay_dang DESC";
        
        $stmt = $conn->prepare($sql);
        if ($status) {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lấy chi tiết bài viết theo slug
     */
    public static function getArticleBySlug($conn, $slug) {
        if ($conn === false) return false;
        $sql = "SELECT bv.*, tk.ho_ten AS tac_gia 
                FROM bai_viet bv 
                LEFT JOIN tai_khoan tk ON bv.ma_tk_dang = tk.ma_tk 
                WHERE bv.slug = :slug LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Lấy chi tiết bài viết theo ID
     */
    public static function getArticleById($conn, $id) {
        if ($conn === false) return false;
        $sql = "SELECT bv.*, tk.ho_ten AS tac_gia 
                FROM bai_viet bv 
                LEFT JOIN tai_khoan tk ON bv.ma_tk_dang = tk.ma_tk 
                WHERE bv.ma_bv = :id LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Thêm bài viết mới
     */
    public static function addArticle($conn, $data) {
        if ($conn === false) return false;
        $sql = "INSERT INTO bai_viet (tieu_de, slug, anh_bia, tom_tat, noi_dung, ma_tk_dang, trang_thai) 
                VALUES (:tieu_de, :slug, :anh_bia, :tom_tat, :noi_dung, :ma_tk_dang, :trang_thai)";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':tieu_de' => $data['tieu_de'],
            ':slug' => $data['slug'],
            ':anh_bia' => $data['anh_bia'],
            ':tom_tat' => $data['tom_tat'],
            ':noi_dung' => $data['noi_dung'],
            ':ma_tk_dang' => $data['ma_tk_dang'],
            ':trang_thai' => $data['trang_thai']
        ]);
    }

    /**
     * Cập nhật bài viết
     */
    public static function updateArticle($conn, $id, $data) {
        if ($conn === false) return false;
        $sql = "UPDATE bai_viet SET 
                tieu_de = :tieu_de, 
                slug = :slug, 
                anh_bia = :anh_bia, 
                tom_tat = :tom_tat, 
                noi_dung = :noi_dung, 
                trang_thai = :trang_thai 
                WHERE ma_bv = :id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':tieu_de' => $data['tieu_de'],
            ':slug' => $data['slug'],
            ':anh_bia' => $data['anh_bia'],
            ':tom_tat' => $data['tom_tat'],
            ':noi_dung' => $data['noi_dung'],
            ':trang_thai' => $data['trang_thai'],
            ':id' => $id
        ]);
    }

    /**
     * Xóa bài viết
     */
    public static function deleteArticle($conn, $id) {
        if ($conn === false) return false;
        $sql = "DELETE FROM bai_viet WHERE ma_bv = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
