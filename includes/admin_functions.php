<?php
/**
 * Admin Utilities Functions
 * Centralized admin functions để tránh code duplication
 * Updated: December 2025
 */

// Check admin access
function check_admin_access() {
    if (!isset($_SESSION['admin_id'])) {
        header('location: login.php');
        exit;
    }
}

// Count orders by status
function count_orders_by_status($conn, $status) {
    $query = "SELECT COUNT(*) as count FROM `orders` WHERE delivery_status = ?";
    $result = db_fetch_one($conn, $query, "s", [$status]);
    return $result['count'] ?? 0;
}

// Get total amount by payment status
function get_total_by_payment_status($conn, $status) {
    $query = "SELECT SUM(total_price) as total FROM `orders` WHERE payment_status = ?";
    $result = db_fetch_one($conn, $query, "s", [$status]);
    return $result['total'] ?? 0;
}

// Send order status update email - Simple version (use email_service.php version for HTML emails)
if (!function_exists('send_order_status_update')) {
    function send_order_status_update($order_id, $email, $name, $status) {
        $subject = "Cập nhật trạng thái đơn hàng #$order_id";
        $message = "Xin chào $name,\n\nĐơn hàng #$order_id của bạn đã được cập nhật.\nTrạng thái hiện tại: $status\n\nCảm ơn bạn đã mua hàng!";
        $headers = "From: noreply@flowershop.com\r\nContent-Type: text/plain; charset=UTF-8";
        return mail($email, $subject, $message, $headers);
    }
}

// Get dashboard statistics
function get_dashboard_stats($conn) {
    return [
        'processing' => count_orders_by_status($conn, 'Đang xử lý'),
        'shipping' => count_orders_by_status($conn, 'Đang giao'),
        'delivered' => count_orders_by_status($conn, 'Đã giao'),
        'cancelled' => count_orders_by_status($conn, 'Đã hủy'),
        'pending_amount' => get_total_by_payment_status($conn, 'pending'),
        'completed_amount' => get_total_by_payment_status($conn, 'completed'),
        'total_orders' => db_count($conn, "SELECT * FROM `orders`"),
        'total_users' => db_count($conn, "SELECT * FROM `users` WHERE user_type = 'user'"),
        'total_products' => db_count($conn, "SELECT * FROM `products`"),
    ];
}

// Format number for display
function format_number($number) {
    return number_format($number, 0, ',', '.');
}

// Format currency for display
function format_currency($amount) {
    return number_format($amount, 0, ',', '.') . 'đ';
}

// Validate admin action
function validate_admin_action($action_type) {
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }
    
    // Check CSRF token if provided
    $token = $_POST['csrf_token'] ?? $_GET['token'] ?? '';
    if (!empty($token) && !verify_csrf_token($token)) {
        return false;
    }
    
    return true;
}

// Sanitize file upload
function sanitize_upload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 2097152) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        return false;
    }
    
    // Check file type
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        return false;
    }
    
    return true;
}

// Handle file upload
function handle_file_upload($file) {
    if (!sanitize_upload($file)) {
        return false;
    }
    
    $file_name = uniqid() . '_' . sanitize_filename($file['name']);
    // Absolute path from config constant
    $absolute_path = defined('UPLOAD_PATH') ? UPLOAD_PATH . $file_name : 'assets/uploads/products/' . $file_name;
    
    if (move_uploaded_file($file['tmp_name'], $absolute_path)) {
        // Return relative path filename (stored in DB as filename only)
        return $file_name;
    }
    
    return false;
}

// Sanitize filename
function sanitize_filename($filename) {
    $filename = basename($filename);
    $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
    return $filename;
}

// Delete product with related data
function delete_product_complete($conn, $product_id) {
    $product_id = (int)$product_id;
    
    // Get product image
    $product = db_fetch_one($conn, "SELECT image FROM `products` WHERE id = ?", "i", [$product_id]);
    
    if (!$product) {
        return false;
    }
    
    // Delete image file (absolute path)
    if (!empty($product['image'])) {
        $img_file = basename($product['image']);
        $abs = defined('UPLOAD_PATH') ? UPLOAD_PATH . $img_file : 'assets/uploads/products/' . $img_file;
        if (file_exists($abs)) {
            @unlink($abs);
        }
    }
    
    // Delete from database
    db_delete($conn, "DELETE FROM `products` WHERE id = ?", "i", [$product_id]);
    db_delete($conn, "DELETE FROM `wishlist` WHERE pid = ?", "i", [$product_id]);
    db_delete($conn, "DELETE FROM `cart` WHERE pid = ?", "i", [$product_id]);
    db_delete($conn, "DELETE FROM `reviews` WHERE product_id = ?", "i", [$product_id]);
    
    return true;
}

// Build filter query
function build_filter_query($filters = []) {
    $where_conditions = [];
    $params = [];
    $types = '';
    
    if (isset($filters['status']) && $filters['status'] !== 'all') {
        $where_conditions[] = "delivery_status = ?";
        $params[] = $filters['status'];
        $types .= 's';
    }
    
    if (isset($filters['search']) && !empty($filters['search'])) {
        $where_conditions[] = "(name LIKE ? OR email LIKE ? OR number LIKE ?)";
        $search_param = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
        $types .= 'sss';
    }
    
    if (isset($filters['date']) && $filters['date'] !== 'all') {
        switch($filters['date']) {
            case 'today':
                $where_conditions[] = "DATE(placed_on) = CURDATE()";
                break;
            case 'week':
                $where_conditions[] = "YEARWEEK(placed_on, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'month':
                $where_conditions[] = "MONTH(placed_on) = MONTH(CURDATE()) AND YEAR(placed_on) = YEAR(CURDATE())";
                break;
        }
    }
    
    return [
        'where' => !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "",
        'params' => $params,
        'types' => $types
    ];
}

?>
