<!-- Chat Widget - Floating Button -->
<div id="chat-widget" class="chat-widget">
    <button id="chat-toggle" class="chat-toggle-btn">
        <i class="fas fa-comments"></i>
        <span id="unread-badge" class="chat-unread-badge" style="display: none;">0</span>
    </button>
    
    <div id="chat-box" class="chat-box" style="display: none;">
        <div class="chat-header">
            <div class="chat-header-info">
                <i class="fas fa-user-circle"></i>
                <div>
                    <h4>Tư vấn viên hoa</h4>
                    <span class="chat-status">Trực tuyến</span>
                </div>
            </div>
            <button id="chat-close" class="chat-close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="chat-messages" class="chat-messages">
            <div class="chat-welcome">
                <i class="fas fa-seedling" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                <h3>Chào mừng bạn! 🌸</h3>
                <p>Chúng tôi có thể giúp bạn chọn hoa phù hợp</p>
            </div>
        </div>
        
        <form id="chat-form" class="chat-input-form">
            <input type="text" 
                   id="chat-message-input" 
                   placeholder="Nhập tin nhắn..." 
                   autocomplete="off"
                   required>
            <button type="submit" class="chat-send-btn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

<style>
/* Chat Widget Styles */
.chat-widget {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 9999;
}

.chat-toggle-btn {
    width: 6rem;
    height: 6rem;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    color: white;
    border: none;
    font-size: 2.5rem;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(255, 107, 157, 0.4);
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 30px rgba(255, 107, 157, 0.6);
}

.chat-unread-badge {
    position: absolute;
    top: 0;
    right: 0;
    background: #e74c3c;
    color: white;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: bold;
    border: 3px solid white;
}

.chat-box {
    position: absolute;
    bottom: 8rem;
    right: 0;
    width: 380px;
    height: 550px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 50px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chat-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    color: white;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header-info {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.chat-header-info i {
    font-size: 2.5rem;
}

.chat-header-info h4 {
    font-size: 1.6rem;
    margin: 0;
    font-weight: 600;
}

.chat-status {
    font-size: 1.2rem;
    opacity: 0.9;
}

.chat-close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 2rem;
    cursor: pointer;
    padding: 0.5rem;
    transition: all 0.3s ease;
}

.chat-close-btn:hover {
    transform: rotate(90deg);
}

.chat-messages {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto;
    background: #f8f9fa;
}

.chat-welcome {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--text-secondary);
}

.chat-welcome h3 {
    font-size: 2rem;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.chat-welcome p {
    font-size: 1.4rem;
}

.chat-message {
    margin-bottom: 1.5rem;
    display: flex;
    gap: 1rem;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.chat-message.user {
    flex-direction: row-reverse;
}

.chat-message-avatar {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
}

.chat-message.admin .chat-message-avatar {
    background: linear-gradient(135deg, var(--secondary) 0%, #a29bfe 100%);
}

.chat-message-content {
    max-width: 70%;
}

.chat-message-bubble {
    background: white;
    padding: 1rem 1.5rem;
    border-radius: 15px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    word-wrap: break-word;
}

.chat-message.user .chat-message-bubble {
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    color: white;
}

.chat-message-text {
    font-size: 1.4rem;
    line-height: 1.5;
    margin: 0;
}

.chat-message-time {
    font-size: 1.1rem;
    color: var(--text-light);
    margin-top: 0.5rem;
}

.chat-input-form {
    padding: 1.5rem;
    background: white;
    border-top: 1px solid #e0e0e0;
    display: flex;
    gap: 1rem;
}

.chat-input-form input {
    flex: 1;
    padding: 1rem 1.5rem;
    border: 2px solid #e0e0e0;
    border-radius: 25px;
    font-size: 1.4rem;
    transition: all 0.3s ease;
}

.chat-input-form input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
}

.chat-send-btn {
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    color: white;
    border: none;
    font-size: 1.6rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-send-btn:hover {
    transform: scale(1.1);
}

.chat-typing-indicator {
    display: flex;
    gap: 0.5rem;
    padding: 1rem;
    align-items: center;
}

.chat-typing-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--primary);
    animation: typing 1.4s infinite;
}

.chat-typing-dot:nth-child(2) {
    animation-delay: 0.2s;
}

.chat-typing-dot:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-10px);
    }
}

