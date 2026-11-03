# Kết quả kiểm tra mã nguồn

Đã kiểm tra tĩnh bằng script:
- Lexer PHP không phát hiện token lỗi ở các file PHP (không thay thế php -l).
- Các file include/require dùng __DIR__ đều tồn tại.
- Có đủ bộ xử lý cho 11 thao tác POST.
- 6 ảnh SVG parse XML thành công.
- Có kiểm tra token Session tồn tại trước so khớp CSRF; lọc lịch sử đơn theo user_id; khóa hàng cho đặt/hủy đơn.
- File ZIP được kiểm tra CRC sau khi đóng gói.

Môi trường tạo bộ code không có PHP hoặc MySQL; chưa chạy php -l và chưa kiểm thử chức năng thực tế trên XAMPP. Hãy thực hiện các kịch bản trong README.md trước khi nộp bài. Không coi các kiểm tra tĩnh này là bảo đảm web không có lỗi runtime.
