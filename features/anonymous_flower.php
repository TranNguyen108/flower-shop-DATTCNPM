<?php
/**
 * 💝 Gửi Hoa Ẩn Danh
 * Gửi hoa bí mật với thông điệp ẩn
 */

@include '../config.php';

$user_id = $_SESSION['user_id'] ?? null;

$message = [];
$secret_code = null;

// Xử lý gửi hoa ẩn danh
if(isset($_POST['send_anonymous'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message[] = 'Lỗi bảo mật!';
    } else {
        // Tạo bảng nếu chưa có
        $check = mysqli_query($conn, "SHOW TABLES LIKE 'anonymous_flowers'");
        if(mysqli_num_rows($check) == 0){
            mysqli_query($conn, "CREATE TABLE anonymous_flowers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                secret_code VARCHAR(20) UNIQUE NOT NULL,
                sender_id INT,
                sender_alias VARCHAR(50),
                recipient_name VARCHAR(100) NOT NULL,
                recipient_phone VARCHAR(20),
                delivery_address TEXT NOT NULL,
                delivery_date DATE NOT NULL,
                message_text TEXT,
                hint_text VARCHAR(200),
                product_id INT,
                custom_bouquet TEXT,
                reveal_date DATE,
                status VARCHAR(20) DEFAULT 'pending',
                total_price DECIMAL(10,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
        }
        
        $secret_code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        $sender_alias = sanitize_input($_POST['sender_alias'] ?? 'Người ái mộ bí ẩn');
        $recipient_name = sanitize_input($_POST['recipient_name']);
        $recipient_phone = sanitize_input($_POST['recipient_phone'] ?? '');
        $delivery_address = sanitize_input($_POST['delivery_address']);
        $delivery_date = sanitize_input($_POST['delivery_date']);
        $message_text = sanitize_input($_POST['message_text'] ?? '');
        $hint_text = sanitize_input($_POST['hint_text'] ?? '');
        $reveal_date = !empty($_POST['reveal_date']) ? sanitize_input($_POST['reveal_date']) : null;
        
        $query = "INSERT INTO anonymous_flowers 
            (secret_code, sender_id, sender_alias, recipient_name, recipient_phone, delivery_address, delivery_date, message_text, hint_text, reveal_date, status) 
            VALUES ('$secret_code', " . ($user_id ? "'$user_id'" : "NULL") . ", '$sender_alias', '$recipient_name', '$recipient_phone', '$delivery_address', '$delivery_date', '$message_text', '$hint_text', " . ($reveal_date ? "'$reveal_date'" : "NULL") . ", 'pending')";
        
        if(mysqli_query($conn, $query)){
            // Thành công - hiển thị mã bí mật
        } else {
            $message[] = 'Có lỗi xảy ra: ' . mysqli_error($conn);
            $secret_code = null;
        }
    }
}

// Tra cứu hoa ẩn danh
$lookup_result = null;
if(isset($_POST['lookup_code'])){
    $code = sanitize_input($_POST['lookup_code']);
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'anonymous_flowers'");
    if(mysqli_num_rows($check) > 0){
        $result = mysqli_query($conn, "SELECT * FROM anonymous_flowers WHERE secret_code = '$code'");
        if(mysqli_num_rows($result) > 0){
            $lookup_result = mysqli_fetch_assoc($result);
        } else {
            $message[] = 'Không tìm thấy mã này!';
        }
    }
}

// Các mẫu lời nhắn
$message_templates = [
    ['emoji' => '💕', 'text' => 'Em là ánh nắng làm ấm tim anh mỗi ngày...'],
    ['emoji' => '🌹', 'text' => 'Người ta nói hoa hồng đại diện cho tình yêu, nhưng với anh, em còn đẹp hơn thế...'],
    ['emoji' => '✨', 'text' => 'Từ lần đầu gặp em, anh đã biết em là người đặc biệt...'],
    ['emoji' => '🌸', 'text' => 'Anh chỉ muốn nói rằng, em thật tuyệt vời!'],
    ['emoji' => '💝', 'text' => 'Hãy nhận bó hoa này như một lời cảm ơn vì đã xuất hiện trong cuộc đời anh...'],
    ['emoji' => '🌺', 'text' => 'Mỗi cánh hoa là một ngày anh nghĩ về em...'],
];

// Các gợi ý hint
$hint_examples = [
    'Người thường ngồi đối diện em trong lớp',
    'Người hay mua cà phê sáng ở quán quen',
    'Đồng nghiệp cùng tầng với em',
    'Người bạn đã biết em từ năm nhất',
    'Ai đó trong hội nhóm gym buổi tối',
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi Hoa Ẩn Danh - Flower Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .anonymous-section {
            padding: 3rem 2rem;
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #feada6 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            border-radius: 25px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #ff6b95, #c44569);
        }
        
        .card-title {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .card-title h2 {
            font-size: 2rem;
            color: #2d3436;
            margin-bottom: 0.5rem;
        }
        
        .card-title p {
            color: #636e72;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            justify-content: center;
        }
        
        .tab {
            padding: 1rem 2rem;
            background: #f8f9fa;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .tab.active {
            background: linear-gradient(135deg, #ff6b95, #c44569);
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
            font-size: 1.05rem;
        }
        
        .form-group label small {
            font-weight: normal;
            color: #636e72;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: #ff6b95;
            box-shadow: 0 0 0 4px rgba(255, 107, 149, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .btn-send {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #ff6b95, #c44569);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.3rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-send:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 107, 149, 0.4);
        }
        
        /* Templates */
        .templates-list {
            display: grid;
            gap: 0.8rem;
            margin-top: 1rem;
        }
        
        .template-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .template-item:hover {
            background: #ff6b9510;
            border-color: #ff6b95;
        }
        
        .template-emoji {
            font-size: 1.5rem;
        }
        
        .template-text {
            flex: 1;
            color: #2d3436;
            font-size: 0.95rem;
        }
        
        /* Hints */
        .hints-box {
            background: #fff5f7;
            padding: 1rem;
            border-radius: 12px;
            margin-top: 0.5rem;
        }
        
        .hints-box p {
            color: #636e72;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .hint-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .hint-tag {
            padding: 0.4rem 0.8rem;
            background: white;
            border: 1px solid #ffccd5;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #c44569;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .hint-tag:hover {
            background: #ff6b95;
            color: white;
        }
        
        /* Success */
        .success-card {
            text-align: center;
            padding: 3rem;
        }
        
        .success-icon {
            font-size: 5rem;
            animation: heartBeat 1.5s infinite;
        }
        
        @keyframes heartBeat {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.1); }
            50% { transform: scale(1); }
            75% { transform: scale(1.15); }
        }
        
        .secret-code-box {
            background: linear-gradient(135deg, #ff6b95, #c44569);
            color: white;
            padding: 2rem;
            border-radius: 20px;
            margin: 2rem 0;
        }
        
        .secret-code-box h3 {
            margin-bottom: 1rem;
        }
        
        .secret-code {
            font-size: 3rem;
            font-weight: bold;
            letter-spacing: 0.3rem;
            font-family: monospace;
            background: white;
            color: #c44569;
            padding: 1rem 2rem;
            border-radius: 10px;
            display: inline-block;
        }
        
        .secret-note {
            margin-top: 1rem;
            font-size: 0.95rem;
            opacity: 0.9;
        }
        
        .btn-copy {
            margin-top: 1rem;
            padding: 0.8rem 2rem;
            background: white;
            color: #c44569;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-copy:hover {
            transform: scale(1.05);
        }
        
        /* Lookup */
        .lookup-form {
            display: flex;
            gap: 1rem;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .lookup-form input {
            flex: 1;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 0.2rem;
            text-align: center;
        }
        
        .lookup-form button {
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #ff6b95, #c44569);
            color: white;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-size: 1.1rem;
        }
        
        /* Lookup Result */
        .lookup-result {
            text-align: center;
            padding: 2rem;
        }
        
        .flower-reveal {
            animation: revealFlower 1s ease-out;
        }
        
        @keyframes revealFlower {
            0% { transform: scale(0) rotate(-180deg); opacity: 0; }
            100% { transform: scale(1) rotate(0); opacity: 1; }
        }
        
        .flower-message {
            background: #fff5f7;
            padding: 2rem;
            border-radius: 20px;
            margin: 2rem 0;
            font-size: 1.2rem;
            color: #2d3436;
            font-style: italic;
            position: relative;
        }
        
        .flower-message::before {
            content: '"';
            font-size: 4rem;
            color: #ffccd5;
            position: absolute;
            top: -10px;
            left: 20px;
        }
        
        .sender-info {
            padding: 1.5rem;
            background: linear-gradient(135deg, #ff6b95, #c44569);
            color: white;
            border-radius: 15px;
            display: inline-block;
        }
        
        .sender-alias {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .sender-hint {
            margin-top: 0.5rem;
            font-size: 0.95rem;
            opacity: 0.9;
        }
        
        /* Animation */
        .floating-hearts {
            position: fixed;
            pointer-events: none;
            z-index: 999;
        }
        
        .heart {
            position: absolute;
            animation: floatUp 4s ease-out forwards;
            font-size: 1.5rem;
        }
        
        @keyframes floatUp {
            0% { transform: translateY(0) rotate(0); opacity: 1; }
            100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
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
    <h3>💝 Gửi Hoa Ẩn Danh</h3>
    <p><a href="../pages/home.php">Trang chủ</a> / Gửi Hoa Ẩn Danh</p>
</section>

<section class="anonymous-section">
    <div class="container">
        
        <?php if($secret_code): ?>
        <!-- Success -->
        <div class="card success-card">
            <div class="success-icon">💝</div>
            <h2 style="margin: 1rem 0; color: #c44569;">Thành công!</h2>
            <p style="color: #636e72; font-size: 1.1rem;">Bó hoa ẩn danh của bạn đã được tạo</p>
            
            <div class="secret-code-box">
                <h3>🔐 Mã Bí Mật Của Bạn</h3>
                <div class="secret-code" id="secretCode"><?php echo $secret_code; ?></div>
                <p class="secret-note">Lưu mã này để theo dõi. Người nhận có thể dùng mã này để xem thông tin người gửi (nếu bạn cho phép).</p>
                <button class="btn-copy" onclick="copyCode()">
                    <i class="fas fa-copy"></i> Sao chép mã
                </button>
            </div>
            
            <a href="anonymous_flower.php" style="color: #c44569; text-decoration: none; font-size: 1.1rem;">
                <i class="fas fa-plus"></i> Gửi thêm hoa ẩn danh khác
            </a>
        </div>
        
        <?php elseif($lookup_result): ?>
        <!-- Lookup Result -->
        <div class="card lookup-result">
            <div class="flower-reveal" style="font-size: 6rem;">💐</div>
            <h2 style="margin: 1rem 0; color: #c44569;">Bạn có hoa!</h2>
            
            <?php if($lookup_result['message_text']): ?>
            <div class="flower-message">
                <?php echo nl2br(e($lookup_result['message_text'])); ?>
            </div>
            <?php endif; ?>
            
            <div class="sender-info">
                <div class="sender-alias">
                    <i class="fas fa-user-secret"></i> <?php echo e($lookup_result['sender_alias']); ?>
                </div>
                <?php if($lookup_result['hint_text']): ?>
                <div class="sender-hint">
                    💡 Gợi ý: <?php echo e($lookup_result['hint_text']); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <p style="margin-top: 2rem; color: #636e72;">
                📅 Sẽ giao ngày: <?php echo date('d/m/Y', strtotime($lookup_result['delivery_date'])); ?>
            </p>
            
            <a href="anonymous_flower.php" style="display: inline-block; margin-top: 2rem; color: #c44569; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
        
        <?php else: ?>
        <!-- Main Form -->
        <div class="card">
            <div class="card-title">
                <h2>💝 Gửi Hoa Ẩn Danh</h2>
                <p>Thể hiện tình cảm một cách bí ẩn và lãng mạn</p>
            </div>
            
            <div class="tabs">
                <button class="tab active" onclick="switchTab('send')">
                    <i class="fas fa-paper-plane"></i> Gửi Hoa
                </button>
                <button class="tab" onclick="switchTab('lookup')">
                    <i class="fas fa-search"></i> Tra Cứu Mã
                </button>
            </div>
            
            <!-- Send Tab -->
            <div id="tab-send" class="tab-content active">
                <form action="" method="POST">
                    <?php echo csrf_field(); ?>
                    
                    <div class="form-group">
                        <label>🎭 Bí danh của bạn <small>(người nhận sẽ thấy)</small></label>
                        <input type="text" name="sender_alias" placeholder="VD: Người ái mộ bí ẩn, Chàng trai quán cà phê..." value="Người ái mộ bí ẩn">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>👤 Tên người nhận *</label>
                            <input type="text" name="recipient_name" required placeholder="Nhập tên người nhận">
                        </div>
                        <div class="form-group">
                            <label>📱 SĐT người nhận</label>
                            <input type="tel" name="recipient_phone" placeholder="Để shipper liên hệ">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>📍 Địa chỉ giao hoa *</label>
                        <input type="text" name="delivery_address" required placeholder="Nhập địa chỉ chi tiết">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>📅 Ngày giao *</label>
                            <input type="date" name="delivery_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="form-group">
                            <label>🔓 Ngày tiết lộ <small>(tùy chọn)</small></label>
                            <input type="date" name="reveal_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            <small style="color: #636e72;">Sau ngày này, người nhận có thể biết bạn là ai</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>💌 Lời nhắn <small>(sẽ in trong thiệp)</small></label>
                        <textarea name="message_text" rows="4" placeholder="Viết lời nhắn ngọt ngào của bạn..."></textarea>
                        
                        <div class="templates-list">
                            <p style="color: #636e72; margin-bottom: 0.5rem;">✨ Mẫu gợi ý:</p>
                            <?php foreach($message_templates as $t): ?>
                            <div class="template-item" onclick="useTemplate(this)">
                                <span class="template-emoji"><?php echo $t['emoji']; ?></span>
                                <span class="template-text"><?php echo $t['text']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>💡 Gợi ý về bạn <small>(để người nhận đoán)</small></label>
                        <input type="text" name="hint_text" placeholder="VD: Người hay ngồi đối diện em trong lớp...">
                        
                        <div class="hints-box">
                            <p>Ví dụ gợi ý:</p>
                            <div class="hint-tags">
                                <?php foreach($hint_examples as $h): ?>
                                <span class="hint-tag" onclick="useHint(this)"><?php echo $h; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="send_anonymous" class="btn-send">
                        <i class="fas fa-paper-plane"></i> Gửi Hoa Ẩn Danh
                    </button>
                </form>
            </div>
            
            <!-- Lookup Tab -->
            <div id="tab-lookup" class="tab-content">
                <div style="text-align: center; padding: 2rem 0;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🔍</div>
                    <h3 style="color: #2d3436; margin-bottom: 0.5rem;">Bạn nhận được hoa ẩn danh?</h3>
                    <p style="color: #636e72; margin-bottom: 2rem;">Nhập mã trên thiệp để xem thông tin</p>
                    
                    <form action="" method="POST" class="lookup-form">
                        <input type="text" name="lookup_code" placeholder="NHẬP MÃ" maxlength="8" required>
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</section>

<div class="floating-hearts" id="hearts"></div>

<?php @include '../footer.php'; ?>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    
    document.querySelector(`.tab:nth-child(${tab === 'send' ? 1 : 2})`).classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}

function useTemplate(el) {
    const text = el.querySelector('.template-text').innerText;
    document.querySelector('textarea[name="message_text"]').value = text;
    createHeart(el);
}

function useHint(el) {
    document.querySelector('input[name="hint_text"]').value = el.innerText;
}

function copyCode() {
    const code = document.getElementById('secretCode').innerText;
    navigator.clipboard.writeText(code).then(() => {
        alert('Đã sao chép mã: ' + code);
    });
}

// Floating hearts effect
function createHeart(el) {
    const rect = el.getBoundingClientRect();
    const heart = document.createElement('span');
    heart.className = 'heart';
    heart.innerHTML = ['💕', '💗', '💝', '❤️'][Math.floor(Math.random() * 4)];
    heart.style.left = rect.left + rect.width / 2 + 'px';
    heart.style.top = rect.top + 'px';
    document.getElementById('hearts').appendChild(heart);
    setTimeout(() => heart.remove(), 4000);
}

// Random hearts on page
setInterval(() => {
    if(Math.random() > 0.7) {
        const heart = document.createElement('span');
        heart.className = 'heart';
        heart.innerHTML = ['💕', '💗', '💝', '❤️', '🌸'][Math.floor(Math.random() * 5)];
        heart.style.left = Math.random() * window.innerWidth + 'px';
        heart.style.top = window.innerHeight + 'px';
        document.getElementById('hearts').appendChild(heart);
        setTimeout(() => heart.remove(), 4000);
    }
}, 1000);
</script>

</body>
</html>
