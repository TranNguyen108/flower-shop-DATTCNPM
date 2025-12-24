<?php
@include '../config.php';

$admin_id = $_SESSION['admin_id'] ?? null;

if(!isset($admin_id)){
   header('location:../auth/login.php');
   exit;
}

// Xử lý phản hồi từ admin
if(isset($_POST['send_reply'])){
   if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
      $message[] = 'Yêu cầu không hợp lệ';
   } else {
      $review_id = (int)$_POST['review_id'];
      $admin_reply = sanitize_input($_POST['admin_reply']);
      
      db_update($conn, "UPDATE `reviews` SET admin_reply = ? WHERE id = ?", 
               "si", [$admin_reply, $review_id]);
      
      $message[] = 'Phản hồi đã được gửi thành công!';
   }
}

// Xóa đánh giá
if(isset($_GET['delete'])){
   if(!verify_csrf_token($_GET['token'] ?? '')){
      $message[] = 'Yêu cầu không hợp lệ';
   } else {
      $delete_id = (int)$_GET['delete'];
      db_delete($conn, "DELETE FROM `reviews` WHERE id = ?", "i", [$delete_id]);
      header('location:reviews.php');
      exit;
   }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Quản lý đánh giá</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <link rel="stylesheet" href="../css/admin-enhanced.css">
   
   <style>
   .review-images {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      margin: 1rem 0;
   }
   
   .review-images img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 8px;
      cursor: pointer;
      border: 2px solid var(--primary);
      transition: var(--transition);
   }
   
   .review-images img:hover {
      transform: scale(1.1);
      box-shadow: var(--shadow-lg);
   }
   
   .review-videos video {
      max-width: 200px;
      border-radius: 8px;
      margin-right: 1rem;
      border: 2px solid var(--primary);
   }
   
   .star-display {
      color: #feca57;
      font-size: 2rem;
      margin: 1rem 0;
   }
   
   .reply-form {
      margin-top: 2rem;
      padding: 2rem;
      background: var(--bg-secondary);
      border-radius: var(--border-radius-md);
      border-left: 4px solid var(--secondary);
   }
   
   .reply-form textarea {
      width: 100%;
      padding: 1.2rem;
      border: 2px solid transparent;
      border-radius: var(--border-radius-md);
      font-size: 1.5rem;
      min-height: 120px;
      resize: vertical;
      background: var(--white);
      transition: var(--transition);
   }
   
   .reply-form textarea:focus {
      border-color: var(--secondary);
      box-shadow: 0 0 0 4px rgba(108, 92, 231, 0.1);
   }
   
   .admin-reply-display {
      margin-top: 1.5rem;
      padding: 1.5rem;
      background: linear-gradient(135deg, rgba(108, 92, 231, 0.1) 0%, rgba(102, 126, 234, 0.1) 100%);
      border-radius: var(--border-radius-md);
      border-left: 4px solid var(--secondary);
   }
   
   .admin-reply-display h4 {
      color: var(--secondary);
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
   }
   
   .filter-section {
      max-width: 1400px;
      margin: 0 auto 3rem;
      padding: 2rem;
      background: var(--white);
      border-radius: var(--border-radius-lg);
      box-shadow: var(--shadow-md);
   }
   
   .filter-section select {
      padding: 1rem 1.5rem;
      font-size: 1.6rem;
      border: 2px solid var(--bg-secondary);
      border-radius: var(--border-radius-md);
      margin: 0 1rem;
      transition: var(--transition);
   }
   
   .filter-section select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(255, 107, 157, 0.1);
   }
   </style>
</head>
<body>

<?php @include './header.php'; ?>

