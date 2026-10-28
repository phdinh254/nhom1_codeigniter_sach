# Validation server và tên độc giả — Lê Đăng Huy

## Quy tắc

| Trường | Ràng buộc server |
| --- | --- |
| Tên sách | Bắt buộc là chuỗi có nội dung, tối đa 255 ký tự, không có ký tự điều khiển |
| Tác giả | Bắt buộc là chuỗi có nội dung, tối đa 150 ký tự |
| Nhà xuất bản | Có thể bỏ trống; tối đa 150 ký tự khi nhập |
| Năm xuất bản | Có thể bỏ trống; khi nhập phải là số nguyên 4 chữ số từ 1000 đến năm hiện tại theo giờ Việt Nam |
| Giá | Số nguyên hoặc chuỗi thập phân chính xác từ 0 đến 99999999.99; tối đa 2 số sau dấu chấm; không nhận dạng số mũ, số âm, boolean hoặc mảng |
| Số lượng | Số nguyên từ 0 đến 4294967295; không nhận số lẻ, boolean hoặc mảng |
| Mô tả | Có thể bỏ trống, tối đa 10000 ký tự; cho phép xuống dòng nhưng chặn ký tự NUL và ký tự điều khiển không hợp lệ |
| Tên đăng nhập | 3–50 ký tự ASCII, bắt đầu bằng chữ/số; các ký tự còn lại gồm chữ, số, dấu chấm, gạch dưới hoặc gạch ngang |
| Họ tên | Bắt buộc có nội dung, tối đa 150 ký tự, không chỉ là khoảng trắng |
| Vai trò | Nếu truyền vào phải là user hoặc admin; trường bị bỏ qua dùng giá trị mặc định của database |
| Trạng thái | Nếu truyền vào chỉ chấp nhận số/chữ số 0 hoặc 1 |

Các quy tắc sách được áp dụng trong BookModel ở phía server. Sửa max trong HTML hay gửi POST trực tiếp vẫn không vượt được giới hạn năm. Giới hạn HTML dùng cùng hàm xác định năm; qua năm mới không phải sửa code.

Controller sách chỉ lấy các trường sách cho phép; không ép mảng thành chuỗi. Các ô tùy chọn bỏ trống được lưu NULL thay vì năm 0. Giá và số lượng bằng 0 vẫn hợp lệ; không tự thêm quy tắc nghiệp vụ bắt chúng lớn hơn 0.

## Đổi tên trong dữ liệu hiện có

Migration RenameSampleUserToReader chỉ đổi full_name của tài khoản mẫu nhanvien từ đúng tên cũ “Nhân viên bán sách” thành “Độc giả”. Không đổi username, mật khẩu, email, ID, vai trò hay trạng thái. Tên đã được người dùng tùy chỉnh được giữ nguyên. Seeder cũng dùng tên mới cho database tạo mới.

Đăng nhập bằng tên cũ nhanvien vẫn hoạt động. role=user tiếp tục là mã nội bộ cho tài khoản thường; không tạo mã role mới làm sai các filter phân quyền.

## Áp dụng

Sau khi Pull Request được merge vào main:

~~~powershell
git pull --ff-only origin main
composer install
php spark migrate
php spark db:seed UserSeeder
php vendor/bin/phpunit tests/database
~~~

Chạy migration trước seeder để cập nhật tên trên database đang có. Chỉ sửa seeder sẽ không đổi tên tài khoản đã tồn tại. Đăng xuất/đăng nhập lại để session nhận họ tên mới. Không cần xóa database hoặc chạy migrate:refresh.

## Kiểm tra thủ công

1. Đăng nhập tài khoản quản trị; mở form thêm và sửa sách.
2. Nhập năm hiện tại + 1: lưu phải bị từ chối và hiển thị lỗi.
3. Nhập năm hiện tại: lưu hợp lệ; để trống năm: lưu NULL.
4. Thử tên chỉ có khoảng trắng, giá âm, giá có 3 số thập phân, số lượng lẻ: phải bị từ chối.
5. Kiểm tra users.full_name của tài khoản mẫu nhanvien là Độc giả; đăng nhập lại bằng mật khẩu cũ.
6. Kiểm tra tên tùy chỉnh của tài khoản khác không bị đổi.

## Kiểm thử tự động

LibraryValidationTest bổ sung kiểm tra giới hạn năm khi thêm/sửa, chuyển năm theo giờ Việt Nam, dữ liệu sai kiểu, giới hạn số, dữ liệu bắt buộc, email trống, migration bảo toàn tài khoản và seeder chạy lặp lại.
