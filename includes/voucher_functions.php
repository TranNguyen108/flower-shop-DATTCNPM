<?php
/**
 * 🎟️ Voucher Functions
 * Xử lý mã giảm giá
 */

// Tạo bảng vouchers nếu chưa có
function init_voucher_table($conn) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'vouchers'");
    if(mysqli_num_rows($check) == 0){
        mysqli_query($conn, "CREATE TABLE vouchers (
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
        )");
        
        // Thêm sample vouchers - Bộ sưu tập chuyên nghiệp
        mysqli_query($conn, "INSERT INTO vouchers (code, name, description, discount_type, discount_value, min_order_value, max_discount, usage_limit, user_limit, start_date, end_date) VALUES
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
            ('BIRTHDAY', 'Happy Birthday', 'Giảm đặc biệt ngày sinh nhật', 'percent', 20, 0, 100000, NULL, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))
        ");
    }
    
    // Bảng lịch sử sử dụng voucher
    $check2 = mysqli_query($conn, "SHOW TABLES LIKE 'voucher_usage'");
    if(mysqli_num_rows($check2) == 0){
        mysqli_query($conn, "CREATE TABLE voucher_usage (
            id INT AUTO_INCREMENT PRIMARY KEY,
            voucher_id INT NOT NULL,
            user_id INT NOT NULL,
            order_id INT,
            used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_voucher (voucher_id, user_id, order_id)
        )");
    }
    
    // Bảng voucher đã thu thập của user
    $check3 = mysqli_query($conn, "SHOW TABLES LIKE 'user_vouchers'");
    if(mysqli_num_rows($check3) == 0){
        mysqli_query($conn, "CREATE TABLE user_vouchers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            voucher_id INT NOT NULL,
            collected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            used_at TIMESTAMP NULL,
            UNIQUE KEY user_voucher_unique (user_id, voucher_id)
        )");
    }
}

// Kiểm tra và áp dụng voucher
function validate_voucher($conn, $code, $user_id, $order_total) {
    init_voucher_table($conn);
    
    $code = strtoupper(trim($code));
    
    // Lấy thông tin voucher
    $stmt = $conn->prepare("SELECT * FROM vouchers WHERE code = ? AND is_active = 1");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 0){
        return ['success' => false, 'message' => 'Mã giảm giá không tồn tại hoặc đã hết hạn!'];
    }
    
    $voucher = $result->fetch_assoc();
    
    // Kiểm tra thời gian
    $now = date('Y-m-d H:i:s');
    if($voucher['start_date'] && $now < $voucher['start_date']){
        return ['success' => false, 'message' => 'Mã giảm giá chưa được kích hoạt!'];
    }
    if($voucher['end_date'] && $now > $voucher['end_date']){
        return ['success' => false, 'message' => 'Mã giảm giá đã hết hạn!'];
    }
    
    // Kiểm tra giới hạn sử dụng tổng
    if($voucher['usage_limit'] !== null && $voucher['used_count'] >= $voucher['usage_limit']){
        return ['success' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng!'];
    }
    
    // Kiểm tra giới hạn user
    if($voucher['user_limit'] > 0){
        $stmt2 = $conn->prepare("SELECT COUNT(*) as cnt FROM voucher_usage WHERE voucher_id = ? AND user_id = ?");
        $stmt2->bind_param("ii", $voucher['id'], $user_id);
        $stmt2->execute();
        $usage = $stmt2->get_result()->fetch_assoc();
        if($usage['cnt'] >= $voucher['user_limit']){
            return ['success' => false, 'message' => 'Bạn đã sử dụng mã này rồi!'];
        }
    }
    
    // Kiểm tra giá trị đơn hàng tối thiểu
    if($order_total < $voucher['min_order_value']){
        return [
            'success' => false, 
            'message' => 'Đơn hàng tối thiểu ' . number_format($voucher['min_order_value'], 0, ',', '.') . '₫ để sử dụng mã này!'
        ];
    }
    
    // Tính số tiền giảm
    if($voucher['discount_type'] == 'percent'){
        $discount = $order_total * ($voucher['discount_value'] / 100);
        if($voucher['max_discount'] !== null){
            $discount = min($discount, $voucher['max_discount']);
        }
    } else {
        $discount = $voucher['discount_value'];
    }
    
    // Không giảm quá tổng đơn
    $discount = min($discount, $order_total);
    
    return [
        'success' => true,
        'voucher' => $voucher,
        'discount' => $discount,
        'message' => 'Áp dụng mã giảm giá thành công!'
    ];
}

