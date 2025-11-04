<?php
/**
 * 📖 Ngôn Ngữ Hoa - Ý Nghĩa Các Loại Hoa
 * Tra cứu ý nghĩa hoa, gợi ý theo thông điệp
 */

@include '../config.php';

// Database ý nghĩa hoa
$flower_meanings = [
    [
        'id' => 1,
        'name' => 'Hoa Hồng Đỏ',
        'emoji' => '🌹',
        'meaning' => 'Tình yêu nồng cháy, đam mê',
        'occasions' => ['Valentine', 'Tỏ tình', 'Kỷ niệm ngày cưới'],
        'message' => 'Anh/Em yêu em/anh',
        'color' => '#e74c3c',
        'detail' => 'Hoa hồng đỏ là biểu tượng vĩnh cửu của tình yêu đam mê. Từ thời La Mã cổ đại, hoa hồng đỏ đã được dùng để bày tỏ tình cảm sâu đậm. Số lượng hoa cũng mang ý nghĩa: 1 bông - tình yêu duy nhất, 12 bông - yêu em mỗi tháng trong năm, 99 bông - yêu em mãi mãi.'
    ],
    [
        'id' => 2,
        'name' => 'Hoa Hồng Hồng',
        'emoji' => '🌸',
        'meaning' => 'Ngưỡng mộ, biết ơn, tình yêu ngọt ngào',
        'occasions' => ['Ngày của Mẹ', 'Cảm ơn', 'Tình bạn'],
        'message' => 'Cảm ơn bạn đã ở bên tôi',
        'color' => '#fd79a8',
        'detail' => 'Hoa hồng hồng tượng trưng cho sự ngưỡng mộ, lòng biết ơn và tình cảm dịu dàng. Phù hợp để tặng mẹ, chị em gái hoặc bạn thân. Màu hồng nhạt thể hiện sự dịu dàng, hồng đậm thể hiện lòng biết ơn sâu sắc.'
    ],
    [
        'id' => 3,
        'name' => 'Hoa Hồng Trắng',
        'emoji' => '🤍',
        'meaning' => 'Thuần khiết, ngây thơ, tình yêu chân thành',
        'occasions' => ['Đám cưới', 'Tang lễ', 'Rửa tội'],
        'message' => 'Tình yêu trong sáng của anh/em',
        'color' => '#ecf0f1',
        'detail' => 'Hoa hồng trắng biểu tượng cho sự thuần khiết, trong trắng và tình yêu chân thành. Thường dùng trong đám cưới như lời hứa về tình yêu vĩnh cửu. Cũng được dùng để tưởng nhớ người đã khuất với ý nghĩa sự thanh thản.'
    ],
    [
        'id' => 4,
        'name' => 'Hoa Hồng Vàng',
        'emoji' => '💛',
        'meaning' => 'Tình bạn, niềm vui, sự quan tâm',
        'occasions' => ['Chúc mừng', 'Thăm bệnh', 'Tình bạn'],
        'message' => 'Bạn là người bạn tuyệt vời',
        'color' => '#f1c40f',
        'detail' => 'Hoa hồng vàng là biểu tượng của tình bạn chân thành và niềm vui. Không nên tặng người yêu vì có thể bị hiểu lầm là chia tay. Phù hợp để chúc mừng thành công, thăm người ốm hoặc cảm ơn bạn bè.'
    ],
    [
        'id' => 5,
        'name' => 'Hoa Hướng Dương',
        'emoji' => '🌻',
        'meaning' => 'Lòng trung thành, ngưỡng mộ, hạnh phúc',
        'occasions' => ['Khai trương', 'Chúc mừng', 'Động viên'],
        'message' => 'Chúc bạn luôn tỏa sáng',
        'color' => '#f39c12',
        'detail' => 'Hoa hướng dương luôn hướng về phía mặt trời, tượng trưng cho sự lạc quan, trung thành và nguồn năng lượng tích cực. Phù hợp để tặng người mới khởi nghiệp, động viên ai đó vượt qua khó khăn hoặc đơn giản là mang lại niềm vui.'
    ],
    [
        'id' => 6,
        'name' => 'Hoa Tulip',
        'emoji' => '🌷',
        'meaning' => 'Tình yêu hoàn hảo, danh vọng',
        'occasions' => ['Tỏ tình', 'Kỷ niệm', 'Chúc mừng'],
        'message' => 'Em là tình yêu hoàn hảo của anh',
        'color' => '#e91e63',
        'detail' => 'Hoa Tulip có nguồn gốc từ Thổ Nhĩ Kỳ, tượng trưng cho tình yêu hoàn hảo. Tulip đỏ - tuyên bố tình yêu, Tulip vàng - nụ cười trong tình yêu, Tulip tím - hoàng tộc, Tulip trắng - xin lỗi. Đây là loài hoa được yêu thích ở Hà Lan.'
    ],
    [
        'id' => 7,
        'name' => 'Hoa Lily',
        'emoji' => '🌺',
        'meaning' => 'Sự thuần khiết, cao quý, may mắn',
        'occasions' => ['Đám cưới', 'Sinh nhật', 'Tết'],
        'message' => 'Chúc bạn may mắn và hạnh phúc',
        'color' => '#fff',
        'detail' => 'Hoa Lily (Huệ Tây) tượng trưng cho sự thuần khiết và cao quý. Trong văn hóa Á Đông, hoa lily trắng còn mang ý nghĩa 100 năm hạnh phúc. Lily hồng thể hiện sự ngưỡng mộ, Lily cam thể hiện niềm đam mê.'
    ],
    [
        'id' => 8,
        'name' => 'Hoa Lan Hồ Điệp',
        'emoji' => '🦋',
        'meaning' => 'Sang trọng, quyền quý, tình yêu thuần khiết',
        'occasions' => ['Khai trương', 'Tết', 'Quà biếu'],
        'message' => 'Chúc làm ăn phát đạt',
        'color' => '#9b59b6',
        'detail' => 'Hoa Lan Hồ Điệp là loài hoa sang trọng nhất, tượng trưng cho sự giàu sang, thịnh vượng. Lan trắng - thuần khiết, Lan tím - hoàng gia, Lan hồng - nữ tính. Rất phù hợp để biếu sếp, đối tác kinh doanh hoặc trang trí nhà cửa ngày Tết.'
    ],
    [
        'id' => 9,
        'name' => 'Hoa Cúc',
        'emoji' => '🌼',
        'meaning' => 'Sự trường thọ, niềm vui, sự trung thành',
        'occasions' => ['Mừng thọ', 'Tang lễ', 'Trang trí'],
        'message' => 'Chúc sống lâu trăm tuổi',
        'color' => '#f1c40f',
        'detail' => 'Hoa Cúc là biểu tượng của mùa thu và sự trường thọ. Cúc vàng - trường thọ, thịnh vượng. Cúc trắng - tưởng nhớ, chia buồn. Cúc đỏ - tình yêu. Trong văn hóa Việt, hoa cúc thường dùng trong ngày giỗ, Tết và mừng thọ.'
    ],
    [
        'id' => 10,
        'name' => 'Hoa Cẩm Chướng',
        'emoji' => '💮',
        'meaning' => 'Tình yêu của mẹ, sự ngưỡng mộ',
        'occasions' => ['Ngày của Mẹ', 'Cảm ơn', 'Tang lễ'],
        'message' => 'Con yêu mẹ',
        'color' => '#e91e63',
        'detail' => 'Hoa Cẩm Chướng là biểu tượng của tình mẫu tử. Cẩm chướng hồng - cảm ơn mẹ, Cẩm chướng đỏ - ngưỡng mộ, Cẩm chướng trắng - tình yêu thuần khiết, tưởng nhớ. Đây là loài hoa chính thức của Ngày Của Mẹ trên toàn thế giới.'
    ],
    [
        'id' => 11,
        'name' => 'Hoa Baby',
        'emoji' => '🤍',
        'meaning' => 'Sự ngây thơ, thuần khiết, vĩnh cửu',
        'occasions' => ['Đám cưới', 'Sinh em bé', 'Rửa tội'],
        'message' => 'Tình yêu vĩnh cửu',
        'color' => '#ecf0f1',
        'detail' => 'Hoa Baby (Gypsophila) tượng trưng cho sự ngây thơ và vĩnh cửu. Thường được kết hợp với các loại hoa khác để tạo bó hoa đầy đặn. Hoa Baby phơi khô vẫn giữ được vẻ đẹp, tượng trưng cho tình yêu bền lâu.'
    ],
    [
        'id' => 12,
        'name' => 'Hoa Cát Tường',
        'emoji' => '💜',
        'meaning' => 'Lòng biết ơn, sự thanh lịch, bình an',
        'occasions' => ['Cảm ơn', 'Chúc bình an', 'Trang trí'],
        'message' => 'Cảm ơn bạn rất nhiều',
        'color' => '#a29bfe',
        'detail' => 'Hoa Cát Tường (Eustoma) có vẻ đẹp thanh tao, tượng trưng cho lòng biết ơn và sự bình an. Cát tường tím - lịch lãm, Cát tường trắng - thuần khiết, Cát tường hồng - tình cảm dịu dàng. Phù hợp cho nhiều dịp khác nhau.'
    ]
];

