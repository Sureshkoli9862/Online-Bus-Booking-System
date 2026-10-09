<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Component - Online System</title>
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
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Background decorative elements */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(-50px) translateY(-50px); }
        }

        .main-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 90%;
            position: relative;
            z-index: 1;
        }

        .panel-logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .panel-logo img {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            object-fit: cover;
        }

        .panel-logo::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
            100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        }

        .panel-title {
            color: #333;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .panel-subtitle {
            color: #666;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .panel-greeting {
            color: #f39c12;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .panel-description {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 35px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .feature-card {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            padding: 25px 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            background: rgba(102, 126, 234, 0.15);
            border-color: rgba(102, 126, 234, 0.3);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
        }

        .feature-icon {
            font-size: 40px;
            margin-bottom: 15px;
            display: block;
        }

        .feature-title {
            color: #333;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .feature-desc {
            color: #666;
            font-size: 12px;
            line-height: 1.4;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .panel-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        .panel-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
        }

        .panel-btn.secondary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .panel-btn.secondary:hover {
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .panel-btn::before {
            content: '→';
            font-size: 18px;
        }

        .panel-btn.secondary::before {
            content: '📊';
        }

        @media (max-width: 768px) {
            .main-panel {
                padding: 30px 20px;
                max-width: 95%;
            }
            
            .feature-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .panel-title {
                font-size: 28px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .panel-btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .panel-title {
                font-size: 24px;
            }
            
            .panel-subtitle {
                font-size: 16px;
            }
            
            .panel-greeting {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="main-panel">
        <div class="panel-logo">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='60' rx='10' fill='%23333'/%3E%3Crect x='10' y='10' width='80' height='40' rx='5' fill='%23667eea'/%3E%3Ccircle cx='20' cy='70' r='8' fill='%23333'/%3E%3Ccircle cx='80' cy='70' r='8' fill='%23333'/%3E%3C/svg%3E" alt="Bus Logo">
        </div>
        
        <h1 class="panel-title">Welcome to Our Online System</h1>
        <p class="panel-subtitle">Online Bus Ticket Booking</p>
        
        <p class="panel-greeting">Hello, suresh!</p>
        
        <p class="panel-description">
            Experience seamless bus ticket booking with our modern platform. Book your tickets easily and 
            securely with just a few clicks.
        </p>
        
        <div class="feature-grid">
            <div class="feature-card">
                <span class="feature-icon">📅</span>
                <div class="feature-title">Easy Booking</div>
                <div class="feature-desc">Quick and simple ticket booking process</div>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🔒</span>
                <div class="feature-title">Secure Payment</div>
                <div class="feature-desc">Safe and encrypted payment gateway</div>
            </div>
            <div class="feature-card">
                <span class="feature-icon">💬</span>
                <div class="feature-title">24/7 Support</div>
                <div class="feature-desc">Round the clock customer assistance</div>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="dashboard.php" class="panel-btn">Continue to Dashboard</a>
            <a href="my_bookings.php" class="panel-btn secondary">View Bookings</a>
        </div>
    </div>
</body>
</html>
