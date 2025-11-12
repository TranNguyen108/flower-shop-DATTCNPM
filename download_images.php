<?php
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Download Images</title></head><body>";
echo "<h2>Đang tải hình ảnh sản phẩm...</h2>";

$upload_dir = __DIR__ . '/assets/uploads/products/';

// Tạo thư mục nếu chưa có
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Danh sách hình ảnh từ Unsplash (hoa)
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

$count = 0;
foreach($images as $filename => $url) {
    $filepath = $upload_dir . $filename;
    
    // Download image
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0'
        ]
    ]);
    
    $image_data = @file_get_contents($url, false, $context);
    
    if($image_data && file_put_contents($filepath, $image_data)) {
        $count++;
        echo "<p>✅ Đã tải: <strong>$filename</strong></p>";
    } else {
        echo "<p>❌ Lỗi tải: $filename</p>";
    }
}

echo "<br><h3 style='color:green'>✅ Đã tải $count hình ảnh!</h3>";
echo "<p>Bây giờ hãy chạy: <a href='add_products.php'>Thêm sản phẩm vào database</a></p>";
echo "</body></html>";
?>
