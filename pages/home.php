<?php
/**
 * Home Page - Enhanced Security
 * CSRF Protection, Prepared Statements, XSS Protection
 */

@include '../config.php';

$user_id = $_SESSION['user_id'] ?? null;

if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

$message = [];

// Add to wishlist
if(isset($_POST['add_to_wishlist'])){
   if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
       $message[] = 'Lỗi bảo mật!';
   } else {
       $product_id = (int)$_POST['product_id'];
       $product_name = sanitize_input($_POST['product_name']);
       $product_price = (int)$_POST['product_price'];
       $product_image = sanitize_input($_POST['product_image']);
       
       // Check if already in wishlist or cart
       $check_wishlist = db_count($conn, "SELECT * FROM wishlist WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);
       $check_cart = db_count($conn, "SELECT * FROM cart WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);

       if($check_wishlist > 0){
           $message[] = 'Đã có trong danh sách yêu thích!';
       }elseif($check_cart > 0){
           $message[] = 'Đã có trong giỏ hàng!';
       }else{
           db_insert($conn, 
               "INSERT INTO wishlist (user_id, pid, name, price, image) VALUES (?, ?, ?, ?, ?)",
               "iisis",
               [$user_id, $product_id, $product_name, $product_price, $product_image]
           );
           $message[] = 'Đã thêm vào danh sách yêu thích!';
       }
   }
}

// Add to cart
if(isset($_POST['add_to_cart'])){
   if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
       $message[] = 'Lỗi bảo mật!';
   } else {
       $product_id = (int)$_POST['product_id'];
       $product_name = sanitize_input($_POST['product_name']);
       $product_price = (int)$_POST['product_price'];
       $product_image = sanitize_input($_POST['product_image']);
       $product_quantity = max(1, (int)$_POST['product_quantity']);

       $check_cart = db_count($conn, "SELECT * FROM cart WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);

       if($check_cart > 0){
           $message[] = 'Đã có trong giỏ hàng!';
       }else{
           // Remove from wishlist if exists
           db_delete($conn, "DELETE FROM wishlist WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);
           
           // Add to cart
           db_insert($conn,
               "INSERT INTO cart (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)",
               "iisiii",
               [$user_id, $product_id, $product_name, $product_price, $product_quantity, $product_image]
           );
           $message[] = 'Đã thêm vào giỏ hàng!';
       }
   }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Trang chủ</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/style-enhanced.css">
   <link rel="stylesheet" href="../css/product-cards.css">
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

<section class="home">

   <div class="content">
      <h3>Bộ sưu tập mới</h3>
      <p>Khám phá những mẫu sản phẩm mới nhất, thiết kế độc đáo, sang trọng và phù hợp với xu hướng hiện đại.</p>
      <a href="./about.php" class="btn">tìm hiểu thêm</a>
   </div>

</section>

<section class="products">

   <h1 class="title">sản phẩm mới nhất</h1>

   <div class="box-container">

      <?php
         $select_products = db_select($conn, "SELECT * FROM products ORDER BY id DESC LIMIT 6");
         if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
      ?>
      <form action="" method="POST" class="box">
         <?php echo csrf_field(); ?>
         <a href="./view_page.php?pid=<?php echo $fetch_products['id']; ?>" class="fas fa-eye"></a>
         <div class="price"><?php echo number_format($fetch_products['price'], 0, ',', '.'); ?>₫</div>
         <img src="../assets/uploads/products/<?php echo e($fetch_products['image']); ?>" alt="" class="image">
         <?php $display_name = fix_encoding($fetch_products['name']); ?>
         <div class="name"><?php echo e($display_name); ?></div>
         <input type="number" name="product_quantity" value="1" min="1" class="qty" style="display:none;">
         <input type="hidden" name="product_id" value="<?php echo $fetch_products['id']; ?>">
         <input type="hidden" name="product_name" value="<?php echo $display_name; ?>">
         <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
         <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
         <input type="submit" value="Yêu Thích" name="add_to_wishlist" class="option-btn">
         <input type="button" value="Thêm Vào Giỏ" class="btn show-qty-btn">
         <input type="submit" value="Xác Nhận" name="add_to_cart" class="btn confirm-qty-btn" style="display:none;">
      </form>
      <?php
         }
      }else{
         echo '<p class="empty">Chưa có sản phẩm nào được thêm!</p>';
      }
      ?>

   </div>

   <div class="more-btn">
      <a href="./shop.php" class="option-btn">xem thêm</a>
   </div>

</section>


<section class="home-contact">

   <div class="content">
      <h3>Bạn có câu hỏi?</h3>
      <p>Hãy liên hệ với chúng tôi để được tư vấn và hỗ trợ nhanh nhất!</p>
      <a href="./contact.php" class="btn">liên hệ ngay</a>
   </div>

</section>

<?php @include '../footer.php'; ?>

<script src="../js/script.js"></script>

</body>
</html>



