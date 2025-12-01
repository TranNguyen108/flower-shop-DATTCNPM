<?php
/**
 * Payment Gateway Integration
 * Supports: MoMo, VNPay, Cash on Delivery
 */

// Don't require config.php here - it's already loaded by main files

// ============= MOMO CONFIGURATION =============
define('MOMO_PARTNER_CODE', 'MOMOBKUN20180529'); // Demo partner code
define('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j'); // Demo access key
define('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'); // Demo secret key
define('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');
define('MOMO_IPN_URL', ''); // Your IPN callback URL
define('MOMO_REDIRECT_URL', ''); // Your return URL

// ============= VNPAY CONFIGURATION =============
define('VNPAY_TMN_CODE', 'YOUR_TMN_CODE'); // Get from VNPay
define('VNPAY_HASH_SECRET', 'YOUR_HASH_SECRET'); // Get from VNPay
define('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
define('VNPAY_RETURN_URL', ''); // Your return URL

/**
 * Initialize payment session
 */
function init_payment_session($order_id, $amount, $order_info) {
    $_SESSION['payment_order_id'] = $order_id;
    $_SESSION['payment_amount'] = $amount;
    $_SESSION['payment_info'] = $order_info;
    $_SESSION['payment_token'] = bin2hex(random_bytes(32));
}

/**
 * Verify payment session
 */
function verify_payment_session($token) {
    return isset($_SESSION['payment_token']) && $_SESSION['payment_token'] === $token;
}

/**
 * Clear payment session
 */
function clear_payment_session() {
    unset($_SESSION['payment_order_id']);
    unset($_SESSION['payment_amount']);
    unset($_SESSION['payment_info']);
    unset($_SESSION['payment_token']);
}

/**
 * Process MoMo Payment
 */
function process_momo_payment($order_id, $amount, $order_info) {
    global $conn;
    
    $endpoint = MOMO_ENDPOINT;
    $partnerCode = MOMO_PARTNER_CODE;
    $accessKey = MOMO_ACCESS_KEY;
    $secretKey = MOMO_SECRET_KEY;
    
    // Generate URLs
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']);
    
    $redirectUrl = $base_url . '/payment_return.php?gateway=momo';
    $ipnUrl = $base_url . '/payment_ipn.php?gateway=momo';
    
    $orderId = 'ORD' . $order_id . '_' . time();
    $requestId = time() . "";
    $amount = (string)$amount;
    $orderInfo = "Thanh toán đơn hàng #" . $order_id . " - " . $order_info;
    $requestType = "payWithATM";
    $extraData = "";
    
    // Create signature
    $rawHash = "accessKey=" . $accessKey . 
               "&amount=" . $amount . 
               "&extraData=" . $extraData . 
               "&ipnUrl=" . $ipnUrl . 
               "&orderId=" . $orderId . 
               "&orderInfo=" . $orderInfo . 
               "&partnerCode=" . $partnerCode . 
               "&redirectUrl=" . $redirectUrl . 
               "&requestId=" . $requestId . 
               "&requestType=" . $requestType;
    
    $signature = hash_hmac("sha256", $rawHash, $secretKey);
    
    $data = [
        'partnerCode' => $partnerCode,
        'partnerName' => "Flower Store",
        'storeId' => "FlowerStore",
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'lang' => 'vi',
        'extraData' => $extraData,
        'requestType' => $requestType,
        'signature' => $signature
    ];
    
    // Send request to MoMo
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    $jsonResult = json_decode($result, true);
    
    // Save payment transaction
    if (isset($jsonResult['payUrl'])) {
        db_insert($conn,
            "INSERT INTO payment_transactions (order_id, gateway, transaction_id, amount, status, request_data, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            "issdss",
            [$order_id, 'momo', $orderId, $amount, 'pending', json_encode($data)]
        );
        
        return [
            'success' => true,
            'payment_url' => $jsonResult['payUrl'],
            'transaction_id' => $orderId
        ];
    }
    
    return [
        'success' => false,
        'message' => $jsonResult['message'] ?? 'Lỗi kết nối MoMo'
    ];
}

/**
 * Process VNPay Payment
 */
function process_vnpay_payment($order_id, $amount, $order_info) {
    global $conn;
    
    $vnp_TmnCode = VNPAY_TMN_CODE;
    $vnp_HashSecret = VNPAY_HASH_SECRET;
    $vnp_Url = VNPAY_URL;
    
    // Generate URLs
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']);
    $vnp_Returnurl = $base_url . '/payment_return.php?gateway=vnpay';
    
    $vnp_TxnRef = 'ORD' . $order_id . '_' . time();
    $vnp_OrderInfo = "Thanh toán đơn hàng #" . $order_id;
    $vnp_OrderType = 'billpayment';
    $vnp_Amount = $amount * 100; // VNPay uses smallest currency unit
    $vnp_Locale = 'vn';
    $vnp_BankCode = '';
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
    
    $inputData = [
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => date('YmdHis'),
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $vnp_TxnRef,
    ];
    
    if (!empty($vnp_BankCode)) {
        $inputData['vnp_BankCode'] = $vnp_BankCode;
    }
    
    ksort($inputData);
    $query = "";
    $i = 0;
    $hashdata = "";
    
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }
    
    $vnp_Url = $vnp_Url . "?" . $query;
    
    if (!empty($vnp_HashSecret)) {
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
    }
    
    // Save payment transaction
    db_insert($conn,
        "INSERT INTO payment_transactions (order_id, gateway, transaction_id, amount, status, request_data, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, NOW())",
        "issdss",
        [$order_id, 'vnpay', $vnp_TxnRef, $amount, 'pending', json_encode($inputData)]
    );
    
    return [
        'success' => true,
        'payment_url' => $vnp_Url,
        'transaction_id' => $vnp_TxnRef
    ];
}

