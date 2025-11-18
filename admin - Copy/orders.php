<?php
header('Content-Type: text/html; charset=UTF-8');

@include '../config.php';

$admin_id = $_SESSION['admin_id'] ?? null;

if(!isset($admin_id)){
   header('location:../auth/login.php');
   exit;
}

// Use function from admin_functions.php or email_service.php instead
// Include admin functions if not already loaded
if (!function_exists('send_order_status_update')) {
    function send_order_status_update($order_id, $email, $name, $status){
       $subject = "Cập nhật trạng thái đơn hàng #$order_id";
       $message = "Xin chào $name,\n\nĐơn hàng #$order_id của bạn đã được cập nhật.\nTrạng thái hiện tại: $status\n\nCảm ơn bạn đã mua hàng!";
       $headers = "From: noreply@flowershop.com";
       mail($email, $subject, $message, $headers);
    }
}

// Lọc và tìm kiếm
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : 'all';

// Xây dựng query với điều kiện lọc
$where_conditions = [];
$params = [];
$types = '';

if($filter_status !== 'all') {
   $where_conditions[] = "delivery_status = ?";
   $params[] = $filter_status;
   $types .= 's';
}

if(!empty($search_term)) {
   $where_conditions[] = "(name LIKE ? OR email LIKE ? OR number LIKE ? OR address LIKE ?)";
   $search_param = "%{$search_term}%";
   $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
   $types .= 'ssss';
}

if($date_filter !== 'all') {
   switch($date_filter) {
      case 'today':
         $where_conditions[] = "DATE(placed_on) = CURDATE()";
         break;
      case 'week':
         $where_conditions[] = "YEARWEEK(placed_on, 1) = YEARWEEK(CURDATE(), 1)";
         break;
      case 'month':
         $where_conditions[] = "MONTH(placed_on) = MONTH(CURDATE()) AND YEAR(placed_on) = YEAR(CURDATE())";
         break;
   }
}

$where_sql = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

if(isset($_POST['update_order'])){
   $token = $_POST['csrf_token'] ?? '';
   if(!verify_csrf_token($token)){
      $message[] = 'Token không hợp lệ!';
   } else {
      $order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
      $update_payment = filter_var($_POST['update_payment'], FILTER_SANITIZE_STRING);
      $update_delivery = filter_var($_POST['update_delivery'], FILTER_SANITIZE_STRING);
      
      if($order_id && $update_payment && $update_delivery){
         $update_orders = $conn->prepare("UPDATE `orders` SET payment_status = ?, delivery_status = ? WHERE id = ?");
         $update_orders->bind_param("ssi", $update_payment, $update_delivery, $order_id);
         
         if($update_orders->execute()){
            $message[] = 'Cập nhật trạng thái đơn hàng thành công!';
            
            $get_order = $conn->prepare("SELECT name, email FROM orders WHERE id = ?");
            $get_order->bind_param("i", $order_id);
            $get_order->execute();
            $order_result = $get_order->get_result();
            if($order_data = $order_result->fetch_assoc()){
               send_order_status_update($order_id, $order_data['email'], $order_data['name'], $update_delivery);
            }
         } else {
            $message[] = 'Cập nhật thất bại!';
         }
      } else {
         $message[] = 'Dữ liệu không hợp lệ!';
      }
   }
}

if(isset($_GET['delete'])){
   $delete_id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
   $token = $_GET['token'] ?? '';
   
   if(!verify_csrf_token($token)){
      $message[] = 'Token không hợp lệ!';
   } elseif($delete_id){
      $conn->begin_transaction();
      try {
         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE order_id = ?");
         $delete_cart->bind_param("i", $delete_id);
         $delete_cart->execute();
         
         $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE order_id = ?");
         $delete_wishlist->bind_param("i", $delete_id);
         $delete_wishlist->execute();
         
         $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
         $delete_order->bind_param("i", $delete_id);
         $delete_order->execute();
         
         $conn->commit();
         $message[] = 'Đơn hàng đã được xóa!';
      } catch(Exception $e){
         $conn->rollback();
         $message[] = 'Lỗi khi xóa đơn hàng: ' . $e->getMessage();
      }
   }
   header('location:admin_orders.php');
   exit;
}

