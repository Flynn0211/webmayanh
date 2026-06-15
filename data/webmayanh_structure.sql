-- Khởi tạo Database và các bảng cho dự án webmayanh
CREATE DATABASE IF NOT EXISTS `webmayanh` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `webmayanh`;

-- 1. Bảng tài khoản
CREATE TABLE IF NOT EXISTS `tai_khoan` (
  `ma_tk` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `mat_khau` varchar(255) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `loai_tk` enum('Admin','User') DEFAULT 'User',
  `email` varchar(100) DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL,
  `hang_thanh_vien` enum('None','Silver','Gold','Diamond') DEFAULT 'None',
  `diem_tich_luy` int(11) DEFAULT 0,
  `trang_thai` enum('HoatDong','Khoa') DEFAULT 'HoatDong',
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_tk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Thêm tài khoản admin mặc định (Mật khẩu mặc định: admin123, cần tự update sang Hash sau khi code hoạt động)
INSERT INTO `tai_khoan` (`username`, `mat_khau`, `ho_ten`, `loai_tk`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', 'Admin');

-- 2. Bảng danh mục
CREATE TABLE IF NOT EXISTS `danh_muc` (
  `ma_dm` int(11) NOT NULL AUTO_INCREMENT,
  `ten_danh_muc` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`ma_dm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `danh_muc` (`ten_danh_muc`, `slug`) VALUES
('Máy ảnh', 'may-anh'),
('Ống kính', 'ong-kinh'),
('Phụ kiện', 'phu-kien');

-- 3. Bảng nhà cung cấp (Thương hiệu)
CREATE TABLE IF NOT EXISTS `nha_cung_cap` (
  `ma_ncc` int(11) NOT NULL AUTO_INCREMENT,
  `ten_ncc` varchar(100) NOT NULL,
  `sdt_lien_he` varchar(20) DEFAULT NULL,
  `dia_chi` text,
  PRIMARY KEY (`ma_ncc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Bảng hàng hóa (Sản phẩm)
CREATE TABLE IF NOT EXISTS `hang_hoa` (
  `ma_hh` int(11) NOT NULL AUTO_INCREMENT,
  `ma_dm` int(11) NOT NULL,
  `ma_ncc` int(11) NOT NULL,
  `ten_hang_hoa` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `anh` varchar(255) DEFAULT NULL,
  `mo_ta` text,
  `thong_so_ky_thuat` text, -- JSON format
  `gia_hien_tai` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gia_cu` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`ma_hh`),
  FOREIGN KEY (`ma_dm`) REFERENCES `danh_muc`(`ma_dm`) ON DELETE CASCADE,
  FOREIGN KEY (`ma_ncc`) REFERENCES `nha_cung_cap`(`ma_ncc`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Bảng tồn kho chi tiết
CREATE TABLE IF NOT EXISTS `ton_kho_chi_tiet` (
  `ma_kho` int(11) NOT NULL DEFAULT 1,
  `ma_hh` int(11) NOT NULL,
  `so_luong_ton` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ma_kho`, `ma_hh`),
  FOREIGN KEY (`ma_hh`) REFERENCES `hang_hoa`(`ma_hh`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Bảng voucher
CREATE TABLE IF NOT EXISTS `voucher` (
  `ma_voucher` int(11) NOT NULL AUTO_INCREMENT,
  `ma_code` varchar(50) NOT NULL UNIQUE,
  `loai_giam_gia` enum('PhanTram','TienMat') NOT NULL,
  `gia_tri_giam` decimal(15,2) NOT NULL,
  `don_toi_thieu` decimal(15,2) DEFAULT 0.00,
  `so_luong` int(11) NOT NULL DEFAULT 0,
  `ngay_bat_dau` datetime DEFAULT CURRENT_TIMESTAMP,
  `ngay_het_han` datetime NOT NULL,
  `trang_thai` enum('HoatDong','HetHan','Khoa') DEFAULT 'HoatDong',
  PRIMARY KEY (`ma_voucher`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Bảng đơn hàng
CREATE TABLE IF NOT EXISTS `don_hang` (
  `ma_dh` int(11) NOT NULL AUTO_INCREMENT,
  `ma_khach_hang` int(11) DEFAULT NULL,
  `ma_voucher` int(11) DEFAULT NULL,
  `ten_nguoi_nhan` varchar(100) NOT NULL,
  `sdt_nguoi_nhan` varchar(20) NOT NULL,
  `tong_tien_hang` decimal(15,2) NOT NULL,
  `phi_van_chuyen` decimal(15,2) DEFAULT 0.00,
  `giam_gia_voucher` decimal(15,2) DEFAULT 0.00,
  `tong_thanh_toan` decimal(15,2) NOT NULL,
  `phuong_thuc_thanh_toan` varchar(50) DEFAULT 'COD',
  `trang_thai_don` varchar(50) DEFAULT 'ChoXacNhan',
  `ngay_dat` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_dh`),
  FOREIGN KEY (`ma_khach_hang`) REFERENCES `tai_khoan`(`ma_tk`) ON DELETE SET NULL,
  FOREIGN KEY (`ma_voucher`) REFERENCES `voucher`(`ma_voucher`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Bảng chi tiết đơn hàng
CREATE TABLE IF NOT EXISTS `chi_tiet_don_hang` (
  `ma_dh` int(11) NOT NULL,
  `ma_hh` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `gia_luc_mua` decimal(15,2) NOT NULL,
  PRIMARY KEY (`ma_dh`, `ma_hh`),
  FOREIGN KEY (`ma_dh`) REFERENCES `don_hang`(`ma_dh`) ON DELETE CASCADE,
  FOREIGN KEY (`ma_hh`) REFERENCES `hang_hoa`(`ma_hh`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Bảng lịch sử giao hàng
CREATE TABLE IF NOT EXISTS `lich_su_giao_hang` (
  `ma_ls` int(11) NOT NULL AUTO_INCREMENT,
  `ma_dh` int(11) NOT NULL,
  `trang_thai` varchar(50) NOT NULL,
  `mo_ta` text,
  `thoi_gian` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_ls`),
  FOREIGN KEY (`ma_dh`) REFERENCES `don_hang`(`ma_dh`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Bảng đánh giá
CREATE TABLE IF NOT EXISTS `danh_gia` (
  `ma_dg` int(11) NOT NULL AUTO_INCREMENT,
  `ma_tk` int(11) NOT NULL,
  `ma_hh` int(11) NOT NULL,
  `so_sao` int(11) NOT NULL,
  `noi_dung` text,
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_dg`),
  FOREIGN KEY (`ma_tk`) REFERENCES `tai_khoan`(`ma_tk`) ON DELETE CASCADE,
  FOREIGN KEY (`ma_hh`) REFERENCES `hang_hoa`(`ma_hh`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Bảng thông báo email
CREATE TABLE IF NOT EXISTS `thong_bao_email` (
  `ma_tb` int(11) NOT NULL AUTO_INCREMENT,
  `ma_tk_nhan` int(11) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `noi_dung` text,
  `ngay_gui` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_tb`),
  FOREIGN KEY (`ma_tk_nhan`) REFERENCES `tai_khoan`(`ma_tk`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- 1. BẢNG DANH MỤC TIN TỨC (VD: Đánh giá, Thủ thuật, Tin khuyến mãi)
CREATE TABLE danh_muc_tin_tuc (
    ma_dm_tin INT AUTO_INCREMENT PRIMARY KEY,
    ten_danh_muc VARCHAR(150) NOT NULL,
    slug VARCHAR(150) UNIQUE NOT NULL
);

-- 2. BẢNG BÀI VIẾT
CREATE TABLE bai_viet (
    ma_bv INT AUTO_INCREMENT PRIMARY KEY,
    ma_dm_tin INT,
    ma_tk_dang INT NOT NULL, -- ID của Admin hoặc Nhân viên viết bài
    tieu_de VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    anh_bia VARCHAR(255), -- Đường dẫn ảnh thumbnail
    tom_tat TEXT, -- Đoạn mô tả ngắn hiển thị ở danh sách
    noi_dung LONGTEXT NOT NULL, -- Cực kỳ quan trọng: Dùng LONGTEXT để chứa mã HTML
    luot_xem INT DEFAULT 0,
    trang_thai ENUM('XuatBan', 'BanNhap', 'An') DEFAULT 'XuatBan',
    ngay_dang TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_dm_tin) REFERENCES danh_muc_tin_tuc(ma_dm_tin) ON DELETE SET NULL,
    FOREIGN KEY (ma_tk_dang) REFERENCES tai_khoan(ma_tk) ON DELETE CASCADE
);