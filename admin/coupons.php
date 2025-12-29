<?php
@include '../config.php';

$admin_id = $_SESSION['admin_id'] ?? null;
if(!$admin_id){
   header('location:../auth/login.php');
   exit;
}

// Thêm coupon
if(isset($_POST['add_coupon'])){
    $code = strtoupper(mysqli_real_escape_string($conn, $_POST['code']));
    $type = mysqli_real_escape_string($conn, $_POST['discount_type']);
    $value = floatval($_POST['discount_value']);
    $min_order = floatval($_POST['min_order']);
    $max_discount = $_POST['max_discount'] ? floatval($_POST['max_discount']) : NULL;
    $usage_limit = $_POST['usage_limit'] ? intval($_POST['usage_limit']) : NULL;
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    
    $check = mysqli_query($conn, "SELECT * FROM coupons WHERE code='$code'");
    if(mysqli_num_rows($check) > 0){
        $message[] = 'Mã coupon đã tồn tại!';
    } else {
        mysqli_query($conn, "INSERT INTO coupons (code, discount_type, discount_value, min_order, max_discount, usage_limit, start_date, end_date) 
        VALUES ('$code', '$type', '$value', '$min_order', ".($max_discount ? "'$max_discount'" : "NULL").", ".($usage_limit ? "'$usage_limit'" : "NULL").", '$start_date', '$end_date')");
        $message[] = 'Thêm mã giảm giá thành công!';
    }
}

// Xóa coupon
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM coupons WHERE id='$id'");
    header('location:admin_coupons.php');
    exit;
}

// Toggle active
if(isset($_GET['toggle'])){
    $id = $_GET['toggle'];
    mysqli_query($conn, "UPDATE coupons SET is_active = NOT is_active WHERE id='$id'");
    header('location:admin_coupons.php');
    exit;
}

$coupons = mysqli_query($conn, "SELECT * FROM coupons ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <title>Quản lý mã giảm giá</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
   .form-container {
      max-width: 800px;
      margin: 2rem auto;
      padding: 2rem;
      background: white;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
   }
   .form-container h3 {
      margin-bottom: 1.5rem;
      color: #333;
   }
   .form-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
   }
   .form-grid .box {
      padding: 1rem;
      border: 1px solid #ddd;
      border-radius: 5px;
   }
   .coupon-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 2rem;
      background: white;
   }
   .coupon-table th, .coupon-table td {
      padding: 1rem;
      border: 1px solid #ddd;
      text-align: left;
   }
   .coupon-table th {
      background: #667eea;
      color: white;
   }
   .badge {
      padding: 0.3rem 0.8rem;
      border-radius: 15px;
      font-size: 0.9rem;
      font-weight: 600;
   }
   .badge-active {
      background: #28a745;
      color: white;
   }
   .badge-inactive {
      background: #dc3545;
      color: white;
   }
   </style>
</head>
<body>

<?php @include './header.php'; ?>

<section class="form-container">
   <h3>Thêm mã giảm giá mới</h3>
   <form action="" method="POST">
      <div class="form-grid">
         <input type="text" name="code" class="box" placeholder="Mã coupon (VD: SUMMER2025)" required>
         <select name="discount_type" class="box" required>
            <option value="percentage">Phần trăm (%)</option>
            <option value="fixed">Giảm cố định (VND)</option>
         </select>
         <input type="number" step="0.01" name="discount_value" class="box" placeholder="Giá trị giảm" required>
         <input type="number" name="min_order" class="box" placeholder="Đơn tối thiểu" value="0">
         <input type="number" name="max_discount" class="box" placeholder="Giảm tối đa (optional)">
         <input type="number" name="usage_limit" class="box" placeholder="Số lần sử dụng (optional)">
         <input type="datetime-local" name="start_date" class="box" required>
         <input type="datetime-local" name="end_date" class="box" required>
      </div>
      <input type="submit" name="add_coupon" value="Thêm mã giảm giá" class="btn" style="margin-top: 1rem;">
   </form>
</section>

<?php
if(!empty($message)){
   foreach($message as $msg){
      echo '<div class="message"><span>'.$msg.'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
}
?>

<section class="products" style="padding-top: 2rem;">
   <h1 class="title">Danh sách mã giảm giá</h1>

   <table class="coupon-table">
      <thead>
         <tr>
            <th>Mã</th>
            <th>Loại</th>
            <th>Giá trị</th>
            <th>Đơn tối thiểu</th>
            <th>Đã dùng</th>
            <th>Trạng thái</th>
            <th>Hạn sử dụng</th>
            <th>Thao tác</th>
         </tr>
      </thead>
      <tbody>
         <?php while($coupon = mysqli_fetch_assoc($coupons)): ?>
         <tr>
            <td><strong><?php echo $coupon['code']; ?></strong></td>
            <td><?php echo $coupon['discount_type'] == 'percentage' ? 'Phần trăm' : 'Cố định'; ?></td>
            <td>
               <?php 
               if($coupon['discount_type'] == 'percentage'){
                  echo $coupon['discount_value'] . '%';
               } else {
                  echo number_format($coupon['discount_value'], 0, ',', '.') . 'đ';
               }
               ?>
            </td>
            <td><?php echo number_format($coupon['min_order'], 0, ',', '.'); ?>đ</td>
            <td><?php echo $coupon['used_count']; ?>/<?php echo $coupon['usage_limit'] ?? '∞'; ?></td>
            <td>
               <span class="badge <?php echo $coupon['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                  <?php echo $coupon['is_active'] ? 'Hoạt động' : 'Tạm dừng'; ?>
               </span>
            </td>
            <td><?php echo date('d/m/Y', strtotime($coupon['end_date'])); ?></td>
            <td>
               <a href="coupons.php?toggle=<?php echo $coupon['id']; ?>" class="option-btn">
                  <?php echo $coupon['is_active'] ? 'Tắt' : 'Bật'; ?>
               </a>
               <a href="coupons.php?delete=<?php echo $coupon['id']; ?>" class="delete-btn" onclick="return confirm('Xóa mã này?');">Xóa</a>
            </td>
         </tr>
         <?php endwhile; ?>
      </tbody>
   </table>
</section>

</body>
</html>

