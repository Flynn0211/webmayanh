<?php
/**
 * Lớp VoucherModel xử lý các truy vấn và kiểm tra điều kiện áp dụng mã giảm giá (voucher).
 */
class VoucherModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Xác thực mã giảm giá dựa trên tổng tiền giỏ hàng và các giới hạn đi kèm.
     *
     * @param string $voucherCode Chuỗi mã giảm giá (VD: WELCOME10, KM200)
     * @param float $totalRaw Tổng giá trị tiền hàng trước giảm giá
     * @return array Kết quả trả về gồm: valid (hợp lệ), discount (tiền giảm), message (thông điệp), id (mã voucher)
     */
    public function validateVoucher($voucherCode, $totalRaw) {
        // Kiểm tra kết nối CSDL và dữ liệu đầu vào
        if ($this->conn === false || empty($voucherCode)) {
            return ['valid' => false, 'discount' => 0, 'message' => 'Lỗi kết nối hoặc mã giảm giá trống.'];
        }

        // Truy vấn tìm kiếm voucher đang hoạt động
        $stmt = $this->conn->prepare("SELECT * FROM voucher WHERE ma_code = ? AND trang_thai = 'HoatDong'");
        if ($stmt) {
            $stmt->execute([$voucherCode]);
            if ($row = $stmt->fetch()) {
                // 1. Kiểm tra số lượng lượt sử dụng còn lại
                if ($row['so_luong'] <= 0) {
                    return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'];
                }
                
                // 2. Kiểm tra thời hạn hiệu lực của voucher
                $now = date('Y-m-d H:i:s');
                if ($now < $row['ngay_bat_dau'] || $now > $row['ngay_het_han']) {
                    return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá không trong thời gian sử dụng.'];
                }
                
                // 3. Kiểm tra giá trị đơn hàng tối thiểu có đạt yêu cầu hay không
                if ($totalRaw < $row['don_toi_thieu']) {
                    return ['valid' => false, 'discount' => 0, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã giảm giá này.'];
                }
                
                // 4. Tính toán số tiền được chiết khấu giảm giá
                $discountAmount = 0;
                if ($row['loai_giam_gia'] === 'PhanTram') {
                    $discountAmount = ($totalRaw * $row['gia_tri_giam']) / 100;
                } else if ($row['loai_giam_gia'] === 'TienMat') {
                    $discountAmount = $row['gia_tri_giam'];
                }
                
                return [
                    'valid' => true, 
                    'discount' => $discountAmount,
                    'type' => $row['loai_giam_gia'],
                    'value' => (float)$row['gia_tri_giam'],
                    'id' => $row['ma_voucher'],
                    'message' => 'Áp dụng mã giảm giá thành công.'
                ];
            }
        }
        return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá không tồn tại hoặc đã bị vô hiệu hóa.'];
    }
}
