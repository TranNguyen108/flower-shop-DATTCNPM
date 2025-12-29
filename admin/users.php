<?php

@include '../config.php';

$admin_id = $_SESSION['admin_id'] ?? null;

if(!isset($admin_id)){
   header('location:../auth/login.php');
   exit;
}

// Handle delete user
if(isset($_GET['delete'])){
   if(!verify_csrf_token($_GET['token'] ?? '')){
      $message[] = 'Yêu cầu không hợp lệ';
   } else {
      $delete_id = (int)$_GET['delete'];
      $user = db_fetch_one($conn, "SELECT user_type FROM `users` WHERE id = ?", "i", [$delete_id]);
      if($user && $user['user_type'] !== 'admin'){
         db_delete($conn, "DELETE FROM `users` WHERE id = ?", "i", [$delete_id]);
         $message[] = 'Đã xóa người dùng thành công!';
      } else {
         $message[] = 'Không thể xóa tài khoản admin!';
      }
   }
}

// Handle update user type
if(isset($_POST['update_user_type'])){
   if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
      $message[] = 'Yêu cầu không hợp lệ';
   } else {
      $user_id = (int)$_POST['user_id'];
      $new_type = $_POST['user_type'];
      
      if(in_array($new_type, ['user', 'admin'])){
         db_update($conn, "UPDATE `users` SET user_type = ? WHERE id = ?", "si", [$new_type, $user_id]);
         $message[] = 'Đã cập nhật loại tài khoản!';
      }
   }
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$user_type_filter = $_GET['user_type'] ?? 'all';
$sort_by = $_GET['sort'] ?? 'newest';

// Build query - Exclude admin accounts from display
$query = "SELECT u.*, 
          COUNT(DISTINCT o.id) as total_orders,
          COALESCE(SUM(o.total_price), 0) as total_spent
          FROM `users` u
          LEFT JOIN `orders` o ON u.id = o.user_id
          WHERE u.user_type = 'user'";
$types = "";
$params = [];

if(!empty($search)){
   $query .= " AND (u.name LIKE ? OR u.email LIKE ?)";
   $search_param = "%{$search}%";
   $types .= "ss";
   $params[] = $search_param;
   $params[] = $search_param;
}

$query .= " GROUP BY u.id";

// Add sorting
switch($sort_by){
   case 'oldest':
      $query .= " ORDER BY u.id ASC";
      break;
   case 'name_asc':
      $query .= " ORDER BY u.name ASC";
      break;
   case 'name_desc':
      $query .= " ORDER BY u.name DESC";
      break;
   case 'most_orders':
      $query .= " ORDER BY total_orders DESC";
      break;
   case 'most_spent':
      $query .= " ORDER BY total_spent DESC";
      break;
   default: // newest
      $query .= " ORDER BY u.id DESC";
}

$result = db_select($conn, $query, $types, $params);
$users = [];
if($result){
   while($row = mysqli_fetch_assoc($result)){
      $users[] = $row;
   }
}

