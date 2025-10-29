<?php
/**
 * Inventory Management Functions
 * Stock tracking, low stock alerts, inventory history
 */

// Don't require config.php here - it's already loaded by main files

/**
 * Check product stock availability
 */
function check_stock_availability($product_id, $quantity_needed = 1) {
    global $conn;
    
    $product = db_fetch_one($conn, 
        "SELECT stock_quantity, is_available FROM products WHERE id = ?",
        "i",
        [$product_id]
    );
    
    if (!$product || !$product['is_available']) {
        return false;
    }
    
    return $product['stock_quantity'] >= $quantity_needed;
}

/**
 * Get product stock quantity
 */
function get_product_stock($product_id) {
    global $conn;
    
    $product = db_fetch_one($conn,
        "SELECT stock_quantity, stock_status FROM products WHERE id = ?",
        "i",
        [$product_id]
    );
    
    return $product ? (int)$product['stock_quantity'] : 0;
}

/**
 * Update product stock status based on quantity
 */
function update_stock_status($product_id) {
    global $conn;
    
    $product = db_fetch_one($conn,
        "SELECT stock_quantity, low_stock_threshold FROM products WHERE id = ?",
        "i",
        [$product_id]
    );
    
    if (!$product) return false;
    
    $quantity = (int)$product['stock_quantity'];
    $threshold = (int)$product['low_stock_threshold'];
    
    // Determine stock status
    if ($quantity <= 0) {
        $status = 'out_of_stock';
        $is_available = 0;
        
        // Create out of stock alert
        create_stock_alert($product_id, 'out_of_stock', $quantity, $threshold);
    } elseif ($quantity <= $threshold) {
        $status = 'low_stock';
        $is_available = 1;
        
        // Create low stock alert
        create_stock_alert($product_id, 'low_stock', $quantity, $threshold);
    } else {
        $status = 'in_stock';
        $is_available = 1;
        
        // Resolve any existing alerts
        resolve_stock_alerts($product_id);
    }
    
    // Update product
    db_update($conn,
        "UPDATE products SET stock_status = ?, is_available = ?, last_stock_update = NOW() WHERE id = ?",
        "sii",
        [$status, $is_available, $product_id]
    );
    
    return true;
}

/**
 * Reduce stock quantity (on order)
 */
function reduce_stock($product_id, $quantity, $order_id = null, $notes = '') {
    global $conn;
    
    // Get current stock
    $current_stock = get_product_stock($product_id);
    
    if ($current_stock < $quantity) {
        return false; // Insufficient stock
    }
    
    $new_stock = $current_stock - $quantity;
    
    // Update product stock
    db_update($conn,
        "UPDATE products SET stock_quantity = ? WHERE id = ?",
        "ii",
        [$new_stock, $product_id]
    );
    
    // Log to inventory history
    log_inventory_change(
        $product_id, 
        'sale', 
        -$quantity, 
        $current_stock, 
        $new_stock, 
        $order_id,
        null,
        $notes ?: "Stock reduced due to order #$order_id"
    );
    
    // Update stock status
    update_stock_status($product_id);
    
    return true;
}

/**
 * Increase stock quantity (restock)
 */
function increase_stock($product_id, $quantity, $admin_id = null, $notes = '') {
    global $conn;
    
    $current_stock = get_product_stock($product_id);
    $new_stock = $current_stock + $quantity;
    
    // Update product stock
    db_update($conn,
        "UPDATE products SET stock_quantity = ? WHERE id = ?",
        "ii",
        [$new_stock, $product_id]
    );
    
    // Log to inventory history
    log_inventory_change(
        $product_id,
        'restock',
        $quantity,
        $current_stock,
        $new_stock,
        null,
        $admin_id,
        $notes ?: "Stock increased by admin"
    );
    
    // Update stock status
    update_stock_status($product_id);
    
    return true;
}

/**
 * Adjust stock quantity (manual correction)
 */
