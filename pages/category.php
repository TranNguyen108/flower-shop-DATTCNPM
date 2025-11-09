<?php
/**
 * Category Page - Unified Category Handler
 * Replaces: hoa-dam-cuoi.php, hoa-sinh-nhat.php, hoa-ngay-le.php, qua-tang.php
 * Updated: December 26, 2025
 */

@include '../config.php';

$user_id = $_SESSION['user_id'] ?? null;

if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

// Get category from URL parameter
$category = $_GET['cat'] ?? 'dam-cuoi';

// Category mapping
$category_map = [
    'dam-cuoi' => 'Hoa Đám Cưới',
    'sinh-nhat' => 'Hoa Sinh Nhật',
    'ngay-le' => 'Hoa Ngày Lễ',
    'qua-tang' => 'Quà Tặng'
];

// Validate category
if(!array_key_exists($category, $category_map)){
    $category = 'dam-cuoi';
}

$category_name = $category_map[$category];

// Get sorting & filtering
$sort = $_GET['sort'] ?? 'newest';
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 10000000;
$page = $_GET['page'] ?? 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query with filters
$where = "category = ? AND price BETWEEN ? AND ?";
$params = [$category, $min_price, $max_price];
$types = "sii";

// Add sorting
switch($sort) {
    case 'price-low':
        $order = "ORDER BY price ASC";
        break;
    case 'price-high':
        $order = "ORDER BY price DESC";
        break;
    case 'popular':
        $order = "ORDER BY id ASC"; // Fallback - view_count column doesn't exist
        break;
    default:
        $order = "ORDER BY id DESC";
}

// Get products
$query = "SELECT * FROM products WHERE $where $order LIMIT ? OFFSET ?";
$products = db_select_array($conn, $query, $types . "ii", array_merge($params, [$per_page, $offset]));

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM products WHERE $where";
$count_result = db_fetch_one($conn, $count_query, $types, $params);
$total_products = $count_result['total'] ?? 0;
$total_pages = ceil($total_products / $per_page);

// Add to wishlist
if(isset($_POST['add_to_wishlist'])){
   if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
       $message[] = 'Lỗi bảo mật!';
   } else {
       $product_id = (int)$_POST['product_id'];
       $product_name = sanitize_input($_POST['product_name']);
       $product_price = (int)$_POST['product_price'];
       $product_image = sanitize_input($_POST['product_image']);
       
       $check_wishlist = db_count($conn, "SELECT * FROM wishlist WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);
       $check_cart = db_count($conn, "SELECT * FROM cart WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);

       if($check_wishlist > 0){
           $message[] = 'Đã có trong danh sách yêu thích!';
       }elseif($check_cart > 0){
           $message[] = 'Đã có trong giỏ hàng!';
       }else{
           db_insert($conn, 
               "INSERT INTO wishlist (user_id, pid, name, price, image) VALUES (?, ?, ?, ?, ?)",
               "iisis",
               [$user_id, $product_id, $product_name, $product_price, $product_image]
           );
           $message[] = 'Đã thêm vào danh sách yêu thích!';
       }
   }
}

// Add to cart
if(isset($_POST['add_to_cart'])){
   if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
       $message[] = 'Lỗi bảo mật!';
   } else {
       $product_id = (int)$_POST['product_id'];
       $product_name = sanitize_input($_POST['product_name']);
       $product_price = (int)$_POST['product_price'];
       $product_image = sanitize_input($_POST['product_image']);
       $product_quantity = (int)($_POST['quantity'] ?? 1);
       
       if($product_quantity < 1) $product_quantity = 1;
       if($product_quantity > 999) $product_quantity = 999;
       
       $check_cart = db_fetch_one($conn, "SELECT * FROM cart WHERE pid = ? AND user_id = ?", "ii", [$product_id, $user_id]);

       if($check_cart){
           db_update($conn,
               "UPDATE cart SET quantity = quantity + ? WHERE pid = ? AND user_id = ?",
               "iii",
               [$product_quantity, $product_id, $user_id]
           );
           $message[] = 'Cập nhật số lượng trong giỏ hàng!';
       }else{
           db_insert($conn,
               "INSERT INTO cart (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)",
               "iisiii",
               [$user_id, $product_id, $product_name, $product_price, $product_quantity, $product_image]
           );
           $message[] = 'Thêm vào giỏ hàng thành công!';
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
   <title><?php echo $category_name; ?></title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/product-cards.css">
</head>
<body>

<?php @include '../header.php'; ?>

<section class="heading">
   <h1><?php echo $category_name; ?></h1>
   <p><a href="./home.php">Trang chủ</a> / <span><?php echo $category_name; ?></span></p>
</section>

<section class="products">

   <div class="filter-container">
      <form method="GET">
         <input type="hidden" name="cat" value="<?php echo $category; ?>">
         
         <label>Sắp xếp:</label>
         <select name="sort" onchange="this.form.submit()">
            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
            <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Phổ biến</option>
            <option value="price-low" <?php echo $sort == 'price-low' ? 'selected' : ''; ?>>Giá: Thấp đến Cao</option>
            <option value="price-high" <?php echo $sort == 'price-high' ? 'selected' : ''; ?>>Giá: Cao đến Thấp</option>
         </select>
      </form>
   </div>

   <div class="box-container">
      <?php
      if($products && count($products) > 0){
         foreach($products as $fetch_products){
            echo '
            <form class="box" method="POST">
               <input type="hidden" name="csrf_token" value="'.generate_csrf_token().'">
               <input type="hidden" name="product_id" value="'.$fetch_products['id'].'">
               <input type="hidden" name="product_name" value="'.$fetch_products['name'].'">
               <input type="hidden" name="product_price" value="'.$fetch_products['price'].'">
               <input type="hidden" name="product_image" value="'.$fetch_products['image'].'">
               
               <div class="price">'.number_format($fetch_products['price'], 0, ',', '.').'<sup>đ</sup></div>
               <a href="./view_page.php?pid='.$fetch_products['id'].'" class="fas fa-eye"></a>
               <img class="image" src="../assets/uploads/products/'.$fetch_products['image'].'" alt="'.$fetch_products['name'].'">
               <div class="name">'.$fetch_products['name'].'</div>
               <input type="hidden" name="quantity" value="1">
               <button type="submit" class="option-btn" name="add_to_wishlist">Yêu Thích</button>
               <button type="submit" class="btn" name="add_to_cart">Thêm Vào Giỏ</button>
            </form>
            ';
         }
      } else {
         echo '<p class="empty">Không có sản phẩm nào trong danh mục này</p>';
      }
      ?>
   </div>

   <!-- Pagination -->
   <div class="pagination">
      <?php
      if($total_pages > 1){
         if($page > 1){
            echo '<a href="?cat='.$category.'&sort='.$sort.'&page=1" class="page-link">« Đầu</a>';
            echo '<a href="?cat='.$category.'&sort='.$sort.'&page='.($page-1).'" class="page-link">‹ Trước</a>';
         }
         
         for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++){
            $active = $i == $page ? 'active' : '';
            echo '<a href="?cat='.$category.'&sort='.$sort.'&page='.$i.'" class="page-link '.$active.'">'.$i.'</a>';
         }
         
         if($page < $total_pages){
            echo '<a href="?cat='.$category.'&sort='.$sort.'&page='.($page+1).'" class="page-link">Tiếp ›</a>';
            echo '<a href="?cat='.$category.'&sort='.$sort.'&page='.$total_pages.'" class="page-link">Cuối »</a>';
         }
      }
      ?>
   </div>

</section>

<?php @include '../footer.php'; ?>

<script src="../js/script.js"></script>

</body>
</html>


