<?php
/**
 * 🤖 AI Tư Vấn Hoa - Chatbot Gợi Ý Hoa
 * Gợi ý hoa theo dịp, người nhận, ngân sách
 */

@include '../config.php';

$user_id = $_SESSION['user_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Tư Vấn Hoa - Flower Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .ai-section {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .chat-container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 30px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 85vh;
            max-height: 700px;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 1.5rem 2rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .bot-avatar {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        
        .bot-info h3 {
            margin: 0;
            font-size: 1.3rem;
        }
        
        .bot-info p {
            margin: 0;
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .bot-status {
            width: 10px;
            height: 10px;
            background: #00ff88;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background: #f8f9fa;
        }
        
        .message {
            margin-bottom: 1rem;
            display: flex;
            gap: 0.8rem;
            animation: messageIn 0.3s ease;
        }
        
        @keyframes messageIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message.user {
            flex-direction: row-reverse;
        }
        
        .message-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .message.bot .message-avatar {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        
        .message.user .message-avatar {
            background: #e0e0e0;
        }
        
        .message-content {
            max-width: 75%;
            padding: 1rem 1.2rem;
            border-radius: 20px;
            font-size: 1.05rem;
            line-height: 1.5;
        }
        
        .message.bot .message-content {
            background: white;
            border-bottom-left-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .message.user .message-content {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-bottom-right-radius: 5px;
        }
        
        /* Quick Options */
        .quick-options {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .quick-btn {
            padding: 0.6rem 1rem;
            background: #f0f0ff;
            border: 2px solid #667eea;
            border-radius: 20px;
            color: #667eea;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .quick-btn:hover {
            background: #667eea;
            color: white;
        }
        
        /* Product Card in Chat */
        .product-card-mini {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1rem;
            margin-top: 1rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .product-emoji {
            font-size: 3rem;
        }
        
        .product-info h4 {
            margin: 0 0 0.3rem;
            color: #2d3436;
        }
        
        .product-price {
            color: #e74c3c;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .product-btn {
            padding: 0.5rem 1rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        /* Typing indicator */
        .typing {
            display: flex;
            gap: 4px;
            padding: 1rem;
        }
        
        .typing span {
            width: 8px;
            height: 8px;
            background: #667eea;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }
        
        .typing span:nth-child(2) { animation-delay: 0.2s; }
        .typing span:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }
        
        /* Chat Input */
        .chat-input {
            padding: 1rem 1.5rem;
            background: white;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 1rem;
        }
        
        .chat-input input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .chat-input input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .send-btn {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .send-btn:hover {
            transform: scale(1.1);
        }
        
        /* Suggested questions */
        .suggestions {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }
        
        .suggestions p {
            font-size: 0.9rem;
            color: #636e72;
            margin-bottom: 0.5rem;
        }
        
        .suggestion-chips {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }
        
        .suggestion-chip {
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            white-space: nowrap;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .suggestion-chip:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
    </style>
</head>
<body>

<?php @include '../header.php'; ?>

<section class="ai-section">
    <div class="chat-container">
        <div class="chat-header">
            <div class="bot-avatar">🌸</div>
            <div class="bot-info">
                <h3>Hoa Bot <span class="bot-status"></span></h3>
                <p>Tư vấn viên AI của bạn</p>
            </div>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <!-- Messages will be added here -->
        </div>
        
        <div class="suggestions">
            <p>💡 Gợi ý câu hỏi:</p>
            <div class="suggestion-chips">
                <span class="suggestion-chip" onclick="sendSuggestion(this)">Hoa tặng người yêu</span>
                <span class="suggestion-chip" onclick="sendSuggestion(this)">Hoa sinh nhật mẹ</span>
                <span class="suggestion-chip" onclick="sendSuggestion(this)">Hoa chúc mừng khai trương</span>
                <span class="suggestion-chip" onclick="sendSuggestion(this)">Hoa dưới 500k</span>
            </div>
        </div>
        
        <div class="chat-input">
            <input type="text" id="user-input" placeholder="Nhập câu hỏi của bạn..." onkeypress="handleKeyPress(event)">
            <button class="send-btn" onclick="sendMessage()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</section>

<?php @include '../footer.php'; ?>

<script>
// AI Knowledge Base
const flowerDB = {
    occasions: {
        'valentine': { flowers: ['Hoa Hồng Đỏ', 'Hoa Tulip'], emoji: '🌹', budget: '300k-800k' },
        'sinh nhật': { flowers: ['Hoa Hướng Dương', 'Hoa Hồng Hồng', 'Hoa Lily'], emoji: '🎂', budget: '200k-500k' },
        'kỷ niệm': { flowers: ['Hoa Hồng', 'Hoa Lan'], emoji: '💕', budget: '500k-1tr' },
        'khai trương': { flowers: ['Hoa Lan Hồ Điệp', 'Hoa Hướng Dương'], emoji: '🏪', budget: '800k-2tr' },
        'chia buồn': { flowers: ['Hoa Cúc Trắng', 'Hoa Lily Trắng'], emoji: '🕯️', budget: '300k-700k' },
        'cảm ơn': { flowers: ['Hoa Hồng Hồng', 'Hoa Cẩm Chướng'], emoji: '🙏', budget: '150k-400k' },
        'xin lỗi': { flowers: ['Hoa Tulip Trắng', 'Hoa Hồng Trắng'], emoji: '😔', budget: '200k-500k' },
        'tốt nghiệp': { flowers: ['Hoa Hướng Dương', 'Bó hoa hỗn hợp'], emoji: '🎓', budget: '200k-600k' },
        'cưới': { flowers: ['Hoa Hồng Trắng', 'Hoa Baby', 'Hoa Cát Tường'], emoji: '💒', budget: '500k-2tr' }
    },
    recipients: {
        'người yêu': { flowers: ['Hoa Hồng Đỏ', 'Hoa Tulip'], style: 'Lãng mạn, ngọt ngào' },
        'mẹ': { flowers: ['Hoa Cẩm Chướng', 'Hoa Lily', 'Hoa Lan'], style: 'Ấm áp, trang nhã' },
        'bạn gái': { flowers: ['Hoa Hồng Hồng', 'Hoa Tulip'], style: 'Dễ thương, lãng mạn' },
        'sếp': { flowers: ['Hoa Lan Hồ Điệp', 'Bình hoa sang trọng'], style: 'Sang trọng, lịch sự' },
        'bạn bè': { flowers: ['Hoa Hướng Dương', 'Hoa Cúc'], style: 'Vui tươi, năng động' },
        'bạn thân': { flowers: ['Hoa Hồng Vàng', 'Bó hoa hỗn hợp'], style: 'Thân thiện, ấm áp' },
        'đồng nghiệp': { flowers: ['Hoa Cát Tường', 'Giỏ hoa nhỏ'], style: 'Thanh lịch, trang nhã' }
    },
    products: [
        { name: 'Bó Hồng Đỏ 20 Bông', price: 350000, emoji: '🌹', occasions: ['valentine', 'kỷ niệm'] },
        { name: 'Hướng Dương Rạng Rỡ', price: 280000, emoji: '🌻', occasions: ['sinh nhật', 'tốt nghiệp'] },
        { name: 'Lan Hồ Điệp 2 Cành', price: 980000, emoji: '🦋', occasions: ['khai trương', 'biếu sếp'] },
        { name: 'Cẩm Chướng Yêu Thương', price: 220000, emoji: '💮', occasions: ['cảm ơn', 'ngày của mẹ'] },
        { name: 'Lily Thanh Khiết', price: 450000, emoji: '🌺', occasions: ['sinh nhật', 'cưới'] },
        { name: 'Bó Hoa Hỗn Hợp', price: 320000, emoji: '💐', occasions: ['sinh nhật', 'bạn bè'] }
    ]
};

// Conversation state
let conversationState = {
    step: 'greeting',
    occasion: null,
    recipient: null,
    budget: null
};

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    showGreeting();
});

function showGreeting() {
    const greetings = [
        'Xin chào! 👋 Mình là Hoa Bot, trợ lý tư vấn hoa của bạn!',
        'Mình có thể giúp bạn chọn hoa phù hợp cho mọi dịp. Bạn muốn tặng hoa cho ai hoặc dịp gì?'
    ];
    
    greetings.forEach((msg, i) => {
        setTimeout(() => addBotMessage(msg), i * 800);
    });
    
    setTimeout(() => {
        addQuickOptions([
            '💕 Tặng người yêu',
            '🎂 Sinh nhật',
            '👩 Tặng mẹ',
            '🏪 Khai trương',
            '🙏 Cảm ơn ai đó'
        ]);
    }, 1800);
}

function addBotMessage(text, isTyping = false) {
    const messagesDiv = document.getElementById('chat-messages');
    
    if (isTyping) {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message bot typing-indicator';
        typingDiv.innerHTML = `
            <div class="message-avatar">🌸</div>
            <div class="message-content">
                <div class="typing">
                    <span></span><span></span><span></span>
                </div>
            </div>
        `;
        messagesDiv.appendChild(typingDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
        return typingDiv;
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message bot';
    messageDiv.innerHTML = `
        <div class="message-avatar">🌸</div>
        <div class="message-content">${text}</div>
    `;
    messagesDiv.appendChild(messageDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function addUserMessage(text) {
    const messagesDiv = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message user';
    messageDiv.innerHTML = `
        <div class="message-avatar">👤</div>
        <div class="message-content">${text}</div>
    `;
    messagesDiv.appendChild(messageDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function addQuickOptions(options) {
    const messagesDiv = document.getElementById('chat-messages');
    const optionsDiv = document.createElement('div');
    optionsDiv.className = 'message bot';
    optionsDiv.innerHTML = `
        <div class="message-avatar">🌸</div>
        <div class="message-content">
            <div class="quick-options">
                ${options.map(opt => `<button class="quick-btn" onclick="selectOption('${opt}')">${opt}</button>`).join('')}
            </div>
        </div>
    `;
    messagesDiv.appendChild(optionsDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function addProductCard(product) {
    const messagesDiv = document.getElementById('chat-messages');
    const cardDiv = document.createElement('div');
    cardDiv.className = 'message bot';
    cardDiv.innerHTML = `
        <div class="message-avatar">🌸</div>
        <div class="message-content">
            <div class="product-card-mini">
                <div class="product-emoji">${product.emoji}</div>
                <div class="product-info">
                    <h4>${product.name}</h4>
                    <div class="product-price">${formatPrice(product.price)}</div>
                    <button class="product-btn" onclick="window.location.href='../pages/shop.php'">
                        <i class="fas fa-shopping-cart"></i> Xem ngay
                    </button>
                </div>
            </div>
        </div>
    `;
    messagesDiv.appendChild(cardDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function selectOption(option) {
    addUserMessage(option);
    processUserInput(option.toLowerCase());
}

function sendMessage() {
    const input = document.getElementById('user-input');
    const text = input.value.trim();
    if (!text) return;
    
    addUserMessage(text);
    input.value = '';
    processUserInput(text.toLowerCase());
}

function sendSuggestion(el) {
    const text = el.textContent;
    addUserMessage(text);
    processUserInput(text.toLowerCase());
}

function handleKeyPress(e) {
    if (e.key === 'Enter') sendMessage();
}

function processUserInput(text) {
    // Show typing indicator
    const typingIndicator = addBotMessage('', true);
    
    setTimeout(() => {
        // Remove typing indicator
        typingIndicator.remove();
        
        // Process input
        let response = analyzeInput(text);
        
        if (response.message) {
            addBotMessage(response.message);
        }
        
        if (response.products && response.products.length > 0) {
            setTimeout(() => {
                addBotMessage('Đây là một số gợi ý cho bạn:');
                response.products.forEach((p, i) => {
                    setTimeout(() => addProductCard(p), i * 300);
                });
            }, 500);
        }
        
        if (response.options) {
            setTimeout(() => addQuickOptions(response.options), 800);
        }
        
    }, 1000 + Math.random() * 500);
}

function analyzeInput(text) {
    let response = { message: '', products: [], options: null };
    
    // Check for occasions
    for (let occasion in flowerDB.occasions) {
        if (text.includes(occasion)) {
            const data = flowerDB.occasions[occasion];
            response.message = `${data.emoji} Cho dịp ${occasion}, mình gợi ý: <b>${data.flowers.join(', ')}</b>. Ngân sách tham khảo: <b>${data.budget}</b>.`;
            response.products = flowerDB.products.filter(p => p.occasions.some(o => text.includes(o))).slice(0, 2);
            response.options = ['Xem thêm mẫu', 'Tư vấn thêm', 'Đặt hoa ngay'];
            return response;
        }
    }
    
    // Check for recipients
    for (let recipient in flowerDB.recipients) {
        if (text.includes(recipient)) {
            const data = flowerDB.recipients[recipient];
            response.message = `Tặng ${recipient}? Mình gợi ý: <b>${data.flowers.join(', ')}</b>. Phong cách: <b>${data.style}</b>. Bạn muốn tặng nhân dịp gì?`;
            response.options = ['Sinh nhật', 'Valentine', 'Cảm ơn', 'Không dịp gì đặc biệt'];
            return response;
        }
    }
    
    // Check for budget
    if (text.includes('dưới') || text.includes('khoảng') || text.includes('k') || text.includes('triệu')) {
        let budget = 0;
        const match = text.match(/(\d+)/);
        if (match) {
            budget = parseInt(match[1]);
            if (text.includes('triệu') || text.includes('tr')) budget *= 1000000;
            else if (budget < 1000) budget *= 1000;
        }
        
        response.message = `Với ngân sách ${formatPrice(budget)}, mình có một số gợi ý sau:`;
        response.products = flowerDB.products.filter(p => p.price <= budget * 1.2).slice(0, 3);
        if (response.products.length === 0) {
            response.products = flowerDB.products.slice(0, 2);
            response.message = 'Đây là một số mẫu phổ biến:';
        }
        return response;
    }
    
    // Check for greetings
    if (text.includes('xin chào') || text.includes('hello') || text.includes('hi')) {
        response.message = 'Chào bạn! 👋 Mình có thể giúp gì cho bạn? Bạn đang tìm hoa cho dịp gì?';
        response.options = ['Sinh nhật', 'Valentine', 'Khai trương', 'Tặng mẹ'];
        return response;
    }
    
    // Check for thanks
    if (text.includes('cảm ơn') || text.includes('thanks')) {
        response.message = 'Không có gì! 😊 Chúc bạn chọn được bó hoa ưng ý! Nếu cần thêm tư vấn, cứ hỏi mình nhé!';
        return response;
    }
    
    // Default response
    response.message = 'Mình hiểu bạn đang tìm hoa. Để tư vấn chính xác hơn, bạn cho mình biết: Tặng ai và dịp gì nhé?';
    response.options = ['💕 Tặng người yêu', '👩 Tặng mẹ', '🎂 Sinh nhật', '🏪 Khai trương'];
    return response;
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price) + '₫';
}
</script>

</body>
</html>
