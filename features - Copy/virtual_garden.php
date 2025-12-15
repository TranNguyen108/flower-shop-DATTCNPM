<?php
/**
 * 🌱 Vườn Hoa Ảo - Virtual Garden
 * Trồng hoa ảo, tưới nước, thu hoạch điểm
 */

@include '../config.php';

$user_id = $_SESSION['user_id'] ?? null;

if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

// Tạo bảng nếu chưa có
$check = mysqli_query($conn, "SHOW TABLES LIKE 'virtual_garden'");
if(mysqli_num_rows($check) == 0){
    mysqli_query($conn, "CREATE TABLE virtual_garden (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        slot_index INT NOT NULL,
        flower_type VARCHAR(50),
        planted_at TIMESTAMP NULL,
        last_watered TIMESTAMP NULL,
        growth_stage INT DEFAULT 0,
        is_dead TINYINT(1) DEFAULT 0,
        UNIQUE KEY user_slot (user_id, slot_index)
    )");
}

$check_points = mysqli_query($conn, "SHOW TABLES LIKE 'garden_points'");
if(mysqli_num_rows($check_points) == 0){
    mysqli_query($conn, "CREATE TABLE garden_points (
        user_id INT PRIMARY KEY,
        points INT DEFAULT 0,
        total_harvested INT DEFAULT 0,
        longest_streak INT DEFAULT 0,
        last_action_date DATE
    )");
}

// Lấy hoặc tạo điểm cho user
$points_result = mysqli_query($conn, "SELECT * FROM garden_points WHERE user_id = '$user_id'");
if(mysqli_num_rows($points_result) == 0){
    mysqli_query($conn, "INSERT INTO garden_points (user_id, points) VALUES ('$user_id', 0)");
    $user_points = ['points' => 0, 'total_harvested' => 0, 'longest_streak' => 0];
} else {
    $user_points = mysqli_fetch_assoc($points_result);
}

// Các loại hoa có thể trồng
$flower_types = [
    'rose' => ['name' => 'Hoa Hồng', 'emoji' => '🌹', 'grow_time' => 3, 'points' => 50, 'stages' => ['🌱', '🌿', '🌷', '🌹']],
    'sunflower' => ['name' => 'Hướng Dương', 'emoji' => '🌻', 'grow_time' => 2, 'points' => 30, 'stages' => ['🌱', '🌿', '🌼', '🌻']],
    'tulip' => ['name' => 'Hoa Tulip', 'emoji' => '🌷', 'grow_time' => 2, 'points' => 35, 'stages' => ['🌱', '🌿', '🌸', '🌷']],
    'cherry' => ['name' => 'Hoa Anh Đào', 'emoji' => '🌸', 'grow_time' => 4, 'points' => 60, 'stages' => ['🌱', '🌿', '🌼', '🌸']],
    'hibiscus' => ['name' => 'Hoa Dâm Bụt', 'emoji' => '🌺', 'grow_time' => 3, 'points' => 45, 'stages' => ['🌱', '🌿', '🌼', '🌺']],
    'lotus' => ['name' => 'Hoa Sen', 'emoji' => '🪷', 'grow_time' => 5, 'points' => 80, 'stages' => ['🌱', '🌿', '🌼', '🪷']],
];

// Xử lý action
$message = [];
$action = $_GET['action'] ?? '';
$slot = isset($_GET['slot']) ? (int)$_GET['slot'] : -1;
$flower = $_GET['flower'] ?? '';

