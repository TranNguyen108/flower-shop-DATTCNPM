-- Reset Voucher Tables for Professional Voucher System
-- Run this SQL to get new voucher data

-- Drop existing tables
DROP TABLE IF EXISTS voucher_usage;
DROP TABLE IF EXISTS user_vouchers;
DROP TABLE IF EXISTS vouchers;

-- Create vouchers table
CREATE TABLE vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    discount_type ENUM('percent', 'fixed') DEFAULT 'percent',
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_value DECIMAL(10,2) DEFAULT 0,
    max_discount DECIMAL(10,2) DEFAULT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    user_limit INT DEFAULT 1,
    start_date DATETIME,
    end_date DATETIME,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create voucher_usage table
CREATE TABLE voucher_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voucher_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY user_voucher (voucher_id, user_id, order_id)
);

-- Create user_vouchers table (voucher wallet)
CREATE TABLE user_vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    voucher_id INT NOT NULL,
    collected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    UNIQUE KEY user_voucher_unique (user_id, voucher_id)
);

-- Insert sample vouchers
INSERT INTO vouchers (code, name, description, discount_type, discount_value, min_order_value, max_discount, usage_limit, user_limit, start_date, end_date) VALUES
    ('WELCOME10', 'Chào mừng khách mới', 'Giảm 10% cho đơn hàng đầu tiên', 'percent', 10, 100000, 50000, 1000, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR)),
    ('FLOWER20', 'Siêu Sale 20%', 'Giảm 20% tối đa 100k cho mọi đơn', 'percent', 20, 200000, 100000, 500, 3, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH)),
    ('SALE50K', 'Giảm ngay 50K', 'Giảm trực tiếp 50.000đ cho đơn từ 300k', 'fixed', 50000, 300000, NULL, 200, 2, NOW(), DATE_ADD(NOW(), INTERVAL 3 MONTH)),
    ('FREESHIP', 'Miễn phí ship', 'Giảm 30.000đ phí vận chuyển', 'fixed', 30000, 150000, NULL, NULL, 5, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR)),
    ('FREESHIP50', 'Freeship đơn lớn', 'Miễn phí ship cho đơn từ 500k', 'fixed', 50000, 500000, NULL, 100, 3, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH)),
    ('HOT30', 'Deal Siêu Hot 30%', 'Giảm 30% tối đa 200k - Limited!', 'percent', 30, 400000, 200000, 50, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH)),
    ('MEGA40', 'Mega Sale 40%', 'Giảm 40% tối đa 300k - VIP Only', 'percent', 40, 600000, 300000, 20, 1, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY)),
    ('NEW100K', 'Giảm 100K đơn lớn', 'Giảm 100.000đ cho đơn từ 700k', 'fixed', 100000, 700000, NULL, 100, 2, NOW(), DATE_ADD(NOW(), INTERVAL 2 MONTH)),
    ('GARDEN5', 'Từ Vườn Hoa Ảo', 'Mã từ game vườn hoa - Giảm 5%', 'percent', 5, 0, 30000, NULL, 10, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR)),
    ('GARDEN10', 'Từ Vườn Hoa Ảo', 'Mã từ game vườn hoa - Giảm 10%', 'percent', 10, 100000, 50000, NULL, 10, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR)),
    ('GARDEN15', 'Từ Vườn Hoa Ảo', 'Mã từ game vườn hoa - Giảm 15%', 'percent', 15, 200000, 80000, NULL, 10, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR)),
    ('GARDEN25', 'Từ Vườn Hoa Ảo VIP', 'Mã từ game vườn hoa - Giảm 25%', 'percent', 25, 300000, 150000, NULL, 5, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR)),
    ('LASTCHANCE', 'Cơ hội cuối', 'Giảm 15% - Sắp hết hạn!', 'percent', 15, 150000, 75000, 30, 2, NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY)),
    ('SUMMER25', 'Summer Sale', 'Giảm 25% mùa hè tươi mát', 'percent', 25, 250000, 125000, 100, 2, NOW(), DATE_ADD(NOW(), INTERVAL 3 MONTH)),
    ('BIRTHDAY', 'Happy Birthday', 'Giảm đặc biệt ngày sinh nhật', 'percent', 20, 0, 100000, NULL, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR));

-- Done!
SELECT 'Voucher tables reset successfully!' as Message;
