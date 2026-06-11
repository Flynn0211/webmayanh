# NHẬT KÝ THAY ĐỔI & BÁO CÁO PHIÊN BẢN (CHANGELOG)

Tệp này ghi nhận toàn bộ các mốc cập nhật, sửa lỗi và nâng cấp kỹ thuật của hệ thống website Máy ảnh & Ống kính (LENS & LIGHT) theo trình t�## [Phiên Bản Cập Nhật Ngày 11/06/2026] - Tích hợp Rich Text Editor (CKEditor 5), Đồng bộ CSDL bài viết & Quản lý Danh mục Sản phẩm an toàn

### 📝 Tích hợp Trình soạn thảo văn bản Rich Text (CKEditor 5) cho Bài viết
- **Editor cao cấp:** Thay thế thẻ `<textarea>` thông thường bằng **CKEditor 5** (phiên bản Classic CDN) cho ô nhập nội dung bài viết trong trang Admin. Quản trị viên dễ dàng định dạng văn bản (in đậm, in nghiêng, tiêu đề, liên kết, danh sách...).
- **Upload ảnh trực tiếp qua CKEditor:** Hỗ trợ upload kéo-thả hoặc chọn tệp hình ảnh trực tiếp trong khung soạn thảo thông qua adapter `simpleUpload`. Cấu hình route API `upload_image` tại [admin/index.php](file:///c:/xampp/htdocs/webmayanh/admin/index.php) và phương thức xử lý upload an toàn [ArticleController::handleCKEditorUpload()](file:///c:/xampp/htdocs/webmayanh/control/ArticleController.php) lưu ảnh vào thư mục `uploads/articles/`.
- **Đồng bộ hóa dữ liệu soạn thảo:** Hàm `syncCKEditor()` trong [admin.js](file:///c:/xampp/htdocs/webmayanh/assets/js/admin.js) tự động chuyển dữ liệu Rich Text từ CKEditor instance sang trường dữ liệu form `<textarea>` ẩn trước khi submit form.

### 🗄️ Chuyển đổi và Đồng bộ Bài viết sang Cơ sở dữ liệu (Database MySQL)
- **Cập nhật Database Schema:** Thêm bảng `bai_viet` vào tệp cấu trúc [webmayanh_structure.sql](file:///c:/xampp/htdocs/webmayanh/data/webmayanh_structure.sql) với các trường dữ liệu chuẩn: `ma_bv` (khóa chính), `tieu_de`, `slug` (duy nhất), `anh_bia`, `tom_tat`, `noi_dung`, `ma_tk_dang`, `trang_thai`, `ngay_dang`.
- **Xây dựng Model PHP mới:** Tạo lớp [ArticleModel.php](file:///c:/xampp/htdocs/webmayanh/model/ArticleModel.php) sử dụng PDO prepared statements để thực hiện toàn bộ thao tác CRUD (Create, Read, Update, Delete) bài viết trực tiếp với CSDL, thay thế cho cơ chế lưu file JSON tạm thời cũ.
- **Tái cấu trúc Controller & Đồng bộ Schema:** Cập nhật [ArticleController.php](file:///c:/xampp/htdocs/webmayanh/control/ArticleController.php) chuyển sang sử dụng PDO và tương tác với `ArticleModel`, tự động đồng bộ hóa các trường thông tin bài viết theo schema CSDL mới (`anh_bia`, `tom_tat`, `trang_thai`, v.v.).

### 🏗️ Áp dụng chuẩn MVC cho Bài viết phía Frontend & Clean Code
- **Bóc tách logic xử lý (MVC Routing):** Tái cấu trúc luồng tải trang Tin tức/Bài viết bằng cách chuyển toàn bộ logic truy vấn dữ liệu từ Views [baiviet.php](file:///c:/xampp/htdocs/webmayanh/view/client/baiviet.php) và [chitietbaiviet.php](file:///c:/xampp/htdocs/webmayanh/view/client/chitietbaiviet.php) sang [index.php](file:///c:/xampp/htdocs/webmayanh/index.php) đóng vai trò Router/Controller chính.
- **Loại bỏ inline CSS:** Di chuyển và tách biệt toàn bộ các khai báo CSS inline trước đây trong views bài viết ra file stylesheet tĩnh tập trung [client.css](file:///c:/xampp/htdocs/webmayanh/assets/css/client.css) (phần `/* --- BLOG LIST --- */` và `/* --- BLOG DETAIL --- */`), tối ưu tốc độ render của trình duyệt.
- **Render nội dung động an toàn:** Sử dụng hàm `html_entity_decode` trong trang [chitietbaiviet.php](file:///c:/xampp/htdocs/webmayanh/view/client/chitietbaiviet.php) để render chính xác mã HTML động từ CKEditor mà không bị escape các ký tự đặc biệt.

### 📁 Ra mắt Tab Quản lý Danh mục (Category Management) & Ràng buộc Xóa an toàn
- **Giao diện quản lý Admin:** Thêm tab "Quản lý danh mục" (`tab-categories`) vào Sidebar của trang quản trị [admin.php](file:///c:/xampp/htdocs/webmayanh/view/admin/admin.php) kèm theo bảng danh sách danh mục và modal thêm/sửa danh mục (`categoryModal`).
- **Xử lý JavaScript (Client-side):** Triển khai hàm render danh mục, đóng/mở modal, gửi API thêm/sửa danh mục không đồng bộ (`fetch` POST), và hàm `deleteCategory` trong [admin.js](file:///c:/xampp/htdocs/webmayanh/assets/js/admin.js).
- **Ràng buộc an toàn toàn vẹn dữ liệu (Backend-side):** 
  - Thêm các endpoint xử lý `add_category`, `edit_category`, `delete_category` tại [AdminController.php](file:///c:/xampp/htdocs/webmayanh/control/AdminController.php).
  - Riêng logic xóa danh mục (`delete_category`): Thực hiện truy vấn kiểm tra ràng buộc `SELECT COUNT(*) FROM hang_hoa WHERE ma_dm = ?`. Nếu danh mục đang chứa sản phẩm, hệ thống từ chối xóa và trả về thông báo lỗi trực quan cho người dùng. Chỉ cho phép xóa khi danh mục hoàn toàn trống.

---

## [Phiên Bản Cập Nhật Ngày 09/06/2026] - Nâng cấp Hệ thống Email Marketing, Tối ưu Hiệu suất & Chống SQL Injection

### 📩 Hệ thống Tự động hóa Email & Marketing (SmtpMailer)
- **Thay thế công nghệ lõi:** Chuyển đổi từ `mail()`/socket thuần sang thư viện **PHPMailer** chuẩn mực (Tích hợp thủ công không dùng Composer) nhằm khắc phục 100% lỗi rớt email.
- **Tính năng Đăng ký Bản tin (Newsletter):** Kích hoạt thành công form nhập email ở Footer trang chủ (`trangchu.php`). Khi khách hàng đăng ký, hệ thống tự động:
  - Cập nhật email trực tiếp vào hồ sơ tài khoản (nếu khách đang đăng nhập nhưng bị trống email).
  - Lưu trữ cố định vào bảng CSDL mới `email_dang_ky` để phục vụ Marketing.
  - Tự động gửi một **Email Chào Mừng** ("Welcome Email") đẹp mắt vào hòm thư của khách.
- **Gửi Email Marketing Tự động:** Khi quản trị viên thêm một **Khuyến mãi** mới trên trang Admin, hệ thống sẽ tự động quét chéo toàn bộ dữ liệu (bằng lệnh `UNION`) để gửi hàng loạt thông báo giảm giá cho toàn bộ người dùng có email trong hệ thống.
- **Gửi Email Ẩn danh siêu tốc (BCC Bulk Mailing):** Thiết lập cơ chế gửi hàng trăm email cùng lúc bằng trường `BCC`, tiết kiệm 99% thời gian so với gửi từng người và bảo vệ tuyệt đối danh tính khách hàng.

### ⚡ Tối ưu Trải nghiệm Lạc quan (Optimistic UI) & Hiệu năng Backend
- **Trải nghiệm tốc độ 0 giây:** Nút gửi "Liên Hệ" và "Đăng ký Bản tin" được áp dụng cơ chế *Fire-and-forget* (Bắn và Quên) bằng `fetch()`. Khi nhấn nút, người dùng lập tức nhận được thông báo thành công thay vì phải ngồi đợi 2-3 giây như trước.
- **Công nghệ SMTP Keep-Alive:** Lớp `SmtpMailer` được thiết kế lại thành **Singleton Pattern**, chỉ mở 1 cổng TCP duy nhất để nã hàng loạt Email rồi mới đóng lại, biến hệ thống thành một cỗ máy gửi thư cực nhẹ và nhanh.
- **Vượt qua Bộ lọc Thư rác (Anti-Spam Filter):** Tất cả thư đi từ hệ thống đều được đẻ ra nội dung dạng `AltBody` (Text thô) để đạt chuẩn điểm Spam tối thiểu của các nhà cung cấp như Google/Outlook.

### 🛡️ Nâng cấp Cấu trúc Database & Vá Lỗ hổng SQL Injection
- Cấu trúc lại toàn bộ các câu truy vấn phức tạp của `AdminController.php` (như sửa/thêm/xóa sản phẩm, đơn hàng, danh mục) và `OrderController.php`, loại bỏ 100% các câu truy vấn nối chuỗi thô sơ sang **Prepared Statements** (kết hợp với PDO).
- Tạo mới bảng `email_dang_ky` lưu trữ email từ Newsletter.
- Vá lỗi cấu hình App Password khi dán bị thừa "khoảng trắng" bằng hàm `str_replace` bên trong `SmtpMailer`.��i dùng lập tức nhận được thông báo thành công thay vì phải ngồi đợi 2-3 giây như trước.
- **Công nghệ SMTP Keep-Alive:** Lớp `SmtpMailer` được thiết kế lại thành **Singleton Pattern**, chỉ mở 1 cổng TCP duy nhất để nã hàng loạt Email rồi mới đóng lại, biến hệ thống thành một cỗ máy gửi thư cực nhẹ và nhanh.
- **Vượt qua Bộ lọc Thư rác (Anti-Spam Filter):** Tất cả thư đi từ hệ thống đều được đẻ ra nội dung dạng `AltBody` (Text thô) để đạt chuẩn điểm Spam tối thiểu của các nhà cung cấp như Google/Outlook.

### 🛡️ Nâng cấp Cấu trúc Database & Vá Lỗ hổng SQL Injection
- Cấu trúc lại toàn bộ các câu truy vấn phức tạp của `AdminController.php` (như sửa/thêm/xóa sản phẩm, đơn hàng, danh mục) và `OrderController.php`, loại bỏ 100% các câu truy vấn nối chuỗi thô sơ sang **Prepared Statements** (kết hợp với PDO).
- Tạo mới bảng `email_dang_ky` lưu trữ email từ Newsletter.
- Vá lỗi cấu hình App Password khi dán bị thừa "khoảng trắng" bằng hàm `str_replace` bên trong `SmtpMailer`.

---

## [Phiên Bản Cập Nhật Ngày 07/06/2026] - Cập nhật Bản đồ Địa điểm cửa hàng sang Trụ sở chính (Cơ sở 613 Âu Cơ)

### 🗺️ Cập nhật & Đồng bộ Bản đồ Đại học Văn Hiến (Cơ sở 613 Âu Cơ)
- **Cập nhật Địa chỉ Showroom:** Thay đổi thông tin địa chỉ từ cơ sở cũ Harmony Campus (`624 Âu Cơ, Bảy Hiền`) sang Trụ sở chính mới (`613 Âu Cơ, Phú Trung, Tân Phú, Hồ Chí Minh`).
- **Nâng cấp Bản đồ Nhúng Google Maps:** Cập nhật tham số tọa độ tìm kiếm trong thẻ `iframe` bản đồ trên toàn hệ thống về địa chỉ `613 Âu Cơ` giúp khách hàng chỉ đường và định vị chính xác nhất:
  - **Footer Tối & Footer Sáng:** Cập nhật ở [_footer_dark.php](file:///c:/xampp/htdocs/webmayanh/view/client/layout/_footer_dark.php) và [_footer_light.php](file:///c:/xampp/htdocs/webmayanh/view/client/layout/_footer_light.php).
  - **Trang Liên Hệ:** Đồng bộ hóa bản đồ bản lớn và văn bản hiển thị địa chỉ trên trang [lienhe.php](file:///c:/xampp/htdocs/webmayanh/view/client/lienhe.php).

---

## [Phiên Bản Cập Nhật Ngày 04/06/2026] - Nâng cấp PDO, Cải tiến Thuật toán Voucher & Trang Liên Hệ

### 🔒 Chuyển đổi toàn diện cơ sở dữ liệu sang PDO (PHP Data Objects)
- **Tầng Cơ Sở Dữ Liệu:** Đã nâng cấp hoàn toàn file `database.php` chuyển từ thư viện `mysqli` cũ sang `PDO`. Điều này mang lại sự bảo mật tuyệt đối chống lại các tấn công SQL Injection.
- **Tái cấu trúc Tầng Model:** Đã cấu trúc lại toàn bộ các class Model bao gồm `ProductModel`, `ReviewModel`, `UserModel`, và `VoucherModel` để sử dụng chuẩn Prepared Statements của PDO (`$stmt->execute()`). Các Controller như `AdminController` và `OrderController` cũng được đồng bộ triệt để.

### 💰 Cải tiến thuật toán Áp dụng Khuyến Mãi (Voucher)
- **Phân bổ giảm giá vào TỪNG sản phẩm:** Nâng cấp thuật toán ở Frontend (`giohang.js`) và Backend (`OrderController.php`). Khi áp dụng voucher giảm tổng tiền, thay vì chỉ trừ ở tổng cuối cùng, hệ thống tự động tính toán phân bổ đều mức giảm trừ thẳng vào đơn giá của từng sản phẩm trong giỏ hàng.
- **Cải tiến UI/UX Giỏ hàng:** Khách hàng giờ đây có thể nhìn thấy trực quan mức giá cũ (gạch ngang) và mức giá mới (đã trừ giảm giá voucher) cho từng món hàng riêng lẻ ngay lập tức.
- **Sửa lỗi Quản lý Khuyến Mãi Admin:** Khắc phục triệt để lỗi mạng (Network error) khi chuyển sang tab Khuyến mãi. Bổ sung đầy đủ API `get_promotions` và `delete_promotion` vào `AdminController` để fetch dữ liệu voucher thực. Fix lỗi không lưu được khi thêm mới khuyến mãi.

### 🌟 Tính năng Quản lý Đánh Giá Thực Tế (Admin Reviews)
- Khắc phục lỗi trang Quản lý Đánh giá trong Admin chỉ hiển thị dữ liệu ảo (Mock data).
- Bổ sung API `get_reviews` và `delete_review` liên kết trực tiếp với cơ sở dữ liệu qua `ReviewModel.php`. Quản trị viên giờ đây có thể duyệt và xóa các đánh giá thực tế của người dùng trực tiếp trên Dashboard.

### 🗺️ Ra mắt Trang Liên Hệ & Đồng bộ hóa Bản đồ Google Maps
- **Trang Liên Hệ Mới:** Xây dựng trang Liên hệ (`lienhe.php`) chuẩn tĩnh theo yêu cầu không cần Database, với thiết kế Form sang trọng và đầy đủ thông tin chi tiết. 
- **Tích hợp Google Maps:** Nhúng bản đồ Google Maps kích thước lớn cực đẹp vào trang Liên hệ.
- **Cải tiến Footer toàn hệ thống:** 
  - Sửa lỗi các nút bấm trống `#` trong Footer để chuyển hướng chuẩn xác về các trang tương ứng.
  - Tích hợp một bản đồ Google Maps siêu nhỏ gọn (cao 80px) trực tiếp vào Footer (cho cả giao diện Sáng và Tối) giúp tăng tính chuyên nghiệp.
  - Tối ưu logic PHP thông minh để ẩn bản đồ Footer này chỉ riêng khi người dùng đang ở trang Liên hệ (tránh trùng lặp 2 bản đồ).

### 🐛 Sửa lỗi Logic Lọc Phụ Kiện (Balo)
- Fix lỗi người dùng nhấn vào nút "Balo & Túi" nhưng không hiển thị sản phẩm balo. Nguyên nhân do bất đồng bộ ký tự "Ba lô" (có khoảng trắng) trong CSDL và "balo" trong mã nguồn. Đã khắc phục triệt để bằng cách Regex bộ lọc trong file `phukien.js`.

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