// Thống kê đơn hàng
$stats_query = "SELECT 
   COUNT(*) as total_orders,
   SUM(CASE WHEN delivery_status = 'Đang xử lý' THEN 1 ELSE 0 END) as processing,
   SUM(CASE WHEN delivery_status = 'Đang giao' THEN 1 ELSE 0 END) as shipping,
   SUM(CASE WHEN delivery_status = 'Đã giao' THEN 1 ELSE 0 END) as delivered,
   SUM(CASE WHEN delivery_status = 'Đã hủy' THEN 1 ELSE 0 END) as cancelled,
   SUM(CASE WHEN payment_status = 'completed' THEN total_price ELSE 0 END) as total_revenue,
   SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_payment
FROM orders";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Quản Lý Đơn Hàng</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
   <style>
      /* Main Layout */
      .orders-page {
         padding: 30px;
         max-width: 1600px;
         margin: 0 auto;
         background: #f5f7fa;
      }

      /* Page Header */
      .page-header {
         background: white;
         padding: 30px;
         border-radius: 12px;
         margin-bottom: 30px;
         box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      }

      .page-header h1 {
         font-size: 2rem;
         color: #2c3e50;
         margin: 0 0 8px 0;
         display: flex;
         align-items: center;
         gap: 12px;
      }

      .page-header p {
         color: #6c757d;
         margin: 0;
         font-size: 0.95rem;
      }

      /* Statistics Section */
      .stats-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
         gap: 20px;
         margin-bottom: 30px;
      }
      
      .stat-box {
         background: white;
         padding: 25px;
         border-radius: 12px;
         box-shadow: 0 2px 8px rgba(0,0,0,0.05);
         transition: all 0.3s ease;
         border-left: 4px solid #667eea;
         position: relative;
         overflow: hidden;
      }

      .stat-box::before {
         content: '';
         position: absolute;
         top: 0;
         right: 0;
         width: 100px;
         height: 100px;
         background: rgba(102, 126, 234, 0.05);
         border-radius: 0 12px 0 100%;
      }
      
      .stat-box:hover {
         transform: translateY(-5px);
         box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      }

      .stat-box.processing {
         border-left-color: #f093fb;
      }

      .stat-box.processing::before {
         background: rgba(240, 147, 251, 0.05);
      }
      
      .stat-box.shipping {
         border-left-color: #4facfe;
      }

      .stat-box.shipping::before {
         background: rgba(79, 172, 254, 0.05);
      }
      
      .stat-box.delivered {
         border-left-color: #43e97b;
      }

      .stat-box.delivered::before {
         background: rgba(67, 233, 123, 0.05);
      }
      
      .stat-box.cancelled {
         border-left-color: #fa709a;
      }

      .stat-box.cancelled::before {
         background: rgba(250, 112, 154, 0.05);
      }
      
      .stat-box.revenue {
         border-left-color: #667eea;
         grid-column: span 2;
      }

      .stat-box.revenue::before {
         background: rgba(102, 126, 234, 0.08);
      }

      .stat-icon {
         font-size: 1.8rem;
         margin-bottom: 10px;
         opacity: 0.7;
      }

      .stat-box.processing .stat-icon {
         color: #f093fb;
      }

      .stat-box.shipping .stat-icon {
         color: #4facfe;
      }

      .stat-box.delivered .stat-icon {
         color: #43e97b;
      }

      .stat-box.cancelled .stat-icon {
         color: #fa709a;
      }

      .stat-box.revenue .stat-icon {
         color: #667eea;
      }
      
      .stat-box h3 {
         font-size: 2.2rem;
         margin: 10px 0 5px 0;
         color: #2c3e50;
         font-weight: 700;
         position: relative;
      }
      
      .stat-box p {
         font-size: 0.9rem;
         color: #6c757d;
         margin: 0;
         font-weight: 500;
         position: relative;
      }
      
      /* Filter Section */
      .filter-container {
         background: white;
         padding: 25px;
         border-radius: 12px;
         margin-bottom: 25px;
         box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      }
      
      .filter-row {
         display: flex;
         gap: 15px;
         flex-wrap: wrap;
         align-items: center;
      }
      
      .filter-group {
         display: flex;
         flex-direction: column;
         gap: 8px;
         flex: 1;
         min-width: 200px;
      }
      
      .filter-group label {
         font-weight: 600;
         color: #2c3e50;
         font-size: 0.9rem;
      }
      
      .filter-group select,
      .filter-group input[type="text"] {
         padding: 10px 15px;
         border: 2px solid #e9ecef;
         border-radius: 8px;
         font-size: 0.95rem;
         transition: all 0.3s;
         background: white;
      }
      
      .filter-group select:focus,
      .filter-group input[type="text"]:focus {
         outline: none;
         border-color: #667eea;
         box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      }
      
      .btn-filter {
         padding: 10px 25px;
         background: #667eea;
         color: white;
         border: none;
         border-radius: 8px;
         cursor: pointer;
         font-weight: 600;
         transition: all 0.3s;
         margin-top: 24px;
         height: fit-content;
      }
      
      .btn-filter:hover {
         background: #5568d3;
         transform: translateY(-2px);
         box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
      }

      /* Orders Section */
      .orders-container {
         display: grid;
         gap: 20px;
      }
      
      .order-box {
         background: white;
         border-radius: 12px;
         padding: 25px;
         box-shadow: 0 2px 8px rgba(0,0,0,0.05);
         transition: all 0.3s;
         border-left: 4px solid #e9ecef;
      }
      
      .order-box:hover {
         box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      }

      .order-id {
         font-size: 1.3rem;
         font-weight: 700;
         color: #2c3e50;
         margin-bottom: 20px;
         display: flex;
         align-items: center;
         justify-content: space-between;
         padding-bottom: 15px;
         border-bottom: 2px solid #f8f9fa;
      }

      .order-id i {
         color: #667eea;
         margin-right: 8px;
      }
      
      .status-badge {
         display: inline-block;
         padding: 6px 16px;
         border-radius: 20px;
         font-weight: 600;
         font-size: 0.85rem;
         letter-spacing: 0.3px;
      }
      
      .status-badge.processing {
         background: #fff3e0;
         color: #e65100;
      }
      
      .status-badge.shipping {
         background: #e3f2fd;
         color: #1565c0;
      }
      
      .status-badge.delivered {
         background: #e8f5e9;
         color: #2e7d32;
      }
      
      .status-badge.cancelled {
         background: #ffebee;
         color: #c62828;
      }
      
      .map-container {
         height: 300px;
         width: 100%;
         margin: 15px 0;
         border-radius: 10px;
         overflow: hidden;
         border: 2px solid #f0f0f0;
      }
      
      .order-details {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
         gap: 15px;
         margin: 20px 0;
         padding: 20px;
         background: #f8f9fa;
         border-radius: 10px;
      }
      
      .detail-item {
         display: flex;
         align-items: flex-start;
         gap: 10px;
         padding: 8px 0;
      }

      .detail-item i {
         color: #667eea;
         margin-top: 3px;
         width: 18px;
      }
      
      .detail-item strong {
         color: #2c3e50;
         font-weight: 600;
         min-width: 110px;
         font-size: 0.9rem;
      }

      .detail-item span {
         color: #6c757d;
         flex: 1;
      }

      .order-actions {
         display: flex;
         gap: 10px;
         margin-top: 20px;
         padding-top: 20px;
         border-top: 2px solid #f8f9fa;
      }

      .btn {
         padding: 10px 20px;
         border: none;
         border-radius: 8px;
         font-weight: 600;
         cursor: pointer;
         transition: all 0.3s;
         font-size: 0.9rem;
      }

      .option-btn {
         background: #667eea;
         color: white;
      }

      .option-btn:hover {
         background: #5568d3;
         transform: translateY(-2px);
         box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
      }

      .delete-btn {
         background: #dc3545;
         color: white;
      }

      .delete-btn:hover {
         background: #c82333;
         transform: translateY(-2px);
         box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
      }
      
      .no-orders {
         text-align: center;
         padding: 80px 20px;
         background: white;
         border-radius: 12px;
         box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      }
      
      .no-orders i {
         font-size: 5rem;
         color: #e9ecef;
         margin-bottom: 20px;
      }
      
      .no-orders h3 {
         color: #6c757d;
         margin: 0 0 10px 0;
         font-size: 1.5rem;
      }

      .no-orders p {
         color: #adb5bd;
         margin: 0;
      }

      /* Orders Grid Layout */
      .box-container {
         display: grid;
         grid-template-columns: repeat(3, 1fr);
         gap: 20px;
         margin-top: 20px;
      }

      @media (max-width: 1200px) {
         .box-container {
            grid-template-columns: repeat(2, 1fr);
         }
      }

      @media (max-width: 768px) {
         .box-container {
            grid-template-columns: 1fr;
         }
      }

      .order-header {
         display: flex;
         align-items: center;
         justify-content: space-between;
         margin-bottom: 10px;
         padding: 15px 20px;
         background: white;
         border-radius: 12px;
         box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      }

      .order-header h2 {
         margin: 0;
         font-size: 1.3rem;
         color: #2c3e50;
      }

      .order-count {
         background: #667eea;
         color: white;
         padding: 5px 15px;
         border-radius: 20px;
         font-size: 0.9rem;
         font-weight: 600;
      }

      .btn-reset {
         padding: 10px 20px;
         background: #6c757d;
         color: white;
         border: none;
         border-radius: 8px;
         cursor: pointer;
         font-weight: 600;
         transition: all 0.3s;
         margin-top: 24px;
         height: fit-content;
         text-decoration: none;
         display: inline-flex;
         align-items: center;
         gap: 5px;
      }

      .btn-reset:hover {
         background: #5a6268;
         transform: translateY(-2px);
      }
   </style>
