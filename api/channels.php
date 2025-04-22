<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

include '../includes/db.php';

// Get all channels
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $db->prepare("
            SELECT c.id, c.name, c.created_at, u.username as created_by,
                   (SELECT COUNT(*) FROM messages WHERE channel_id = c.id) as message_count
            FROM channels c
            LEFT JOIN users u ON c.created_by = u.id
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        $channels = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'channels' => $channels]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Create a new channel
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = $_POST['name'] ?? '';
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Channel name is required']);
        exit;
    }
    
    // Format channel name (lowercase, replace spaces with hyphens)
    $name = strtolower(preg_replace('/\s+/', '-', trim($name)));
    
    try {
        // Check if channel already exists
        $stmt = $db->prepare("SELECT id FROM channels WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Channel already exists']);
            exit;
        }
        
        // Create new channel
        $stmt = $db->prepare("INSERT INTO channels (name, created_by) VALUES (?, ?)");
        $stmt->execute([$name, $_SESSION['user_id']]);
        
        $channel_id = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'channel' => [
                'id' => $channel_id,
                'name' => $name,
                'created_by' => $_SESSION['username'],
                'message_count' => 0
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