INSERT INTO appliances (sku, name, brand, category, price, stock, warranty_months, energy_rating) VALUES
('TV-32A1', 'ทีวี 32 นิ้ว HD', 'Panaphonic', 'ทีวี', 4990.00, 12, 24, 3),
('TV-55U2', 'ทีวี 55 นิ้ว 4K', 'Sangsung', 'ทีวี', 16990.00, 7, 24, 5),
('FR-250S', 'ตู้เย็น 2 ประตู 250L', 'Hitano', 'ตู้เย็น', 8990.00, 10, 36, 5),
('AC-12000', 'แอร์ 12000 BTU อินเวอร์เตอร์', 'Daika', 'แอร์', 13990.00, 6, 60, 5),
('WM-8KG', 'เครื่องซักผ้า 8 กก.', 'Toshiha', 'เครื่องซักผ้า', 6990.00, 9, 24, 4),
('MW-23L', 'ไมโครเวฟ 23 ลิตร', 'Panaphonic', 'ไมโครเวฟ', 2490.00, 20, 12, 3),
('VA-1000', 'เครื่องดูดฝุ่น 1000W', 'Sangsung', 'เครื่องใช้ในบ้าน', 1590.00, 15, 12, 2),
('IH-2000', 'เตาแม่เหล็กไฟฟ้า 2000W', 'Sharpix', 'เครื่องครัว', 1290.00, 25, 12, 3),
('AR-5L', 'หม้อทอดไร้น้ำมัน 5 ลิตร', 'SmartCook', 'เครื่องครัว', 1790.00, 18, 12, 4),
('FR-180S', 'ตู้เย็น 1 ประตู 180L', 'Toshiha', 'ตู้เย็น', 6490.00, 8, 24, 4),
('TV-43FHD', 'ทีวี 43 นิ้ว Full HD', 'Sharpix', 'ทีวี', 8990.00, 14, 24, 4),
('AC-9000', 'แอร์ 9000 BTU อินเวอร์เตอร์', 'Daika', 'แอร์', 11990.00, 11, 48, 5),
('WM-10KG', 'เครื่องซักผ้า 10 กก. ฝาหน้า', 'Hitano', 'เครื่องซักผ้า', 10990.00, 5, 36, 5),
('MW-30L', 'ไมโครเวฟ 30 ลิตร', 'Sangsung', 'ไมโครเวฟ', 3290.00, 13, 12, 4),
('VC-1500', 'เครื่องดูดฝุ่น 1500W', 'Panaphonic', 'เครื่องใช้ในบ้าน', 1990.00, 16, 12, 3),
('IH-2500', 'เตาแม่เหล็กไฟฟ้า 2500W', 'SmartCook', 'เครื่องครัว', 1790.00, 22, 12, 4),
('AR-3.5L', 'หม้อทอดไร้น้ำมัน 3.5 ลิตร', 'Sharpix', 'เครื่องครัว', 1490.00, 19, 12, 3),
('FR-300S', 'ตู้เย็น 3 ประตู 300L', 'Daika', 'ตู้เย็น', 12990.00, 7, 48, 5),
('TV-65UHD', 'ทีวี 65 นิ้ว UHD Smart TV', 'Hitano', 'ทีวี', 22900.00, 4, 36, 5),
('AC-18000', 'แอร์ 18000 BTU อินเวอร์เตอร์', 'Sangsung', 'แอร์', 18990.00, 5, 60, 5);CREATE DATABASE IF NOT EXISTS webapi_demo
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE webapi_demo;

CREATE TABLE IF NOT EXISTS appliances (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(32) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  brand VARCHAR(80) NOT NULL,
  category VARCHAR(80) NOT NULL,
  price DECIMAL(10,2) NOT NULL CHECK (price >= 0),
  stock INT NOT NULL DEFAULT 0 CHECK (stock >= 0),
  warranty_months TINYINT UNSIGNED NOT NULL DEFAULT 12,
  energy_rating TINYINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
