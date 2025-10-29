<?php
/**
 * Payment Return Page
 * Handles callback from payment gateways (MoMo, VNPay)
 */

@include '../config.php';
@include '../includes/payment_gateway.php';
@include '../includes/email_service.php';

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){
   header('location:../auth/login.php');
   exit;
}

$gateway = $_GET['gateway'] ?? '';
$payment_success = false;
$payment_message = '';
$order_id = null;

// Process MoMo return
if($gateway === 'momo') {
    $partnerCode = $_GET['partnerCode'] ?? '';
    $orderId = $_GET['orderId'] ?? '';
    $requestId = $_GET['requestId'] ?? '';
    $amount = $_GET['amount'] ?? 0;
    $orderInfo = $_GET['orderInfo'] ?? '';
    $orderType = $_GET['orderType'] ?? '';
    $transId = $_GET['transId'] ?? '';
    $resultCode = $_GET['resultCode'] ?? '';
    $message = $_GET['message'] ?? '';
    $payType = $_GET['payType'] ?? '';
    $responseTime = $_GET['responseTime'] ?? '';
    $extraData = $_GET['extraData'] ?? '';
    $signature = $_GET['signature'] ?? '';
    
    // Verify signature
    if(verify_momo_signature($_GET)) {
        if($resultCode == 0) {
            // Payment successful
            update_payment_status($orderId, 'completed', $_GET);
            $payment_success = true;
            $payment_message = 'Thanh toán MoMo thành công!';
            
            // Get order ID from transaction
            $transaction = db_fetch_one($conn, 
                "SELECT order_id FROM payment_transactions WHERE transaction_id = ?",
                "s",
                [$orderId]
            );
            $order_id = $transaction['order_id'] ?? null;
            
            // Send email notification
            if($order_id) {
                $order = db_fetch_one($conn,
                    "SELECT o.*, u.name as user_name FROM orders o 
                     LEFT JOIN users u ON o.user_id = u.id 
                     WHERE o.id = ?",
                    "i",
                    [$order_id]
                );
                
                if($order && !empty($order['email'])) {
                    send_order_status_update(
                        $order_id,
                        $order['email'],
                        $order['user_name'] ?? $order['name'],
                        'Đã thanh toán'
                    );
                }
            }
        } else {
            // Payment failed
            update_payment_status($orderId, 'failed', $_GET);
            $payment_success = false;
            $payment_message = 'Thanh toán MoMo thất bại: ' . $message;
        }
    } else {
        $payment_message = 'Chữ ký không hợp lệ. Có thể giao dịch bị giả mạo!';
    }
}

