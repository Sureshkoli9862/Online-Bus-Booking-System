<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Online Bus Booking System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .admin-login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
        }

        .login-header {
            background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        }

        .admin-icon {
            font-size: 48px;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .login-subtitle {
            font-size: 14px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .login-form {
            padding: 40px 30px;
            background: linear-gradient(to bottom, #f8f9ff 0%, #e8f0ff 100%);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #e91e63;
            box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(233, 30, 99, 0.3);
            margin-top: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(233, 30, 99, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .back-to-site {
            text-align: center;
            padding: 20px;
            background: white;
        }

        .back-link {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #e91e63;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #f44336;
        }

        @media (max-width: 480px) {
            .admin-login-container {
                max-width: 100%;
                margin: 10px;
            }
            
            .login-form {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="login-header">
            <div class="admin-icon">👨‍💼</div>
            <h1>Admin Login</h1>
            <div class="login-subtitle">Access Administrative Dashboard</div>
        </div>
        
        <form class="login-form" method="POST" action="admin_dashboard.php">
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    Invalid admin credentials. Please try again.
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label class="form-label" for="admin_username">Admin Username</label>
                <input type="text" id="admin_username" name="admin_username" class="form-input" placeholder="Enter admin username" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="admin_password">Admin Password</label>
                <input type="password" id="admin_password" name="admin_password" class="form-input" placeholder="Enter admin password" required>
            </div>
            
            <button type="submit" class="login-btn">
                🔐 Login to Dashboard
            </button>
        </form>
        
        <div class="back-to-site">
            <a href="index.php" class="back-link">← Back to Main Site</a>
        </div>
    </div>

    <script>
        // Simple client-side validation
        document.querySelector('.login-form').addEventListener('submit', function(e) {
            const username = document.getElementById('admin_username').value;
            const password = document.getElementById('admin_password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return;
            }
        });
    </script>
</body>
</html>
