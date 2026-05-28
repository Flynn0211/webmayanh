# Cấu trúc dự án

```text
📁 webmayanh
├── 📁 assets/               # Chứa tài nguyên tĩnh (Client-side) tự viết hoặc thư viện (Bootstrap)
│   ├── 📁 css/              # Giao diện tĩnh phân chia rõ ràng
│   │   ├── admin.css
│   │   ├── base.css
│   │   ├── client.css
│   │   └── responsive.css
│   └── 📁 js/               # Xử lý AJAX thêm giỏ hàng, check form, tương tác động...
│       ├── admin.js
│       ├── auth.js
│       ├── mayanh.js
│       └── giohang.js
│
├── 📁 uploads/              # Thư mục lưu file động (Cần bảo mật chống upload mã độc)
│   ├── 📁 products/         # Ảnh sản phẩm (máy ảnh, ống kính...) do Admin tải lên
│   └── 📁 avatars/          # Ảnh đại diện của người dùng
│
├── 📁 model/                # TẦNG DỮ LIỆU (Thực thi truy vấn MySQL)
│   ├── database.php         # Kết nối CSDL (mysqli, $conn)
│   ├── ProductModel.php     # Truy vấn: Sản phẩm, đánh giá, khuyến mãi
│   ├── UserModel.php        # Truy vấn: Đăng ký, đăng nhập, phân loại User (Diamond/Gold/Silver)
│   └── OrderModel.php       # Truy vấn: Tạo đơn hàng, cập nhật trạng thái, voucher
│
├── 📁 control/              # TẦNG ĐIỀU KHIỂN & BẢO MẬT (Xử lý logic, quyết định luồng)
│   ├── ProductController.php# Xử lý logic hiển thị sản phẩm ra các trang ngoài
│   ├── AuthController.php   # Xử lý logic Đăng nhập/Đăng xuất/Phân quyền (User vs Admin)
│   ├── CartController.php   # Xử lý: Thêm/sửa/xóa giỏ hàng, check voucher, thanh toán
│   └── AdminController.php  # Xử lý logic thống kê doanh thu, quản lý riêng cho luồng Admin
│
├── 📁 view/                 # TẦNG HIỂN THỊ (Chỉ chứa HTML + PHP in dữ liệu)
│   ├── 📁 client/           # KHU VỰC DÀNH CHO USER (Khách hàng)
│   │   ├── 📁 layout/       # Chứa các thành phần dùng chung (_navbar.php, _footer.php...)
│   │   ├── trangchu.php         # Trang chủ
│   │   ├── chitietsanpham.php   # Chi tiết sản phẩm & form bình luận
│   │   ├── giohang.php          # Trang giỏ hàng, nhập voucher, thanh toán
│   │   ├── donhang.php          # Xem lịch sử mua hàng, quá trình giao hàng
│   │   ├── mayanh.php           # Trang hiển thị danh mục máy ảnh
│   │   └── login.php            # Trang đăng ký / đăng nhập
│   │
│   └── 📁 admin/            # KHU VỰC DÀNH CHO ADMIN (Ban quản trị)
│       ├── 📁 layout/       # Chứa khung giao diện Admin tách biệt (sidebar, topbar...)
│       └── admin.php        # Bảng điều khiển quản trị (Dashboard, thống kê, quản lý tổng hợp)
│
├── config.php               # Chứa các hằng số hệ thống (URL gốc, cấu hình Gửi Email...)
├── databasenote.md          # Ghi chú cấu trúc cơ sở dữ liệu
├── index.php                # (Router) Cổng vào duy nhất điều phối mọi URL của hệ thống
└── README.md                # File hướng dẫn và tài liệu dự án
```

---

# GHI CHÚ & NHẮC NHỞ CHO PHÁT TRIỂN (DEVELOPMENT NOTES & REMINDERS)

> [!IMPORTANT]
> Đây là các nguyên tắc cốt lõi của dự án. Tất cả thành viên phát triển phải đọc kỹ và tuân thủ nghiêm ngặt để đảm bảo code sạch, đồng bộ và không bị xung đột.

