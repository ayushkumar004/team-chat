<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
include 'includes/db.php';

// Get user data
$stmt = $db->prepare("SELECT id, username, avatar FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get initial channel (default to general)
$activeChannelId = 1;
$stmt = $db->prepare("SELECT id, name FROM channels WHERE id = ?");
$stmt->execute([$activeChannelId]);
$activeChannel = $stmt->fetch();
?>

<div class="flex h-screen bg-gray-50">
    <!-- Mobile menu button -->
    <div class="fixed top-4 left-4 z-50 md:hidden">
        <button id="mobile-menu-button" class="bg-white p-2 rounded-lg shadow-md text-gray-700 hover:bg-gray-100">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Sidebar -->
    <div id="sidebar" class="bg-white w-64 border-r border-gray-200 flex-shrink-0 flex flex-col mobile-menu closed md:!transform-none fixed md:relative h-full z-40">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-comments text-primary-600 text-xl mr-2"></i>
                <h1 class="font-bold text-lg text-gray-800">TeamChat</h1>
            </div>
            <button id="mobile-close-button" class="md:hidden text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-4 border-b border-gray-200">
            <div class="relative">
                <input type="text" placeholder="Search" class="w-full pl-9 pr-4 py-2 bg-gray-100 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto">
            <div class="p-4">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Channels</h2>
                    <button id="new-channel-button" class="text-gray-500 hover:text-primary-600">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                
                <div id="channels-list" class="space-y-1">
                    <!-- Channels will be loaded here -->
                </div>
            </div>
        </div>
        
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-800 flex items-center justify-center font-bold mr-3">
                    <?php echo substr($user['username'], 0, 1); ?>
                </div>
                <div class="flex-1">
                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars($user['username']); ?></div>
                    <div class="text-xs text-gray-500">Online</div>
                </div>
                <button id="logout-button" class="text-gray-500 hover:text-gray-700" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Main content -->
    <div class="flex-1 flex flex-col">
        <!-- Channel header -->
        <div class="bg-white border-b border-gray-200 p-4 flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-hashtag text-gray-500 mr-2"></i>
                <h2 id="current-channel-name" class="font-semibold text-gray-800"><?php echo htmlspecialchars($activeChannel['name']); ?></h2>
            </div>
            <div class="flex items-center space-x-3">
                <button class="text-gray-500 hover:text-gray-700" title="Channel Members">
                    <i class="fas fa-users"></i>
                </button>
                <button class="text-gray-500 hover:text-gray-700" title="Search Messages">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        
        <!-- Messages area -->
        <div id="messages-container" class="flex-1 overflow-y-auto p-4 bg-gray-50">
            <!-- Messages will be loaded here -->
            <div class="flex justify-center">
                <div class="spinner"></div>
            </div>
        </div>
        
        <!-- Message input -->
        <div class="bg-white border-t border-gray-200 p-4">
            <div class="flex items-center space-x-2">
                <label for="file-upload" class="file-input-label p-2 rounded-lg text-gray-500 hover:text-primary-600" title="Upload File">
                    <i class="fas fa-paperclip"></i>
                    <input type="file" id="file-upload" class="hidden">
                </label>
                
                <div class="relative flex-1">
                    <textarea id="message-input" class="message-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none resize-none" placeholder="Type a message..." rows="1"></textarea>
                </div>
                
                <button id="send-message-button" class="p-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition duration-200">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- New Channel Modal -->
<div id="new-channel-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">Create a New Channel</h3>
            <button id="close-modal-button" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="channel-error" class="hidden bg-red-100 text-red-600 p-3 rounded-lg mb-4"></div>
        
        <form id="new-channel-form">
            <div class="mb-4">
                <label for="channel-name" class="block text-sm font-medium text-gray-700 mb-1">Channel Name</label>
                <input type="text" id="channel-name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="e.g. project-discussion" required>
                <p class="text-xs text-gray-500 mt-1">Channel names must be lowercase, without spaces, and shorter than 80 characters.</p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" id="cancel-channel-button" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" id="create-channel-button" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">Create Channel</button>
            </div>
        </form>
    </div>
</div>

<!-- File Upload Preview Modal -->
<div id="file-preview-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">Upload File</h3>
            <button id="close-file-modal-button" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="file-preview-container" class="mb-4">
            <div class="flex items-center p-3 bg-gray-100 rounded-lg">
                <i class="fas fa-file text-primary-600 text-2xl mr-3"></i>
                <div class="flex-1 overflow-hidden">
                    <p id="file-name" class="font-medium text-gray-800 truncate"></p>
                    <p id="file-size" class="text-sm text-gray-500"></p>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button type="button" id="cancel-upload-button" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="button" id="confirm-upload-button" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">Send File</button>
        </div>
    </div>
</div>

<script>
    // Store user data for JavaScript
    const currentUser = {
        id: <?php echo $user['id']; ?>,
        username: "<?php echo addslashes($user['username']); ?>",
        avatar: "<?php echo $user['avatar'] ? addslashes($user['avatar']) : substr($user['username'], 0, 1); ?>"
    };
    
    const activeChannelId = <?php echo $activeChannelId; ?>;
</script>

<script src="assets/js/chat.js"></script>

<?php include 'includes/footer.php'; ?>