# Chức năng Authentication (Đăng nhập / Đăng xuất)

Người thực hiện: Sơn Trung Nguyên — nhánh `feature/authentication`

## Thành phần

| File | Vai trò |
|---|---|
| `app/Controllers/Auth.php` | Hiển thị form login, xử lý đăng nhập, đăng xuất |
| `app/Controllers/Dashboard.php` | Trang thông tin tài khoản đang đăng nhập |
| `app/Filters/AuthFilter.php` | (phần Authorization) chặn trang cần đăng nhập, nhớ trang đang mở vào Session `redirect_url` |
| `app/Models/UserModel.php` | Tìm user theo username, `password_hash` khi thêm/sửa, `password_verify` khi đăng nhập |
| `app/Views/auth/login.php` | Giao diện đăng nhập (Bootstrap 5), hiển thị lỗi |
| `app/Views/dashboard.php` | Thông tin tài khoản (dùng layout chung) + nút xem danh sách sách |
| `app/Database/Migrations/2026-10-22-000001_CreateUsersTable.php` | Bảng `users` |
| `app/Database/Seeds/UserSeeder.php` | Tài khoản mẫu |

## Routes

| Method | URL | Xử lý |
|---|---|---|
| GET | `/login` | `Auth::login` (đã đăng nhập thì chuyển về `/books`) |
| POST | `/login` | `Auth::attemptLogin` (có CSRF) |
| POST | `/logout` | `Auth::logout` (có CSRF) |
| GET | `/dashboard` | `Dashboard::index` |

## Luồng xử lý

1. Kiểm tra dữ liệu nhập (không bỏ trống username/password).
2. Tìm tài khoản theo username, so khớp mật khẩu bằng `password_verify()`.
3. Sai username hoặc mật khẩu → báo chung "Tên đăng nhập hoặc mật khẩu không đúng." (không tiết lộ username có tồn tại hay không).
4. Tài khoản `is_active = 0` → báo "Tài khoản đã bị khóa".
5. Đúng → `session()->regenerate(true)` (chống session fixation), lưu `isLoggedIn`, `user_id`, `username`, `full_name`, `role` vào Session, cập nhật `last_login_at`.
6. Chuyển trang sau đăng nhập: nếu trước đó người dùng mở một trang cần đăng nhập (ví dụ `/books/create`) thì quay lại đúng trang đó (giữ cả query string); nếu vào thẳng `/login` thì về trang danh sách sách `/books`. Chỉ nhớ request GET; URL được ghép qua `site_url()` nên luôn nằm trong website.
7. Đăng xuất → xóa các khóa đăng nhập khỏi Session, cấp session ID mới, chuyển về `/login`.

Các khóa Session `isLoggedIn` và `role` có thể dùng lại cho phần Authorization (filter phân quyền).

## Chạy thử

```bash
composer install
cp env .env          # sửa database.default.* cho đúng MySQL trên máy
php spark migrate
php spark db:seed UserSeeder
php spark serve
```

Tài khoản mẫu:

| Username | Mật khẩu | Vai trò | Ghi chú |
|---|---|---|---|
| admin | admin123 | admin | |
| nhanvien | nhanvien123 | user | Họ tên hiển thị: Độc giả (giữ tên đăng nhập cũ) |
| taikhoankhoa | khoa123456 | user | Bị khóa, dùng để test thông báo lỗi |
