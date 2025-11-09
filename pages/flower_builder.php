<?php
/**
 * Custom Flower Builder - Tự Thiết Kế Bó Hoa
 * Chọn hoa, số lượng, cách gói, phụ kiện
 */

@include '../config.php';

$user_id = $_SESSION['user_id'] ?? null;

if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

$message = [];

// Debug: Xem data nhận được
if(isset($_POST['add_to_cart'])){
    error_log("POST received: " . print_r($_POST, true));
}

// Xử lý đặt hàng bó hoa tùy chỉnh
if(isset($_POST['add_to_cart'])){
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message[] = 'Lỗi bảo mật!';
    } else {
        $bouquet_name = sanitize_input($_POST['bouquet_name'] ?? 'Bó hoa tự thiết kế');
        $selected_items = json_decode($_POST['selected_items'] ?? '[]', true);
        $total_price = (int)($_POST['total_price'] ?? 0);
        
        // Debug
        error_log("Bouquet name: $bouquet_name");
        error_log("Total price: $total_price");
        error_log("Selected items: " . print_r($selected_items, true));
        
        if($total_price > 0 && !empty($selected_items)){
            // Tạo mô tả chi tiết
            $description_parts = [];
            foreach($selected_items as $item) {
                $description_parts[] = $item['name'] . ' x' . $item['quantity'];
            }
            $description = implode(', ', $description_parts);
            
            // Lưu vào giỏ hàng
            $custom_name = !empty($bouquet_name) ? $bouquet_name : "Bó hoa tự thiết kế - " . date('d/m H:i');
            
            $insert_id = db_insert($conn,
                "INSERT INTO cart (user_id, pid, name, price, quantity, image, is_custom, custom_data) 
                 VALUES (?, 0, ?, ?, 1, 'custom_bouquet.png', 1, ?)",
                "isis",
                [$user_id, $custom_name, $total_price, json_encode($selected_items)]
            );
            
            if($insert_id){
                $message[] = 'Đã thêm bó hoa vào giỏ hàng thành công!';
            } else {
                $message[] = 'Lỗi khi thêm vào giỏ hàng!';
            }
        } else {
            $message[] = 'Vui lòng chọn ít nhất 1 loại hoa!';
        }
    }
}

// Dữ liệu hoa và phụ kiện
$main_flowers = [
    ['id' => 1, 'name' => 'Hoa Hồng Đỏ', 'price' => 15000, 'unit' => 'cành', 'image' => '🌹', 'color' => '#e74c3c'],
    ['id' => 2, 'name' => 'Hoa Hồng Hồng', 'price' => 15000, 'unit' => 'cành', 'image' => '🌸', 'color' => '#fd79a8'],
    ['id' => 3, 'name' => 'Hoa Hồng Trắng', 'price' => 15000, 'unit' => 'cành', 'image' => '🤍', 'color' => '#ecf0f1'],
    ['id' => 4, 'name' => 'Hoa Hồng Vàng', 'price' => 18000, 'unit' => 'cành', 'image' => '💛', 'color' => '#f1c40f'],
    ['id' => 5, 'name' => 'Hoa Hướng Dương', 'price' => 25000, 'unit' => 'cành', 'image' => '🌻', 'color' => '#f39c12'],
    ['id' => 6, 'name' => 'Hoa Tulip', 'price' => 30000, 'unit' => 'cành', 'image' => '🌷', 'color' => '#e74c3c'],
    ['id' => 7, 'name' => 'Hoa Lily', 'price' => 35000, 'unit' => 'cành', 'image' => '🌺', 'color' => '#fff'],
    ['id' => 8, 'name' => 'Hoa Cẩm Chướng', 'price' => 12000, 'unit' => 'cành', 'image' => '💮', 'color' => '#e91e63'],
    ['id' => 9, 'name' => 'Hoa Cúc', 'price' => 10000, 'unit' => 'cành', 'image' => '🌼', 'color' => '#fff'],
    ['id' => 10, 'name' => 'Hoa Lan Hồ Điệp', 'price' => 80000, 'unit' => 'cành', 'image' => '🦋', 'color' => '#9b59b6'],
    ['id' => 11, 'name' => 'Hoa Baby', 'price' => 25000, 'unit' => 'bó nhỏ', 'image' => '🤍', 'color' => '#ecf0f1'],
    ['id' => 12, 'name' => 'Hoa Cát Tường', 'price' => 20000, 'unit' => 'cành', 'image' => '💜', 'color' => '#a29bfe'],
];

