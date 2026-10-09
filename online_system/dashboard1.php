<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch user data and statistics
try {
    $pdo = getDBConnection();
    
    // Get user info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Get booking statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_bookings FROM bookings WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $totalBookings = $stmt->fetch()['total_bookings'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as upcoming_bookings FROM bookings WHERE user_id = ? AND booking_status = 'upcoming' AND travel_date >= CURDATE()");
    $stmt->execute([$_SESSION['user_id']]);
    $upcomingBookings = $stmt->fetch()['upcoming_bookings'];
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total_spent FROM bookings WHERE user_id = ? AND payment_status = 'confirmed'");
    $stmt->execute([$_SESSION['user_id']]);
    $totalSpent = $stmt->fetch()['total_spent'];
    
    // Get recent bookings
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $recentBookings = $stmt->fetchAll();
    
} catch (Exception $e) {
    $user = ['username' => 'User', 'name' => 'User'];
    $totalBookings = 0;
    $upcomingBookings = 0;
    $totalSpent = 0;
    $recentBookings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Online Bus Booking</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            max-width: 100vw;
        }

        /* Background decorative elements */
        .main-content::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            animation: float 20s infinite linear;
            z-index: -1;
        }

        @keyframes float {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(-50px) translateY(-50px); }
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
        }

        .sidebar-title {
            color: white;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .sidebar-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }

        .sidebar-header h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 14px;
            color: #bdc3c7;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: 5px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar-nav a i {
            margin-right: 10px;
            font-size: 16px;
        }


        /* Main Content */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            overflow-x: hidden;
            max-width: calc(100vw - 250px);
            box-sizing: border-box;
        }

        /* Custom Scrollbar */
        .main-content::-webkit-scrollbar {
            width: 8px;
        }

        .main-content::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .main-content::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        .main-content::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Scroll to Top Button */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
            z-index: 1000;
        }

        .scroll-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .scroll-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.5);
        }

        .page-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .page-title {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
        }

        /* Booking Form */
        .booking-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .booking-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 500;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            background: white;
        }

        select.form-control {
            cursor: pointer;
        }

        .next-btn {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            margin: 30px auto 0;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .next-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
                max-width: 100vw;
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                flex-direction: column;
                gap: 15px;
            }
        }

        @media (max-width: 640px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .main-content {
                margin-left: 0;
            }
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            width: 100%;
            box-sizing: border-box;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            width: 100%;
            box-sizing: border-box;
            min-width: 0;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 32px;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-sublabel {
            color: #95a5a6;
            font-size: 12px;
            margin-top: 5px;
        }

        /* Booking Sections */
        .booking-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }

        .section-box {
            background: linear-gradient(145deg, #ffffff 0%, #f8fbff 100%);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 12px 35px rgba(66, 133, 244, 0.2);
            border: 3px solid #e3f2fd;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            min-height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .section-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #4285f4, #1976d2, #42a5f5);
        }

        .section-box:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 45px rgba(135, 206, 235, 0.3);
            border-color: #87ceeb;
        }

        .section-box.upcoming::before {
            background: linear-gradient(90deg, #ff9800, #ffc107, #ffeb3b);
        }

        .section-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 28px;
            font-weight: 700;
            color: #1976d2;
            margin-bottom: 15px;
        }

        .section-description {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .section-count {
            font-size: 48px;
            font-weight: 800;
            color: #ff5722;
            margin-bottom: 10px;
        }

        .section-label {
            font-size: 14px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Profile Card */
        .profile-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fbff 100%);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 12px 35px rgba(66, 133, 244, 0.2);
            border: 3px solid #e3f2fd;
            text-align: center;
            position: relative;
            overflow: hidden;
            max-width: 600px;
            margin: 0 auto;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #4285f4, #1976d2, #42a5f5);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: white;
            margin: 0 auto 25px;
            box-shadow: 0 8px 25px rgba(66, 133, 244, 0.3);
        }

        .profile-name {
            font-size: 32px;
            font-weight: 700;
            color: #1976d2;
            margin-bottom: 10px;
        }

        .profile-email {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .profile-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-box {
            background: #f0f7ff;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #4285f4;
        }

        .detail-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .detail-value {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }

        .edit-btn {
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(66, 133, 244, 0.3);
        }

        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(66, 133, 244, 0.4);
        }

        /* Help Grid */
        .help-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .help-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fbff 100%);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(66, 133, 244, 0.15);
            border: 3px solid #e3f2fd;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .help-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(135, 206, 235, 0.3);
        }

        .help-icon {
            font-size: 50px;
            margin-bottom: 20px;
            text-align: center;
        }

        .help-title {
            font-size: 22px;
            font-weight: 700;
            color: #1976d2;
            margin-bottom: 15px;
            text-align: center;
        }

        .help-description {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
            text-align: center;
            margin-bottom: 20px;
        }

        .contact-info {
            background: #f0f7ff;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #4285f4;
            font-size: 14px;
            color: #2c3e50;
        }

        .faq-section {
            background: linear-gradient(145deg, #ffffff 0%, #f8fbff 100%);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(66, 133, 244, 0.15);
            border: 3px solid #e3f2fd;
        }

        .faq-title {
            font-size: 24px;
            font-weight: 700;
            color: #1976d2;
            margin-bottom: 25px;
            text-align: center;
        }

        .faq-item {
            margin-bottom: 20px;
            padding: 15px;
            background: #f0f7ff;
            border-radius: 8px;
            border-left: 4px solid #4285f4;
        }

        .faq-question {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .faq-answer {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }

        .content-section {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Journey Details */
        .journey-details {
            margin-top: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #e8f4fd 0%, #f0f7ff 100%);
            border-radius: 12px;
            border-left: 4px solid #3498db;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.1);
        }

        .journey-details h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 20px;
            text-align: center;
        }

        .journey-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .info-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-value {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }

        .next-btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-title">Dashboard</div>
            <div class="sidebar-subtitle">Welcome, <?php echo htmlspecialchars($user['name'] ?? $user['username']); ?></div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="dashboard1.php" class="active"><i>🏠</i> Dashboard</a></li>
            <li><a href="online_ticket.php"><i>🎫</i> Online Ticket</a></li>
            <li><a href="my_bookings_new.php"><i>📋</i> My Bookings</a></li>
            <li><a href="profile.php"><i>👤</i> Profile</a></li>
            <li><a href="help_support.php"><i>❓</i> Help & Support</a></li>
            <li><a href="logout.php"><i>🚪</i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Welcome to Dashboard</h1>
            <p class="page-subtitle">Your central hub for managing bus bookings and account</p>
        </div>
            
            <div class="dashboard-stats">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number"><?php echo $totalBookings; ?></div>
                <div class="stat-label">TOTAL BOOKINGS</div>
                <div class="stat-sublabel">All your bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🚀</div>
                <div class="stat-number"><?php echo $upcomingBookings; ?></div>
                <div class="stat-label">ACTIVE TICKETS</div>
                <div class="stat-sublabel">Current bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number"><?php echo number_format($totalSpent); ?></div>
                <div class="stat-label">TOTAL SPENT (RS.)</div>
                <div class="stat-sublabel">Travel history</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📱</div>
                <div class="stat-number"><?php echo htmlspecialchars($user['phone'] ?? '9862463805'); ?></div>
                <div class="stat-label">PHONE NUMBER</div>
                <div class="stat-sublabel">Contact info</div>
            </div>
        </div>
                </div>
            </div>
        </div>

        <!-- Online Ticket Section -->
        <div id="online-ticket-section" class="content-section" style="display: none;">
            <div class="page-header">
                <h1>Online Bus Ticket Booking</h1>
                <p>Book your bus tickets easily and securely</p>
            </div>

            <!-- Booking Form -->
            <div class="booking-section">
                <h2 class="booking-title">Book Your Ticket</h2>
                
                <form id="booking-form" action="bus_selection.php" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="from">From</label>
                            <select id="from" name="from" class="form-control" required>
                                <option value="">Select departure location</option>
                                <option value="kanchanpur">Kanchanpur</option>
                                <option value="dhangadi">Dhangadi</option>
                                <option value="bhasi">Bhasi</option>
                                <option value="attariya">Attariya</option>
                                <option value="dadeldhura">Dadeldhura</option>
                                <option value="baitadi">Baitadi</option>
                                <option value="darchula">Darchula</option>
                                <option value="aacham">Aacham</option>
                                <option value="bajhang">Bajhang</option>
                                <option value="patan">Patan</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="to">To</label>
                            <select id="to" name="to" class="form-control" required>
                                <option value="">Select destination</option>
                                <option value="kathmandu">Kathmandu</option>
                                <option value="nepaljung">Nepaljung</option>
                                <option value="pokhara">Pokhara</option>
                                <option value="lalitpur">Lalitpur</option>
                                <option value="bhaktapur">Bhaktapur</option>
                                <option value="jhapa">Jhapa</option>
                                <option value="iran">Iran</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="travel-date">Travel Date</label>
                            <input type="date" id="travel-date" name="travel_date" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="time">Preferred Time</label>
                            <select id="time" name="preferred_time" class="form-control" required>
                                <option value="">Select time</option>
                                <option value="06:00 AM">06:00 AM</option>
                                <option value="08:00 AM">08:00 AM</option>
                                <option value="10:00 AM">10:00 AM</option>
                                <option value="12:00 PM">12:00 PM</option>
                                <option value="02:00 PM">02:00 PM</option>
                                <option value="04:00 PM">04:00 PM</option>
                                <option value="06:00 PM">06:00 PM</option>
                                <option value="08:00 PM">08:00 PM</option>
                                <option value="10:00 PM">10:00 PM</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Journey Details Display -->
                    <div id="journey-details" class="journey-details" style="display: none;">
                        <h3>Journey Details</h3>
                        <div class="journey-info">
                            <div class="info-box">
                                <div class="info-label">Estimated Arrival</div>
                                <div id="arrival-time" class="info-value">--:-- --</div>
                            </div>
                            <div class="info-box">
                                <div class="info-label">Journey Duration</div>
                                <div id="duration" class="info-value">-- hours</div>
                            </div>
                            <div class="info-box">
                                <div class="info-label">Distance</div>
                                <div id="distance" class="info-value">-- km</div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" id="next-btn" class="next-btn" disabled>Next</button>
                </form>
            </div>
        </div>

        <!-- My Bookings Section -->
        <div id="my-bookings-section" class="content-section" style="display: none;">
            <div class="page-header">
                <h1>My Bookings</h1>
                <p>View and manage your bus ticket bookings</p>
            </div>
            
            <div class="booking-sections">
                <div class="section-box" onclick="window.location.href='booking_information.php'">
                    <div class="section-icon">🎫</div>
                    <div class="section-title">Current Bookings</div>
                    <div class="section-description">View your active and upcoming bookings</div>
                    <div class="section-count">3</div>
                    <div class="section-label">Active Tickets</div>
                </div>
                
                <div class="section-box upcoming" onclick="window.location.href='booking_information.php'">
                    <div class="section-icon">📅</div>
                    <div class="section-title">Booking History</div>
                    <div class="section-description">View your past travel history</div>
                    <div class="section-count">12</div>
                    <div class="section-label">Completed Trips</div>
                </div>
            </div>
        </div>

        <!-- Profile Section -->
        <div id="profile-section" class="content-section" style="display: none;">
            <div class="page-header">
                <h1>User Profile</h1>
                <p>Manage your account information</p>
            </div>
            
            <div class="profile-card">
                <div class="profile-avatar">👤</div>
                <div class="profile-name">User</div>
                <div class="profile-email">user@example.com</div>
                
                <div class="profile-details">
                    <div class="detail-box">
                        <div class="detail-label">Member Since</div>
                        <div class="detail-value">January 2025</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Total Bookings</div>
                        <div class="detail-value" id="totalBookingsCount">0</div>
                    </div>
                </div>
                
                <button class="edit-btn" onclick="window.location.href='profile.php'">Edit Profile</button>
            </div>
        </div>

        <!-- Help & Support Section -->
        <div id="help-support-section" class="content-section" style="display: none;">
            <div class="page-header">
                <h1>Help & Support</h1>
                <p>Get help with your bookings</p>
            </div>
            
            <div class="help-grid">
                <div class="help-card">
                    <div class="help-icon">📞</div>
                    <div class="help-title">Contact Support</div>
                    <div class="help-description">Get help from our team</div>
                    <div class="contact-info">
                        📧 support@busticket.com<br>
                        📱 +977-1-234-5678
                    </div>
                </div>
                
                <div class="help-card">
                    <div class="help-icon">💬</div>
                    <div class="help-title">Live Chat</div>
                    <div class="help-description">Chat with support team</div>
                    <div class="contact-info">
                        Available 24/7<br>
                        Quick response
                    </div>
                </div>
            </div>
            
            <div class="faq-section">
                <div class="faq-title">Common Questions</div>
                
                <div class="faq-item">
                    <div class="faq-question">How to cancel booking?</div>
                    <div class="faq-answer">Cancel from My Bookings section before 2 hours of departure.</div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">Payment methods?</div>
                    <div class="faq-answer">We accept cards, mobile banking, and digital wallets.</div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">Change travel date?</div>
                    <div class="faq-answer">Yes, you can change date 24 hours before travel with small fee.</div>
                </div>
            </div>
    </div>

    <script>
        // Set minimum date to today
        document.getElementById('travel-date').min = new Date().toISOString().split('T')[0];
        
        // Nepal city distance and duration data (in hours)
        const cityData = {
            'kanchanpur-kathmandu': { distance: 720, duration: 12 },
            'kanchanpur-nepaljung': { distance: 180, duration: 4 },
            'kanchanpur-pokhara': { distance: 580, duration: 10 },
            'kanchanpur-lalitpur': { distance: 725, duration: 12 },
            'kanchanpur-bhaktapur': { distance: 730, duration: 12 },
            'kanchanpur-jhapa': { distance: 890, duration: 15 },
            'kanchanpur-iran': { distance: 950, duration: 16 },
            'dhangadi-kathmandu': { distance: 650, duration: 11 },
            'dhangadi-nepaljung': { distance: 120, duration: 3 },
            'dhangadi-pokhara': { distance: 520, duration: 9 },
            'dhangadi-lalitpur': { distance: 655, duration: 11 },
            'dhangadi-bhaktapur': { distance: 660, duration: 11 },
            'dhangadi-jhapa': { distance: 820, duration: 14 },
            'dhangadi-iran': { distance: 880, duration: 15 },
            'bhasi-kathmandu': { distance: 580, duration: 10 },
            'bhasi-nepaljung': { distance: 85, duration: 2 },
            'bhasi-pokhara': { distance: 450, duration: 8 },
            'bhasi-lalitpur': { distance: 585, duration: 10 },
            'bhasi-bhaktapur': { distance: 590, duration: 10 },
            'bhasi-jhapa': { distance: 750, duration: 13 },
            'bhasi-iran': { distance: 810, duration: 14 },
            'attariya-kathmandu': { distance: 620, duration: 10 },
            'attariya-nepaljung': { distance: 95, duration: 2 },
            'attariya-pokhara': { distance: 490, duration: 8 },
            'attariya-lalitpur': { distance: 625, duration: 10 },
            'attariya-bhaktapur': { distance: 630, duration: 11 },
            'attariya-jhapa': { distance: 790, duration: 13 },
            'attariya-iran': { distance: 850, duration: 14 },
            'dadeldhura-kathmandu': { distance: 780, duration: 13 },
            'dadeldhura-nepaljung': { distance: 250, duration: 5 },
            'dadeldhura-pokhara': { distance: 650, duration: 11 },
            'dadeldhura-lalitpur': { distance: 785, duration: 13 },
            'dadeldhura-bhaktapur': { distance: 790, duration: 13 },
            'dadeldhura-jhapa': { distance: 950, duration: 16 },
            'dadeldhura-iran': { distance: 1010, duration: 17 },
            'baitadi-kathmandu': { distance: 820, duration: 14 },
            'baitadi-nepaljung': { distance: 290, duration: 6 },
            'baitadi-pokhara': { distance: 690, duration: 12 },
            'baitadi-lalitpur': { distance: 825, duration: 14 },
            'baitadi-bhaktapur': { distance: 830, duration: 14 },
            'baitadi-jhapa': { distance: 990, duration: 17 },
            'baitadi-iran': { distance: 1050, duration: 18 },
            'darchula-kathmandu': { distance: 890, duration: 15 },
            'darchula-nepaljung': { distance: 360, duration: 7 },
            'darchula-pokhara': { distance: 760, duration: 13 },
            'darchula-lalitpur': { distance: 895, duration: 15 },
            'darchula-bhaktapur': { distance: 900, duration: 15 },
            'darchula-jhapa': { distance: 1060, duration: 18 },
            'darchula-iran': { distance: 1120, duration: 19 },
            'aacham-kathmandu': { distance: 750, duration: 13 },
            'aacham-nepaljung': { distance: 220, duration: 4 },
            'aacham-pokhara': { distance: 620, duration: 11 },
            'aacham-lalitpur': { distance: 755, duration: 13 },
            'aacham-bhaktapur': { distance: 760, duration: 13 },
            'aacham-jhapa': { distance: 920, duration: 16 },
            'aacham-iran': { distance: 980, duration: 17 },
            'bajhang-kathmandu': { distance: 810, duration: 14 },
            'bajhang-nepaljung': { distance: 280, duration: 5 },
            'bajhang-pokhara': { distance: 680, duration: 12 },
            'bajhang-lalitpur': { distance: 815, duration: 14 },
            'bajhang-bhaktapur': { distance: 820, duration: 14 },
            'bajhang-jhapa': { distance: 980, duration: 17 },
            'bajhang-iran': { distance: 1040, duration: 18 },
            'patan-kathmandu': { distance: 8, duration: 1 },
            'patan-nepaljung': { distance: 520, duration: 9 },
            'patan-pokhara': { distance: 200, duration: 4 },
            'patan-lalitpur': { distance: 5, duration: 1 },
            'patan-bhaktapur': { distance: 15, duration: 1 },
            'patan-jhapa': { distance: 480, duration: 8 },
            'patan-iran': { distance: 540, duration: 9 }
        };
        
        function calculateJourney() {
            const from = document.getElementById('from').value;
            const to = document.getElementById('to').value;
            const travelDate = document.getElementById('travel-date').value;
            const preferredTime = document.getElementById('time').value;
            const nextBtn = document.getElementById('next-btn');
            
            if (from && to && from !== to && travelDate && preferredTime) {
                const routeKey = from < to ? `${from}-${to}` : `${to}-${from}`;
                const routeData = cityData[routeKey];
                
                if (routeData) {
                    // Parse preferred time
                    const timeMatch = preferredTime.match(/(\d{2}):(\d{2}) (AM|PM)/);
                    if (timeMatch) {
                        let hour = parseInt(timeMatch[1]);
                        const minute = parseInt(timeMatch[2]);
                        const period = timeMatch[3];
                        
                        // Convert to 24-hour format
                        if (period === 'PM' && hour !== 12) hour += 12;
                        if (period === 'AM' && hour === 12) hour = 0;
                        
                        // Calculate arrival time
                        const arrivalHour = (hour + routeData.duration) % 24;
                        const nextDay = hour + routeData.duration >= 24;
                        
                        // Format arrival time
                        const formatTime = (h) => {
                            const period = h >= 12 ? 'PM' : 'AM';
                            const displayHour = h === 0 ? 12 : h > 12 ? h - 12 : h;
                            return `${displayHour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')} ${period}${nextDay ? ' (+1 day)' : ''}`;
                        };
                        
                        // Update display
                        document.getElementById('duration').textContent = `${routeData.duration} hours`;
                        document.getElementById('arrival-time').textContent = formatTime(arrivalHour);
                        document.getElementById('distance').textContent = `${routeData.distance} km`;
                        document.getElementById('journey-details').style.display = 'block';
                        
                        // Enable next button
                        if (nextBtn) nextBtn.disabled = false;
                    }
                } else {
                    document.getElementById('journey-details').style.display = 'none';
                    if (nextBtn) nextBtn.disabled = true;
                }
            } else {
                document.getElementById('journey-details').style.display = 'none';
                if (nextBtn) nextBtn.disabled = true;
            }
        }
        
        // Add event listeners for automatic calculation
        document.getElementById('from').addEventListener('change', calculateJourney);
        document.getElementById('to').addEventListener('change', calculateJourney);
        document.getElementById('time').addEventListener('change', calculateJourney);
        
        // Add event listeners for travel date and form submission
        document.getElementById('travel-date').addEventListener('change', calculateJourney);
        
        // Form submission for online ticket booking
        document.getElementById('booking-form').addEventListener('submit', function(e) {
            const from = document.getElementById('from').value;
            const to = document.getElementById('to').value;
            const travelDate = document.getElementById('travel-date').value;
            const preferredTime = document.getElementById('time').value;
            
            if (!from || !to || !travelDate || !preferredTime) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            if (from === to) {
                e.preventDefault();
                alert('Departure and destination cannot be the same.');
                return false;
            }
            
            // Store booking data for the next page
            sessionStorage.setItem('bookingData', JSON.stringify({
                from: from,
                to: to,
                travel_date: travelDate,
                preferred_time: preferredTime
            }));
        });
        
        // Section switching functionality
        function showSection(sectionName) {
            // Hide all sections
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => {
                section.style.display = 'none';
            });
            
            // Remove active class from all menu items
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.classList.remove('active');
            });
            
            // Show selected section
            const targetSection = document.getElementById(sectionName + '-section');
            if (targetSection) {
                targetSection.style.display = 'block';
            }
            
            // Add active class to clicked menu item
            event.target.closest('.menu-item').classList.add('active');
        }
        
        // Scroll to top button functionality
        const scrollToTopBtn = document.createElement('button');
        scrollToTopBtn.className = 'scroll-to-top';
        scrollToTopBtn.innerHTML = '↑';
        scrollToTopBtn.title = 'Scroll to top';
        document.body.appendChild(scrollToTopBtn);
        
        const mainContent = document.querySelector('.main-content');
        
        mainContent.addEventListener('scroll', function() {
            if (mainContent.scrollTop > 300) {
                scrollToTopBtn.classList.add('show');
            } else {
                scrollToTopBtn.classList.remove('show');
            }
        });
        
        scrollToTopBtn.addEventListener('click', function() {
            mainContent.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>
