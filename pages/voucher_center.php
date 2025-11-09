<?php
/**
 * 🎟️ Kho Voucher - Voucher Center
 * Trang voucher chuyên nghiệp như Shopee/Lazada
 */

include '../config.php';
require_once '../includes/voucher_functions.php';

// Lấy user_id từ session
$user_id = $_SESSION['user_id'] ?? null;

// Khởi tạo bảng
init_voucher_table($conn);

// Tạo bảng user_vouchers (kho voucher của user)
$check = mysqli_query($conn, "SHOW TABLES LIKE 'user_vouchers'");
if(mysqli_num_rows($check) == 0){
    mysqli_query($conn, "CREATE TABLE user_vouchers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        voucher_id INT NOT NULL,
        collected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_used TINYINT(1) DEFAULT 0,
        used_at TIMESTAMP NULL,
        UNIQUE KEY user_voucher (user_id, voucher_id)
    )");
}

// Thu thập voucher
if(isset($_GET['collect']) && $user_id){
    $vid = (int)$_GET['collect'];
    $check = mysqli_query($conn, "SELECT id FROM user_vouchers WHERE user_id = '$user_id' AND voucher_id = '$vid'");
    if(mysqli_num_rows($check) == 0){
        mysqli_query($conn, "INSERT INTO user_vouchers (user_id, voucher_id) VALUES ('$user_id', '$vid')");
    }
    header('location: voucher_center.php?collected=1');
    exit;
}

// Lấy vouchers theo category
$category = $_GET['cat'] ?? 'all';
$now = date('Y-m-d H:i:s');

// Set user_id to 0 if not logged in (for SQL queries)
$user_id_sql = $user_id ? (int)$user_id : 0;

// Lấy tất cả vouchers - query đơn giản
$sql = "SELECT * FROM vouchers WHERE is_active = 1 ORDER BY discount_value DESC";
$all_vouchers = mysqli_query($conn, $sql);

if(!$all_vouchers) {
    // Debug SQL error
    error_log("Voucher SQL Error: " . mysqli_error($conn));
}

// Đếm tổng số voucher
$total_vouchers = $all_vouchers ? mysqli_num_rows($all_vouchers) : 0;

// Lấy danh sách voucher user đã collect (nếu đăng nhập)
$collected_vouchers = [];
$used_vouchers = [];
if($user_id_sql > 0) {
    // Voucher đã lưu
    $collect_result = mysqli_query($conn, "SELECT voucher_id FROM user_vouchers WHERE user_id = $user_id_sql");
    if($collect_result) {
        while($row = mysqli_fetch_assoc($collect_result)) {
            $collected_vouchers[$row['voucher_id']] = true;
        }
    }
    // Voucher đã dùng
    $usage_result = mysqli_query($conn, "SELECT voucher_id, COUNT(*) as cnt FROM voucher_usage WHERE user_id = $user_id_sql GROUP BY voucher_id");
    if($usage_result) {
        while($row = mysqli_fetch_assoc($usage_result)) {
            $used_vouchers[$row['voucher_id']] = $row['cnt'];
        }
    }
}

// Phân loại vouchers
$vouchers_by_cat = [
    'hot' => [],      // Voucher hot (giảm nhiều)
    'freeship' => [], // Freeship
    'new' => [],      // Mới
    'expiring' => [], // Sắp hết hạn
];

if($all_vouchers && mysqli_num_rows($all_vouchers) > 0){
    while($v = mysqli_fetch_assoc($all_vouchers)){
        // Hot: giảm >= 15%
        if($v['discount_type'] == 'percent' && $v['discount_value'] >= 15){
            $vouchers_by_cat['hot'][] = $v;
        }
        // Freeship
        if(stripos($v['code'], 'FREESHIP') !== false || stripos($v['name'], 'ship') !== false){
            $vouchers_by_cat['freeship'][] = $v;
        }
        // Mới (trong 7 ngày)
        if(strtotime($v['created_at']) > strtotime('-7 days')){
            $vouchers_by_cat['new'][] = $v;
        }
        // Sắp hết hạn (còn < 3 ngày)
        if($v['end_date'] && strtotime($v['end_date']) < strtotime('+3 days')){
            $vouchers_by_cat['expiring'][] = $v;
        }
    }
    
    // Reset pointer
    mysqli_data_seek($all_vouchers, 0);
}

