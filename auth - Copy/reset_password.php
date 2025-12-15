<?php
@include '../config.php';

$token = $_GET['token'] ?? '';

if (isset($_POST['submit'])) {
   $new_pass = md5($_POST['password']);
   $cpass = md5($_POST['cpassword']);

   if ($new_pass != $cpass) {
      echo "<div class='message'>
               <span>M?t kh?u kh�ng kh?p!</span>
               <i class='fas fa-times' onclick='this.parentElement.remove();'></i>
            </div>";
   } else {
      $check = mysqli_query($conn, "SELECT * FROM users WHERE reset_token='$token'");
      if (mysqli_num_rows($check) > 0) {
         mysqli_query($conn, "UPDATE users SET password='$new_pass', reset_token=NULL WHERE reset_token='$token'");
         echo "<div class='message'>
                  <span>�?i m?t kh?u th�nh c�ng! <a href='login.php'>�ang nh?p ngay</a></span>
                  <i class='fas fa-times' onclick='this.parentElement.remove();'></i>
               </div>";
      } else {
         echo "<div class='message'>
                  <span>Token kh�ng h?p l?!</span>
                  <i class='fas fa-times' onclick='this.parentElement.remove();'></i>
               </div>";
      }
   }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <title>�?t l?i m?t kh?u</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<section class="form-container">
   <form action="" method="post">
      <h3>�?t l?i m?t kh?u</h3>
      <input type="password" name="password" class="box" placeholder="M?t kh?u m?i" required>
      <input type="password" name="cpassword" class="box" placeholder="Nh?p l?i m?t kh?u" required>
      <input type="submit" name="submit" class="btn" value="X�c nh?n d?i m?t kh?u">
      <p><a href="login.php">Quay l?i dang nh?p</a></p>
   </form>
</section>

</body>
</html>

