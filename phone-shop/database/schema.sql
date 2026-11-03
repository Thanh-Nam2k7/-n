CREATE DATABASE IF NOT EXISTS phone_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE phone_shop;
-- Import vào database mới; không xóa dữ liệu cũ khi chạy nhầm.
CREATE TABLE users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL, role ENUM('user','admin') NOT NULL DEFAULT 'user',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE categories (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL UNIQUE) ENGINE=InnoDB;
CREATE TABLE products (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, category_id INT UNSIGNED NOT NULL,
 name VARCHAR(190) NOT NULL, price BIGINT UNSIGNED NOT NULL,
 stock INT UNSIGNED NOT NULL DEFAULT 0, compatible VARCHAR(190) NOT NULL DEFAULT '',
 description TEXT NOT NULL,image VARCHAR(100) NOT NULL DEFAULT 'case.svg',active TINYINT NOT NULL DEFAULT 1,
 FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE TABLE orders (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id INT UNSIGNED NOT NULL,
 receiver VARCHAR(100) NOT NULL,phone VARCHAR(20) NOT NULL,address VARCHAR(500) NOT NULL,
 total BIGINT UNSIGNED NOT NULL,
 status ENUM('pending','confirmed','shipping','completed','cancelled') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE TABLE order_items (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,order_id INT UNSIGNED NOT NULL,product_id INT UNSIGNED NOT NULL,
 product_name VARCHAR(190) NOT NULL,price BIGINT UNSIGNED NOT NULL,quantity INT UNSIGNED NOT NULL,
 FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE RESTRICT,
 FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE TABLE login_attempts (email_key CHAR(64) PRIMARY KEY,attempts INT NOT NULL DEFAULT 0,window_start DATETIME NOT NULL) ENGINE=InnoDB;
INSERT INTO categories(name) VALUES ('Ốp lưng'),('Củ sạc'),('Cáp sạc'),('Tai nghe'),('Giá đỡ'),('Kính cường lực');
INSERT INTO products(category_id,name,price,stock,compatible,description,image) VALUES
(1,'Ốp lưng pastel xanh',79000,30,'iPhone 13','Ốp minh họa, màu xanh pastel. Mỗi dòng máy / màu được quản lý như một sản phẩm riêng.','case.svg'),
(1,'Ốp lưng trong suốt',69000,20,'Samsung A15','Ốp trong suốt, thiết kế gọn nhẹ. Thông tin và giá dùng để làm dữ liệu demo.','case.svg'),
(2,'Củ sạc USB-C 20W',159000,15,'Thiết bị hỗ trợ USB-C PD','Củ sạc minh họa. Kiểm tra chuẩn sạc của thiết bị trước khi mua.','charger.svg'),
(3,'Cáp USB-C 1 mét',59000,50,'Cổng USB-C','Cáp dài 1 mét, dùng cho dữ liệu minh họa đồ án.','cable.svg'),
(4,'Tai nghe có dây',99000,25,'Cổng 3.5 mm','Tai nghe có dây và micro. Thiết bị không có cổng 3.5 mm cần bộ chuyển đổi phù hợp.','audio.svg'),
(5,'Giá đỡ điện thoại để bàn',49000,40,'Điện thoại 4–7 inch','Giá đỡ để bàn, gọn nhẹ, dễ mang theo.','stand.svg'),
(6,'Kính cường lực trong suốt',39000,60,'iPhone 13','Kính bảo vệ màn hình. Chọn đúng dòng máy để vừa kích thước.','glass.svg'),
(3,'Cáp USB-C 2 mét',89000,18,'Cổng USB-C','Cáp dài 2 mét cho góc học tập. Giá và thông số minh họa.','cable.svg');
