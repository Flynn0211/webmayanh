-- Database Schema cho dự án Web Máy Ảnh (LENS & LIGHT)

CREATE DATABASE IF NOT EXISTS `webmayanh` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `webmayanh`;

-- Bảng `bai_viet`
CREATE TABLE `bai_viet` (
  `ma_bv` int(11) NOT NULL AUTO_INCREMENT,
  `tieu_de` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `anh_dai_dien` longtext DEFAULT NULL,
  `mo_ta_ngan` text DEFAULT NULL,
  `noi_dung` longtext NOT NULL,
  `ma_tk` int(11) NOT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `trang_thai` enum('XuatBan','Nhao') DEFAULT 'XuatBan',
  PRIMARY KEY (`ma_bv`),
  UNIQUE KEY `slug` (`slug`),
  KEY `ma_tk` (`ma_tk`),
  CONSTRAINT `bai_viet_ibfk_1` FOREIGN KEY (`ma_tk`) REFERENCES `tai_khoan` (`ma_tk`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng `binh_luan_danh_gia`
CREATE TABLE `binh_luan_danh_gia` (
  `ma_bl` int(11) NOT NULL AUTO_INCREMENT,
  `ma_tk` int(11) NOT NULL,
  `ma_hh` int(11) NOT NULL,
  `so_sao` int(11) NOT NULL COMMENT 'Tư 1 đến 5',
  `noi_dung` text DEFAULT NULL,
  `trang_thai` varchar(20) DEFAULT 'HienThi',
  `ngay_bl` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_bl`),
  KEY `ma_tk` (`ma_tk`),
  KEY `ma_hh` (`ma_hh`),
  CONSTRAINT `binh_luan_danh_gia_ibfk_1` FOREIGN KEY (`ma_tk`) REFERENCES `tai_khoan` (`ma_tk`) ON DELETE CASCADE,
  CONSTRAINT `binh_luan_danh_gia_ibfk_2` FOREIGN KEY (`ma_hh`) REFERENCES `hang_hoa` (`ma_hh`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng `chi_tiet_don_hang`
CREATE TABLE `chi_tiet_don_hang` (
  `ma_dh` int(11) NOT NULL,
  `ma_hh` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `gia_luc_mua` decimal(15,2) NOT NULL,
  PRIMARY KEY (`ma_dh`,`ma_hh`),
  KEY `ma_hh` (`ma_hh`),
  CONSTRAINT `chi_tiet_don_hang_ibfk_1` FOREIGN KEY (`ma_dh`) REFERENCES `don_hang` (`ma_dh`) ON DELETE CASCADE,
  CONSTRAINT `chi_tiet_don_hang_ibfk_2` FOREIGN KEY (`ma_hh`) REFERENCES `hang_hoa` (`ma_hh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng `chi_tiet_khuyen_mai`
CREATE TABLE `chi_tiet_khuyen_mai` (
  `ma_km` int(11) NOT NULL,
  `ma_hh` int(11) NOT NULL,
  PRIMARY KEY (`ma_km`,`ma_hh`),
  KEY `ma_hh` (`ma_hh`),
  CONSTRAINT `chi_tiet_khuyen_mai_ibfk_1` FOREIGN KEY (`ma_km`) REFERENCES `khuyen_mai` (`ma_km`) ON DELETE CASCADE,
  CONSTRAINT `chi_tiet_khuyen_mai_ibfk_2` FOREIGN KEY (`ma_hh`) REFERENCES `hang_hoa` (`ma_hh`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng `danh_muc`
CREATE TABLE `danh_muc` (
  `ma_dm` int(11) NOT NULL AUTO_INCREMENT,
  `ten_danh_muc` varchar(100) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`ma_dm`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `danh_muc_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `danh_muc` (`ma_dm`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `danh_muc` (`ma_dm`, `ten_danh_muc`, `slug`) VALUES
(1, 'Máy ảnh', 'may-anh'),
(2, 'Ống kính', 'ong-kinh'),
(3, 'Phụ kiện', 'phu-kien');

-- Bảng `don_hang`
CREATE TABLE `don_hang` (
  `ma_dh` int(11) NOT NULL AUTO_INCREMENT,
  `ma_khach_hang` int(11) DEFAULT NULL,
  `ma_voucher` int(11) DEFAULT NULL COMMENT 'Voucher áp dụng cho đơn này',
  `ten_nguoi_nhan` varchar(100) NOT NULL,
  `sdt_nguoi_nhan` varchar(15) NOT NULL,
  `ngay_dat` timestamp NOT NULL DEFAULT current_timestamp(),
  `tong_tien_hang` decimal(15,2) NOT NULL,
  `phi_van_chuyen` decimal(15,2) DEFAULT 0.00,
  `giam_gia_voucher` decimal(15,2) DEFAULT 0.00,
  `tong_thanh_toan` decimal(15,2) DEFAULT NULL COMMENT 'GENERATED ALWAYS AS (tong_tien_hang + phi_van_chuyen - giam_gia_voucher) STORED',
  `phuong_thuc_thanh_toan` varchar(20) NOT NULL,
  `trang_thai_don` varchar(50) DEFAULT 'ChoXacNhan' COMMENT 'ENUM(''ChoXacNhan'', ''XacNhanDonHang'', ''DangGiao'', ''DaGiao'', ''ThanhCong'', ''DaHuy'')',
  `trang_thai_thanh_toan` varchar(20) DEFAULT 'ChuaThanhToan',
  `dia_chi_giao` text NOT NULL,
  `dia_chi_giao_hang` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ma_dh`),
  KEY `ma_khach_hang` (`ma_khach_hang`),
  KEY `ma_voucher` (`ma_voucher`),
  CONSTRAINT `don_hang_ibfk_1` FOREIGN KEY (`ma_khach_hang`) REFERENCES `tai_khoan` (`ma_tk`) ON DELETE SET NULL,
  CONSTRAINT `don_hang_ibfk_2` FOREIGN KEY (`ma_voucher`) REFERENCES `voucher` (`ma_voucher`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng `email_dang_ky`
CREATE TABLE `email_dang_ky` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `ngay_dang_ky` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng `hang_hoa`
CREATE TABLE `hang_hoa` (
  `ma_hh` int(11) NOT NULL AUTO_INCREMENT,
  `ma_dm` int(11) DEFAULT NULL,
  `ma_ncc` int(11) DEFAULT NULL,
  `ten_hang_hoa` varchar(150) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `anh` longtext DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `thong_so_ky_thuat` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`thong_so_ky_thuat`)),
  `gia_hien_tai` decimal(15,2) NOT NULL DEFAULT 0.00,
  `trang_thai` varchar(20) DEFAULT 'DangBan',
  `anh_phu` longtext DEFAULT NULL,
  PRIMARY KEY (`ma_hh`),
  UNIQUE KEY `slug` (`slug`),
  KEY `ma_dm` (`ma_dm`),
  KEY `ma_ncc` (`ma_ncc`),
  CONSTRAINT `hang_hoa_ibfk_1` FOREIGN KEY (`ma_dm`) REFERENCES `danh_muc` (`ma_dm`) ON DELETE SET NULL,
  CONSTRAINT `hang_hoa_ibfk_2` FOREIGN KEY (`ma_ncc`) REFERENCES `nha_cung_cap` (`ma_ncc`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng `kho_hang`
CREATE TABLE `kho_hang` (
  `ma_kho` int(11) NOT NULL AUTO_INCREMENT,
  `ten_kho` varchar(100) NOT NULL,
  PRIMARY KEY (`ma_kho`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `kho_hang` (`ma_kho`, `ten_kho`) VALUES (1, 'Kho Tổng');

-- Bảng `khuyen_mai`
CREATE TABLE `khuyen_mai` (
  `ma_km` int(11) NOT NULL AUTO_INCREMENT,
  `ten_km` varchar(150) NOT NULL,
  `loai_giam_gia` varchar(20) DEFAULT NULL COMMENT 'ENUM(''PhanTram'', ''TienMat'')',
  `gia_tri_giam` decimal(15,2) NOT NULL,
  `ngay_bat_dau` datetime NOT NULL,
  `ngay_het_han` datetime NOT NULL,
  `trang_thai` varchar(20) DEFAULT 'HoatDong',
  PRIMARY KEY (`ma_km`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng `lich_su_giao_hang`
CREATE TABLE `lich_su_giao_hang` (
  `ma_ls` int(11) NOT NULL AUTO_INCREMENT,
  `ma_dh` int(11) NOT NULL,
  `trang_thai` varchar(50) NOT NULL,
  `mo_ta` text DEFAULT NULL COMMENT 'VD: Đơn hàng đã đến kho Củ Chi',
  `thoi_gian` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_ls`),
  KEY `ma_dh` (`ma_dh`),
  CONSTRAINT `lich_su_giao_hang_ibfk_1` FOREIGN KEY (`ma_dh`) REFERENCES `don_hang` (`ma_dh`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng `nha_cung_cap`
CREATE TABLE `nha_cung_cap` (
  `ma_ncc` int(11) NOT NULL AUTO_INCREMENT,
  `ten_ncc` varchar(100) NOT NULL,
  `sdt_lien_he` varchar(15) DEFAULT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ma_ncc`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng `tai_khoan`
CREATE TABLE `tai_khoan` (
  `ma_tk` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `sdt` varchar(15) DEFAULT NULL,
  `loai_tk` varchar(20) DEFAULT NULL COMMENT 'ENUM(''Admin'', ''NhanVien'', ''User'')',
  `hang_thanh_vien` varchar(20) DEFAULT 'None' COMMENT 'ENUM(''None'', ''Silver'', ''Gold'', ''Diamond'')',
  `diem_tich_luy` int(11) DEFAULT 0,
  `trang_thai` varchar(20) DEFAULT 'HoatDong' COMMENT 'ENUM(''HoatDong'', ''BiKhoa'')',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_tk`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `tai_khoan` (`ma_tk`, `username`, `mat_khau`, `ho_ten`, `loai_tk`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', 'Admin');

-- Bảng `thong_bao_email`
CREATE TABLE `thong_bao_email` (
  `ma_tb` int(11) NOT NULL AUTO_INCREMENT,
  `ma_tk_nhan` int(11) NOT NULL COMMENT 'Khách hàng nhận email',
  `tieu_de` varchar(200) NOT NULL,
  `noi_dung` text NOT NULL,
  `da_doc` tinyint(1) DEFAULT 0,
  `ngay_gui` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_tb`),
  KEY `ma_tk_nhan` (`ma_tk_nhan`),
  CONSTRAINT `thong_bao_email_ibfk_1` FOREIGN KEY (`ma_tk_nhan`) REFERENCES `tai_khoan` (`ma_tk`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng `ton_kho_chi_tiet`
CREATE TABLE `ton_kho_chi_tiet` (
  `ma_kho` int(11) NOT NULL,
  `ma_hh` int(11) NOT NULL,
  `so_luong_ton` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ma_kho`,`ma_hh`),
  KEY `ma_hh` (`ma_hh`),
  CONSTRAINT `ton_kho_chi_tiet_ibfk_1` FOREIGN KEY (`ma_kho`) REFERENCES `kho_hang` (`ma_kho`) ON DELETE CASCADE,
  CONSTRAINT `ton_kho_chi_tiet_ibfk_2` FOREIGN KEY (`ma_hh`) REFERENCES `hang_hoa` (`ma_hh`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng `voucher`
CREATE TABLE `voucher` (
  `ma_voucher` int(11) NOT NULL AUTO_INCREMENT,
  `ma_code` varchar(20) NOT NULL,
  `loai_giam_gia` varchar(20) DEFAULT NULL COMMENT 'ENUM(''PhanTram'', ''TienMat'')',
  `gia_tri_giam` decimal(15,2) NOT NULL,
  `don_toi_thieu` decimal(15,2) DEFAULT 0.00,
  `so_luong` int(11) NOT NULL,
  `ngay_bat_dau` datetime NOT NULL,
  `ngay_het_han` datetime NOT NULL,
  `trang_thai` varchar(20) DEFAULT 'HoatDong',
  PRIMARY KEY (`ma_voucher`),
  UNIQUE KEY `ma_code` (`ma_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

