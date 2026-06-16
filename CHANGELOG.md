# Nhật ký thay đổi (Changelog) - WebMayAnh

Tất cả các thay đổi quan trọng đối với dự án **WebMayAnh** sẽ được lưu trữ tại đây.

## [1.2.0] - 2026-06-16

> [!IMPORTANT]
> **Yêu cầu cập nhật Cơ sở dữ liệu (Database Migration)**:
> Phiên bản này bổ sung trường địa chỉ cho tài khoản. Vui lòng chạy câu lệnh SQL sau trong CSDL local để tránh lỗi khi đăng ký/đăng nhập:
> ```sql
> ALTER TABLE `tai_khoan` ADD COLUMN `dia_chi` TEXT DEFAULT NULL AFTER `sdt`;
> ```

### Thêm mới (Added)
- **Thông tin giao hàng khi đăng ký & Cập nhật**:
  - Yêu cầu người dùng cung cấp **Số điện thoại** và **Địa chỉ** khi đăng ký tài khoản mới.
  - Hỗ trợ lựa chọn địa chỉ thông qua bộ dropdown 3 cấp độ (Tỉnh/Thành phố, Quận/Huyện, Phường/Xã) trực quan.
  - Thêm popup thông báo tự động hiển thị ngay sau khi đăng nhập đối với các tài khoản cũ (chưa có thông tin số điện thoại/địa chỉ), cho phép người dùng đi nhanh đến trang cập nhật hoặc bỏ qua để cập nhật sau.

### Cải tiến & Tối ưu (Changed & Optimized)
- **Tự động điền thông tin thanh toán**:
  - Tự động điền thông tin số điện thoại và địa chỉ đã lưu của tài khoản vào form thanh toán khi đặt hàng.
  - Cho phép người dùng chỉnh sửa linh hoạt thông tin giao hàng trực tiếp tại trang checkout trước khi xác nhận đặt hàng.
- **Tối ưu hóa dữ liệu địa chỉ hành chính**:
  - Tích hợp tệp dữ liệu hành chính Việt Nam (`assets/js/provinces_data.json`) nội bộ, loại bỏ việc gọi API bên thứ ba (`provinces.open-api.vn`) vốn chậm và kém ổn định.
  - Giải quyết hoàn toàn lỗi mất/trống thông tin dropdown tỉnh thành, cải thiện tốc độ phản hồi giao diện tức thì.
- **Phiên đăng nhập dài hạn & Bảo toàn Giỏ hàng**:
  - Tăng thời gian lưu phiên đăng nhập (PHP Session lifetime) lên tối đa **30 ngày** (2.592.000 giây) bằng cấu hình cookie và bộ dọn rác session ở máy chủ.
  - Bảo toàn tuyệt đối giỏ hàng của người dùng khi phiên đăng nhập hết hạn hoặc khi đăng xuất/đăng nhập nhờ cơ chế lưu trữ giỏ hàng hoàn toàn dưới `localStorage` ở Client.

---

## [1.1.0] - 2026-06-16

### Thêm mới (Added)
- **Quản lý đơn hàng nâng cao**:
  - Thêm trạng thái đơn hàng **"Đã Xác Nhận"** vào luồng xử lý đơn hàng.
  - Tự động cộng lại số lượng sản phẩm vào kho (`ton_kho_chi_tiet.so_luong_ton`) khi đơn hàng bị hủy (`Đã hủy`).
  - Hỗ trợ gửi Email thông báo tự động với tên trạng thái tiếng Việt thân thiện (ví dụ: *Chờ Xác Nhận*, *Đã Xác Nhận*, *Đang Xử Lý*, *Đang Giao*, *Đã Giao*, *Hoàn Thành*, *Đã hủy*).

### Sửa lỗi (Fixed)
- **Đồng bộ hóa & Khóa trạng thái đơn hàng**:
  - Khóa (vô hiệu hóa) ô chọn trạng thái trên giao diện Admin và chặn API cập nhật ở phía Backend một khi đơn hàng đã chuyển sang trạng thái cuối cùng là **"Hoàn Thành"** hoặc **"Đã hủy"**.
  - Tự động khôi phục (rollback) trạng thái trên giao diện dropdown của Admin nếu xảy ra lỗi trong quá trình gọi API cập nhật trạng thái đơn hàng.
  - Sửa lỗi hiển thị thiếu tùy chọn trạng thái trong danh sách dropdown xử lý đơn hàng của Admin.

### Cải tiến & Bảo mật (Changed & Security)
- **Ràng buộc xóa sản phẩm**:
  - Ngăn chặn việc xóa sản phẩm nếu sản phẩm đó đã được đặt mua (đã có trong bảng `chi_tiet_don_hang`) để bảo vệ tính toàn vẹn dữ liệu lịch sử đơn hàng.
  - Đưa ra cảnh báo hướng dẫn Quản trị viên vô hiệu hóa thay vì xóa sản phẩm để lưu lại lịch sử hóa đơn.
- **Tương thích Hosting & Xử lý Upload Ảnh (InfinityFree Compatibility)**:
  - Tự động kiểm tra và tạo thư mục lưu trữ ảnh vật lý (`uploads/products/` và `uploads/articles/`) với quyền `0755` nếu chưa tồn tại trên máy chủ.
  - Thêm thông báo lỗi chi tiết khi gặp lỗi ghi file tĩnh (ví dụ do phân quyền thư mục hoặc hết dung lượng đĩa), hướng dẫn thiết lập CHMOD `755` hoặc `777`.
  - Tối ưu hóa API thêm/sửa sản phẩm bằng cơ chế `try-catch` để bắt các ngoại lệ liên quan đến phân quyền thư mục khi giải mã ảnh Base64.

---

## [1.0.1] - 2026-06-16

### Sửa lỗi (Fixed)
- **Đồng bộ tồn kho**:
  - Đồng bộ số lượng tồn kho thực tế vào giỏ hàng ở Client trong thời gian thực để giới hạn chính xác nút tăng/giảm số lượng (+/-) không vượt quá số lượng tồn kho hiện tại.
  - Sửa lỗi hiển thị số lượng tồn kho trên trang chi tiết sản phẩm lấy sai nguồn dữ liệu (đã chuyển sang lấy trực tiếp từ bảng `ton_kho_chi_tiet`).

---

## [1.0.0] - 2026-06-15

### Khởi tạo (Initial Release)
- Khởi tạo cấu trúc dự án chuẩn MVC (Model-View-Controller) PHP thuần.
- Triển khai giao diện Client & Admin sử dụng Vanilla CSS (BEM) và Vanilla JS.
- Tích hợp hệ thống CSDL MySQL liên kết chặt chẽ qua PDO.
