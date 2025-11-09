<?php
/**
 * Shop Page - Enhanced Security
 * Prepared Statements, CSRF Protection, XSS Escaping
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

@include '../config.php';
@include '../includes/inventory_functions.php';

$user_id = $_SESSION['user_id'] ?? null;

if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

$message = [];

// Lọc và sắp xếp (sanitize inputs)
$category = sanitize_input($_GET['category'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$min_price = max(0, (int)($_GET['min_price'] ?? 0));
$max_price = max(0, (int)($_GET['max_price'] ?? 10000000));
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Add to wishlist
if(isset($_POST['add_to_wishlist'])){
   if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
       $message[] = 'Lỗi bảo mật!';
   } else {
       $product_id = (int)$_POST['product_id'];
       $product_name = sanitize_input($_POST['product_name']);
       $product_price = (int)$_POST['product_price'];
       $product_image = sanitize_input($_POST['product_image']);

       $check_wishlist = db_count($conn, "SELECT * FROM wishlist WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);
       $check_cart = db_count($conn, "SELECT * FROM cart WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);

       if($check_wishlist > 0){
           $message[] = 'Đã có trong danh sách yêu thích';
       }elseif($check_cart > 0){
           $message[] = 'Đã có trong giỏ hàng';
       }else{
           db_insert($conn, "INSERT INTO wishlist (user_id, pid, name, price, image) VALUES (?, ?, ?, ?, ?)", 
                     "iisis", [$user_id, $product_id, $product_name, $product_price, $product_image]);
           $message[] = 'Đã thêm vào danh sách yêu thích';
       }
   }
}

// Add to cart
if(isset($_POST['add_to_cart'])){
   if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
       $message[] = 'Lỗi bảo mật!';
   } else {
       $product_id = (int)$_POST['product_id'];
       $product_name = sanitize_input($_POST['product_name']);
       $product_price = (int)$_POST['product_price'];
       $product_image = sanitize_input($_POST['product_image']);
       $product_quantity = max(1, (int)$_POST['product_quantity']);

       // Check stock availability
       if(!check_stock_availability($product_id, $product_quantity)) {
           $message[] = 'Sản phẩm không đủ số lượng hoặc đã hết hàng!';
       } else {
           $check_cart = db_count($conn, "SELECT * FROM cart WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);

           if($check_cart > 0){
               $message[] = 'Đã có trong giỏ hàng';
           }else{
               // Remove from wishlist if exists
               db_delete($conn, "DELETE FROM wishlist WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);
               
               // Add to cart
               db_insert($conn, "INSERT INTO cart (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)",
                         "iisiii", [$user_id, $product_id, $product_name, $product_price, $product_quantity, $product_image]);
               $message[] = 'Đã thêm vào giỏ hàng';
           }
       }
   }
}

// Build query with prepared statement
$where_conditions = [];
$params = [];
$types = "";

if($category) {
    $where_conditions[] = "category = ?";
    $params[] = $category;
    $types .= "s";
}

$where_conditions[] = "price BETWEEN ? AND ?";
$params[] = $min_price;
$params[] = $max_price;
$types .= "ii";

$where = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

$order = "ORDER BY id DESC";
$allowed_sorts = ['price_asc', 'price_desc', 'name_asc', 'name_desc', 'newest'];
if (in_array($sort, $allowed_sorts)) {
    switch($sort){
        case 'price_asc': $order = "ORDER BY price ASC"; break;
        case 'price_desc': $order = "ORDER BY price DESC"; break;
        case 'name_asc': $order = "ORDER BY name ASC"; break;
        case 'name_desc': $order = "ORDER BY name DESC"; break;
    }
}

// Count total products
$count_query = "SELECT COUNT(*) as total FROM products $where";
$count_result = db_select($conn, $count_query, $types, $params);
$total_products = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_products / $per_page);

// Get products
$query = "SELECT * FROM products $where $order LIMIT $per_page OFFSET $offset";
$select_products = db_select($conn, $query, $types, $params);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa hàng</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/style-enhanced.css">
   <link rel="stylesheet" href="../css/product-cards.css">
</head>
<body>

<?php @include '../header.php'; ?>

<?php
if(!empty($message)){
   foreach($message as $msg){
      echo '<div class="message"><span>'.e($msg).'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
}
?>

<section class="heading">
    <h3>Cửa hàng của chúng tôi</h3>
    <p> <a href="./home.php">Trang chủ</a> / Cửa hàng </p>
</section>

<!-- B? l?c & S?p x?p -->
<section class="filter-section" style="background: #f5f5f5; padding: 2rem; margin: 2rem auto; max-width: 1200px; border-radius: 10px;">
    <form method="GET" action="./shop.php">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
            
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Danh mục:</label>
                <select name="category" style="width: 100%; padding: 0.8rem; border-radius: 5px; border: 1px solid #ddd;">
                    <option value="">Tất cả</option>
                    <option value="dam-cuoi" <?php echo $category == 'dam-cuoi' ? 'selected' : ''; ?>>Đám cưới</option>
                    <option value="sinh-nhat" <?php echo $category == 'sinh-nhat' ? 'selected' : ''; ?>>Sinh nhật</option>
                    <option value="ngay-le" <?php echo $category == 'ngay-le' ? 'selected' : ''; ?>>Ngày lễ</option>
                    <option value="qua-tang" <?php echo $category == 'qua-tang' ? 'selected' : ''; ?>>Quà tặng</option>
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Sắp xếp:</label>
                <select name="sort" style="width: 100%; padding: 0.8rem; border-radius: 5px; border: 1px solid #ddd;">
                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                    <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                    <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                    <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Tên A-Z</option>
                    <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Tên Z-A</option>
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Giá từ:</label>
                <input type="number" name="min_price" value="<?php echo $min_price; ?>" placeholder="0" style="width: 100%; padding: 0.8rem; border-radius: 5px; border: 1px solid #ddd;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Đến:</label>
                <input type="number" name="max_price" value="<?php echo $max_price; ?>" placeholder="10000000" style="width: 100%; padding: 0.8rem; border-radius: 5px; border: 1px solid #ddd;">
            </div>

            <div>
                <button type="submit" style="width: 100%; padding: 0.8rem; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </div>
        </div>
    </form>
    <p style="margin-top: 1rem; color: #666;">Tìm thấy <?php echo $total_products; ?> sản phẩm</p>
</section>

<section class="products">
    <h1 class="title">sản phẩm</h1>

   <div class="box-container">
  <?php
     if(mysqli_num_rows($select_products) > 0){
        while($fetch_products = mysqli_fetch_assoc($select_products)){
  ?>
  <form action="" method="POST" class="box">
     <?php echo csrf_field(); ?>
     <a href="./view_page.php?pid=<?php echo $fetch_products['id']; ?>" class="fas fa-eye"></a>
     
     <?php
     // Stock badge
     $stock_status = $fetch_products['stock_status'] ?? 'in_stock';
     $stock_qty = $fetch_products['stock_quantity'] ?? 0;
     $is_available = $fetch_products['is_available'] ?? 1;
     
     if($stock_status == 'out_of_stock' || !$is_available):
     ?>
        <div style="position: absolute; top: 10px; left: 10px; background: #ef4444; color: white; padding: 0.5rem; border-radius: 5px; font-weight: bold;">
           ✖ Hết hàng
        </div>
     <?php elseif($stock_status == 'low_stock'): ?>
        <div style="position: absolute; top: 10px; left: 10px; background: #f59e0b; color: white; padding: 0.5rem; border-radius: 5px; font-weight: bold;">
           ⚠ Chỉ còn <?php echo $stock_qty; ?>
        </div>
     <?php endif; ?>
     
    <div class="price"><?php echo number_format($fetch_products['price'], 0, ',', '.'); ?>₫</div>
    <img src="../assets/uploads/products/<?php echo e($fetch_products['image']); ?>" alt="<?php echo e(fix_encoding($fetch_products['name'])); ?>" class="image">
    <?php $display_name = fix_encoding($fetch_products['name']); ?>
    <div class="name"><?php echo e($display_name); ?></div>
     
     <?php if($is_available && $stock_qty > 0): ?>
        <input type="number" name="product_quantity" value="1" min="1" max="<?php echo $stock_qty; ?>" class="qty" style="display:none;">
        <input type="hidden" name="product_id" value="<?php echo $fetch_products['id']; ?>">
        <input type="hidden" name="product_name" value="<?php echo $display_name; ?>">
        <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
        <input type="hidden" name="product_image" value="<?php echo e($fetch_products['image']); ?>">
        <input type="submit" value="Yêu Thích" name="add_to_wishlist" class="option-btn">
        <input type="button" value="Thêm Vào Giỏ" class="btn show-qty-btn">
        <input type="submit" value="Xác Nhận" name="add_to_cart" class="btn confirm-qty-btn" style="display:none;">
     <?php else: ?>
          <div style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 5px; margin-top: 1rem;">
              Sản phẩm tạm hết hàng
          </div>
     <?php endif; ?>
  </form>
  <?php
     }
  }else{
    echo '<p class="empty">Chưa có sản phẩm nào!</p>';
  }
  ?>
   </div>

   <!-- Pagination -->
   <?php if($total_pages > 1): ?>
   <div class="pagination" style="text-align: center; margin-top: 3rem;">
      <?php
      $query_string = http_build_query(array_merge($_GET, ['page' => '']));
      $query_string = rtrim($query_string, '=');
      
      if($page > 1): ?>
         <a href="?<?php echo $query_string; ?><?php echo $page-1; ?>" style="display: inline-block; padding: 1rem 1.5rem; margin: 0 0.3rem; background: #667eea; color: white; border-radius: 5px; text-decoration: none;">← Trước</a>
      <?php endif; ?>

      <?php for($i = 1; $i <= $total_pages; $i++): ?>
         <a href="?<?php echo $query_string; ?><?php echo $i; ?>" 
            style="display: inline-block; padding: 1rem 1.5rem; margin: 0 0.3rem; background: <?php echo $i == $page ? '#764ba2' : '#ddd'; ?>; color: <?php echo $i == $page ? 'white' : '#333'; ?>; border-radius: 5px; text-decoration: none;">
            <?php echo $i; ?>
         </a>
      <?php endfor; ?>

      <?php if($page < $total_pages): ?>
         <a href="?<?php echo $query_string; ?><?php echo $page+1; ?>" style="display: inline-block; padding: 1rem 1.5rem; margin: 0 0.3rem; background: #667eea; color: white; border-radius: 5px; text-decoration: none;">Sau →</a>
      <?php endif; ?>
   </div>
   <?php endif; ?>
</section>

<?php @include '../footer.php'; ?>

<script src="../js/script.js"></script>

</body>
</html>



