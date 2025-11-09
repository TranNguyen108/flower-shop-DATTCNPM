<?php

@include '../config.php';

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

// Gửi tin nhắn
if(isset($_POST['send'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message[] = 'Yêu cầu không hợp lệ';
    } else {
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $number = sanitize_input($_POST['number']);
        $msg = sanitize_input($_POST['message']);

        $check = db_count($conn, "SELECT * FROM `message` WHERE user_id = ? AND name = ? AND email = ? AND number = ? AND message = ?", 
                 "issss", [$user_id, $name, $email, $number, $msg]);

        if($check > 0){
            $message[] = 'Bạn đã gửi tin nhắn này trước đó!';
        }else{
            db_insert($conn, "INSERT INTO `message`(user_id, name, email, number, message) VALUES(?, ?, ?, ?, ?)", 
                     "issss", [$user_id, $name, $email, $number, $msg]);
            $message[] = 'Tin nhắn đã được gửi thành công!';
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
    <title>Liên hệ</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css -->
   <link rel="stylesheet" href="../css/style.css">

   <style>
   .user-messages {
      max-width: 800px;
      margin: 2rem auto;
      border: 1px solid #ddd;
      border-radius: .5rem;
      padding: 1rem;
      background: #fafafa;
   }
   .user-messages h3 {
      margin-bottom: 1rem;
      font-size: 1.4rem;
   }
   .user-messages .box {
      border-bottom: 1px solid #eee;
      padding: .8rem 0;
   }
   .user-messages .admin-reply {
      margin-top: .3rem;
      padding: .5rem;
      background: #f0f8ff;
      border-left: 3px solid #007bff;
      border-radius: .3rem;
   }
   </style>
</head>
<body>
   
<?php @include '../header.php'; ?>

<section class="heading">
    <h3>Liên hệ với chúng tôi</h3>
    <p><a href="./home.php">Trang chủ</a> / Liên hệ</p>
</section>

<?php
if(isset($message)){
   foreach($message as $msg){
      echo '<div class="message"><span>'.e($msg).'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
}
?>

<section class="contact">
    <form action="" method="POST">
        <?php echo csrf_field(); ?>
        <h3>Gửi tin nhắn cho chúng tôi!</h3>
        <input type="text" name="name" placeholder="Nhập tên của bạn" class="box" required> 
        <input type="email" name="email" placeholder="Nhập email của bạn" class="box" required>
        <input type="number" name="number" placeholder="Nhập số điện thoại" class="box" required>
        <textarea name="message" class="box" placeholder="Nhập nội dung tin nhắn..." required cols="30" rows="10"></textarea>
        <input type="submit" value="Gửi tin nhắn" name="send" class="btn">
    </form>
</section>

<section class="user-messages">
   <h3>Tin nhắn của bạn</h3>
   <?php
   $user_messages = db_select_array($conn, "SELECT * FROM `message` WHERE user_id = ? ORDER BY id DESC", "i", [$user_id]);
   if(!empty($user_messages)){
       foreach($user_messages as $row){
           echo '<div class="box">';
           echo '<p><strong>Nội dung:</strong> '.e($row['message']).'</p>';
           if(!empty($row['admin_reply'])){
               echo '<div class="admin-reply"><strong>Admin trả lời:</strong> '.nl2br(e($row['admin_reply'])).'</div>';
           }
           echo '</div>';
       }
   }else{
       echo '<p>Bạn chưa gửi tin nhắn nào.</p>';
   }
   ?>
</section>

<?php @include '../footer.php'; ?>

<script src="../js/script.js"></script>

</body>
</html>


