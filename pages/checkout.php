<?php
/**
 * Checkout Page - Enhanced Security
 * Prepared Statements, CSRF Protection, Validation
 */

@include '../config.php';
@include '../includes/payment_gateway.php';
@include '../includes/email_service.php';
@include '../includes/inventory_functions.php';

$user_id = $_SESSION['user_id'] ?? null;

if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

// Lấy danh sách sản phẩm được chọn để thanh toán
$selected_items = [];
if(isset($_GET['items']) && !empty($_GET['items'])) {
    $selected_items = array_map('intval', explode(',', $_GET['items']));
} elseif(isset($_POST['selected_items']) && !empty($_POST['selected_items'])) {
    $selected_items = array_map('intval', explode(',', $_POST['selected_items']));
}

$message = [];

if(isset($_POST['order'])){
    // CSRF verification
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message[] = 'Lỗi bảo mật!';
    } else {
        // Sanitize and validate inputs
        $name = sanitize_input($_POST['name']);
        $number = sanitize_input($_POST['number']);
        $email = sanitize_input($_POST['email']);
        $method = sanitize_input($_POST['method']);
        $address = 'Số nhà ' . sanitize_input($_POST['flat']) . ', ' . 
                   sanitize_input($_POST['street']) . ', ' . 
                   sanitize_input($_POST['city']) . ', ' . 
                   sanitize_input($_POST['country']) . ' - ' . 
                   sanitize_input($_POST['pin_code']);
        $placed_on = date('d-m-Y');

        // Validation
        $errors = [];
        if (strlen($name) < 3) $errors[] = 'Tên phải có ít nhất 3 ký tự!';
        if (!validate_email($email)) $errors[] = 'Email không hợp lệ!';
        if (!validate_phone($number)) $errors[] = 'Số điện thoại không hợp lệ!';
        
        if (!empty($errors)) {
            $message = array_merge($message, $errors);
        } else {
            // Get cart items - chỉ lấy sản phẩm được chọn
            $cart_total = 0;
            $cart_products = [];
            $cart_item_ids = [];

            // Nếu có selected items thì chỉ lấy những sản phẩm đó
            if(!empty($selected_items)) {
                $placeholders = implode(',', array_fill(0, count($selected_items), '?'));
                $types = 'i' . str_repeat('i', count($selected_items));
                $params = array_merge([$user_id], $selected_items);
                $cart_query = db_select($conn, "SELECT * FROM cart WHERE user_id = ? AND id IN ($placeholders)", $types, $params);
            } else {
                $cart_query = db_select($conn, "SELECT * FROM cart WHERE user_id = ?", "i", [$user_id]);
            }
            
            if(mysqli_num_rows($cart_query) > 0){
                while($cart_item = mysqli_fetch_assoc($cart_query)){
                    $cart_products[] = $cart_item['name'].' ('.$cart_item['quantity'].') ';
                    $sub_total = ($cart_item['price'] * $cart_item['quantity']);
                    $cart_total += $sub_total;
                    $cart_item_ids[] = $cart_item['id'];
                }
            }

            $total_products = implode(', ', $cart_products);

            if($cart_total == 0){
                $message[] = 'Giỏ hàng của bạn đang trống!';
            } else {
                // Validate stock availability
                $stock_validation = validate_cart_stock($user_id);
                
                if(!$stock_validation['valid']) {
                    $message = array_merge($message, $stock_validation['errors']);
                } else {
                    // Check duplicate order
                    $existing = db_fetch_one($conn, 
                        "SELECT id FROM orders WHERE user_id = ? AND total_products = ? AND total_price = ? AND placed_on = ?",
                        "isis", [$user_id, $total_products, $cart_total, $placed_on]
                    );
                    
                    if($existing){
                        $message[] = 'Đơn hàng đã được đặt trước đó!';
                    } else {
                        // Insert order
                        $order_id = db_insert($conn, 
                            "INSERT INTO orders (user_id, name, number, email, method, payment_method, address, total_products, total_price, placed_on, payment_status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')",
                            "isssssssss",
                            [$user_id, $name, $number, $email, $method, $method, $address, $total_products, $cart_total, $placed_on]
                        );

                        if($order_id){
                            // Insert order items
                            $cart_query = db_select($conn, "SELECT * FROM cart WHERE user_id = ?", "i", [$user_id]);
                            $order_items = [];
                            while($cart_item = mysqli_fetch_assoc($cart_query)){
                                db_insert($conn, 
                                    "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)",
                                    "iiii",
                                    [$order_id, $cart_item['pid'], $cart_item['quantity'], $cart_item['price']]
                                );
                                
                                $order_items[] = [
                                    'name' => $cart_item['name'],
                                    'quantity' => $cart_item['quantity'],
                                    'price' => $cart_item['price']
                                ];
                            }
                            
                            // Reduce stock for all order items
                            if(!process_order_stock($order_id)) {
                                $message[] = 'Lỗi khi cập nhật tồn kho. Vui lòng liên hệ admin.';
                            }

                            // Clear only selected items from cart
                            if(!empty($cart_item_ids)) {
                                $placeholders = implode(',', array_fill(0, count($cart_item_ids), '?'));
                                $types = str_repeat('i', count($cart_item_ids) + 1);
                                $params = array_merge([$user_id], $cart_item_ids);
                                db_delete($conn, "DELETE FROM cart WHERE user_id = ? AND id IN ($placeholders)", $types, $params);
                            }
                            
                            // Process payment based on method
                            $payment_result = null;
                            
                            if($method === 'momo') {
                                $payment_result = process_momo_payment($order_id, $cart_total, $total_products);
                            } 
                            elseif($method === 'vnpay') {
                                $payment_result = process_vnpay_payment($order_id, $cart_total, $total_products);
                            }
                            elseif($method === 'cod') {
                                $payment_result = process_cod_payment($order_id, $cart_total);
                                
                                // Send order confirmation for COD
                                $order_details = [
                                    'items' => $order_items,
                                    'total' => $cart_total,
                                    'address' => $address,
                                    'phone' => $number,
                                    'payment_method' => 'Thanh toán khi nhận hàng (COD)'
                                ];
                                send_order_confirmation($order_id, $email, $name, $order_details);
                            }
                            
                            // Handle payment result
                            if($payment_result && $payment_result['success']) {
                                if(isset($payment_result['payment_url'])) {
                                    // Redirect to payment gateway
                                    header('Location: ' . $payment_result['payment_url']);
                                    exit;
                                } else {
                                    // COD - show success message
                                    $message[] = 'Đặt hàng thành công! Chúng tôi đã gửi email xác nhận.';
                                    header('refresh:3;url=orders.php');
                                }
                            } else {
                                $message[] = $payment_result['message'] ?? 'Lỗi khi xử lý thanh toán!';
                            }
                        } else {
                            $message[] = 'Lỗi khi đặt hàng. Vui lòng thử lại!';
                        }
                    }
                }
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
   <title>Thanh toán đơn hàng</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/style-enhanced.css">
</head>
<body>
<?php @include '../header.php'; ?>

<section class="heading">
    <h3>Thanh toán đơn hàng</h3>
    <p><a href="./home.php">Trang chủ</a> / Thanh toán</p>
</section>

<section class="display-order">
    <h3 style="color: #667eea; margin-bottom: 15px;"><i class="fas fa-shopping-basket"></i> Sản phẩm thanh toán</h3>
    <?php
        $grand_total = 0;
        $display_item_count = 0;
        
        // Nếu có selected items thì chỉ hiển thị những sản phẩm đó
        if(!empty($selected_items)) {
            $placeholders = implode(',', array_fill(0, count($selected_items), '?'));
            $types = 'i' . str_repeat('i', count($selected_items));
            $params = array_merge([$user_id], $selected_items);
            $cart_items_result = db_select($conn, "SELECT * FROM `cart` WHERE user_id = ? AND id IN ($placeholders)", $types, $params);
        } else {
            $cart_items_result = db_select($conn, "SELECT * FROM `cart` WHERE user_id = ?", "i", [$user_id]);
        }
        
        if($cart_items_result && mysqli_num_rows($cart_items_result) > 0){
            while($fetch_cart = mysqli_fetch_assoc($cart_items_result)){
            $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
            $grand_total += $total_price;
            $display_item_count++;
    ?>    
    <p> <?php echo e($fetch_cart['name']) ?> <span>(<?php echo number_format($fetch_cart['price'],0,',','.').'₫'.' x '.(int)$fetch_cart['quantity']  ?>)</span> </p>
    <?php
        }
        }else{
            echo '<p class="empty">Không có sản phẩm nào được chọn để thanh toán. <a href="./cart.php">Quay lại giỏ hàng</a></p>';
        }
    ?>
    <div class="grand-total">Tổng cộng (<?php echo $display_item_count; ?> sản phẩm): <span><?php echo number_format($grand_total,0,',','.'); ?>₫</span></div>
</section>

<section class="checkout">

    <form action="" method="POST">
        <?php echo csrf_field(); ?>
        <?php if(!empty($selected_items)): ?>
        <input type="hidden" name="selected_items" value="<?php echo implode(',', $selected_items); ?>">
        <?php endif; ?>
        <h3>Đặt hàng ngay</h3>

        <div class="flex">
            <div class="inputBox">
                <span>Họ và tên:</span>
                <input type="text" name="name" placeholder="Nhập họ tên của bạn" required>
            </div>
            <div class="inputBox">
                <span>Số điện thoại:</span>
                <input type="number" name="number" min="0" placeholder="Nhập số điện thoại" required>
            </div>
            <div class="inputBox">
                <span>Email:</span>
                <input type="email" name="email" placeholder="Nhập email của bạn" required>
            </div>
            <div class="inputBox">
                <span>Phương thức thanh toán:</span>
                <select name="method" id="payment-method" required>
                    <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                    <option value="momo">Ví điện tử MoMo</option>
                    <option value="vnpay" disabled>VNPay (Coming soon)</option>
                    <option value="banking" disabled>Chuyển khoản ngân hàng (Coming soon)</option>
                </select>
            </div>
            
            <div id="payment-info" style="background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 15px 0; display: none;">
                <p style="margin: 0; color: #2e7d32;">
                    <i class="fas fa-info-circle"></i> 
                    <strong id="payment-description"></strong>
                </p>
            </div>
            <div class="inputBox">
                <span>Địa chỉ dòng 1:</span>
                <input type="text" name="flat" placeholder="VD: Số nhà" required>
            </div>
            <div class="inputBox">
                <span>Địa chỉ dòng 2:</span>
                <input type="text" name="street" placeholder="VD: Tên đường" required>
            </div>
            <div class="inputBox">
                <span>Thành phố:</span>
                <input type="text" name="city" placeholder="VD: Hà Nội" required>
            </div>
            <div class="inputBox">
                <span>Tỉnh / Thành:</span>
                <input type="text" name="state" placeholder="VD: Hà Nội" required>
            </div>
            <div class="inputBox">
                <span>Quốc gia:</span>
                <input type="text" name="country" placeholder="VD: Việt Nam" required>
            </div>
            <div class="inputBox">
                <span>Mã bưu điện:</span>
                <input type="number" min="0" name="pin_code" placeholder="VD: 100000" required>
            </div>
        </div>

        <input type="submit" name="order" value="Đặt hàng ngay" class="btn" id="submit-order">

    </form>

</section>

<?php @include '../footer.php'; ?>

<script src="../js/script.js"></script>
<script>
// Payment method selector
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethod = document.getElementById('payment-method');
    const paymentInfo = document.getElementById('payment-info');
    const paymentDescription = document.getElementById('payment-description');
    const submitButton = document.getElementById('submit-order');
    
    const paymentDescriptions = {
        'cod': 'Thanh toán bằng tiền mặt khi nhận hàng. Miễn phí ship nội thành!',
        'momo': 'Thanh toán qua ví điện tử MoMo. Bạn sẽ được chuyển đến trang MoMo để hoàn tất thanh toán.',
        'vnpay': 'Thanh toán qua cổng VNPay. Hỗ trợ thẻ ATM, Visa, MasterCard.',
        'banking': 'Chuyển khoản trực tiếp vào tài khoản ngân hàng của shop.'
    };
    
    const submitButtonText = {
        'cod': 'Đặt hàng ngay',
        'momo': 'Thanh toán với MoMo',
        'vnpay': 'Thanh toán với VNPay',
        'banking': 'Xác nhận đơn hàng'
    };
    
    paymentMethod.addEventListener('change', function() {
        const selected = this.value;
        
        if(paymentDescriptions[selected]) {
            paymentDescription.textContent = paymentDescriptions[selected];
            paymentInfo.style.display = 'block';
            submitButton.value = submitButtonText[selected];
        } else {
            paymentInfo.style.display = 'none';
        }
    });
    
    // Trigger on load
    if(paymentMethod.value) {
        paymentMethod.dispatchEvent(new Event('change'));
    }
});
</script>
</body>
</html>