// Get statistics
$total_users = db_fetch_one($conn, "SELECT COUNT(*) as count FROM `users` WHERE user_type = 'user'")['count'] ?? 0;
$total_admins = db_fetch_one($conn, "SELECT COUNT(*) as count FROM `users` WHERE user_type = 'admin'")['count'] ?? 0;
$users_with_orders = db_fetch_one($conn, "SELECT COUNT(DISTINCT user_id) as count FROM `orders`")['count'] ?? 0;
$new_users_today = db_fetch_one($conn, "SELECT COUNT(*) as count FROM `users` WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;
$new_users_week = db_fetch_one($conn, "SELECT COUNT(*) as count FROM `users` WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0;
$new_users_month = db_fetch_one($conn, "SELECT COUNT(*) as count FROM `users` WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'] ?? 0;


?>
<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Quản Lý Người Dùng - Admin</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <link rel="stylesheet" href="../css/admin-enhanced.css">

   <style>
      .users-section {
         padding: 30px;
         max-width: 1600px;
         margin: 0 auto;
      }

      .page-header {
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         padding: 40px;
         border-radius: 20px;
         color: white;
         margin-bottom: 30px;
         box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
      }

      .page-header h1 {
         font-size: 2.5rem;
         margin: 0 0 10px 0;
         display: flex;
         align-items: center;
         gap: 15px;
      }

      .page-header p {
         font-size: 1.1rem;
         opacity: 0.9;
         margin: 0;
      }

      /* Statistics */
      .stats-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 20px;
         margin-bottom: 30px;
      }

      .stat-box {
         background: white;
         padding: 25px;
         border-radius: 15px;
         box-shadow: 0 5px 20px rgba(0,0,0,0.08);
         display: flex;
         align-items: center;
         gap: 20px;
         transition: all 0.3s ease;
      }

      .stat-box:hover {
         transform: translateY(-5px);
         box-shadow: 0 10px 30px rgba(0,0,0,0.12);
      }

      .stat-icon {
         width: 70px;
         height: 70px;
         border-radius: 15px;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 2rem;
         color: white;
      }

      .stat-box:nth-child(1) .stat-icon {
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      }

      .stat-box:nth-child(2) .stat-icon {
         background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      }

      .stat-box:nth-child(3) .stat-icon {
         background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      }

      .stat-box:nth-child(4) .stat-icon {
         background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
      }

      .stat-box:nth-child(5) .stat-icon {
         background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
      }

      .stat-box:nth-child(6) .stat-icon {
         background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
      }

      .stat-content h3 {
         font-size: 2rem;
         margin: 0 0 5px 0;
         color: #2c3e50;
      }

      .stat-content p {
         margin: 0;
         color: #6c757d;
         font-size: 0.95rem;
      }

      /* Filters */
      .filters-container {
         background: white;
         padding: 25px;
         border-radius: 15px;
         box-shadow: 0 5px 20px rgba(0,0,0,0.08);
         margin-bottom: 30px;
      }

      .filters-row {
         display: grid;
         grid-template-columns: 2fr 1.5fr 150px;
         gap: 15px;
         align-items: end;
      }

      .filter-group {
         display: flex;
         flex-direction: column;
         gap: 8px;
      }

      .filter-group label {
         font-weight: 600;
         color: #2c3e50;
         font-size: 0.95rem;
      }

      .filter-group input,
      .filter-group select {
         padding: 12px 15px;
         border: 2px solid #e9ecef;
         border-radius: 10px;
         font-size: 1rem;
         transition: all 0.3s ease;
      }

      .filter-group input:focus,
      .filter-group select:focus {
         outline: none;
         border-color: #667eea;
         box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      }

      .filter-btn {
         padding: 12px 25px;
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         color: white;
         border: none;
         border-radius: 10px;
         font-size: 1rem;
         font-weight: 600;
         cursor: pointer;
         transition: all 0.3s ease;
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 8px;
      }

      .filter-btn:hover {
         transform: translateY(-2px);
         box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
      }

      /* Users Grid */
      .users-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
         gap: 20px;
      }

      .user-card {
         background: white;
         border-radius: 15px;
         padding: 25px;
         box-shadow: 0 5px 20px rgba(0,0,0,0.08);
         transition: all 0.3s ease;
         position: relative;
         overflow: hidden;
         display: flex;
         flex-direction: column;
         min-height: 320px;
      }

      .user-card::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         right: 0;
         height: 5px;
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      }

      .user-card:hover {
         transform: translateY(-5px);
         box-shadow: 0 10px 30px rgba(0,0,0,0.12);
      }

      .user-header {
         display: flex;
         align-items: center;
         gap: 15px;
         margin-bottom: 20px;
         padding-bottom: 15px;
         border-bottom: 2px solid #f8f9fa;
      }

      .user-avatar {
         width: 60px;
         height: 60px;
         border-radius: 50%;
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         display: flex;
         align-items: center;
         justify-content: center;
         color: white;
         font-size: 1.8rem;
         font-weight: 700;
         flex-shrink: 0;
      }

      .user-info h3 {
         margin: 0 0 5px 0;
         color: #2c3e50;
         font-size: 1.2rem;
      }

      .user-type-badge {
         display: inline-block;
         padding: 4px 12px;
         border-radius: 20px;
         font-size: 0.85rem;
         font-weight: 600;
      }

      .user-type-badge.admin {
         background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
         color: white;
      }

      .user-type-badge.user {
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         color: white;
      }

      .user-details {
         display: flex;
         flex-direction: column;
         gap: 12px;
         margin-bottom: 20px;
      }

      .detail-row {
         display: flex;
         align-items: center;
         gap: 10px;
         color: #6c757d;
         font-size: 0.95rem;
      }

      .detail-row i {
         width: 20px;
         color: #667eea;
      }

      .user-stats {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 10px;
         padding: 15px;
         background: #f8f9fa;
         border-radius: 10px;
         margin-bottom: 15px;
      }

      .user-stat {
         text-align: center;
         overflow: hidden;
      }

      .user-stat strong {
         display: block;
         font-size: 1.3rem;
         color: #667eea;
         margin-bottom: 5px;
         word-break: break-word;
         line-height: 1.2;
      }

      .user-stat span {
         font-size: 0.85rem;
         color: #6c757d;
      }

      /* Fix card display */
      .user-card {
         display: flex;
         flex-direction: column;
      }

      .user-actions {
         margin-top: auto;
      }

      .user-actions {
         display: flex;
         gap: 10px;
         margin-top: auto;
         padding-top: 15px;
      }

      .user-actions .action-btn {
         flex: 1;
         padding: 12px 15px !important;
         border: none !important;
         border-radius: 8px !important;
         font-size: 14px !important;
         font-weight: 600 !important;
         cursor: pointer;
         transition: all 0.3s ease;
         display: flex !important;
         align-items: center;
         justify-content: center;
         gap: 6px;
         text-decoration: none !important;
         margin: 0 !important;
      }

      .user-actions .edit-btn {
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
         color: white !important;
      }

      .user-actions .edit-btn:hover {
         transform: translateY(-2px);
         box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
      }

      .user-actions .delete-btn {
         background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%) !important;
         color: white !important;
      }

      .user-actions .delete-btn:hover {
         transform: translateY(-2px);
         box-shadow: 0 5px 15px rgba(238, 9, 121, 0.3);
      }

      .empty-state {
         text-align: center;
         padding: 60px 20px;
         background: white;
         border-radius: 15px;
         box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      }

      .empty-state i {
         font-size: 4rem;
         color: #e9ecef;
         margin-bottom: 20px;
      }

      .empty-state h3 {
         color: #6c757d;
         margin: 0 0 10px 0;
      }

      /* Modal */
      .modal {
         display: none;
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: rgba(0,0,0,0.5);
         z-index: 10000;
         align-items: center;
         justify-content: center;
      }

      .modal.active {
         display: flex;
      }

      .modal-content {
         background: white;
         padding: 30px;
         border-radius: 15px;
         max-width: 400px;
         width: 90%;
         box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      }

      .modal-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 20px;
      }

      .modal-header h2 {
         margin: 0;
         color: #2c3e50;
      }

      .close-modal {
         background: none;
         border: none;
         font-size: 1.5rem;
         cursor: pointer;
         color: #6c757d;
      }

      .form-group {
         margin-bottom: 20px;
      }

      .form-group label {
         display: block;
         margin-bottom: 8px;
         font-weight: 600;
         color: #2c3e50;
      }

      .form-group select {
         width: 100%;
         padding: 12px;
         border: 2px solid #e9ecef;
         border-radius: 10px;
         font-size: 1rem;
      }

      .submit-btn {
         width: 100%;
         padding: 12px;
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         color: white;
         border: none;
         border-radius: 10px;
         font-size: 1rem;
         font-weight: 600;
         cursor: pointer;
         transition: all 0.3s ease;
      }

      .submit-btn:hover {
         transform: translateY(-2px);
         box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
      }

      @media (max-width: 768px) {
         .filters-row {
            grid-template-columns: 1fr;
         }

         .users-grid {
            grid-template-columns: 1fr;
         }

         .page-header h1 {
            font-size: 1.8rem;
         }
      }
   </style>