// Vouchers của tôi
$my_vouchers = [];
if($user_id){
    $my_result = mysqli_query($conn, "SELECT v.*, uv.collected_at, uv.is_used 
        FROM user_vouchers uv 
        JOIN vouchers v ON uv.voucher_id = v.id 
        WHERE uv.user_id = '$user_id' AND uv.is_used = 0
        AND v.is_active = 1
        AND (v.end_date IS NULL OR v.end_date >= '$now')
        ORDER BY uv.collected_at DESC");
    if($my_result){
        while($row = mysqli_fetch_assoc($my_result)){
            $my_vouchers[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kho Voucher - Flower Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary: #ee4d2d;
            --primary-dark: #d73211;
            --secondary: #ff7337;
            --gold: #ffd700;
            --success: #26aa99;
        }
        
        .voucher-center {
            background: linear-gradient(135deg, #fff5f5 0%, #fff0e6 100%);
            min-height: 100vh;
            padding-bottom: 3rem;
        }
        
        /* Hero Banner */
        .voucher-hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 3rem 2rem;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .voucher-hero::before {
            content: '🎟️';
            position: absolute;
            font-size: 15rem;
            opacity: 0.1;
            right: -50px;
            top: -30px;
            transform: rotate(15deg);
        }
        
        .voucher-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .voucher-hero p {
            font-size: 1.2rem;
            opacity: 0.95;
        }
        
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 1.5rem;
        }
        
        .hero-stat {
            text-align: center;
        }
        
        .hero-stat .number {
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .hero-stat .label {
            font-size: 0.95rem;
            opacity: 0.9;
        }
        
        /* Container */
        .vc-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        /* Category Tabs */
        .vc-tabs {
            display: flex;
            gap: 0.5rem;
            padding: 1.5rem 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .vc-tab {
            padding: 0.8rem 1.5rem;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .vc-tab:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .vc-tab.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        .vc-tab .count {
            background: rgba(255,255,255,0.3);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.85rem;
        }
        
        .vc-tab.active .count {
            background: rgba(255,255,255,0.3);
        }
        
        /* Section */
        .vc-section {
            margin-bottom: 2rem;
        }
        
        .vc-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .vc-section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .vc-section-title .badge {
            background: var(--primary);
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .see-all {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Voucher Grid */
        .voucher-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.2rem;
        }
        
        /* Voucher Card - Shopee Style */
        .voucher-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            display: flex;
            position: relative;
            transition: all 0.3s;
            border: 1px solid #f0f0f0;
        }
        
        .voucher-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        
        .voucher-card.collected {
            border-color: var(--success);
        }
        
        /* Left side - Discount display */
        .vc-left {
            width: 120px;
            min-height: 130px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
        }
        
        .vc-left::after {
            content: '';
            position: absolute;
            right: -8px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            background: #f5f5f5;
            border-radius: 50%;
        }
        
        .vc-left.freeship {
            background: linear-gradient(135deg, var(--success) 0%, #20c997 100%);
        }
        
        .vc-left.gold {
            background: linear-gradient(135deg, #f39c12 0%, #e74c3c 100%);
        }
        
        .vc-icon {
            font-size: 2rem;
            margin-bottom: 0.3rem;
        }
        
        .vc-discount {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1;
        }
        
        .vc-discount-label {
            font-size: 0.75rem;
            opacity: 0.9;
            margin-top: 3px;
        }
        
        /* Right side - Info */
        .vc-right {
            flex: 1;
            padding: 1rem 1.2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .vc-name {
            font-weight: 700;
            color: #2d3436;
            font-size: 1rem;
            margin-bottom: 0.3rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .vc-code {
            font-family: monospace;
            font-size: 0.85rem;
            color: var(--primary);
            background: #fff5f5;
            padding: 3px 8px;
            border-radius: 4px;
            border: 1px dashed var(--primary);
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        
        .vc-conditions {
            font-size: 0.85rem;
            color: #636e72;
            margin-bottom: 0.5rem;
        }
        
        .vc-conditions i {
            width: 16px;
            color: #aaa;
        }
        
        .vc-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }
        
        .vc-expiry {
            font-size: 0.8rem;
            color: #e74c3c;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .vc-expiry.warning {
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            50% { opacity: 0.5; }
        }
        
        .vc-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .vc-btn-collect {
            background: var(--primary);
            color: white;
        }
        
        .vc-btn-collect:hover {
            background: var(--primary-dark);
        }
        
        .vc-btn-collected {
            background: #f0f0f0;
            color: var(--success);
            cursor: default;
        }
        
        .vc-btn-use {
            background: var(--primary);
            color: white;
        }
        
        /* Progress bar for usage limit */
        .vc-progress {
            margin-top: 0.5rem;
        }
        
        .vc-progress-bar {
            height: 4px;
            background: #f0f0f0;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .vc-progress-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 2px;
            transition: width 0.3s;
        }
        
        .vc-progress-text {
            font-size: 0.75rem;
            color: #e74c3c;
            margin-top: 3px;
        }
        
        /* My Vouchers Section */
        .my-vouchers-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .my-vouchers-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .my-vouchers-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .my-vouchers-count {
            background: var(--primary);
            color: white;
            padding: 3px 12px;
            border-radius: 15px;
            font-size: 0.9rem;
        }
        
        .my-vouchers-scroll {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            -webkit-overflow-scrolling: touch;
        }
        
        .my-voucher-card {
            min-width: 280px;
            background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);
            border: 2px solid var(--primary);
            border-radius: 10px;
            padding: 1rem;
            position: relative;
        }
        
        .my-voucher-card::before {
            content: '✓';
            position: absolute;
            top: -8px;
            right: -8px;
            width: 24px;
            height: 24px;
            background: var(--success);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        /* Empty state */
        .empty-vouchers {
            text-align: center;
            padding: 3rem;
            color: #636e72;
        }
        
        .empty-vouchers i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
        
        /* Flash banner */
        .flash-banner {
            background: linear-gradient(90deg, #e74c3c, #c0392b, #e74c3c);
            background-size: 200% 100%;
            animation: flashBg 2s linear infinite;
            color: white;
            padding: 0.8rem;
            text-align: center;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }
        
        @keyframes flashBg {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }
        
        .flash-banner .countdown {
            background: white;
            color: #e74c3c;
            padding: 5px 12px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 1.1rem;
        }
        
        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #2d3436;
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            z-index: 10000;
            opacity: 0;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        
        .toast.success {
            background: var(--success);
        }
        
        .toast i {
            font-size: 1.3rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .voucher-hero h1 {
                font-size: 1.8rem;
            }
            
            .hero-stats {
                gap: 1.5rem;
            }
            
            .hero-stat .number {
                font-size: 1.8rem;
            }
            
            .voucher-grid {
                grid-template-columns: 1fr;
            }
            
            .vc-tabs {
                padding: 1rem 0;
            }
            
            .vc-tab {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<?php @include '../header.php'; ?>

<?php if(isset($_GET['collected'])): ?>
<div class="toast success show" id="toast">
    <i class="fas fa-check-circle"></i>
    <span>Đã lưu voucher vào kho của bạn!</span>
</div>
<script>setTimeout(() => document.getElementById('toast').classList.remove('show'), 3000);</script>
<?php endif; ?>

<!-- Flash Banner -->
<?php if(count($vouchers_by_cat['expiring']) > 0): ?>
<div class="flash-banner">
    <i class="fas fa-bolt"></i>
    <span>⚡ <?php echo count($vouchers_by_cat['expiring']); ?> VOUCHER SẮP HẾT HẠN!</span>
    <span class="countdown" id="countdown">00:00:00</span>
    <span>Thu thập ngay!</span>
</div>
<?php endif; ?>

<!-- Hero -->
<section class="voucher-hero">
    <h1>🎟️ Kho Voucher</h1>
    <p>Thu thập voucher và tiết kiệm khi mua sắm</p>
    
    <div class="hero-stats">
        <div class="hero-stat">
            <div class="number"><?php echo $total_vouchers; ?></div>
            <div class="label">Voucher khả dụng</div>
        </div>
        <div class="hero-stat">
            <div class="number"><?php echo count($my_vouchers); ?></div>
            <div class="label">Voucher của tôi</div>
        </div>
        <div class="hero-stat">
            <div class="number"><?php echo count($vouchers_by_cat['hot']); ?></div>
            <div class="label">Voucher HOT</div>
        </div>
    </div>
</section>

<section class="voucher-center">
    <div class="vc-container">
        
        <!-- Category Tabs -->
        <div class="vc-tabs">
            <div class="vc-tab active" data-cat="all">
                <i class="fas fa-th-large"></i> Tất cả
            </div>
            <div class="vc-tab" data-cat="my">
                <i class="fas fa-wallet"></i> Của tôi
                <span class="count"><?php echo count($my_vouchers); ?></span>
            </div>
            <div class="vc-tab" data-cat="hot">
                <i class="fas fa-fire"></i> HOT
                <span class="count"><?php echo count($vouchers_by_cat['hot']); ?></span>
            </div>
            <div class="vc-tab" data-cat="freeship">
                <i class="fas fa-truck"></i> Freeship
            </div>
            <div class="vc-tab" data-cat="new">
                <i class="fas fa-sparkles"></i> Mới
            </div>
            <div class="vc-tab" data-cat="expiring">
                <i class="fas fa-clock"></i> Sắp hết hạn
                <span class="count" style="background:#e74c3c;color:white;"><?php echo count($vouchers_by_cat['expiring']); ?></span>
            </div>
        </div>
        
        <!-- My Vouchers Section -->
        <?php if($user_id && count($my_vouchers) > 0): ?>
        <div class="my-vouchers-section" id="section-my">
            <div class="my-vouchers-header">
                <h3 class="my-vouchers-title">
                    <i class="fas fa-wallet" style="color:var(--primary);"></i>
                    Voucher của tôi
                </h3>
                <span class="my-vouchers-count"><?php echo count($my_vouchers); ?> voucher</span>
            </div>
            
            <div class="my-vouchers-scroll">
                <?php foreach($my_vouchers as $v): ?>
                <div class="my-voucher-card">
                    <div class="vc-code"><?php echo e($v['code']); ?></div>
                    <div class="vc-name"><?php echo e($v['name']); ?></div>
                    <div class="vc-conditions">
                        <?php if($v['discount_type'] == 'percent'): ?>
                            Giảm <?php echo $v['discount_value']; ?>%
                            <?php if($v['max_discount']): ?> (tối đa <?php echo number_format($v['max_discount'], 0, ',', '.'); ?>₫)<?php endif; ?>
                        <?php else: ?>
                            Giảm <?php echo number_format($v['discount_value'], 0, ',', '.'); ?>₫
                        <?php endif; ?>
                    </div>
                    <?php if($v['end_date']): ?>
                    <div class="vc-expiry">
                        <i class="fas fa-clock"></i>
                        HSD: <?php echo date('d/m/Y', strtotime($v['end_date'])); ?>
                    </div>
                    <?php endif; ?>
                    <a href="../pages/cart.php" class="vc-btn vc-btn-use" style="display:inline-block;margin-top:10px;text-decoration:none;text-align:center;">
                        Dùng ngay
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- All Vouchers -->
        <div class="vc-section" id="section-all">
            <div class="vc-section-header">
                <h3 class="vc-section-title">
                    <i class="fas fa-tags" style="color:var(--primary);"></i>
                    Tất cả Voucher
                    <span class="badge"><?php echo $total_vouchers; ?></span>
                </h3>
            </div>
            
            <div class="voucher-grid">
                <?php 
                if($all_vouchers && $total_vouchers > 0):
                    mysqli_data_seek($all_vouchers, 0);
                    while($v = mysqli_fetch_assoc($all_vouchers)): 
                        $is_freeship = stripos($v['code'], 'FREESHIP') !== false || stripos($v['name'], 'ship') !== false;
                        $is_gold = $v['discount_type'] == 'percent' && $v['discount_value'] >= 20;
                        $usage_percent = $v['usage_limit'] ? ($v['used_count'] / $v['usage_limit']) * 100 : 0;
                        $is_expiring = $v['end_date'] && strtotime($v['end_date']) < strtotime('+3 days');
                        $v_is_collected = isset($collected_vouchers[$v['id']]);
                ?>
                <div class="voucher-card <?php echo $v_is_collected ? 'collected' : ''; ?>">
                    <div class="vc-left <?php echo $is_freeship ? 'freeship' : ($is_gold ? 'gold' : ''); ?>">
                        <div class="vc-icon">
                            <?php echo $is_freeship ? '🚚' : '🎟️'; ?>
                        </div>
                        <div class="vc-discount">
                            <?php if($v['discount_type'] == 'percent'): ?>
                                <?php echo $v['discount_value']; ?>%
                            <?php else: ?>
                                <?php echo number_format($v['discount_value']/1000, 0); ?>K
                            <?php endif; ?>
                        </div>
                        <div class="vc-discount-label">GIẢM</div>
                    </div>
                    
                    <div class="vc-right">
                        <div>
                            <div class="vc-name"><?php echo e($v['name']); ?></div>
                            <div class="vc-code"><?php echo e($v['code']); ?></div>
                            <div class="vc-conditions">
                                <?php if($v['min_order_value'] > 0): ?>
                                <div><i class="fas fa-shopping-cart"></i> Đơn từ <?php echo number_format($v['min_order_value'], 0, ',', '.'); ?>₫</div>
                                <?php endif; ?>
                                <?php if($v['max_discount'] && $v['discount_type'] == 'percent'): ?>
                                <div><i class="fas fa-hand-holding-usd"></i> Giảm tối đa <?php echo number_format($v['max_discount'], 0, ',', '.'); ?>₫</div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($v['usage_limit']): ?>
                            <div class="vc-progress">
                                <div class="vc-progress-bar">
                                    <div class="vc-progress-fill" style="width: <?php echo $usage_percent; ?>%"></div>
                                </div>
                                <div class="vc-progress-text">Đã dùng <?php echo round($usage_percent); ?>%</div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="vc-footer">
                            <?php if($v['end_date']): ?>
                            <div class="vc-expiry <?php echo $is_expiring ? 'warning' : ''; ?>">
                                <i class="fas fa-clock"></i>
                                <?php echo $is_expiring ? 'Còn ' . ceil((strtotime($v['end_date']) - time()) / 86400) . ' ngày' : 'HSD: ' . date('d/m', strtotime($v['end_date'])); ?>
                            </div>
                            <?php else: ?>
                            <div></div>
                            <?php endif; ?>
                            
                            <?php if($user_id): ?>
                                <?php 
                                $is_collected = isset($collected_vouchers[$v['id']]);
                                $times_used = $used_vouchers[$v['id']] ?? 0;
                                $user_limit = (int)($v['user_limit'] ?? 0);
                                ?>
                                <?php if($is_collected): ?>
                                    <button class="vc-btn vc-btn-collected">
                                        <i class="fas fa-check"></i> Đã lưu
                                    </button>
                                <?php elseif($user_limit > 0 && $times_used >= $user_limit): ?>
                                    <button class="vc-btn vc-btn-collected">Đã dùng</button>
                                <?php else: ?>
                                    <a href="?collect=<?php echo $v['id']; ?>" class="vc-btn vc-btn-collect">
                                        Lưu
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="../auth/login.php" class="vc-btn vc-btn-collect">Đăng nhập</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #999;">
                    <i class="fas fa-ticket-alt" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>Chưa có voucher nào</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</section>

<?php @include '../footer.php'; ?>

<script>
// Tab switching
document.querySelectorAll('.vc-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.vc-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const cat = this.dataset.cat;
        // Filter vouchers based on category
        filterVouchers(cat);
    });
});

function filterVouchers(cat) {
    const cards = document.querySelectorAll('.voucher-card');
    const mySection = document.getElementById('section-my');
    const allSection = document.getElementById('section-all');
    
    if(cat === 'my') {
        if(mySection) mySection.style.display = 'block';
        allSection.style.display = 'none';
        return;
    }
    
    if(mySection) mySection.style.display = cat === 'all' ? 'block' : 'none';
    allSection.style.display = 'block';
    
    // Show all for now (can implement filtering logic)
    cards.forEach(card => card.style.display = 'flex');
}

// Countdown timer
function updateCountdown() {
    const now = new Date();
    const midnight = new Date();
    midnight.setHours(24, 0, 0, 0);
    
    const diff = midnight - now;
    const hours = Math.floor(diff / 3600000);
    const minutes = Math.floor((diff % 3600000) / 60000);
    const seconds = Math.floor((diff % 60000) / 1000);
    
    const el = document.getElementById('countdown');
    if(el) {
        el.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
}

setInterval(updateCountdown, 1000);
updateCountdown();
</script>

</body>
</html>
