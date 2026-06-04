<?php
/**
 * Lớp UserModel quản lý các truy vấn liên quan đến thông tin tài khoản (người dùng/nhân viên).
 */
class UserModel {
    /**
     * Lấy thông tin tài khoản bằng tên đăng nhập (chỉ lấy tài khoản đang hoạt động)
     *
     * @param mysqli|false $conn Đối tượng kết nối CSDL
     * @param string $username Tên đăng nhập cần tìm
     * @return array|null Trả về mảng dữ liệu tài khoản nếu tìm thấy, ngược lại trả về null
     */
    public static function getUserByUsername($conn, $username) {
        if ($conn === false) {
            return null;
        }
        $stmt = $conn->prepare("SELECT * FROM tai_khoan WHERE username = ? AND trang_thai = 'HoatDong'");
        if ($stmt) {
            $stmt->execute([$username]);
            return $stmt->fetch();
        }
        return null;
    }

    /**
     * Đăng ký một tài khoản khách hàng mới vào cơ sở dữ liệu
     * Mật khẩu đăng ký sẽ được tự động băm (hash) bằng password_hash() đảm bảo an toàn.
     *
     * @param mysqli|false $conn Đối tượng kết nối CSDL
     * @param string $fullname Họ và tên đầy đủ
     * @param string $username Tên đăng nhập mong muốn
     * @param string $password Mật khẩu dạng thuần (sẽ băm trước khi lưu)
     * @return bool Trạng thái đăng ký thành công hay thất bại
     */
    public static function registerUser($conn, $fullname, $username, $password) {
        if ($conn === false) {
            return false;
        }
        $stmt = $conn->prepare("INSERT INTO tai_khoan (username, mat_khau, ho_ten, loai_tk, hang_thanh_vien, diem_tich_luy, trang_thai) VALUES (?, ?, ?, 'User', 'None', 0, 'HoatDong')");
        if ($stmt) {
            // Thực hiện băm mật khẩu chuẩn bảo mật cao chống tấn công dò mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            return $stmt->execute([$username, $hashed_password, $fullname]);
        }
        return false;
    }
}