$fillers = [
    ['id' => 20, 'name' => 'Lá Monstera', 'price' => 15000, 'unit' => 'lá', 'image' => '🌿', 'color' => '#27ae60'],
    ['id' => 21, 'name' => 'Lá Dương Xỉ', 'price' => 8000, 'unit' => 'cành', 'image' => '☘️', 'color' => '#2ecc71'],
    ['id' => 22, 'name' => 'Cành Eucalyptus', 'price' => 20000, 'unit' => 'cành', 'image' => '🍃', 'color' => '#1abc9c'],
    ['id' => 23, 'name' => 'Lá Bạc', 'price' => 12000, 'unit' => 'cành', 'image' => '🌿', 'color' => '#bdc3c7'],
    ['id' => 24, 'name' => 'Cỏ Đuôi Thỏ', 'price' => 15000, 'unit' => 'cành', 'image' => '🌾', 'color' => '#dfe6e9'],
];

$wrapping_styles = [
    ['id' => 30, 'name' => 'Giấy Kraft', 'price' => 20000, 'image' => '📦', 'desc' => 'Phong cách vintage, tự nhiên'],
    ['id' => 31, 'name' => 'Giấy Hàn Quốc', 'price' => 35000, 'image' => '🎁', 'desc' => 'Sang trọng, nhiều màu sắc'],
    ['id' => 32, 'name' => 'Giấy Trong Suốt', 'price' => 25000, 'image' => '✨', 'desc' => 'Hiện đại, tinh tế'],
    ['id' => 33, 'name' => 'Hộp Vuông', 'price' => 80000, 'image' => '🎀', 'desc' => 'Hộp cứng cao cấp'],
    ['id' => 34, 'name' => 'Hộp Tim', 'price' => 120000, 'image' => '💝', 'desc' => 'Lãng mạn, đặc biệt'],
    ['id' => 35, 'name' => 'Giỏ Mây', 'price' => 100000, 'image' => '🧺', 'desc' => 'Tự nhiên, bền đẹp'],
];

