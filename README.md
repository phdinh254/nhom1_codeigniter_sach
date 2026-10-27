# Quản lý sách với CodeIgniter 4 (Nhóm 1)

Ứng dụng quản lý sách được xây dựng bằng CodeIgniter 4, có đăng nhập, phân quyền Admin/User và các chức năng CRUD sách.

- **Repository:** [nhom1_codeigniter_sach](https://github.com/phdinh254/nhom1_codeigniter_sach)

## Công nghệ sử dụng

- CodeIgniter 4
- PHP 8.2+
- MySQL (quản lý bằng phpMyAdmin)
- XAMPP
- Composer
- Git/GitHub

## Yêu cầu môi trường

- PHP 8.2 trở lên
- PHP cần bật các extension: `intl`, `mbstring`, `json`, `mysqlnd`, `libcurl`
- Composer
- MySQL hoặc XAMPP
- Git

## Cài đặt và chạy

1. Clone repository và vào thư mục project:

   ```bash
   git clone https://github.com/phdinh254/nhom1_codeigniter_sach.git
   cd nhom1_codeigniter_sach
   ```

2. Cài đặt các dependency:

   ```bash
   composer install
   ```

3. Tạo file `.env` từ file mẫu `.env.example`:

   ```bash
   copy .env.example .env
   ```

   Trên macOS/Linux, dùng:

   ```bash
   cp .env.example .env
   ```

4. Mở XAMPP Control Panel, bật **Apache** và **MySQL**. Tạo database `quan_ly_sach` trong phpMyAdmin hoặc chạy file `database/create_database.sql` bằng tab SQL trong phpMyAdmin. Mở `.env` và kiểm tra cấu hình kết nối database, cập nhật tên đăng nhập hoặc mật khẩu nếu thiết lập MySQL của bạn khác:

   ```ini
   database.default.hostname = localhost
   database.default.database = quan_ly_sach
   database.default.username = root
   database.default.password = ''
   database.default.DBDriver = MySQLi
   ```

5. Chạy migration để tạo/cập nhật cấu trúc database:

   ```bash
   php spark migrate
   ```

6. Tạo tài khoản mẫu và dữ liệu sách:

   ```bash
   php spark db:seed UserSeeder
   php spark db:seed DemoSeeder
   ```

7. Khởi động server phát triển:

   ```bash
   php spark serve
   ```

   Mở [http://localhost:8080](http://localhost:8080) trên trình duyệt.

## Cơ sở dữ liệu

Phần Database & Model do **Lê Đăng Huy** phụ trách, gồm database `quan_ly_sach`, migration cho bảng `books` và `users`, cùng `BookModel` và `UserModel`. Yêu cầu PHP 8.2 trở lên; chạy `php spark migrate` để áp dụng migration.

Sau khi tích hợp Authentication, chạy `php spark migrate` để thống nhất các cột `users.password_hash`, `is_active` và `last_login_at`. Migration giữ lại tài khoản cùng mật khẩu đã băm từ database cũ, vì vậy không cần xóa database.

Xem [hướng dẫn Database & Model](docs/HUONG_DAN_LE_DANG_HUY.md).

## Cấu trúc thư mục chính

- `app/` — mã nguồn ứng dụng:
  - `app/Controllers/` — các controller xử lý yêu cầu, gồm xác thực, sách và dashboard.
  - `app/Models/` — model thao tác với dữ liệu sách và người dùng.
  - `app/Views/` — giao diện đăng nhập, sách, dashboard và các trang lỗi.
  - `app/Filters/` — filter xác thực và kiểm tra quyền Admin.
  - `app/Config/` — cấu hình ứng dụng, database, routes, filter và validation.
  - `app/Database/Migrations/` — migration tạo và cập nhật cấu trúc database.
  - `app/Database/Seeds/` — seeder dữ liệu sách và người dùng.
  - `app/Validation/` — các quy tắc validation tùy chỉnh.
  - `app/Language/` — thông báo validation theo ngôn ngữ.
- `database/` — hiện có `create_database.sql`, script SQL tạo database.
- `docs/` — tài liệu của project.
- `public/` — điểm vào ứng dụng và tài nguyên công khai.
- `tests/` — mã kiểm thử.
- `writable/` — thư mục lưu dữ liệu phát sinh khi ứng dụng chạy.

## Quy trình làm việc nhóm

- Mỗi thành viên phát triển trên nhánh riêng.
- Khi hoàn tất công việc, thành viên tạo Pull Request để đề xuất thay đổi.
- Trưởng nhóm review Pull Request và merge các thay đổi đã được duyệt vào nhánh chính.

## Thành viên nhóm

| STT | Thành viên | Vai trò |
| --- | --- | --- |
| 1 | Trần Phú Dinh | Trưởng nhóm / Tích hợp |
| 2 | Lê Đăng Huy | Database & Model |
| 3 | Sơn Trung Nguyên | Authentication |
| 4 | Phan Trần Trường Huy | Phân quyền |
| 5 | Trần Hoàng Long | CRUD Backend |
| 6 | Khưu Gia Bảo | CRUD Frontend |
| 7 | Từ Phan Thành Lập | Validation & Testing |
| 8 | Từ Gia Đạt | Tài liệu & README |

## Chức năng

- Đăng nhập bằng tài khoản và mật khẩu; tài khoản không hoạt động không thể đăng nhập.
- Đăng xuất.
- Xem dashboard tài khoản sau khi đăng nhập.
- Xem danh sách sách, tìm theo tên sách và phân trang.
- Admin thêm, xem chi tiết để sửa, cập nhật và xóa sách.
- Kiểm tra dữ liệu đầu vào cho đăng nhập, thông tin sách và thông tin người dùng.
- Phân quyền: người dùng đã đăng nhập xem danh sách sách; chỉ Admin được thực hiện các thao tác quản lý sách.

## Route và phân quyền

| Route | Phương thức HTTP | Controller::method | Quyền truy cập |
| --- | --- | --- | --- |
| `/` | GET | `Home::index` | công khai |
| `/login` | GET | `Auth::login` | công khai |
| `/login` | POST | `Auth::attemptLogin` | công khai |
| `/logout` | POST | `Auth::logout` | công khai |
| `/dashboard` | GET | `Dashboard::index` | cần đăng nhập |
| `/books` | GET | `Book::index` | cần đăng nhập |
| `/books/create` | GET | `Book::create` | chỉ Admin |
| `/books/store` | POST | `Book::store` | chỉ Admin |
| `/books/edit/{id}` | GET | `Book::edit($id)` | chỉ Admin |
| `/books/update/{id}` | POST | `Book::update($id)` | chỉ Admin |
| `/books/delete/{id}` | POST | `Book::delete($id)` | chỉ Admin |
| `/no-access` | GET | Closure (trả trang lỗi 403) | công khai |

Các route POST `/login`, `/logout`, `/books/store`, `/books/update/{id}` và `/books/delete/{id}` có filter CSRF. Các route sách được bảo vệ bởi `auth`; nhóm route thêm/sửa/xóa còn có filter `admin`.

## Tài khoản demo

| Username | Mật khẩu | Quyền / trạng thái |
| --- | --- | --- |
| `admin` | `admin123` | Admin, đang hoạt động |
| `nhanvien` | `nhanvien123` | User, đang hoạt động |
| `taikhoankhoa` | `khoa123456` | User, bị khóa |
| `huy_demo` | `DemoHuy@2026` | User (vai trò mặc định), đang hoạt động |

Chạy `php spark db:seed UserSeeder` để tạo ba tài khoản đầu. Chạy `php spark db:seed DemoSeeder` để thêm sách mẫu và tài khoản `huy_demo`. Đây là dữ liệu phục vụ demo/kiểm thử; không chạy seeder demo trên môi trường công khai.

## Ảnh minh họa

Đang cập nhật
