# 📐 QUY TẮC DỰ ÁN — WEBMAYANH

> Đây là tài liệu quy tắc bắt buộc cho **toàn bộ thành viên** phát triển dự án `webmayanh`.
> Mọi đóng góp (code mới, sửa lỗi, tính năng mới) đều phải tuân thủ nghiêm ngặt các quy tắc dưới đây.

---

## 📁 1. Kiến trúc & Cấu trúc thư mục

Dự án áp dụng mô hình **MVC (Model – View – Controller)** với **Single Entry Point**.

```
webmayanh/
├── assets/
│   ├── css/          # Toàn bộ CSS tự viết (KHÔNG dùng Tailwind)
│   └── js/           # JavaScript xử lý AJAX, tương tác phía client
├── model/            # Tầng dữ liệu: truy vấn MySQL
├── control/          # Tầng điều khiển: xử lý logic & phân quyền
├── view/
│   ├── client/       # Giao diện dành cho người dùng
│   │   └── layout/   # Các partial dùng chung (_navbar, _footer...)
│   └── admin/        # Giao diện dành cho quản trị viên
│       └── layout/
├── uploads/
│   ├── products/     # Ảnh sản phẩm do Admin upload
│   └── avatars/      # Ảnh đại diện người dùng
├── config.php        # Hằng số hệ thống (BASE_URL, cấu hình email...)
├── index.php         # 🚪 Router duy nhất — điểm vào của toàn bộ hệ thống
├── README.md
└── rule.md           # ← File này
```

### ✅ Nguyên tắc phân lớp

| Tầng | Thư mục | Được phép | Không được phép |
|------|---------|-----------|-----------------|
| **Model** | `model/` | Truy vấn SQL, trả về dữ liệu | Xử lý logic nghiệp vụ, in HTML |
| **Controller** | `control/` | Xử lý logic, phân quyền, gọi Model | Truy vấn SQL trực tiếp, in HTML |
| **View** | `view/` | In dữ liệu từ PHP (`echo`), HTML | Truy vấn DB, xử lý nghiệp vụ phức tạp |

---

## 🌐 2. Quy tắc Routing & Điều hướng

- Dự án dùng **Single Entry Point** qua `index.php`.
- **TUYỆT ĐỐI KHÔNG** liên kết trực tiếp đến file `.php` trong `view/` hoặc `control/`.

```php
// ✅ ĐÚNG
<a href="index.php?page=giohang">Giỏ hàng</a>
<a href="index.php?page=chitietsanpham&id=5">Xem sản phẩm</a>

// ❌ SAI
<a href="view/client/giohang.php">Giỏ hàng</a>
```

### Thêm trang mới:
1. Đăng ký tên trang vào mảng `$clientPages` trong `index.php`
2. Tạo file view tương ứng tại `view/client/<tênTrang>.php`
3. Nếu cần xử lý action riêng: thêm `elseif ($action === '...')` vào `index.php`

---

## 🗄️ 3. Quy tắc Cơ sở dữ liệu (Database)

- **Thư viện:** Sử dụng `mysqli` (không dùng PDO).
- **Biến kết nối:** `$conn` được khởi tạo từ `model/database.php`.
- **Charset:** Luôn dùng `utf8mb4`.

### Kiểm tra kết nối bắt buộc:

```php
// Mọi hàm trong Model đều phải kiểm tra $conn trước khi truy vấn
if ($conn === false) {
    return []; // hoặc return false / null tùy ngữ cảnh
}
```

### Bảo mật truy vấn:

```php
// ✅ ĐÚNG — Dùng Prepared Statement
$stmt = $conn->prepare("SELECT * FROM tai_khoan WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();

// ❌ SAI — Nối chuỗi trực tiếp (SQL Injection)
$query = "SELECT * FROM tai_khoan WHERE username = '$username'";
```

> [!CAUTION]
> **Tuyệt đối không** nối biến người dùng nhập vào trực tiếp trong chuỗi SQL.
> Luôn dùng **Prepared Statement** với `bind_param()`.

---

## 🎨 4. Quy tắc Giao diện & CSS