function adjust_stock($product_id, $new_quantity, $admin_id = null, $notes = '') {
    global $conn;
    
    $current_stock = get_product_stock($product_id);
    $change = $new_quantity - $current_stock;
    
    // Update product stock
    db_update($conn,
        "UPDATE products SET stock_quantity = ? WHERE id = ?",
        "ii",
        [$new_quantity, $product_id]
    );
    
    // Log to inventory history
    log_inventory_change(
        $product_id,
        'adjustment',
        $change,
        $current_stock,
        $new_quantity,
        null,
        $admin_id,
        $notes ?: "Manual stock adjustment by admin"
    );
    
    // Update stock status
    update_stock_status($product_id);
    
    return true;
}

/**
 * Return stock (order cancellation/return)
 */
function return_stock($product_id, $quantity, $order_id = null, $notes = '') {
    global $conn;
    
    $current_stock = get_product_stock($product_id);
    $new_stock = $current_stock + $quantity;
    
    // Update product stock
    db_update($conn,
        "UPDATE products SET stock_quantity = ? WHERE id = ?",
        "ii",
        [$new_stock, $product_id]
    );
    
    // Log to inventory history
    log_inventory_change(
        $product_id,
        'return',
        $quantity,
        $current_stock,
        $new_stock,
        $order_id,
        null,
        $notes ?: "Stock returned from order #$order_id"
    );
    
    // Update stock status
    update_stock_status($product_id);
    
    return true;
}

/**
 * Log inventory change to history
 */
function log_inventory_change($product_id, $change_type, $quantity_change, $quantity_before, $quantity_after, $order_id = null, $admin_id = null, $notes = '') {
    global $conn;
    
    db_insert($conn,
        "INSERT INTO inventory_history (product_id, change_type, quantity_change, quantity_before, quantity_after, order_id, admin_id, notes) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        "isiiiiss",
        [$product_id, $change_type, $quantity_change, $quantity_before, $quantity_after, $order_id, $admin_id, $notes]
    );
}

/**
 * Get inventory history for product
 */
function get_inventory_history($product_id, $limit = 50) {
    global $conn;
    
    return db_select($conn,
        "SELECT ih.*, p.name as product_name, o.id as order_number 
         FROM inventory_history ih 
         LEFT JOIN products p ON ih.product_id = p.id
         LEFT JOIN orders o ON ih.order_id = o.id
         WHERE ih.product_id = ? 
         ORDER BY ih.created_at DESC 
         LIMIT ?",
        "ii",
        [$product_id, $limit]
    );
}

/**
 * Get all inventory history (for reports)
 */
function get_all_inventory_history($limit = 100) {
    global $conn;
    
    return db_select($conn,
        "SELECT ih.*, p.name as product_name, p.image as product_image
         FROM inventory_history ih 
         LEFT JOIN products p ON ih.product_id = p.id
         ORDER BY ih.created_at DESC 
         LIMIT ?",
        "i",
        [$limit]
    );
}

/**
 * Create stock alert
 */
function create_stock_alert($product_id, $alert_type, $current_quantity, $threshold) {
    global $conn;
    
    // Check if unresolved alert already exists
    $existing = db_fetch_one($conn,
        "SELECT id FROM stock_alerts 
         WHERE product_id = ? AND alert_type = ? AND is_resolved = 0",
        "is",
        [$product_id, $alert_type]
    );
    
    if (!$existing) {
        db_insert($conn,
            "INSERT INTO stock_alerts (product_id, alert_type, current_quantity, threshold) 
             VALUES (?, ?, ?, ?)",
            "isii",
            [$product_id, $alert_type, $current_quantity, $threshold]
        );
    }
}

/**
 * Resolve stock alerts for product
 */
function resolve_stock_alerts($product_id) {
    global $conn;
    
    db_update($conn,
        "UPDATE stock_alerts SET is_resolved = 1, resolved_at = NOW() 
         WHERE product_id = ? AND is_resolved = 0",
        "i",
        [$product_id]
    );
}

/**
 * Get active stock alerts
 */
