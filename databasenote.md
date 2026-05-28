# Tài Liệu Cấu Trúc Cơ Sở Dữ Liệu - Website Bán Máy Ảnh

**Mô tả:** Tài liệu mô tả cấu trúc các bảng (Tables) và mối quan hệ (Relationships) trong hệ cơ sở dữ liệu (MySQL) của dự án Website Thương Mại Điện Tử bán Máy Ảnh.

---

## 1. Nhóm Quản Lý Người Dùng & Giao Tiếp

### `tai_khoan`
Quản lý thông tin đăng nhập, phân quyền và hạng thành viên của tất cả người dùng trong hệ thống (Admin, Staff, Customer).
* **ma_tk (PK):** Khóa chính tự tăng.
* **username, mat_khau, ho_ten, email, sdt:** Thông tin cá nhân cơ bản.
* **loai_tk:** Phân quyền (`Admin`, `NhanVien`, `User`).
* **hang_thanh_vien:** Hạng thẻ (`None`, `Silver`, `Gold`, `Diamond`).
* **diem_tich_luy:** Điểm thưởng khi mua hàng.
* **trang_thai:** `HoatDong` hoặc `BiKhoa` (Soft delete).

### `thong_bao_email`
Lưu trữ lịch sử các thông báo, email mà hệ thống/admin đã gửi cho người dùng.
* **ma_tb (PK):** Mã thông báo.
* **ma_tk_nhan (FK):** Trỏ đến user nhận thông báo.
* **tieu_de, noi_dung:** Nội dung email.
* **da_doc:** Trạng thái đọc (Boolean).

---

## 2. Nhóm Quản Lý Hàng Hóa & Danh Mục

### `danh_muc`
Quản lý danh mục sản phẩm hỗ trợ cấu trúc cây đa cấp (Parent - Child).
* **ma_dm (PK):** Mã danh mục.
* **ten_danh_muc, slug:** Tên và đường dẫn SEO.
* **parent_id (FK):** Trỏ về chính `ma_dm` để xác định danh mục cha.

### `nha_cung_cap`
Thông tin các hãng máy ảnh, phụ kiện (Sony, Canon, Nikon...).
* **ma_ncc (PK):** Mã nhà cung cấp.
* **ten_ncc, sdt_lien_he, dia_chi:** Thông tin liên hệ.

### `hang_hoa`
Bảng Core lưu trữ thông tin sản phẩm máy ảnh.
* **ma_hh (PK):** Mã hàng hóa.
* **ma_dm (FK), ma_ncc (FK):** Thuộc danh mục và nhà cung cấp nào.
* **ten_hang_hoa, slug, anh, mo_ta:** Thông tin hiển thị web.
* **thong_so_ky_thuat:** (Kiểu `JSON`) Lưu cấu hình động (Megapixel, Ngàm, ISO...) để tiện làm Filter.
* **gia_hien_tai:** Giá bán niêm yết hiện tại.

### `binh_luan_danh_gia`
Lưu review và đánh giá sao của khách hàng.
* **ma_bl (PK):** Mã bình luận.
* **ma_tk (FK), ma_hh (FK):** Khách hàng nào đánh giá máy ảnh nào.
* **so_sao:** Rating từ 1 đến 5.
* **noi_dung, trang_thai:** Text review và trạng thái hiển thị.

---

## 3. Nhóm Quản Lý Khuyến Mãi & Kho Hàng

### `khuyen_mai` & `chi_tiet_khuyen_mai`
Các chiến dịch Sale áp dụng trực tiếp để giảm giá các dòng máy ảnh cụ thể.
* **khuyen_mai:** Thông tin chiến dịch (Tên, `%` hoặc `Tiền mặt`, Thời hạn).
* **chi_tiet_khuyen_mai:** Bảng trung gian map những sản phẩm (`ma_hh`) nào được áp dụng chiến dịch (`ma_km`).

### `kho_hang` & `ton_kho_chi_tiet`
Hệ thống quản lý tồn kho đa điểm (Multi-warehouse).
* **kho_hang:** Danh sách chi nhánh kho (Tên, địa chỉ).
* **ton_kho_chi_tiet:** Số lượng máy ảnh thực tế đang nằm tại từng kho tương ứng (`ma_kho`, `ma_hh`, `so_luong_ton`).

---

## 4. Nhóm Quản Lý Đơn Hàng & Thanh Toán

### `voucher`
Mã giảm giá nhập ở bước Checkout (Giỏ hàng).
* **ma_voucher (PK):** Mã ID hệ thống.
* **ma_code:** Code khách hàng nhập (VD: `SALE500K`).
* **loai_giam_gia, gia_tri_giam, don_toi_thieu, so_luong:** Cấu hình điều kiện sử dụng.

### `don_hang`
Thông tin tổng quát và trạng thái của hóa đơn mua hàng.
* **ma_dh (PK):** Mã đơn hàng.
* **ma_khach_hang (FK):** Trỏ về User. **Lưu ý:** Có thể `NULL` nếu là Khách vãng lai (Guest Checkout).
* **ten_nguoi_nhan, sdt_nguoi_nhan:** Thông tin giao hàng snapshot.
* **ma_voucher (FK):** Mã giảm giá đã áp dụng.
* **tong_thanh_toan:** Cột Generated ảo, tự động tính theo công thức: `(tong_tien_hang + phi_van_chuyen - giam_gia_voucher)`.
* **trang_thai_don:** Flow vận hành: `ChoXacNhan` -> `XacNhanDonHang` -> `DangGiao` -> `DaGiao` -> `ThanhCong`.

### `chi_tiet_don_hang`
Chi tiết các máy ảnh trong giỏ hàng.
* **ma_dh (FK), ma_hh (FK):** Ràng buộc liên kết khóa ngoại.
* **gia_luc_mua:** *Rất quan trọng*. Lưu cứng lại giá tiền ngay tại thời điểm khách bấm thanh toán để bảo vệ dữ liệu doanh thu nếu sau này kho cập nhật giá sản phẩm.

### `lich_su_giao_hang`
Tracking hành trình vận chuyển của đơn hàng.
* **ma_ls (PK):** Mã lịch sử.
* **ma_dh (FK):** Của đơn hàng nào.
* **trang_thai, mo_ta, thoi_gian:** Chi tiết tiến trình (VD: "Đơn hàng đã nhập kho Củ Chi").

---

## 💡 Ghi chú dành cho Developer (Lưu ý quan trọng khi Code)
1. **Khách vãng lai:** Bảng `don_hang` hỗ trợ Insert thông tin người nhận mà không cần ép buộc bắt ID Tài khoản. Nếu khách đăng ký tài khoản sau đó, viết script cập nhật lại `ma_khach_hang` theo `sdt_nguoi_nhan`.
2. **Cột JSON:** Khi render bộ lọc thông số cấu hình, truy vấn bóc tách dữ liệu từ cột `thong_so_ky_thuat` thay vì query Join nhiều bảng.
3. **Thống kê Doanh thu:** Viết query `SELECT SUM()` từ bảng `chi_tiet_don_hang` và nhân với `gia_luc_mua`, **KHÔNG** lấy `gia_hien_tai` ở bảng `hang_hoa` để đảm bảo báo cáo không bị sai lệch.