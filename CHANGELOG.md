# NHẬT KÝ THAY ĐỔI & BÁO CÁO PHIÊN BẢN (CHANGELOG)

Tệp này ghi nhận toàn bộ các mốc cập nhật, sửa lỗi và nâng cấp kỹ thuật của hệ thống website Máy ảnh & Ống kính (LENS & LIGHT) theo trình tự thời gian ngược dòng (mới nhất lên đầu).

---

## [Phiên Bản Cập Nhật Ngày 01/06/2026 - Chiều tối] - Cải tiến Hiệu ứng chuyển động & Đồng bộ tương tác Premium

### ⚡ Cải tiến Thanh trượt chỉ mục trượt ngang động (.nav-indicator)
- **Định vị chuẩn xác:** Bổ sung `position: relative` cho `.site-nav__links` để căn chỉnh tọa độ `.nav-indicator` chuẩn tuyệt đối theo chiều cao và chân chữ của các nút menu chính. Loại bỏ hiện tượng thanh kẻ nằm sát đáy Header.
- **Áp dụng kỹ thuật FLIP bằng phần cứng:** Thay thế toàn bộ quá trình biến đổi `left` và `width` (gây chậm CPU do Layout/Paint) bằng các phép biến đổi hình học GPU-accelerated: `translateX` và `scaleX` với `will-change: transform`. Hiệu ứng trượt lướt mượt mà 60fps/120fps không bao giờ khựng lag.
- **Giao diện đồng bộ:** Thiết lập đệm dưới chữ `padding-bottom: 6px` cho tất cả tùy chọn menu và xóa bỏ đệm riêng lẻ của nút active để ngăn chặn lệch tâm và co giật khung hình.

### ✨ Đồng bộ hóa các hiệu ứng tương tác cao cấp & Micro-interactions
- **Phản hồi nút bấm thông minh (Tactile Feedback):**
  - Thêm hiệu ứng hover phóng to nhẹ nhàng (`scale(1.15)`) cho các icon nút trên Navbar (`.nav-icon-btn`, `.nav-menu-btn`) và tự động thu nhỏ đàn hồi (`scale(0.9)`) khi nhấn chuột (`:active`).
  - Nâng cấp các nút bấm nổi bật (`.btn-hero-primary`, `.btn-hero-ghost`, `.newsletter-form__btn`, `.review-submit-btn`) với hiệu ứng nhấc lên nhẹ (`translateY(-2px)`), tỏa bóng bóng mờ và phản hồi lực nhấn nhả.
  - Đồng điệu hóa nút lọc hãng sản xuất `.brand-btn` với các bước trượt êm ái và đổ bóng êm dịu khi active.
- **Hoạt họa Bouncy Badge Giỏ hàng:** Bổ sung hiệu ứng nảy đầy sống động `@keyframes badgePop` khi badge hiển thị số lượng giỏ hàng.
- **Premium Cards & Image Hover:** Đồng nhất toàn bộ thẻ sản phẩm trang chủ (`.product-card`) và danh mục (`.catalog-card`) sử dụng chung đường cong chuyển động Cubic Bezier `cubic-bezier(0.16, 1, 0.3, 1)`. Ảnh sản phẩm khi di chuột sẽ từ từ zoom rộng (`scale(1.08)`) và nhấc lên nhẹ nhàng mang lại cảm giác cực kỳ sang trọng.
- **Hiệu ứng chuyển trang Chi tiết Sản phẩm điện ảnh (Cinematic Staggered Transition):**
  - **Snappy Exit (`exit-to-product`):** Khi nhấp vào thẻ sản phẩm, giao diện hiện tại sẽ thu nhỏ 3% (`scale(0.97)`) và mờ dần cực nhanh trong 150ms để nhường chỗ cho trang chi tiết.
  - **Staggered Entry (`entry-product`):** Khi trang chi tiết tải xong, các khối nội dung sẽ xuất hiện tuần tự (staggered) bằng các lớp hoạt họa độc lập: breadcrumbs trượt nhẹ từ trên xuống; khung ảnh sản phẩm zoom lướt đàn hồi mềm mại từ dưới lên; tên, giá, nút mua hàng, thông số kỹ thuật và bình luận lần lượt trôi lên nhịp nhàng đầy nghệ thuật.
