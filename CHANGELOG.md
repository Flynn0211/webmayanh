# BÁO CÁO CẬP NHẬT GIAO DIỆN & TÍNH NĂNG (GẦN NHẤT)

File này tóm tắt toàn bộ những thay đổi mới nhất về Frontend, Backend và Cơ sở dữ liệu để team dễ dàng nắm bắt trước khi thực hiện Git Commit.

## Bản Cập Nhật Ngày 30/05/2026 (16:50)
- **Lộ trình Phát triển 3 Tuần Tới**: Tạo file [features_3_weeks.md](file:///c:/xampp/htdocs/webmayanh/features_3_weeks.md) phác thảo chi tiết lộ trình xây dựng và phát triển các tính năng (Thanh toán VNPAY, Tìm kiếm nâng cao, Đánh giá có ảnh, Điểm tích lũy thành viên, Admin Analytics, SEO & Security) theo đúng mô hình MVC và quy chuẩn của dự án.

## Bản Cập Nhật Ngày 30/05/2026 (16:45)
- **Hệ thống Đăng nhập & Mật khẩu**: Chuyển đổi mã hóa mật khẩu sang password_hash() với cơ chế "tự động chuyển đổi" cho người dùng cũ. Sửa lại luồng redirect chuẩn thay vì in mã JavaScript thẻ <script>.
- **Tái cấu trúc MVC (AdminController)**: Đã bóc tách xử lý logic (thêm, sửa, xóa sản phẩm, thêm voucher) từ view/admin/admin.php sang control/AdminController.php để đúng chuẩn MVC.
- **Cấu hình Hệ thống & Email**: Thêm config.php để lưu các biến Database và cấu hình SMTP. SmtpMailer.php hiện đã lấy các thông số động từ config thay vì fix cứng.
- **Xử lý An toàn Lỗi Database**: Bổ sung xử lý bắt lỗi mất kết nối DB tại các Controllers, tránh Fatal Error và trả về chuẩn JSON.
- **Xuất Database Schema**: Tạo file data/webmayanh_structure.sql với toàn bộ định nghĩa các bảng và tài khoản admin mặc định.

## 1. Cơ Sở Dữ Liệu & Quản Lý Đơn Hàng (Backend)
- **Đồng bộ MySQL hoàn toàn:** Đã chuyển đổi triệt để hệ thống lưu trữ Đơn Hàng từ localStorage sang CSDL MySQL. localStorage hiện tại chỉ đóng vai trò giỏ hàng tạm thời, khi người dùng thanh toán mới chính thức đẩy lên Database.
- **Render qua PHP:** Hệ thống Admin hiện tại đã được inject trực tiếp mảng dữ liệu qua biến $dbOrders bằng PHP, đảm bảo tính đồng bộ real-time mà không phải phụ thuộc vào các file JSON cũ.

## 2. Giao Diện Quản Trị (Admin Dashboard)
- **Huy hiệu số lượng đơn hàng (Badge):** Fix lỗi delay hiển thị (ban đầu là 0) bằng cách dùng PHP count($dbOrders) để render ngay lúc tải trang.
- **Popup Chi tiết Đơn hàng:** 
  - Đập bỏ bảng (table) HTML cũ, thiết kế lại hoàn toàn bằng giao diện dạng Card (Thẻ) chuẩn Premium.
  - Sử dụng Flexbox để căn lề ảnh, thông tin sản phẩm và giá tiền chuẩn xác, không bị xô lệch khi tên sản phẩm quá dài.
  - Nút **Đóng** được cập nhật sang màu Đỏ để cảnh báo thị giác tốt hơn.

## 3. Trải Nghiệm Người Dùng (Client UI/UX)
- **Xử lý khoảng trống (Padding):** Fix lỗi icon "Bỏ yêu thích" đè lên tên sản phẩm trong mục *Đơn hàng của tôi > Sản phẩm yêu thích* bằng cách bổ sung padding-right: 2.5rem vào .fav-card__info.
- **Nút Yêu Thích (Heart Icon) thời gian thực:**
  - Viết lại hàm handleFavorite và isFavorited thành Global Helper (nằm trong auth.js) để tái sử dụng.
  - Nút trái tim ở mọi trang (Trang chủ, Máy ảnh, Ống kính, Chi tiết sản phẩm) giờ đây sẽ **chuyển màu đỏ và tô kín (Fill)** ngay lập tức khi click, và lưu trạng thái để tải trang lại vẫn giữ nguyên màu đỏ.
- **Sửa link Trang chủ:** 
  - Nút Khám Phá Bộ Sưu Tập và Tìm hiểu thêm ở Banner đầu trang đã được gán sự kiện click để nhảy trực tiếp sang trang Ống Kính (ongkinh.php).

## 4. Quản Lý Nội Dung (CMS - Bài viết)
- **Thêm bài viết mới:** Tạo thêm bài viết "JOURNAL NO. 12: Nghệ Thuật Của Sự Tối Giản Trong Nhiếp Ảnh Phong Cảnh" (BV04) vào hệ thống data/articles.json.
- **Fix định tuyến (Routing):** Sửa lỗi link đọc bài viết ngoài trang chủ bị trỏ nhầm vào danh sách. Đã đổi thành index.php?page=chitietbaiviet&slug=... để trỏ thẳng vào nội dung bài.
- **Khôi phục hình ảnh lỗi:** Đã thay thế các đường link ảnh Unsplash bị hỏng của bài "Leica M11 Monochrom" và "Top 5 ống kính Canon RF" bằng các link hình ảnh chất lượng cao và ổn định hơn.

---
**Ghi chú cho người push code:** Toàn bộ code đã được test kỹ. Các file temp (như temp_add.php, temp_update.php) có thể xóa an toàn trước khi push. Khuyến nghị chạy lệnh git add . và commit với thông điệp: "Feat: Migrate Orders to MySQL, Revamp Admin Order UI, Fix Favorite Toggle & Blog links".

## 5. Dọn Dẹp Mã Nguồn (Refactor & Clean-up)
- **Loại bỏ Code Rác:** Xóa toàn bộ logic đọc localStorage cũ kỹ (để thừa) trong các file trangchu.js, mayanh.js, ongkinh.js, chitietsanpham.js. Tất cả hiện giờ chỉ đọc dữ liệu xịn từ Database qua window.dbProducts.
- **Xóa File Thừa:** Đã xóa bỏ các file tạm thời không dùng tới như temp_db.php, databasenote.md và các file kịch bản sửa lỗi tạm.