function get_active_stock_alerts() {
    global $conn;
    
    return db_select($conn,
        "SELECT sa.*, p.name as product_name, p.image as product_image, p.stock_quantity
         FROM stock_alerts sa
         INNER JOIN products p ON sa.product_id = p.id
         WHERE sa.is_resolved = 0
         ORDER BY sa.alert_type DESC, sa.created_at DESC"
    );
}

/**
 * Get low stock products
 */
function get_low_stock_products($limit = 20) {
    global $conn;
    
    return db_select($conn,
        "SELECT id, name, image, stock_quantity, low_stock_threshold, stock_status 
         FROM products 
         WHERE stock_status IN ('low_stock', 'out_of_stock')
         ORDER BY stock_quantity ASC 
         LIMIT ?",
        "i",
        [$limit]
    );
}

/**
 * Get inventory statistics
 */
function get_inventory_stats() {
    global $conn;
    
    $stats = [];
    
    // Total products
    $result = db_fetch_one($conn, "SELECT COUNT(*) as total FROM products");
    $stats['total_products'] = $result['total'] ?? 0;
    
    // In stock
    $result = db_fetch_one($conn, "SELECT COUNT(*) as count FROM products WHERE stock_status = 'in_stock'");
    $stats['in_stock'] = $result['count'] ?? 0;
    
    // Low stock
    $result = db_fetch_one($conn, "SELECT COUNT(*) as count FROM products WHERE stock_status = 'low_stock'");
    $stats['low_stock'] = $result['count'] ?? 0;
    
    // Out of stock
    $result = db_fetch_one($conn, "SELECT COUNT(*) as count FROM products WHERE stock_status = 'out_of_stock'");
    $stats['out_of_stock'] = $result['count'] ?? 0;
    
    // Total stock value
    $result = db_fetch_one($conn, "SELECT SUM(stock_quantity * price) as total_value FROM products");
    $stats['total_value'] = $result['total_value'] ?? 0;
    
    // Active alerts
    $result = db_fetch_one($conn, "SELECT COUNT(*) as count FROM stock_alerts WHERE is_resolved = 0");
    $stats['active_alerts'] = $result['count'] ?? 0;
    
    return $stats;
}

/**
 * Check and process cart items stock
 */
function validate_cart_stock($user_id) {
    global $conn;
    
    $cart_items = db_select($conn,
        "SELECT c.*, p.stock_quantity, p.is_available 
         FROM cart c
         INNER JOIN products p ON c.pid = p.id
         WHERE c.user_id = ?",
        "i",
        [$user_id]
    );
    
    $errors = [];
    
    foreach ($cart_items as $item) {
        if (!$item['is_available']) {
            $errors[] = $item['name'] . ' không còn hàng';
        } elseif ($item['stock_quantity'] < $item['quantity']) {
            $errors[] = $item['name'] . ' chỉ còn ' . $item['stock_quantity'] . ' sản phẩm';
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Process order stock reduction
 */
function process_order_stock($order_id) {
    global $conn;
    
    // Get order items
    $order_items = db_select($conn,
        "SELECT * FROM order_items WHERE order_id = ?",
        "i",
        [$order_id]
    );
    
    $success = true;
    
    foreach ($order_items as $item) {
        if (!reduce_stock($item['product_id'], $item['quantity'], $order_id)) {
            $success = false;
            break;
        }
    }
    
    return $success;
}

/**
 * Bulk update stock from CSV
 */
function bulk_update_stock_from_array($stock_data, $admin_id = null) {
    $results = [
        'success' => 0,
        'failed' => 0,
        'errors' => []
    ];
    
    foreach ($stock_data as $row) {
        $product_id = $row['product_id'] ?? null;
        $quantity = $row['quantity'] ?? null;
        $notes = $row['notes'] ?? 'Bulk update';
        
        if (!$product_id || !is_numeric($quantity)) {
            $results['failed']++;
            $results['errors'][] = "Invalid data for product ID: $product_id";
            continue;
        }
        
        if (adjust_stock($product_id, $quantity, $admin_id, $notes)) {
            $results['success']++;
        } else {
            $results['failed']++;
            $results['errors'][] = "Failed to update product ID: $product_id";
        }
    }
    
    return $results;
}
