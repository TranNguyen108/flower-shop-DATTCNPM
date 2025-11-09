<?php
@include '../config.php';

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

if(isset($_POST['submit_review'])){
   if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
      $message[] = 'Y�u c?u kh�ng h?p l?';
      header('location:orders.php');
      exit;
   }
   
   $order_id = (int)$_POST['order_id'];
   $rating = max(1, min(5, (int)$_POST['rating']));
   $review_message = sanitize_input($_POST['message']);
   
   $uploaded_images = [];
   $uploaded_videos = [];
   
   // X? l� upload h�nh �nh
   if(isset($_FILES['review_images']['name']) && !empty($_FILES['review_images']['name'][0])){
      $total_images = min(5, count($_FILES['review_images']['name']));
      
      for($i = 0; $i < $total_images; $i++){
         $image_name = $_FILES['review_images']['name'][$i];
         $image_tmp = $_FILES['review_images']['tmp_name'][$i];
         $image_size = $_FILES['review_images']['size'][$i];
         $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
         
         $allowed_img = ['jpg', 'jpeg', 'png', 'gif'];
         
         if(in_array($image_ext, $allowed_img) && $image_size <= 5000000){
            $new_image_name = uniqid() . '_' . secure_filename($image_name);
            $upload_path = '../assets/uploads/products/reviews/' . $new_image_name;
            
            if(!file_exists('../assets/uploads/products/reviews')){
               mkdir('../assets/uploads/products/reviews', 0777, true);
            }
            
            if(move_uploaded_file($image_tmp, $upload_path)){
               $uploaded_images[] = $new_image_name;
            }
         }
      }
   }
   
   // X? l� upload video
   if(isset($_FILES['review_videos']['name']) && !empty($_FILES['review_videos']['name'][0])){
      $total_videos = min(2, count($_FILES['review_videos']['name']));
      
      for($i = 0; $i < $total_videos; $i++){
         $video_name = $_FILES['review_videos']['name'][$i];
         $video_tmp = $_FILES['review_videos']['tmp_name'][$i];
         $video_size = $_FILES['review_videos']['size'][$i];
         $video_ext = strtolower(pathinfo($video_name, PATHINFO_EXTENSION));
         
         $allowed_vid = ['mp4', 'avi', 'mov', 'wmv'];
         
         if(in_array($video_ext, $allowed_vid) && $video_size <= 50000000){
            $new_video_name = uniqid() . '_' . secure_filename($video_name);
            $upload_path = '../assets/uploads/products/reviews/' . $new_video_name;
            
            if(!file_exists('../assets/uploads/products/reviews')){
               mkdir('../assets/uploads/products/reviews', 0777, true);
            }
            
            if(move_uploaded_file($video_tmp, $upload_path)){
               $uploaded_videos[] = $new_video_name;
            }
         }
      }
   }
   
   $images_str = implode(',', $uploaded_images);
   $videos_str = implode(',', $uploaded_videos);
   
   // Ki?m tra xem d� d�nh gi� chua
   $check_review = db_count("SELECT COUNT(*) FROM `order_reviews` WHERE order_id = ? AND user_id = ?", [$order_id, $user_id]);
   
   if($check_review > 0){
      // C?p nh?t d�nh gi�
      db_update("UPDATE `order_reviews` SET rating = ?, message = ?, images = ?, videos = ? WHERE order_id = ? AND user_id = ?", 
               [$rating, $review_message, $images_str, $videos_str, $order_id, $user_id]);
      $message[] = 'C?p nh?t d�nh gi� th�nh c�ng!';
   } else {
      // Th�m d�nh gi� m?i
      db_insert("INSERT INTO `order_reviews` (order_id, user_id, rating, message, images, videos) VALUES (?, ?, ?, ?, ?, ?)", 
               [$order_id, $user_id, $rating, $review_message, $images_str, $videos_str]);
      $message[] = '��nh gi� c?a b?n d� du?c g?i th�nh c�ng!';
   }
   
   header('location:orders.php');
   exit;
}
?>


