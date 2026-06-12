# Hệ thống Website Máy ảnh & Ống kính (LENS & LIGHT)

Đây là mã nguồn dự án Website kinh doanh máy ảnh, ống kính và phụ kiện nhiếp ảnh được xây dựng theo mô hình **MVC (Model-View-Controller)** tinh gọn, sử dụng PHP thuần kết hợp CSDL MySQL và các kỹ thuật AJAX tương tác động cao cấp.

---

## 📁 Cấu trúc thư mục dự án

```text
📁 LapTrinhWebNangCao
├── 📁 assets/               # Tài nguyên tĩnh (Client-side) tự viết hoặc thư viện
│   ├── 📁 css/              # Giao diện tĩnh phân chia rõ ràng
│   │   ├── admin.css        # Giao diện bảng điều khiển Admin Premium
│   │   ├── base.css         # Hệ thống biến CSS (Color Palette HSL, Typography)
│   │   ├── client.css       # Giao diện trang khách hàng (Home, Detail, Cart...)
│   │   └── responsive.css   # Xử lý tương thích đa màn hình (Mobile, Tablet, Desktop)
│   └── 📁 js/               # Tương tác AJAX động, giỏ hàng tạm, quản lý yêu thích
│       ├── admin.js         # AJAX tương tác nghiệp vụ Admin
│       ├── auth.js          # AJAX Đăng nhập/Đăng ký & nút Yêu thích Global
│       ├── chitietsanpham.js# AJAX Nạp bài đánh giá, tính năng bình chọn sao
│       └── giohang.js       # AJAX xử lý đơn hàng, kho và phương thức thanh toán
│
├── 📁 model/                # TẦNG DỮ LIỆU (Thực thi truy vấn CSDL qua PDO)
│   ├── database.php         # Khởi tạo kết nối CSDL bằng PDO bảo mật tuyệt đối
│   ├── ProductModel.php     # Truy vấn hàng hóa và khuyến mãi đang diễn ra
│   ├── ReviewModel.php      # Quản lý bình luận, thêm đánh giá sản phẩm (API Admin)
│   ├── UserModel.php        # Quản lý đăng ký, đăng nhập & nâng cấp mật khẩu hash
│   ├── VoucherModel.php     # Xác thực điều kiện sử dụng mã giảm giá
│   └── SmtpMailer.php       # Thư viện gửi mail qua PHPMailer
│
├── 📁 control/              # TẦNG ĐIỀU KHIỂN & BẢO MẬT (Xử lý logic hệ thống)
│   ├── ProductController.php# Xử lý hiển thị sản phẩm, chặn trùng lặp đánh giá
│   ├── AuthController.php   # Quản lý Session đồng bộ, Đăng nhập/Đăng ký/Đổi mật khẩu
│   ├── OrderController.php  # Quy trình thanh toán Transaction an toàn, trừ kho thông minh
│   ├── ArticleController.php# Quản lý tin tức/bài viết (Base64 Database Upload)
│   ├── ContactController.php# Xử lý Form liên hệ & Đăng ký Newsletter tự động
│   └── AdminController.php  # Xử lý cập nhật danh mục, sản phẩm, Base64
│
├── 📁 view/                 # TẦNG HIỂN THỊ (HTML + PHP in dữ liệu)
│   ├── 📁 client/           # Layout & trang dành cho Khách hàng
│   └── 📁 admin/            # Layout & trang dành cho Quản trị viên
│
├── config.php               # Lưu trữ cấu hình toàn hệ thống (Database, SMTP Email...)
├── index.php                # (Router) Cổng vào duy nhất điều phối mọi URL của hệ thống
└── README.md                # File hướng dẫn này
```

---

## 🛠️ Các Cơ Chế & Tính Năng Nổi Bật

### 1. Kiến trúc Base64 Tích hợp CSDL (Mới cập nhật)
- Loại bỏ hoàn toàn sự phụ thuộc vào file ảnh trên ổ cứng nội bộ. Toàn bộ tính năng upload (Ảnh Sản phẩm, Bài viết, ảnh phụ) đều được mã hóa theo chuẩn Base64 Real-time.
- Dữ liệu ảnh được ghi thẳng vào CSDL (bảng `hang_hoa`, `bai_viet` dưới dạng `LONGTEXT`). Đảm bảo khả năng sao lưu, di dời dự án cực nhanh, an toàn 100%.

