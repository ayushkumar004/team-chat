<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if file was uploaded without errors
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $channel_id = $_POST['channel_id'] ?? '';
        
        if (empty($channel_id)) {
            echo json_encode(['success' => false, 'message' => 'Channel ID is required']);
            exit;
        }
        
        // Create uploads directory if it doesn't exist
        $upload_dir = '../assets/uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $filename = time() . '_' . basename($file['name']);
        $filepath = $upload_dir . $filename;
        $relative_path = 'assets/uploads/' . $filename;
        
        // Check file size (limit to 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File size exceeds the limit (10MB)']);
            exit;
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            try {
                // Insert file message
                $stmt = $db->prepare("
                    INSERT INTO messages (channel_id, user_id, content, is_file, file_path)
                    VALUES (?, ?, ?, 1, ?)
                ");
                $stmt->execute([$channel_id, $_SESSION['user_id'], $file['name'], $relative_path]);
                
                $message_id = $db->lastInsertId();
                
                // Get user info
                $stmt = $db->prepare("SELECT id, username, avatar FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                echo json_encode([
                    'success' => true,
                    'message' => [
                        'id' => $message_id,
                        'content' => $file['name'],
                        'is_file' => true,
                        'file_path' => $relative_path,
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
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>