## 1. Quy tắc về Giao diện & Styling (CSS)
*   **TUYỆT ĐỐI KHÔNG sử dụng Tailwind CSS.** Dự án đã gỡ bỏ hoàn toàn Tailwind CSS.
*   **Sử dụng Vanilla CSS tự viết** phân chia rõ ràng trong thư mục `assets/css/`:
    *   `base.css`: Định nghĩa hệ thống màu sắc (Color Palette), typography, biến CSS (CSS Variables) và các reset CSS cơ bản.
    *   `client.css`: Toàn bộ CSS dành cho trang khách hàng (Client).
    *   `admin.css`: Toàn bộ CSS dành cho bảng điều khiển quản trị (Admin Dashboard).
    *   `responsive.css`: Chứa các `@media` queries để xử lý responsive giao diện trên điện thoại, máy tính bảng.
*   **Khuyến khích:** Sử dụng phương pháp đặt tên BEM (Block-Element-Modifier) và sử dụng biến CSS `--color-primary`, `--font-main`... đã khai báo trong `base.css` để giữ tính đồng bộ cho giao diện.

## 2. Quy tắc Điều hướng & Routing (URL)
*   Dự án áp dụng mô hình **Single Entry Point** (Một điểm truy cập duy nhất) thông qua `index.php`.
*   **Không bao giờ** liên kết trực tiếp đến các file `.php` trong thư mục `view/` hoặc `control/`. Mọi liên kết điều hướng phải trỏ qua `index.php` sử dụng tham số `page`.
    *   ✅ **Đúng:** `<a href="index.php?page=giohang">Giỏ hàng</a>` hoặc `<a href="index.php?page=chitietsanpham&id=5">Xem sản phẩm</a>`
    *   ❌ **Sai:** `<a href="view/client/giohang.php">Giỏ hàng</a>`
*   Nếu cần thêm trang mới:
    1. Đăng ký trang đó vào mảng `$clientPages` hoặc điều kiện đặc biệt trong `index.php`.
    2. Tạo file giao diện tương ứng tại `view/client/` hoặc `view/admin/`.

## 3. Quy tắc Kết nối Cơ sở dữ liệu (Database)
*   Sử dụng kết nối từ file `model/database.php`.
*   **Công nghệ sử dụng:** Sử dụng thư viện `mysqli` truyền thống dưới dạng đối tượng kết nối trực tiếp `$conn` (không dùng PDO hay Class bao ngoài).
*   **Bảo mật & Xử lý lỗi:**
    *   Cảnh báo lỗi mặc định đã được tắt qua `mysqli_report(MYSQLI_REPORT_OFF);` nhằm bảo mật thông tin máy chủ.
    *   Khi kết nối CSDL lỗi, biến `$conn` sẽ tự động chuyển thành `false` (thay vì làm sập trang web).
    *   **Nhắc nhở:** Khi sử dụng biến kết nối `$conn` ở các file Model, luôn luôn thực hiện kiểm tra kiểm soát lỗi trước khi truy vấn:
        ```php
        if ($conn === false) {
            // Xử lý lỗi hoặc thông báo mất kết nối CSDL
            return [];
        }
        ```

## 4. Quy trình thêm tính năng mới (MVC Workflow)
Khi bạn muốn thêm một tính năng mới (ví dụ: bình luận, mã giảm giá, quản lý bài viết...):
1.  **Bước 1 (Database & Model):** Tạo các bảng trong CSDL (nếu cần), sau đó tạo hoặc cập nhật file Model tương ứng trong thư mục `model/` để viết các hàm thực thi truy vấn SQL.
2.  **Bước 2 (Controller):** Tạo hoặc cập nhật file Controller trong thư mục `control/` để xử lý logic, kiểm tra dữ liệu đầu vào và phân quyền truy cập.
3.  **Bước 3 (View):** Tạo giao diện hiển thị trong `view/client/` hoặc `view/admin/`. Chỉ in dữ liệu từ PHP (`echo`) và nhận biến từ Controller truyền xuống, tuyệt đối không truy vấn DB trực tiếp từ View.
4.  **Bước 4 (Routing):** Khai báo trang mới trong `index.php`.