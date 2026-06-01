<?php
// Load User Model layer
require_once __DIR__ . '/../model/UserModel.php';

class AuthController {
    /**
     * Process client authentication. Sets server session for admins and returns response state.
     */
    public static function handleLogin($conn) {
        $login_error = "";
        $js_login_success = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_login'])) {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';

            $row = UserModel::getUserByUsername($conn, $username);
            if ($row) {
                if ($password === $row['mat_khau']) {
                    // Create user profile snapshot
                    $user_data = [
                        'username' => $row['username'],
                        'fullname' => $row['ho_ten'],
                        'role'     => strtolower($row['loai_tk']),
                        'email'    => $row['email'],
                        'phone'    => $row['sdt']
                    ];

                    // Set PHP session for ALL users (reliable, not dependent on browser localStorage)
                    $_SESSION['client_logged_in'] = true;
                    $_SESSION['client_username']  = $row['username'];
                    $_SESSION['client_fullname']  = $row['ho_ten'];
                    $_SESSION['client_role']      = strtolower($row['loai_tk']);
                    $_SESSION['client_email']     = $row['email'];
                    $_SESSION['client_phone']     = $row['sdt'];

                    if ($row['loai_tk'] === 'Admin') {
                        // Admin: also set admin session for Dashboard access
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_username']  = $row['username'];
                        $_SESSION['admin_fullname']  = $row['ho_ten'];

                        $js_login_success = "<script>
                            localStorage.setItem('currentUser', JSON.stringify(" . json_encode($user_data, JSON_UNESCAPED_UNICODE) . "));
                            window.location.href = 'admin/index.php';
                        </script>";
                    } else {
                        $js_login_success = "<script>
                            localStorage.setItem('currentUser', JSON.stringify(" . json_encode($user_data, JSON_UNESCAPED_UNICODE) . "));
                            window.location.href = 'index.php?page=trangchu';
                        </script>";
                    }
                } else {
                    $login_error = "Sai mật khẩu đăng nhập!";
                }
            } else {
                $login_error = "Tài khoản không tồn tại hoặc đã bị khóa!";
            }
        }

