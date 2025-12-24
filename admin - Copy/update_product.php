<?php

@include '../config.php';


$admin_id = $_SESSION['admin_id'] ?? null;

if(!isset($admin_id)){
   header('location:../auth/login.php');
   exit;
}

if(isset($_POST['update_product'])){

   $update_p_id = $_POST['update_p_id'];
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $price = mysqli_real_escape_string($conn, $_POST['price']);
   $details = mysqli_real_escape_string($conn, $_POST['details']);

   mysqli_query($conn, "UPDATE `products` SET name = '$name', details = '$details', price = '$price' WHERE id = '$update_p_id'") or die('Truy v?n th?t b?i');

   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = '../../assets/uploads/products/'.$image;  // s?a l?i bi?n $image_folter th?nh $image_folder
   $old_image = $_POST['update_p_image'];
   
   if(!empty($image)){
      if($image_size > 2000000){
         $message[] = 'K?ch thu?c file ?nh qu? l?n!';
      }else{
         mysqli_query($conn, "UPDATE `products` SET image = '$image' WHERE id = '$update_p_id'") or die('Truy v?n th?t b?i');
         move_uploaded_file($image_tmp_name, $image_folder);
         unlink('../../assets/uploads/products/'.$old_image);
         $message[] = 'C?p nh?t ?nh th?nh c?ng!';
      }
   }

   $message[] = 'C?p nh?t s?n ph?m th?nh c?ng!';

}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>C?p nh?t s?n ph?m</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>
   
<?php @include './header.php'; ?>

<section class="update-product">

<?php

   $update_id = (int)$_GET['update'];
   if(!verify_csrf_token($_GET['token'] ?? '')){
      die('Y?u c?u kh?ng h?p l?');
   }
   
   $products = db_select("SELECT * FROM `products` WHERE id = ?", [$update_id]);
   if(!empty($products)){
      foreach($products as $fetch_products){
?>

<form action="" method="post" enctype="multipart/form-data">
   <?php echo csrf_field(); ?>
   <img src="../../assets/uploads/products/<?php echo e($fetch_products['image']); ?>" class="image" alt="<?php echo e($fetch_products['name']); ?>">
   <input type="hidden" value="<?php echo (int)$fetch_products['id']; ?>" name="update_p_id">
   <input type="hidden" value="<?php echo e($fetch_products['image']); ?>" name="update_p_image">
   <input type="text" class="box" value="<?php echo e($fetch_products['name']); ?>" required placeholder="C?p nh?t t?n s?n ph?m" name="name">
   <input type="number" min="0" step="0.01" class="box" value="<?php echo (float)$fetch_products['price']; ?>" required placeholder="C?p nh?t gi? s?n ph?m" name="price">
   <textarea name="details" class="box" required placeholder="C?p nh?t chi ti?t s?n ph?m" cols="30" rows="10"><?php echo e($fetch_products['details']); ?></textarea>
   <input type="file" accept="image/jpg, image/jpeg, image/png" class="box" name="image">
   <input type="submit" value="C?p nh?t s?n ph?m" name="update_product" class="btn">
   <a href="products.php" class="option-btn">Quay l?i</a>
</form>

<?php
      }
   }else{
      echo '<p class="empty">Chua ch?n s?n ph?m d? c?p nh?t</p>';
   }
?>

</section>

<script src="../../js/admin_script.js"></script>

</body>
</html>

