<?php
header('Content-Type: text/html; charset=utf-8');
$conn = mysqli_connect('localhost', 'root', '', 'shop_db');
mysqli_set_charset($conn, 'utf8mb4');

if(!$conn) {
    die('Connection failed');
}

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Thêm sản phẩm</title></head><body>";

// ===== BƯỚC 1: TẢI HÌNH ẢNH =====
echo "<h2>Bước 1: Đang tải hình ảnh...</h2>";

$upload_dir = __DIR__ . '/assets/uploads/products/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$images = [
    'wedding1.jpg' => 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=400&h=400&fit=crop',
    'wedding2.jpg' => 'https://images.unsplash.com/photo-1522057306606-8d84afe14e67?w=400&h=400&fit=crop',
    'wedding3.jpg' => 'https://images.unsplash.com/photo-1469371670807-013ccf25f16a?w=400&h=400&fit=crop',
    'birthday1.jpg' => 'https://images.unsplash.com/photo-1455659817273-f96807779a8a?w=400&h=400&fit=crop',
    'birthday2.jpg' => 'https://images.unsplash.com/photo-1487530811176-3780de880c2d?w=400&h=400&fit=crop',
    'birthday3.jpg' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=400&h=400&fit=crop',
    'sunflower.jpg' => 'https://images.unsplash.com/photo-1597848212624-a19eb35e2651?w=400&h=400&fit=crop',
    'valentine.jpg' => 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=400&h=400&fit=crop',
    'women_day.jpg' => 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=400&h=400&fit=crop',
    'vn_women.jpg' => 'https://images.unsplash.com/photo-1508610048659-a06b669e3321?w=400&h=400&fit=crop',
    'tet.jpg' => 'https://images.unsplash.com/photo-1457089328109-e5d9bd499191?w=400&h=400&fit=crop',
    'gift1.jpg' => 'https://images.unsplash.com/photo-1549488344-cbb6c34cf08b?w=400&h=400&fit=crop',
    'gift2.jpg' => 'https://images.unsplash.com/photo-1526047932273-341f2a7631f9?w=400&h=400&fit=crop',
    'gift3.jpg' => 'https://images.unsplash.com/photo-1563241527-3004b7be0ffd?w=400&h=400&fit=crop',
    'opening.jpg' => 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?w=400&h=400&fit=crop',
    'lily.jpg' => 'https://images.unsplash.com/photo-1468327768560-75b778cbb551?w=400&h=400&fit=crop',
    'carnation.jpg' => 'https://images.unsplash.com/photo-1589244159943-460088ed5c92?w=400&h=400&fit=crop',
    'orchid.jpg' => 'https://images.unsplash.com/photo-1566873535350-a3f5d4a804b7?w=400&h=400&fit=crop',
    'daisy.jpg' => 'https://images.unsplash.com/photo-1606041008023-472dfb5e530f?w=400&h=400&fit=crop',
];

$img_count = 0;
foreach($images as $filename => $url) {
    $filepath = $upload_dir . $filename;
    if(!file_exists($filepath)) {
        $context = stream_context_create(['http' => ['timeout' => 30, 'user_agent' => 'Mozilla/5.0']]);
        $image_data = @file_get_contents($url, false, $context);
        if($image_data && file_put_contents($filepath, $image_data)) {
            $img_count++;
            echo "✅ $filename<br>";
        }
    } else {
        echo "⏭️ $filename (đã có)<br>";
    }
}
echo "<p><strong>Đã tải $img_count hình ảnh mới</strong></p>";