- **TUYỆT ĐỐI KHÔNG** sử dụng **Tailwind CSS** — đã bị loại bỏ hoàn toàn khỏi dự án.
- **CHỈ ĐƯỢC PHÉP** viết CSS trong các file `.css` thuộc thư mục `assets/css/`. Nghiêm cấm hoàn toàn việc viết CSS nội tuyến (inline style attribute `style="..."`) hoặc CSS nội bộ (internal stylesheet `<style>...</style>`) trực tiếp trong các file HTML/PHP (View).
- **Sử dụng Vanilla CSS** tự viết, phân chia theo file:

| File | Mục đích |
|------|----------|
| `assets/css/base.css` | Biến CSS, màu sắc, typography, reset cơ bản |
| `assets/css/client.css` | Toàn bộ CSS trang khách hàng |
| `assets/css/admin.css` | Toàn bộ CSS trang quản trị |
| `assets/css/responsive.css` | Media queries cho responsive |

### Quy tắc đặt tên class (BEM):

```css
/* Block */
.product-card { }

/* Element */
.product-card__title { }
.product-card__image { }

/* Modifier */
.product-card--featured { }
.product-card--out-of-stock { }
```

### Sử dụng biến CSS:

```css
/* ✅ ĐÚNG — Dùng biến đã khai báo trong base.css */
color: var(--color-primary);
font-family: var(--font-main);

/* ❌ SAI — Hardcode giá trị màu */
color: #e63946;
```

> [!IMPORTANT]
> Luôn định nghĩa màu sắc, font, spacing trong `base.css` trước, sau đó dùng biến CSS trong các file khác.

---

## ⚙️ 5. Quy tắc JavaScript

- Mỗi trang có file JS riêng tương ứng trong `assets/js/`.
- Giao tiếp với server qua **AJAX (Fetch API)**, truyền `action` và `page` qua URL parameter.
- Toàn bộ state người dùng đăng nhập được lưu song song:
  - **PHP Session** (phía server, là nguồn chân lý)
  - **localStorage** key `currentUser` (phía client, cho JS đọc nhanh)

```javascript
// ✅ Cách đọc thông tin user phía JS
const user = JSON.parse(localStorage.getItem('currentUser') || 'null');

// ✅ Cách gọi AJAX đúng chuẩn
fetch('index.php?action=get_orders', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ ... })
})
.then(res => res.json())
.then(data => { ... });
```

> [!WARNING]
> Không bao giờ dùng localStorage làm nguồn kiểm tra phân quyền bảo mật.
> Mọi kiểm tra quyền truy cập **bắt buộc** thực hiện phía PHP (`$_SESSION`).

---

## 🔐 6. Quy tắc Xác thực & Phân quyền (Auth)

### Session keys chuẩn:

| Key | Giá trị | Mô tả |
|-----|---------|-------|
| `$_SESSION['client_logged_in']` | `true` / không tồn tại | Đã đăng nhập phía client |
| `$_SESSION['client_username']` | string | Tên đăng nhập |
| `$_SESSION['client_fullname']` | string | Họ tên đầy đủ |
| `$_SESSION['client_role']` | `'user'` / `'admin'` | Vai trò người dùng |
| `$_SESSION['client_email']` | string | Email |
| `$_SESSION['client_phone']` | string | Số điện thoại |
| `$_SESSION['admin_logged_in']` | `true` / không tồn tại | Đã đăng nhập Admin |

### Kiểm tra phân quyền trong Controller/View:

```php
// Kiểm tra đăng nhập
if (!isset($_SESSION['client_logged_in']) || !$_SESSION['client_logged_in']) {
    header('Location: index.php?page=login');
    exit;
}

// Kiểm tra quyền Admin
if ($_SESSION['client_role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}
```

---

## 📤 7. Quy tắc Upload File

- Ảnh sản phẩm: lưu vào `uploads/products/`
- Ảnh đại diện: lưu vào `uploads/avatars/`
- **Bắt buộc kiểm tra** phần mở rộng file và kiểu MIME trước khi lưu.
- **Không tin tưởng** tên file gốc do người dùng cung cấp — đổi tên file khi lưu.

```php
// ✅ Đổi tên file để tránh trùng và injection
$extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$newName = uniqid('img_', true) . '.' . strtolower($extension);
$allowedTypes = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
if (!in_array(strtolower($extension), $allowedTypes)) {
    // Từ chối upload
}
```

---

