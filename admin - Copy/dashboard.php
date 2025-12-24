<?php
header('Content-Type: text/html; charset=UTF-8');
@include '../config.php';

$admin_id = $_SESSION['admin_id'] ?? null;

if(!isset($admin_id)){
   header('location:../auth/login.php');
   exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>B?ng Qu?n Tr?</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <link rel="stylesheet" href="../css/admin-enhanced.css">
</head>
<body>
   
<?php @include './header.php'; ?>

<section class="dashboard">

   <h1 class="title">Bảng Điều Khiển</h1>

   <div class="box-container">
      
<div class="box">
   <?php
      $query_processing = mysqli_query($conn, "SELECT * FROM `orders` WHERE delivery_status = 'Đang xử lý'") or die('Không thể truy vấn!');
      $count_processing = mysqli_num_rows($query_processing);
   ?>
   <h3><?php echo $count_processing; ?></h3>
   <p>Đơn đang xử lý</p>
</div>

<div class="box">
   <?php
      $query_shipping = mysqli_query($conn, "SELECT * FROM `orders` WHERE delivery_status = 'Đang giao'") or die('Không thể truy vấn!');
      $count_shipping = mysqli_num_rows($query_shipping);
   ?>
   <h3><?php echo $count_shipping; ?></h3>
   <p>Đơn đang giao</p>
</div>

<div class="box">
   <?php
      $query_delivered = mysqli_query($conn, "SELECT * FROM `orders` WHERE delivery_status = 'Đã giao'") or die('Không thể truy vấn!');
      $count_delivered = mysqli_num_rows($query_delivered);
   ?>
   <h3><?php echo $count_delivered; ?></h3>
   <p>Đơn đã giao</p>
</div>

<div class="box">
   <?php
      $query_cancelled = mysqli_query($conn, "SELECT * FROM `orders` WHERE delivery_status = 'Đã hủy'") or die('Không thể truy vấn!');
      $count_cancelled = mysqli_num_rows($query_cancelled);
   ?>
   <h3><?php echo $count_cancelled; ?></h3>
   <p>Đơn đã hủy</p>
</div>

      <div class="box">
         <?php
            $total_pendings = 0;
            $select_pendings = mysqli_query($conn, "SELECT * FROM `orders` WHERE payment_status = 'pending'") or die('Không thể truy vấn dữ liệu!');
            while($fetch_pendings = mysqli_fetch_assoc($select_pendings)){
               $total_pendings += $fetch_pendings['total_price'];
            };
         ?>
         <h3><?php echo number_format($total_pendings, 0, ',', '.'); ?>đ</h3>
         <p>Đơn chờ thanh toán</p>
      </div>

      <div class="box">
         <?php
            $total_completes = 0;
            $select_completes = mysqli_query($conn, "SELECT * FROM `orders` WHERE payment_status = 'completed'") or die('Không thể truy vấn dữ liệu!');
            while($fetch_completes = mysqli_fetch_assoc($select_completes)){
               $total_completes += $fetch_completes['total_price'];
            };
         ?>
         <h3><?php echo number_format($total_completes, 0, ',', '.'); ?>đ</h3>
         <p>Đơn đã thanh toán</p>
      </div>

      <div class="box">
         <?php
            $select_orders = mysqli_query($conn, "SELECT * FROM `orders`") or die('Không thể truy vấn dữ liệu!');
            $number_of_orders = mysqli_num_rows($select_orders);
         ?>
         <h3><?php echo $number_of_orders; ?></h3>
         <p>Tổng số đơn hàng</p>
      </div>

      <div class="box">
         <?php
            $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('Không thể truy vấn dữ liệu!');
            $number_of_products = mysqli_num_rows($select_products);
         ?>
         <h3><?php echo $number_of_products; ?></h3>
         <p>Sản phẩm đã thêm</p>
      </div>

      <div class="box">
         <?php
            $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE user_type = 'user'") or die('Không thể truy vấn dữ liệu!');
            $number_of_users = mysqli_num_rows($select_users);
         ?>
         <h3><?php echo $number_of_users; ?></h3>
         <p>Người dùng thường</p>
      </div>

      <div class="box">
         <?php
            $select_admin = mysqli_query($conn, "SELECT * FROM `users` WHERE user_type = 'admin'") or die('Không thể truy vấn dữ liệu!');
            $number_of_admin = mysqli_num_rows($select_admin);
         ?>
         <h3><?php echo $number_of_admin; ?></h3>
         <p>Quản trị viên</p>
      </div>

      <div class="box">
         <?php
            $select_account = mysqli_query($conn, "SELECT * FROM `users`") or die('Không thể truy vấn dữ liệu!');
            $number_of_account = mysqli_num_rows($select_account);
         ?>
         <h3><?php echo $number_of_account; ?></h3>
         <p>Tổng tài khoản</p>
      </div>

      <div class="box">
         <?php
            $select_messages = mysqli_query($conn, "SELECT * FROM `message`") or die('Không thể truy vấn dữ liệu!');
            $number_of_messages = mysqli_num_rows($select_messages);
         ?>
         <h3><?php echo $number_of_messages; ?></h3>
         <p>Tin nhắn mới</p>
      </div>

   </div>

</section>

<script src="../../js/admin_script.js"></script>

</body>
</html>

