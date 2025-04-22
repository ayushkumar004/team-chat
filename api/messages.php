<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

include '../includes/db.php';

// Get messages for a channel
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['channel_id'])) {
    $channel_id = $_GET['channel_id'];
    $last_id = $_GET['last_id'] ?? 0; // For polling new messages
    
    try {
        $stmt = $db->prepare("
            SELECT m.id, m.content, m.is_file, m.file_path, m.created_at, 
                   u.id as user_id, u.username, u.avatar
            FROM messages m
            JOIN users u ON m.user_id = u.id
            WHERE m.channel_id = ? AND m.id > ?
            ORDER BY m.created_at ASC
            LIMIT 50
        ");
        $stmt->execute([$channel_id, $last_id]);
        $messages = $stmt->fetchAll();
        
        $formatted_messages = [];
        foreach ($messages as $message) {
            $formatted_messages[] = [
                'id' => $message['id'],
                'content' => $message['content'],
                'is_file' => (bool)$message['is_file'],
                'file_path' => $message['file_path'],
                'time' => date('h:i A', strtotime($message['created_at'])),
                'date' => date('M d, Y', strtotime($message['created_at'])),
                'user' => [
                    'id' => $message['user_id'],
                    'username' => $message['username'],
                    'avatar' => $message['avatar'] ? $message['avatar'] : substr($message['username'], 0, 1)
                ]
            ];
        }
        
        echo json_encode(['success' => true, 'messages' => $formatted_messages]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Send a new message
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $channel_id = $_POST['channel_id'] ?? '';
    $content = $_POST['content'] ?? '';
    $is_file = isset($_POST['is_file']) ? (bool)$_POST['is_file'] : false;
    $file_path = $_POST['file_path'] ?? null;
    
    if (empty($channel_id) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Channel ID and content are required']);
        exit;
    }
    
    try {
        $stmt = $db->prepare("
            INSERT INTO messages (channel_id, user_id, content, is_file, file_path)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$channel_id, $_SESSION['user_id'], $content, $is_file, $file_path]);
        
        $message_id = $db->lastInsertId();
        
        // Get user info
        $stmt = $db->prepare("SELECT id, username, avatar FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => [
                'id' => $message_id,
                'content' => $content,
                'is_file' => $is_file,
                'file_path' => $file_path,
                'time' => date('h:i A'),
                'date' => date('M d, Y'),
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'avatar' => $user['avatar'] ? $user['avatar'] : substr($user['username'], 0, 1)
                ]
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>