<?php
/**
 * Admin Inventory Management
 * Stock tracking, alerts, history
 */

@include '../config.php';
@include '../includes/inventory_functions.php';

$admin_id = $_SESSION['admin_id'] ?? null;

if(!$admin_id){
   header('location:../auth/login.php');
   exit;
}

$message = [];

// Update stock
if(isset($_POST['update_stock'])){
   if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
      $message[] = 'Yêu cầu không hợp lệ';
   } else {
      $product_id = (int)$_POST['product_id'];
      $action = sanitize_input($_POST['action']);
      $quantity = (int)$_POST['quantity'];
      $notes = sanitize_input($_POST['notes'] ?? '');
      
      $success = false;
      
      switch($action) {
         case 'restock':
            $success = increase_stock($product_id, $quantity, $admin_id, $notes);
            $msg = 'Đã nhập thêm ' . $quantity . ' sản phẩm vào kho';
            break;
         case 'adjust':
            $success = adjust_stock($product_id, $quantity, $admin_id, $notes);
            $msg = 'Đã điều chỉnh số lượng tồn kho';
            break;
         case 'set_threshold':
            $success = db_update($conn,
               "UPDATE products SET low_stock_threshold = ? WHERE id = ?",
               "ii",
               [$quantity, $product_id]
            );
            $msg = 'Đã cập nhật ngưỡng cảnh báo';
            break;
      }
      
      if($success) {
         $message[] = $msg;
      } else {
         $message[] = 'Lỗi khi cập nhật kho';
      }
   }
}

// Resolve alert
if(isset($_GET['resolve_alert'])){
   if(!verify_csrf_token($_GET['token'] ?? '')){
      $message[] = 'Yêu cầu không hợp lệ';
   } else {
      $alert_id = (int)$_GET['resolve_alert'];
      db_update($conn,
         "UPDATE stock_alerts SET is_resolved = 1, resolved_at = NOW() WHERE id = ?",
         "i",
         [$alert_id]
      );
      header('location:admin_inventory.php');
      exit;
   }
}

// Get inventory stats
$stats = get_inventory_stats();

// Get active alerts
$alerts = get_active_stock_alerts();

// Get low stock products
$low_stock_products = get_low_stock_products(50);

