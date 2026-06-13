<?php
/**
 * Lớp ReviewModel quản lý các thao tác truy vấn và ghi dữ liệu đánh giá, bình luận của sản phẩm.
 */
class ReviewModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Lấy toàn bộ danh sách đánh giá của một sản phẩm cụ thể (đã được duyệt hiển thị)
     *
     * @param int $ma_hh Mã hàng hóa (sản phẩm)
     * @return array Danh sách các đánh giá của sản phẩm kèm thông tin người gửi
     */
    public function getReviewsByProduct($ma_hh) {
        if ($this->conn === false) {
            return [];
        }

        // Truy vấn lấy danh sách bình luận kèm theo họ tên và username tài khoản tương ứng
        $stmt = $this->conn->prepare("SELECT b.*, t.ho_ten, t.username FROM binh_luan_danh_gia b JOIN tai_khoan t ON b.ma_tk = t.ma_tk WHERE b.ma_hh = ? AND b.trang_thai = 'HienThi' ORDER BY b.ngay_bl DESC");
        if ($stmt) {
            $stmt->execute([$ma_hh]);
            $reviews = [];
            while ($row = $stmt->fetch()) {
                $reviews[] = $row;
            }
            return $reviews;
        }
        return [];
    }

    /**
     * Thêm mới một bình luận đánh giá cho sản phẩm
     *
     * @param int $ma_tk Mã tài khoản của khách hàng đánh giá
     * @param int $ma_hh Mã hàng hóa được đánh giá
     * @param int $so_sao Số sao đánh giá (1-5)
     * @param string $noi_dung Nội dung bình luận chi tiết
     * @return bool Trạng thái thêm mới thành công hay thất bại
     */
    public function addReview($ma_tk, $ma_hh, $so_sao, $noi_dung) {
        if ($this->conn === false) {
            return false;
        }

        // Câu lệnh thêm mới bản ghi đánh giá mặc định ở trạng thái 'HienThi'
        $stmt = $this->conn->prepare("INSERT INTO binh_luan_danh_gia (ma_tk, ma_hh, so_sao, noi_dung, trang_thai) VALUES (?, ?, ?, ?, 'HienThi')");
        if ($stmt) {
            return $stmt->execute([$ma_tk, $ma_hh, $so_sao, $noi_dung]);
        }
        return false;
    }
}
