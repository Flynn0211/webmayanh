<?php
/**
 * Tệp tin: AuthController.php
 * Chức năng: Xử lý logic và nghiệp vụ liên quan đến AuthController
 * Tác giả: Nhóm Lập Trình Web Nâng Cao
 */

/**
 * Lớp AuthController điều phối toàn bộ các xử lý liên quan đến xác thực tài khoản (Đăng nhập, Đăng ký, Đăng xuất)
 * Đồng bộ trạng thái phiên (Session) giữa khách hàng (Client) và quản trị viên (Admin), tự động cập nhật băm mật khẩu bảo mật cao (bcrypt) và quản lý hồ sơ tài khoản.
 */

// Nạp tầng nghiệp vụ CSDL của tài khoản người dùng
require_once __DIR__ . '/../model/UserModel.php';

class AuthController {
    private $conn;
    private $userModel;

    public function __construct($conn) {
        $this->conn = $conn;
        
        $this->userModel = new UserModel($conn);
    }

    /**
     * Xử lý xác thực đăng nhập phía khách hàng (Client Login).
     *
     * @param PDO|false $conn Kết nối CSDL
     * @return array Mảng trạng thái lỗi hoặc thông báo thành công
     */
    public function handleLogin($conn) {
        $login_error = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_login'])) {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';

            // Lấy thông tin tài khoản bằng tên đăng nhập
            $row = $this->userModel->getUserByUsername($username);
            if ($row) {
                $is_password_correct = false;
                
                // 1. Kiểm tra bằng cơ chế so khớp băm chuẩn của PHP
                if (password_verify($password, $row['mat_khau'])) {
                    $is_password_correct = true;
                } 
                // 2. Cơ chế dự phòng (Migration): Nếu là mật khẩu thô dạng cũ, thực hiện đối chiếu thô và tự động nâng cấp sang mã hóa băm bảo mật
                elseif ($password === $row['mat_khau']) {
                    $is_password_correct = true;
                    // Tự động nâng cấp mật khẩu thô lên băm bcrypt lưu xuống CSDL
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upgradeStmt = $this->conn->prepare("UPDATE tai_khoan SET mat_khau = ? WHERE username = ?");
                    if ($upgradeStmt) {
                        $upgradeStmt->execute([$newHash, $username]);
                    }
                }

                if ($is_password_correct) {
                    // Thiết lập thông tin phiên SESSION của Client
                    $_SESSION['client_logged_in'] = true;
                    $_SESSION['client_username']  = $row['username'];
                    $_SESSION['client_fullname']  = $row['ho_ten'];
                    $_SESSION['client_role']      = strtolower($row['loai_tk']);
                    $_SESSION['client_email']     = $row['email'];
                    $_SESSION['client_phone']     = $row['sdt'];

                    // Nếu là tài khoản Admin, cấp thêm quyền SESSION Quản trị và chuyển hướng thẳng vào Admin Dashboard
                    if ($row['loai_tk'] === 'Admin') {
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_username']  = $row['username'];
                        $_SESSION['admin_fullname']  = $row['ho_ten'];

                        header("Location: admin/index.php");
                        exit;
                    } else {
                        header("Location: index.php?page=trangchu");
                        exit;
                    }
                } else {
                    $login_error = "Mật khẩu đăng nhập không chính xác!";
                }
            } else {
                $login_error = "Tài khoản không tồn tại hoặc đã bị khóa!";
            }
        }