// Lưu việc sử dụng voucher
function use_voucher($conn, $voucher_id, $user_id, $order_id) {
    // Thêm vào lịch sử
    $stmt = $conn->prepare("INSERT INTO voucher_usage (voucher_id, user_id, order_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $voucher_id, $user_id, $order_id);
    $stmt->execute();
    
    // Tăng used_count
    $stmt2 = $conn->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = ?");
    $stmt2->bind_param("i", $voucher_id);
    $stmt2->execute();
}

// Lấy danh sách voucher có thể dùng (chỉ lấy voucher user đã lưu)
function get_available_vouchers($conn, $user_id, $order_total = 0) {
    init_voucher_table($conn);
    
    // Đảm bảo bảng user_vouchers tồn tại
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'user_vouchers'");
    if(mysqli_num_rows($check) == 0){
        mysqli_query($conn, "CREATE TABLE user_vouchers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            voucher_id INT NOT NULL,
            collected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_used TINYINT(1) DEFAULT 0,
            used_at TIMESTAMP NULL,
            UNIQUE KEY user_voucher (user_id, voucher_id)
        )");
    }
    
    $vouchers = [];
    
    // Lấy voucher user đã lưu - query đơn giản
    $sql = "SELECT v.*, 
            v.used_count as usage_count,
            uv.collected_at as saved_at,
            uv.is_used as uv_is_used
            FROM user_vouchers uv
            JOIN vouchers v ON uv.voucher_id = v.id 
            WHERE uv.user_id = $user_id
            AND uv.is_used = 0
            AND v.is_active = 1
            ORDER BY v.discount_value DESC";
    
    $result = mysqli_query($conn, $sql);
    
    if(!$result) {
        error_log("Voucher SQL Error: " . mysqli_error($conn));
        return [];
    }
    
    while($row = mysqli_fetch_assoc($result)){
        // Kiểm tra còn hạn không
        $now = time();
        $end_date = $row['end_date'] ? strtotime($row['end_date']) : null;
        $start_date = $row['start_date'] ? strtotime($row['start_date']) : null;
        
        // Bỏ qua voucher hết hạn hoặc chưa bắt đầu
        if($end_date && $now > $end_date) continue;
        if($start_date && $now < $start_date) continue;
        
        // Kiểm tra usage_limit
        if($row['usage_limit'] > 0 && $row['used_count'] >= $row['usage_limit']) continue;
        
        // Kiểm tra user đã dùng bao nhiêu lần
        $usage_check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM voucher_usage WHERE voucher_id = {$row['id']} AND user_id = $user_id");
        $user_used = mysqli_fetch_assoc($usage_check)['cnt'] ?? 0;
        
        // Kiểm tra điều kiện sử dụng
        if($row['user_limit'] > 0 && $user_used >= $row['user_limit']){
            $row['can_use'] = false;
            $row['reason'] = 'Đã dùng hết lượt';
        } elseif($order_total > 0 && $order_total < $row['min_order_value']){
            $row['can_use'] = false;
            $row['reason'] = 'Đơn tối thiểu ' . number_format($row['min_order_value'], 0, ',', '.') . '₫';
        } else {
            $row['can_use'] = true;
            $row['reason'] = '';
        }
        
        $row['usage_count'] = $row['usage_count'] ?? 0;
        $row['user_used'] = $user_used;
        
        $vouchers[] = $row;
    }
    
    return $vouchers;
}
?>
