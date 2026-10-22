# Phần việc Lê Đăng Huy — Database & Model

Repository: https://github.com/phdinh254/nhom1_codeigniter_sach

Nhánh: `feature/database-model`.

Theo ảnh phân công: thiết kế CSDL `quan_ly_sach`, migration bảng `books`, `users`, tạo `BookModel`, `UserModel`, kiểm tra trên phpMyAdmin và chụp ảnh minh chứng.

## 1. Chuẩn bị và mở đúng dự án trong VS Code

Dự án đang dùng CodeIgniter 4.7, yêu cầu PHP **8.2 trở lên**. PHP 8.0 không chạy được dự án này. Cài PHP 8.2+ (có thể dùng XAMPP bản PHP 8.2+) và Composer. Bật `intl`, `mbstring`, `mysqli`; để chạy PHPUnit cần thêm `sqlite3` và `dom`. Sau khi cài, mở lại VS Code để terminal nhận PATH mới.

Trong VS Code chọn **Terminal > New Terminal**, chạy:

```powershell
php -v
composer --version
git --version
```

Nếu dùng bản tải ZIP trong ảnh, thư mục đó có thể không có `.git`. Hãy clone sang thư mục mới để Git quản lý lịch sử, không ghi đè thư mục ZIP đang có:

```powershell
git clone --branch feature/database-model --single-branch https://github.com/phdinh254/nhom1_codeigniter_sach.git nhom1_codeigniter_sach
cd nhom1_codeigniter_sach
code .
```

Nếu đã clone từ Git trước đó, vào đúng thư mục và kiểm tra `git status` trước khi đổi nhánh; lưu thay đổi đang làm rồi chạy `git switch feature/database-model` và `git pull --ff-only origin feature/database-model`.

Trong cửa sổ VS Code vừa mở, kiểm tra:

```powershell
git branch --show-current
composer install
```

Tên nhánh phải là `feature/database-model`. Dùng `composer install` để giữ đúng phiên bản trong `composer.lock`.

## 2. Tạo database trên phpMyAdmin

Mở XAMPP Control Panel, Start **Apache** và **MySQL**, rồi vào http://localhost/phpmyadmin.

Chọn tab **SQL**, dán và chạy nội dung `database/create_database.sql`:

```sql
CREATE DATABASE IF NOT EXISTS quan_ly_sach
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Không tạo thủ công hai bảng: bước migration sẽ tạo chúng và lưu lịch sử vào bảng `migrations`.

## 3. Cấu hình kết nối

Trong terminal VS Code, nếu chưa có `.env`, chạy:

```powershell
Copy-Item .env.example .env
```

Nếu đã có `.env`, chỉnh các giá trị kết nối trong tệp đó thay vì chép đè. Cấu hình mẫu dùng database `quan_ly_sach`, tài khoản MySQL `root`, mật khẩu trống, cổng `3306`. Thay theo máy thực tế nếu đã đặt mật khẩu hoặc đổi cổng. `.env` được Git bỏ qua; không commit thông tin kết nối riêng.

## 4. Chạy migration và thêm dữ liệu mẫu

```powershell
php spark migrate
php spark migrate:status
php spark db:seed DemoSeeder
```

Kết quả cần kiểm tra: migration báo hoàn tất; các migration đã chạy; phpMyAdmin có ba bảng `books`, `users`, `migrations`. Bảng `books` có ba sách mẫu, bảng `users` có tài khoản thường `huy_demo`.

Sau khi tích hợp nhánh Authentication, chạy `php spark migrate` cả với database đã có dữ liệu. Hai migration tạo users sẽ bỏ qua bảng đã tồn tại; migration `AlignUsersForAuthentication` thống nhất cấu trúc. Nếu bảng cũ có cột `password` chứa hash, migration đổi tên thành `password_hash` và giữ nguyên hash cùng tài khoản. Migration cũng bổ sung `is_active`, `last_login_at` và thống nhất độ dài tên/email, chỉ mục email. Không cần xóa database hoặc chạy refresh. Nếu dữ liệu email cũ bị trùng, cần xử lý trùng trước khi tạo chỉ mục UNIQUE.

Migration thống nhất cấu trúc có `down()` giữ nguyên các cột đã bổ sung để bảo toàn dữ liệu và khả năng đăng nhập; rollback toàn bộ vẫn xóa bảng qua các migration tạo bảng trước đó.

`DemoSeeder` chỉ dành cho bài demo cục bộ. Mật khẩu mẫu công khai là `DemoHuy@2026`, được băm trước khi lưu; không dùng tài khoản này trên hệ thống công khai. Chạy lại seeder sẽ bỏ qua dữ liệu mẫu đã tồn tại, không ghi đè dữ liệu đã sửa.

## 5. Các tệp và cấu trúc bàn giao

| Tệp | Công việc |
| --- | --- |
| `app/Database/Migrations/2026-10-22-000001_CreateBooks.php` | Tạo/xóa bảng books qua up/down |
| `app/Database/Migrations/2026-10-22-000002_CreateUsers.php` | Tạo/xóa bảng users, UNIQUE username và email |
| `app/Database/Migrations/2026-10-22-000001_CreateUsersTable.php` | Migration users của nhánh Authentication; không tạo lại bảng đã có |
| `app/Database/Migrations/2026-10-22-000003_AlignUsersForAuthentication.php` | Thống nhất schema users và giữ dữ liệu từ cả hai nhánh |
| `app/Models/BookModel.php` | CRUD sách, validation, timestamps |
| `app/Models/UserModel.php` | CRUD người dùng, validation, băm mật khẩu |
| `app/Database/Seeds/BookSeeder.php`, `DemoSeeder.php` | Dữ liệu mẫu để kiểm tra |
| `tests/database/LibraryModelsTest.php` | Kiểm tra CRUD, dữ liệu sai, trùng tài khoản và mật khẩu |

### Bảng books

| Cột | Kiểu | Ý nghĩa |
| --- | --- | --- |
| id | INT UNSIGNED, PK, tự tăng | Mã sách |
| title | VARCHAR(255) | Tên sách, bắt buộc |
| author | VARCHAR(150) | Tác giả, bắt buộc |
| publisher | VARCHAR(150), NULL | Nhà xuất bản |
| published_year | SMALLINT UNSIGNED, NULL | Năm xuất bản |
| price | DECIMAL(10,2), mặc định 0 | Giá, model nhận từ 0 đến 99999999.99 |
| quantity | INT UNSIGNED, mặc định 0 | Số lượng nguyên không âm |
| description | TEXT, NULL | Mô tả |
| created_at, updated_at | DATETIME, NULL | Model tự ghi thời gian |

### Bảng users

| Cột | Kiểu | Ý nghĩa |
| --- | --- | --- |
| id | INT UNSIGNED, PK, tự tăng | Mã người dùng |
| username | VARCHAR(50), UNIQUE | Tên đăng nhập |
| full_name | VARCHAR(150) | Họ tên |
| email | VARCHAR(191), UNIQUE, NULL | Email tùy chọn; khớp tài khoản bị khóa của nhóm |
| password_hash | VARCHAR(255) | Chuỗi băm mật khẩu; không lưu mật khẩu gốc |
| role | VARCHAR(20), mặc định user | Vai trò, chỉ cập nhật qua chức năng đã kiểm tra quyền |
| is_active | TINYINT, mặc định 1 | 1: hoạt động; 0: bị khóa |
| last_login_at | DATETIME, NULL | Lần đăng nhập gần nhất |
| created_at, updated_at | DATETIME, NULL | Model tự ghi thời gian |

Ảnh phân công không quy định chi tiết tên cột. Cấu trúc trên là thiết kế cho nhóm dùng chung; thành viên làm controller/view cần thống nhất tên trường này. Hai bảng độc lập, chưa có nghiệp vụ yêu cầu khóa ngoại giữa chúng.

### Cách thành viên khác dùng model

```php
$books = new \App\Models\BookModel();
$id = $books->insert([
    'title' => 'Sách mới', 'author' => 'Tác giả',
    'price' => '80000.00', 'quantity' => 5,
]);
if ($id === false) {
    $errors = $books->errors();
}
$list = $books->findAll();
$detail = $books->find($id);
$books->update($id, ['quantity' => 4]);
// $books->delete($id); // Xóa thật khi người dùng xác nhận trong giao diện.