        return ['error' => $login_error, 'success' => ''];
    }

    /**
     * Xử lý đăng ký tài khoản khách hàng mới.
     *
     * @param PDO|false $conn Kết nối CSDL
     * @return array Kết quả lỗi hoặc thành công
     */
    public function handleRegister($conn) {
        $login_error = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_register'])) {
            $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';

            // Kiểm tra tính duy nhất của tên đăng nhập
            $existing = $this->userModel->getUserByUsername($username);
            if ($existing) {
                $login_error = "Tên đăng nhập này đã tồn tại trên hệ thống!";
            } else {
                // Thực thi thêm mới tài khoản
                if ($this->userModel->registerUser($fullname, $username, $password)) {
                    // Tự động đăng nhập người dùng ngay sau khi đăng ký thành công
                    $_SESSION['client_logged_in'] = true;
                    $_SESSION['client_username']  = $username;
                    $_SESSION['client_fullname']  = $fullname;
                    $_SESSION['client_role']      = 'user';
                    $_SESSION['client_email']     = '';
                    $_SESSION['client_phone']     = '';

                    header("Location: index.php?page=trangchu");
                    exit;
                } else {
                    $login_error = "Gặp lỗi hệ thống trong quá trình tạo tài khoản mới.";
                }
            }
        }

        return ['error' => $login_error, 'success' => ''];
    }

    /**
     * Xử lý đăng nhập trực tiếp từ cổng Admin (Admin Portal Login).
     *
     * @param PDO|false $conn Kết nối CSDL
     * @return string Trạng thái lỗi nếu đăng nhập thất bại
     */
    public function handleAdminLogin($conn) {
        $login_error = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_admin'])) {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';

            if (empty($username) || empty($password)) {
                $login_error = "Vui lòng điền đầy đủ thông tin đăng nhập.";
            } else {
                $row = $this->userModel->getUserByUsername($username);
                if ($row) {
                    if ($row['loai_tk'] === 'Admin') {
                        $is_password_correct = false;
                        if (password_verify($password, $row['mat_khau'])) {
                            $is_password_correct = true;
                        } elseif ($password === $row['mat_khau']) {
                            $is_password_correct = true;
                            $newHash = password_hash($password, PASSWORD_DEFAULT);
                            $upgradeStmt = $this->conn->prepare("UPDATE tai_khoan SET mat_khau = ? WHERE username = ?");
                            if ($upgradeStmt) {
                                $upgradeStmt->execute([$newHash, $username]);
                            }
                        }

                        if ($is_password_correct) {
                            // Cấp quyền cho cả phiên Admin và Client đảm bảo tính đồng bộ hoàn chỉnh toàn bộ website
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_username'] = $row['username'];
                            $_SESSION['admin_fullname'] = $row['ho_ten'];
                            
                            $_SESSION['client_logged_in'] = true;
                            $_SESSION['client_username']  = $row['username'];
                            $_SESSION['client_fullname']  = $row['ho_ten'];
                            $_SESSION['client_role']      = 'admin';
                            $_SESSION['client_email']     = $row['email'];
                            $_SESSION['client_phone']     = $row['sdt'];

                            header("Location: index.php");
                            exit;
                        } else {
                            $login_error = "Mật khẩu đăng nhập không chính xác.";
                        }
                    } else {
                        $login_error = "Tài khoản của bạn không được cấp quyền Quản Trị.";
                    }
                } else {
                    $login_error = "Tài khoản không tồn tại hoặc đã bị khóa.";
                }
            }
        }

        return $login_error;
    }

    /**
     * Thực hiện Đăng xuất tài khoản khách hàng (Client Logout).
     */
    public function handleClientLogout() {
        unset($_SESSION['client_logged_in']);
        unset($_SESSION['client_username']);
        unset($_SESSION['client_fullname']);
        unset($_SESSION['client_role']);
        unset($_SESSION['client_email']);
        unset($_SESSION['client_phone']);
        header("Location: index.php?page=trangchu");
        exit;
    }

    /**
     * Thực hiện Đăng xuất tài khoản quản trị (Admin Logout).
     */
    public function handleAdminLogout() {
        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_fullname']);
        unset($_SESSION['client_logged_in']);
        unset($_SESSION['client_username']);
        unset($_SESSION['client_fullname']);
        unset($_SESSION['client_role']);
        unset($_SESSION['client_email']);
        unset($_SESSION['client_phone']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        header("Location: ../index.php?page=trangchu");
        exit;
    }

    /**
     * Lấy dữ liệu hồ sơ cá nhân và tự động đồng bộ lại hạng thành viên dựa trên điểm tích lũy (AJAX GET).
     */
    public function getProfile() {
        
        if ($this->conn === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            return;
        }
        if (session_status() === PHP_SESSION_NONE) session_start();
        $username = isset($_SESSION['client_username']) ? $_SESSION['client_username'] : '';
        if (!$username) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            return;
        }

        $stmt = $this->conn->prepare("SELECT ho_ten, email, sdt, hang_thanh_vien, diem_tich_luy FROM tai_khoan WHERE username = ?");
        $stmt->execute([$username]);
        if ($row = $stmt->fetch()) {
            // Tự động phân cấp hạng thành viên thực tế dựa trên tổng điểm tích lũy
            $pts = (int)$row['diem_tich_luy'];
            $tier = 'None';
            if ($pts >= 10000) $tier = 'Diamond';
            elseif ($pts >= 5000) $tier = 'Gold';
            elseif ($pts >= 1000) $tier = 'Silver';
            
            $row['hang_thanh_vien'] = $tier;
            
            // Cập nhật ngược lại CSDL để đồng bộ dữ liệu
            $stmt_u = $this->conn->prepare("UPDATE tai_khoan SET hang_thanh_vien = ? WHERE username = ?");
            $stmt_u->execute([$tier, $username]);

            echo json_encode(['success' => true, 'profile' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin tài khoản']);
        }
    }

    /**
     * Cập nhật thông tin chi tiết hồ sơ cá nhân của khách hàng (AJAX POST).
     */
    public function updateProfile() {
        
        if ($this->conn === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            return;
        }
        if (session_status() === PHP_SESSION_NONE) session_start();
        $username = isset($_SESSION['client_username']) ? $_SESSION['client_username'] : '';
        if (!$username) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            return;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $ho_ten = isset($data['ho_ten']) ? trim($data['ho_ten']) : '';
        $email = isset($data['email']) ? trim($data['email']) : '';
        $sdt = isset($data['sdt']) ? trim($data['sdt']) : '';

        $stmt = $this->conn->prepare("UPDATE tai_khoan SET ho_ten = ?, email = ?, sdt = ? WHERE username = ?");
        if ($stmt->execute([$ho_ten, $email, $sdt, $username])) {
            // Đồng bộ lại các biến Session
            $_SESSION['client_fullname'] = $ho_ten;
            $_SESSION['client_email'] = $email;
            $_SESSION['client_phone'] = $sdt;
            echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật vào cơ sở dữ liệu']);
        }
    }

    /**
     * Xử lý thay đổi mật khẩu tài khoản khách hàng (AJAX POST).
     */
    public function changePassword() {
        
        if ($this->conn === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            return;
        }
        if (session_status() === PHP_SESSION_NONE) session_start();
        $username = isset($_SESSION['client_username']) ? $_SESSION['client_username'] : '';
        if (!$username) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            return;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || !isset($data['old_password']) || !isset($data['new_password'])) {
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu mật khẩu']);
            return;
        }

        $oldPwd = $data['old_password'];
        $newPwd = $data['new_password'];

        // Lấy mã băm mật khẩu hiện tại trong CSDL để so khớp
        $stmt = $this->conn->prepare("SELECT mat_khau FROM tai_khoan WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($row = $stmt->fetch()) {
            if (password_verify($oldPwd, $row['mat_khau'])) {
                // Mã hóa băm mật khẩu mới trước khi lưu trữ
                $newHash = password_hash($newPwd, PASSWORD_DEFAULT);
                $updateStmt = $this->conn->prepare("UPDATE tai_khoan SET mat_khau = ? WHERE username = ?");
                if ($updateStmt->execute([$newHash, $username])) {
                    echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật CSDL']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu cũ không chính xác']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản']);
        }
    }
}