<section class="messages">
   <h1 class="title">Đánh giá của khách hàng</h1>

   <div class="filter-section">
      <label style="font-size: 1.6rem; font-weight: 600;">Lọc theo:</label>
      <select onchange="filterReviews(this.value)">
         <option value="all">Tất cả đánh giá</option>
         <option value="5">★★★★★ (5 sao)</option>
         <option value="4">★★★★ (4 sao)</option>
         <option value="3">★★★ (3 sao)</option>
         <option value="2">★★ (2 sao)</option>
         <option value="1">★ (1 sao)</option>
         <option value="replied">Đã phản hồi</option>
         <option value="not_replied">Chưa phản hồi</option>
      </select>
   </div>

   <div class="box-container">
   <?php
      $select_reviews = mysqli_query($conn, "
         SELECT r.*, p.name as product_name
         FROM `reviews` r
         LEFT JOIN `products` p ON r.product_id = p.id
         ORDER BY r.created_at DESC
      ") or die('Lỗi truy vấn đánh giá: ' . mysqli_error($conn));
      
      if(mysqli_num_rows($select_reviews) > 0){
         while($fetch_review = mysqli_fetch_assoc($select_reviews)){
   ?>
   <div class="box" data-rating="<?php echo $fetch_review['rating']; ?>" data-replied="<?php echo empty($fetch_review['admin_reply']) ? 'not_replied' : 'replied'; ?>">
      <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
         <div>
            <p style="font-size: 1.8rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem;">
               <i class="fas fa-user"></i> <?php echo htmlspecialchars($fetch_review['user_name']); ?>
            </p>
            <p style="font-size: 1.4rem; color: var(--text-secondary);">
               <i class="fas fa-box"></i> Sản phẩm: <?php echo htmlspecialchars($fetch_review['product_name'] ?? 'N/A'); ?>
            </p>
         </div>
         <a href="reviews.php?delete=<?php echo $fetch_review['id']; ?>&token=<?php echo urlencode(generate_csrf_token()); ?>" class="delete-btn" onclick="return confirm('Xóa đánh giá này?');" style="margin-top: 0;">
            <i class="fas fa-trash"></i>
         </a>
      </div>
      
      <div class="star-display">
         <?php 
         for($i = 1; $i <= 5; $i++){
            echo $i <= $fetch_review['rating'] ? '★' : '☆';
         }
         ?>
         <span style="font-size: 1.6rem; color: var(--text-secondary); margin-left: 1rem;">
            (<?php echo $fetch_review['rating']; ?>/5)
         </span>
      </div>
      
      <p style="font-size: 1.6rem; color: var(--text-primary); line-height: 1.8; margin: 1.5rem 0; padding: 1.5rem; background: var(--white); border-radius: 8px; border-left: 3px solid var(--primary);">
         <i class="fas fa-quote-left" style="color: var(--primary);"></i>
         <?php echo nl2br(htmlspecialchars($fetch_review['comment'])); ?>
         <i class="fas fa-quote-right" style="color: var(--primary);"></i>
      </p>
      
      <?php if(!empty($fetch_review['image'])): ?>
      <div class="review-images">
         <img src="../assets/uploads/reviews/<?php echo htmlspecialchars($fetch_review['image']); ?>" onclick="window.open(this.src, '_blank')" alt="Review image">
      </div>
      <?php endif; ?>
      
      <p style="font-size: 1.3rem; color: var(--text-light); margin-top: 1rem;">
         <i class="fas fa-clock"></i> Đánh giá lúc: <?php echo date('d/m/Y H:i', strtotime($fetch_review['created_at'])); ?>
      </p>
      
      <?php if(!empty($fetch_review['admin_reply'])): ?>
      <div class="admin-reply-display">
         <h4>
            <i class="fas fa-reply"></i> Phản hồi của bạn:
         </h4>
         <p style="font-size: 1.5rem; line-height: 1.8; color: var(--text-primary);">
            <?php echo nl2br(htmlspecialchars($fetch_review['admin_reply'])); ?>
         </p>
      </div>
      <?php endif; ?>
      
      <div class="reply-form">
         <h4 style="margin-bottom: 1rem; color: var(--text-primary);">
            <i class="fas fa-reply"></i> 
            <?php echo !empty($fetch_review['admin_reply']) ? 'Cập nhật phản hồi:' : 'Gửi phản hồi:'; ?>
         </h4>
         <form action="" method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="review_id" value="<?php echo $fetch_review['id']; ?>">
            <textarea name="admin_reply" placeholder="Nhập phản hồi của bạn..." required><?php echo $fetch_review['admin_reply']; ?></textarea>
            <button type="submit" name="send_reply" class="btn" style="margin-top: 1rem;">
               <i class="fas fa-paper-plane"></i> 
               <?php echo !empty($fetch_review['admin_reply']) ? 'Cập nhật phản hồi' : 'Gửi phản hồi'; ?>
            </button>
         </form>
      </div>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">Chưa có đánh giá nào!</p>';
      }
   ?>
   </div>
</section>

<script>
function filterReviews(filter) {
   const boxes = document.querySelectorAll('.box-container .box');
   
   boxes.forEach(box => {
      const rating = box.getAttribute('data-rating');
      const replied = box.getAttribute('data-replied');
      
      if(filter === 'all') {
         box.style.display = 'block';
      } else if(filter === 'replied' || filter === 'not_replied') {
         box.style.display = replied === filter ? 'block' : 'none';
      } else {
         box.style.display = rating === filter ? 'block' : 'none';
      }
   });
}

// Lightbox cho ảnh
document.querySelectorAll('.review-images img').forEach(img => {
   img.style.cursor = 'zoom-in';
});
</script>

</body>
</html>

