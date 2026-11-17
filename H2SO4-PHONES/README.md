# H2SO4 PHONES — Website bán thiết bị công nghệ

**Đồ án mẫu PHP Core + MySQL + XAMPP**, giao diện dark mode: đen than, xanh mint và tím dịu. Bố cục và trải nghiệm tham khảo website thương mại điện tử CellphoneS, **không phải bản sao mã nguồn / thương hiệu / dữ liệu CellphoneS**.

## 1. Yêu cầu môi trường

- Windows với **XAMPP** (Apache, MySQL/MariaDB, PHP 8.1+). Khuyên dùng PHP 8.2/8.3.
- PHP extensions: `pdo_mysql`, `mbstring`, `fileinfo`, `openssl` (thông thường đã có trong XAMPP).
- Trình duyệt Chrome, Edge hoặc Firefox.
- Không cần Composer, npm, framework hay Internet để dùng icon/sản phẩm demo; Google Fonts chỉ là tùy chọn (nếu không tải được sẽ dùng phông hệ thống).

## 2. Cài đặt từng bước (dễ làm)

**Bước 1 — Giải nén:**

Sao chép nguyên thư mục `H2SO4-PHONES` vào `C:\xampp\htdocs\H2SO4-PHONES\`.

**Bước 2 — Bật server:**

Mở **XAMPP Control Panel**, nhấn **Start** cho **Apache** và **MySQL**.

**Bước 3 — Import database:**

1. Mở `http://localhost/phpmyadmin/`.
2. Chọn tab **Import / Nhập**.
3. Chọn file `H2SO4-PHONES/database/h2so4_phones.sql`.
4. Nhấn **Go / Thực hiện**. File SQL tự tạo database `h2so4_phones`, các bảng và 24 sản phẩm mẫu.

Chỉ import một lần vào database trống để tránh trùng khóa chính/slug.

**Bước 4 — Cấu hình kết nối:**

Mở `app/config.php`. Mặc định XAMPP thường sử dụng:

```php
const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'h2so4_phones';
const DB_USER = 'root';
const DB_PASS = '';
```

Nếu MySQL có mật khẩu hoặc chạy cổng khác, hãy chỉnh tại đây.

**Bước 5 — Tạo Admin lần đầu:**

Mở **`http://localhost/H2SO4-PHONES/setup.php`**, điền họ tên, email và **mật khẩu Admin do chính bạn đặt** (tối thiểu 10 ký tự). Sau khi tạo, hệ thống tự khóa trang tạo Admin khi đã tồn tại tài khoản Admin.

**Bước 6 — Vào website:**

| Địa chỉ | Chức năng |
|---|---|
| `http://localhost/H2SO4-PHONES/` | Trang chủ cửa hàng |
| `http://localhost/H2SO4-PHONES/catalog.php` | Danh mục và tìm kiếm |
| `http://localhost/H2SO4-PHONES/cart.php` | Giỏ hàng |
| `http://localhost/H2SO4-PHONES/login.php` | Đăng nhập |
| `http://localhost/H2SO4-PHONES/admin/` | Trang quản trị (cần đăng nhập Admin) |

Không có tài khoản Admin/mật khẩu mặc định. **Bạn tự tạo ở bước 5.**

## 3. Các chức năng đã cài đặt

**Khách hàng:** trang chủ, banner, danh mục, tìm kiếm, lọc thương hiệu và giá, sắp xếp, phân trang, chi tiết sản phẩm, thêm/cập nhật/xóa giỏ hàng với Session, đăng ký và đăng nhập, quản lý hồ sơ, đổi mật khẩu, danh sách yêu thích, checkout có thể không cần tài khoản, đặt hàng COD/chuyển khoản mô phỏng, lịch sử đơn hàng, tra cứu bằng mã đơn + số điện thoại.

**Admin:** dashboard số liệu và doanh thu đơn hoàn tất, quản lý sản phẩm (thêm/sửa/xóa, hình ảnh, tồn kho, giá, trạng thái, SKU), danh mục (thêm/sửa/xóa/ẩn), duyệt và đổi trạng thái đơn theo luồng, hoàn kho khi hủy, đánh dấu thanh toán, tra cứu/khóa tài khoản khách (không cho khóa Admin), quản lý banner (thêm/sửa/xóa/ẩn).

**Bảo mật cơ bản:** mật khẩu `password_hash()` / `password_verify()`, phân quyền `admin` / `customer`, PDO prepared statements chống SQL Injection, escape HTML chống XSS, CSRF token cho form thay đổi dữ liệu, session cookie HttpOnly/SameSite, đổi session ID sau login, kiểm MIME và kích thước upload, khóa dòng sản phẩm `FOR UPDATE` khi tạo đơn và transaction khi trừ/hoàn kho.

