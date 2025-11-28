<?php
/**
 * 🎮 Mini Games - Trò Chơi Hoa
 * Memory Match + Xếp Hoa
 */

@include '../config.php';

$user_id = $_SESSION['user_id'] ?? null;

// Tạo bảng điểm game
if($user_id){
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'game_scores'");
    if(mysqli_num_rows($check) == 0){
        mysqli_query($conn, "CREATE TABLE game_scores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            game_type VARCHAR(50) NOT NULL,
            score INT DEFAULT 0,
            best_score INT DEFAULT 0,
            played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }
}

// Lấy điểm cao nhất
$best_memory = 0;
$best_puzzle = 0;
if($user_id){
    $result = mysqli_query($conn, "SELECT game_type, MAX(best_score) as best FROM game_scores WHERE user_id = '$user_id' GROUP BY game_type");
    while($row = mysqli_fetch_assoc($result)){
        if($row['game_type'] == 'memory') $best_memory = $row['best'];
        if($row['game_type'] == 'puzzle') $best_puzzle = $row['best'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Games - Flower Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .games-section {
            padding: 3rem 2rem;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            min-height: 100vh;
        }
        
        .games-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        /* Game Selection */
        .game-selector {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .game-card {
            background: white;
            padding: 2rem;
            border-radius: 25px;
            width: 300px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 4px solid transparent;
        }
        
        .game-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .game-card.active {
            border-color: #667eea;
        }
        
        .game-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .game-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2d3436;
            margin-bottom: 0.5rem;
        }
        
        .game-desc {
            color: #636e72;
            font-size: 0.95rem;
        }
        
        .game-best {
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: #f8f9fa;
            border-radius: 20px;
            display: inline-block;
            font-size: 0.9rem;
        }
        
        /* Game Area */
        .game-area {
            background: white;
            border-radius: 25px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            display: none;
        }
        
        .game-area.active {
            display: block;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        
        /* Game Header */
        .game-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .game-stats {
            display: flex;
            gap: 2rem;
        }
        
        .stat-box {
            text-align: center;
            padding: 0.8rem 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #636e72;
        }
        
        .btn-restart {
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .btn-restart:hover {
            transform: scale(1.05);
        }
        
        /* Memory Game */
        .memory-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .memory-card {
            aspect-ratio: 1;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            transition: all 0.3s;
            position: relative;
            transform-style: preserve-3d;
        }
        
        .memory-card .card-front,
        .memory-card .card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
        }
        
        .memory-card .card-front {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-size: 2rem;
        }
        
        .memory-card .card-back {
            background: white;
            transform: rotateY(180deg);
            border: 3px solid #667eea;
        }
        
        .memory-card.flipped {
            transform: rotateY(180deg);
        }
        
        .memory-card.matched {
            animation: matchPulse 0.5s;
        }
        
        @keyframes matchPulse {
            0%, 100% { transform: rotateY(180deg) scale(1); }
            50% { transform: rotateY(180deg) scale(1.1); }
        }
        
        /* Puzzle Game */
        .puzzle-area {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .puzzle-target {
            text-align: center;
        }
        
        .puzzle-target h4 {
            margin-bottom: 1rem;
            color: #636e72;
        }
        
        .target-bouquet {
            display: grid;
            grid-template-columns: repeat(3, 60px);
            gap: 0.5rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
            border: 3px dashed #667eea;
        }
        
        .target-slot {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        
        .puzzle-workspace {
            text-align: center;
        }
        
        .puzzle-workspace h4 {
            margin-bottom: 1rem;
            color: #636e72;
        }
        
        .player-bouquet {
            display: grid;
            grid-template-columns: repeat(3, 60px);
            gap: 0.5rem;
            padding: 1.5rem;
            background: #fff;
            border-radius: 15px;
            border: 3px solid #667eea;
            min-height: 200px;
        }
        
        .player-slot {
            width: 60px;
            height: 60px;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .player-slot:hover {
            background: #fee2e2;
        }
        
        .flower-palette {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 1.5rem;
            max-width: 400px;
        }
        
        .palette-flower {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            cursor: grab;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        
        .palette-flower:hover {
            border-color: #667eea;
            transform: scale(1.1);
        }
        
        .palette-flower.selected {
            border-color: #667eea;
            background: #667eea20;
        }
        
        .btn-check {
            margin-top: 1.5rem;
            padding: 1rem 3rem;
            background: linear-gradient(135deg, #4ade80, #22c55e);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        /* Result Modal */
        .result-modal {
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
        
        .result-modal.active {
            display: flex;
        }
        
        .result-content {
            background: white;
            padding: 3rem;
            border-radius: 25px;
            text-align: center;
            max-width: 400px;
            animation: popIn 0.5s;
        }
        
        @keyframes popIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .result-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        
        .result-title {
            font-size: 2rem;
            color: #2d3436;
            margin-bottom: 0.5rem;
        }
        
        .result-score {
            font-size: 1.5rem;
            color: #667eea;
            margin-bottom: 1.5rem;
        }
        
        .result-btn {
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1.1rem;
            margin: 0.3rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .memory-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 0.5rem;
            }
            
            .memory-card {
                font-size: 2rem;
            }
            
            .game-stats {
                gap: 0.5rem;
            }
            
            .stat-box {
                padding: 0.5rem 1rem;
            }
        }
    </style>
</head>
<body>

<?php @include '../header.php'; ?>

<section class="heading">
    <h3>🎮 Mini Games</h3>
    <p><a href="../pages/home.php">Trang chủ</a> / Mini Games</p>
</section>

<section class="games-section">
    <div class="games-container">
        
        <!-- Game Selection -->
        <div class="game-selector">
            <div class="game-card active" onclick="selectGame('memory')">
                <div class="game-icon">🧠</div>
                <div class="game-name">Memory Match</div>
                <div class="game-desc">Lật và ghép đôi các cặp hoa giống nhau</div>
                <div class="game-best">🏆 Best: <?php echo $best_memory; ?> điểm</div>
            </div>
            
            <div class="game-card" onclick="selectGame('puzzle')">
                <div class="game-icon">💐</div>
                <div class="game-name">Xếp Hoa</div>
                <div class="game-desc">Sao chép đúng mẫu bó hoa được cho</div>
                <div class="game-best">🏆 Best: <?php echo $best_puzzle; ?> điểm</div>
            </div>
        </div>
        
        <!-- Memory Game -->
        <div class="game-area active" id="memory-game">
            <div class="game-header">
                <div class="game-stats">
                    <div class="stat-box">
                        <div class="stat-value" id="memory-moves">0</div>
                        <div class="stat-label">Lượt</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" id="memory-pairs">0/8</div>
                        <div class="stat-label">Cặp</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" id="memory-time">0:00</div>
                        <div class="stat-label">Thời gian</div>
                    </div>
                </div>
                <button class="btn-restart" onclick="startMemoryGame()">
                    <i class="fas fa-redo"></i> Chơi lại
                </button>
            </div>
            
            <div class="memory-grid" id="memory-grid">
                <!-- Cards will be generated by JS -->
            </div>
        </div>
        
        <!-- Puzzle Game -->
        <div class="game-area" id="puzzle-game">
            <div class="game-header">
                <div class="game-stats">
                    <div class="stat-box">
                        <div class="stat-value" id="puzzle-level">1</div>
                        <div class="stat-label">Level</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" id="puzzle-score">0</div>
                        <div class="stat-label">Điểm</div>
                    </div>
                </div>
                <button class="btn-restart" onclick="startPuzzleGame()">
                    <i class="fas fa-redo"></i> Chơi lại
                </button>
            </div>
            
            <div class="puzzle-area">
                <div class="puzzle-target">
                    <h4>📋 Mẫu cần xếp:</h4>
                    <div class="target-bouquet" id="target-bouquet">
                        <!-- Generated by JS -->
                    </div>
                </div>
                
                <div class="puzzle-workspace">
                    <h4>🎨 Bó hoa của bạn:</h4>
                    <div class="player-bouquet" id="player-bouquet">
                        <!-- Generated by JS -->
                    </div>
                    
                    <div class="flower-palette" id="flower-palette">
                        <!-- Generated by JS -->
                    </div>
                    
                    <button class="btn-check" onclick="checkPuzzle()">
                        ✅ Kiểm tra
                    </button>
                </div>
            </div>
        </div>
        
    </div>
</section>

<!-- Result Modal -->
<div class="result-modal" id="result-modal">
    <div class="result-content">
        <div class="result-icon" id="result-icon">🎉</div>
        <div class="result-title" id="result-title">Tuyệt vời!</div>
        <div class="result-score" id="result-score">100 điểm</div>
        <button class="result-btn" onclick="closeResult()">Đóng</button>
        <button class="result-btn" onclick="playAgain()">Chơi lại</button>
    </div>
</div>

<?php @include '../footer.php'; ?>

<script>
const FLOWERS = ['🌹', '🌻', '🌷', '🌸', '🌺', '💐', '🌼', '🪻'];
let currentGame = 'memory';

// ============ GAME SELECTION ============
function selectGame(game) {
    currentGame = game;
    document.querySelectorAll('.game-card').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.game-area').forEach(a => a.classList.remove('active'));
    
    document.querySelector(`.game-card:nth-child(${game === 'memory' ? 1 : 2})`).classList.add('active');
    document.getElementById(game + '-game').classList.add('active');
    
    if(game === 'memory') startMemoryGame();
    else startPuzzleGame();
}

// ============ MEMORY GAME ============
let memoryCards = [];
let flippedCards = [];
let matchedPairs = 0;
let memoryMoves = 0;
let memoryTimer;
let memorySeconds = 0;
let isLocked = false;

function startMemoryGame() {
    // Reset
    matchedPairs = 0;
    memoryMoves = 0;
    memorySeconds = 0;
    flippedCards = [];
    isLocked = false;
    
    clearInterval(memoryTimer);
    memoryTimer = setInterval(() => {
        memorySeconds++;
        document.getElementById('memory-time').textContent = formatTime(memorySeconds);
    }, 1000);
    
    // Create card pairs
    let cards = [...FLOWERS, ...FLOWERS];
    cards = shuffleArray(cards);
    memoryCards = cards;
    
    // Render
    const grid = document.getElementById('memory-grid');
    grid.innerHTML = '';
    
    cards.forEach((flower, i) => {
        const card = document.createElement('div');
        card.className = 'memory-card';
        card.dataset.index = i;
        card.innerHTML = `
            <div class="card-front">❓</div>
            <div class="card-back">${flower}</div>
        `;
        card.onclick = () => flipCard(card, i);
        grid.appendChild(card);
    });
    
    updateMemoryStats();
}

function flipCard(card, index) {
    if(isLocked || card.classList.contains('flipped') || card.classList.contains('matched')) return;
    
    card.classList.add('flipped');
    flippedCards.push({ card, index, flower: memoryCards[index] });
    
    if(flippedCards.length === 2) {
        memoryMoves++;
        isLocked = true;
        
        const [first, second] = flippedCards;
        
        if(first.flower === second.flower) {
            // Match!
            matchedPairs++;
            first.card.classList.add('matched');
            second.card.classList.add('matched');
            flippedCards = [];
            isLocked = false;
            
            if(matchedPairs === 8) {
                clearInterval(memoryTimer);
                const score = Math.max(0, 1000 - (memoryMoves * 10) - (memorySeconds * 2));
                showResult('🎉', 'Hoàn thành!', score + ' điểm', 'memory', score);
            }
        } else {
            // No match
            setTimeout(() => {
                first.card.classList.remove('flipped');
                second.card.classList.remove('flipped');
                flippedCards = [];
                isLocked = false;
            }, 1000);
        }
        
        updateMemoryStats();
    }
}

function updateMemoryStats() {
    document.getElementById('memory-moves').textContent = memoryMoves;
    document.getElementById('memory-pairs').textContent = matchedPairs + '/8';
}

function formatTime(seconds) {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
}

// ============ PUZZLE GAME ============
let puzzleLevel = 1;
let puzzleScore = 0;
let targetPattern = [];
let playerPattern = [];
let selectedFlower = null;

function startPuzzleGame() {
    puzzleLevel = 1;
    puzzleScore = 0;
    generatePuzzle();
    updatePuzzleStats();
}

function generatePuzzle() {
    // Generate random target pattern
    const size = Math.min(puzzleLevel + 2, 9);
    targetPattern = [];
    for(let i = 0; i < size; i++) {
        targetPattern.push(FLOWERS[Math.floor(Math.random() * FLOWERS.length)]);
    }
    
    playerPattern = new Array(size).fill(null);
    selectedFlower = null;
    
    renderPuzzle();
}

function renderPuzzle() {
    // Target
    const target = document.getElementById('target-bouquet');
    target.innerHTML = '';
    targetPattern.forEach(f => {
        const slot = document.createElement('div');
        slot.className = 'target-slot';
        slot.textContent = f;
        target.appendChild(slot);
    });
    
    // Player
    const player = document.getElementById('player-bouquet');
    player.innerHTML = '';
    playerPattern.forEach((f, i) => {
        const slot = document.createElement('div');
        slot.className = 'player-slot';
        slot.textContent = f || '';
        slot.onclick = () => placeFlower(i);
        player.appendChild(slot);
    });
    
    // Palette
    const palette = document.getElementById('flower-palette');
    palette.innerHTML = '';
    FLOWERS.forEach(f => {
        const flower = document.createElement('div');
        flower.className = 'palette-flower';
        if(selectedFlower === f) flower.classList.add('selected');
        flower.textContent = f;
        flower.onclick = () => selectFlower(f);
        palette.appendChild(flower);
    });
}

function selectFlower(flower) {
    selectedFlower = flower;
    renderPuzzle();
}

function placeFlower(index) {
    if(selectedFlower) {
        playerPattern[index] = selectedFlower;
    } else {
        playerPattern[index] = null;
    }
    renderPuzzle();
}

function checkPuzzle() {
    const correct = targetPattern.every((f, i) => f === playerPattern[i]);
    
    if(correct) {
        const levelScore = puzzleLevel * 100;
        puzzleScore += levelScore;
        puzzleLevel++;
        
        if(puzzleLevel > 5) {
            showResult('🏆', 'Hoàn thành tất cả!', puzzleScore + ' điểm', 'puzzle', puzzleScore);
        } else {
            showResult('✅', 'Đúng rồi!', '+' + levelScore + ' điểm', 'puzzle', null);
            setTimeout(() => {
                closeResult();
                generatePuzzle();
                updatePuzzleStats();
            }, 1500);
        }
    } else {
        showResult('❌', 'Sai rồi!', 'Thử lại nhé', 'puzzle', null);
    }
    
    updatePuzzleStats();
}

function updatePuzzleStats() {
    document.getElementById('puzzle-level').textContent = puzzleLevel;
    document.getElementById('puzzle-score').textContent = puzzleScore;
}

// ============ UTILITIES ============
function shuffleArray(array) {
    const arr = [...array];
    for(let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
}

function showResult(icon, title, score, gameType, finalScore) {
    document.getElementById('result-icon').textContent = icon;
    document.getElementById('result-title').textContent = title;
    document.getElementById('result-score').textContent = score;
    document.getElementById('result-modal').classList.add('active');
    
    // Save score to server
    if(finalScore !== null) {
        // Could use AJAX to save score
        console.log('Save score:', gameType, finalScore);
    }
}

function closeResult() {
    document.getElementById('result-modal').classList.remove('active');
}

function playAgain() {
    closeResult();
    if(currentGame === 'memory') startMemoryGame();
    else startPuzzleGame();
}

// Initialize
startMemoryGame();
</script>

</body>
</html>