// Get recent inventory history
$recent_history = get_all_inventory_history(30);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Quản lý kho hàng - Admin</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   
   <style>
   .inventory-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 2rem;
      margin-bottom: 3rem;
   }
   
   .stat-box {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: center;
   }
   
   .stat-box i {
      font-size: 3rem;
      margin-bottom: 1rem;
   }
   
   .stat-box.in-stock i { color: #10b981; }
   .stat-box.low-stock i { color: #f59e0b; }
   .stat-box.out-stock i { color: #ef4444; }
   .stat-box.value i { color: #3b82f6; }
   .stat-box.alerts i { color: #8b5cf6; }
   
   .stat-box h3 {
      font-size: 2.5rem;
      margin: 0.5rem 0;
      color: #333;
   }
   
   .stat-box p {
      color: #666;
      margin: 0;
   }
   
   .alerts-section {
      background: #fff3cd;
      border-left: 4px solid #f59e0b;
      padding: 2rem;
      border-radius: 5px;
      margin-bottom: 3rem;
   }
   
   .alert-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem;
      background: white;
      margin-bottom: 1rem;
      border-radius: 5px;
   }
   
   .alert-item img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 5px;
   }
   
   .alert-item.out-of-stock {
      border-left: 4px solid #ef4444;
   }
   
   .alert-item.low-stock {
      border-left: 4px solid #f59e0b;
   }
   
   .stock-table {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      margin-bottom: 3rem;
   }
   
   .stock-table table {
      width: 100%;
      border-collapse: collapse;
   }
   
   .stock-table th,
   .stock-table td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid #e5e7eb;
   }
   
   .stock-table th {
      background: #f3f4f6;
      font-weight: bold;
   }
   
   .stock-badge {
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: bold;
   }
   
   .stock-badge.in-stock {
      background: #d1fae5;
      color: #065f46;
   }
   
   .stock-badge.low-stock {
      background: #fef3c7;
      color: #92400e;
   }
   
   .stock-badge.out-of-stock {
      background: #fee2e2;
      color: #991b1b;
   }
   
   .stock-input {
      display: flex;
      gap: 0.5rem;
      align-items: center;
   }
   
   .stock-input input {
      width: 80px;
      padding: 0.3rem;
      border: 1px solid #ddd;
      border-radius: 3px;
   }
   
   .stock-input select {
      padding: 0.3rem;
      border: 1px solid #ddd;
      border-radius: 3px;
   }
   
   .history-timeline {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
   }
   
   .history-item {
      display: flex;
      gap: 1rem;
      padding: 1rem;
      border-left: 3px solid #e5e7eb;
      margin-bottom: 1rem;
   }
   
   .history-item.sale { border-left-color: #ef4444; }
   .history-item.restock { border-left-color: #10b981; }
   .history-item.adjustment { border-left-color: #3b82f6; }
   .history-item.return { border-left-color: #f59e0b; }
   
   .tabs {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
      border-bottom: 2px solid #e5e7eb;
   }
   
   .tab {
      padding: 1rem 2rem;
      cursor: pointer;
      background: none;
      border: none;
      font-size: 1rem;
      color: #666;
      transition: all 0.3s;
   }
   
   .tab.active {
      color: #667eea;
      border-bottom: 3px solid #667eea;
      margin-bottom: -2px;
   }
   
   .tab-content {
      display: none;
   }
   
   .tab-content.active {
      display: block;
   }
   
   .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
   }
   
   .modal-content {
      background: white;
      margin: 5% auto;
      padding: 2rem;
      border-radius: 10px;
      max-width: 500px;
   }
   
   .close-modal {
      float: right;
      font-size: 2rem;
      cursor: pointer;
   }
   </style>
</head>
<body>

<?php @include './header.php'; ?>

<section class="dashboard">
   <h1 class="title">📦 Quản lý kho hàng</h1>

   <?php
   if(!empty($message)){
      foreach($message as $msg){
         echo '<div class="message"><span>'.e($msg).'</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
      }
   }
   ?>

   <!-- Inventory Statistics -->
   <div class="inventory-stats">
      <div class="stat-box in-stock">
         <i class="fas fa-check-circle"></i>
         <h3><?php echo $stats['in_stock']; ?></h3>
         <p>Sản phẩm còn hàng</p>
      </div>
      
      <div class="stat-box low-stock">
         <i class="fas fa-exclamation-triangle"></i>
         <h3><?php echo $stats['low_stock']; ?></h3>
         <p>Sắp hết hàng</p>
      </div>
      
      <div class="stat-box out-stock">
         <i class="fas fa-times-circle"></i>
         <h3><?php echo $stats['out_of_stock']; ?></h3>
         <p>Hết hàng</p>
      </div>
      
      <div class="stat-box value">
         <i class="fas fa-dollar-sign"></i>
         <h3><?php echo number_format($stats['total_value'], 0, ',', '.'); ?>đ</h3>
         <p>Giá trị tồn kho</p>
      </div>
      
      <div class="stat-box alerts">
         <i class="fas fa-bell"></i>
         <h3><?php echo $stats['active_alerts']; ?></h3>
         <p>Cảnh báo chưa xử lý</p>
      </div>
   </div>

   <!-- Active Alerts -->
   <?php if(!empty($alerts)): ?>
   <div class="alerts-section">
      <h2><i class="fas fa-bell"></i> Cảnh báo tồn kho (<?php echo count($alerts); ?>)</h2>
      <?php foreach($alerts as $alert): ?>
      <div class="alert-item <?php echo e($alert['alert_type']); ?>">
         <img src="../../assets/uploads/products/<?php echo e($alert['product_image']); ?>" alt="<?php echo e($alert['product_name']); ?>">
         <div style="flex: 1;">
            <strong><?php echo e($alert['product_name']); ?></strong>
            <p style="margin: 0.3rem 0;">
               <?php if($alert['alert_type'] == 'out_of_stock'): ?>
                  <span style="color: #ef4444;">&#10007; Hết hàng (0 sản phẩm)</span>
               <?php else: ?>
                  <span style="color: #f59e0b;">&#9888; Sắp hết (Còn <?php echo $alert['stock_quantity']; ?> / Ngưỡng: <?php echo $alert['threshold']; ?>)</span>
               <?php endif; ?>
            </p>
            <small>Cảnh báo lúc: <?php echo date('d/m/Y H:i', strtotime($alert['created_at'])); ?></small>
         </div>
         <button onclick="openStockModal(<?php echo $alert['product_id']; ?>, '<?php echo e($alert['product_name']); ?>', <?php echo $alert['stock_quantity']; ?>)" class="btn" style="padding: 0.5rem 1rem;">
            <i class="fas fa-box"></i> Nhập hàng
         </button>
         <a href="inventory.php?resolve_alert=<?php echo $alert['id']; ?>&token=<?php echo generate_csrf_token(); ?>" 
            class="btn" style="padding: 0.5rem 1rem; background: #10b981;" 
            onclick="return confirm('Đánh dấu đã xử lý?')">
            <i class="fas fa-check"></i>
         </a>
      </div>
      <?php endforeach; ?>
   </div>
   <?php endif; ?>

   <!-- Tabs -->
   <div class="tabs">
      <button class="tab active" onclick="showTab('products')">
         <i class="fas fa-boxes"></i> Danh sách sản phẩm
      </button>
      <button class="tab" onclick="showTab('history')">
         <i class="fas fa-history"></i> Lịch sử nhập/xuất
      </button>
   </div>

   <!-- Products Tab -->
   <div id="products-tab" class="tab-content active">
      <div class="stock-table">
         <h2>Danh s�ch s?n ph?m t?n kho</h2>
         <table>
            <thead>
               <tr>
                  <th>S?n ph?m</th>
                  <th>T?n kho</th>
                  <th>Ngu?ng</th>
                  <th>Tr?ng th�i</th>
                  <th>Gi� tr?</th>
                  <th>H�nh d?ng</th>
               </tr>
            </thead>
            <tbody>
               <?php
               $all_products = db_select($conn, "SELECT * FROM products ORDER BY stock_quantity ASC");
               foreach($all_products as $product):
               ?>
               <tr>
                  <td>
                     <div style="display: flex; align-items: center; gap: 1rem;">
                        <img src="../../assets/uploads/products/<?php echo e($product['image']); ?>" 
                             alt="<?php echo e($product['name']); ?>" 
                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                        <div>
                           <strong><?php echo e($product['name']); ?></strong><br>
                           <small><?php echo e($product['category']); ?></small>
                        </div>
                     </div>
                  </td>
                  <td><strong><?php echo $product['stock_quantity']; ?></strong></td>
                  <td><?php echo $product['low_stock_threshold']; ?></td>
                  <td>
                     <span class="stock-badge <?php echo e($product['stock_status']); ?>">
                        <?php
                        switch($product['stock_status']) {
                           case 'in_stock': echo '? C�n h�ng'; break;
                           case 'low_stock': echo '?? S?p h?t'; break;
                           case 'out_of_stock': echo '? H?t h�ng'; break;
                        }
                        ?>
                     </span>
                  </td>
                  <td><?php echo number_format($product['stock_quantity'] * $product['price'], 0, ',', '.'); ?>d</td>
                  <td>
                     <button onclick="openStockModal(<?php echo $product['id']; ?>, '<?php echo e($product['name']); ?>', <?php echo $product['stock_quantity']; ?>, <?php echo $product['low_stock_threshold']; ?>)" 
                             class="btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                        <i class="fas fa-edit"></i> C?p nh?t
                     </button>
                  </td>
               </tr>
               <?php endforeach; ?>
            </tbody>
         </table>
      </div>
   </div>

   <!-- History Tab -->
   <div id="history-tab" class="tab-content">
      <div class="history-timeline">
         <h2>L?ch s? nh?p/xu?t kho (30 giao d?ch g?n nh?t)</h2>
         <?php foreach($recent_history as $history): ?>
         <div class="history-item <?php echo e($history['change_type']); ?>">
            <div style="flex: 1;">
               <strong><?php echo e($history['product_name']); ?></strong>
               <p style="margin: 0.5rem 0;">
                  <?php
                  switch($history['change_type']) {
                     case 'sale':
                        echo '?? Xu?t kho (B�n h�ng)';
                        break;
                     case 'restock':
                        echo '?? Nh?p kho';
                        break;
                     case 'adjustment':
                        echo '?? �i?u ch?nh';
                        break;
                     case 'return':
                        echo '?? Tr? h�ng';
                        break;
                  }
                  ?>
                  : <strong style="color: <?php echo $history['quantity_change'] > 0 ? '#10b981' : '#ef4444'; ?>">
                     <?php echo ($history['quantity_change'] > 0 ? '+' : '') . $history['quantity_change']; ?>
                  </strong>
               </p>
               <p style="margin: 0;">
                  Tru?c: <?php echo $history['quantity_before']; ?> ? Sau: <?php echo $history['quantity_after']; ?>
               </p>
               <?php if($history['notes']): ?>
               <small style="color: #666;"><?php echo e($history['notes']); ?></small>
               <?php endif; ?>
            </div>
            <div style="text-align: right; color: #666;">
               <small><?php echo date('d/m/Y', strtotime($history['created_at'])); ?></small><br>
               <small><?php echo date('H:i', strtotime($history['created_at'])); ?></small>
            </div>
         </div>
         <?php endforeach; ?>
      </div>
   </div>

</section>

<!-- Stock Update Modal -->
<div id="stock-modal" class="modal">
   <div class="modal-content">
      <span class="close-modal" onclick="closeStockModal()">&times;</span>
      <h2>C?p nh?t t?n kho</h2>
      <form method="POST" action="">
         <?php echo csrf_field(); ?>
         <input type="hidden" name="product_id" id="modal-product-id">
         
         <p><strong id="modal-product-name"></strong></p>
         <p>T?n kho hi?n t?i: <strong id="modal-current-stock"></strong></p>
         
         <div class="inputBox" style="margin: 1rem 0;">
            <span>H�nh d?ng:</span>
            <select name="action" id="modal-action" onchange="updateModalFields()" required>
               <option value="restock">Nh?p th�m h�ng</option>
               <option value="adjust">�i?u ch?nh s? lu?ng</option>
               <option value="set_threshold">�?t ngu?ng c?nh b�o</option>
            </select>
         </div>
         
         <div class="inputBox" style="margin: 1rem 0;">
            <span id="quantity-label">S? lu?ng nh?p:</span>
            <input type="number" name="quantity" id="modal-quantity" min="1" required>
         </div>
         
         <div class="inputBox" style="margin: 1rem 0;">
            <span>Ghi ch�:</span>
            <textarea name="notes" id="modal-notes" rows="3" style="width: 100%; padding: 0.5rem;"></textarea>
         </div>
         
         <input type="submit" name="update_stock" value="C?p nh?t" class="btn">
      </form>
   </div>
</div>

<script src="../../js/admin_script.js"></script>
<script>
function showTab(tabName) {
   // Hide all tabs
   document.querySelectorAll('.tab-content').forEach(tab => {
      tab.classList.remove('active');
   });
   document.querySelectorAll('.tab').forEach(btn => {
      btn.classList.remove('active');
   });
   
   // Show selected tab
   document.getElementById(tabName + '-tab').classList.add('active');
   event.target.classList.add('active');
}

function openStockModal(productId, productName, currentStock, threshold = null) {
   document.getElementById('modal-product-id').value = productId;
   document.getElementById('modal-product-name').textContent = productName;
   document.getElementById('modal-current-stock').textContent = currentStock;
   document.getElementById('modal-quantity').value = threshold || '';
   document.getElementById('stock-modal').style.display = 'block';
}

function closeStockModal() {
   document.getElementById('stock-modal').style.display = 'none';
}

function updateModalFields() {
   const action = document.getElementById('modal-action').value;
   const label = document.getElementById('quantity-label');
   const quantityInput = document.getElementById('modal-quantity');
   
   switch(action) {
      case 'restock':
         label.textContent = 'S? lu?ng nh?p:';
         quantityInput.min = 1;
         break;
      case 'adjust':
         label.textContent = 'S? lu?ng m?i:';
         quantityInput.min = 0;
         break;
      case 'set_threshold':
         label.textContent = 'Ngu?ng c?nh b�o:';
         quantityInput.min = 1;
         break;
   }
}

// Close modal on outside click
window.onclick = function(event) {
   const modal = document.getElementById('stock-modal');
   if (event.target == modal) {
      closeStockModal();
   }
}
</script>

</body>
</html>

