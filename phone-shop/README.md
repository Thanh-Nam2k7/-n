# PHONE CORNER — Đồ án bán phụ kiện điện thoại
PHP Core + PDO + MySQL/MariaDB + Bootstrap. Không dùng framework hay Composer.

## 1. Cài trên XAMPP (Windows)
1. Dùng XAMPP có PHP 8.0 trở lên, bật Apache và MySQL.
2. Giải nén, đặt thư mục `phone-shop` vào `C:\xampp\htdocs\phone-shop`.
3. Mở `http://localhost/phpmyadmin`, chọn Import, chọn `database/schema.sql`, bấm Go. Script tạo database `phone_shop`, 6 bảng và 8 sản phẩm. Chỉ import một lần vào database mới; không import đè dữ liệu đang dùng.
4. Mở `app/config.php`, sửa host/port/username/password nếu khác mặc định. Tài khoản root mật khẩu rỗng chỉ dùng cho XAMPP trên máy cá nhân.
5. Truy cập `http://localhost/phone-shop/`.
6. Đăng ký tài khoản của bạn. Mật khẩu tối thiểu 8, tối đa 72 byte (ký tự có dấu có thể chiếm nhiều byte).
7. Để cấp Admin: mở CMD trong thư mục dự án, chạy:
   ```bat
   C:\xampp\php\php.exe tools\promote-admin.php email-cua-ban@example.com
   ```
   Thay email bằng tài khoản vừa đăng ký. Hoặc vào phpMyAdmin → phone_shop → users → sửa `role` thành `admin` cho đúng tài khoản.
8. Tải lại trang và chọn **Quản trị**, hoặc mở `http://localhost/phone-shop/admin.php`.

Không có tài khoản hoặc mật khẩu Admin mặc định. Chức năng đăng ký luôn tạo User.
Nếu Apache dùng cổng 8080, thêm `:8080` sau localhost. Nếu MySQL dùng cổng khác, đổi port trong config.

## 2. Cấu trúc
- `index.php`: trang chủ, chi tiết, đăng ký / đăng nhập, giỏ, thanh toán, đơn của tôi.
- `admin.php`: thống kê, CRUD sản phẩm / danh mục, quản lý trạng thái đơn.
- `app/bootstrap.php`: PDO, Session, CSRF, escape HTML, kiểm tra quyền, hàm dùng chung.
- `app/actions.php`: xử lý POST, kiểm tra dữ liệu, nghiệp vụ mua hàng và quản trị.
- `app/header.php`, `app/footer.php`: giao diện dùng chung.
- `assets/`: CSS và 6 ảnh SVG minh họa do dự án cung cấp.
- `database/schema.sql`: tạo bảng và dữ liệu mẫu.
- `tools/promote-admin.php`: cấp quyền Admin từ terminal.

## 3. Quy trình nghiệp vụ
Khách có thể tìm sản phẩm và thêm giỏ; phải đăng nhập để đặt hàng. Giỏ lưu Session và được xóa khi đăng xuất. Đây là giỏ theo phiên trình duyệt, không đồng bộ nhiều thiết bị.
Mỗi màu / dòng máy là một sản phẩm riêng. Giá tính bằng số nguyên VND. Đơn hàng chỉ COD, miễn phí vận chuyển trong bản đồ án.
Đặt hàng dùng transaction và SELECT FOR UPDATE: đọc giá từ database, kiểm tra và trừ tồn kho, tạo đơn cùng chi tiết. Lỗi thì rollback toàn bộ. Session lock và chuyển hướng sau POST hạn chế đặt trùng khi tải lại.
Trạng thái: Chờ xác nhận → Đã xác nhận → Đang giao → Hoàn thành. Admin chỉ được hủy khi chờ xác nhận hoặc đã xác nhận; hủy hoàn tồn kho một lần trong transaction. Không hỗ trợ hoàn hàng sau khi giao trong bản này.
Doanh thu chỉ tính đơn hoàn thành. Đơn giữ tên và giá sản phẩm tại thời điểm mua; sửa sản phẩm không làm đổi lịch sử.
Sản phẩm đã có đơn hàng: nút Xóa chuyển sang ngừng bán. Sản phẩm chưa có đơn: xóa thật. Không xóa danh mục còn sản phẩm.

