<?php
// Script để thêm dữ liệu mẫu với encoding UTF-8 đúng
header('Content-Type: text/html; charset=UTF-8');
require_once 'config.php';

// Set charset cho connection
mysqli_set_charset($conn, "utf8mb4");

echo "<h2>Đang thêm dữ liệu mẫu...</h2>";

// Xóa reviews cũ bị lỗi encoding
$conn->query("DELETE FROM reviews WHERE id > 0");
echo "<p>✓ Đã xóa reviews cũ</p>";

// Thêm reviews mới với encoding đúng
$reviews = [
    [1, 'Trần Văn Minh', 5, 'Hoa rất đẹp và tươi, giao hàng nhanh. Sẽ ủng hộ shop dài dài!', '2025-12-20 15:30:00'],
    [4, 'Lê Thị Hương', 5, 'Bó hoa hồng rất đẹp, bạn gái rất thích. Cảm ơn shop!', '2025-12-18 18:45:00'],
    [6, 'Phạm Đức Anh', 4, 'Hoa tulip vàng đẹp lắm, chỉ có điều giao hàng hơi chậm một chút.', '2025-12-16 10:20:00'],
    [7, 'Nguyễn Thị Mai', 5, 'Bó hoa cưới tuyệt vời! Đúng như mong đợi. Highly recommend!', '2025-12-11 20:00:00'],
    [10, 'Hoàng Văn Tùng', 4, 'Hoa hồng đỏ rất tươi, đóng gói cẩn thận. Giá hợp lý.', '2025-12-25 14:00:00'],
    [5, 'Vũ Thị Lan', 5, 'Sen hồng đẹp quá! Để bàn làm việc rất sang. Shop nhiệt tình!', '2025-12-23 11:30:00'],
    [9, 'Đặng Minh Tuấn', 4, 'Hoa trang trí tiệc cưới rất đẹp, tư vấn nhiệt tình.', '2025-12-24 16:45:00'],
    [8, 'Bùi Thị Ngọc', 5, 'Hoa cầm tay cô dâu xinh quá! Cảm ơn shop đã hỗ trợ nhanh chóng!', '2025-12-22 09:15:00'],
    [1, 'Nguyễn Thị Mai', 4, 'Hoa sen đẹp, thơm nhẹ nhàng. Rất hài lòng với dịch vụ.', '2025-12-19 13:20:00'],
    [4, 'Vũ Thị Lan', 5, 'Mua hoa hồng tặng mẹ, mẹ rất thích. Sẽ quay lại!', '2025-12-17 17:00:00'],
    [2, 'Trần Thu Hà', 5, 'Hoa mẫu đơn đẹp xuất sắc! Màu sắc tươi tắn, cánh hoa dày dặn.', '2025-12-15 09:00:00'],
    [3, 'Lý Minh Châu', 4, 'Nến thơm lavender rất dễ chịu, mùi hương nhẹ nhàng thư giãn.', '2025-12-14 14:30:00'],
];

$stmt = $conn->prepare("INSERT INTO reviews (product_id, user_name, rating, comment, created_at) VALUES (?, ?, ?, ?, ?)");

$count = 0;
foreach($reviews as $r) {
    $stmt->bind_param("isiss", $r[0], $r[1], $r[2], $r[3], $r[4]);
    if($stmt->execute()) {
        $count++;
    }
}
echo "<p>✓ Đã thêm $count đánh giá mới</p>";

// Xóa orders cũ bị lỗi và thêm mới
$conn->query("DELETE FROM orders WHERE id > 3");
echo "<p>✓ Đã xóa orders cũ bị lỗi</p>";

