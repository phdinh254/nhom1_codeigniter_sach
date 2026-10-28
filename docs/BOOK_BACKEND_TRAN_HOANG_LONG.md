# Backend quản lý sách (Controller + Routes) — Trần Hoàng Long

Nhánh `feature/book-backend`. Commit:

- `b0e3cf8` — tạo `Book` controller và các route CRUD `/books`.
- `e61cf98` — sửa lỗi: thêm CSRF cho các route POST, lưu ô tùy chọn bỏ trống thành `NULL`, không chặn đăng nhập khi băm lại mật khẩu lỗi.

## Thành phần

| File | Vai trò |
|---|---|
| `app/Controllers/Book.php` | Nhận request, gọi `BookModel`, trả view hoặc chuyển trang kèm thông báo |
| `app/Config/Routes.php` (nhóm `books`) | Khai báo URL, phương thức HTTP và filter bảo vệ từng route |

Phần liên quan do thành viên khác làm: `BookModel` + migration (Database & Model), view `books/*` (Frontend), `AuthFilter`/`AdminFilter` (Authorization), quy tắc validation (Lê Đăng Huy).

## Routes

| Method | URL | Xử lý | Filter |
|---|---|---|---|
| GET | `/books` | `Book::index` — danh sách, tìm kiếm, phân trang | `auth` |
| GET | `/books/create` | `Book::create` — form thêm | `auth`, `admin` |
| POST | `/books/store` | `Book::store` — lưu sách mới | `auth`, `admin`, `csrf` |
| GET | `/books/edit/{id}` | `Book::edit` — form sửa | `auth`, `admin` |
| POST | `/books/update/{id}` | `Book::update` — lưu thay đổi | `auth`, `admin`, `csrf` |
| POST | `/books/delete/{id}` | `Book::delete` — xóa | `auth`, `admin`, `csrf` |

- Nhóm ngoài `books` gắn filter `auth`: chưa đăng nhập thì về `/login`.
- Nhóm con gắn filter `admin`: tài khoản `user` chỉ được xem danh sách, vào chức năng thêm/sửa/xóa sẽ bị chuyển sang `/no-access` (403).
- CodeIgniter 4.7 gộp filter của group và của route, nên route POST chạy đủ cả ba filter `auth` → `admin` → `csrf`.
- `{id}` dùng `(:num)` nên chỉ nhận số. Xóa và sửa bắt buộc dùng POST; gọi `GET /books/delete/1` trả 404, không xóa nhầm qua link.

## Luồng xử lý trong controller

1. **`index()`**: lấy `keyword` từ query string (chỉ nhận chuỗi, bỏ khoảng trắng hai đầu). Nếu có thì lọc `LIKE` theo tên sách. Sắp xếp sách mới nhất lên đầu, mỗi trang 10 cuốn bằng `paginate(10)`. View nhận `books`, `pager`, `keyword`.
2. **`create()` / `edit($id)`**: trả form. `edit` gọi `findOrFail($id)`, nếu không có sách thì ném `PageNotFoundException` → 404.
3. **`store()`**: `insert()` dữ liệu form. Model kiểm tra validation; sai thì quay lại form, giữ dữ liệu đã nhập (`withInput()`) và gửi lỗi qua flash `errors`. Đúng thì về `/books` với flash `success`.
4. **`update($id)`**: kiểm tra sách tồn tại, `update()`, xử lý lỗi/thành công giống `store()`.
5. **`delete($id)`**: kiểm tra tồn tại rồi xóa, về `/books` kèm thông báo.
6. **`bookData()`**: chỉ lấy 7 trường của sách từ POST (bỏ token CSRF và mọi trường lạ), bỏ khoảng trắng thừa. Các ô tùy chọn (`publisher`, `published_year`, `description`) để trống thì đổi thành `NULL`.

Controller không tự viết câu SQL; mọi truy vấn đi qua Query Builder của `BookModel`, nên dữ liệu được escape tự động.

## Bảo mật

| Nguy cơ | Cách xử lý |
|---|---|
| Chưa đăng nhập vào trang sách | Filter `auth` |
| User thường thêm/sửa/xóa sách | Filter `admin` + view ẩn nút thao tác |
| CSRF (trang khác lừa admin gửi form xóa/sửa) | Filter `csrf` trên các route POST; form có `csrf_field()` |
| SQL injection qua ô tìm kiếm | Query Builder `like()` tự escape |
| XSS qua tên sách | View in dữ liệu bằng `esc()` |
| Gửi thêm trường lạ (mass assignment) | `bookData()` whitelist + `$allowedFields` của model |
| ID không tồn tại | `findOrFail()` → 404 |

## Các lỗi đã sửa (commit `e61cf98`)

1. **Route POST của sách chưa kiểm tra CSRF.** Form có token nhưng route không bật filter `csrf`, nên token không được kiểm tra. Thử thực tế: một request POST không có token vẫn xóa được sách. Đã thêm `['filter' => 'csrf']` cho `store`, `update`, `delete`; giờ thiếu token sẽ trả 403.
2. **Để trống "Năm xuất bản" gây lỗi 500 trên MySQL.** Form gửi chuỗi rỗng `''` vào cột `SMALLINT`; MySQL/MariaDB ở strict mode (mặc định của XAMPP) báo *Incorrect integer value*. Đã đổi các ô tùy chọn bỏ trống thành `NULL`.
3. **Băm lại mật khẩu lỗi làm hỏng đăng nhập** (`UserModel::verifyCredentials`). Mật khẩu đúng nhưng bước rehash bị validation từ chối thì trước đây trả về "sai mật khẩu". Giờ rehash lỗi thì bỏ qua, vẫn cho đăng nhập.

## Kiểm thử

Chạy `php spark serve` trên database mới (migrate + seed), dùng script curl giả lập trình duyệt: 48/48 bước đạt.

- Đăng nhập: sai mật khẩu, tài khoản khóa, thiếu CSRF, quay lại đúng trang định vào sau khi đăng nhập.
- CRUD (admin): danh sách, tìm kiếm, thêm (cả trường hợp sai validation và bỏ trống ô tùy chọn), sửa, xóa, sửa ID không tồn tại → 404.
- Phân quyền: user không thấy nút Sửa/Xóa, gọi trực tiếp `create`/`update`/`delete` bị chuyển `/no-access`.
- Bảo mật: POST thiếu token CSRF → 403; tên sách `<script>` được escape; tìm kiếm `' OR 1=1 --` không lỗi và không trả toàn bộ sách; đăng ký kèm `role=admin` vẫn chỉ tạo tài khoản `user`.

## Chạy thử

```bash
composer install
cp .env.example .env     # sửa database.default.* cho đúng MySQL trên máy
php spark migrate
php spark db:seed UserSeeder
php spark db:seed BookSeeder
php spark serve          # mở http://localhost:8080/books
```

Tài khoản `admin` / `admin123` để thử thêm/sửa/xóa; `nhanvien` / `nhanvien123` để thử quyền chỉ xem.
