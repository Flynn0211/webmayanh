<?php
class ReviewModel {
    /**
     * Get reviews for a specific product
     */
    public static function getReviewsByProduct($conn, $ma_hh) {
        if ($conn === false) {
            return [];
        }

        $stmt = $conn->prepare("SELECT b.*, t.ho_ten, t.username FROM binh_luan_danh_gia b JOIN tai_khoan t ON b.ma_tk = t.ma_tk WHERE b.ma_hh = ? AND b.trang_thai = 'HienThi' ORDER BY b.ngay_bl DESC");
        if ($stmt) {
            $stmt->bind_param("i", $ma_hh);
            $stmt->execute();
            $res = $stmt->get_result();
            $reviews = [];
            while ($row = $res->fetch_assoc()) {
                $reviews[] = $row;
            }
            return $reviews;
        }
        return [];
    }

    /**
     * Add a review for a product
     */
    public static function addReview($conn, $ma_tk, $ma_hh, $so_sao, $noi_dung) {
        if ($conn === false) {
            return false;
        }

        $stmt = $conn->prepare("INSERT INTO binh_luan_danh_gia (ma_tk, ma_hh, so_sao, noi_dung, trang_thai) VALUES (?, ?, ?, ?, 'HienThi')");
        if ($stmt) {
            $stmt->bind_param("iiis", $ma_tk, $ma_hh, $so_sao, $noi_dung);
            return $stmt->execute();
        }
        return false;
    }
}
?>
