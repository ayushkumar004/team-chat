document.addEventListener('DOMContentLoaded', function() {
    // Login form handling
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginButton = document.getElementById('login-button');
            const loginSpinner = document.getElementById('login-spinner');
            const errorDiv = document.getElementById('login-error');
            
            // Validate form
            if (!email || !password) {
                showError(errorDiv, 'Please fill in all fields');
                return;
            }
            
            // Show loading state
            loginButton.disabled = true;
            loginSpinner.classList.remove('hidden');
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('email', email);
            formData.append('password', password);
            
            // Send login request
            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to chat page
                    window.location.href = 'chat.php';
                } else {
                    showError(errorDiv, data.message);
                    loginButton.disabled = false;
                    loginSpinner.classList.add('hidden');
                }
            })
            .catch(error => {
                showError(errorDiv, 'An error occurred. Please try again.');
                console.error('Login error:', error);
                loginButton.disabled = false;
                loginSpinner.classList.add('hidden');
            });
        });
    }
    
    // Registration form handling
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const registerButton = document.getElementById('register-button');
            const registerSpinner = document.getElementById('register-spinner');
            const errorDiv = document.getElementById('register-error');
            
            // Validate form
            if (!username || !email || !password || !confirmPassword) {
                showError(errorDiv, 'Please fill in all fields');
                return;
            }
            
            if (password !== confirmPassword) {
                showError(errorDiv, 'Passwords do not match');
                return;
            }
            
            // Show loading state
            registerButton.disabled = true;
            registerSpinner.classList.remove('hidden');
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'register');
            formData.append('username', username);
            formData.append('email', email);
            formData.append('password', password);
            formData.append('confirm_password', confirmPassword);
            
            // Send registration request
            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to chat page
                    window.location.href = 'chat.php';
                } else {
                    showError(errorDiv, data.message);
                    registerButton.disabled = false;
                    registerSpinner.classList.add('hidden');
                }
            })
            .catch(error => {
                showError(errorDiv, 'An error occurred. Please try again.');
                console.error('Registration error:', error);
                registerButton.disabled = false;
                registerSpinner.classList.add('hidden');
            });
        });
    }
    
    // Helper function to show error messages
    function showError(element, message) {
        element.textContent = message;
        element.classList.remove('hidden');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            element.classList.add('hidden');
        }, 5000);
    }
});