// ===== BƯỚC 2: SỬA ENCODING CÁC SẢN PHẨM CŨ =====
echo "<h2>Bước 2: Sửa tên sản phẩm cũ...</h2>";
$old_products = [
    1 => ['name' => 'Hoa Sen', 'details' => 'Hoa sen – biểu tượng của sự thuần khiết, thanh tao'],
    2 => ['name' => 'Mộng Mơ', 'details' => 'Giỏ hoa pastel nhẹ nhàng với hoa cát tường, hoa hồng'],
    3 => ['name' => 'Nến thơm lavender', 'details' => 'Nến thơm hương lavender giúp thư giãn'],
    4 => ['name' => 'Hoa Hồng', 'details' => 'Hoa hồng đỏ – biểu tượng của tình yêu nồng nàn'],
    5 => ['name' => 'Sen hồng', 'details' => 'Biểu tượng của sự thanh cao, rực rỡ'],
    6 => ['name' => 'Hoa tulip Vàng', 'details' => 'Biểu tượng của niềm vui và lời chúc khởi đầu hạnh phúc']
];
foreach($old_products as $id => $data) {
    $stmt = mysqli_prepare($conn, "UPDATE products SET name = ?, details = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $data['name'], $data['details'], $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo "✅ ID $id: {$data['name']}<br>";
}

// ===== BƯỚC 3: THÊM SẢN PHẨM MỚI =====
echo "<h2>Bước 3: Thêm sản phẩm mới...</h2>";

$new_products = [
    ['name' => 'Bó hoa cưới trắng', 'details' => 'Bó hoa cưới tinh khôi với hoa hồng trắng và baby breath', 'price' => 850000, 'category' => 'dam-cuoi', 'image' => 'wedding1.jpg'],
    ['name' => 'Hoa cầm tay cô dâu', 'details' => 'Hoa cầm tay sang trọng với hoa mẫu đơn và hoa lan', 'price' => 1200000, 'category' => 'dam-cuoi', 'image' => 'wedding2.jpg'],
    ['name' => 'Hoa trang trí tiệc cưới', 'details' => 'Bình hoa để bàn tiệc cưới phong cách châu Âu', 'price' => 650000, 'category' => 'dam-cuoi', 'image' => 'wedding3.jpg'],
    ['name' => 'Bó hoa hồng đỏ', 'details' => 'Bó 20 hoa hồng đỏ Ecuador nhập khẩu', 'price' => 750000, 'category' => 'sinh-nhat', 'image' => 'birthday1.jpg'],
    ['name' => 'Giỏ hoa mix màu', 'details' => 'Giỏ hoa nhiều màu sắc tươi vui cho ngày sinh nhật', 'price' => 450000, 'category' => 'sinh-nhat', 'image' => 'birthday2.jpg'],
    ['name' => 'Hộp hoa hồng sáp', 'details' => 'Hộp hoa hồng sáp thơm lâu, quà tặng ý nghĩa', 'price' => 350000, 'category' => 'sinh-nhat', 'image' => 'birthday3.jpg'],
    ['name' => 'Bó hoa hướng dương', 'details' => 'Bó hoa hướng dương rực rỡ - biểu tượng của niềm vui', 'price' => 280000, 'category' => 'sinh-nhat', 'image' => 'sunflower.jpg'],
    ['name' => 'Hoa Valentine', 'details' => 'Bó hoa hồng đỏ 99 bông - tình yêu vĩnh cửu', 'price' => 2500000, 'category' => 'ngay-le', 'image' => 'valentine.jpg'],
    ['name' => 'Hoa 8/3', 'details' => 'Bó hoa tulip hồng tặng mẹ, tặng vợ ngày 8/3', 'price' => 550000, 'category' => 'ngay-le', 'image' => 'women_day.jpg'],
    ['name' => 'Hoa 20/10', 'details' => 'Giỏ hoa tone hồng pastel ngày Phụ nữ Việt Nam', 'price' => 480000, 'category' => 'ngay-le', 'image' => 'vn_women.jpg'],
    ['name' => 'Hoa Tết', 'details' => 'Chậu hoa mai vàng rực rỡ đón xuân', 'price' => 1500000, 'category' => 'ngay-le', 'image' => 'tet.jpg'],
    ['name' => 'Hộp quà chocolate hoa', 'details' => 'Hộp quà gồm hoa hồng và chocolate Ferrero', 'price' => 680000, 'category' => 'qua-tang', 'image' => 'gift1.jpg'],
    ['name' => 'Gấu bông kèm hoa', 'details' => 'Gấu bông dễ thương kèm bó hoa nhỏ xinh', 'price' => 420000, 'category' => 'qua-tang', 'image' => 'gift2.jpg'],
    ['name' => 'Set quà spa thư giãn', 'details' => 'Hộp quà gồm nến thơm, muối tắm và hoa khô', 'price' => 550000, 'category' => 'qua-tang', 'image' => 'gift3.jpg'],
    ['name' => 'Lẵng hoa khai trương', 'details' => 'Lẵng hoa to đẹp chúc mừng khai trương', 'price' => 1200000, 'category' => 'qua-tang', 'image' => 'opening.jpg'],
    ['name' => 'Hoa ly trắng', 'details' => 'Bó hoa ly trắng tinh khiết, hương thơm nhẹ nhàng', 'price' => 320000, 'category' => 'ngay-le', 'image' => 'lily.jpg'],
    ['name' => 'Hoa cẩm chướng', 'details' => 'Bó hoa cẩm chướng - biểu tượng của tình mẫu tử', 'price' => 250000, 'category' => 'sinh-nhat', 'image' => 'carnation.jpg'],
    ['name' => 'Hoa lan hồ điệp', 'details' => 'Chậu lan hồ điệp sang trọng làm quà tặng', 'price' => 980000, 'category' => 'qua-tang', 'image' => 'orchid.jpg'],
    ['name' => 'Bó hoa cúc họa mi', 'details' => 'Bó hoa cúc họa mi trong trẻo, thanh khiết', 'price' => 180000, 'category' => 'sinh-nhat', 'image' => 'daisy.jpg'],
];

$count = 0;
foreach($new_products as $product) {
    // Kiểm tra xem sản phẩm đã tồn tại chưa
    $check = mysqli_query($conn, "SELECT id FROM products WHERE name = '{$product['name']}'");
    if(mysqli_num_rows($check) == 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO products (name, details, price, category, image, stock_quantity, low_stock_threshold, stock_status, is_available) VALUES (?, ?, ?, ?, ?, 50, 10, 'in_stock', 1)");
        mysqli_stmt_bind_param($stmt, "ssiss", $product['name'], $product['details'], $product['price'], $product['category'], $product['image']);
        if(mysqli_stmt_execute($stmt)) {
            $count++;
            echo "✅ {$product['name']} - " . number_format($product['price'], 0, ',', '.') . "đ<br>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "⏭️ {$product['name']} (đã có)<br>";
    }
}

echo "<br><h2 style='color:green'>🎉 Hoàn tất! Đã thêm $count sản phẩm mới!</h2>";
echo "<p style='font-size:18px'><a href='pages/shop.php' style='color:#667eea; text-decoration:none; font-weight:bold;'>👉 Quay lại Shop xem kết quả</a></p>";
echo "</body></html>";

mysqli_close($conn);
?>