        return ['error' => $login_error, 'success' => $js_login_success];
    }

    /**
     * Process client registration.
     */
    public static function handleRegister($conn) {
        $login_error = "";
        $js_login_success = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_register'])) {
            $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';

            // Validate if username exists
            $existing = UserModel::getUserByUsername($conn, $username);
            if ($existing) {
                $login_error = "Tên đăng nhập đã tồn tại!";
            } else {
                // Save new user in database
                if (UserModel::registerUser($conn, $fullname, $username, $password)) {
                    $user_data = [
                        'username' => $username,
                        'fullname' => $fullname,
                        'role'     => 'user'
                    ];

                    // Set PHP session after registration
                    $_SESSION['client_logged_in'] = true;
                    $_SESSION['client_username']  = $username;
                    $_SESSION['client_fullname']  = $fullname;
                    $_SESSION['client_role']      = 'user';
                    $_SESSION['client_email']     = '';
                    $_SESSION['client_phone']     = '';

                    $js_login_success = "<script>
                        localStorage.setItem('currentUser', JSON.stringify(" . json_encode($user_data, JSON_UNESCAPED_UNICODE) . "));
                        window.location.href = 'index.php?page=trangchu';
                    </script>";
                } else {
                    $login_error = "Lỗi khi tạo tài khoản mới.";
                }
            }
        }

        return ['error' => $login_error, 'success' => $js_login_success];
    }

    /**
     * Process administrator authentication.
     */
    public static function handleAdminLogin($conn) {
        $login_error = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_admin'])) {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';

            if (empty($username) || empty($password)) {
                $login_error = "Vui lòng điền đầy đủ thông tin đăng nhập.";
            } else {
                $row = UserModel::getUserByUsername($conn, $username);
                if ($row) {
                    if ($row['loai_tk'] === 'Admin') {
                        if ($password === $row['mat_khau']) {
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_username'] = $row['username'];
                            $_SESSION['admin_fullname'] = $row['ho_ten'];
                            
                            // Đồng thời cấp session cho phía Client để đồng bộ trạng thái đăng nhập
                            $_SESSION['client_logged_in'] = true;
                            $_SESSION['client_username']  = $row['username'];
                            $_SESSION['client_fullname']  = $row['ho_ten'];
                            $_SESSION['client_role']      = 'admin';
                            $_SESSION['client_email']     = $row['email'];
                            $_SESSION['client_phone']     = $row['sdt'];

                            $user_data = [
                                'username' => $row['username'],
                                'fullname' => $row['ho_ten'],
                                'role'     => 'admin',
                                'email'    => $row['email'],
                                'phone'    => $row['sdt']
                            ];

                            // Trả về HTML chứa script set localStorage rồi mới redirect
                            echo "<script>
                                localStorage.setItem('currentUser', JSON.stringify(" . json_encode($user_data, JSON_UNESCAPED_UNICODE) . "));
                                window.location.href = 'index.php';
                            </script>";
                            exit;
                        } else {
                            $login_error = "Mật khẩu đăng nhập không chính xác.";
                        }
                    } else {
                        $login_error = "Tài khoản không có quyền Quản Trị.";
                    }
                } else {
                    $login_error = "Tài khoản không tồn tại hoặc đã bị khóa.";
                }
            }
        }

        return $login_error;
    }

    /**
     * Terminate client user session and redirect to home.
     */
    public static function handleClientLogout() {
        unset($_SESSION['client_logged_in']);
        unset($_SESSION['client_username']);
        unset($_SESSION['client_fullname']);
        unset($_SESSION['client_role']);
        unset($_SESSION['client_email']);
        unset($_SESSION['client_phone']);
        // Clear localStorage via JS then redirect
        echo "<script>localStorage.removeItem('currentUser'); window.location.href='index.php?page=trangchu';</script>";
        exit;
    }

    /**
     * Terminate administrator session and redirect.
     */
    public static function handleAdminLogout() {
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
     * Get user profile details as JSON
     */
    public static function getProfile() {
        global $conn;
        if (session_status() === PHP_SESSION_NONE) session_start();
        $username = isset($_SESSION['client_username']) ? $_SESSION['client_username'] : '';
        if (!$username) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }

        $stmt = $conn->prepare("SELECT ho_ten, email, sdt, hang_thanh_vien, diem_tich_luy FROM tai_khoan WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            // Tính toán lại hạng thành viên dựa trên điểm
            $pts = (int)$row['diem_tich_luy'];
            $tier = 'Thường';
            if ($pts >= 10000) $tier = 'Diamond';
            elseif ($pts >= 5000) $tier = 'Gold';
            elseif ($pts >= 1000) $tier = 'Silver';
            
            $row['hang_thanh_vien'] = $tier;
            
            // Cập nhật lại vào DB để đồng bộ
            $stmt_u = $conn->prepare("UPDATE tai_khoan SET hang_thanh_vien = ? WHERE username = ?");
            $stmt_u->bind_param("ss", $tier, $username);
            $stmt_u->execute();

            echo json_encode(['success' => true, 'profile' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    }

    /**
     * Update user profile details
     */
    public static function updateProfile() {
        global $conn;
        if (session_status() === PHP_SESSION_NONE) session_start();
        $username = isset($_SESSION['client_username']) ? $_SESSION['client_username'] : '';
        if (!$username) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            return;
        }

        $ho_ten = isset($data['ho_ten']) ? trim($data['ho_ten']) : '';
        $email = isset($data['email']) ? trim($data['email']) : '';
        $sdt = isset($data['sdt']) ? trim($data['sdt']) : '';

        $stmt = $conn->prepare("UPDATE tai_khoan SET ho_ten = ?, email = ?, sdt = ? WHERE username = ?");
        $stmt->bind_param("ssss", $ho_ten, $email, $sdt, $username);
        if ($stmt->execute()) {
            // Update session values
            $_SESSION['client_fullname'] = $ho_ten;
            $_SESSION['client_email'] = $email;
            $_SESSION['client_phone'] = $sdt;
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật CSDL']);
        }
    }

    /**
     * Change user password
     */
    public static function changePassword() {
        global $conn;
        if (session_status() === PHP_SESSION_NONE) session_start();
        $username = isset($_SESSION['client_username']) ? $_SESSION['client_username'] : '';
        if (!$username) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || !isset($data['old_password']) || !isset($data['new_password'])) {
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
            return;
        }

        $oldPwd = $data['old_password'];
        $newPwd = $data['new_password'];

        // Get current hash
        $stmt = $conn->prepare("SELECT mat_khau FROM tai_khoan WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            if (password_verify($oldPwd, $row['mat_khau'])) {
                // old password correct, hash new password
                $newHash = password_hash($newPwd, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE tai_khoan SET mat_khau = ? WHERE username = ?");
                $updateStmt->bind_param("ss", $newHash, $username);
                if ($updateStmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Đổi mật khẩu thất bại trên CSDL']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu cũ không chính xác']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản']);
        }
    }
}
?>