### 2. Thuật toán trừ kho (Smart Inventory) cực chuẩn
- Khách hàng không thể thêm hoặc thanh toán số lượng vượt mức tồn kho thực tế. 
- Ngay khi đặt lệnh mua, hệ thống sẽ dò tìm số lượng ở tất cả các chi nhánh kho (Multi-warehouse logic) để khấu trừ số lượng một cách an toàn thông qua cấu trúc Transaction của PHP PDO.

### 3. Tự động Gửi Email Thông Báo Đơn Hàng
- Khi khách hàng đặt đơn hàng mới thành công, hệ thống gửi email xác nhận.
- Khi quản trị viên cập nhật trạng thái đơn hàng (từ Admin Panel) thành "Đang Giao", "Hoàn Thành" hoặc "Đã Hủy", một email chuẩn HTML chuyên nghiệp sẽ được bắn tự động đến hộp thư của người mua theo thời gian thực.

### 4. Thuật toán Voucher & Thanh toán QR
- Tích hợp quét mã VietQR tự động khi khách hàng chọn thanh toán chuyển khoản, tạo trải nghiệm mua sắm mượt mà.
- Tính toán phân bổ tiền giảm giá Voucher theo từng mặt hàng đơn lẻ. Tích hợp ưu đãi theo Hạng thành viên (Silver, Gold, Diamond).

### 5. Chia ngăn quản lý đơn hàng động trong Admin Panel
- Bảng quản trị đơn hàng được tách làm 2 ngăn rõ rệt: **Đơn đang xử lý** và **Đã hoàn thành**.
- Cập nhật nhanh chóng trạng thái qua AJAX và chuyển ngăn dữ liệu tức thì mà không cần nạp lại trang, tiết kiệm thời gian vận hành.

### 6. Nâng cấp Bảo mật toàn diện với PDO (PHP Data Objects)
- Toàn bộ các câu truy vấn từ Client đến Admin đều dùng kỹ thuật Prepared Statements chặn đứng SQL Injection.
- Mật khẩu người dùng và quản trị đều băm qua thuật toán mạnh mẽ `bcrypt` (`password_hash`).

---

## 🚀 Hướng dẫn Cấu hình & Khởi chạy

### Bước 1: Nạp Cơ Sở Dữ Liệu
Bạn không cần tạo thư mục ảnh, cũng như không cần lo lắng về dữ liệu rác. File CSDL mới nhất đã gói gọn toàn bộ kiến trúc (bao gồm cấu trúc LONGTEXT).
- Tạo một Database mới trong MySQL (ví dụ: `webmayanh`).
- Import nội dung file `data/webmayanh_structure.sql` vào cơ sở dữ liệu vừa tạo. File này đã bao gồm mọi bảng và một số bản ghi mẫu như tài khoản Admin, các Danh mục và Kho Hàng.

### Bước 2: Cấu hình hệ thống (config.php)
Mở file `config.php` ở thư mục gốc và tinh chỉnh lại theo thiết lập ở môi trường máy tính của bạn:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'webmayanh');

// Cấu hình gửi mail tự động qua SMTP (Tuỳ chọn)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com'); // Điền Email của bạn
define('SMTP_PASS', 'your_app_password');    // Điền Mật khẩu ứng dụng của Gmail
define('SMTP_FROM_NAME', 'LENS & LIGHT');
```

### Bước 3: Khởi chạy
- Bạn có thể chạy qua phần mềm XAMPP bằng cách đưa dự án vào thư mục `htdocs` và truy cập `http://localhost/LapTrinhWebNangCao`.
- Hoặc sử dụng máy chủ PHP tích hợp sẵn. Mở terminal tại thư mục gốc dự án và chạy:
  ```bash
  php -S localhost:8000
  ```
  Sau đó truy cập `http://localhost:8000` trên trình duyệt.

**Thông tin đăng nhập Admin mặc định:**
- Tài khoản: `admin`
- Mật khẩu: `admin123`
