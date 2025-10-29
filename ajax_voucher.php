<?php
/**
 * AJAX Voucher Handler - Professional Version
 * Kiểm tra và áp dụng mã giảm giá với nhiều tính năng
 */

@include 'config.php';
require_once 'includes/voucher_functions.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action){
    case 'apply':
        $code = strtoupper(sanitize_input($_POST['code'] ?? ''));
        $order_total = (float)($_POST['order_total'] ?? 0);
        
        if(empty($code)){
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá!']);
            exit;
        }
        
        $result = validate_voucher($conn, $code, $user_id, $order_total);
        
        if($result['success']){
            // Lưu vào session
            $_SESSION['applied_voucher'] = [
                'id' => $result['voucher']['id'],
                'code' => $result['voucher']['code'],
                'name' => $result['voucher']['name'],
                'discount' => $result['discount'],
                'discount_type' => $result['voucher']['discount_type'],
                'discount_value' => $result['voucher']['discount_value']
            ];
            
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'discount' => $result['discount'],
                'discount_formatted' => number_format($result['discount'], 0, ',', '.') . '₫',
                'voucher_name' => $result['voucher']['name'],
                'voucher_code' => $result['voucher']['code']
            ]);
        } else {
            echo json_encode($result);
        }
        break;
        
    case 'remove':
        unset($_SESSION['applied_voucher']);
        echo json_encode(['success' => true, 'message' => 'Đã hủy mã giảm giá!']);
        break;
        
    case 'list':
        $order_total = (float)($_GET['order_total'] ?? 0);
        $filter = $_GET['filter'] ?? 'all';
        
        // Query trực tiếp - không dùng function để tránh cache
        $sql = "SELECT v.*, uv.is_used as uv_is_used
                FROM user_vouchers uv
                JOIN vouchers v ON uv.voucher_id = v.id 
                WHERE uv.user_id = $user_id
                AND uv.is_used = 0
                AND v.is_active = 1
                ORDER BY v.discount_value DESC";
        
        $result = mysqli_query($conn, $sql);
        $vouchers = [];
        
        if($result) {
            while($row = mysqli_fetch_assoc($result)) {
                // Check dates
                $now = time();
                $end_date = $row['end_date'] ? strtotime($row['end_date']) : null;
                $start_date = $row['start_date'] ? strtotime($row['start_date']) : null;
                
                // Skip expired or not started
                if($end_date && $now > $end_date) continue;
                if($start_date && $now < $start_date) continue;
                
                // Check usage_limit
                if($row['usage_limit'] > 0 && $row['used_count'] >= $row['usage_limit']) continue;
                
                // Check min_order
                if($order_total > 0 && $order_total < $row['min_order_value']) {
                    $row['can_use'] = false;
                    $row['reason'] = 'Đơn tối thiểu ' . number_format($row['min_order_value'], 0, ',', '.') . '₫';
                } else {
                    $row['can_use'] = true;
                    $row['reason'] = '';
                }
                
                $row['usage_count'] = $row['used_count'] ?? 0;
                $row['usage_limit'] = $row['usage_limit'] ?? 0;
                
                $vouchers[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'vouchers' => $vouchers]);
        break;
        
    case 'check':
        // Kiểm tra voucher đang áp dụng
        if(isset($_SESSION['applied_voucher'])){
            echo json_encode([
                'success' => true, 
                'has_voucher' => true,
                'voucher' => $_SESSION['applied_voucher']
            ]);
        } else {
            echo json_encode(['success' => true, 'has_voucher' => false]);
        }
        break;
    
    case 'collect':
        // Thu thập voucher vào kho của user
        $voucher_id = (int)($_POST['voucher_id'] ?? 0);
        
        if($voucher_id <= 0){
            echo json_encode(['success' => false, 'message' => 'Voucher không hợp lệ!']);
            exit;
        }
        
        // Check if already collected
        $check = db_fetch_one($conn, "SELECT id FROM user_vouchers WHERE user_id = ? AND voucher_id = ?", "ii", [$user_id, $voucher_id]);
        if($check){
            echo json_encode(['success' => false, 'message' => 'Bạn đã lưu voucher này rồi!']);
            exit;
        }
        
        // Check voucher exists and is active
        $voucher = db_fetch_one($conn, "SELECT * FROM vouchers WHERE id = ? AND is_active = 1 AND end_date >= CURDATE()", "i", [$voucher_id]);
        if(!$voucher){
            echo json_encode(['success' => false, 'message' => 'Voucher không tồn tại hoặc đã hết hạn!']);
            exit;
        }
        
        // Collect voucher
        $result = db_insert($conn, "INSERT INTO user_vouchers (user_id, voucher_id, collected_at) VALUES (?, ?, NOW())", "ii", [$user_id, $voucher_id]);
        
        if($result){
            echo json_encode(['success' => true, 'message' => 'Đã lưu voucher vào kho của bạn!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra!']);
        }
        break;
    
    case 'my_vouchers':
        // Lấy voucher đã thu thập của user
        $vouchers = db_select($conn, "
            SELECT v.*, uv.collected_at, uv.used_at
            FROM user_vouchers uv
            JOIN vouchers v ON uv.voucher_id = v.id
            WHERE uv.user_id = ? AND v.is_active = 1 AND v.end_date >= CURDATE() AND uv.used_at IS NULL
            ORDER BY v.end_date ASC
        ", "i", [$user_id]);
        
        $result = [];
        while($row = mysqli_fetch_assoc($vouchers)){
            $result[] = $row;
        }
        
        echo json_encode(['success' => true, 'vouchers' => $result]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
