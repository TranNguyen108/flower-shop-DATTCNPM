<?php
/**
 * 📅 Đặt Lịch Tặng Hoa & 🔔 Nhắc Nhở Ngày Lễ
 * Đặt trước hoa cho ngày đặc biệt
 */

@include '../config.php';

$user_id = $_SESSION['user_id'] ?? null;

if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

$message = [];

// Xử lý lưu reminder
if(isset($_POST['save_reminder'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message[] = 'Lỗi bảo mật!';
    } else {
        $event_name = sanitize_input($_POST['event_name']);
        $event_date = sanitize_input($_POST['event_date']);
        $recipient = sanitize_input($_POST['recipient']);
        $note = sanitize_input($_POST['note'] ?? '');
        $remind_days = (int)$_POST['remind_days'];
        $repeat_yearly = isset($_POST['repeat_yearly']) ? 1 : 0;
        
        // Kiểm tra bảng tồn tại, nếu không thì tạo
        $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'flower_reminders'");
        if(mysqli_num_rows($check_table) == 0){
            mysqli_query($conn, "CREATE TABLE flower_reminders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                event_name VARCHAR(100) NOT NULL,
                event_date DATE NOT NULL,
                recipient VARCHAR(100),
                note TEXT,
                remind_days INT DEFAULT 3,
                repeat_yearly TINYINT(1) DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
        }
        
        $insert = mysqli_query($conn, "INSERT INTO flower_reminders 
            (user_id, event_name, event_date, recipient, note, remind_days, repeat_yearly) 
            VALUES ('$user_id', '$event_name', '$event_date', '$recipient', '$note', '$remind_days', '$repeat_yearly')");
        
        if($insert){
            $message[] = 'Đã lưu lịch nhắc nhở thành công!';
        } else {
            $message[] = 'Có lỗi xảy ra!';
        }
    }
}

// Xóa reminder
if(isset($_GET['delete_reminder'])){
    $rid = (int)$_GET['delete_reminder'];
    mysqli_query($conn, "DELETE FROM flower_reminders WHERE id = '$rid' AND user_id = '$user_id'");
    header('location: schedule_gift.php');
    exit;
}

// Lấy danh sách reminders
$reminders = [];
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'flower_reminders'");
if(mysqli_num_rows($check_table) > 0){
    $result = mysqli_query($conn, "SELECT * FROM flower_reminders WHERE user_id = '$user_id' ORDER BY event_date ASC");
    while($row = mysqli_fetch_assoc($result)){
        $reminders[] = $row;
    }
}

// Ngày lễ cố định
$holidays = [
    ['date' => '02-14', 'name' => 'Valentine', 'emoji' => '💕'],
    ['date' => '03-08', 'name' => 'Quốc tế Phụ nữ', 'emoji' => '👩'],
    ['date' => '05-01', 'name' => 'Quốc tế Lao động', 'emoji' => '💪'],
    ['date' => '10-20', 'name' => 'Ngày Phụ nữ Việt Nam', 'emoji' => '🌸'],
    ['date' => '11-20', 'name' => 'Ngày Nhà giáo', 'emoji' => '📚'],
    ['date' => '12-25', 'name' => 'Giáng sinh', 'emoji' => '🎄'],
];

// Tính ngày lễ sắp tới
$upcoming_holidays = [];
$today = new DateTime();
foreach($holidays as $h){
    $date = DateTime::createFromFormat('m-d', $h['date']);
    $date->setDate((int)$today->format('Y'), (int)$date->format('m'), (int)$date->format('d'));
    if($date < $today){
        $date->modify('+1 year');
    }
    $diff = $today->diff($date)->days;
    $h['full_date'] = $date->format('Y-m-d');
    $h['days_left'] = $diff;
    $upcoming_holidays[] = $h;
}
usort($upcoming_holidays, fn($a, $b) => $a['days_left'] - $b['days_left']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lịch Tặng Hoa - Flower Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .schedule-section {
            padding: 3rem 2rem;
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            min-height: 100vh;
        }
        
        .schedule-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        @media (max-width: 992px) {
            .schedule-container {
                grid-template-columns: 1fr;
            }
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .card-header i {
            font-size: 2rem;
            color: #667eea;
        }
        
        .card-header h2 {
            margin: 0;
            color: #2d3436;
        }
        
        /* Form */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2d3436;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        /* Holidays */
        .holidays-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .holiday-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            transition: all 0.3s;
        }
        
        .holiday-item:hover {
            background: #667eea10;
        }
        
        .holiday-emoji {
            font-size: 2.5rem;
        }
        
        .holiday-info {
            flex: 1;
        }
        
        .holiday-name {
            font-weight: 600;
            color: #2d3436;
            font-size: 1.1rem;
        }
        
        .holiday-date {
            color: #636e72;
            font-size: 0.95rem;
        }
        
        .holiday-countdown {
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .holiday-countdown.urgent {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .btn-quick-order {
            padding: 0.5rem 1rem;
            background: #00b894;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        /* My Reminders */
        .reminders-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .reminder-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.2rem;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }
        
        .reminder-icon {
            font-size: 2rem;
        }
        
        .reminder-info {
            flex: 1;
        }
        
        .reminder-name {
            font-weight: 600;
            color: #2d3436;
            font-size: 1.1rem;
        }
        
        .reminder-details {
            color: #636e72;
            font-size: 0.95rem;
        }
        
        .reminder-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-delete {
            padding: 0.5rem 0.8rem;
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .btn-order {
            padding: 0.5rem 0.8rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #636e72;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        /* Quick Add */
        .quick-add-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px dashed #e0e0e0;
        }
        
        .quick-add-section h4 {
            color: #636e72;
            margin-bottom: 1rem;
        }
        
        .quick-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .quick-btn {
            padding: 0.6rem 1rem;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.95rem;
        }
        
        .quick-btn:hover {
            border-color: #667eea;
            background: #667eea10;
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
    <h3>📅 Đặt Lịch Tặng Hoa</h3>
    <p><a href="./home.php">Trang chủ</a> / Đặt Lịch Tặng Hoa</p>
</section>

<section class="schedule-section">
    <div class="schedule-container">
        
        <!-- Left: Form & My Reminders -->
        <div>
            <!-- Add Reminder Form -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bell"></i>
                    <h2>Thêm Nhắc Nhở Mới</h2>
                </div>
                
                <form action="" method="POST">
                    <?php echo csrf_field(); ?>
                    
                    <div class="form-group">
                        <label>📌 Tên sự kiện</label>
                        <input type="text" name="event_name" placeholder="VD: Sinh nhật mẹ, Kỷ niệm ngày cưới..." required>
                    </div>
                    
                    <div class="form-group">
                        <label>📆 Ngày</label>
                        <input type="date" name="event_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>👤 Người nhận</label>
                        <input type="text" name="recipient" placeholder="VD: Mẹ, Người yêu, Bạn thân...">
                    </div>
                    
                    <div class="form-group">
                        <label>⏰ Nhắc trước</label>
                        <select name="remind_days">
                            <option value="1">1 ngày</option>
                            <option value="3" selected>3 ngày</option>
                            <option value="7">1 tuần</option>
                            <option value="14">2 tuần</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>📝 Ghi chú</label>
                        <textarea name="note" rows="3" placeholder="Ghi chú thêm..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="repeat_yearly" id="repeat_yearly">
                            <label for="repeat_yearly">🔄 Nhắc lại hàng năm</label>
                        </div>
                    </div>
                    
                    <button type="submit" name="save_reminder" class="btn-submit">
                        <i class="fas fa-save"></i> Lưu Nhắc Nhở
                    </button>
                </form>
                
                <!-- Quick Add -->
                <div class="quick-add-section">
                    <h4>⚡ Thêm nhanh:</h4>
                    <div class="quick-btns">
                        <button class="quick-btn" onclick="quickAdd('Sinh nhật người yêu', '')">💕 Sinh nhật người yêu</button>
                        <button class="quick-btn" onclick="quickAdd('Sinh nhật mẹ', 'Mẹ')">👩 Sinh nhật mẹ</button>
                        <button class="quick-btn" onclick="quickAdd('Kỷ niệm ngày cưới', '')">💒 Kỷ niệm cưới</button>
                        <button class="quick-btn" onclick="quickAdd('Sinh nhật bạn thân', '')">🎂 Sinh nhật bạn</button>
                    </div>
                </div>
            </div>
            
            <!-- My Reminders -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <i class="fas fa-list-check"></i>
                    <h2>Lịch Nhắc Của Tôi</h2>
                </div>
                
                <div class="reminders-list">
                    <?php if(empty($reminders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-xmark"></i>
                        <p>Chưa có lịch nhắc nào</p>
                    </div>
                    <?php else: ?>
                        <?php foreach($reminders as $r): 
                            $event_date = new DateTime($r['event_date']);
                            $days_left = (int)$today->diff($event_date)->format('%r%a');
                        ?>
                        <div class="reminder-item">
                            <div class="reminder-icon">
                                <?php echo $days_left <= 7 ? '⏰' : '📅'; ?>
                            </div>
                            <div class="reminder-info">
                                <div class="reminder-name"><?php echo e($r['event_name']); ?></div>
                                <div class="reminder-details">
                                    📆 <?php echo $event_date->format('d/m/Y'); ?>
                                    <?php if($r['recipient']): ?>
                                    • 👤 <?php echo e($r['recipient']); ?>
                                    <?php endif; ?>
                                    <?php if($days_left > 0): ?>
                                    • Còn <b><?php echo $days_left; ?></b> ngày
                                    <?php elseif($days_left == 0): ?>
                                    • <b style="color:#e74c3c;">Hôm nay!</b>
                                    <?php else: ?>
                                    • <span style="color:#636e72;">Đã qua</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="reminder-actions">
                                <a href="../pages/shop.php" class="btn-order">
                                    <i class="fas fa-shopping-cart"></i>
                                </a>
                                <a href="?delete_reminder=<?php echo $r['id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Xóa nhắc nhở này?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Right: Upcoming Holidays -->
        <div>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-star"></i>
                    <h2>🎉 Ngày Lễ Sắp Tới</h2>
                </div>
                
                <div class="holidays-list">
                    <?php foreach(array_slice($upcoming_holidays, 0, 6) as $h): 
                        $date = new DateTime($h['full_date']);
                    ?>
                    <div class="holiday-item">
                        <div class="holiday-emoji"><?php echo $h['emoji']; ?></div>
                        <div class="holiday-info">
                            <div class="holiday-name"><?php echo $h['name']; ?></div>
                            <div class="holiday-date"><?php echo $date->format('d/m/Y'); ?></div>
                        </div>
                        <span class="holiday-countdown <?php echo $h['days_left'] <= 7 ? 'urgent' : ''; ?>">
                            <?php if($h['days_left'] == 0): ?>
                                Hôm nay!
                            <?php else: ?>
                                <?php echo $h['days_left']; ?> ngày
                            <?php endif; ?>
                        </span>
                        <button class="btn-quick-order" onclick="location.href='../pages/shop.php'">
                            Đặt hoa
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Tips -->
            <div class="card" style="margin-top: 2rem; background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                <h3 style="margin-bottom: 1rem;">💡 Mẹo hay</h3>
                <ul style="line-height: 2; padding-left: 1.5rem;">
                    <li>Đặt hoa trước 2-3 ngày để được phục vụ tốt nhất</li>
                    <li>Ngày lễ lớn nên đặt trước 1 tuần</li>
                    <li>Bật nhắc nhở hàng năm cho sinh nhật</li>
                    <li>Ghi chú sở thích hoa của người nhận</li>
                </ul>
            </div>
        </div>
        
    </div>
</section>

<?php @include '../footer.php'; ?>

<script>
function quickAdd(eventName, recipient) {
    document.querySelector('input[name="event_name"]').value = eventName;
    document.querySelector('input[name="recipient"]').value = recipient;
    document.querySelector('input[name="event_date"]').focus();
}
</script>

</body>
</html>
