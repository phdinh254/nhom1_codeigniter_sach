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

Migration `RenameSampleUserToReader` đã phát hành chỉ đổi tên hiển thị. Migration tiếp theo `ConvertSampleAccountToReader` hoàn tất chuyển tài khoản mẫu cũ thành độc giả: username `docgia`, mật khẩu mẫu `docgia123` (chỉ lưu hash bcrypt), họ tên “Độc giả”, email `docgia@nhasach.local`, quyền `user`. ID, trạng thái khóa và các dữ liệu liên kết được giữ nguyên. Seeder tạo cùng tài khoản độc giả trên database mới.

Sau khi nâng cấp, đăng nhập bằng `docgia` / `docgia123`; thông tin đăng nhập cũ không còn dùng được. `role=user` là mã nội bộ cho độc giả, hiển thị “Độc giả” trên thanh điều hướng và trang tài khoản; filter vẫn chặn quyền quản trị. Migration chỉ chuyển tài khoản có username cũ `nhanvien` và tên mẫu “Nhân viên bán sách” hoặc “Độc giả”, giữ nguyên tài khoản đã có tên tùy chỉnh. Nếu username/email đích đã được tài khoản khác sử dụng, migration báo lỗi trước khi thay đổi dữ liệu. Chạy lại migration hoặc seeder không đặt lại mật khẩu tài khoản đã chuyển. Rollback migration mới giữ tài khoản độc giả vì không thể khôi phục mật khẩu trước đó.

## Áp dụng

Sau khi Pull Request được merge vào main:

~~~powershell
git pull --ff-only origin main
composer install
php spark migrate
php spark db:seed UserSeeder
php vendor/bin/phpunit tests/database
~~~

Chạy migration trước seeder để cập nhật tài khoản trên database đang có. Chỉ sửa seeder sẽ không chuyển tài khoản đã tồn tại. Đăng xuất/đăng nhập lại để session nhận tên đăng nhập, họ tên và quyền mới. Không cần xóa database hoặc chạy migrate:refresh.

## Kiểm tra thủ công

1. Đăng nhập tài khoản quản trị; mở form thêm và sửa sách.
2. Nhập năm hiện tại + 1: lưu phải bị từ chối và hiển thị lỗi.
3. Nhập năm hiện tại: lưu hợp lệ; để trống năm: lưu NULL.
4. Thử tên chỉ có khoảng trắng, giá âm, giá có 3 số thập phân, số lượng lẻ: phải bị từ chối.
5. Đăng nhập bằng `docgia` / `docgia123`; kiểm tra họ tên và vai trò hiển thị là Độc giả, email `docgia@nhasach.local`, không vào được trang quản trị. Tên đăng nhập cũ không còn hoạt động.
6. Kiểm tra tên tùy chỉnh của tài khoản khác không bị đổi.

## Kiểm thử tự động

LibraryValidationTest kiểm tra giới hạn năm khi thêm/sửa, chuyển năm theo giờ Việt Nam, dữ liệu sai kiểu, giới hạn số, dữ liệu bắt buộc, email trống và seeder chạy lặp lại. SampleReaderMigrationTest kiểm tra chuyển tài khoản cũ sau migration đã phát hành, bảo toàn ID/trạng thái/tên tùy chỉnh, mật khẩu mới được băm, xung đột username/email và chạy lại không đặt lại mật khẩu.
