<?php
/**
 * 🎯 Quiz Tính Cách - Tìm Loại Hoa Phù Hợp
 * Trắc nghiệm vui để gợi ý hoa theo tính cách
 */

@include '../config.php';

$user_id = $_SESSION['user_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Tính Cách Hoa - Flower Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .quiz-section {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
        }
        
        .quiz-container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .quiz-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .quiz-header h1 {
            font-size: 2.5rem;
            color: #2d3436;
            margin-bottom: 0.5rem;
        }
        
        .quiz-header p {
            color: #636e72;
            font-size: 1.2rem;
        }
        
        .progress-bar {
            width: 100%;
            height: 10px;
            background: #e0e0e0;
            border-radius: 10px;
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        
        .question-container {
            display: none;
        }
        
        .question-container.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .question-number {
            font-size: 1rem;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .question-text {
            font-size: 1.6rem;
            color: #2d3436;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        
        .answers {
            display: grid;
            gap: 1rem;
        }
        
        .answer-btn {
            padding: 1.2rem 1.5rem;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .answer-btn:hover {
            border-color: #667eea;
            background: #f0f0ff;
            transform: translateX(10px);
        }
        
        .answer-btn .emoji {
            font-size: 1.8rem;
        }
        
        /* Result */
        .result-container {
            display: none;
            text-align: center;
        }
        
        .result-container.active {
            display: block;
            animation: fadeIn 0.8s ease;
        }
        
        .result-flower {
            font-size: 8rem;
            margin: 1rem 0;
            animation: bounce 1s ease infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        
        .result-title {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .result-description {
            font-size: 1.2rem;
            color: #636e72;
            line-height: 1.8;
            margin-bottom: 2rem;
        }
        
        .result-traits {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.8rem;
            margin-bottom: 2rem;
        }
        
        .trait-tag {
            padding: 0.5rem 1.2rem;
            background: linear-gradient(135deg, #667eea20, #764ba220);
            color: #667eea;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .result-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .result-btn {
            padding: 1rem 2rem;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-primary:hover, .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        /* Share buttons */
        .share-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px dashed #e0e0e0;
        }
        
        .share-section h4 {
            color: #636e72;
            margin-bottom: 1rem;
        }
        
        .share-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .share-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            transition: all 0.3s;
        }
        
        .share-btn.facebook { background: #1877f2; }
        .share-btn.twitter { background: #1da1f2; }
        .share-btn.copy { background: #667eea; }
        
        .share-btn:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>

<?php @include '../header.php'; ?>

<section class="quiz-section">
    <div class="quiz-container">
        <div class="quiz-header">
            <h1>🌸 Quiz Tính Cách Hoa</h1>
            <p>Khám phá loại hoa đại diện cho bạn!</p>
        </div>
        
        <div class="progress-bar">
            <div class="progress-fill" id="progress" style="width: 0%"></div>
        </div>
        
        <!-- Questions -->
        <div id="questions-wrapper">
            <!-- Q1 -->
            <div class="question-container active" data-question="1">
                <div class="question-number">Câu 1/7</div>
                <div class="question-text">Cuối tuần lý tưởng của bạn là gì?</div>
                <div class="answers">
                    <button class="answer-btn" data-value="A">
                        <span class="emoji">🏠</span>
                        <span>Ở nhà đọc sách, xem phim</span>
                    </button>
                    <button class="answer-btn" data-value="B">
                        <span class="emoji">🎉</span>
                        <span>Đi chơi với bạn bè, party</span>
                    </button>
                    <button class="answer-btn" data-value="C">
                        <span class="emoji">🌿</span>
                        <span>Dã ngoại, gần gũi thiên nhiên</span>
                    </button>
                    <button class="answer-btn" data-value="D">
                        <span class="emoji">🎨</span>
                        <span>Làm việc sáng tạo, học điều mới</span>
                    </button>
                </div>
            </div>
            
            <!-- Q2 -->
            <div class="question-container" data-question="2">
                <div class="question-number">Câu 2/7</div>
                <div class="question-text">Màu sắc nào bạn thích nhất?</div>
                <div class="answers">
                    <button class="answer-btn" data-value="A">
                        <span class="emoji">❤️</span>
                        <span>Đỏ, hồng - nồng nhiệt</span>
                    </button>
                    <button class="answer-btn" data-value="B">
                        <span class="emoji">💛</span>
                        <span>Vàng, cam - vui tươi</span>
                    </button>
                    <button class="answer-btn" data-value="C">
                        <span class="emoji">💜</span>
                        <span>Tím, xanh - bí ẩn</span>
                    </button>
                    <button class="answer-btn" data-value="D">
                        <span class="emoji">🤍</span>
                        <span>Trắng, pastel - thanh nhã</span>
                    </button>
                </div>
            </div>
            
            <!-- Q3 -->
            <div class="question-container" data-question="3">
                <div class="question-number">Câu 3/7</div>
                <div class="question-text">Trong nhóm bạn, bạn thường là?</div>
                <div class="answers">
                    <button class="answer-btn" data-value="A">
                        <span class="emoji">👑</span>
                        <span>Người dẫn dắt, quyết định</span>
                    </button>
                    <button class="answer-btn" data-value="B">
                        <span class="emoji">🎭</span>
                        <span>Người vui vẻ, kể chuyện hài</span>
                    </button>
                    <button class="answer-btn" data-value="C">
                        <span class="emoji">👂</span>
                        <span>Người lắng nghe, tâm sự</span>
                    </button>
                    <button class="answer-btn" data-value="D">
                        <span class="emoji">💡</span>
                        <span>Người đưa ra ý tưởng độc đáo</span>
                    </button>
                </div>
            </div>
            
            <!-- Q4 -->
            <div class="question-container" data-question="4">
                <div class="question-number">Câu 4/7</div>
                <div class="question-text">Khi gặp khó khăn, bạn thường?</div>
                <div class="answers">
                    <button class="answer-btn" data-value="A">
                        <span class="emoji">💪</span>
                        <span>Đối mặt trực tiếp, không lùi bước</span>
                    </button>
                    <button class="answer-btn" data-value="B">
                        <span class="emoji">🗣️</span>
                        <span>Tìm người để chia sẻ, xin lời khuyên</span>
                    </button>
                    <button class="answer-btn" data-value="C">
                        <span class="emoji">🧘</span>
                        <span>Bình tĩnh suy nghĩ, từ từ giải quyết</span>
                    </button>
                    <button class="answer-btn" data-value="D">
                        <span class="emoji">🔄</span>
                        <span>Tìm cách tiếp cận mới, sáng tạo</span>
                    </button>
                </div>
            </div>
            
            <!-- Q5 -->
            <div class="question-container" data-question="5">
                <div class="question-number">Câu 5/7</div>
                <div class="question-text">Món quà nào bạn muốn nhận nhất?</div>
                <div class="answers">
                    <button class="answer-btn" data-value="A">
                        <span class="emoji">💎</span>
                        <span>Đồ trang sức, phụ kiện đẹp</span>
                    </button>
                    <button class="answer-btn" data-value="B">
                        <span class="emoji">🎫</span>
                        <span>Vé concert, du lịch trải nghiệm</span>
                    </button>
                    <button class="answer-btn" data-value="C">
                        <span class="emoji">📚</span>
                        <span>Sách, đồ handmade ý nghĩa</span>
                    </button>
                    <button class="answer-btn" data-value="D">
                        <span class="emoji">🎁</span>
                        <span>Bất ngờ, miễn có tâm là được</span>
                    </button>
                </div>
            </div>
            
            <!-- Q6 -->
            <div class="question-container" data-question="6">
                <div class="question-number">Câu 6/7</div>
                <div class="question-text">Mùa nào bạn yêu thích nhất?</div>
                <div class="answers">
                    <button class="answer-btn" data-value="A">
                        <span class="emoji">🌸</span>
                        <span>Xuân - tươi mới, tràn đầy sức sống</span>
                    </button>
                    <button class="answer-btn" data-value="B">
                        <span class="emoji">☀️</span>
                        <span>Hạ - nóng bỏng, năng động</span>
                    </button>
                    <button class="answer-btn" data-value="C">
                        <span class="emoji">🍂</span>
                        <span>Thu - lãng mạn, trầm lắng</span>
                    </button>
                    <button class="answer-btn" data-value="D">
                        <span class="emoji">❄️</span>
                        <span>Đông - ấm áp, sum vầy</span>
                    </button>
                </div>
            </div>
            
            <!-- Q7 -->
            <div class="question-container" data-question="7">
                <div class="question-number">Câu 7/7</div>
                <div class="question-text">Điều gì quan trọng nhất với bạn?</div>
                <div class="answers">
                    <button class="answer-btn" data-value="A">
                        <span class="emoji">❤️</span>
                        <span>Tình yêu và các mối quan hệ</span>
                    </button>
                    <button class="answer-btn" data-value="B">
                        <span class="emoji">🎯</span>
                        <span>Sự nghiệp và thành công</span>
                    </button>
                    <button class="answer-btn" data-value="C">
                        <span class="emoji">🌈</span>
                        <span>Tự do và trải nghiệm</span>
                    </button>
                    <button class="answer-btn" data-value="D">
                        <span class="emoji">🏠</span>
                        <span>Gia đình và sự bình yên</span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Result -->
        <div class="result-container" id="result">
            <div class="result-flower" id="result-emoji">🌹</div>
            <h2 class="result-title" id="result-name">Bạn là Hoa Hồng!</h2>
            <p class="result-description" id="result-desc">
                Mô tả tính cách...
            </p>
            <div class="result-traits" id="result-traits">
                <!-- Traits tags -->
            </div>
            <div class="result-actions">
                <a href="../pages/shop.php" class="result-btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Mua hoa này
                </a>
                <button onclick="restartQuiz()" class="result-btn btn-secondary">
                    <i class="fas fa-redo"></i> Làm lại
                </button>
            </div>
            
            <div class="share-section">
                <h4>Chia sẻ kết quả:</h4>
                <div class="share-buttons">
                    <a href="#" class="share-btn facebook" onclick="shareResult('facebook')">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="share-btn twitter" onclick="shareResult('twitter')">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <button class="share-btn copy" onclick="copyResult()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php @include '../footer.php'; ?>

<script>
// Quiz Data
const flowerResults = {
    rose: {
        emoji: '🌹',
        name: 'Hoa Hồng - Người Lãng Mạn',
        desc: 'Bạn là người đam mê, nồng nhiệt và luôn theo đuổi tình yêu đích thực. Bạn có trái tim ấm áp, biết yêu thương và được mọi người yêu mến. Sự quyến rũ tự nhiên của bạn khiến người khác bị thu hút.',
        traits: ['Lãng mạn', 'Đam mê', 'Quyến rũ', 'Trung thành', 'Yêu thương'],
        color: '#e74c3c'
    },
    sunflower: {
        emoji: '🌻',
        name: 'Hoa Hướng Dương - Người Lạc Quan',
        desc: 'Bạn như ánh nắng mặt trời, luôn mang năng lượng tích cực đến mọi người xung quanh. Dù trong hoàn cảnh nào, bạn vẫn giữ được sự vui vẻ và lạc quan. Bạn là nguồn cảm hứng cho nhiều người!',
        traits: ['Lạc quan', 'Vui vẻ', 'Năng động', 'Truyền cảm hứng', 'Ấm áp'],
        color: '#f39c12'
    },
    lily: {
        emoji: '🌺',
        name: 'Hoa Lily - Người Thanh Lịch',
        desc: 'Bạn toát lên vẻ đẹp thanh cao, quý phái. Sự tinh tế trong cách cư xử và gu thẩm mỹ khiến bạn nổi bật. Bạn có tâm hồn thuần khiết và luôn hướng đến sự hoàn hảo.',
        traits: ['Thanh lịch', 'Tinh tế', 'Thuần khiết', 'Cao quý', 'Hoàn hảo'],
        color: '#9b59b6'
    },
    orchid: {
        emoji: '🦋',
        name: 'Hoa Lan - Người Bí Ẩn',
        desc: 'Bạn có sức hút khó cưỡng từ sự bí ẩn và độc đáo. Không ai có thể đoán được suy nghĩ của bạn, và đó chính là điều làm bạn thú vị. Bạn có gu thẩm mỹ cao cấp và cá tính riêng biệt.',
        traits: ['Bí ẩn', 'Độc đáo', 'Sang trọng', 'Cá tính', 'Quyến rũ'],
        color: '#8e44ad'
    },
    daisy: {
        emoji: '🌼',
        name: 'Hoa Cúc - Người Chân Thành',
        desc: 'Bạn giản dị, chân thành và đáng tin cậy. Mọi người yêu quý bạn vì sự thật thà và tình bạn bền vững. Bạn tìm thấy niềm vui trong những điều nhỏ bé và biết trân trọng cuộc sống.',
        traits: ['Chân thành', 'Giản dị', 'Đáng tin', 'Vui vẻ', 'Trung thực'],
        color: '#f1c40f'
    },
    tulip: {
        emoji: '🌷',
        name: 'Hoa Tulip - Người Mơ Mộng',
        desc: 'Bạn có tâm hồn nghệ sĩ, mơ mộng và sáng tạo. Thế giới nội tâm của bạn phong phú với những ý tưởng độc đáo. Bạn nhạy cảm với cái đẹp và luôn tìm kiếm sự hoàn mỹ.',
        traits: ['Mơ mộng', 'Sáng tạo', 'Nghệ sĩ', 'Nhạy cảm', 'Lãng mạn'],
        color: '#e91e63'
    }
};

let currentQuestion = 1;
let answers = [];
const totalQuestions = 7;

// Handle answer selection
document.querySelectorAll('.answer-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const value = this.dataset.value;
        answers.push(value);
        
        if (currentQuestion < totalQuestions) {
            nextQuestion();
        } else {
            showResult();
        }
    });
});

function nextQuestion() {
    // Hide current
    document.querySelector(`.question-container[data-question="${currentQuestion}"]`).classList.remove('active');
    
    currentQuestion++;
    
    // Show next
    document.querySelector(`.question-container[data-question="${currentQuestion}"]`).classList.add('active');
    
    // Update progress
    const progress = (currentQuestion - 1) / totalQuestions * 100;
    document.getElementById('progress').style.width = progress + '%';
}

function calculateResult() {
    // Count answer types
    const counts = { A: 0, B: 0, C: 0, D: 0 };
    answers.forEach(a => counts[a]++);
    
    // Determine flower based on answers
    const maxCount = Math.max(...Object.values(counts));
    const dominantTypes = Object.keys(counts).filter(k => counts[k] === maxCount);
    const dominant = dominantTypes[0];
    
    // Map to flower
    const flowerMap = {
        'A': ['rose', 'lily'],
        'B': ['sunflower', 'tulip'],
        'C': ['orchid', 'daisy'],
        'D': ['daisy', 'lily']
    };
    
    // More specific logic
    if (counts.A >= 3) return 'rose';
    if (counts.B >= 3) return 'sunflower';
    if (counts.C >= 3 && counts.D >= 2) return 'orchid';
    if (counts.D >= 3) return 'daisy';
    if (counts.A >= 2 && counts.D >= 2) return 'lily';
    if (counts.B >= 2 && counts.C >= 2) return 'tulip';
    
    // Default based on dominant
    const options = flowerMap[dominant];
    return options[Math.floor(Math.random() * options.length)];
}

function showResult() {
    const flowerKey = calculateResult();
    const flower = flowerResults[flowerKey];
    
    // Update progress to 100%
    document.getElementById('progress').style.width = '100%';
    
    // Hide questions
    document.getElementById('questions-wrapper').style.display = 'none';
    
    // Show result
    const resultEl = document.getElementById('result');
    resultEl.classList.add('active');
    
    document.getElementById('result-emoji').textContent = flower.emoji;
    document.getElementById('result-name').textContent = flower.name;
    document.getElementById('result-name').style.color = flower.color;
    document.getElementById('result-desc').textContent = flower.desc;
    
    // Add traits
    const traitsEl = document.getElementById('result-traits');
    traitsEl.innerHTML = flower.traits.map(t => 
        `<span class="trait-tag" style="border: 2px solid ${flower.color}; color: ${flower.color}">${t}</span>`
    ).join('');
}

function restartQuiz() {
    currentQuestion = 1;
    answers = [];
    
    // Reset progress
    document.getElementById('progress').style.width = '0%';
    
    // Hide result
    document.getElementById('result').classList.remove('active');
    
    // Show questions
    document.getElementById('questions-wrapper').style.display = 'block';
    
    // Reset to first question
    document.querySelectorAll('.question-container').forEach(q => q.classList.remove('active'));
    document.querySelector('.question-container[data-question="1"]').classList.add('active');
}

function shareResult(platform) {
    const text = `Tôi là ${document.getElementById('result-name').textContent}! Bạn là loại hoa gì? Làm quiz tại:`;
    const url = window.location.href;
    
    if (platform === 'facebook') {
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(text)}`, '_blank');
    } else if (platform === 'twitter') {
        window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`, '_blank');
    }
}

function copyResult() {
    const text = `${document.getElementById('result-name').textContent}\n${document.getElementById('result-desc').textContent}\n\nLàm quiz tại: ${window.location.href}`;
    navigator.clipboard.writeText(text).then(() => {
        alert('Đã copy kết quả!');
    });
}
</script>

</body>
</html>
