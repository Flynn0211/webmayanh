# Hệ thống Website Máy ảnh & Ống kính (LENS & LIGHT)

Đây là mã nguồn dự án Website kinh doanh máy ảnh, ống kính và phụ kiện nhiếp ảnh được xây dựng theo mô hình **MVC (Model-View-Controller)** tinh gọn. Dự án sử dụng PHP thuần kết hợp CSDL MySQL và các kỹ thuật AJAX tương tác động cao cấp. Toàn bộ mã nguồn đã được tái cấu trúc (refactor) đạt chuẩn **100% Lập trình Hướng đối tượng (OOP)** vững chắc.

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
├── 📁 model/                # TẦNG DỮ LIỆU (OOP - Thực thi truy vấn CSDL qua PDO)
│   ├── database.php         # Khởi tạo kết nối PDO ($conn) dùng chung cho toàn hệ thống
│   ├── ProductModel.php     # Quản lý Class truy vấn hàng hóa (Nhận $conn qua constructor)
│   ├── ReviewModel.php      # Quản lý Class bình luận, đánh giá sản phẩm
│   ├── UserModel.php        # Quản lý Class đăng nhập, đăng ký & nâng cấp mật khẩu
│   ├── VoucherModel.php     # Quản lý Class xác thực điều kiện sử dụng mã giảm giá
│   └── SmtpMailer.php       # Class tĩnh hỗ trợ gửi mail qua thư viện PHPMailer
│
├── 📁 control/              # TẦNG ĐIỀU KHIỂN (OOP - Xử lý logic nghiệp vụ)
│   ├── ProductController.php# Class xử lý sản phẩm (Nhận $conn, khởi tạo ProductModel)
│   ├── AuthController.php   # Class quản lý Session, Đăng nhập/Đăng ký/Đổi mật khẩu
│   ├── OrderController.php  # Class quy trình thanh toán Transaction an toàn
│   ├── ArticleController.php# Class quản lý tin tức/bài viết
│   ├── ContactController.php# Class xử lý Form liên hệ & Đăng ký Newsletter
│   └── AdminController.php  # Class xử lý cập nhật danh mục, sản phẩm, quản trị
│
├── 📁 view/                 # TẦNG HIỂN THỊ (HTML + PHP in dữ liệu)
│   ├── 📁 client/           # Layout & trang dành cho Khách hàng
│   └── 📁 admin/            # Layout & trang dành cho Quản trị viên
│
├── config.php               # Lưu trữ các hằng số cấu hình (Database, SMTP Email...)
├── index.php                # (Router) Cổng vào điều phối mọi URL phía Khách hàng
├── README.md                # File hướng dẫn này
└── CHANGELOG.md             # Lịch sử cập nhật dự án
```

---

## ⚙️ Quy trình hoạt động của dự án (Application Flow)

Dự án hoạt động theo luồng **MVC Hướng đối tượng chuẩn mực** thông qua Router trung tâm, với nguyên tắc luân chuyển dữ liệu như sau:

1. **Khởi tạo hệ thống (Bootstrap & Config):**
   - Mọi request (yêu cầu) từ người dùng đều bắt buộc đi qua tệp `index.php` ở thư mục gốc (hoặc `admin/index.php` đối với trang quản trị).
   - Tại đây, hệ thống nạp `config.php` để lấy các hằng số cấu hình cốt lõi (Database, SMTP).
   - Tiếp theo, nạp `model/database.php` để tạo ra đối tượng kết nối **`$conn` (PDO)** duy nhất. Kết nối này sẽ tồn tại và được luân chuyển xuyên suốt một vòng đời request.

2. **Điều hướng (Routing):**
   - Dựa vào tham số URL `?page=...` hoặc lệnh gửi ngầm `?action=...`, Router (tức `index.php`) sẽ quyết định chức năng nào sẽ được thực thi.
   - Các hành động đơn lẻ (như API AJAX, thanh toán, đăng xuất) sẽ được bắt (catch) và trả kết quả ngay tại đầu Router. Các request xem trang thông thường sẽ được ánh xạ xuống việc "include" các file view.

3. **Khởi tạo Controller (Tầng điều khiển):**
   - Nhờ áp dụng mô hình OOP, hệ thống tiến hành khởi tạo trực tiếp một Controller và **"tiêm" (inject) kết nối CSDL** vào nó thông qua Constructor. 
   - Mã lệnh thực thi tiêu biểu: `(new AuthController($conn))->handleLogin()`.
   - Controller đóng vai trò như một "nhạc trưởng". Nó tiếp nhận dữ liệu đầu vào (POST/GET/JSON) từ người dùng, kiểm tra tính hợp lệ và gọi các Model tương ứng để xử lý nghiệp vụ sâu.

4. **Tương tác Model (Tầng dữ liệu):**
   - Bên trong nội bộ Controller, nó sẽ tự động khởi tạo Model chuyên trách (Ví dụ: `new UserModel($this->conn)`). 
   - Các Model class chứa toàn bộ các hàm thuần túy để truy vấn CSDL an toàn bằng kĩ thuật Prepared Statements (`SELECT`, `INSERT`, `UPDATE`). Model không tự xuất thông báo mà chỉ trả về dữ liệu thô (mảng JSON, biến Boolean) ngược lại cho Controller.

5. **Hiển thị View (Tầng giao diện):**
   - Sau khi Controller nhận được dữ liệu từ Model, chu trình xử lý tách làm 2 nhánh:
     - **Nếu là request AJAX:** Controller sẽ mã hóa mảng thành dạng `json_encode()` và `echo` trực tiếp về lại cho Javascript hiển thị không cần load trang.
     - **Nếu là request Web thông thường:** Khối điều hướng cuối `index.php` sẽ gán các biến cần thiết và nhúng (include) file giao diện HTML tĩnh (`view/client/...` hoặc `view/admin/...`). Trình duyệt sẽ nhận mã HTML tĩnh đã render hoàn chỉnh và hiển thị đến người dùng.

---

## 🛠️ Các Cơ Chế & Tính Năng Nổi Bật

### 1. Kiến trúc Base64 Tích hợp CSDL (Mới cập nhật)
- Loại bỏ hoàn toàn sự phụ thuộc vào file ảnh trên ổ cứng nội bộ. Toàn bộ tính năng upload (Ảnh Sản phẩm, Bài viết, ảnh phụ) đều được mã hóa theo chuẩn Base64 Real-time.
- Dữ liệu ảnh được ghi thẳng vào CSDL (bảng `hang_hoa`, `bai_viet` dưới dạng `LONGTEXT`). Đảm bảo khả năng sao lưu, di dời dự án cực nhanh, an toàn 100%.

### 2. Thuật toán trừ kho (Smart Inventory) đa kho thông minh
- Khách hàng không thể thêm hoặc thanh toán số lượng vượt mức tồn kho thực tế. 
- Ngay khi đặt lệnh mua, hệ thống sẽ dò tìm số lượng ở tất cả các chi nhánh kho (Multi-warehouse logic) để khấu trừ ưu tiên từ kho nhiều hàng nhất một cách an toàn thông qua cấu trúc **Database Transaction** của PHP PDO. Nếu có bất kỳ lỗi nào xảy ra trong quá trình thanh toán, mọi thay đổi sẽ tự động Rollback (khôi phục trạng thái ban đầu).

### 3. Tự động Gửi Email Thông Báo Đơn Hàng (SmtpMailer)
- Khi khách hàng đặt đơn hàng mới thành công, hệ thống gửi email xác nhận.
- Khi quản trị viên cập nhật trạng thái đơn hàng (từ Admin Panel) thành "Đang Giao", "Hoàn Thành" hoặc "Đã Hủy", một email chuẩn HTML chuyên nghiệp sẽ bắn tự động đến hộp thư của người mua theo thời gian thực (qua cổng Gmail SMTP TLS 587).

### 4. Thuật toán Voucher & Thanh toán VietQR
- Tích hợp tính năng tạo mã VietQR tự động khi khách hàng chọn thanh toán chuyển khoản Ngân hàng (MB Bank/ViettinBank). QR Code chứa sẵn số tiền chính xác, thông tin số tài khoản và cú pháp (Ví dụ: `THANH TOAN DH 12`).
- Thuật toán phân bổ động số tiền giảm giá Voucher vào từng đơn vị mặt hàng nhỏ trong giỏ hàng. Tích hợp ưu đãi chiết khấu cố định theo Hạng thành viên (Silver: 2%, Gold: 5%, Diamond: 10%).

### 5. Chia ngăn quản lý đơn hàng động bằng AJAX (Admin Panel)
- Bảng quản trị đơn hàng được tách làm 2 Tab rõ rệt: **Đơn đang xử lý** và **Đã hoàn thành**.
- Người quản trị cập nhật trạng thái thông qua các nút thao tác nhanh (Fetch API AJAX), danh sách đơn hàng sẽ chuyển ngăn dữ liệu tức thì mà không cần nạp lại trang, giúp tốc độ xử lý nghiệp vụ mượt mà như một ứng dụng độc lập (SPA - Single Page Application).

### 6. Bảo mật cao cấp toàn diện (Security)
- Chặn đứng 100% rủi ro tấn công SQL Injection bằng cách chuẩn hóa mọi câu truy vấn đầu vào thông qua PDO Prepared Statements (`?` parameter binding).
- Nâng cấp tiêu chuẩn bảo mật mật khẩu: Toàn bộ mật khẩu người dùng và quản trị đều được băm (hash) qua thuật toán bảo mật cấp cao `bcrypt` qua hàm `password_hash()` của lõi hệ thống PHP. Cơ chế tự động Upgrade mã băm đối với các tài khoản cũ mượt mà.

---

## 🚀 Hướng dẫn Cấu hình & Khởi chạy

### Bước 1: Nạp Cơ Sở Dữ Liệu
Bạn không cần tạo thư mục ảnh, cũng như không cần lo lắng về dữ liệu rác. File CSDL gốc đã gói gọn toàn bộ kiến trúc (bao gồm cấu trúc LONGTEXT).
- Tạo một Database mới trong MySQL (ví dụ: `webmayanh`).
- Import nội dung file `data/webmayanh_structure.sql` vào cơ sở dữ liệu vừa tạo. (File này đã bao gồm mọi bảng và một số bản ghi mẫu như tài khoản Admin, các Danh mục và Kho Hàng).

### Bước 2: Cấu hình hệ thống (config.php)
Mở file `config.php` ở thư mục gốc và tinh chỉnh lại theo thiết lập ở môi trường máy tính của bạn:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'webmayanh');

// Cấu hình gửi mail tự động qua SMTP (Tuỳ chọn - Nếu muốn test gửi mail)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com'); // Điền Email của bạn
define('SMTP_PASS', 'your_app_password');    // Điền Mật khẩu ứng dụng (App Password)
define('SMTP_FROM_NAME', 'LENS & LIGHT');
```

### Bước 3: Khởi chạy
- Bạn có thể chạy bằng phần mềm XAMPP, đưa dự án vào thư mục `htdocs` và truy cập `http://localhost/LapTrinhWebNangCao`.
- Hoặc sử dụng máy chủ PHP tích hợp sẵn siêu tốc. Mở terminal tại thư mục gốc dự án và chạy:
  ```bash
  php -S localhost:8000
  ```
  Sau đó truy cập `http://localhost:8000` trên trình duyệt.

**Thông tin đăng nhập Admin mẫu:**
- Tài khoản: `admin`
- Mật khẩu: `admin123`
