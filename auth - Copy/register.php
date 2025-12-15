<?php
/**
 * Register Page - Enhanced Security
 * Password hashing, CSRF protection, Validation, Prepared statements
 */

@include '../config.php';
@include '../includes/email_service.php';

$message = [];

if(isset($_POST['submit'])){
    // CSRF Token verification
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $message[] = 'Lỗi bảo mật. Vui lòng thử lại!';
    } else {
        // Sanitize input
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $password = $_POST['pass'];
        $confirm_password = $_POST['cpass'];
        
        // Validation
        $errors = [];
        
        if (strlen($name) < 3) {
            $errors[] = 'Tên phải có ít nhất 3 ký tự!';
        }
        
        if (!validate_email($email)) {
            $errors[] = 'Email không hợp lệ!';
        }
        
        if (strlen($password) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự!';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Mật khẩu xác nhận không khớp!';
        }
        
        if (empty($errors)) {
            // Kiểm tra email đã tồn tại
            $existing_user = db_fetch_one($conn, "SELECT id FROM users WHERE email = ?", "s", [$email]);
            
            if ($existing_user) {
                $message[] = 'Email này đã được đăng ký!';
            } else {
                // Hash password với bcrypt
                $hashed_password = hash_password($password);
                $user_type = 'user';
                
                // Insert với prepared statement
                $insert_id = db_insert(
                    $conn, 
                    "INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)",
                    "ssss",
                    [$name, $email, $hashed_password, $user_type]
                );
                
                if ($insert_id) {
                    // Send welcome email (không block nếu email fail)
                    @send_welcome_email($email, $name);
                    $message[] = 'Đăng ký thành công! Đang chuyển đến trang đăng nhập...';
                    header('refresh:2;url=login.php');
                } else {
                    $message[] = 'Lỗi đăng ký. Vui lòng thử lại!';
                    error_log('Register error: db_insert failed for email ' . $email);
                }
            }
        } else {
            $message = array_merge($message, $errors);
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
   <title>�ang k�</title>

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
?>
   
<section class="form-container">

   <form action="" method="post">
      <h3>Đăng ký tài khoản</h3>
      <?php echo csrf_field(); ?>
      <input type="text" name="name" class="box" placeholder="Nhập tên người dùng" required minlength="3">
      <input type="email" name="email" class="box" placeholder="Nhập email của bạn" required>
      <input type="password" name="pass" class="box" placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)" required minlength="6">
      <input type="password" name="cpass" class="box" placeholder="Xác nhận mật khẩu" required minlength="6">
      <input type="submit" class="btn" name="submit" value="Đăng ký">
      <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
   </form>

</section>

</body>
</html>


