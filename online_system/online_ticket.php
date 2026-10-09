<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Handle POST-Redirect-GET pattern to prevent form resubmission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store form data in session
    $_SESSION['ticket_form_data'] = $_POST;
    
    // Redirect to bus_selection.php with the form data
    header('Location: bus_selection.php');
    exit;
}

// Get user info
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT name, username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (Exception $e) {
    $user = ['name' => 'User', 'username' => 'user'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bus Ticket Booking</title>
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

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }

            .main-content {
                margin-left: 0;
            }
        }

        @keyframes float {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(-50px) translateY(-50px); }
        }

        /* Sidebar - Matching my_bookings_new.php exactly */
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
            border-left-color: #3498db;
        }

        .menu-item.active {
            background: rgba(52, 152, 219, 0.2);
            border-left-color: #3498db;
        }

        .menu-item i {
            margin-right: 12px;
            font-size: 16px;
            width: 20px;
        }


        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            overflow-x: hidden;
            overflow-y: auto;
            height: 100vh;
            max-width: calc(100vw - 250px);
            box-sizing: border-box;
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
        .booking-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            box-sizing: border-box;
            max-width: 100%;
        }

        .booking-title {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            width: 100%;
            box-sizing: border-box;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
            color: #2c3e50;
            box-sizing: border-box;
            max-width: 100%;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Fix date picker styling to prevent black overlay */
        input[type="date"]::-webkit-calendar-picker-indicator {
            background: transparent;
            bottom: 0;
            color: transparent;
            cursor: pointer;
            height: auto;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            width: auto;
            z-index: 1;
        }

        input[type="date"]::-webkit-inner-spin-button,
        input[type="date"]::-webkit-clear-button {
            z-index: 1;
        }

        /* Ensure date picker appears above other elements */
        input[type="date"] {
            position: relative;
            z-index: 10;
        }

        /* Prevent any overlay issues */
        .form-group {
            position: relative;
            z-index: 5;
        }

        /* Fix dropdown styling to prevent black flash - Enhanced */
        select.form-control {
            background: white !important;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23667eea' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            padding-right: 40px;
            will-change: transform, opacity;
            backface-visibility: hidden;
            transform: translateZ(0);
        }

        /* Dropdown options styling - Enhanced */
        select.form-control option {
            background: white !important;
            color: #2c3e50 !important;
            padding: 10px 15px;
            border: none;
            transition: background-color 0.15s ease;
        }

        select.form-control option:hover,
        select.form-control option:focus {
            background: #f8f9fa !important;
            color: #2c3e50 !important;
        }

        select.form-control option:checked {
            background: #667eea !important;
            color: white !important;
        }

        /* Remove all default select styling that causes black flash */
        select.form-control::-ms-expand {
            display: none;
        }

        select.form-control::-webkit-scrollbar {
            width: 8px;
        }

        select.form-control::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        select.form-control::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 4px;
        }

        /* Ultra smooth transitions */
        select.form-control {
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateZ(0);
        }

        select.form-control:focus {
            outline: none !important;
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
            background-color: white !important;
            transform: translateZ(0) scale(1.01);
        }

        select.form-control:active {
            transform: translateZ(0) scale(0.99);
        }

        /* Prevent any rendering issues */
        .form-control {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        /* Journey Details */
        .journey-details {
            display: none;
            margin-top: 20px;
            padding: 15px;
            background: linear-gradient(135deg, #e8f4fd 0%, #f0f7ff 100%);
            border-radius: 8px;
            border-left: 4px solid #3498db;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.1);
        }

        .journey-details h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
            text-align: center;
        }

        .journey-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }

        .info-box {
            background: white;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }

        .info-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 700;
        }

        .next-btn {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            margin: 15px auto 0;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .next-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .next-btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
                padding: 15px;
                max-width: 100vw;
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-title">Dashboard</div>
            <div class="sidebar-subtitle">Welcome, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?></div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="dashboard1.php"><i>🏠</i> Dashboard</a></li>
            <li><a href="online_ticket.php" class="active"><i>🎫</i> Online Ticket</a></li>
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
            <h1 class="page-title">Online Ticket Booking</h1>
            <p class="page-subtitle">Book your journey with ease and comfort</p>
        </div>

        <!-- Booking Form -->
        <div class="booking-card">
            <div class="card-header">
                <h2>🚌 Book Your Bus Ticket</h2>
                <p>Fill in the details below to search for available buses</p>
            </div>
            
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
                <div id="journey-details" class="journey-details">
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
                        nextBtn.disabled = false;
                    }
                } else {
                    document.getElementById('journey-details').style.display = 'none';
                    nextBtn.disabled = true;
                }
            } else {
                document.getElementById('journey-details').style.display = 'none';
                nextBtn.disabled = true;
            }
        }
        
        // Add event listeners for automatic calculation
        document.getElementById('from').addEventListener('change', calculateJourney);
        document.getElementById('to').addEventListener('change', calculateJourney);
        document.getElementById('travel-date').addEventListener('change', calculateJourney);
        document.getElementById('time').addEventListener('change', calculateJourney);
        
        // Form validation and data handling
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
            
            // Store booking data in session for the next page
            sessionStorage.setItem('bookingData', JSON.stringify({
                from: from,
                to: to,
                travel_date: travelDate,
                preferred_time: preferredTime
            }));
        });
    </script>
</body>
</html>
