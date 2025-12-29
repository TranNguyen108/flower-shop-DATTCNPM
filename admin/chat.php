<?php

@include '../config.php';

$admin_id = $_SESSION['admin_id'] ?? null;

if(!isset($admin_id)){
   header('location:../auth/login.php');
   exit;
}

// Handle send reply
if(isset($_POST['send_reply'])){
   if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
      $message[] = 'Yêu cầu không hợp lệ!';
   } else {
      $conversation_id = (int)$_POST['conversation_id'];
      $reply_message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
      $admin_name = $_SESSION['admin_name'] ?? 'Admin';
      
      if(!empty($reply_message)){
         $stmt = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_type, sender_id, sender_name, message) 
             VALUES (?, 'admin', ?, ?, ?)");
         $stmt->bind_param("iiss", $conversation_id, $admin_id, $admin_name, $reply_message);
         $stmt->execute();
         
         $update_stmt = $conn->prepare("UPDATE chat_conversations SET last_message_at = NOW() WHERE id = ?");
         $update_stmt->bind_param("i", $conversation_id);
         $update_stmt->execute();
         
         $message[] = 'Đã gửi tin nhắn!';
      }
   }
}

// Handle close conversation
if(isset($_GET['close'])){
   $conversation_id = (int)$_GET['close'];
   $stmt = $conn->prepare("UPDATE chat_conversations SET status = 'closed' WHERE id = ?");
   $stmt->bind_param("i", $conversation_id);
   $stmt->execute();
   header('location:admin_chat.php');
}

