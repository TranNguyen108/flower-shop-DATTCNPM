<?php

@include '../config.php';
@include 'includes/inventory_functions.php';


$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

if(isset($_POST['add_to_wishlist'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message[] = 'Y�u c?u kh�ng h?p l?';
    } else {
        $product_id = (int)$_POST['product_id'];
        $product_name = sanitize_input($_POST['product_name']);
        $product_price = (float)$_POST['product_price'];
        $product_image = sanitize_input($_POST['product_image']);

        $check_wishlist = db_count("SELECT COUNT(*) FROM `wishlist` WHERE name = ? AND user_id = ?", [$product_name, $user_id]);
        $check_cart = db_count("SELECT COUNT(*) FROM `cart` WHERE name = ? AND user_id = ?", [$product_name, $user_id]);

        if($check_wishlist > 0){
            $message[] = 'S?n ph?m d� c� trong danh s�ch y�u th�ch!';
        }elseif($check_cart > 0){
            $message[] = 'S?n ph?m d� c� trong gi? h�ng!';
        }else{
            db_insert("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?, ?, ?, ?, ?)", 
                     [$user_id, $product_id, $product_name, $product_price, $product_image]);
            $message[] = '�� th�m v�o danh s�ch y�u th�ch!';
        }
    }
}

if(isset($_POST['add_to_cart'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message[] = 'Yêu cầu không hợp lệ';
    } else {
        $product_id = (int)$_POST['product_id'];
        $product_name = sanitize_input($_POST['product_name']);
        $product_price = (float)$_POST['product_price'];
        $product_image = sanitize_input($_POST['product_image']);
        $product_quantity = max(1, (int)$_POST['product_quantity']);

        // Check stock availability
        $stock_check = check_stock_availability($conn, $product_id, $product_quantity);
        
        if(!$stock_check['available']){
            $message[] = $stock_check['message'];
        } else {
            $check_cart = db_count($conn, "SELECT COUNT(*) FROM `cart` WHERE name = ? AND user_id = ?", "si", [$product_name, $user_id]);

            if($check_cart > 0){
                $message[] = 'Sản phẩm đã có trong giỏ hàng!';
            }else{
                $check_wishlist = db_count($conn, "SELECT COUNT(*) FROM `wishlist` WHERE name = ? AND user_id = ?", "si", [$product_name, $user_id]);

                if($check_wishlist > 0){
                    db_delete($conn, "DELETE FROM `wishlist` WHERE name = ? AND user_id = ?", "si", [$product_name, $user_id]);
                }

                db_insert($conn, "INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?, ?, ?, ?, ?, ?)", 
                         "iisdis", [$user_id, $product_id, $product_name, $product_price, $product_quantity, $product_image]);
                $message[] = 'Đã thêm sản phẩm vào giỏ hàng!';
            }
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
   <title>T�m ki?m s?n ph?m</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/style-enhanced.css">
   <link rel="stylesheet" href="../css/product-cards.css">

</head>
<body>
   
<?php @include '../header.php'; ?>

<section class="heading">
    <h3>T�m ki?m s?n ph?m</h3>
    <p><a href="./home.php">Trang ch?</a> / T�m ki?m</p>
</section>

<section class="search-form">
    <form action="" method="POST">
        <?php echo csrf_field(); ?>
        <input type="text" class="box" placeholder="Tìm kiếm sản phẩm..." name="search_box" value="<?php echo isset($_POST['search_box']) ? e($_POST['search_box']) : (isset($_GET['search_box']) ? e($_GET['search_box']) : ''); ?>">
        <input type="submit" class="btn" value="Tìm kiếm" name="search_btn">
    </form>
</section>

<section class="products" style="padding-top: 0;">

   <div class="box-container">

      <?php
        $search_performed = false;
        $search_box = '';
        
        // Handle both POST and GET requests
        if(isset($_POST['search_btn']) || isset($_GET['search_box'])){
            $search_performed = true;
            
            if(isset($_POST['search_btn'])) {
                if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
                    echo '<p class="empty">Yêu cầu không hợp lệ!</p>';
                    $search_performed = false;
                } else {
                    $search_box = sanitize_input($_POST['search_box']);
                }
            } else {
                $search_box = sanitize_input($_GET['search_box'] ?? '');
            }
            
            if($search_performed && !empty($search_box)){
                // Enhanced search - search in name and details
                $search_pattern = '%' . $search_box . '%';
                $products = db_select($conn,
                    "SELECT * FROM products 
                     WHERE (name LIKE ? OR details LIKE ?) 
                     AND is_available = 1
                     ORDER BY 
                        CASE 
                            WHEN name LIKE ? THEN 1
                            WHEN name LIKE ? THEN 2
                            ELSE 3
                        END,
                        stock_status ASC,
                        name ASC",
                    "ssss",
                    [$search_pattern, $search_pattern, $search_box . '%', '%' . $search_box . '%']
                );
                
                if(!empty($products)){
                    echo '<p style="text-align: center; font-size: 1.2rem; color: #666; margin-bottom: 2rem;">Tìm thấy <strong>' . count($products) . '</strong> sản phẩm cho "<strong>' . e($search_box) . '</strong>"</p>';
                    
                    foreach($products as $fetch_products){
                        $stock_status = $fetch_products['stock_status'] ?? 'in_stock';
                        $stock_qty = $fetch_products['stock_quantity'] ?? 0;
                        $is_available = $fetch_products['is_available'] ?? 1;
      ?>
      <form action="" method="POST" class="box">
         <?php echo csrf_field(); ?>
         <a href="./view_page.php?pid=<?php echo (int)$fetch_products['id']; ?>" class="fas fa-eye"></a>
         
         <?php if($stock_status == 'out_of_stock' || !$is_available): ?>
            <div style="position: absolute; top: 10px; left: 10px; background: #ef4444; color: white; padding: 0.5rem; border-radius: 5px; font-weight: bold; z-index: 10;">
               ❌ Hết hàng
            </div>
         <?php elseif($stock_status == 'low_stock'): ?>
            <div style="position: absolute; top: 10px; left: 10px; background: #f59e0b; color: white; padding: 0.5rem; border-radius: 5px; font-weight: bold; z-index: 10;">
               ⚠️ Chỉ còn <?php echo $stock_qty; ?>
            </div>
         <?php endif; ?>
         
         <div class="price"><?php echo number_format($fetch_products['price'], 0, ',', '.') . 'đ'; ?></div>
         <img src="../assets/uploads/products/<?php echo e($fetch_products['image']); ?>" alt="<?php echo e($fetch_products['name']); ?>" class="image">
         <div class="name"><?php echo e($fetch_products['name']); ?></div>
         
         <?php if($is_available && $stock_qty > 0): ?>
            <input type="number" name="product_quantity" value="1" min="1" max="<?php echo $stock_qty; ?>" class="qty" style="display:none;">
            <input type="hidden" name="product_id" value="<?php echo (int)$fetch_products['id']; ?>">
            <input type="hidden" name="product_name" value="<?php echo e($fetch_products['name']); ?>">
            <input type="hidden" name="product_price" value="<?php echo (float)$fetch_products['price']; ?>">
            <input type="hidden" name="product_image" value="<?php echo e($fetch_products['image']); ?>">
            <input type="submit" value="Thêm vào yêu thích" name="add_to_wishlist" class="option-btn">
            <input type="button" value="Thêm vào giỏ hàng" class="btn show-qty-btn">
            <input type="submit" value="Xác nhận" name="add_to_cart" class="btn confirm-qty-btn" style="display:none;">
         <?php else: ?>
            <div style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 5px; margin-top: 1rem;">
               Sản phẩm tạm hết hàng
            </div>
         <?php endif; ?>
      </form>
      <?php
                    }
                }else{
                    echo '<p class="empty">Không tìm thấy sản phẩm nào cho "<strong>' . e($search_box) . '</strong>"</p>';
                    echo '<div style="text-align: center; margin-top: 2rem;">';
                    echo '<a href="./shop.php" class="btn" style="display: inline-block;">Xem tất cả sản phẩm</a>';
                    echo '</div>';
                }
            }
        }else{
            echo '<p class="empty">Vui lòng nhập từ khóa để tìm kiếm!</p>';
            echo '<div style="text-align: center; margin-top: 2rem;">';
            echo '<a href="./shop.php" class="btn" style="display: inline-block;">Khám phá sản phẩm</a>';
            echo '</div>';
        }
      ?>

   </div>

</section>

<?php @include '../footer.php'; ?>

<script src="../js/script.js"></script>

</body>
</html>



