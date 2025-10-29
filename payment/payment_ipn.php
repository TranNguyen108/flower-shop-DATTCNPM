<?php
/**
 * Payment IPN (Instant Payment Notification) Handler
 * Background callback from payment gateways
 */

@include '../config.php';
@include '../includes/payment_gateway.php';

$gateway = $_GET['gateway'] ?? '';

// Process MoMo IPN
if($gateway === 'momo') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if(verify_momo_signature($data)) {
        $orderId = $data['orderId'] ?? '';
        $resultCode = $data['resultCode'] ?? '';
        
        if($resultCode == 0) {
            update_payment_status($orderId, 'completed', $data);
            
            // Log successful payment
            error_log("MoMo IPN: Payment successful for order $orderId", 0);
            
            // Return success to MoMo
            http_response_code(200);
            echo json_encode(['status' => 'success']);
        } else {
            update_payment_status($orderId, 'failed', $data);
            
            // Log failed payment
            error_log("MoMo IPN: Payment failed for order $orderId", 0);
            
            http_response_code(200);
            echo json_encode(['status' => 'failed']);
        }
    } else {
        // Invalid signature
        error_log("MoMo IPN: Invalid signature", 0);
        http_response_code(400);
        echo json_encode(['status' => 'invalid signature']);
    }
}

// Process VNPay IPN
elseif($gateway === 'vnpay') {
    if(verify_vnpay_signature($_GET)) {
        $vnp_TxnRef = $_GET['vnp_TxnRef'] ?? '';
        $vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
        
        if($vnp_ResponseCode == '00') {
            update_payment_status($vnp_TxnRef, 'completed', $_GET);
            
            // Log successful payment
            error_log("VNPay IPN: Payment successful for transaction $vnp_TxnRef", 0);
            
            // Return success to VNPay
            http_response_code(200);
            echo json_encode([
                'RspCode' => '00',
                'Message' => 'Success'
            ]);
        } else {
            update_payment_status($vnp_TxnRef, 'failed', $_GET);
            
            // Log failed payment
            error_log("VNPay IPN: Payment failed for transaction $vnp_TxnRef", 0);
            
            http_response_code(200);
            echo json_encode([
                'RspCode' => '01',
                'Message' => 'Failed'
            ]);
        }
    } else {
        // Invalid signature
        error_log("VNPay IPN: Invalid signature", 0);
        http_response_code(400);
        echo json_encode([
            'RspCode' => '97',
            'Message' => 'Invalid signature'
        ]);
    }
}

exit;

