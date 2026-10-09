<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header Box -->
    <div class="header-box">
        <div class="header-text">Welcome to our online service</div>
    </div>
    
    <!-- Page Copyright -->
    <div class="page-copyright">© Suresh</div>
    
    <div class="container">
        <div class="form-container">
            <!-- Login Form -->
            <div class="form-wrapper" id="loginForm">
                <h2>Login</h2>
                <form method="POST" action="login_process.php">
                    <div class="input-group">
                        <input type="tel" name="phone" placeholder="Phone Number" required>
                        <i class="icon">📱</i>
                    </div>
                    <div class="input-group password-group">
                        <input type="password" name="password" id="loginPassword" placeholder="Password" required>
                        <i class="icon password-toggle" onclick="togglePassword('loginPassword', this)">👁️</i>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                <p class="switch-form">
                    Don't have an account? 
                    <a href="#" onclick="showRegister()">Register here</a>
                </p>
            </div>

            <!-- Register Form -->
            <div class="form-wrapper hidden" id="registerForm">
                <h2>Register</h2>
                <form method="POST" action="register_process.php">
                    <div class="input-group">
                        <input type="text" name="name" placeholder="Full Name" required>
                        <i class="icon">👤</i>
                    </div>
                    <div class="input-group">
                        <input type="tel" name="phone" placeholder="Phone Number" required>
                        <i class="icon">📱</i>
                    </div>
                    <div class="input-group">
                        <input type="text" name="address" placeholder="Address" required>
                        <i class="icon">🏠</i>
                    </div>
                    <div class="input-group password-group">
                        <input type="password" name="password" id="registerPassword" placeholder="Password" required>
                        <i class="icon password-toggle" onclick="togglePassword('registerPassword', this)">👁️</i>
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
                <p class="switch-form">
                    Already have an account? 
                    <a href="#" onclick="showLogin()">Login here</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Message Display -->
    <div id="message" class="message hidden"></div>

    <script>
        function showLogin() {
            document.getElementById('loginForm').classList.remove('hidden');
            document.getElementById('registerForm').classList.add('hidden');
        }

        function showRegister() {
            document.getElementById('loginForm').classList.add('hidden');
            document.getElementById('registerForm').classList.remove('hidden');
        }

        function togglePassword(inputId, iconElement) {
            const passwordInput = document.getElementById(inputId);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                iconElement.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                iconElement.textContent = '👁️';
            }
        }

        // Show any error messages from PHP session
        <?php if (isset($_SESSION['message'])): ?>
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = '<?php echo addslashes($_SESSION['message']); ?>';
            messageDiv.className = 'message <?php echo $_SESSION['message_type'] ?? 'error'; ?>';
            messageDiv.classList.remove('hidden');
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 5000);
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
