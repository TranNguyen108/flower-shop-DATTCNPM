<?php
@include '../config.php';

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:../auth/login.php');
}

if(isset($_POST['add_to_wishlist'])){
   if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
      $message[] = 'Lỗi bảo mật!';
   } else {
      $product_id = (int)($_POST['product_id'] ?? 0);
      $product_name = sanitize_input($_POST['product_name'] ?? '');
      $product_price = (int)($_POST['product_price'] ?? 0);
      $product_image = sanitize_input($_POST['product_image'] ?? '');
       
      $check_wishlist = db_count($conn, "SELECT * FROM `wishlist` WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);
      $check_cart = db_count($conn, "SELECT * FROM `cart` WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);

      if($check_wishlist > 0){
         $message[] = 'Đã có trong danh sách yêu thích!';
      }elseif($check_cart > 0){
         $message[] = 'Đã có trong giỏ hàng!';
      }else{
         db_insert($conn, "INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?, ?, ?, ?, ?)", "iisis", [$user_id, $product_id, $product_name, $product_price, $product_image]);
         $message[] = 'Đã thêm vào danh sách yêu thích!';
      }
   }
}

if(isset($_POST['add_to_cart'])){
   if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
       $message[] = 'Lỗi bảo mật!';
   } else {
       $product_id = (int)($_POST['product_id'] ?? 0);
       $product_name = sanitize_input($_POST['product_name'] ?? '');
       $product_price = (int)($_POST['product_price'] ?? 0);
       $product_image = sanitize_input($_POST['product_image'] ?? '');
       $product_quantity = max(1, (int)($_POST['product_quantity'] ?? 1));

       $check_cart = db_count($conn, "SELECT * FROM `cart` WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);

       if($check_cart > 0){
          $message[] = 'Đã có trong giỏ hàng!';
       }else{
          $check_wishlist = db_count($conn, "SELECT * FROM `wishlist` WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);
          if($check_wishlist > 0){
             db_delete($conn, "DELETE FROM `wishlist` WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);
          }
          db_insert($conn, "INSERT INTO `cart`(user_id, pid, name, price, image, quantity) VALUES(?, ?, ?, ?, ?, ?)", "iisiii", [$user_id, $product_id, $product_name, $product_price, $product_image, $product_quantity]);
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
   <title>Sản phẩm bán chạy nhất</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/style-enhanced.css">
   <link rel="stylesheet" href="../css/product-cards.css">
</head>
<body>
   
<?php @include '../header.php'; ?>

<?php
if(isset($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.$msg.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="products">

   <h1 class="title">🔥 Sản Phẩm Bán Chạy Nhất 🔥</h1>

   <div class="box-container">

   <?php
   // Query products with highest sales from completed orders
   $select_products = mysqli_query($conn, "
      SELECT p.*, COALESCE(SUM(oi.quantity), 0) as total_sold
      FROM products p
      LEFT JOIN order_items oi ON p.id = oi.product_id
      LEFT JOIN orders o ON oi.order_id = o.id AND o.delivery_status = 'Đã giao'
      GROUP BY p.id
      ORDER BY total_sold DESC
      LIMIT 9
   ");
   
   if(mysqli_num_rows($select_products) > 0){
      while($fetch_product = mysqli_fetch_assoc($select_products)){
   ?>
   <form action="" method="POST" class="box">
      <div class="price"><?php echo number_format($fetch_product['price'], 0, ',', '.'); ?>₫</div>
      <a href="./view_page.php?pid=<?php echo $fetch_product['id']; ?>" class="fas fa-eye"></a>
      <img class="image" src="../assets/uploads/products/<?php echo e($fetch_product['image']); ?>" alt="<?php echo e(fix_encoding($fetch_product['name'])); ?>">
      <?php $display_name = fix_encoding($fetch_product['name']); ?>
      <div class="name"><?php echo e($display_name); ?></div>
      <input type="hidden" name="product_id" value="<?php echo $fetch_product['id']; ?>">
      <input type="hidden" name="product_name" value="<?php echo $display_name; ?>">
      <input type="hidden" name="product_price" value="<?php echo $fetch_product['price']; ?>">
      <input type="hidden" name="product_image" value="<?php echo $fetch_product['image']; ?>">
      <?php echo csrf_field(); ?>
      <input type="number" name="product_quantity" value="1" min="1" class="qty" style="display:none;">
      <input type="submit" value="Yêu Thích" name="add_to_wishlist" class="option-btn">
      <input type="button" value="Thêm Vào Giỏ" class="btn show-qty-btn">
      <input type="submit" value="Xác Nhận" name="add_to_cart" class="btn confirm-qty-btn" style="display:none;">
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">Chưa có sản phẩm nào được bán!</p>';
   }
   ?>

   </div>

</section>

<?php @include '../footer.php'; ?>

<script src="../js/script.js"></script>

</body>
</html>


