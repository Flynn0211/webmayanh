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
│       └── chitietsanpham.js# AJAX Nạp bài đánh giá, tính năng bình chọn sao
│
├── 📁 uploads/              # Lưu trữ dữ liệu động (Ảnh tải lên an toàn)
│   ├── 📁 products/         # Ảnh sản phẩm do Admin tải lên (chính + ảnh phụ)
│   └── 📁 articles/         # Ảnh minh họa bài viết CMS
│
├── 📁 model/                # TẦNG DỮ LIỆU (Thực thi truy vấn CSDL MySQL)
│   ├── database.php         # Khởi tạo kết nối MySQLi ($conn) bảo mật cao
│   ├── ProductModel.php     # Truy vấn hàng hóa và khuyến mãi đang diễn ra
│   ├── ReviewModel.php      # Quản lý bình luận, thêm đánh giá sản phẩm
│   ├── UserModel.php        # Quản lý đăng ký, đăng nhập & nâng cấp mật khẩu hash
│   ├── VoucherModel.php     # Xác thực điều kiện sử dụng mã giảm giá
│   └── SmtpMailer.php       # Thư viện gửi mail qua Socket thô độc lập
│
├── 📁 control/              # TẦNG ĐIỀU KHIỂN & BẢO MẬT (Xử lý logic hệ thống)
│   ├── ProductController.php# Xử lý hiển thị sản phẩm, chặn trùng lặp đánh giá
│   ├── AuthController.php   # Quản lý Session đồng bộ, Đăng nhập/Đăng ký/Đổi mật khẩu
│   ├── OrderController.php  # Quy trình thanh toán Transaction an toàn, trừ kho thông minh
│   ├── ArticleController.php# Quản lý tin tức/bài viết CMS (CSDL articles.json)
│   └── AdminController.php  # Xử lý upload ảnh base64, thêm/sửa/xóa sản phẩm
│
├── 📁 view/                 # TẦNG HIỂN THỊ (HTML + PHP in dữ liệu)
│   ├── 📁 client/           # Layout & trang dành cho Khách hàng
│   │   ├── 📁 layout/       # Thành phần chung (_navbar.php, _footer.php...)
│   │   ├── trangchu.php     # Trang chủ hiển thị banner và sản phẩm nổi bật
│   │   ├── chitietsanpham.php # Chi tiết sản phẩm, slideshow ảnh & đánh giá
│   │   ├── giohang.php      # Trang giỏ hàng, áp dụng voucher & thanh toán
│   │   └── donhang.php      # Lịch sử đơn hàng và cập nhật hành trình
│   │
│   └── 📁 admin/            # Layout & trang dành cho Quản trị viên
│       ├── 📁 layout/       # Sidebar, Topbar quản trị
│       ├── admin.php        # Giao diện tổng hợp quản trị nâng cao
│       └── login.php        # Trang đăng nhập quản trị
│
├── config.php               # Lưu trữ cấu hình toàn hệ thống (Database, SMTP Email...)
├── index.php                # (Router) Cổng vào duy nhất điều phối mọi URL của hệ thống
└── README.md                # File hướng dẫn này
```

---

## 🛠️ Các Cơ Chế & Tính Năng Nổi Bật Gần Đây

### 1. Cơ chế nhiều ảnh phụ sản phẩm (Option 2 - `anh_phu` JSON)
- **Database:** Bảng `hang_hoa` bổ sung thêm cột `anh_phu` kiểu dữ liệu `TEXT` để lưu trữ một danh sách các ảnh phụ dạng mảng JSON (ví dụ: `["uploads/products/image1.jpg", "uploads/products/image2.jpg"]`).
- **Admin:** Khi Thêm/Sửa sản phẩm, Admin có thể kéo thả tải lên cùng lúc nhiều hình ảnh phụ. Phía server (`AdminController::handleAjaxAction()`) sẽ tự động giải mã các chuỗi ảnh base64, lưu file an toàn vào thư mục `uploads/products/` và đóng gói thành chuỗi JSON để lưu trữ trực tiếp vào cột `anh_phu`.
- **Client:** Trong trang chi tiết sản phẩm, hệ thống tự động giải mã mảng JSON này để hiển thị thành danh sách các ảnh nhỏ (thumbnail) ngay dưới ảnh chính, hỗ trợ click chuyển đổi hiển thị ảnh chính động mượt mà.

### 2. Kiểm tra chặn trùng lặp đánh giá sản phẩm
- **Nghiệp vụ:** Để đảm bảo tính trung thực và ngăn chặn spam, **mỗi tài khoản khách hàng chỉ được đánh giá mỗi sản phẩm tối đa một lần duy nhất**.
- **Kỹ thuật:** Phía server (`ProductController::handleAddReview()`) sẽ thực hiện kiểm tra kiểm soát lỗi trước khi ghi nhận đánh giá mới bằng cách đếm số lượng bản ghi tương ứng của tài khoản hiện hành trên bảng `binh_luan_danh_gia` (tên bảng chính xác trong CSDL). Nếu đã tồn tại đánh giá, hệ thống sẽ trả về phản hồi JSON thông báo lịch sự từ chối ghi nhận.

### 3. Chia ngăn quản lý đơn hàng động trong Admin Panel
- **Trải nghiệm:** Bảng quản trị đơn hàng được tách làm 2 ngăn rõ rệt: **Đơn đang xử lý** (Chờ xác nhận, Đang xử lý, Đang giao...) và **Đã hoàn thành** (Đơn đã hoàn thành, Đã hủy).
- **Real-time:** Khi Admin bấm chuyển trạng thái đơn hàng sang "Hoàn thành", hệ thống sẽ sử dụng AJAX gửi yêu cầu cập nhật xuống CSDL, đồng thời **tự động di chuyển dòng đơn hàng đó sang ngăn Đã hoàn thành** trên giao diện ngay lập tức mà không cần tải lại toàn bộ trang.

### 4. Đồng bộ trạng thái Session và bảo mật băm mật khẩu
- **Session:** Đồng bộ triệt để trạng thái đăng nhập giữa Client và Admin Portal. Khi Admin đăng nhập, hệ thống cũng tự động kích hoạt Session Client tương ứng để tránh bị đá văng về trang chủ hoặc mất quyền truy cập.
- **Bảo mật:** Toàn bộ mật khẩu của tài khoản đều được băm bảo mật bằng thuật toán băm chuẩn `password_hash()` (bcrypt). Hệ thống có tích hợp sẵn cơ chế **tự động nâng cấp mật khẩu** (khi tài khoản cũ dùng mật khẩu thô đăng nhập thành công, mật khẩu đó sẽ lập tức được băm và ghi đè an toàn vào CSDL).

### 5. Kỹ thuật hoạt họa FLIP bằng GPU & Micro-interactions 60fps cao cấp
- **Thanh trượt ngang động (FLIP Underline):** Áp dụng thuật toán **FLIP (First, Last, Invert, Play)** cho thanh kẻ đỏ trượt ngang chỉ mục menu `.nav-indicator`. Chuyển đổi toàn bộ quá trình biến đổi từ thay đổi `left`/`width` (gây lag do CPU) sang **CSS transforms (`translateX` và `scaleX`)** tận dụng tăng tốc phần cứng từ GPU thông qua `will-change: transform`. Giúp thanh kẻ lướt êm ái 60fps/120fps trên mọi thiết bị khi chuyển tiếp trang.
- **Tương tác phản hồi lực (Micro-interactions):** Đồng bộ các hiệu ứng hover nâng nổi, đổ bóng mờ ảo cho các nút bấm (`.btn-hero-primary`, `.btn-hero-ghost`...) và các icon trên thanh Navbar với các bước nhấn nhả đàn hồi vô cùng chuyên nghiệp.
- **Đồng bộ hóa nhịp độ chuyển động:** Toàn bộ thẻ sản phẩm (`.product-card`, `.catalog-card`) sử dụng chung đường cong chuyển động Cubic Bezier `cubic-bezier(0.16, 1, 0.3, 1)` cho hiệu ứng phóng to ảnh và nhấc thẻ, tạo nên trải nghiệm người dùng cực kỳ đồng bộ, tinh tế và sang trọng.

---

## 🚀 Hướng dẫn Cấu hình & Sử dụng

### 1. Cấu hình hệ thống (config.php)
Tạo hoặc mở file `config.php` ở thư mục gốc và điền các thông tin kết nối CSDL cũng như tài khoản gửi mail của bạn:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'webmayanh');

// Cấu hình gửi mail tự động qua SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com'); // Điền Email của bạn
define('SMTP_PASS', 'your_app_password');    // Điền Mật khẩu ứng dụng (App Password)
define('SMTP_FROM_NAME', 'LENS & LIGHT');
```

### 2. Khởi chạy
- Bạn có thể chạy ứng dụng qua phần mềm XAMPP bằng cách đưa dự án vào thư mục `htdocs` và truy cập `http://localhost/LapTrinhWebNangCao`.
- Hoặc sử dụng máy chủ PHP tích hợp sẵn bằng cách mở terminal tại thư mục gốc và chạy lệnh:
  ```bash
  php -S localhost:8000
  ```
  Sau đó truy cập `http://localhost:8000` trên trình duyệt.