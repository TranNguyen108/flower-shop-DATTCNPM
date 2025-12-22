<?php
/**
 * Test AJAX API trực tiếp
 */
include 'config.php';

header('Content-Type: text/html; charset=utf-8');

$user_id = $_SESSION['user_id'] ?? null;

echo "<h2>Debug API</h2>";
echo "<p>User ID: " . ($user_id ?? 'NULL') . "</p>";

if(!$user_id){
    echo "<p style='color:red'>Chưa đăng nhập!</p>";
    exit;
}

$order_total = 560000;

// Query trực tiếp - KHÔNG CÓ ĐIỀU KIỆN
$sql = "SELECT v.*, uv.is_used as uv_is_used
        FROM user_vouchers uv
        JOIN vouchers v ON uv.voucher_id = v.id 
        WHERE uv.user_id = $user_id";

echo "<p>SQL: <code>$sql</code></p>";

$result = mysqli_query($conn, $sql);

if(!$result) {
    echo "<p style='color:red'>SQL Error: " . mysqli_error($conn) . "</p>";
    exit;
}

$total_rows = mysqli_num_rows($result);
echo "<p>Total rows từ DB: <strong>$total_rows</strong></p>";

$vouchers = [];
$skipped = [];

while($row = mysqli_fetch_assoc($result)) {
    $skip_reason = null;
    
    // Check is_used
    if($row['uv_is_used'] != 0) {
        $skip_reason = "uv_is_used = {$row['uv_is_used']}";
    }
    
    // Check is_active
    if(!$skip_reason && $row['is_active'] != 1) {
        $skip_reason = "is_active = {$row['is_active']}";
    }
    
    // Check dates
    $now = time();
    $end_date = $row['end_date'] ? strtotime($row['end_date']) : null;
    $start_date = $row['start_date'] ? strtotime($row['start_date']) : null;
    
    if(!$skip_reason && $end_date && $now > $end_date) {
        $skip_reason = "Expired: end_date={$row['end_date']}";
    }
    if(!$skip_reason && $start_date && $now < $start_date) {
        $skip_reason = "Not started: start_date={$row['start_date']}";
    }
    
    // Check usage_limit
    if(!$skip_reason && $row['usage_limit'] > 0 && $row['used_count'] >= $row['usage_limit']) {
        $skip_reason = "Usage limit reached: {$row['used_count']}/{$row['usage_limit']}";
    }
    
    if($skip_reason) {
        $skipped[] = ['code' => $row['code'], 'reason' => $skip_reason];
        continue;
    }
    
    // Check min_order
    if($order_total > 0 && $order_total < $row['min_order_value']) {
        $row['can_use'] = false;
        $row['reason'] = 'Đơn tối thiểu ' . number_format($row['min_order_value'], 0, ',', '.') . '₫';
    } else {
        $row['can_use'] = true;
        $row['reason'] = '';
    }
    
    $vouchers[] = $row;
}

echo "<h3>Kết quả:</h3>";
echo "<p>Vouchers hợp lệ: <strong>" . count($vouchers) . "</strong></p>";

if(count($skipped) > 0) {
    echo "<h4>Đã bỏ qua:</h4>";
    echo "<ul>";
    foreach($skipped as $s) {
        echo "<li><strong>{$s['code']}</strong>: {$s['reason']}</li>";
    }
    echo "</ul>";
}

if(count($vouchers) > 0) {
    echo "<h4>Vouchers OK:</h4>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Code</th><th>Name</th><th>can_use</th></tr>";
    foreach($vouchers as $v) {
        echo "<tr>";
        echo "<td>{$v['code']}</td>";
        echo "<td>{$v['name']}</td>";
        echo "<td>" . ($v['can_use'] ? '✅' : '❌ ' . $v['reason']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