/* Responsive */
@media (max-width: 450px) {
    .chat-box {
        width: calc(100vw - 4rem);
        height: calc(100vh - 12rem);
        bottom: 9rem;
        right: 50%;
        transform: translateX(50%);
    }
    
    .chat-widget {
        right: 50%;
        transform: translateX(50%);
    }
}
</style>

<script>
// Chat Widget JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const chatToggle = document.getElementById('chat-toggle');
    const chatBox = document.getElementById('chat-box');
    const chatClose = document.getElementById('chat-close');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-message-input');
    const chatMessages = document.getElementById('chat-messages');
    let conversationId = null;
    let checkNewMessagesInterval = null;
    
    // Base URL for AJAX calls
    const chatAjaxUrl = '/flower-shop/chat/chat_ajax.php';
    
    // Toggle chat box
    chatToggle.onclick = function() {
        chatBox.style.display = chatBox.style.display === 'none' ? 'flex' : 'none';
        if(chatBox.style.display === 'flex') {
            loadChatHistory();
            startCheckingNewMessages();
            chatInput.focus();
        } else {
            stopCheckingNewMessages();
        }
    };
    
    chatClose.onclick = function() {
        chatBox.style.display = 'none';
        stopCheckingNewMessages();
    };
    
    // Send message
    chatForm.onsubmit = function(e) {
        e.preventDefault();
        const message = chatInput.value.trim();
        
        if(message === '') return;
        
        // Add user message to UI immediately
        addMessageToUI('user', message, 'Bạn', 'Vừa xong');
        chatInput.value = '';
        
        // Send to server
        fetch(chatAjaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=send_message&message=' + encodeURIComponent(message) + '&conversation_id=' + (conversationId || '')
        })
        .then(response => {
            if(!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                conversationId = data.conversation_id;
            } else {
                console.error('Chat error:', data.error);
                // Hiển thị lỗi nếu chưa đăng nhập
                if(data.error === 'Not logged in') {
                    alert('Vui lòng đăng nhập để sử dụng chat!');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    };
    
    // Load chat history
    function loadChatHistory() {
        fetch(chatAjaxUrl + '?action=load_messages')
            .then(response => response.json())
            .then(data => {
                if(data.success && data.messages && data.messages.length > 0) {
                    // Remove welcome message
                    const welcome = chatMessages.querySelector('.chat-welcome');
                    if(welcome) welcome.remove();
                    
                    conversationId = data.conversation_id;
                    
                    data.messages.forEach(msg => {
                        addMessageToUI(msg.sender_type, msg.message, msg.sender_name, msg.time);
                    });
                    
                    scrollToBottom();
                }
            })
            .catch(error => console.error('Error:', error));
    }
    
    // Check for new messages
    function startCheckingNewMessages() {
        checkNewMessagesInterval = setInterval(() => {
            if(!conversationId) return;
            
            fetch(chatAjaxUrl + '?action=check_new_messages&conversation_id=' + conversationId)
                .then(response => response.json())
                .then(data => {
                    if(data.success && data.new_messages && data.new_messages.length > 0) {
                        data.new_messages.forEach(msg => {
                            addMessageToUI(msg.sender_type, msg.message, msg.sender_name, msg.time);
                        });
                        scrollToBottom();
                    }
                    
                    // Update unread count
                    updateUnreadBadge(data.unread_count || 0);
                })
                .catch(error => console.error('Error:', error));
        }, 3000); // Check every 3 seconds
    }
    
    function stopCheckingNewMessages() {
        if(checkNewMessagesInterval) {
            clearInterval(checkNewMessagesInterval);
        }
    }
    
    // Add message to UI
    function addMessageToUI(type, message, senderName, time) {
        const welcome = chatMessages.querySelector('.chat-welcome');
        if(welcome) welcome.remove();
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-message ' + type;
        messageDiv.innerHTML = `
            <div class="chat-message-avatar">
                <i class="fas fa-${type === 'user' ? 'user' : 'headset'}"></i>
            </div>
            <div class="chat-message-content">
                <div class="chat-message-bubble">
                    <p class="chat-message-text">${escapeHtml(message)}</p>
                </div>
                <div class="chat-message-time">${time}</div>
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        scrollToBottom();
    }
    
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    function updateUnreadBadge(count) {
        const badge = document.getElementById('unread-badge');
        if(count > 0) {
            badge.textContent = count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>

