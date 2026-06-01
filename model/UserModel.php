<?php
class UserModel {
    /**
     * Retrieve a user by username if active.
     */
    public static function getUserByUsername($conn, $username) {
        if ($conn === false) {
            return null;
        }
        $stmt = $conn->prepare("SELECT * FROM tai_khoan WHERE username = ? AND trang_thai = 'HoatDong'");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $res = $stmt->get_result();
            return $res->fetch_assoc();
        }
        return null;
    }

    /**
     * Insert a new user into the tai_khoan table.
     */
    public static function registerUser($conn, $fullname, $username, $password) {
        if ($conn === false) {
            return false;
        }
        $stmt = $conn->prepare("INSERT INTO tai_khoan (username, mat_khau, ho_ten, loai_tk, hang_thanh_vien, diem_tich_luy, trang_thai) VALUES (?, ?, ?, 'User', 'None', 0, 'HoatDong')");
        if ($stmt) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("sss", $username, $hashed_password, $fullname);
            return $stmt->execute();
        }
        return false;
    }
}
?>

