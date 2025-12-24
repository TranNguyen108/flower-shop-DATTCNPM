<?php
/**
 * Chat AJAX Handler
 * Handles chat messages between users and admins
 */

@include '../config.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Send message
if($action === 'send_message'){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        // For AJAX, generate token if not present
        if(!isset($_POST['csrf_token'])){
            $_POST['csrf_token'] = $_SESSION['csrf_token'];
        }
    }
    
    $message = sanitize_input($_POST['message'] ?? '');
    $conversation_id = isset($_POST['conversation_id']) && !empty($_POST['conversation_id']) ? (int)$_POST['conversation_id'] : null;
    
    if(empty($message)){
        echo json_encode(['success' => false, 'error' => 'Message is empty']);
        exit;
    }
    
    // Check if conversation exists
    if(!$conversation_id){
        // Create new conversation
        $insert_conversation = db_insert($conn,
            "INSERT INTO chat_conversations (user_id, user_name, last_message_at) VALUES (?, ?, NOW())",
            "is",
            [$user_id, $user_name]
        );
        
        $conversation_id = mysqli_insert_id($conn);
    } else {
        // Update existing conversation
        db_update($conn,
            "UPDATE chat_conversations SET last_message_at = NOW() WHERE id = ? AND user_id = ?",
            "ii",
            [$conversation_id, $user_id]
        );
    }
    
    // Insert message
    $insert_message = db_insert($conn,
        "INSERT INTO chat_messages (conversation_id, sender_type, sender_id, sender_name, message) 
         VALUES (?, 'user', ?, ?, ?)",
        "iiss",
        [$conversation_id, $user_id, $user_name, $message]
    );
    
    echo json_encode([
        'success' => true,
        'conversation_id' => $conversation_id,
        'message' => 'Message sent'
    ]);
    exit;
}

// Load messages
if($action === 'load_messages'){
    // Get user's conversation
    $conversation = db_select_array($conn,
        "SELECT * FROM chat_conversations WHERE user_id = ? ORDER BY last_message_at DESC LIMIT 1",
        "i",
        [$user_id]
    );
    
    if(empty($conversation)){
        echo json_encode(['success' => true, 'messages' => []]);
        exit;
    }
    
    $conversation_id = $conversation[0]['id'];
    
    // Get messages
    $messages = db_select_array($conn,
        "SELECT *, DATE_FORMAT(created_at, '%H:%i') as time 
         FROM chat_messages 
         WHERE conversation_id = ? 
         ORDER BY created_at ASC",
        "i",
        [$conversation_id]
    );
    
    // Mark admin messages as read
    db_update($conn,
        "UPDATE chat_messages SET is_read = 1 
         WHERE conversation_id = ? AND sender_type = 'admin' AND is_read = 0",
        "i",
        [$conversation_id]
    );
    
    echo json_encode([
        'success' => true,
        'conversation_id' => $conversation_id,
        'messages' => $messages
    ]);
    exit;
}

// Check new messages
if($action === 'check_new_messages'){
    $conversation_id = (int)$_GET['conversation_id'];
    $last_message_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
    
    // Get latest message ID from session if not provided
    if($last_message_id === 0 && isset($_SESSION['last_chat_message_id'])){
        $last_message_id = $_SESSION['last_chat_message_id'];
    }
    
    // Get new messages
    $new_messages = db_select_array($conn,
        "SELECT *, DATE_FORMAT(created_at, '%H:%i') as time 
         FROM chat_messages 
         WHERE conversation_id = ? AND id > ? AND sender_type = 'admin'
         ORDER BY created_at ASC",
        "ii",
        [$conversation_id, $last_message_id]
    );
    
    // Update last message ID
    if(!empty($new_messages)){
        $_SESSION['last_chat_message_id'] = $new_messages[count($new_messages) - 1]['id'];
        
        // Mark as read
        db_update($conn,
            "UPDATE chat_messages SET is_read = 1 
             WHERE conversation_id = ? AND sender_type = 'admin' AND is_read = 0",
            "i",
            [$conversation_id]
        );
    }
    
    // Get unread count
    $unread_count = db_count($conn,
        "SELECT COUNT(*) FROM chat_messages 
         WHERE conversation_id = ? AND sender_type = 'admin' AND is_read = 0",
        "i",
        [$conversation_id]
    );
    
    echo json_encode([
        'success' => true,
        'new_messages' => $new_messages,
        'unread_count' => $unread_count
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>