## 4. Cấu trúc thư mục

```
H2SO4-PHONES/
├── index.php                 Trang chủ
├── catalog.php               Tìm kiếm / danh mục / bộ lọc
├── product.php               Chi tiết sản phẩm
├── cart.php                  Giỏ hàng (Session)
├── checkout.php              Đặt hàng
├── success.php               Xác nhận đặt hàng
├── login.php, register.php   Đăng nhập, đăng ký
├── account.php, orders.php   Cá nhân và đơn hàng
├── order.php, lookup.php     Chi tiết / tra cứu đơn
├── wishlist.php, logout.php  Yêu thích / đăng xuất
├── setup.php                 Tạo Admin lần đầu
├── admin/                   Trang quản trị
│   ├── index.php            Dashboard
│   ├── products.php         Danh sách sản phẩm
│   ├── product_form.php     Thêm / sửa
│   ├── product_action.php   Ẩn / xóa
│   ├── categories.php, category_form.php, category_action.php
│   ├── orders.php, order_detail.php
│   ├── users.php
│   └── banners.php, banner_form.php, banner_action.php
├── app/
│   ├── config.php           Cấu hình DB
│   ├── bootstrap.php        PDO, CSRF, Session, hàm chung
│   └── partials/            Header, Footer, Product Card...
├── assets/
│   ├── css/                 CSS dark mode và Admin
│   ├── js/                  JavaScript giao diện + icon offline
│   └── images/products/     24 ảnh minh họa SVG gốc
├── uploads/products/        Hình ảnh sản phẩm upload từ Admin
└── database/
    └── h2so4_phones.sql     Cấu trúc + dữ liệu mẫu
```

## 5. Kiểm tra chức năng (gợi ý quay video nộp đồ án)

1. Mở trang chủ, danh mục **Điện thoại**, lọc thương hiệu và giá, dùng ô tìm kiếm.
2. Xem chi tiết một sản phẩm, thêm vào giỏ, tăng/giảm số lượng, cập nhật giỏ.
3. Checkout với họ tên, số điện thoại và địa chỉ; xác nhận đặt đơn.
4. Copy mã đơn, vào **Tra cứu đơn hàng**, nhập mã đơn và đúng số điện thoại.
5. Đăng nhập Admin, mở **Đơn hàng**, chuyển `Chờ xác nhận → Đã xác nhận → Đang giao → Hoàn tất`.
6. Admin thêm sản phẩm mới, upload ảnh, sửa giá / tồn kho, ẩn và xóa sản phẩm.
7. Admin thêm danh mục, chỉnh banner và xem dashboard.
8. Đăng ký khách mới, đăng nhập, lưu sản phẩm yêu thích và xem đơn hàng đã đặt bằng tài khoản.

## 6. Quan trọng: giới hạn của bản demo

- Giá bán, tồn kho, sản phẩm và các ảnh vẽ minh họa đều **là dữ liệu mẫu để học**, không phải thông tin thật của CellphoneS hay các hãng.
- **Không có tích hợp thanh toán ngân hàng, VNPay, MoMo hoặc gửi email/SMS thật**. Chọn “chuyển khoản” chỉ đánh dấu phương thức thanh toán trong DB. Admin xác nhận thanh toán bằng tay.
- Không tích hợp vận chuyển thật, tính VAT, hóa đơn điện tử, quản lý bảo hành, đánh giá người mua hay đồng bộ ERP.
- Nếu đưa website lên Internet cho khách hàng giao dịch thật, cần bổ sung HTTPS, xác minh email, chống brute-force, logging/audit, cấu hình secrets qua biến môi trường, backup, quản lý hoàn tiền và rà soát bảo mật chuyên sâu.
- Sau khi tạo Admin, nên **xóa file `setup.php`** khi triển khai ra môi trường công khai.

## 7. Nếu gặp lỗi

- **Không kết nối database:** kiểm tra MySQL đã Start chưa, đúng DB tên và tài khoản trong `app/config.php` chưa; extension `pdo_mysql` đã bật chưa.
- **HTTP 404:** đảm bảo tên thư mục chính xác là `H2SO4-PHONES` trong `htdocs` và Apache chạy đúng cổng. Nếu Apache chạy cổng 8080, sử dụng `http://localhost:8080/H2SO4-PHONES/`.
- **Ảnh upload không lưu:** cấp quyền ghi cho `uploads/products`.
- **Không tạo được Admin:** import SQL trước. Chỉ cho phép tạo lần đầu khi chưa tồn tại Admin.
- **Lỗi Session:** cho phép cookie trên localhost.
- **Không có icon hoặc font:** icon được bundle local. Font có fallback nên vẫn dùng được khi offline.

---
**Đồ án H2SO4 PHONES — PHP Core thuần, MySQL, XAMPP.**
