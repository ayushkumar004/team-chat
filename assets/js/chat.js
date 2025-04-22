document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const sidebar = document.getElementById('sidebar');
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileCloseButton = document.getElementById('mobile-close-button');
    const channelsList = document.getElementById('channels-list');
    const messagesContainer = document.getElementById('messages-container');
    const messageInput = document.getElementById('message-input');
    const sendMessageButton = document.getElementById('send-message-button');
    const fileUpload = document.getElementById('file-upload');
    const logoutButton = document.getElementById('logout-button');
    const newChannelButton = document.getElementById('new-channel-button');
    const newChannelModal = document.getElementById('new-channel-modal');
    const closeModalButton = document.getElementById('close-modal-button');
    const cancelChannelButton = document.getElementById('cancel-channel-button');
    const newChannelForm = document.getElementById('new-channel-form');
    const filePreviewModal = document.getElementById('file-preview-modal');
    const closeFileModalButton = document.getElementById('close-file-modal-button');
    const cancelUploadButton = document.getElementById('cancel-upload-button');
    const confirmUploadButton = document.getElementById('confirm-upload-button');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    
    // State variables
    let channels = [];
    let activeChannelId = window.activeChannelId || 1; // From PHP, default to 1 if not defined
    let currentChannelId = activeChannelId; // From PHP
    let lastMessageId = 0;
    let selectedFile = null;
    let pollingInterval;
    let currentUser = window.currentUser || { id: 1, username: 'Guest', avatar: 'G' }; // From PHP, default guest user

    // Initialize chat
    init();
    
    // Initialize chat functionality
    function init() {
        // Mobile menu toggle
        mobileMenuButton.addEventListener('click', toggleMobileMenu);
        mobileCloseButton.addEventListener('click', toggleMobileMenu);
        
        // Load channels
        loadChannels();
        
        // Load initial messages
        loadMessages(currentChannelId);
        
        // Set up event listeners
        setupEventListeners();
        
        // Start polling for new messages
        startPolling();
    }
    
    // Toggle mobile menu
    function toggleMobileMenu() {
        sidebar.classList.toggle('closed');
    }
    
    // Load all channels
    function loadChannels() {
        fetch('api/channels.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    channels = data.channels;
                    renderChannels();
                }
            })
            .catch(error => console.error('Error loading channels:', error));
    }
    
    // Render channels list
    function renderChannels() {
        channelsList.innerHTML = '';
        
        channels.forEach(channel => {
            const channelElement = document.createElement('div');
            channelElement.className = `channel-item flex items-center p-2 rounded-lg cursor-pointer ${channel.id === currentChannelId ? 'active' : ''}`;
            channelElement.dataset.id = channel.id;
            
            channelElement.innerHTML = `
                <i class="fas fa-hashtag text-gray-500 mr-2"></i>
                <span class="text-gray-800">${channel.name}</span>
            `;
            
            channelElement.addEventListener('click', () => {
                // Update active channel
                document.querySelectorAll('.channel-item').forEach(el => el.classList.remove('active'));
                channelElement.classList.add('active');
                
                // Update current channel
                currentChannelId = channel.id;
                document.getElementById('current-channel-name').textContent = channel.name;
                
                // Load messages for this channel
                loadMessages(channel.id);
                
                // Close mobile menu if open
                if (window.innerWidth < 768) {
                    sidebar.classList.add('closed');
                }
            });
            
            channelsList.appendChild(channelElement);
        });
    }
    
    // Load messages for a channel
    function loadMessages(channelId) {
        // Show loading spinner
        messagesContainer.innerHTML = '<div class="flex justify-center"><div class="spinner"></div></div>';
        
        fetch(`api/messages.php?channel_id=${channelId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderMessages(data.messages);
                    
                    // Update last message ID for polling
                    if (data.messages.length > 0) {
                        lastMessageId = Math.max(...data.messages.map(msg => msg.id));
                    }
                }
            })
            .catch(error => console.error('Error loading messages:', error));
    }
    
    // Render messages
    function renderMessages(messages) {
        messagesContainer.innerHTML = '';
        
        if (messages.length === 0) {
            messagesContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full text-gray-500">
                    <i class="fas fa-comments text-4xl mb-4"></i>
                    <p>No messages yet. Be the first to send a message!</p>
                </div>
            `;
            return;
        }
        
        let currentDate = '';
        
        messages.forEach(message => {
            // Add date separator if needed
            if (message.date !== currentDate) {
                currentDate = message.date;
                const dateSeparator = document.createElement('div');
                dateSeparator.className = 'flex items-center my-4';
                dateSeparator.innerHTML = `
                    <div class="flex-1 border-t border-gray-300"></div>
                    <div class="mx-4 text-xs text-gray-500">${message.date}</div>
                    <div class="flex-1 border-t border-gray-300"></div>
                `;
                messagesContainer.appendChild(dateSeparator);
            }
            
            const messageElement = document.createElement('div');
            messageElement.className = 'flex mb-4 message-animation';
            messageElement.dataset.id = message.id;
            
            const isCurrentUser = message.user.id === currentUser.id;
            
            messageElement.innerHTML = `
                <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-800 flex items-center justify-center font-bold mr-3 flex-shrink-0">
                    ${message.user.avatar}
                </div>
                <div class="flex-1">
                    <div class="flex items-baseline">
                        <span class="font-medium text-gray-800">${message.user.username}</span>
                        <span class="ml-2 text-xs text-gray-500">${message.time}</span>
                    </div>
                    ${message.is_file ? renderFileMessage(message) : `<p class="mt-1 text-gray-700">${message.content}</p>`}
                </div>
            `;
            
            messagesContainer.appendChild(messageElement);
        });
        
        // Scroll to bottom
        scrollToBottom();
    }
    
    // Render file message
    function renderFileMessage(message) {
        const fileExtension = message.content.split('.').pop().toLowerCase();
        let fileIcon = 'fa-file';
        let fileClass = 'text-gray-500';
        
        // Determine file icon based on extension
        if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
            fileIcon = 'fa-file-image';
            fileClass = 'text-green-500';
        } else if (['pdf'].includes(fileExtension)) {
            fileIcon = 'fa-file-pdf';
            fileClass = 'text-red-500';
        } else if (['doc', 'docx'].includes(fileExtension)) {
            fileIcon = 'fa-file-word';
            fileClass = 'text-blue-500';
        } else if (['xls', 'xlsx'].includes(fileExtension)) {
            fileIcon = 'fa-file-excel';
            fileClass = 'text-green-600';
        } else if (['zip', 'rar'].includes(fileExtension)) {
            fileIcon = 'fa-file-archive';
            fileClass = 'text-orange-500';
        }
        
        return `
            <div class="mt-1 p-3 bg-gray-100 rounded-lg inline-block">
                <div class="flex items-center">
                    <i class="fas ${fileIcon} ${fileClass} text-xl mr-3"></i>
                    <div>
                        <a href="${message.file_path}" target="_blank" class="text-primary-600 hover:underline font-medium">${message.content}</a>
                        <p class="text-xs text-gray-500">Click to download</p>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Send a message
    function sendMessage() {
        const content = messageInput.value.trim();
        if (!content) return;
        
        const formData = new FormData();
        formData.append('action', 'send');
        formData.append('channel_id', currentChannelId);
        formData.append('content', content);
        
        fetch('api/messages.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear input
                messageInput.value = '';
                
                // Add message to UI
                appendMessage(data.message);
                
                // Update last message ID
                lastMessageId = data.message.id;
            }
        })
        .catch(error => console.error('Error sending message:', error));
    }
    
    // Append a single message to the container
    function appendMessage(message) {
        const messageElement = document.createElement('div');
        messageElement.className = 'flex mb-4 message-animation';
        messageElement.dataset.id = message.id;
        
        messageElement.innerHTML = `
            <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-800 flex items-center justify-center font-bold mr-3 flex-shrink-0">
                ${message.user.avatar}
            </div>
            <div class="flex-1">
                <div class="flex items-baseline">
                    <span class="font-medium text-gray-800">${message.user.username}</span>
                    <span class="ml-2 text-xs text-gray-500">${message.time}</span>
                </div>
                ${message.is_file ? renderFileMessage(message) : `<p class="mt-1 text-gray-700">${message.content}</p>`}
            </div>
        `;
        
        messagesContainer.appendChild(messageElement);
        
        // Scroll to bottom
        scrollToBottom();
    }
    
    // Scroll messages container to bottom
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Start polling for new messages
    function startPolling() {
        // Clear any existing interval
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
        
        // Poll every 3 seconds
        pollingInterval = setInterval(() => {
            if (currentChannelId) {
                pollNewMessages();
            }
        }, 3000);
    }
    
    // Poll for new messages
    function pollNewMessages() {
        fetch(`api/messages.php?channel_id=${currentChannelId}&last_id=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages.length > 0) {
                    data.messages.forEach(message => {
                        appendMessage(message);
                        
                        // Update last message ID
                        if (message.id > lastMessageId) {
                            lastMessageId = message.id;
                        }
                    });
                }
            })
            .catch(error => console.error('Error polling messages:', error));
    }
    
    // Create a new channel
    function createChannel() {
        const channelName = document.getElementById('channel-name').value.trim();
        if (!channelName) return;
        
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('name', channelName);
        
        fetch('api/channels.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                newChannelModal.classList.add('hidden');
                
                // Add channel to list
                channels.push(data.channel);
                
                // Re-render channels
                renderChannels();
                
                // Switch to new channel
                currentChannelId = data.channel.id;
                document.getElementById('current-channel-name').textContent = data.channel.name;
                loadMessages(data.channel.id);
                
                // Clear input
                document.getElementById('channel-name').value = '';
            } else {
                // Show error
                const errorDiv = document.getElementById('channel-error');
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    errorDiv.classList.add('hidden');
                }, 5000);
            }
        })
        .catch(error => console.error('Error creating channel:', error));
    }
    
    // Handle file upload
    function handleFileUpload() {
        if (!selectedFile) return;
        
        const formData = new FormData();
        formData.append('file', selectedFile);
        formData.append('channel_id', currentChannelId);
        
        fetch('api/upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                filePreviewModal.classList.add('hidden');
                
                // Add message to UI
                appendMessage(data.message);
                
                // Update last message ID
                lastMessageId = data.message.id;
                
                // Reset selected file
                selectedFile = null;
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error uploading file:', error));
    }
    
    // Format file size
    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' bytes';
        else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        else return (bytes / 1048576).toFixed(1) + ' MB';
    }
    
    // Set up event listeners
    function setupEventListeners() {
        // Send message on button click
        sendMessageButton.addEventListener('click', sendMessage);
        
        // Send message on Enter key
        messageInput.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        
        // Auto-resize textarea
        messageInput.addEventListener('input', () => {
            messageInput.style.height = 'auto';
            messageInput.style.height = (messageInput.scrollHeight) + 'px';
        });
        
        // File upload
        fileUpload.addEventListener('change', e => {
            if (e.target.files.length > 0) {
                selectedFile = e.target.files[0];
                
                // Show file preview modal
                fileName.textContent = selectedFile.name;
                fileSize.textContent = formatFileSize(selectedFile.size);
                filePreviewModal.classList.remove('hidden');
            }
        });
        
        // Confirm file upload
        confirmUploadButton.addEventListener('click', handleFileUpload);
        
        // Cancel file upload
        cancelUploadButton.addEventListener('click', () => {
            filePreviewModal.classList.add('hidden');
            selectedFile = null;
        });
        
        // Close file modal
        closeFileModalButton.addEventListener('click', () => {
            filePreviewModal.classList.add('hidden');
            selectedFile = null;
        });
        
        // New channel modal
        newChannelButton.addEventListener('click', () => {
            newChannelModal.classList.remove('hidden');
        });
        
        // Close channel modal
        closeModalButton.addEventListener('click', () => {
            newChannelModal.classList.add('hidden');
        });
        
        // Cancel channel creation
        cancelChannelButton.addEventListener('click', () => {
            newChannelModal.classList.add('hidden');
        });
        
        // Create channel form
        newChannelForm.addEventListener('submit', e => {
            e.preventDefault();
            createChannel();
        });
        
        // Logout
        logoutButton.addEventListener('click', () => {
            const formData = new FormData();
            formData.append('action', 'logout');
            
            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.php';
                }
            })
            .catch(error => console.error('Error logging out:', error));
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', e => {
            if (e.target === newChannelModal) {
                newChannelModal.classList.add('hidden');
            }
            if (e.target === filePreviewModal) {
                filePreviewModal.classList.add('hidden');
                selectedFile = null;
            }
        });
    }
});