/**
 * Process Cash on Delivery
 */
function process_cod_payment($order_id, $amount) {
    global $conn;
    
    // Save COD transaction
    $transaction_id = 'COD' . $order_id . '_' . time();
    
    db_insert($conn,
        "INSERT INTO payment_transactions (order_id, gateway, transaction_id, amount, status, created_at) 
         VALUES (?, ?, ?, ?, ?, NOW())",
        "issds",
        [$order_id, 'cod', $transaction_id, $amount, 'pending']
    );
    
    return [
        'success' => true,
        'transaction_id' => $transaction_id,
        'message' => 'Đơn hàng đã được tạo. Vui lòng thanh toán khi nhận hàng.'
    ];
}

/**
 * Verify MoMo callback signature
 */
function verify_momo_signature($data) {
    $secretKey = MOMO_SECRET_KEY;
    $accessKey = MOMO_ACCESS_KEY;
    
    $rawHash = "accessKey=" . $accessKey .
               "&amount=" . $data['amount'] .
               "&extraData=" . ($data['extraData'] ?? '') .
               "&message=" . $data['message'] .
               "&orderId=" . $data['orderId'] .
               "&orderInfo=" . $data['orderInfo'] .
               "&orderType=" . $data['orderType'] .
               "&partnerCode=" . $data['partnerCode'] .
               "&payType=" . $data['payType'] .
               "&requestId=" . $data['requestId'] .
               "&responseTime=" . $data['responseTime'] .
               "&resultCode=" . $data['resultCode'] .
               "&transId=" . $data['transId'];
    
    $signature = hash_hmac("sha256", $rawHash, $secretKey);
    
    return $signature === $data['signature'];
}

/**
 * Verify VNPay callback signature
 */
function verify_vnpay_signature($data) {
    $vnp_SecureHash = $data['vnp_SecureHash'];
    unset($data['vnp_SecureHash']);
    
    ksort($data);
    $hashdata = "";
    $i = 0;
    
    foreach ($data as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }
    
    $secureHash = hash_hmac('sha512', $hashdata, VNPAY_HASH_SECRET);
    
    return $secureHash === $vnp_SecureHash;
}

/**
 * Update payment status
 */
function update_payment_status($transaction_id, $status, $response_data = null) {
    global $conn;
    
    $update_data = [$status, $transaction_id];
    $sql = "UPDATE payment_transactions SET status = ?, updated_at = NOW()";
    
    if ($response_data) {
        $sql .= ", response_data = ?";
        $update_data = [$status, json_encode($response_data), $transaction_id];
    }
    
    $sql .= " WHERE transaction_id = ?";
    
    db_update($conn, $sql, str_repeat('s', count($update_data)), $update_data);
    
    // Update order payment status
    if ($status === 'completed') {
        db_update($conn, 
            "UPDATE orders SET payment_status = 'paid' WHERE id = (
                SELECT order_id FROM payment_transactions WHERE transaction_id = ?
            )",
            "s",
            [$transaction_id]
        );
    } elseif ($status === 'failed') {
        db_update($conn, 
            "UPDATE orders SET payment_status = 'failed' WHERE id = (
                SELECT order_id FROM payment_transactions WHERE transaction_id = ?
            )",
            "s",
            [$transaction_id]
        );
    }
}

/**
 * Get payment methods
 */
function get_payment_methods() {
    return [
        'cod' => [
            'name' => 'Thanh toán khi nhận hàng (COD)',
            'icon' => 'fa-money-bill-wave',
            'description' => 'Thanh toán bằng tiền mặt khi nhận hàng',
            'enabled' => true
        ],
        'momo' => [
            'name' => 'Ví MoMo',
            'icon' => 'fa-wallet',
            'description' => 'Thanh toán qua ví điện tử MoMo',
            'enabled' => true
        ],
        'vnpay' => [
            'name' => 'VNPay',
            'icon' => 'fa-credit-card',
            'description' => 'Thanh toán qua cổng VNPay (ATM, Visa, Master)',
            'enabled' => false // Enable when you have credentials
        ],
        'banking' => [
            'name' => 'Chuyển khoản ngân hàng',
            'icon' => 'fa-university',
            'description' => 'Chuyển khoản trực tiếp qua ngân hàng',
            'enabled' => false
        ]
    ];
}

/**
 * Get payment transaction by order ID
 */
function get_payment_transaction($order_id) {
    global $conn;
    return db_fetch_one($conn, 
        "SELECT * FROM payment_transactions WHERE order_id = ? ORDER BY created_at DESC LIMIT 1",
        "i",
        [$order_id]
    );
}

/**
 * Format amount to VND currency
 */
function format_vnd($amount) {
    return number_format($amount, 0, ',', '.') . 'đ';
}
