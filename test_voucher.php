<?php
/**
 * Test Voucher API
 */
include 'config.php';
require_once 'includes/voucher_functions.php';

echo "<h2>🔍 Test Voucher Debug</h2>";

// Check session
$user_id = $_SESSION['user_id'] ?? null;
echo "<p><strong>User ID:</strong> " . ($user_id ?? 'NULL - Chưa đăng nhập') . "</p>";

if(!$user_id) {
    echo "<p style='color:red;'>❌ Chưa đăng nhập! <a href='auth/login.php'>Đăng nhập</a></p>";
    exit;
}

// Check user_vouchers table
echo "<h3>📦 Voucher đã lưu trong user_vouchers:</h3>";
$saved = mysqli_query($conn, "SELECT * FROM user_vouchers WHERE user_id = $user_id");
if($saved && mysqli_num_rows($saved) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Voucher ID</th><th>is_used</th><th>collected_at</th></tr>";
    while($row = mysqli_fetch_assoc($saved)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['voucher_id']}</td>";
        echo "<td>{$row['is_used']}</td>";
        echo "<td>{$row['collected_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:orange;'>⚠️ Chưa lưu voucher nào!</p>";
}

// TEST TRỰC TIẾP - không dùng function
echo "<h3>🎟️ TEST TRỰC TIẾP (không qua function):</h3>";

$sql_direct = "SELECT v.*, uv.is_used as uv_is_used
        FROM user_vouchers uv
        JOIN vouchers v ON uv.voucher_id = v.id 
        WHERE uv.user_id = $user_id
        AND uv.is_used = 0
        AND v.is_active = 1
        ORDER BY v.discount_value DESC";

$result_direct = mysqli_query($conn, $sql_direct);
$vouchers_direct = [];

if($result_direct) {
    $count = mysqli_num_rows($result_direct);
    echo "<p>✅ Query OK - Rows: <strong>$count</strong></p>";
    
    while($row = mysqli_fetch_assoc($result_direct)) {
        // Check dates
        $now = time();
        $end_date = $row['end_date'] ? strtotime($row['end_date']) : null;
        
        $is_valid = true;
        $reason = '';
        
        if($end_date && $now > $end_date) {
            $is_valid = false;
            $reason = 'Hết hạn';
        }
        
        // Check min_order
        $order_total = 560000;
        if($is_valid && $order_total < $row['min_order_value']) {
            $row['can_use'] = false;
            $row['reason'] = 'Đơn tối thiểu ' . number_format($row['min_order_value']) . '₫';
        } else {
            $row['can_use'] = true;
            $row['reason'] = '';
        }
        
        if($is_valid) {
            $vouchers_direct[] = $row;
        }
    }
    
    echo "<p>Sau khi filter: <strong>" . count($vouchers_direct) . "</strong> voucher hợp lệ</p>";
    
    if(count($vouchers_direct) > 0) {
        echo "<table border='1' cellpadding='5' style='background:#e8f5e9;'>";
        echo "<tr><th>ID</th><th>Code</th><th>Name</th><th>Discount</th><th>Min Order</th><th>End Date</th><th>can_use</th></tr>";
        foreach($vouchers_direct as $v) {
            echo "<tr>";
            echo "<td>{$v['id']}</td>";
            echo "<td><strong>{$v['code']}</strong></td>";
            echo "<td>{$v['name']}</td>";
            echo "<td>" . number_format($v['discount_value']) . " ({$v['discount_type']})</td>";
            echo "<td>" . number_format($v['min_order_value']) . "₫</td>";
            echo "<td>{$v['end_date']}</td>";
            echo "<td>" . ($v['can_use'] ? '✅ Yes' : '❌ ' . $v['reason']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color:red;'>❌ SQL Error: " . mysqli_error($conn) . "</p>";
}

// Test function
echo "<h3>🎟️ Kết quả get_available_vouchers():</h3>";
$vouchers = get_available_vouchers($conn, $user_id, 560000);
echo "<p>Số voucher từ function: <strong>" . count($vouchers) . "</strong></p>";

echo "<hr>";
echo "<p><a href='pages/cart.php'>← Quay lại giỏ hàng</a></p>";
?>