</head>
<body>
   
<?php @include './header.php'; ?>

<section class="users-section">

   <!-- Page Header -->
   <div class="page-header">
      <h1><i class="fas fa-users"></i> Quản Lý Người Dùng</h1>
      <p>Quản lý tất cả tài khoản người dùng và quyền truy cập hệ thống</p>
   </div>

   <!-- Statistics -->
   <div class="stats-container">
      <div class="stat-box">
         <div class="stat-icon">
            <i class="fas fa-users"></i>
         </div>
         <div class="stat-content">
            <h3><?php echo $total_users; ?></h3>
            <p>Tổng Người Dùng</p>
         </div>
      </div>

      <div class="stat-box">
         <div class="stat-icon">
            <i class="fas fa-user-shield"></i>
         </div>
         <div class="stat-content">
            <h3><?php echo $total_admins; ?></h3>
            <p>Quản Trị Viên</p>
         </div>
      </div>

      <div class="stat-box">
         <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
         </div>
         <div class="stat-content">
            <h3><?php echo $users_with_orders; ?></h3>
            <p>Đã Mua Hàng</p>
         </div>
      </div>

      <div class="stat-box">
         <div class="stat-icon">
            <i class="fas fa-user-plus"></i>
         </div>
         <div class="stat-content">
            <h3><?php echo $new_users_today; ?></h3>
            <p>Đăng Ký Hôm Nay</p>
         </div>
      </div>

      <div class="stat-box">
         <div class="stat-icon">
            <i class="fas fa-calendar-week"></i>
         </div>
         <div class="stat-content">
            <h3><?php echo $new_users_week; ?></h3>
            <p>Đăng Ký Tuần Này</p>
         </div>
      </div>

      <div class="stat-box">
         <div class="stat-icon">
            <i class="fas fa-calendar-alt"></i>
         </div>
         <div class="stat-content">
            <h3><?php echo $new_users_month; ?></h3>
            <p>Đăng Ký Tháng Này</p>
         </div>
      </div>
   </div>

   <!-- Filters -->
   <div class="filters-container">
      <form method="GET" action="">
         <div class="filters-row">
            <div class="filter-group">
               <label><i class="fas fa-search"></i> Tìm kiếm</label>
               <input type="text" name="search" placeholder="Tìm theo tên hoặc email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="filter-group">
               <label><i class="fas fa-sort"></i> Sắp xếp</label>
               <select name="sort">
                  <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                  <option value="oldest" <?php echo $sort_by == 'oldest' ? 'selected' : ''; ?>>Cũ nhất</option>
                  <option value="name_asc" <?php echo $sort_by == 'name_asc' ? 'selected' : ''; ?>>Tên A-Z</option>
                  <option value="name_desc" <?php echo $sort_by == 'name_desc' ? 'selected' : ''; ?>>Tên Z-A</option>
                  <option value="most_orders" <?php echo $sort_by == 'most_orders' ? 'selected' : ''; ?>>Nhiều đơn nhất</option>
                  <option value="most_spent" <?php echo $sort_by == 'most_spent' ? 'selected' : ''; ?>>Chi tiêu nhiều nhất</option>
               </select>
            </div>

            <button type="submit" class="filter-btn">
               <i class="fas fa-search"></i> Lọc
            </button>
         </div>
      </form>
   </div>

   <!-- Users Grid -->
   <div class="users-grid">
      <?php
         if(!empty($users)){
            foreach($users as $user){
               $initial = mb_substr($user['name'], 0, 1, 'UTF-8');
      ?>
      <div class="user-card">
         <div class="user-header">
            <div class="user-avatar"><?php echo strtoupper($initial); ?></div>
            <div class="user-info">
               <h3><?php echo e($user['name']); ?></h3>
               <span class="user-type-badge <?php echo $user['user_type']; ?>">
                  <?php echo $user['user_type'] == 'admin' ? 'Quản Trị Viên' : 'Người Dùng'; ?>
               </span>
            </div>
         </div>

         <div class="user-details">
            <div class="detail-row">
               <i class="fas fa-id-badge"></i>
               <span>ID: #<?php echo (int)$user['id']; ?></span>
            </div>
            <div class="detail-row">
               <i class="fas fa-envelope"></i>
               <span><?php echo e($user['email']); ?></span>
            </div>
         </div>

         <div class="user-stats">
            <div class="user-stat">
               <strong><?php echo (int)$user['total_orders']; ?></strong>
               <span>Đơn hàng</span>
            </div>
            <div class="user-stat">
               <strong><?php echo number_format($user['total_spent']); ?>đ</strong>
               <span>Chi tiêu</span>
            </div>
         </div>

         <div class="user-actions">
            <button class="action-btn edit-btn" onclick="openEditModal(<?php echo (int)$user['id']; ?>, '<?php echo e($user['user_type']); ?>')">
               <i class="fas fa-edit"></i> Sửa
            </button>
            <a href="users.php?delete=<?php echo (int)$user['id']; ?>&token=<?php echo urlencode(generate_csrf_token()); ?>" 
               onclick="return confirm('Bạn có chắc muốn xóa người dùng này?');" 
               class="action-btn delete-btn">
               <i class="fas fa-trash"></i> Xóa
            </a>
         </div>
      </div>
      <?php
            }
         } else {
      ?>
      <div class="empty-state" style="grid-column: 1/-1;">
         <i class="fas fa-users-slash"></i>
         <h3>Không tìm thấy người dùng</h3>
         <p>Thử thay đổi bộ lọc của bạn</p>
      </div>
      <?php } ?>
   </div>

</section>

<!-- Edit User Modal -->
<div class="modal" id="editModal">
   <div class="modal-content">
      <div class="modal-header">
         <h2>Cập Nhật Loại Tài Khoản</h2>
         <button class="close-modal" onclick="closeEditModal()">
            <i class="fas fa-times"></i>
         </button>
      </div>
      <form method="POST" action="">
         <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
         <input type="hidden" name="user_id" id="edit_user_id">
         
         <div class="form-group">
            <label>Loại tài khoản</label>
            <select name="user_type" id="edit_user_type">
               <option value="user">Người dùng</option>
               <option value="admin">Quản trị viên</option>
            </select>
         </div>

         <button type="submit" name="update_user_type" class="submit-btn">
            <i class="fas fa-save"></i> Lưu thay đổi
         </button>
      </form>
   </div>
</div>

<script src="../../js/admin_script.js"></script>
<script>
function openEditModal(userId, userType) {
   document.getElementById('edit_user_id').value = userId;
   document.getElementById('edit_user_type').value = userType;
   document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
   document.getElementById('editModal').classList.remove('active');
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
   if(e.target === this) {
      closeEditModal();
   }
});
</script>

</body>
</html>