## 4. Bảo mật được triển khai
- password_hash / password_verify; không lưu mật khẩu gốc.
- PDO prepared statements, tắt emulate prepares; sắp xếp dùng danh sách cho phép.
- htmlspecialchars khi xuất dữ liệu; mô tả là văn bản thuần, không cho HTML.
- CSRF token cho mọi POST; logout / xóa dùng POST.
- Kiểm tra quyền server-side; truy vấn đơn của tôi lọc theo user_id.
- Session HttpOnly, SameSite=Lax, strict mode; đổi ID khi đăng nhập / đăng xuất; Secure khi dùng HTTPS.
- Giới hạn 10 lần thử đăng nhập / 15 phút theo email; hết thời gian tự đặt lại.
- Ràng buộc khóa ngoại; khóa hàng khi đặt / hủy đơn; kiểm tra giá, số lượng, tồn kho phía server.
- Admin chọn ảnh từ danh sách SVG cục bộ; chưa có upload ảnh. Có thể mở rộng upload sau với kiểm tra MIME, kích thước và tên file ngẫu nhiên.
- .htaccess ngăn truy cập thư mục cấu hình / SQL / công cụ trên Apache. Không dùng PHP development server để public dự án.

## 5. Giao diện và giới hạn
Bootstrap 5.3.3 tải qua CDN nên cần Internet để hiện đủ giao diện. Nếu muốn offline, tải bootstrap.min.css vào assets và đổi link trong app/header.php sang assets/bootstrap.min.css. Không cần Bootstrap JS vì dự án không dùng component JS.
Ảnh là minh họa, giá/thông số là dữ liệu demo. Không có thanh toán online, email, phân trang hay lưu giỏ database. Bản phù hợp học tập, cần cấu hình HTTPS và tài khoản DB riêng khi triển khai công khai.

## 6. Kịch bản kiểm thử trên XAMPP
1. Đăng ký email mới; thử email trùng, mật khẩu ngắn; đăng nhập đúng/sai và đăng xuất.
2. User mở admin.php phải bị chặn 403; đăng ký không được tự cấp admin.
3. Tìm bằng tên/dòng máy, lọc danh mục và khoảng giá, sắp xếp; mở chi tiết.
4. Thêm giỏ, đổi số lượng, xóa; thử số âm, số vượt kho và sản phẩm hết hàng.
5. Đặt hàng; kiểm tra orders/order_items và tồn kho bị trừ đúng, tổng giá đúng.
6. Tải lại trang sau đặt: không tạo đơn mới. Hết hàng trước khi đặt: không tạo đơn dở dang.
7. Tài khoản khác không xem được lịch sử đơn tài khoản đầu.
8. Admin thêm/sửa/xóa danh mục và sản phẩm; xóa danh mục có sản phẩm phải báo lỗi.
9. Chuyển trạng thái đúng thứ tự, hủy đơn; kiểm tra tồn kho hoàn đúng một lần. Đơn đã hoàn thành không hủy được.
10. Tên sản phẩm `<script>alert(1)</script>` phải hiển thị như chữ, không chạy JS; tìm `' OR 1=1 --` không gây lỗi SQL hoặc bỏ qua đăng nhập.
11. Xóa hoặc đổi token CSRF khi gửi POST phải bị chặn 403.
12. Kiểm tra điện thoại / cửa sổ hẹp; bảng quản trị cuộn ngang.

## 7. Lỗi thường gặp
- Có lỗi hệ thống: kiểm tra MySQL đang chạy, đã import đúng database và cấu hình PDO. Chi tiết lỗi nằm trong nhật ký PHP/Apache của XAMPP.
- Không đủ hàng: giảm số lượng hoặc xóa sản phẩm đã ngừng bán.
- Quá nhiều lần đăng nhập: chờ 15 phút. Khi thử nghiệm cục bộ có thể xóa dòng tương ứng trong login_attempts bằng phpMyAdmin.
- Không đổi được danh mục: tên trùng hoặc danh mục vẫn chứa sản phẩm.