</head>
<body>

<?php @include './header.php'; ?>

<?php
if(isset($message)){
   foreach($message as $msg){
      echo '<div class="message"><span>'.$msg.'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
}
?>

<section class="placed-orders orders-page">
   <!-- Page Header -->
   <div class="page-header">
      <h1><i class="fas fa-shopping-cart"></i> Quản Lý Đơn Hàng</h1>
      <p>Theo dõi và quản lý tất cả đơn hàng của khách hàng</p>
   </div>

   <!-- Thống kê tổng quan -->
   <div class="stats-container">
      <div class="stat-box">
         <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
         <h3><?php echo $stats['total_orders']; ?></h3>
         <p>Tổng đơn hàng</p>
      </div>
      <div class="stat-box processing">
         <div class="stat-icon"><i class="fas fa-clock"></i></div>
         <h3><?php echo $stats['processing']; ?></h3>
         <p>Đang xử lý</p>
      </div>
      <div class="stat-box shipping">
         <div class="stat-icon"><i class="fas fa-truck"></i></div>
         <h3><?php echo $stats['shipping']; ?></h3>
         <p>Đang giao</p>
      </div>
      <div class="stat-box delivered">
         <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
         <h3><?php echo $stats['delivered']; ?></h3>
         <p>Đã giao</p>
      </div>
      <div class="stat-box cancelled">
         <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
         <h3><?php echo $stats['cancelled']; ?></h3>
         <p>Đã hủy</p>
      </div>
      <div class="stat-box revenue">
         <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
         <h3><?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?>đ</h3>
         <p>Tổng doanh thu (Đã thanh toán)</p>
      </div>
   </div>

   <!-- Bộ lọc và tìm kiếm -->
   <div class="filter-container">
      <form method="GET" action="admin_orders.php">
         <div class="filter-row">
            <div class="filter-group">
               <label><i class="fas fa-filter"></i> Trạng thái</label>
               <select name="status">
                  <option value="all" <?php echo ($filter_status == 'all') ? 'selected' : ''; ?>>Tất cả</option>
                  <option value="Đang xử lý" <?php echo ($filter_status == 'Đang xử lý') ? 'selected' : ''; ?>>Đang xử lý</option>
                  <option value="Đang giao" <?php echo ($filter_status == 'Đang giao') ? 'selected' : ''; ?>>Đang giao</option>
                  <option value="Đã giao" <?php echo ($filter_status == 'Đã giao') ? 'selected' : ''; ?>>Đã giao</option>
                  <option value="Đã hủy" <?php echo ($filter_status == 'Đã hủy') ? 'selected' : ''; ?>>Đã hủy</option>
               </select>
            </div>
            
            <div class="filter-group">
               <label><i class="fas fa-calendar"></i> Thời gian</label>
               <select name="date">
                  <option value="all" <?php echo ($date_filter == 'all') ? 'selected' : ''; ?>>Tất cả</option>
                  <option value="today" <?php echo ($date_filter == 'today') ? 'selected' : ''; ?>>Hôm nay</option>
                  <option value="week" <?php echo ($date_filter == 'week') ? 'selected' : ''; ?>>Tuần này</option>
                  <option value="month" <?php echo ($date_filter == 'month') ? 'selected' : ''; ?>>Tháng này</option>
               </select>
            </div>
            
            <div class="filter-group">
               <label><i class="fas fa-search"></i> Tìm kiếm</label>
               <input type="text" name="search" placeholder="Tên, email, số điện thoại, địa chỉ..." value="<?php echo htmlspecialchars($search_term); ?>">
            </div>
            
            <button type="submit" class="btn-filter">
               <i class="fas fa-search"></i> Tìm kiếm
            </button>
            <a href="orders.php" class="btn-reset">
               <i class="fas fa-redo"></i> Đặt lại
            </a>
         </div>
      </form>
   </div>

   <div class="order-header">
      <h2>Danh sách đơn hàng</h2>
      <span class="order-count">
         <i class="fas fa-box"></i> 
         <?php 
            $count_query = "SELECT COUNT(*) as count FROM orders $where_sql";
            if(!empty($params)) {
               $count_stmt = $conn->prepare($count_query);
               if(!empty($types)) {
                  $count_stmt->bind_param($types, ...$params);
               }
               $count_stmt->execute();
               $count_result = $count_stmt->get_result();
               $count_data = $count_result->fetch_assoc();
               echo $count_data['count'];
            } else {
               $count_result = $conn->query($count_query);
               $count_data = $count_result->fetch_assoc();
               echo $count_data['count'];
            }
         ?> đơn hàng
      </span>
   </div>

   <div class="box-container">
      <?php
      $orders_data = [];
      
      // Truy vấn với prepared statement
      $query = "SELECT * FROM `orders` $where_sql ORDER BY placed_on DESC";
      
      if(!empty($params)) {
         $select_orders = $conn->prepare($query);
         if(!empty($types)) {
            $select_orders->bind_param($types, ...$params);
         }
         $select_orders->execute();
         $result = $select_orders->get_result();
      } else {
         $result = $conn->query($query);
      }
      
      if($result && $result->num_rows > 0){
         while($fetch_orders = $result->fetch_assoc()){
            $orders_data[] = $fetch_orders;
            
            // Xác định class cho status badge
            $status_class = 'processing';
            switch($fetch_orders['delivery_status']) {
               case 'Đang giao': $status_class = 'shipping'; break;
               case 'Đã giao': $status_class = 'delivered'; break;
               case 'Đã hủy': $status_class = 'cancelled'; break;
            }
      ?>
      <div class="order-box">
         <div class="order-id">
            <i class="fas fa-receipt"></i> Đơn hàng #<?php echo $fetch_orders['id']; ?>
            <span class="status-badge <?php echo $status_class; ?>">
               <?php echo htmlspecialchars($fetch_orders['delivery_status']); ?>
            </span>
         </div>
         
         <div class="order-details">
            <div class="detail-item">
               <strong><i class="fas fa-user"></i> Khách hàng:</strong>
               <?php echo htmlspecialchars($fetch_orders['name']); ?>
            </div>
            <div class="detail-item">
               <strong><i class="fas fa-phone"></i> Số điện thoại:</strong>
               <?php echo htmlspecialchars($fetch_orders['number']); ?>
            </div>
            <div class="detail-item">
               <strong><i class="fas fa-envelope"></i> Email:</strong>
               <?php echo htmlspecialchars($fetch_orders['email']); ?>
            </div>
            <div class="detail-item">
               <strong><i class="fas fa-calendar"></i> Ngày đặt:</strong>
               <?php echo date('d/m/Y H:i', strtotime($fetch_orders['placed_on'])); ?>
            </div>
            <div class="detail-item">
               <strong><i class="fas fa-map-marker-alt"></i> Địa chỉ:</strong>
               <?php echo htmlspecialchars($fetch_orders['address']); ?>
            </div>
            <div class="detail-item">
               <strong><i class="fas fa-credit-card"></i> Thanh toán:</strong>
               <?php 
                  switch($fetch_orders['method']) {
                     case 'cash on delivery': echo 'Thanh toán khi nhận hàng'; break;
                     case 'momo': echo 'Ví MoMo'; break;
                     case 'bank': echo 'Chuyển khoản ngân hàng'; break;
                     default: echo htmlspecialchars($fetch_orders['method']);
                  }
               ?>
            </div>
         </div>
         
         <div class="detail-item">
            <strong><i class="fas fa-box"></i> Sản phẩm:</strong>
            <?php echo htmlspecialchars($fetch_orders['total_products']); ?>
         </div>
         
         <div class="detail-item">
            <strong><i class="fas fa-money-bill-wave"></i> Tổng tiền:</strong>
            <span style="color: #e74c3c; font-size: 1.2rem; font-weight: bold;">
               <?php echo number_format($fetch_orders['total_price'], 0, ',', '.'); ?>đ
            </span>
         </div>
         
         <div class="detail-item">
            <strong><i class="fas fa-info-circle"></i> Trạng thái thanh toán:</strong>
            <span style="color:<?php echo ($fetch_orders['payment_status'] == 'completed') ? '#27ae60' : '#f39c12'; ?>; font-weight: bold;">
               <?php echo ($fetch_orders['payment_status'] == 'pending') ? 'Chưa thanh toán' : 'Đã thanh toán'; ?>
            </span>
         </div>

         <?php if($fetch_orders['delivery_status'] !== '�� giao' && !empty($fetch_orders['delivery_lat']) && !empty($fetch_orders['delivery_lng'])): ?>
         <div class="map-container" id="map_<?php echo (int)$fetch_orders['id']; ?>"></div>
         <?php endif; ?>
         
         <form action="" method="post" style="margin-top: 20px;">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="order_id" value="<?php echo (int)$fetch_orders['id']; ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
               <div>
                  <label style="display: block; margin-bottom: 5px; font-weight: 600;">
                     <i class="fas fa-credit-card"></i> Tr?ng th�i thanh to�n:
                  </label>
                  <select name="update_payment" style="width: 100%; padding: 10px; border-radius: 5px; border: 2px solid #ddd;">
                     <option value="pending" <?php if($fetch_orders['payment_status'] == 'pending') echo 'selected'; ?>>Chua thanh to�n</option>
                     <option value="completed" <?php if($fetch_orders['payment_status'] == 'completed') echo 'selected'; ?>>�� thanh to�n</option>
                  </select>
               </div>
               
               <div>
                  <label style="display: block; margin-bottom: 5px; font-weight: 600;">
                     <i class="fas fa-truck"></i> Trạng thái giao hàng:
                  </label>
                  <select name="update_delivery" style="width: 100%; padding: 10px; border-radius: 5px; border: 2px solid #ddd;">
                     <option value="Đang xử lý" <?php if($fetch_orders['delivery_status'] == 'Đang xử lý') echo 'selected'; ?>>Đang xử lý</option>
                     <option value="Đang giao" <?php if($fetch_orders['delivery_status'] == 'Đang giao') echo 'selected'; ?>>Đang giao</option>
                     <option value="Đã giao" <?php if($fetch_orders['delivery_status'] == 'Đã giao') echo 'selected'; ?>>Đã giao</option>
                     <option value="Đã hủy" <?php if($fetch_orders['delivery_status'] == 'Đã hủy') echo 'selected'; ?>>Đã hủy</option>
                  </select>
               </div>
            </div>

            <div style="display: flex; gap: 10px;">
               <input type="submit" name="update_order" value="Cập nhật đơn hàng" class="option-btn" style="flex: 1;">
               <a href="orders.php?delete=<?php echo (int)$fetch_orders['id']; ?>&token=<?php echo urlencode(generate_csrf_token()); ?>" 
                  class="delete-btn" 
                  onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này?');">
                  <i class="fas fa-trash"></i> Xóa
               </a>
            </div>
         </form>
      </div>
      <?php
         }
      } else {
         echo '<div class="no-orders">
                  <i class="fas fa-inbox"></i>
                  <h3>Không tìm thấy đơn hàng nào!</h3>
                  <p>Hãy thử thay đổi bộ lọc hoặc tìm kiếm với từ khóa khác.</p>
               </div>';
      }
      ?>
   </div>
   </div>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
   <?php
   $store_lat = 14.054510;
   $store_lng = 109.042130;

   foreach($orders_data as $order){
      $orderId = $order['id'];
      $lat = floatval($order['delivery_lat']);
      $lng = floatval($order['delivery_lng']);
      $status = $order['delivery_status'];

      if($status !== 'Đã giao' && !empty($lat) && !empty($lng)){
   ?>
   const map<?php echo $orderId; ?> = L.map('map_<?php echo $orderId; ?>').setView([<?php echo $store_lat; ?>, <?php echo $store_lng; ?>], 15);
   L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
   }).addTo(map<?php echo $orderId; ?>);

   const route<?php echo $orderId; ?> = [
      [<?php echo $store_lat; ?>, <?php echo $store_lng; ?>],
      [<?php echo $lat; ?>, <?php echo $lng; ?>]
   ];

   const motorbikeIcon<?php echo $orderId; ?> = L.icon({
      iconUrl: 'images/giaohang.png',
      iconSize: [40, 40],
      iconAnchor: [20, 40],
      popupAnchor: [0, -40]
   });

   let marker<?php echo $orderId; ?> = L.marker(route<?php echo $orderId; ?>[0], { icon: motorbikeIcon<?php echo $orderId; ?> }).addTo(map<?php echo $orderId; ?>);
   marker<?php echo $orderId; ?>.bindPopup("Đang giao hàng").openPopup();

   let routeLine<?php echo $orderId; ?> = L.polyline([route<?php echo $orderId; ?>[0], route<?php echo $orderId; ?>[0]], {
      color: 'red', weight: 3, opacity: 0.8, dashArray: '5,10'
   }).addTo(map<?php echo $orderId; ?>);

   let index<?php echo $orderId; ?> = 0;

   function moveMarker<?php echo $orderId; ?>() {
      index<?php echo $orderId; ?>++;
      if(index<?php echo $orderId; ?> >= route<?php echo $orderId; ?>.length) {
         marker<?php echo $orderId; ?>.bindPopup("Đã giao hàng").openPopup();
         return;
      }
      const latlng = route<?php echo $orderId; ?>[index<?php echo $orderId; ?>];
      marker<?php echo $orderId; ?>.setLatLng(latlng);
      routeLine<?php echo $orderId; ?>.setLatLngs([route<?php echo $orderId; ?>[0], latlng]);
      map<?php echo $orderId; ?>.panTo(latlng);
      setTimeout(moveMarker<?php echo $orderId; ?>, 2000);
   }
   setTimeout(moveMarker<?php echo $orderId; ?>, 2000);
   <?php } } ?>
});
</script>

<script src="../../js/admin_script.js"></script>

</body>
</html>

