<?php
@include '../config.php';

$user_id = $_SESSION['user_id'] ?? null;
if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

// Thêm vào wishlist
if(isset($_POST['add_to_wishlist'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message[] = 'Yêu cầu không hợp lệ';
    } else {
        $pid = (int)$_POST['product_id'];
        $name = sanitize_input($_POST['product_name']);
        $price = (float)$_POST['product_price'];
        $image = sanitize_input($_POST['product_image']);

        $check_wishlist = db_count($conn, "SELECT * FROM wishlist WHERE user_id = ? AND pid = ?", "ii", [$user_id, $pid]);
        $check_cart = db_count($conn, "SELECT * FROM cart WHERE user_id = ? AND pid = ?", "ii", [$user_id, $pid]);

        if($check_wishlist > 0){
            $message[] = 'Sản phẩm đã có trong danh sách yêu thích';
        }elseif($check_cart > 0){
            $message[] = 'Sản phẩm đã có trong giỏ hàng';
        }else{
            db_insert($conn, "INSERT INTO wishlist (user_id, pid, name, price, image) VALUES (?, ?, ?, ?, ?)", 
                     "iisis", [$user_id, $pid, $name, $price, $image]);
            $message[] = 'Đã thêm sản phẩm vào danh sách yêu thích';
        }
    }
}

// Gửi đánh giá
if(isset($_POST['submit_review'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message[] = 'Yêu cầu không hợp lệ';
    } else {
        $pid = (int)$_POST['product_id'];
        $user_name = sanitize_input($_POST['user_name']);
        $rating = max(1, min(5, (int)$_POST['rating']));
        $comment = sanitize_input($_POST['comment']);

        $product = db_fetch_one($conn, "SELECT name FROM products WHERE id = ?", "i", [$pid]);
        if($product){
            $product_name = $product['name'];
            $check_order = db_count($conn, "SELECT * FROM orders WHERE user_id = ? AND total_products LIKE ?", 
                                   "is", [$user_id, "%$product_name%"]);

            if($check_order > 0){
                db_insert($conn, "INSERT INTO reviews (product_id, user_name, rating, comment) VALUES (?, ?, ?, ?)", 
                         "isis", [$pid, $user_name, $rating, $comment]);
                $message[] = 'Đã gửi đánh giá thành công!';
            } else {
                $message[] = 'Bạn phải mua sản phẩm này mới được đánh giá!';
            }
        }
    }
}

// Thêm vào giỏ hàng
if(isset($_POST['add_to_cart'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message[] = 'Yêu cầu không hợp lệ';
    } else {
        $pid = (int)$_POST['product_id'];
        $name = sanitize_input($_POST['product_name']);
        $price = (float)$_POST['product_price'];
        $image = sanitize_input($_POST['product_image']);
        $qty = max(1, (int)$_POST['product_quantity']);

        $check_cart = db_count($conn, "SELECT * FROM cart WHERE user_id = ? AND pid = ?", "ii", [$user_id, $pid]);

        if($check_cart > 0){
            $message[] = 'Sản phẩm đã có trong giỏ hàng';
        }else{
            db_delete($conn, "DELETE FROM wishlist WHERE user_id = ? AND pid = ?", "ii", [$user_id, $pid]);
            db_insert($conn, "INSERT INTO cart (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)", 
                     "iisiss", [$user_id, $pid, $name, $price, $qty, $image]);
            $message[] = 'Đã thêm sản phẩm vào giỏ hàng';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Xem chi tiết sản phẩm</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
<style>
.quick-view {
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
}

.quick-view .title {
    text-align: center;
    margin-bottom: 2rem;
    font-size: 2.5rem;
    color: #333;
}

.quick-view form {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.quick-view form .image {
    width: 100%;
    max-height: 400px;
    object-fit: contain;
    border-radius: 10px;
    margin-bottom: 1.5rem;
}

.quick-view form .name {
    font-size: 2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 1rem;
    text-align: center;
}

.quick-view form .price {
    font-size: 2.2rem;
    color: #ff6b9d;
    font-weight: 700;
    text-align: center;
    margin-bottom: 1rem;
}

.quick-view form .details {
    font-size: 1.4rem;
    color: #666;
    line-height: 1.8;
    margin-bottom: 1.5rem;
    text-align: center;
}

.quick-view form .qty {
    width: 100%;
    padding: 1rem;
    font-size: 1.6rem;
    border: 2px solid #ddd;
    border-radius: 8px;
    margin-bottom: 1rem;
    text-align: center;
}

.quick-view form .btn,
.quick-view form .option-btn {
    width: 100%;
    padding: 1.2rem;
    font-size: 1.6rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 0.8rem;
    transition: all 0.3s;
}

.quick-view form .btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.quick-view form .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.quick-view form .option-btn {
    background: white;
    color: #667eea;
    border: 2px solid #667eea;
}

.quick-view form .option-btn:hover {
    background: #667eea;
    color: white;
}

/* Review section */
.quick-view .review-form, 
.quick-view .reviews-list {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 1.5rem;
    margin-top: 2rem;
    background: #fafafa;
}

.quick-view .review-form h3, 
.quick-view .reviews-list h3 {
    font-size: 1.8rem;
    margin-bottom: 1rem;
    color: #333;
}

.quick-view .review-form input[type="text"],
.quick-view .review-form textarea,
.quick-view .review-form select {
    width: 100%;
    font-size: 1.4rem;
    padding: 1rem;
    margin-top: 0.5rem;
    margin-bottom: 1rem;
    border: 1px solid #ccc;
    border-radius: 8px;
}

.quick-view .review-form input[type="submit"] {
    background: #ff6b9d;
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 8px;
    font-size: 1.4rem;
    cursor: pointer;
}

.quick-view .reviews-list .review-item {
    border-bottom: 1px solid #eee;
    padding: 1rem 0;
}

.quick-view .reviews-list .review-item:last-child {
    border-bottom: none;
}

.quick-view .reviews-list strong {
    font-size: 1.4rem;
    color: #333;
}

.quick-view .reviews-list .stars {
    color: #ffc107;
}

.quick-view .reviews-list em {
    display: block;
    margin-top: 0.5rem;
    color: #666;
    font-size: 1.3rem;
}

.quick-view .reviews-list .admin-reply {
    margin-top: 0.8rem;
    padding: 0.8rem 1rem;
    background: #e3f2fd;
    border-left: 3px solid #2196f3;
    border-radius: 5px;
    font-size: 1.3rem;
}

.quick-view .more-btn {
    text-align: center;
    margin-top: 2rem;
}

.empty {
    text-align: center;
    font-size: 1.6rem;
    color: #666;
    padding: 2rem;
}
</style>
</head>
<body>

<?php @include '../header.php'; ?>

<?php
if(isset($message)){
   foreach($message as $msg){
      echo '<div class="message"><span>'.e($msg).'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
}
?>

<section class="quick-view">
<h1 class="title">Chi tiết sản phẩm</h1>
<?php
if(isset($_GET['pid'])){
    $pid = (int)$_GET['pid'];
    $product = db_fetch_one($conn, "SELECT * FROM products WHERE id = ?", "i", [$pid]);
    
    if($product){
?>
<form action="" method="POST">
   <?php echo csrf_field(); ?>
   <img src="../assets/uploads/products/<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>" class="image">
   <div class="name"><?php echo e($product['name']); ?></div>
   <div class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</div>
   <div class="details"><?php echo e($product['details']); ?></div>
   
   <input type="number" name="product_quantity" value="1" min="1" class="qty">
   <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
   <input type="hidden" name="product_name" value="<?php echo e($product['name']); ?>">
   <input type="hidden" name="product_price" value="<?php echo (float)$product['price']; ?>">
   <input type="hidden" name="product_image" value="<?php echo e($product['image']); ?>">
   
   <input type="submit" value="Thêm vào yêu thích" name="add_to_wishlist" class="option-btn">
   <input type="submit" value="Thêm vào giỏ hàng" name="add_to_cart" class="btn">
</form>

<!-- Review Form -->
<div class="review-form">
    <h3>Gửi đánh giá sản phẩm</h3>
    <form action="" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
        <input type="text" name="user_name" placeholder="Tên của bạn" required>
        <label>Đánh giá sao:</label>
        <select name="rating" required>
            <option value="5">⭐⭐⭐⭐⭐ Tuyệt vời</option>
            <option value="4">⭐⭐⭐⭐ Tốt</option>
            <option value="3">⭐⭐⭐ Bình thường</option>
            <option value="2">⭐⭐ Tệ</option>
            <option value="1">⭐ Rất tệ</option>
        </select>
        <textarea name="comment" placeholder="Nhận xét của bạn..." rows="4" required></textarea>
        <input type="submit" name="submit_review" value="Gửi đánh giá">
    </form>
</div>

<!-- Reviews List -->
<div class="reviews-list">
    <h3>Đánh giá từ khách hàng</h3>
    <?php
    $reviews = db_select_array($conn, "SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC", "i", [$pid]);
    if(!empty($reviews)){
        foreach($reviews as $review){
            echo '<div class="review-item">';
            echo '<strong>'.e($review['user_name']).'</strong> ';
            echo '<span class="stars">'.str_repeat("⭐", $review['rating']).'</span>';
            echo '<em>'.e($review['comment']).'</em>';
            if(!empty($review['admin_reply'])){
                echo '<div class="admin-reply"><strong>Admin trả lời:</strong> '.e($review['admin_reply']).'</div>';
            }
            echo '</div>';
        }
    } else {
        echo '<p class="empty">Chưa có đánh giá nào.</p>';
    }
    ?>
</div>

<?php
    } else {
        echo '<p class="empty">Không tìm thấy sản phẩm!</p>';
    }
} else {
    echo '<p class="empty">Vui lòng chọn sản phẩm!</p>';
}
?>

<div class="more-btn">
    <a href="./shop.php" class="option-btn">Tiếp tục mua sắm</a>
</div>
</section>

<?php @include '../footer.php'; ?>
<script src="../js/script.js"></script>
</body>
</html>