- **Sửa cú pháp chuyển tiếp trang:** Xóa bỏ khối `to` trùng lặp trong `@keyframes pageExit` của [base.css](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/assets/css/base.css) và làm sạch luồng dựng khung hình GPU.

---

## [Phiên Bản Cập Nhật Ngày 01/06/2026] - Tối ưu hóa mã nguồn & Khắc phục lỗi Đánh giá

### 🛠️ Sửa lỗi & Khắc phục sự cố mạng khi Đánh giá sản phẩm
- **Khắc phục lỗi "Lỗi mạng" khi Đánh giá:** 
  - Xác định nguyên nhân do bước kiểm tra trùng lặp đánh giá sử dụng sai tên bảng `danh_gia` (không tồn tại trong CSDL thực tế).
  - Cập nhật lại câu lệnh kiểm tra trùng lặp trong [ProductController::handleAddReview()](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/control/ProductController.php#L122) trỏ chuẩn xác tới bảng **`binh_luan_danh_gia`**.
  - Xử lý loại bỏ hoàn toàn các thẻ đóng PHP `?>` và dòng trống dư thừa ở cuối file [ProductController.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/control/ProductController.php) và [ReviewModel.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/model/ReviewModel.php) để loại bỏ nguy cơ làm hỏng định dạng phản hồi AJAX JSON do khoảng trắng rác.
  - Phục hồi chính xác các biểu thức lệnh return bị cắt xén trong [ReviewModel.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/model/ReviewModel.php).

### 🇻🇳 Việt hóa chú thích & Clean Code toàn diện
- **Việt hóa bình luận:** Tiến hành dịch thuật, viết lại và bổ sung Docstrings giải thích chi tiết toàn bộ mã nguồn PHP bằng tiếng Việt dễ hiểu trong tất cả các file:
  - **Tầng Model:** [database.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/model/database.php), [ProductModel.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/model/ProductModel.php), [ReviewModel.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/model/ReviewModel.php), [UserModel.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/model/UserModel.php), [VoucherModel.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/model/VoucherModel.php), và [SmtpMailer.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/model/SmtpMailer.php).
  - **Tầng Controller:** [ProductController.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/control/ProductController.php), [AdminController.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/control/AdminController.php), [ArticleController.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/control/ArticleController.php), [AuthController.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/control/AuthController.php), và [OrderController.php](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/control/OrderController.php).
- **Dọn dẹp mã nguồn:** Xóa bỏ toàn bộ các file PHP kiểm tra tạm thời (`list_users.php`, `test_db_check.php`...) ra khỏi workspace của dự án để đảm bảo mã nguồn sạch sẽ.

### 📝 Cập nhật Tài liệu Kỹ thuật
- **README.md:** Thiết kế lại sơ đồ cấu trúc thư mục hiện tại của hệ thống, bổ sung tài liệu hướng dẫn kỹ thuật chi tiết về cơ chế nhiều ảnh phụ (`anh_phu` JSON array), cơ chế chặn trùng lặp đánh giá sản phẩm, SMTP gửi mail socket thô, và cách thức cấu hình `config.php`.

---

## [Phiên Bản Cập Nhật Ngày 31/05/2026] - Nâng cấp Cơ chế Đa ảnh sản phẩm & Giao diện Header Logo

### 📸 Tích hợp Nhiều ảnh phụ Sản phẩm (Option 2)
- **Database Schema:** Thực hiện bổ sung cột `anh_phu` dạng `TEXT` lưu mảng JSON các đường dẫn ảnh phụ vào bảng sản phẩm `hang_hoa` thành công.
- **Admin Dashboard:** Nâng cấp form Thêm/Sửa sản phẩm cho phép Admin tải lên đồng thời nhiều tệp ảnh phụ bằng kéo thả widget. Tự động mã hóa Base64 và giải mã lưu trữ phía server.
- **Frontend Chi tiết sản phẩm:** Triển khai slideshow mượt mà dưới dạng các ảnh thu nhỏ (thumbnail) click để chuyển đổi hiển thị động lên ảnh chính của sản phẩm.
- **Bypass Caching:** Thêm cơ chế chèn dấu vết thời gian phiên bản (`?v=<?php echo time(); ?>`) cho các file tĩnh CSS/JS để cập nhật giao diện ngay lập tức trên trình duyệt của khách hàng không bị lưu cache cũ.

### 📐 Sắp xếp giao diện Header Logo & nút Quản trị
- **Logo Wrapping Fix:** Khắc phục triệt để lỗi logo chữ `LENS & LIGHT` bị dính liền hoặc tự động xuống dòng khi có tài khoản admin đăng nhập. Sử dụng thuộc tính `white-space: nowrap`, `flex-shrink: 0`, và bổ sung khoảng cách `margin-right: 3rem` an toàn.
- **Nút Quản trị (Admin Button):** Chuyển nút điều hướng `Quản trị` sang sát góc trên cùng bên phải của thanh điều hướng, thiết kế lại thành nút bấm kêu gọi hành động dạng Premium thanh lịch, đồng bộ hoàn hảo với layout chung.

---

## [Bản Cập Nhật Ngày 30/05/2026 (16:50)]
- **Lộ trình Phát triển 3 Tuần Tới**: Tạo file [features_3_weeks.md](file:///c:/Users/tuana/Desktop/LapTrinhWebNangCao/features_3_weeks.md) phác thảo chi tiết lộ trình xây dựng và phát triển các tính năng (Thanh toán VNPAY, Tìm kiếm nâng cao, Đánh giá có ảnh, Điểm tích lũy thành viên, Admin Analytics, SEO & Security) theo đúng mô hình MVC và quy chuẩn của dự án.

## [Bản Cập Nhật Ngày 30/05/2026 (16:45)]
- **Hệ thống Đăng nhập & Mật khẩu**: Chuyển đổi mã hóa mật khẩu sang password_hash() với cơ chế "tự động chuyển đổi" cho người dùng cũ. Sửa lại luồng redirect chuẩn thay vì in mã JavaScript thẻ <script>.
- **Tái cấu trúc MVC (AdminController)**: Đã bóc tách xử lý logic (thêm, sửa, xóa sản phẩm, thêm voucher) từ view/admin/admin.php sang control/AdminController.php để đúng chuẩn MVC.
- **Cấu hình Hệ thống & Email**: Thêm config.php để lưu các biến Database và cấu hình SMTP. SmtpMailer.php hiện đã lấy các thông số động từ config thay vì fix cứng.
- **Xử lý An toàn Lỗi Database**: Bổ sung xử lý bắt lỗi mất kết nối DB tại các Controllers, tránh Fatal Error và trả về chuẩn JSON.
- **Xuất Database Schema**: Tạo file data/webmayanh_structure.sql với toàn bộ định nghĩa các bảng và tài khoản admin mặc định.

---

## [Bản Cập Nhật Ngày 28/05/2026]
- **Đồng bộ MySQL hoàn toàn:** Đã chuyển đổi triệt để hệ thống lưu trữ Đơn Hàng từ localStorage sang CSDL MySQL. localStorage hiện tại chỉ đóng vai trò giỏ hàng tạm thời, khi người dùng thanh toán mới chính thức đẩy lên Database.
- **Popup Chi tiết Đơn hàng:** Thiết kế lại hoàn toàn bằng giao diện dạng Card (Thẻ) chuẩn Premium, sử dụng Flexbox căn lề đẹp mắt, nút Đóng màu Đỏ nổi bật.
- **Nút Yêu Thích (Heart Icon) thời gian thực:** Chuyển trạng thái yêu thích đồng bộ global qua AJAX và lưu giữ màu đỏ yêu thích khi làm mới trang.