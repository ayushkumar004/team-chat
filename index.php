<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: chat.php');
    exit;
}

include 'includes/header.php';
?>

<div class="flex min-h-screen">
    <!-- Left side - Hero image/info -->
    <div class="hidden lg:flex lg:w-1/2 bg-primary-600 text-white p-12 flex-col justify-center items-center">
        <div class="max-w-md mx-auto">
            <div class="text-center mb-8">
                <i class="fas fa-comments text-5xl mb-4"></i>
                <h1 class="text-4xl font-bold mb-2">TeamChat</h1>
                <p class="text-xl text-primary-100">Real-time team communication made simple</p>
            </div>
            
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 shadow-lg">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-primary-300 flex items-center justify-center text-primary-800 font-bold mr-3">A</div>
                    <div class="flex-1">
                        <div class="bg-white/20 rounded-lg p-3 text-sm">
                            Hey team! How's the project coming along?
                        </div>
                        <div class="text-xs mt-1 text-primary-100">10:30 AM</div>
                    </div>
                </div>
                
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-primary-300 flex items-center justify-center text-primary-800 font-bold mr-3">B</div>
                    <div class="flex-1">
                        <div class="bg-white/20 rounded-lg p-3 text-sm">
                            Making great progress! Just finished the design phase.
                        </div>
                        <div class="text-xs mt-1 text-primary-100">10:32 AM</div>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-primary-300 flex items-center justify-center text-primary-800 font-bold mr-3">C</div>
                    <div class="flex-1">
                        <div class="bg-white/20 rounded-lg p-3 text-sm">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-file-pdf text-red-300 mr-2"></i>
                                <span class="text-white">project-update.pdf</span>
                            </div>
                            Here's the latest report for everyone to review!
                        </div>
                        <div class="text-xs mt-1 text-primary-100">10:35 AM</div>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 text-center text-primary-100">
                <p>Join thousands of teams already using TeamChat</p>
                <div class="flex justify-center mt-4 space-x-6">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fab fa-apple"></i>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fab fa-google"></i>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fab fa-microsoft"></i>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fab fa-amazon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right side - Login form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            <div class="text-center mb-10 lg:hidden">
                <i class="fas fa-comments text-4xl text-primary-600 mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-800">TeamChat</h1>
                <p class="text-gray-600">Real-time team communication</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Login to Your Account</h2>
                
                <div id="login-error" class="hidden bg-red-100 text-red-600 p-3 rounded-lg mb-4"></div>
                
                <form id="login-form" class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <a href="#" class="text-sm text-primary-600 hover:text-primary-800">Forgot password?</a>
                        </div>
                        <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                    </div>
                    
                    <div>
                        <button type="submit" id="login-button" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <span>Login</span>
                            <div id="login-spinner" class="ml-2 spinner hidden"></div>
                        </button>
                    </div>
                </form>
                
                <div class="mt-6 text-center">
                    <p class="text-gray-600">Don't have an account? 
                        <a href="register.php" class="text-primary-600 hover:text-primary-800 font-medium">Sign up</a>
                    </p>
                </div>
            </div>
            
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>© 2025 TeamChat. All rights reserved.</p>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/auth.js"></script>

<?php include 'includes/footer.php'; ?>