// Get all conversations
$conversations_query = "SELECT c.*, 
    (SELECT COUNT(*) FROM chat_messages WHERE conversation_id = c.id AND sender_type = 'user' AND is_read = 0) as unread_count,
    (SELECT message FROM chat_messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message
    FROM chat_conversations c
    WHERE c.status = 'open'
    ORDER BY c.last_message_at DESC";
$conversations_result = $conn->query($conversations_query);
$conversations = [];
if($conversations_result && $conversations_result->num_rows > 0){
   while($row = $conversations_result->fetch_assoc()){
      $conversations[] = $row;
   }
}

// Get selected conversation messages
$selected_conversation = null;
$messages = [];
if(isset($_GET['conversation_id'])){
   $conversation_id = (int)$_GET['conversation_id'];
   
   $conv_stmt = $conn->prepare("SELECT * FROM chat_conversations WHERE id = ?");
   $conv_stmt->bind_param("i", $conversation_id);
   $conv_stmt->execute();
   $conv_result = $conv_stmt->get_result();
   
   if($conv_result && $conv_result->num_rows > 0){
      $selected_conversation = $conv_result->fetch_assoc();
      
      $msg_stmt = $conn->prepare("SELECT *, DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as formatted_time 
          FROM chat_messages 
          WHERE conversation_id = ? 
          ORDER BY created_at ASC");
      $msg_stmt->bind_param("i", $conversation_id);
      $msg_stmt->execute();
      $msg_result = $msg_stmt->get_result();
      
      while($msg = $msg_result->fetch_assoc()){
         $messages[] = $msg;
      }
      
      // Mark user messages as read
      $update_stmt = $conn->prepare("UPDATE chat_messages SET is_read = 1 
          WHERE conversation_id = ? AND sender_type = 'user' AND is_read = 0");
      $update_stmt->bind_param("i", $conversation_id);
      $update_stmt->execute();
   }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Quản Lý Chat</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   
   <style>
   * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
   }
   
   body {
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
   }
   
   .heading {
      display: none;
   }
   
   .chat-container {
      display: grid;
      grid-template-columns: 380px 1fr;
      gap: 20px;
      height: calc(100vh - 120px);
      margin: 20px;
      max-width: 1600px;
      margin: 20px auto;
   }
   
   .conversations-list {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      overflow: hidden;
      display: flex;
      flex-direction: column;
   }
   
   .conversations-header {
      padding: 25px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
   }
   
   .conversations-header h3 {
      margin: 0;
      font-size: 1.4rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
   }
   
   .conversations-header h3 i {
      font-size: 1.6rem;
   }
   
   .conversations-list-scroll {
      flex: 1;
      overflow-y: auto;
   }
   
   .conversation-item {
      padding: 20px;
      border-bottom: 1px solid #f0f0f0;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      background: white;
   }
   
   .conversation-item:hover {
      background: #f8f9fa;
      transform: translateX(5px);
   }
   
   .conversation-item.active {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-left: 5px solid #fff;
   }
   
   .conversation-user {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 10px;
   }
   
   .conversation-user i {
      font-size: 1.3rem;
      color: #667eea;
   }
   
   .conversation-item.active .conversation-user i {
      color: white;
   }
   
   .conversation-last-message {
      font-size: 0.95rem;
      color: #666;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      padding-left: 30px;
   }
   
   .conversation-item.active .conversation-last-message {
      color: rgba(255,255,255,0.9);
   }
   
   .unread-badge {
      position: absolute;
      top: 20px;
      right: 20px;
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: white;
      min-width: 24px;
      height: 24px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.85rem;
      font-weight: 700;
      padding: 0 8px;
      box-shadow: 0 3px 10px rgba(245, 87, 108, 0.4);
   }
   
   .chat-window {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      overflow: hidden;
   }
   
   .chat-window-header {
      padding: 0;
      border-bottom: none;
      display: flex;
      flex-direction: column;
      background: white;
      position: relative;
   }
   
   .chat-header-top {
      padding: 20px 30px;
      background: linear-gradient(135deg, #ff6b9d 0%, #ffa6c9 100%);
      display: flex;
      justify-content: space-between;
      align-items: center;
   }
   
   .chat-header-brand {
      display: flex;
      align-items: center;
      gap: 15px;
      color: white;
   }
   
   .chat-header-brand i {
      font-size: 2rem;
      background: rgba(255,255,255,0.2);
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
   }
   
   .chat-header-brand h2 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: 0.5px;
   }
   
   .chat-header-brand p {
      margin: 0;
      font-size: 0.85rem;
      opacity: 0.9;
   }
   
   .chat-header-actions {
      display: flex;
      gap: 15px;
   }
   
   .header-action-btn {
      background: rgba(255,255,255,0.2);
      color: white;
      padding: 10px 20px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      border: 2px solid rgba(255,255,255,0.3);
      white-space: nowrap;
   }
   
   .header-action-btn:hover {
      background: white;
      color: #ff6b9d;
      border-color: white;
   }
   
   .chat-header-bottom {
      padding: 20px 30px;
      background: white;
      border-bottom: 2px solid #f8f9fa;
      display: flex;
      justify-content: space-between;
      align-items: center;
   }
   
   .header-user-info {
      display: flex;
      align-items: center;
      gap: 15px;
   }
   
   .header-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      box-shadow: 0 3px 10px rgba(102, 126, 234, 0.2);
      flex-shrink: 0;
   }
   
   .header-user-details {
      display: flex;
      flex-direction: column;
      gap: 4px;
   }
   
   .header-user-name {
      font-size: 1.2rem;
      font-weight: 700;
      color: #2c3e50;
      margin: 0;
      line-height: 1.2;
   }
   
   .header-conversation-time {
      font-size: 0.85rem;
      color: #6c757d;
      display: flex;
      align-items: center;
      gap: 5px;
      margin: 0;
      line-height: 1.2;
   }
   
   .header-conversation-time i {
      color: #ff6b9d;
      font-size: 0.8rem;
   }
   
   .chat-messages-area {
      flex: 1;
      padding: 30px;
      overflow-y: auto;
      background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
   }
   
   .chat-message-item {
      margin-bottom: 20px;
      display: flex;
      gap: 15px;
      animation: fadeIn 0.3s ease;
   }
   
   @keyframes fadeIn {
      from {
         opacity: 0;
         transform: translateY(10px);
      }
      to {
         opacity: 1;
         transform: translateY(0);
      }
   }
   
   .chat-message-item.admin {
      flex-direction: row-reverse;
   }
   
   .message-avatar {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      flex-shrink: 0;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
   }
   
   .chat-message-item.admin .message-avatar {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
   }
   
   .message-content {
      max-width: 65%;
      display: flex;
      flex-direction: column;
   }
   
   .chat-message-item.admin .message-content {
      align-items: flex-end;
   }
   
   .message-bubble {
      background: white;
      padding: 15px 20px;
      border-radius: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      position: relative;
      border: 2px solid #f0f0f0;
   }
   
   .chat-message-item.user .message-bubble {
      border-bottom-left-radius: 5px;
   }
   
   .chat-message-item.admin .message-bubble {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-bottom-right-radius: 5px;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
   }
   
   .message-text {
      font-size: 1rem;
      line-height: 1.6;
      word-wrap: break-word;
      margin: 0;
   }
   
   .message-time {
      font-size: 0.85rem;
      color: #999;
      margin-top: 8px;
      display: flex;
      align-items: center;
      gap: 5px;
   }
   
   .chat-message-item.admin .message-time {
      color: #667eea;
   }
   
   .message-time i {
      font-size: 0.75rem;
   }
   
   .chat-reply-form {
      padding: 25px 30px;
      border-top: 2px solid #f0f0f0;
      display: flex;
      gap: 15px;
      background: white;
   }
   
   .chat-reply-form textarea {
      flex: 1;
      padding: 15px 20px;
      border: 2px solid #e9ecef;
      border-radius: 25px;
      font-size: 1rem;
      resize: none;
      min-height: 50px;
      max-height: 120px;
      font-family: inherit;
      transition: all 0.3s ease;
      background: #f8f9fa;
   }
   
   .chat-reply-form textarea:focus {
      outline: none;
      border-color: #667eea;
      background: white;
      box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
   }
   
   .chat-reply-form button {
      padding: 15px 30px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 25px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
   }
   
   .chat-reply-form button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
   }
   
   .chat-reply-form button:active {
      transform: translateY(0);
   }
   
   .chat-reply-form button i {
      font-size: 1.1rem;
   }
   
   .empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      color: #adb5bd;
   }
   
   .empty-state i {
      font-size: 5rem;
      margin-bottom: 20px;
      opacity: 0.3;
   }
   
   .empty-state h3 {
      font-size: 1.5rem;
      margin-bottom: 10px;
      color: #6c757d;
   }
   
   .empty-state p {
      font-size: 1rem;
      color: #adb5bd;
   }
   
   .delete-btn {
      background: rgba(255,255,255,0.2);
      color: white;
      padding: 10px 20px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      border: 2px solid rgba(255,255,255,0.3);
      white-space: nowrap;
   }
   
   .delete-btn i {
      font-size: 1rem;
   }
   
   .delete-btn:hover {
      background: white;
      color: #dc3545;
      border-color: white;
   }
   
   .status-indicator {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 15px;
      background: #d4edda;
      color: #155724;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
   }
   
   .status-indicator i {
      font-size: 0.7rem;
   }
   
   /* Scrollbar styling */
   .conversations-list-scroll::-webkit-scrollbar,
   .chat-messages-area::-webkit-scrollbar {
      width: 8px;
   }
   
   .conversations-list-scroll::-webkit-scrollbar-track,
   .chat-messages-area::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
   }
   
   .conversations-list-scroll::-webkit-scrollbar-thumb,
   .chat-messages-area::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 10px;
   }
   
   .conversations-list-scroll::-webkit-scrollbar-thumb:hover,
   .chat-messages-area::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
   }
   
   @media (max-width: 1024px) {
      .chat-container {
         grid-template-columns: 1fr;
         height: auto;
      }
      
      .conversations-list {
         max-height: 400px;
      }
      
      .chat-window {
         height: calc(100vh - 500px);
      }
   }
   
   @media (max-width: 768px) {
      .chat-container {
         margin: 10px;
         gap: 10px;
      }
      
      .message-content {
         max-width: 85%;
      }
      
      .chat-reply-form {
         flex-direction: column;
      }
      
      .chat-reply-form button {
         width: 100%;
         justify-content: center;
      }
   }
   </style>