$accessories = [
    ['id' => 40, 'name' => 'Nơ Satin Nhỏ', 'price' => 10000, 'image' => '🎀'],
    ['id' => 41, 'name' => 'Nơ Satin Lớn', 'price' => 20000, 'image' => '🎗️'],
    ['id' => 42, 'name' => 'Thiệp Chúc Mừng', 'price' => 15000, 'image' => '💌'],
    ['id' => 43, 'name' => 'Gấu Bông Mini', 'price' => 50000, 'image' => '🧸'],
    ['id' => 44, 'name' => 'Socola Ferrero (5v)', 'price' => 80000, 'image' => '🍫'],
    ['id' => 45, 'name' => 'Đèn LED Nhấp Nháy', 'price' => 30000, 'image' => '💡'],
    ['id' => 46, 'name' => 'Bướm Trang Trí', 'price' => 15000, 'image' => '🦋'],
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tự Thiết Kế Bó Hoa - Flower Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/custom-builder.css">
</head>
<body>

<?php @include '../header.php'; ?>

<?php
if(!empty($message)){
    foreach($message as $msg){
        echo '<div class="message"><span>'.e($msg).'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
    }
}
?>

<section class="heading">
    <h3><i class="fas fa-palette"></i> Tự Thiết Kế Bó Hoa</h3>
    <p><a href="./home.php">Trang chủ</a> / Tự Thiết Kế Bó Hoa</p>
</section>

<section class="builder-section">
    <div class="builder-wrapper">
        
        <!-- Left: Selection Area -->
        <div class="selection-area">
            
            <!-- Step 1: Chọn Hoa Chính -->
            <div class="selection-step" id="step-flowers">
                <div class="step-header">
                    <span class="step-number">1</span>
                    <h3>Chọn Hoa Chính <span class="required">*</span></h3>
                </div>
                <div class="items-grid">
                    <?php foreach($main_flowers as $flower): ?>
                    <div class="item-card" data-id="<?php echo $flower['id']; ?>" 
                         data-name="<?php echo e($flower['name']); ?>"
                         data-price="<?php echo $flower['price']; ?>"
                         data-type="flower"
                         data-emoji="<?php echo $flower['image']; ?>">
                        <div class="item-emoji" style="background: <?php echo $flower['color']; ?>30; border-color: <?php echo $flower['color']; ?>;">
                            <?php echo $flower['image']; ?>
                        </div>
                        <div class="item-details">
                            <h4><?php echo e($flower['name']); ?></h4>
                            <p class="item-price"><?php echo number_format($flower['price'], 0, ',', '.'); ?>₫<span>/<?php echo $flower['unit']; ?></span></p>
                        </div>
                        <div class="quantity-control">
                            <button type="button" class="qty-btn minus" disabled><i class="fas fa-minus"></i></button>
                            <span class="qty-value">0</span>
                            <button type="button" class="qty-btn plus"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Step 2: Lá & Hoa Phụ -->
            <div class="selection-step" id="step-fillers">
                <div class="step-header">
                    <span class="step-number">2</span>
                    <h3>Thêm Lá & Hoa Phụ <span class="optional">(Không bắt buộc)</span></h3>
                </div>
                <div class="items-grid">
                    <?php foreach($fillers as $filler): ?>
                    <div class="item-card" data-id="<?php echo $filler['id']; ?>" 
                         data-name="<?php echo e($filler['name']); ?>"
                         data-price="<?php echo $filler['price']; ?>"
                         data-type="filler"
                         data-emoji="<?php echo $filler['image']; ?>">
                        <div class="item-emoji" style="background: <?php echo $filler['color']; ?>30; border-color: <?php echo $filler['color']; ?>;">
                            <?php echo $filler['image']; ?>
                        </div>
                        <div class="item-details">
                            <h4><?php echo e($filler['name']); ?></h4>
                            <p class="item-price"><?php echo number_format($filler['price'], 0, ',', '.'); ?>₫<span>/<?php echo $filler['unit']; ?></span></p>
                        </div>
                        <div class="quantity-control">
                            <button type="button" class="qty-btn minus" disabled><i class="fas fa-minus"></i></button>
                            <span class="qty-value">0</span>
                            <button type="button" class="qty-btn plus"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Step 3: Kiểu Gói -->
            <div class="selection-step" id="step-wrapping">
                <div class="step-header">
                    <span class="step-number">3</span>
                    <h3>Chọn Kiểu Gói <span class="required">*</span></h3>
                </div>
                <div class="items-grid wrap-grid">
                    <?php foreach($wrapping_styles as $wrap): ?>
                    <div class="item-card wrap-card" data-id="<?php echo $wrap['id']; ?>" 
                         data-name="<?php echo e($wrap['name']); ?>"
                         data-price="<?php echo $wrap['price']; ?>"
                         data-type="wrap"
                         data-emoji="<?php echo $wrap['image']; ?>">
                        <div class="item-emoji large"><?php echo $wrap['image']; ?></div>
                        <div class="item-details">
                            <h4><?php echo e($wrap['name']); ?></h4>
                            <p class="item-desc"><?php echo e($wrap['desc']); ?></p>
                            <p class="item-price"><?php echo number_format($wrap['price'], 0, ',', '.'); ?>₫</p>
                        </div>
                        <div class="check-indicator"><i class="fas fa-check-circle"></i></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Step 4: Phụ Kiện -->
            <div class="selection-step" id="step-accessories">
                <div class="step-header">
                    <span class="step-number">4</span>
                    <h3>Thêm Phụ Kiện <span class="optional">(Không bắt buộc)</span></h3>
                </div>
                <div class="items-grid">
                    <?php foreach($accessories as $acc): ?>
                    <div class="item-card" data-id="<?php echo $acc['id']; ?>" 
                         data-name="<?php echo e($acc['name']); ?>"
                         data-price="<?php echo $acc['price']; ?>"
                         data-type="accessory"
                         data-emoji="<?php echo $acc['image']; ?>">
                        <div class="item-emoji"><?php echo $acc['image']; ?></div>
                        <div class="item-details">
                            <h4><?php echo e($acc['name']); ?></h4>
                            <p class="item-price"><?php echo number_format($acc['price'], 0, ',', '.'); ?>₫</p>
                        </div>
                        <div class="quantity-control">
                            <button type="button" class="qty-btn minus" disabled><i class="fas fa-minus"></i></button>
                            <span class="qty-value">0</span>
                            <button type="button" class="qty-btn plus"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        </div>
        
        <!-- Right: Summary -->
        <div class="summary-area">
            <div class="summary-sticky">
                
                <div class="summary-card">
                    <div class="summary-header">
                        <h3><i class="fas fa-shopping-basket"></i> Bó Hoa Của Bạn</h3>
                    </div>
                    
                    <!-- Bouquet Name -->
                    <div class="name-input">
                        <label><i class="fas fa-tag"></i> Đặt tên bó hoa:</label>
                        <input type="text" id="bouquet-name" placeholder="VD: Bó hoa sinh nhật mẹ...">
                    </div>
                    
                    <!-- Visual Preview -->
                    <div class="visual-preview" id="visual-preview">
                        <div class="preview-empty">
                            <i class="fas fa-seedling"></i>
                            <p>Bó hoa của bạn sẽ hiển thị ở đây</p>
                        </div>
                    </div>
                    
                    <!-- Selected Items -->
                    <div class="selected-items">
                        <h4><i class="fas fa-list"></i> Chi tiết đã chọn:</h4>
                        <ul id="selected-list">
                            <li class="empty-msg">Chưa chọn gì</li>
                        </ul>
                    </div>
                    
                    <!-- Price Breakdown -->
                    <div class="price-breakdown">
                        <div class="price-row">
                            <span><i class="fas fa-seedling"></i> Hoa chính:</span>
                            <span id="price-flowers">0₫</span>
                        </div>
                        <div class="price-row">
                            <span><i class="fas fa-leaf"></i> Lá & Hoa phụ:</span>
                            <span id="price-fillers">0₫</span>
                        </div>
                        <div class="price-row">
                            <span><i class="fas fa-gift"></i> Kiểu gói:</span>
                            <span id="price-wrap">0₫</span>
                        </div>
                        <div class="price-row">
                            <span><i class="fas fa-star"></i> Phụ kiện:</span>
                            <span id="price-accessories">0₫</span>
                        </div>
                        <div class="price-row total">
                            <span>TỔNG CỘNG:</span>
                            <span id="price-total">0₫</span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <form action="" method="POST" id="order-form">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="bouquet_name" id="form-bouquet-name">
                        <input type="hidden" name="selected_items" id="form-selected-items">
                        <input type="hidden" name="total_price" id="form-total-price" value="0">
                        
                        <div class="action-buttons">
                            <button type="button" class="btn-reset" onclick="resetAll()">
                                <i class="fas fa-undo"></i> Làm lại
                            </button>
                            <button type="submit" name="add_to_cart" class="btn-submit" id="btn-submit" disabled>
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Tips -->
                <div class="tips-card">
                    <h4><i class="fas fa-lightbulb"></i> Mẹo hay</h4>
                    <ul>
                        <li>🌸 Bó đẹp thường có 5-12 cành hoa chính</li>
                        <li>🌿 Thêm lá xanh giúp bó hoa tươi và sang hơn</li>
                        <li>✨ Số lẻ (3, 5, 7, 9) tạo sự cân đối tự nhiên</li>
                        <li>🎨 Kết hợp 2-3 màu hoa tương đồng</li>
                    </ul>
                </div>
                
            </div>
        </div>
        
    </div>
</section>

<?php @include '../footer.php'; ?>

<script src="../js/custom-builder.js"></script>

</body>
</html>