## 🔁 8. Quy trình thêm tính năng mới (MVC Workflow)

Khi thêm tính năng mới (ví dụ: đánh giá sản phẩm, voucher, bài viết...):

```
Bước 1 → Model   : Tạo/cập nhật file trong model/ (viết hàm truy vấn SQL)
Bước 2 → Controller: Tạo/cập nhật file trong control/ (logic, validate, phân quyền)
Bước 3 → View    : Tạo giao diện trong view/client/ hoặc view/admin/
Bước 4 → Router  : Đăng ký trang/action mới trong index.php
Bước 5 → JS/CSS  : Thêm file JS riêng nếu cần, cập nhật CSS tương ứng
```

---

## ✍️ 9. Quy tắc đặt tên

### PHP — Classes:
```php
// PascalCase cho tên class
class ProductController { }
class UserModel { }
```

### PHP — Functions/Methods:
```php
// camelCase cho hàm
public static function getProductById($conn, $id) { }
public static function handleCheckout() { }
```

### PHP — Variables:
```php
// camelCase cho biến
$productList = [];
$currentUser = '';
// snake_case cho biến ánh xạ trực tiếp từ cột CSDL
$ho_ten = $row['ho_ten'];
```

### Files:
```
Model:       ProductModel.php, UserModel.php      (PascalCase + Model)
Controller:  ProductController.php                (PascalCase + Controller)
View:        trangchu.php, chitietsanpham.php     (lowercase, tiếng Việt không dấu)
JS:          mayanh.js, giohang.js                (khớp tên trang view)
CSS Partial: _navbar.php, _footer.php             (prefix _ cho partial/layout)
```

---

## 💬 10. Quy tắc Comment Code

```php
/**
 * Lấy thông tin sản phẩm theo ID.
 *
 * @param mysqli $conn  Kết nối CSDL
 * @param int    $id    ID sản phẩm
 * @return array|null   Mảng dữ liệu sản phẩm hoặc null nếu không tìm thấy
 */
public static function getProductById($conn, $id) {
    // Kiểm tra kết nối trước khi truy vấn
    if ($conn === false) return null;
    // ...
}
```

- Comment bằng **tiếng Việt** cho logic nghiệp vụ, bằng **tiếng Anh** cho docblock.
- **Không comment code thừa** (code đã bị xóa thì xóa hẳn, không để lại dưới dạng comment).

---

## 🚫 11. Những điều TUYỆT ĐỐI KHÔNG làm

| ❌ Không làm | ✅ Thay thế đúng |
|-------------|----------------|
| Dùng Tailwind CSS | Vanilla CSS trong `assets/css/` |
| Viết CSS nội tuyến (`style="..."`) hoặc dùng thẻ `<style>` trực tiếp trong file view/HTML/PHP | Viết CSS tách biệt trong các file `.css` thuộc thư mục `assets/css/` |
| Link thẳng đến `view/client/*.php` | Link qua `index.php?page=...` |
| Truy vấn SQL trực tiếp trong View | Gọi hàm từ Model qua Controller |
| Nối chuỗi SQL với biến user input | Dùng Prepared Statement |
| Dùng localStorage để kiểm tra quyền | Kiểm tra `$_SESSION` phía PHP |
| Để lộ lỗi DB ra màn hình người dùng | Bắt lỗi, log nội bộ, hiện thông báo chung |
| Hardcode URL tuyệt đối trong code | Dùng hằng số từ `config.php` |

---

## 📝 12. Quy tắc Git & Version Control

- **Commit message** phải rõ ràng, ngắn gọn, dùng tiếng Việt hoặc tiếng Anh nhất quán.
- Định dạng gợi ý: `[type]: mô tả ngắn`

```
feat: thêm tính năng lọc sản phẩm theo giá
fix: sửa lỗi không lưu được giỏ hàng khi chưa đăng nhập
style: cập nhật responsive cho trang mobile
refactor: tách logic voucher ra VoucherController riêng
docs: cập nhật README hướng dẫn cài đặt
```

- **Không commit** thông tin nhạy cảm: mật khẩu DB, API key, email credentials.
- File `config.php` chứa thông tin cấu hình thực — cân nhắc thêm vào `.gitignore` với template mẫu.

---

*Cập nhật lần cuối: 2026-05-30 | Dự án: webmayanh*
