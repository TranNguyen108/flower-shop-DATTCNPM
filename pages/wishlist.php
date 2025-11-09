<?php
/**
 * Wishlist Page - Enhanced Security
 * Prepared Statements, CSRF Protection, Ownership Verification
 */

@include '../config.php';

$user_id = $_SESSION['user_id'] ?? null;

if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

$message = [];

// Add to cart from wishlist
if(isset($_POST['add_to_cart'])){
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message[] = 'Lỗi bảo mật!';
    } else {
        $product_id = (int)$_POST['product_id'];
        $product_name = sanitize_input($_POST['product_name']);
        $product_price = (int)$_POST['product_price'];
        $product_image = sanitize_input($_POST['product_image']);
        $product_quantity = 1;

        // Check if already in cart
        $check_cart = db_count($conn, "SELECT * FROM cart WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);

        if($check_cart > 0){
            $message[] = 'Sản phẩm đã có trong giỏ hàng';
        } else {
            // Remove from wishlist
            db_delete($conn, "DELETE FROM wishlist WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);
            
            // Add to cart
            db_insert($conn, 
                "INSERT INTO cart (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)",
                "iisiii",
                [$user_id, $product_id, $product_name, $product_price, $product_quantity, $product_image]
            );
            $message[] = 'Đã thêm sản phẩm vào giỏ hàng';
        }
    }
}

// Delete one item
if(isset($_GET['delete'])){
    $delete_id = (int)$_GET['delete'];
    // Verify ownership
    $check = db_fetch_one($conn, "SELECT id FROM wishlist WHERE id = ? AND user_id = ?", "ii", [$delete_id, $user_id]);
    if ($check) {
        db_delete($conn, "DELETE FROM wishlist WHERE id = ? AND user_id = ?", "ii", [$delete_id, $user_id]);
        $message[] = 'Đã xóa sản phẩm khỏi danh sách yêu thích!';
    }
    header('location:wishlist.php');
    exit;
}

// Delete all items
if(isset($_GET['delete_all'])){
    db_delete($conn, "DELETE FROM wishlist WHERE user_id = ?", "i", [$user_id]);
    $message[] = 'Đã xóa tất cả sản phẩm!';
    header('location:wishlist.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách yêu thích</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/style-enhanced.css">
   <link rel="stylesheet" href="../css/product-cards.css">

</head>
<body>
   
<?php @include '../header.php'; ?>

<?php
// Hiển thị thông báo nếu có
if(isset($message)){
   foreach($message as $msg){
      echo '<div class="message"><span>'.$msg.'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
}
?>

<section class="heading">
    <h3>Danh sách yêu thích của bạn</h3>
    <p> <a href="./home.php">Trang chủ</a> / Yêu thích </p>
</section>

<section class="wishlist">

    <h1 class="title">Sản phẩm đã thêm</h1>

    <div class="box-container">

    <?php
        $grand_total = 0;
        $select_wishlist = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE user_id = '$user_id'") or die('query failed');
        if(mysqli_num_rows($select_wishlist) > 0){
            while($fetch_wishlist = mysqli_fetch_assoc($select_wishlist)){
    ?>
    <form action="" method="POST" class="box">
        <a href="./wishlist.php?delete=<?php echo $fetch_wishlist['id']; ?>" class="fas fa-times" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi danh sách yêu thích?');"></a>
        <a href="./view_page.php?pid=<?php echo $fetch_wishlist['pid']; ?>" class="fas fa-eye" title="Xem chi ti?t"></a>
        <img src="../assets/uploads/products/<?php echo $fetch_wishlist['image']; ?>" alt="" class="image">
        <div class="name"><?php echo $fetch_wishlist['name']; ?></div>
         <div class="price">Giá: <?php echo number_format($fetch_wishlist['price'], 0, ',', '.'); ?>₫</div>
        <input type="hidden" name="product_id" value="<?php echo $fetch_wishlist['pid']; ?>">
        <input type="hidden" name="product_name" value="<?php echo $fetch_wishlist['name']; ?>">
        <input type="hidden" name="product_price" value="<?php echo $fetch_wishlist['price']; ?>">
        <input type="hidden" name="product_image" value="<?php echo $fetch_wishlist['image']; ?>">
        <input type="submit" value="Thêm vào giỏ hàng" name="add_to_cart" class="btn">
        
    </form>
    <?php
    $grand_total += $fetch_wishlist['price'];
        }
    }else{
        echo '<p class="empty">Danh sách yêu thích của bạn đang trống</p>';
    }
    ?>
    </div>

    <div class="wishlist-total">
        <p>Tổng giá trị : <span><?php echo number_format($grand_total, 0, ',', '.'); ?>₫</span></p>
        <a href="./shop.php" class="option-btn">Tiếp tục mua sắm</a>
        <a href="./wishlist.php?delete_all" class="delete-btn <?php echo ($grand_total > 0)?'':'disabled' ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa hết danh sách yêu thích?');">Xóa tất cả</a>
    </div>

</section>

<?php @include '../footer.php'; ?>

<script src="../js/script.js"></script>

</body>
</html>