</head>
<body>
   
<?php @include './header.php'; ?>

<section class="heading">
    <h3>Quản Lý Chat Tư Vấn</h3>
    <p><a href="page.php">Trang Chủ</a> / Chat</p>
</section>

<div class="chat-container">
   <!-- Conversations List -->
   <div class="conversations-list">
      <div class="conversations-header">
         <h3>
            <i class="fas fa-comments"></i> Cuộc Trò Chuyện
         </h3>
      </div>
      
      <div class="conversations-list-scroll">
         <?php if(empty($conversations)): ?>
            <div style="padding: 3rem; text-align: center; color: #adb5bd;">
               <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
               <p style="font-size: 1rem;">Chưa có tin nhắn nào</p>
            </div>
         <?php else: ?>
            <?php foreach($conversations as $conv): ?>
               <a href="?conversation_id=<?php echo $conv['id']; ?>" style="text-decoration: none; color: inherit;">
                  <div class="conversation-item <?php echo isset($_GET['conversation_id']) && $_GET['conversation_id'] == $conv['id'] ? 'active' : ''; ?>">
                     <div class="conversation-user">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($conv['user_name']); ?>
                     </div>
                     <div class="conversation-last-message">
                        <?php echo htmlspecialchars(substr($conv['last_message'] ?? 'Chưa có tin nhắn', 0, 50)); ?>
                     </div>
                     <?php if($conv['unread_count'] > 0): ?>
                        <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                     <?php endif; ?>
                  </div>
               </a>
            <?php endforeach; ?>
         <?php endif; ?>
      </div>
   </div>
   
   <!-- Chat Window -->
   <div class="chat-window">
      <?php if($selected_conversation): ?>
         <div class="chat-window-header">
            <!-- Top Section: Branding -->
            <div class="chat-header-top">
               <div class="chat-header-brand">
                  <i class="fas fa-comments"></i>
                  <div>
                     <h2>Chat Tư Vấn</h2>
                     <p>Hệ thống hỗ trợ khách hàng</p>
                  </div>
               </div>
               <div class="chat-header-actions">
                  <a href="page.php" class="header-action-btn">
                     <i class="fas fa-home"></i> Trang chủ
                  </a>
                  <a href="?close=<?php echo $selected_conversation['id']; ?>" 
                     class="delete-btn" 
                     onclick="return confirm('Bạn có chắc muốn đóng cuộc trò chuyện này?')">
                     <i class="fas fa-times-circle"></i> Đóng
                  </a>
               </div>
            </div>
            
            <!-- Bottom Section: User Info -->
            <div class="chat-header-bottom">
               <div class="header-user-info">
                  <div class="header-avatar">
                     <i class="fas fa-user"></i>
                  </div>
                  <div class="header-user-details">
                     <h3 class="header-user-name"><?php echo htmlspecialchars($selected_conversation['user_name']); ?></h3>
                     <p class="header-conversation-time">
                        <i class="fas fa-clock"></i>
                        Bắt đầu lúc <?php echo date('H:i - d/m/Y', strtotime($selected_conversation['created_at'])); ?>
                     </p>
                  </div>
               </div>
               <div class="status-indicator">
                  <i class="fas fa-circle"></i> Đang hoạt động
               </div>
            </div>
         </div>
         
         <div class="chat-messages-area" id="chatMessagesArea">
            <?php foreach($messages as $msg): ?>
               <div class="chat-message-item <?php echo $msg['sender_type']; ?>">
                  <div class="message-avatar">
                     <i class="fas fa-<?php echo $msg['sender_type'] === 'admin' ? 'headset' : 'user'; ?>"></i>
                  </div>
                  <div class="message-content">
                     <div class="message-bubble">
                        <p class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                     </div>
                     <div class="message-time">
                        <i class="far fa-clock"></i> <?php echo $msg['formatted_time']; ?>
                     </div>
                  </div>
               </div>
            <?php endforeach; ?>
         </div>
         
         <form class="chat-reply-form" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="conversation_id" value="<?php echo $selected_conversation['id']; ?>">
            <textarea name="message" placeholder="Nhập tin nhắn trả lời..." required></textarea>
            <button type="submit" name="send_reply">
               <i class="fas fa-paper-plane"></i> Gửi
            </button>
         </form>
      <?php else: ?>
         <div class="empty-state">
            <i class="fas fa-comments"></i>
            <h3>Chọn cuộc trò chuyện</h3>
            <p style="font-size: 1.5rem;">Chọn một cuộc trò chuyện bên trái để bắt đầu</p>
         </div>
      <?php endif; ?>
   </div>
</div>

<script src="../../js/admin_script.js"></script>
<script>
// Auto scroll to bottom
const chatArea = document.getElementById('chatMessagesArea');
if(chatArea) {
   chatArea.scrollTop = chatArea.scrollHeight;
}

// Tắt auto-reload khi đang nhập tin nhắn
let isTyping = false;
const messageInput = document.querySelector('textarea[name="message"]');

if(messageInput) {
   messageInput.addEventListener('focus', function() {
      isTyping = true;
   });
   
   messageInput.addEventListener('blur', function() {
      setTimeout(function() {
         isTyping = false;
      }, 1000);
   });
   
   // Ngan Enter submit, ch? Ctrl+Enter m?i g?i
   messageInput.addEventListener('keydown', function(e) {
      if(e.key === 'Enter' && !e.ctrlKey && !e.shiftKey) {
         e.preventDefault();
         // Submit form khi nh?n Enter
         const form = this.closest('form');
         if(form) {
            form.submit();
         }
      }
   });
}

// Auto refresh mỗi 15 giây, chỉ khi KHÔNG đang nhập
setInterval(function() {
   if(!isTyping && window.location.search.includes('conversation_id')) {
      location.reload();
   }
}, 15000);
</script>

</body>
</html>

