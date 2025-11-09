<?php
@include '../config.php';
@include 'includes/email_service.php';

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){
   header('location:../auth/login.php');
   exit;
}

// Chỉ xử lý khi bấm nút đặt hàng (có POST và tồn tại $_POST['order'])
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order'])){

   $name = sanitize_input($_POST['name'] ?? '');
   $number = sanitize_input($_POST['number'] ?? '');
   $email = sanitize_input($_POST['email'] ?? '');
   $method = sanitize_input($_POST['method'] ?? '');
   $address = sanitize_input($_POST['address'] ?? '');
   $placed_on = date('Y-m-d H:i:s');
   $payment_status = 'pending';
   $delivery_status = 'Đang xử lý';

   // Get cart items with prepared statement
   $cart_items = db_select($conn, "SELECT * FROM cart WHERE user_id = ?", "i", [$user_id]);

   $total_price = 0;
   $total_products = '';
   $order_items = [];

   if(count($cart_items) > 0){
       foreach($cart_items as $cart_item){
           $product_name = $cart_item['name'];
           $qty = (int)$cart_item['quantity'];
           $price = (float)$cart_item['price'];
           $total_price += ($price * $qty);
           $total_products .= $product_name . ' ('.$qty.') - ';
           
           // Store for email
           $order_items[] = [
               'name' => $product_name,
               'quantity' => $qty,
               'price' => $price
           ];
       }

       // Insert order with prepared statement
       $order_id = db_insert($conn, 
           "INSERT INTO orders(user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status, delivery_status) 
           VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
           "issssssdsss",
           [$user_id, $name, $number, $email, $method, $address, $total_products, $total_price, $placed_on, $payment_status, $delivery_status]
       );

       if($order_id){
           // Insert order items
           foreach($cart_items as $cart_item){
               $pid = (int)$cart_item['pid'];
               $qty = (int)$cart_item['quantity'];
               $price = (float)$cart_item['price'];

               db_insert($conn, 
                   "INSERT INTO order_items(order_id, product_id, quantity, price) VALUES(?, ?, ?, ?)",
                   "iiid",
                   [$order_id, $pid, $qty, $price]
               );
           }

           // Clear cart
           db_delete($conn, "DELETE FROM cart WHERE user_id = ?", "i", [$user_id]);
           
           // Send order confirmation email
           $order_details = [
               'items' => $order_items,
               'total' => $total_price,
               'address' => $address,
               'phone' => $number,
               'payment_method' => $method
           ];
           send_order_confirmation($order_id, $email, $name, $order_details);

           echo "Đặt hàng thành công! Chúng tôi đã gửi email xác nhận đến bạn.";
       }
   } else {
       echo "Giỏ hàng của bạn đang trống!";
   }
}

// Nếu chỉ GET → không echo gì thêm, để hiển thị giao diện bình thường
?>