// Lọc theo occasion nếu có
$filter_occasion = $_GET['occasion'] ?? '';
$search = $_GET['search'] ?? '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ngôn Ngữ Hoa - Ý Nghĩa Các Loại Hoa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .flower-lang-section {
            padding: 3rem 2rem;
            background: linear-gradient(180deg, #ffecd2 0%, #fcb69f 30%, #fff 100%);
            min-height: 100vh;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-header h1 {
            font-size: 3rem;
            color: #2d3436;
            margin-bottom: 1rem;
        }
        
        .section-header p {
            font-size: 1.3rem;
            color: #636e72;
        }
        
        /* Search & Filter */
        .search-filter {
            max-width: 800px;
            margin: 0 auto 3rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 1rem 1.5rem 1rem 3.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .search-box i {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #b2bec3;
        }
        
        .filter-tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .filter-tag {
            padding: 0.7rem 1.2rem;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }
        
        .filter-tag:hover, .filter-tag.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        /* Flower Cards */
        .flowers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .flower-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .flower-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .flower-card-header {
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .flower-emoji {
            font-size: 5rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .flower-name {
            font-size: 1.5rem;
            color: #2d3436;
            margin-bottom: 0.5rem;
        }
        
        .flower-meaning {
            font-size: 1.2rem;
            color: #667eea;
            font-weight: 600;
        }
        
        .flower-card-body {
            padding: 0 2rem 2rem;
        }
        
        .flower-message {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-style: italic;
            color: #636e72;
            text-align: center;
        }
        
        .flower-message::before {
            content: '"';
            font-size: 1.5rem;
            color: #667eea;
        }
        
        .flower-message::after {
            content: '"';
            font-size: 1.5rem;
            color: #667eea;
        }
        
        .flower-occasions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .occasion-tag {
            padding: 0.4rem 0.8rem;
            background: linear-gradient(135deg, #667eea20, #764ba220);
            color: #667eea;
            border-radius: 15px;
            font-size: 0.9rem;
        }
        
        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 2rem;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 25px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalIn 0.3s ease;
        }
        
        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .modal-header {
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            border: none;
            background: #f8f9fa;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .modal-close:hover {
            background: #e74c3c;
            color: white;
        }
        
        .modal-emoji {
            font-size: 6rem;
        }
        
        .modal-name {
            font-size: 2rem;
            color: #2d3436;
            margin: 1rem 0 0.5rem;
        }
        
        .modal-meaning {
            font-size: 1.3rem;
            color: #667eea;
            font-weight: 600;
        }
        
        .modal-body {
            padding: 0 2rem 2rem;
        }
        
        .modal-detail {
            line-height: 1.8;
            color: #636e72;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .modal-section {
            margin-bottom: 1.5rem;
        }
        
        .modal-section h4 {
            color: #2d3436;
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .modal-occasions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .modal-message {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            font-size: 1.3rem;
            font-style: italic;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .modal-btn {
            padding: 1rem 2rem;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-buy {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-share {
            background: #f8f9fa;
            color: #667eea;
        }
        
        .modal-btn:hover {
            transform: translateY(-2px);
        }
        
        /* Find by Message */
        .find-by-message {
            background: white;
            padding: 3rem;
            border-radius: 25px;
            max-width: 800px;
            margin: 0 auto 3rem;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        .find-by-message h2 {
            color: #2d3436;
            margin-bottom: 1rem;
        }
        
        .find-by-message p {
            color: #636e72;
            margin-bottom: 2rem;
        }
        
        .message-options {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }
        
        .message-btn {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1.1rem;
        }
        
        .message-btn:hover {
            border-color: #667eea;
            background: #667eea10;
        }
        
        @media (max-width: 768px) {
            .flowers-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<?php @include '../header.php'; ?>

<section class="heading">
    <h3>📖 Ngôn Ngữ Hoa</h3>
    <p><a href="./home.php">Trang chủ</a> / Ngôn Ngữ Hoa</p>
</section>

<section class="flower-lang-section">
    <div class="section-header">
        <h1>📖 Ngôn Ngữ Hoa</h1>
        <p>Mỗi loài hoa mang một thông điệp riêng. Hãy chọn đúng hoa để nói lên lòng mình!</p>
    </div>
    
    <!-- Find by Message -->
    <div class="find-by-message">
        <h2>💌 Bạn muốn nói gì?</h2>
        <p>Chọn thông điệp và chúng tôi sẽ gợi ý loại hoa phù hợp</p>
        <div class="message-options">
            <button class="message-btn" onclick="filterByMessage('yêu')">❤️ Tôi yêu bạn</button>
            <button class="message-btn" onclick="filterByMessage('cảm ơn')">🙏 Cảm ơn bạn</button>
            <button class="message-btn" onclick="filterByMessage('xin lỗi')">😔 Xin lỗi</button>
            <button class="message-btn" onclick="filterByMessage('chúc mừng')">🎉 Chúc mừng</button>
            <button class="message-btn" onclick="filterByMessage('nhớ')">💭 Tôi nhớ bạn</button>
            <button class="message-btn" onclick="filterByMessage('động viên')">💪 Động viên</button>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="search-filter">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="search-input" placeholder="Tìm kiếm theo tên hoa, ý nghĩa..." onkeyup="filterFlowers()">
        </div>
    </div>
    
    <div class="filter-tags">
        <button class="filter-tag active" onclick="filterByOccasion('')">Tất cả</button>
        <button class="filter-tag" onclick="filterByOccasion('Valentine')">💕 Valentine</button>
        <button class="filter-tag" onclick="filterByOccasion('Ngày của Mẹ')">👩 Ngày của Mẹ</button>
        <button class="filter-tag" onclick="filterByOccasion('Đám cưới')">💒 Đám cưới</button>
        <button class="filter-tag" onclick="filterByOccasion('Sinh nhật')">🎂 Sinh nhật</button>
        <button class="filter-tag" onclick="filterByOccasion('Khai trương')">🏪 Khai trương</button>
        <button class="filter-tag" onclick="filterByOccasion('Tang lễ')">🕯️ Chia buồn</button>
    </div>
    
    <!-- Flowers Grid -->
    <div class="flowers-grid" id="flowers-grid">
        <?php foreach($flower_meanings as $flower): ?>
        <div class="flower-card" 
             data-name="<?php echo strtolower($flower['name']); ?>"
             data-meaning="<?php echo strtolower($flower['meaning']); ?>"
             data-occasions="<?php echo strtolower(implode(',', $flower['occasions'])); ?>"
             data-message="<?php echo strtolower($flower['message']); ?>"
             onclick="showFlowerDetail(<?php echo htmlspecialchars(json_encode($flower)); ?>)">
            <div class="flower-card-header" style="background: linear-gradient(180deg, <?php echo $flower['color']; ?>20 0%, white 100%);">
                <span class="flower-emoji"><?php echo $flower['emoji']; ?></span>
                <h3 class="flower-name"><?php echo $flower['name']; ?></h3>
                <p class="flower-meaning"><?php echo $flower['meaning']; ?></p>
            </div>
            <div class="flower-card-body">
                <div class="flower-message"><?php echo $flower['message']; ?></div>
                <div class="flower-occasions">
                    <?php foreach($flower['occasions'] as $occasion): ?>
                    <span class="occasion-tag"><?php echo $occasion; ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Modal -->
<div class="modal-overlay" id="modal" onclick="closeModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header" id="modal-header">
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
            <span class="modal-emoji" id="modal-emoji">🌹</span>
            <h2 class="modal-name" id="modal-name">Hoa Hồng</h2>
            <p class="modal-meaning" id="modal-meaning">Tình yêu</p>
        </div>
        <div class="modal-body">
            <p class="modal-detail" id="modal-detail">Chi tiết...</p>
            
            <div class="modal-section">
                <h4><i class="fas fa-calendar-alt"></i> Phù hợp cho:</h4>
                <div class="modal-occasions" id="modal-occasions"></div>
            </div>
            
            <div class="modal-section">
                <h4><i class="fas fa-comment-alt"></i> Thông điệp:</h4>
                <div class="modal-message" id="modal-message">"Anh yêu em"</div>
            </div>
            
            <div class="modal-actions">
                <a href="../pages/shop.php" class="modal-btn btn-buy">
                    <i class="fas fa-shopping-cart"></i> Mua hoa này
                </a>
                <button class="modal-btn btn-share" onclick="shareFlower()">
                    <i class="fas fa-share-alt"></i> Chia sẻ
                </button>
            </div>
        </div>
    </div>
</div>

<?php @include '../footer.php'; ?>

<script>
let currentFlower = null;

function showFlowerDetail(flower) {
    currentFlower = flower;
    
    document.getElementById('modal-header').style.background = `linear-gradient(180deg, ${flower.color}30 0%, white 100%)`;
    document.getElementById('modal-emoji').textContent = flower.emoji;
    document.getElementById('modal-name').textContent = flower.name;
    document.getElementById('modal-meaning').textContent = flower.meaning;
    document.getElementById('modal-detail').textContent = flower.detail;
    document.getElementById('modal-message').textContent = `"${flower.message}"`;
    
    const occasionsHtml = flower.occasions.map(o => 
        `<span class="occasion-tag">${o}</span>`
    ).join('');
    document.getElementById('modal-occasions').innerHTML = occasionsHtml;
    
    document.getElementById('modal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('modal').classList.remove('active');
    document.body.style.overflow = '';
}

function filterFlowers() {
    const search = document.getElementById('search-input').value.toLowerCase();
    const cards = document.querySelectorAll('.flower-card');
    
    cards.forEach(card => {
        const name = card.dataset.name;
        const meaning = card.dataset.meaning;
        const message = card.dataset.message;
        
        if (name.includes(search) || meaning.includes(search) || message.includes(search)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function filterByOccasion(occasion) {
    // Update active tag
    document.querySelectorAll('.filter-tag').forEach(tag => tag.classList.remove('active'));
    event.target.classList.add('active');
    
    const cards = document.querySelectorAll('.flower-card');
    
    cards.forEach(card => {
        if (!occasion || card.dataset.occasions.includes(occasion.toLowerCase())) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function filterByMessage(keyword) {
    document.getElementById('search-input').value = keyword;
    filterFlowers();
    
    // Scroll to grid
    document.getElementById('flowers-grid').scrollIntoView({ behavior: 'smooth' });
}

function shareFlower() {
    if (currentFlower) {
        const text = `${currentFlower.emoji} ${currentFlower.name}: ${currentFlower.meaning}\n"${currentFlower.message}"`;
        navigator.clipboard.writeText(text).then(() => {
            alert('Đã copy nội dung!');
        });
    }
}

// Close modal with Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});
</script>

</body>
</html>
