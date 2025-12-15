<?php
/**
 * Login Page - Enhanced Security
 * Password hashing, CSRF protection, Prepared statements
 */

@include '../config.php';

$message = [];

if(isset($_POST['submit'])){
    // CSRF Token verification
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $message[] = 'Lỗi bảo mật. Vui lòng thử lại!';
    } else {
        // Sanitize input
        $email = sanitize_input($_POST['email']);
        $password = $_POST['pass'];
        
        // Validate email
        if (!validate_email($email)) {
            $message[] = 'Email không hợp lệ!';
        } else {
            // Sử dụng prepared statement
            $user = db_fetch_one($conn, "SELECT * FROM users WHERE email = ?", "s", [$email]);
            
            if ($user && verify_password($password, $user['password'])) {
                // Password đúng - Regenerate session ID (chống session fixation)
                session_regenerate_id(true);
                
                if($user['user_type'] == 'admin'){
                    $_SESSION['admin_name'] = $user['name'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['initiated'] = true;
                    header('location: ../admin/dashboard.php');
                    exit;
                } elseif($user['user_type'] == 'user'){
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = 'user';
                    $_SESSION['initiated'] = true;
                    header('location: ../pages/home.php');
                    exit;
                }
            } else {
                $message[] = 'Email hoặc mật khẩu không đúng!';
            }
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
    <title>Đăng nhập</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php
if(isset($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.e($msg).'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

// Hiển thị thông báo session timeout
if(isset($_GET['timeout'])){
   echo '
   <div class="message">
      <span>Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại!</span>
      <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
   </div>
   ';
}
?>
   
<section class="form-container">

   <form action="" method="post">
      <h3>Đăng nhập</h3>
      <?php echo csrf_field(); ?>
      <input type="email" name="email" class="box" placeholder="Nhập email của bạn" required>
      <input type="password" name="pass" class="box" placeholder="Nhập mật khẩu của bạn" required minlength="6">
      <input type="submit" class="btn" name="submit" value="Đăng nhập">
      <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
      <p><a href="forgot_password.php">Quên mật khẩu?</a></p>
   </form>

</section>

</body>
</html>


