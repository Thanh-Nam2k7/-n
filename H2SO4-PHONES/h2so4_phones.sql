-- H2SO4 PHONES / PHP Core + MySQL 8 / utf8mb4
-- Tạo database và import trực tiếp qua phpMyAdmin > Import.
CREATE DATABASE IF NOT EXISTS h2so4_phones CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE h2so4_phones;

CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(180) NOT NULL UNIQUE,
 phone VARCHAR(20) NULL,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL, slug VARCHAR(130) NOT NULL UNIQUE,
 icon VARCHAR(32) NOT NULL DEFAULT 'smartphone', description VARCHAR(255) NULL,
 sort_order INT NOT NULL DEFAULT 0, is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 category_id INT UNSIGNED NOT NULL, name VARCHAR(200) NOT NULL, slug VARCHAR(220) NOT NULL UNIQUE,
 brand VARCHAR(100) NOT NULL, sku VARCHAR(70) NOT NULL UNIQUE,
 price BIGINT UNSIGNED NOT NULL, old_price BIGINT UNSIGNED NULL,
 stock INT UNSIGNED NOT NULL DEFAULT 0, image VARCHAR(250) NULL,
 short_description VARCHAR(450) NULL, description TEXT NULL, specs TEXT NULL,
 is_featured TINYINT(1) NOT NULL DEFAULT 0, is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
 INDEX idx_product_cat (category_id, is_active), INDEX idx_product_price (price), INDEX idx_product_brand (brand), FULLTEXT KEY ft_product_search (name, brand, description)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_code VARCHAR(24) NOT NULL UNIQUE,
 user_id INT UNSIGNED NULL, customer_name VARCHAR(120) NOT NULL,
 customer_email VARCHAR(180) NULL, customer_phone VARCHAR(20) NOT NULL,
 address VARCHAR(400) NOT NULL, note VARCHAR(600) NULL,
 subtotal BIGINT UNSIGNED NOT NULL, shipping_fee BIGINT UNSIGNED NOT NULL,
 total BIGINT UNSIGNED NOT NULL,
 payment_method ENUM('cod','bank_transfer') NOT NULL DEFAULT 'cod',
 payment_status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
 status ENUM('pending','confirmed','shipping','completed','cancelled') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_orders_user (user_id), INDEX idx_orders_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NULL,
 product_name VARCHAR(200) NOT NULL, product_sku VARCHAR(70) NOT NULL,
 price BIGINT UNSIGNED NOT NULL, quantity INT UNSIGNED NOT NULL,
 line_total BIGINT UNSIGNED NOT NULL,
 FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
 FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS wishlists (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL,
 product_id INT UNSIGNED NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
 UNIQUE KEY uq_wishlist (user_id, product_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS banners (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(120) NOT NULL, subtitle VARCHAR(255) NOT NULL,
 tag VARCHAR(70) NOT NULL DEFAULT 'DEAL HOT', button_text VARCHAR(55) NOT NULL DEFAULT 'Khám phá ngay',
 button_url VARCHAR(255) NOT NULL DEFAULT 'catalog.php',
 gradient VARCHAR(25) NOT NULL DEFAULT 'purple',
 is_active TINYINT(1) NOT NULL DEFAULT 1, sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO categories (id, name, slug, icon, description, sort_order) VALUES
(1,'Điện thoại','dien-thoai','smartphone','Smartphone phong cách và hiệu năng',1),
(2,'Laptop','laptop','laptop','Laptop học tập và làm việc',2),
(3,'Máy tính bảng','may-tinh-bang','tablet','Tablet giải trí và sáng tạo',3),
(4,'Tai nghe & Âm thanh','am-thanh','headphones','Âm thanh chuẩn chất riêng',4),
(5,'Đồng hồ thông minh','dong-ho','watch','Smartwatch năng động',5),
(6,'Phụ kiện','phu-kien','plug','Sạc, cáp, ốp lưng và phụ kiện',6),
(7,'Gaming','gaming','gamepad-2','Trang bị cho mọi trận đấu',7);

INSERT INTO banners (title, subtitle, tag, button_text, button_url, gradient, sort_order) VALUES
('Chạm tương lai. Bật cá tính.','Siêu phẩm công nghệ, mức giá dễ chịu. Ưu đãi dành riêng cho bạn.','H2SO4 DROP 2026','Mua sắm ngay','catalog.php','purple',1),
('Phụ kiện chuẩn gu, giá chuẩn yêu.','Sạc nhanh, tai nghe và phụ kiện thiết kế tinh tế cho mọi thiết bị.','ACCESSORY WEEK','Xem phụ kiện','catalog.php?category=phu-kien','mint',2),
('Gaming mode: ON','Âm thanh sống động và thiết bị chiến game mạnh mẽ.','GAME ON','Khám phá gaming','catalog.php?category=gaming','amber',3);

-- Sản phẩm mẫu (giá, tồn kho và mô tả chỉ phục vụ demo đồ án)
INSERT INTO products (category_id,name,slug,brand,sku,price,old_price,stock,image,short_description,description,specs,is_featured) VALUES
(1,'Astra 15 Pro 256GB','astra-15-pro','H2SO4','AST-15P-256',22990000,25990000,18,'assets/images/products/astra-15-pro.svg','Thân máy tinh xảo, màn hình sắc nét, hiệu năng mạnh.','Astra 15 Pro 256GB là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Thân máy tinh xảo, màn hình sắc nét, hiệu năng mạnh. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','6.7 inch OLED | 256GB | 5000 mAh',1),
(1,'Astra 15 128GB','astra-15','H2SO4','AST-15-128',15990000,18590000,24,'assets/images/products/astra-15.svg','Thiết kế mỏng nhẹ, camera đa dụng, dùng cả ngày.','Astra 15 128GB là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Thiết kế mỏng nhẹ, camera đa dụng, dùng cả ngày. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','6.1 inch OLED | 128GB | 4500 mAh',1),
(1,'Galaxy Neo Ultra 512GB','galaxy-neo-ultra','Samsung','GLX-NEO-512',27990000,30990000,11,'assets/images/products/galaxy-neo-ultra.svg','Màn hình rộng, hiệu năng cao, bút cảm ứng tiện lợi.','Galaxy Neo Ultra 512GB là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Màn hình rộng, hiệu năng cao, bút cảm ứng tiện lợi. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','6.8 inch AMOLED | 512GB | 5000 mAh',1),
(1,'Redmi Turbo 256GB','redmi-turbo','Xiaomi','RDM-TUR-256',7490000,8990000,31,'assets/images/products/redmi-turbo.svg','Sức mạnh bền bỉ, pin khỏe, sạc nhanh.','Redmi Turbo 256GB là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Sức mạnh bền bỉ, pin khỏe, sạc nhanh. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','6.67 inch AMOLED | 256GB | 5500 mAh',1),
(1,'Nova Flip X 256GB','nova-flip-x','H2SO4','NOV-FLP-256',18990000,21990000,12,'assets/images/products/nova-flip-x.svg','Gập mở linh hoạt, phong cách khác biệt.','Nova Flip X 256GB là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Gập mở linh hoạt, phong cách khác biệt. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','Màn hình gập | 256GB | 4200 mAh',1),
(1,'Vivo Light 5G 128GB','vivo-light-5g','Vivo','VVO-LGT-128',6290000,7290000,28,'assets/images/products/vivo-light-5g.svg','Trẻ trung, chụp ảnh đẹp, kết nối 5G.','Vivo Light 5G 128GB là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Trẻ trung, chụp ảnh đẹp, kết nối 5G. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','6.5 inch | 128GB | 5000 mAh',1),
(2,'AeroBook Pro 14','aerobook-pro-14','H2SO4','LTP-ABP-14',24990000,27990000,10,'assets/images/products/aerobook-pro-14.svg','Laptop nhôm cao cấp, hiệu năng sáng tạo.','AeroBook Pro 14 là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Laptop nhôm cao cấp, hiệu năng sáng tạo. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','14 inch 2.8K | 16GB RAM | 512GB SSD',1),
(2,'ZenBook Air 13','zenbook-air-13','ASUS','LTP-ZBA-13',17990000,20990000,13,'assets/images/products/zenbook-air-13.svg','Nhẹ như không, gọn cho mọi hành trình.','ZenBook Air 13 là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Nhẹ như không, gọn cho mọi hành trình. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','13.3 inch | 16GB RAM | 512GB SSD',1),
(2,'Legion Spark Gaming 15','legion-spark-15','Lenovo','LTP-LSP-15',28990000,31990000,9,'assets/images/products/legion-spark-15.svg','Chiến game mượt, tản nhiệt tối ưu.','Legion Spark Gaming 15 là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Chiến game mượt, tản nhiệt tối ưu. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','15.6 inch 165Hz | 16GB RAM | 1TB SSD',1),
(3,'Tab Studio 11','tab-studio-11','H2SO4','TAB-STD-11',10990000,12590000,20,'assets/images/products/tab-studio-11.svg','Màn hình lớn, bút vẽ, học và giải trí.','Tab Studio 11 là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Màn hình lớn, bút vẽ, học và giải trí. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','11 inch 120Hz | 128GB | 8000 mAh',1),
(3,'Galaxy Tab Neo 10','galaxy-tab-neo','Samsung','TAB-GLX-10',8690000,9990000,17,'assets/images/products/galaxy-tab-neo.svg','Tablet đa nhiệm cho học tập và làm việc.','Galaxy Tab Neo 10 là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Tablet đa nhiệm cho học tập và làm việc. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','10.9 inch | 128GB | Wi-Fi',1),
(4,'AirBeat Pro ANC','airbeat-pro-anc','H2SO4','AUD-ABP-01',2190000,2890000,44,'assets/images/products/airbeat-pro-anc.svg','Chống ồn chủ động, âm thanh sống động.','AirBeat Pro ANC là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Chống ồn chủ động, âm thanh sống động. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','ANC | Bluetooth 5.3 | 30 giờ',1),
(4,'SoundWave Studio','soundwave-studio','Sony','AUD-SWS-02',3990000,4690000,19,'assets/images/products/soundwave-studio.svg','Tai nghe over-ear êm ái, chống ồn.','SoundWave Studio là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Tai nghe over-ear êm ái, chống ồn. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','ANC | Bluetooth | 40 giờ',0),
(4,'MiniBoom Go','miniboom-go','JBL','AUD-MBG-03',1390000,1790000,36,'assets/images/products/miniboom-go.svg','Loa di động nhỏ gọn, bass mạnh.','MiniBoom Go là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Loa di động nhỏ gọn, bass mạnh. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','Bluetooth | Chống nước IPX7',0),
(5,'Watch Neo S','watch-neo-s','H2SO4','WCH-NEO-S',3690000,4490000,25,'assets/images/products/watch-neo-s.svg','Theo dõi sức khỏe và vận động thời thượng.','Watch Neo S là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Theo dõi sức khỏe và vận động thời thượng. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','AMOLED 1.43 inch | 100 chế độ thể thao',0),
(5,'FitBand Pulse','fitband-pulse','Xiaomi','WCH-FBP-02',890000,1190000,56,'assets/images/products/fitband-pulse.svg','Vòng tay thông minh, pin bền.','FitBand Pulse là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Vòng tay thông minh, pin bền. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','AMOLED | 14 ngày pin',0),
(6,'Sạc nhanh GaN 65W','sac-nhanh-gan-65w','Anker','ACC-GAN-65',790000,1090000,61,'assets/images/products/sac-nhanh-gan-65w.svg','Sạc nhỏ gọn, sạc nhanh đa thiết bị.','Sạc nhanh GaN 65W là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Sạc nhỏ gọn, sạc nhanh đa thiết bị. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','65W | USB-C x2 | USB-A x1',1),
(6,'Cáp Type-C Flex 2m','cap-type-c-flex-2m','H2SO4','ACC-CAB-2M',190000,290000,120,'assets/images/products/cap-type-c-flex-2m.svg','Cáp bện dù bền bỉ, truyền dữ liệu nhanh.','Cáp Type-C Flex 2m là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Cáp bện dù bền bỉ, truyền dữ liệu nhanh. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','USB-C to USB-C | 2m | 100W',1),
(6,'Ốp lưng MagShield Pro','op-magshield-pro','H2SO4','ACC-CAS-01',390000,590000,75,'assets/images/products/op-magshield-pro.svg','Bảo vệ chắc chắn, hỗ trợ sạc không dây.','Ốp lưng MagShield Pro là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Bảo vệ chắc chắn, hỗ trợ sạc không dây. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','TPU | MagSafe-compatible',1),
(6,'Pin dự phòng Aero 10000','pin-du-phong-aero','Baseus','ACC-PWB-10',690000,890000,38,'assets/images/products/pin-du-phong-aero.svg','Pin di động tiện lợi, hiển thị dung lượng.','Pin dự phòng Aero 10000 là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Pin di động tiện lợi, hiển thị dung lượng. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','10000mAh | 22.5W | USB-C',1),
(6,'Đế sạc không dây Halo','de-sac-halo','H2SO4','ACC-HAL-01',590000,790000,29,'assets/images/products/de-sac-halo.svg','Thiết kế tối giản, đặt sạc thật nhanh.','Đế sạc không dây Halo là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Thiết kế tối giản, đặt sạc thật nhanh. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','Wireless 15W | Type-C',1),
(7,'Bàn phím Nova RGB TKL','ban-phim-nova-rgb','H2SO4','GAM-KBD-01',1490000,1890000,33,'assets/images/products/ban-phim-nova-rgb.svg','Layout 87 phím, đèn RGB đa sắc.','Bàn phím Nova RGB TKL là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Layout 87 phím, đèn RGB đa sắc. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','TKL 87 keys | Mechanical | RGB',0),
(7,'Chuột Vortex X Wireless','chuot-vortex-x','Logitech','GAM-MSE-02',1090000,1390000,41,'assets/images/products/chuot-vortex-x.svg','Chuột không dây độ trễ thấp, cảm biến mượt.','Chuột Vortex X Wireless là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Chuột không dây độ trễ thấp, cảm biến mượt. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','Wireless | 16000 DPI | 65g',0),
(7,'Tay cầm Pulse Controller','tay-cam-pulse','H2SO4','GAM-PAD-03',990000,1290000,22,'assets/images/products/tay-cam-pulse.svg','Tay cầm gaming đa nền tảng, rung phản hồi.','Tay cầm Pulse Controller là sản phẩm minh họa dành cho website đồ án H2SO4 PHONES. Tay cầm gaming đa nền tảng, rung phản hồi. Thiết kế hiện đại, phù hợp nhu cầu học tập, làm việc và giải trí. Thông số và giá chỉ là dữ liệu mẫu.','Bluetooth | USB-C | 15 giờ',0);