$users = new \App\Models\UserModel();
// insert/update nhận mật khẩu gốc; không password_hash trước khi gọi model.
// Cập nhật username/email giữ nguyên: gửi kèm id để loại chính dòng đó khỏi kiểm tra trùng.
$users->update($userId, ['id' => $userId, 'email' => $newEmail]);
// Khi đổi hồ sơ mà không đổi mật khẩu: bỏ hẳn khóa password khỏi dữ liệu update.
// Đăng nhập: $users->verifyCredentials($username, $passwordNhap).
// Auth::attemptLogin tiếp tục kiểm tra is_active và cập nhật last_login_at.
```

Controller cần kiểm tra kết quả `insert/update` và hiển thị `errors()` khi thất bại; chỉ lấy các trường cho phép từ request. Không lấy role hoặc is_active trực tiếp từ form đăng ký; các trường này phục vụ seeder và chức năng quản trị đã kiểm tra quyền. Khi trả dữ liệu người dùng ra giao diện/API, loại trường `password_hash`. Các hàm findByUsername, verifyCredentials của nhóm được giữ để tích hợp đăng nhập và phân quyền.

## 6. Kiểm tra và chụp ảnh minh chứng

Chạy kiểm thử riêng phần database/model:

```powershell
php vendor/bin/phpunit tests/database/LibraryModelsTest.php
```

Mặc định test dùng SQLite trong RAM với tiền tố `db_`, không tác động database `quan_ly_sach`. Cần bật extension `sqlite3` trong PHP CLI.

Chuẩn bị các ảnh sau bằng Snipping Tool (`Windows + Shift + S`):

1. phpMyAdmin chọn `quan_ly_sach`, nhìn thấy `books`, `users`, `migrations`.
2. Bảng `books` tab **Structure** và **Browse**, thấy cấu trúc và ba sách mẫu.
3. Bảng `users` tab **Structure** và **Browse**, thấy dữ liệu mẫu và cột password_hash dạng hash, is_active, last_login_at.
4. Terminal VS Code có `php spark migrate` báo thành công và `php spark migrate:status` (bao gồm các migration tích hợp của nhóm).
5. Nếu chạy được test, chụp kết quả PHPUnit để bổ sung minh chứng.

Ảnh phải chụp từ lần chạy thật trên máy, không dùng ảnh dựng. Hướng dẫn này không thay cho minh chứng đã chạy thành công.

## 7. Commit và push bằng VS Code

Code đã có trên nhánh thì clone/pull là nhận được các tệp. Với lần Huy sửa tiếp, dùng **Source Control** để xem từng thay đổi rồi stage đúng tệp. Hoặc terminal:

```powershell
git status
git diff
git add app/Database/Migrations app/Database/Seeds app/Models tests/database/LibraryModelsTest.php database/create_database.sql docs/HUONG_DAN_LE_DANG_HUY.md .env.example
git diff --cached --stat
git commit -m "feat(database): add books/users migrations and models"
git push origin feature/database-model
```

Nếu Git chưa có danh tính, đặt trong riêng repository bằng tên và email GitHub đã xác minh của Huy. Khi đăng nhập GitHub, dùng trình duyệt hoặc Git Credential Manager; không dán token vào code hay URL.

Nếu push bị từ chối do có commit mới từ bạn cùng nhóm: commit công việc đang làm, chạy `git pull --rebase origin feature/database-model`, xử lý xung đột nếu có, kiểm thử lại rồi push. Không force push.

Sau khi push, mở nhánh trên GitHub để kiểm tra commit. Chỉ tạo/merge pull request vào `main` theo quy ước nhóm.

## 8. Lỗi thường gặp

- **PHP version must be 8.2 or higher**: chạy `where.exe php`, chọn PHP 8.2+ trong PATH rồi mở terminal mới.
- **intl missing**: mở đúng `php.ini` từ `php --ini`, bật `extension=intl`.
- **composer not recognized**: cài Composer với PHP 8.2+ và mở terminal mới.
- **Unable to connect / Access denied**: Start MySQL, kiểm tra host/cổng/tài khoản trong `.env`.
- **Unknown database quan_ly_sach**: chạy SQL ở bước 2 trước migration.
- **Nothing to migrate**: chạy `php spark migrate:status`; nếu các migration có trạng thái đã chạy thì bình thường.
- **not a git repository**: đang mở bản ZIP hoặc thư mục cha; dùng bản clone ở bước 1.

Không chạy `migrate:rollback`, `migrate:refresh` trên database có dữ liệu cần giữ: rollback các migration tạo bảng sẽ xóa bảng tương ứng.