// Thêm orders mới
$orders = [
    // [user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status, delivery_status]
    [3, 'Nguyễn Ngọc Sinh', '0355610260', 'ngocsinh6005@gmail.com', 'momo', 'Số 45 Nguyễn Huệ, Quận 1, TP.HCM', 'Hoa Hồng (2), Hoa Sen (1)', 850000, '2025-12-20 10:30:00', 'completed', 'Đã giao'],
    [4, 'Trần Văn Minh', '0901234567', 'minhtran@gmail.com', 'cash on delivery', 'Số 123 Lê Lợi, Quận 3, TP.HCM', 'Bó hoa cưới trắng (1)', 1200000, '2025-12-18 14:20:00', 'completed', 'Đã giao'],
    [5, 'Lê Thị Hương', '0912345678', 'huongle@gmail.com', 'bank', 'Số 78 Trần Hưng Đạo, Quận 5, TP.HCM', 'Hoa tulip Vàng (3), Hoa Sen (2)', 1500000, '2025-12-15 09:15:00', 'completed', 'Đã giao'],
    [6, 'Phạm Đức Anh', '0923456789', 'ducanhpham@gmail.com', 'momo', 'Số 200 Điện Biên Phủ, Quận Bình Thạnh, TP.HCM', 'Hoa cầm tay cô dâu (1)', 980000, '2025-12-10 16:45:00', 'completed', 'Đã giao'],
    
    // Đang giao
    [7, 'Nguyễn Thị Mai', '0934567890', 'mainguyen@gmail.com', 'cash on delivery', 'Số 55 Võ Văn Tần, Quận 3, TP.HCM', 'Bó hoa hồng đỏ (2)', 760000, '2025-12-25 08:30:00', 'pending', 'Đang giao'],
    [8, 'Hoàng Văn Tùng', '0945678901', 'tunghoang@gmail.com', 'momo', 'Số 89 Nguyễn Thị Minh Khai, Quận 1, TP.HCM', 'Hoa trang trí tiệc cưới (1), Nến thơm lavender (2)', 2100000, '2025-12-25 11:20:00', 'completed', 'Đang giao'],
    [9, 'Vũ Thị Lan', '0956789012', 'lanvu@gmail.com', 'bank', 'Số 150 Cách Mạng Tháng 8, Quận 10, TP.HCM', 'Sen hồng (5)', 1250000, '2025-12-24 15:00:00', 'completed', 'Đang giao'],
    
    // Đang xử lý  
    [10, 'Đặng Minh Tuấn', '0967890123', 'tuandang@gmail.com', 'cash on delivery', 'Số 33 Pasteur, Quận 1, TP.HCM', 'Hoa Hồng (3), Hoa tulip Vàng (2)', 1100000, '2025-12-26 07:45:00', 'pending', 'Đang xử lý'],
    [11, 'Bùi Thị Ngọc', '0978901234', 'ngocbui@gmail.com', 'momo', 'Số 45 Nguyễn Huệ, Quận 1, TP.HCM', 'Mẫu đơn (2)', 680000, '2025-12-26 08:15:00', 'pending', 'Đang xử lý'],
    [3, 'Nguyễn Ngọc Sinh', '0355610260', 'ngocsinh6005@gmail.com', 'bank', 'Số 123 Lê Lợi, Quận 3, TP.HCM', 'Hoa Sen (4)', 920000, '2025-12-26 09:00:00', 'pending', 'Đang xử lý'],
    
    // Đã hủy
    [4, 'Trần Văn Minh', '0901234567', 'minhtran@gmail.com', 'cash on delivery', 'Số 78 Trần Hưng Đạo, Quận 5, TP.HCM', 'Bó hoa cưới trắng (1)', 1200000, '2025-12-22 10:00:00', 'pending', 'Đã hủy'],
    [5, 'Lê Thị Hương', '0912345678', 'huongle@gmail.com', 'momo', 'Số 55 Võ Văn Tần, Quận 3, TP.HCM', 'Hoa cầm tay cô dâu (2)', 1960000, '2025-12-19 14:30:00', 'pending', 'Đã hủy'],
];

$stmt2 = $conn->prepare("INSERT INTO orders (user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status, delivery_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$order_count = 0;
foreach($orders as $o) {
    $stmt2->bind_param("issssssisss", $o[0], $o[1], $o[2], $o[3], $o[4], $o[5], $o[6], $o[7], $o[8], $o[9], $o[10]);
    if($stmt2->execute()) {
        $order_count++;
    }
}
echo "<p>✓ Đã thêm $order_count đơn hàng mới</p>";

// Cập nhật users với tên tiếng Việt đúng
$users_update = [
    [4, 'Trần Văn Minh', 'minhtran@gmail.com'],
    [5, 'Lê Thị Hương', 'huongle@gmail.com'],
    [6, 'Phạm Đức Anh', 'ducanhpham@gmail.com'],
    [7, 'Nguyễn Thị Mai', 'mainguyen@gmail.com'],
    [8, 'Hoàng Văn Tùng', 'tunghoang@gmail.com'],
    [9, 'Vũ Thị Lan', 'lanvu@gmail.com'],
    [10, 'Đặng Minh Tuấn', 'tuandang@gmail.com'],
    [11, 'Bùi Thị Ngọc', 'ngocbui@gmail.com'],
];

$stmt3 = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
foreach($users_update as $u) {
    $stmt3->bind_param("si", $u[1], $u[0]);
    $stmt3->execute();
}
echo "<p>✓ Đã cập nhật tên users</p>";

echo "<h2 style='color: green;'>✅ Hoàn tất!</h2>";
echo "<p><a href='admin/reviews.php'>Xem trang Reviews</a></p>";
echo "<p><a href='admin/orders.php'>Xem trang Orders</a></p>";
echo "<p><a href='admin/users.php'>Xem trang Users</a></p>";

// Xóa file này sau khi chạy xong
// unlink(__FILE__);
?>
