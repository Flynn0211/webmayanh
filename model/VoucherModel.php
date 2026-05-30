<?php
class VoucherModel {
    /**
     * Validate a voucher code against cart total and constraints.
     */
    public static function validateVoucher($conn, $voucherCode, $totalRaw) {
        if ($conn === false || empty($voucherCode)) {
            return ['valid' => false, 'discount' => 0, 'message' => 'Lỗi kết nối hoặc mã trống'];
        }

        $stmt = $conn->prepare("SELECT * FROM voucher WHERE ma_code = ? AND trang_thai = 'HoatDong'");
        if ($stmt) {
            $stmt->bind_param("s", $voucherCode);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                // Check quantity
                if ($row['so_luong'] <= 0) {
                    return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'];
                }
                
                // Check date
                $now = date('Y-m-d H:i:s');
                if ($now < $row['ngay_bat_dau'] || $now > $row['ngay_het_han']) {
                    return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá không trong thời gian sử dụng.'];
                }
                
                // Check minimum order amount
                if ($totalRaw < $row['don_toi_thieu']) {
                    return ['valid' => false, 'discount' => 0, 'message' => 'Đơn hàng chưa đạt mức tối thiểu để dùng mã này.'];
                }
                
                // Calculate discount
                $discountAmount = 0;
                if ($row['loai_giam_gia'] === 'PhanTram') {
                    $discountAmount = ($totalRaw * $row['gia_tri_giam']) / 100;
                } else if ($row['loai_giam_gia'] === 'TienMat') {
                    $discountAmount = $row['gia_tri_giam'];
                }
                
                return [
                    'valid' => true, 
                    'discount' => $discountAmount,
                    'id' => $row['ma_voucher'],
                    'message' => 'Áp dụng mã giảm giá thành công.'
                ];
            }
        }
        return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá không tồn tại hoặc đã bị khóa.'];
    }
}
?>
