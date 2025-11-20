<?php
@include '../config.php';
@include '../includes/email_service.php';

if (isset($_POST['submit'])) {
   $email = sanitize_input($_POST['email']);
   $token = bin2hex(random_bytes(50));
   $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour

   // Check if user exists
   $user = db_fetch_one($conn, "SELECT id, name FROM users WHERE email = ?", "s", [$email]);
   
   if ($user) {
      // Update reset token and expiry
      db_update($conn, 
         "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?",
         "sss",
         [$token, $token_expiry, $email]
      );
      
      // Send password reset email
      $email_sent = send_password_reset_email($email, $user['name'], $token);

      echo "<div class='message'>
               <span>" . ($email_sent ? 'Link d?t l?i m?t kh?u d� du?c g?i d?n email c?a b?n! Vui l�ng ki?m tra h?p thu.' : 'C� l?i khi g?i email. Vui l�ng th? l?i sau.') . "</span>
               <i class='fas fa-times' onclick='this.parentElement.remove();'></i>
            </div>";
   } else {
      echo "<div class='message'>
               <span>Email kh�ng t?n t?i trong h? th?ng!</span>
               <i class='fas fa-times' onclick='this.parentElement.remove();'></i>
            </div>";
   }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <title>Qu�n m?t kh?u</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<section class="form-container">
   <form action="" method="post">
      <h3>Qu�n m?t kh?u</h3>
      <input type="email" name="email" class="box" placeholder="Nh?p email d� dang k�" required>
      <input type="submit" name="submit" class="btn" value="G?i link d?t l?i">
      <p><a href="login.php">Quay l?i dang nh?p</a></p>
   </form>
</section>

</body>
</html>