if($action == 'plant' && $slot >= 0 && isset($flower_types[$flower])){
    // Kiểm tra slot trống
    $check_slot = mysqli_query($conn, "SELECT * FROM virtual_garden WHERE user_id = '$user_id' AND slot_index = '$slot'");
    if(mysqli_num_rows($check_slot) == 0){
        mysqli_query($conn, "INSERT INTO virtual_garden (user_id, slot_index, flower_type, planted_at, last_watered, growth_stage) 
            VALUES ('$user_id', '$slot', '$flower', NOW(), NOW(), 0)");
        $message[] = 'Đã trồng ' . $flower_types[$flower]['name'] . '!';
    } else {
        $existing = mysqli_fetch_assoc($check_slot);
        if($existing['flower_type'] == null){
            mysqli_query($conn, "UPDATE virtual_garden SET flower_type = '$flower', planted_at = NOW(), last_watered = NOW(), growth_stage = 0, is_dead = 0 
                WHERE user_id = '$user_id' AND slot_index = '$slot'");
            $message[] = 'Đã trồng ' . $flower_types[$flower]['name'] . '!';
        }
    }
}

if($action == 'water' && $slot >= 0){
    $result = mysqli_query($conn, "SELECT * FROM virtual_garden WHERE user_id = '$user_id' AND slot_index = '$slot' AND flower_type IS NOT NULL");
    if(mysqli_num_rows($result) > 0){
        $plant = mysqli_fetch_assoc($result);
        if(!$plant['is_dead']){
            $last_watered = strtotime($plant['last_watered']);
            $hours_since = (time() - $last_watered) / 3600;
            
            if($hours_since >= 6){
                $new_stage = min($plant['growth_stage'] + 1, 3);
                mysqli_query($conn, "UPDATE virtual_garden SET last_watered = NOW(), growth_stage = '$new_stage' 
                    WHERE user_id = '$user_id' AND slot_index = '$slot'");
                $message[] = 'Đã tưới nước! Cây đang phát triển 🌱';
            } else {
                $wait_hours = round(6 - $hours_since, 1);
                $message[] = 'Cây chưa cần tưới. Chờ thêm ' . $wait_hours . ' giờ nữa.';
            }
        }
    }
}

if($action == 'harvest' && $slot >= 0){
    $result = mysqli_query($conn, "SELECT * FROM virtual_garden WHERE user_id = '$user_id' AND slot_index = '$slot'");
    if(mysqli_num_rows($result) > 0){
        $plant = mysqli_fetch_assoc($result);
        if($plant['growth_stage'] >= 3 && !$plant['is_dead'] && isset($flower_types[$plant['flower_type']])){
            $points = $flower_types[$plant['flower_type']]['points'];
            mysqli_query($conn, "UPDATE garden_points SET points = points + $points, total_harvested = total_harvested + 1 WHERE user_id = '$user_id'");
            mysqli_query($conn, "UPDATE virtual_garden SET flower_type = NULL, growth_stage = 0, is_dead = 0 WHERE user_id = '$user_id' AND slot_index = '$slot'");
            $user_points['points'] += $points;
            $message[] = 'Thu hoạch thành công! +' . $points . ' điểm 🎉';
        }
    }
}

if($action == 'remove' && $slot >= 0){
    mysqli_query($conn, "UPDATE virtual_garden SET flower_type = NULL, growth_stage = 0, is_dead = 0 WHERE user_id = '$user_id' AND slot_index = '$slot'");
    $message[] = 'Đã dọn ô đất!';
}

// Lấy trạng thái vườn
$garden_slots = [];
for($i = 0; $i < 6; $i++){
    $garden_slots[$i] = null;
}

$slots_result = mysqli_query($conn, "SELECT * FROM virtual_garden WHERE user_id = '$user_id'");
while($row = mysqli_fetch_assoc($slots_result)){
    $garden_slots[$row['slot_index']] = $row;
    
    // Kiểm tra cây chết (không tưới > 24h)
    if($row['flower_type'] && !$row['is_dead']){
        $hours_since_water = (time() - strtotime($row['last_watered'])) / 3600;
        if($hours_since_water > 48){
            mysqli_query($conn, "UPDATE virtual_garden SET is_dead = 1 WHERE id = '{$row['id']}'");
            $garden_slots[$row['slot_index']]['is_dead'] = 1;
        }
    }
}

// Refresh points
$points_result = mysqli_query($conn, "SELECT * FROM garden_points WHERE user_id = '$user_id'");
$user_points = mysqli_fetch_assoc($points_result);

// Rewards
$rewards = [
    ['points' => 100, 'reward' => 'Giảm 5%', 'code' => 'GARDEN5'],
    ['points' => 300, 'reward' => 'Giảm 10%', 'code' => 'GARDEN10'],
    ['points' => 500, 'reward' => 'Giảm 15%', 'code' => 'GARDEN15'],
    ['points' => 1000, 'reward' => 'Giảm 25%', 'code' => 'GARDEN25'],
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vườn Hoa Ảo - Flower Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .garden-section {
            padding: 2rem;
            background: linear-gradient(180deg, #87CEEB 0%, #98D8AA 50%, #64B5F6 100%);
            min-height: 100vh;
        }
        
        .garden-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        /* Stats Bar */
        .stats-bar {
            display: flex;
            justify-content: space-around;
            background: rgba(255,255,255,0.95);
            padding: 1.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.3rem;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2d3436;
        }
        
        .stat-label {
            color: #636e72;
            font-size: 0.9rem;
        }
        
        /* Garden Grid */
        .garden-area {
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect fill="%238B4513" x="0" y="0" width="100" height="100"/><rect fill="%23654321" x="5" y="5" width="90" height="90" rx="5"/></svg>');
            background-size: cover;
            border-radius: 25px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 50px rgba(0,0,0,0.2);
        }
        
        .garden-title {
            text-align: center;
            color: white;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .garden-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .garden-slot {
            aspect-ratio: 1;
            background: linear-gradient(135deg, #8B4513 0%, #654321 100%);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            cursor: pointer;
            transition: all 0.3s;
            border: 4px solid #5D4037;
            box-shadow: inset 0 -5px 15px rgba(0,0,0,0.3);
        }
        
        .garden-slot:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3), inset 0 -5px 15px rgba(0,0,0,0.3);
        }
        
        .garden-slot.empty {
            background: linear-gradient(135deg, #6B4423 0%, #4a3520 100%);
        }
        
        .garden-slot.empty::after {
            content: '+';
            font-size: 3rem;
            color: rgba(255,255,255,0.3);
        }
        
        .plant-emoji {
            font-size: 4rem;
            animation: sway 3s ease-in-out infinite;
        }
        
        @keyframes sway {
            0%, 100% { transform: rotate(-5deg); }
            50% { transform: rotate(5deg); }
        }
        
        .plant-dead {
            filter: grayscale(100%);
            opacity: 0.6;
            animation: none;
        }
        
        .growth-bar {
            position: absolute;
            bottom: 10px;
            left: 10px;
            right: 10px;
            height: 8px;
            background: rgba(0,0,0,0.3);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .growth-progress {
            height: 100%;
            background: linear-gradient(90deg, #4ade80, #22c55e);
            border-radius: 4px;
            transition: width 0.5s;
        }
        
        .water-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.2rem;
        }
        
        .needs-water {
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        /* Action buttons */
        .slot-actions {
            position: absolute;
            bottom: -60px;
            left: 50%;
            transform: translateX(-50%);
            display: none;
            gap: 0.5rem;
            z-index: 10;
        }
        
        .garden-slot:hover .slot-actions {
            display: flex;
        }
        
        .slot-btn {
            padding: 0.5rem 0.8rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .btn-water {
            background: #3498db;
            color: white;
        }
        
        .btn-harvest {
            background: #f39c12;
            color: white;
        }
        
        .btn-remove {
            background: #e74c3c;
            color: white;
        }
        
        /* Flower Selection */
        .flower-shop {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .flower-shop h3 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #2d3436;
        }
        
        .flower-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .flower-card {
            text-align: center;
            padding: 1.5rem 1rem;
            background: #f8f9fa;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s;
            border: 3px solid transparent;
        }
        
        .flower-card:hover {
            border-color: #4ade80;
            transform: translateY(-5px);
        }
        
        .flower-card.selected {
            border-color: #4ade80;
            background: #dcfce7;
        }
        
        .flower-card-emoji {
            font-size: 3rem;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .flower-card-name {
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 0.3rem;
        }
        
        .flower-card-info {
            font-size: 0.85rem;
            color: #636e72;
        }
        
        /* Rewards */
        .rewards-section {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .rewards-section h3 {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .rewards-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .reward-item {
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .reward-item.available {
            background: linear-gradient(135deg, #4ade80, #22c55e);
            color: white;
        }
        
        .reward-points {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .reward-value {
            font-size: 1.2rem;
            margin-bottom: 0.3rem;
        }
        
        .reward-code {
            font-family: monospace;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .reward-progress {
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            margin-top: 1rem;
            overflow: hidden;
        }
        
        .reward-progress-bar {
            height: 100%;
            background: #4ade80;
            border-radius: 4px;
        }
        
        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
        
        .modal-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .modal-flowers {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .modal-flower {
            padding: 1rem;
            font-size: 2.5rem;
            cursor: pointer;
            border-radius: 15px;
            transition: all 0.2s;
        }
        
        .modal-flower:hover {
            background: #dcfce7;
            transform: scale(1.1);
        }
        
        .modal-close {
            padding: 0.8rem 2rem;
            background: #e0e0e0;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
        }
        
        /* Weather */
        .weather-effect {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 100;
        }
        
        .sun {
            position: absolute;
            top: 20px;
            right: 50px;
            font-size: 4rem;
            animation: pulse 2s infinite;
        }
        
        .cloud {
            position: absolute;
            font-size: 3rem;
            animation: float 20s linear infinite;
        }
        
        @keyframes float {
            0% { transform: translateX(-100px); }
            100% { transform: translateX(calc(100vw + 100px)); }
        }
    </style>
</head>
<body>

<?php @include '../header.php'; ?>

<?php
if(!empty($message)){
    foreach($message as $msg){
        echo '<div class="message"><span>'.e($msg).'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
    }
}
?>

<section class="heading">
    <h3>🌱 Vườn Hoa Ảo</h3>
    <p><a href="../pages/home.php">Trang chủ</a> / Vườn Hoa Ảo</p>
</section>

<section class="garden-section">
    <div class="garden-container">
        
        <!-- Stats -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-icon">⭐</div>
                <div class="stat-value"><?php echo number_format($user_points['points']); ?></div>
                <div class="stat-label">Điểm tích lũy</div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">🌸</div>
                <div class="stat-value"><?php echo $user_points['total_harvested']; ?></div>
                <div class="stat-label">Đã thu hoạch</div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">🏆</div>
                <div class="stat-value">Lv.<?php echo floor($user_points['total_harvested'] / 10) + 1; ?></div>
                <div class="stat-label">Cấp độ</div>
            </div>
        </div>
        
        <!-- Garden -->
        <div class="garden-area">
            <h2 class="garden-title">🏡 Khu Vườn Của Tôi</h2>
            
            <div class="garden-grid">
                <?php for($i = 0; $i < 6; $i++): 
                    $slot = $garden_slots[$i];
                    $has_plant = $slot && $slot['flower_type'];
                    $is_dead = $has_plant && $slot['is_dead'];
                    $can_harvest = $has_plant && !$is_dead && $slot['growth_stage'] >= 3;
                    $needs_water = false;
                    
                    if($has_plant && !$is_dead){
                        $hours_since_water = (time() - strtotime($slot['last_watered'])) / 3600;
                        $needs_water = $hours_since_water >= 6;
                    }
                ?>
                <div class="garden-slot <?php echo !$has_plant ? 'empty' : ''; ?>" 
                     data-slot="<?php echo $i; ?>"
                     onclick="<?php echo !$has_plant ? 'openPlantModal('.$i.')' : ''; ?>">
                    
                    <?php if($has_plant): 
                        $flower = $flower_types[$slot['flower_type']] ?? null;
                        if($flower):
                            $stage = $slot['growth_stage'];
                            $emoji = $flower['stages'][$stage] ?? $flower['emoji'];
                    ?>
                        <div class="plant-emoji <?php echo $is_dead ? 'plant-dead' : ''; ?>">
                            <?php echo $is_dead ? '🥀' : $emoji; ?>
                        </div>
                        
                        <?php if($needs_water && !$is_dead): ?>
                        <div class="water-indicator needs-water">💧</div>
                        <?php endif; ?>
                        
                        <div class="growth-bar">
                            <div class="growth-progress" style="width: <?php echo ($stage / 3) * 100; ?>%"></div>
                        </div>
                        
                        <div class="slot-actions">
                            <?php if($is_dead): ?>
                                <a href="?action=remove&slot=<?php echo $i; ?>" class="slot-btn btn-remove">🗑️ Dọn</a>
                            <?php elseif($can_harvest): ?>
                                <a href="?action=harvest&slot=<?php echo $i; ?>" class="slot-btn btn-harvest">🌾 Thu</a>
                            <?php else: ?>
                                <a href="?action=water&slot=<?php echo $i; ?>" class="slot-btn btn-water">💧 Tưới</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; endif; ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <!-- Flower Shop -->
        <div class="flower-shop">
            <h3>🌼 Chọn Hoa Để Trồng</h3>
            <div class="flower-list">
                <?php foreach($flower_types as $key => $flower): ?>
                <div class="flower-card" data-flower="<?php echo $key; ?>">
                    <span class="flower-card-emoji"><?php echo $flower['emoji']; ?></span>
                    <div class="flower-card-name"><?php echo $flower['name']; ?></div>
                    <div class="flower-card-info">
                        ⏱️ <?php echo $flower['grow_time']; ?> ngày • ⭐ <?php echo $flower['points']; ?> điểm
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Rewards -->
        <div class="rewards-section">
            <h3>🎁 Đổi Điểm Thưởng</h3>
            <div class="rewards-list">
                <?php foreach($rewards as $r): 
                    $progress = min(100, ($user_points['points'] / $r['points']) * 100);
                    $available = $user_points['points'] >= $r['points'];
                ?>
                <div class="reward-item <?php echo $available ? 'available' : ''; ?>">
                    <div class="reward-points"><?php echo $r['points']; ?> ⭐</div>
                    <div class="reward-value"><?php echo $r['reward']; ?></div>
                    <div class="reward-code">Mã: <?php echo $r['code']; ?></div>
                    <?php if(!$available): ?>
                    <div class="reward-progress">
                        <div class="reward-progress-bar" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                    <?php else: ?>
                    <button style="margin-top: 1rem; padding: 0.5rem 1rem; background: white; color: #22c55e; border: none; border-radius: 8px; cursor: pointer;">
                        Sử dụng ngay
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
    </div>
</section>

<!-- Plant Modal -->
<div class="modal-overlay" id="plantModal">
    <div class="modal-content">
        <h3 class="modal-title">🌱 Chọn hoa để trồng</h3>
        <div class="modal-flowers">
            <?php foreach($flower_types as $key => $flower): ?>
            <span class="modal-flower" onclick="plantFlower('<?php echo $key; ?>')" title="<?php echo $flower['name']; ?>">
                <?php echo $flower['emoji']; ?>
            </span>
            <?php endforeach; ?>
        </div>
        <button class="modal-close" onclick="closePlantModal()">Hủy</button>
    </div>
</div>

<!-- Weather effects -->
<div class="weather-effect">
    <div class="sun">☀️</div>
    <div class="cloud" style="top: 60px; animation-delay: 0s;">☁️</div>
    <div class="cloud" style="top: 100px; animation-delay: -10s;">☁️</div>
</div>

<?php @include '../footer.php'; ?>

<script>
let selectedSlot = -1;

function openPlantModal(slot) {
    selectedSlot = slot;
    document.getElementById('plantModal').classList.add('active');
}

function closePlantModal() {
    document.getElementById('plantModal').classList.remove('active');
    selectedSlot = -1;
}

function plantFlower(flower) {
    if(selectedSlot >= 0) {
        window.location.href = `?action=plant&slot=${selectedSlot}&flower=${flower}`;
    }
}

// Close modal on outside click
document.getElementById('plantModal').addEventListener('click', function(e) {
    if(e.target === this) closePlantModal();
});
</script>

</body>
</html>
