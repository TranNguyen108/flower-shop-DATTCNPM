<?php
@include '../config.php';

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){
   header('location:../auth/login.php');
   exit;
}

$message = [];

// Upload avatar
if(isset($_POST['update_avatar'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message[] = 'Y�u c?u kh�ng h?p l?';
    } elseif(isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0){
        $validation = validate_image_upload($_FILES['avatar']);
        if($validation['valid']){
            $new_name = 'avatar_' . $user_id . '_' . time() . '.' . $validation['ext'];
            $upload_path = '../assets/uploads/products/' . $new_name;
            
            if(move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)){
                db_update("UPDATE users SET avatar = ? WHERE id = ?", [$new_name, $user_id]);
                $message[] = 'C?p nh?t �nh d?i di?n th�nh c�ng!';
            }
        } else {
            $message[] = $validation['error'];
        }
    }
}

// Update profile
if(isset($_POST['update_profile'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message[] = 'Y�u c?u kh�ng h?p l?';
    } else {
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $address = sanitize_input($_POST['address']);
        
        if(!validate_email($email)){
            $message[] = 'Email kh�ng h?p l?!';
        } else {
            db_update("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?", 
                     [$name, $email, $phone, $address, $user_id]);
            $message[] = 'C?p nh?t th�ng tin th�nh c�ng!';
        }
    }
}

// Change password
if(isset($_POST['change_password'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message[] = 'Y�u c?u kh�ng h?p l?';
    } else {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];
        
        $user_data = db_fetch_one("SELECT password FROM users WHERE id = ?", [$user_id]);
        
        if(!verify_password($old_pass, $user_data['password'])){
            $message[] = 'M?t kh?u cu kh�ng d�ng!';
        } elseif($new_pass != $confirm_pass){
            $message[] = 'M?t kh?u m?i kh�ng kh?p!';
        } elseif(strlen($new_pass) < 6){
            $message[] = 'M?t kh?u ph?i �tu 6 k� t?!';
        } else {
            $hashed_new = hash_password($new_pass);
            db_update("UPDATE users SET password = ? WHERE id = ?", [$hashed_new, $user_id]);
            $message[] = '�?i m?t kh?u th�nh c�ng!';
        }
    }
}

$user = db_fetch_one("SELECT * FROM users WHERE id = ?", [$user_id]);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Quản lý tài khoản</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <style>
   .profile-container {
       max-width: 900px;
       margin: 3rem auto;
       padding: 2rem;
   }
   .profile-box {
       background: white;
       padding: 2rem;
       border-radius: 10px;
       box-shadow: 0 5px 15px rgba(0,0,0,0.1);
       margin-bottom: 2rem;
   }
   .profile-box h3 {
       margin-bottom: 1.5rem;
       color: #333;
       border-bottom: 2px solid #8e44ad;
       padding-bottom: 0.5rem;
   }
   .avatar-section {
       text-align: center;
   }
   .avatar-preview {
       width: 150px;
       height: 150px;
       border-radius: 50%;
       object-fit: cover;
       border: 5px solid #8e44ad;
       margin-bottom: 1rem;
   }
   .form-group {
       margin-bottom: 1.5rem;
   }
   .form-group label {
       display: block;
       margin-bottom: 0.5rem;
       font-weight: 600;
       color: #555;
   }
   .form-group input, .form-group textarea {
       width: 100%;
       padding: 1rem;
       border: 1px solid #ddd;
       border-radius: 5px;
       font-size: 1rem;
   }
   .btn-profile {
       background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
       color: white;
       padding: 1rem 2rem;
       border: none;
       border-radius: 5px;
       cursor: pointer;
       font-size: 1rem;
       transition: transform 0.3s;
   }
   .btn-profile:hover {
       transform: translateY(-2px);
   }
   </style>
</head>
<body>

<?php @include '../header.php'; ?>

<section class="heading">
    <h3>Quản lý tài khoản</h3>
    <p><a href="./home.php">Trang chủ</a> / Tài khoản</p>
</section>

<?php
if(!empty($message)){
   foreach($message as $msg){
      echo '<div class="message"><span>'.$msg.'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
}
?>

<div class="profile-container">
    <!-- Avatar Section -->
    <div class="profile-box avatar-section">
        <h3>Ảnh đại diện</h3>
        <img src="../assets/uploads/products/<?php echo $user['avatar']; ?>" alt="Avatar" class="avatar-preview">
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="file" name="avatar" accept="image/*" required>
            <button type="submit" name="update_avatar" class="btn-profile">Cập nhật ảnh</button>
        </form>
    </div>

    <!-- Profile Info -->
    <div class="profile-box">
        <h3>Thông tin cá nhân</h3>
        <form action="" method="POST">
            <div class="form-group">
                <label>Họ tên:</label>
                <input type="text" name="name" value="<?php echo $user['name']; ?>" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại:</label>
                <input type="text" name="phone" value="<?php echo $user['phone'] ?? ''; ?>" placeholder="Nhập số điện thoại">
            </div>
            <div class="form-group">
                <label>Địa chỉ:</label>
                <textarea name="address" rows="3" placeholder="Nhập địa chỉ"><?php echo $user['address'] ?? ''; ?></textarea>
            </div>
            <button type="submit" name="update_profile" class="btn-profile">Lưu thay đổi</button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="profile-box">
        <h3>Đổi mật khẩu</h3>
        <form action="" method="POST">
            <div class="form-group">
                <label>Mật khẩu cũ:</label>
                <input type="password" name="old_password" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu mới:</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="form-group">
                <label>Nhập lại mật khẩu mới:</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" name="change_password" class="btn-profile">Đổi mật khẩu</button>
        </form>
    </div>
</div>

<?php @include '../footer.php'; ?>

</body>
</html>