// Process VNPay return
elseif($gateway === 'vnpay') {
    $vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
    $vnp_TxnRef = $_GET['vnp_TxnRef'] ?? '';
    $vnp_Amount = ($_GET['vnp_Amount'] ?? 0) / 100; // Convert back from smallest unit
    $vnp_OrderInfo = $_GET['vnp_OrderInfo'] ?? '';
    $vnp_TransactionNo = $_GET['vnp_TransactionNo'] ?? '';
    
    // Verify signature
    if(verify_vnpay_signature($_GET)) {
        if($vnp_ResponseCode == '00') {
            // Payment successful
            update_payment_status($vnp_TxnRef, 'completed', $_GET);
            $payment_success = true;
            $payment_message = 'Thanh toán VNPay thành công!';
            
            // Get order ID from transaction
            $transaction = db_fetch_one($conn, 
                "SELECT order_id FROM payment_transactions WHERE transaction_id = ?",
                "s",
                [$vnp_TxnRef]
            );
            $order_id = $transaction['order_id'] ?? null;
            
            // Send email notification
            if($order_id) {
                $order = db_fetch_one($conn,
                    "SELECT o.*, u.name as user_name FROM orders o 
                     LEFT JOIN users u ON o.user_id = u.id 
                     WHERE o.id = ?",
                    "i",
                    [$order_id]
                );
                
                if($order && !empty($order['email'])) {
                    send_order_status_update(
                        $order_id,
                        $order['email'],
                        $order['user_name'] ?? $order['name'],
                        'Đã thanh toán'
                    );
                }
            }
        } else {
            // Payment failed
            update_payment_status($vnp_TxnRef, 'failed', $_GET);
            $payment_success = false;
            
            // VNPay error codes
            $error_messages = [
                '07' => 'Giao dịch bị nghi ngờ',
                '09' => 'Thẻ chưa đăng ký dịch vụ',
                '10' => 'Thẻ hết hạn',
                '11' => 'Thẻ bị khóa',
                '12' => 'Thẻ chưa kích hoạt',
                '13' => 'OTP không đúng',
                '24' => 'Khách hàng hủy giao dịch',
                '51' => 'Tài khoản không đủ số dư',
                '65' => 'Vượt quá số lần nhập OTP',
                '75' => 'Ngân hàng bảo trì',
                '79' => 'Số tiền vượt quá hạn mức',
            ];
            
            $payment_message = 'Thanh toán VNPay thất bại: ' . 
                ($error_messages[$vnp_ResponseCode] ?? 'Lỗi không xác định');
        }
    } else {
        $payment_message = 'Chữ ký không hợp lệ. Có thể giao dịch bị giả mạo!';
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Kết quả thanh toán - Flower Store</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <style>
   .payment-result {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
   }
   
   .result-container {
      background: white;
      padding: 3rem;
      border-radius: 10px;
      box-shadow: 0 0 30px rgba(0,0,0,0.1);
      max-width: 600px;
      text-align: center;
   }
   
   .result-icon {
      font-size: 5rem;
      margin-bottom: 2rem;
   }
   
   .result-icon.success {
      color: #10b981;
   }
   
   .result-icon.error {
      color: #ef4444;
   }
   
   .result-title {
      font-size: 2rem;
      margin-bottom: 1rem;
      color: #333;
   }
   
   .result-message {
      font-size: 1.2rem;
      color: #666;
      margin-bottom: 2rem;
   }
   
   .result-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
   }
   
   .result-button {
      padding: 1rem 2rem;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      transition: all 0.3s;
   }
   
   .result-button.primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
   }
   
   .result-button.primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
   }
   
   .result-button.secondary {
      background: #f3f4f6;
      color: #333;
   }
   
   .result-button.secondary:hover {
      background: #e5e7eb;
   }
   
   .transaction-details {
      background: #f9fafb;
      padding: 1.5rem;
      border-radius: 8px;
      margin: 2rem 0;
      text-align: left;
   }
   
   .transaction-details p {
      margin: 0.5rem 0;
      color: #4b5563;
   }
   
   .transaction-details strong {
      color: #1f2937;
   }
   </style>
</head>
<body>

<section class="payment-result">
   <div class="result-container">
      <?php if($payment_success): ?>
         <div class="result-icon success">
            <i class="fas fa-check-circle"></i>
         </div>
         <h1 class="result-title">Thanh toán thành công! 🎉</h1>
         <p class="result-message"><?php echo e($payment_message); ?></p>
         
         <?php if($order_id): ?>
         <div class="transaction-details">
            <p><strong>Mã đơn hàng:</strong> #<?php echo e($order_id); ?></p>
            <p><strong>Cổng thanh toán:</strong> <?php echo e(strtoupper($gateway)); ?></p>
            <p><strong>Thời gian:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
            <p style="color: #10b981; font-weight: bold;">
               <i class="fas fa-check"></i> Đơn hàng đã được thanh toán
            </p>
         </div>
         <?php endif; ?>
         
         <div class="result-buttons">
            <a href="orders.php" class="result-button primary">
               <i class="fas fa-list"></i> Xem đơn hàng
            </a>
            <a href="shop.php" class="result-button secondary">
               <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
            </a>
         </div>
      <?php else: ?>
         <div class="result-icon error">
            <i class="fas fa-times-circle"></i>
         </div>
         <h1 class="result-title">Thanh toán thất bại</h1>
         <p class="result-message"><?php echo e($payment_message); ?></p>
         
         <div class="transaction-details">
            <p><strong>Cổng thanh toán:</strong> <?php echo e(strtoupper($gateway)); ?></p>
            <p><strong>Thời gian:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
            <p style="color: #ef4444;">
               <i class="fas fa-exclamation-triangle"></i> Vui lòng thử lại hoặc chọn phương thức thanh toán khác
            </p>
         </div>
         
         <div class="result-buttons">
            <a href="checkout.php" class="result-button primary">
               <i class="fas fa-redo"></i> Thử lại
            </a>
            <a href="cart.php" class="result-button secondary">
               <i class="fas fa-shopping-cart"></i> Giỏ hàng
            </a>
         </div>
      <?php endif; ?>
   </div>
</section>

</body>
</html>

