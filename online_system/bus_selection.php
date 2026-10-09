<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Handle POST data and redirect to prevent form resubmission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store POST data in session
    $_SESSION['booking_form_data'] = $_POST;
    
    // Redirect to same page with GET to prevent resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Retrieve booking data from session if available
$booking_data = $_SESSION['booking_form_data'] ?? null;

// Get user info
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT name, username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Fetch all buses (both active and inactive)
    $stmt = $pdo->query("SELECT * FROM buses ORDER BY is_active DESC, departure_time ASC");
    $buses = $stmt->fetchAll();
    
} catch (Exception $e) {
    $user = ['name' => 'User', 'username' => 'user'];
    $buses = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Selection - Online Bus Booking</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            position: relative;
            overflow: hidden;
        }

        /* Custom scrollbar styles */
        ::-webkit-scrollbar {
            display: none;
        }

        html {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .page-container {
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .page-container::-webkit-scrollbar {
            display: none;
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

        /* Header */
        .header {
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
        }

        /* Top Navigation */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 4px 15px rgba(66, 133, 244, 0.1);
            margin-bottom: 20px;
            border-bottom: 3px solid #e3f2fd;
        }

        .back-btn {
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(66, 133, 244, 0.2);
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(66, 133, 244, 0.3);
            background: linear-gradient(135deg, #1976d2 0%, #0d47a1 100%);
        }

        .nav-info {
            display: flex;
            gap: 30px;
            font-size: 14px;
            color: #666;
            align-items: center;
        }

        .nav-info span {
            background: #f0f7ff;
            padding: 8px 15px;
            border-radius: 6px;
            border: 1px solid #e3f2fd;
            font-weight: 500;
        }

        .username {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            font-weight: 600;
        }

        /* Bus Selection Section */
        .bus-selection {
            background: #e8f0fe;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }

        .bus-selection h2 {
            color: #1976d2;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .bus-selection p {
            color: #666;
            font-size: 14px;
        }

        /* Bus Grid */
        .bus-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            padding: 0 30px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .bus-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fbff 100%);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(66, 133, 244, 0.15);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 3px solid #e3f2fd;
            position: relative;
            overflow: hidden;
        }

        .bus-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4285f4, #1976d2, #42a5f5);
        }

        .bus-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 35px rgba(135, 206, 235, 0.4);
            border-color: #87ceeb;
        }

        .bus-card.selected {
            border-color: #ff5722;
            background: linear-gradient(145deg, #fff3e0 0%, #ffcc80 100%);
            box-shadow: 0 12px 30px rgba(255, 87, 34, 0.3);
        }

        .bus-card.selected::before {
            background: linear-gradient(90deg, #ff5722, #ff9800, #ffab40);
        }

        .bus-card.full {
            border-color: #ff9800;
            background: linear-gradient(145deg, #ffebee 0%, #ffcdd2 100%);
            opacity: 0.8;
        }

        .bus-card.full::before {
            background: linear-gradient(90deg, #ff9800, #ff5722, #f44336);
        }

        .bus-type {
            color: #4285f4;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .bus-name {
            font-size: 20px;
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 10px;
        }

        .bus-phone {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .bus-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 18px;
            font-size: 13px;
            color: #555;
            background: #f0f7ff;
            padding: 10px 15px;
            border-radius: 8px;
            border-left: 4px solid #4285f4;
        }

        .bus-time {
            text-align: center;
            margin-bottom: 18px;
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #e1f5fe;
        }

        .time {
            font-size: 20px;
            font-weight: 700;
            color: #1976d2;
            background: #e3f2fd;
            padding: 10px 15px;
            border-radius: 8px;
            display: inline-block;
        }

        .price {
            font-size: 22px;
            font-weight: 800;
            color: #ff5722;
            margin-bottom: 18px;
            text-align: center;
            background: linear-gradient(135deg, #fff3e0, #ffcc80);
            padding: 10px;
            border-radius: 8px;
            border: 2px solid #ffab40;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .select-btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.4s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .select-btn.available {
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            color: white;
            border: 2px solid transparent;
        }

        .select-btn.available:hover {
            background: linear-gradient(135deg, #1976d2 0%, #0d47a1 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(66, 133, 244, 0.3);
        }

        .select-btn.full {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
            cursor: not-allowed;
            opacity: 0.8;
        }

        .select-btn.inactive {
            background: linear-gradient(135deg, #9e9e9e 0%, #616161 100%);
            color: white;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .bus-card.inactive {
            border-color: #9e9e9e;
            background: linear-gradient(145deg, #f5f5f5 0%, #e0e0e0 100%);
            opacity: 0.7;
        }

        .bus-card.inactive::before {
            background: linear-gradient(90deg, #9e9e9e, #616161, #424242);
        }

        .inactive-message {
            background: #ffebee;
            color: #c62828;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
            border: 1px solid #ffcdd2;
            text-align: center;
        }

        .select-btn.selected {
            background: linear-gradient(135deg, #ff5722 0%, #d84315 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(255, 87, 34, 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .bus-grid {
                grid-template-columns: 1fr;
                padding: 0 15px;
            }
            
            .top-nav {
                padding: 15px;
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="nav-info">
                <span class="username">👤 <?php echo htmlspecialchars($user['name'] ?? $user['username']); ?></span>
            </div>
        </div>

        <!-- Header -->
        <div class="header">
            <h1>Welcome to Our Buses</h1>
        </div>

        <!-- Bus Selection Info -->
        <div class="bus-selection">
            <h2>🚌 Select Your Bus</h2>
            <p>Choose your preferred bus from the available options</p>
        </div>

        <!-- Bus Grid -->
        <div class="bus-grid">
            <?php if (count($buses) > 0): ?>
                <?php foreach ($buses as $bus): ?>
                    <div class="bus-card <?php echo !$bus['is_active'] ? 'inactive' : ''; ?>">
                        <div class="bus-type"><?php echo htmlspecialchars($bus['bus_number']); ?></div>
                        <div class="bus-name"><?php echo htmlspecialchars($bus['bus_name']); ?></div>
                        <div class="bus-phone">📞 <?php echo htmlspecialchars($bus['driver_phone']); ?></div>
                        
                        <?php if (!$bus['is_active']): ?>
                            <div class="inactive-message">⚠️ This bus is deactivated</div>
                        <?php endif; ?>
                        
                        <div class="bus-details">
                            <span>🪑 <?php echo $bus['total_seats']; ?> seats</span>
                            <span><?php echo htmlspecialchars($bus['bus_type']); ?></span>
                        </div>
                        <div class="bus-time">
                            <span class="time"><?php echo date('g:i A', strtotime($bus['departure_time'])); ?></span>
                        </div>
                        <div class="price">Rs. <?php echo number_format($bus['fare_amount'], 0); ?></div>
                        
                        <?php if ($bus['is_active']): ?>
                            <button class="select-btn available" onclick="selectBus(<?php echo $bus['id']; ?>, '<?php echo addslashes($bus['bus_name']); ?>', '<?php echo addslashes($bus['bus_number']); ?>', '<?php echo $bus['fare_amount']; ?>', '<?php echo date('g:i A', strtotime($bus['departure_time'])); ?>', '<?php echo addslashes($bus['bus_type']); ?>', <?php echo $bus['total_seats']; ?>, '<?php echo addslashes($bus['driver_phone']); ?>')">Select</button>
                        <?php else: ?>
                            <button class="select-btn inactive" onclick="showInactiveMessage()">Deactivated</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: #666;">
                    <h3>No buses available</h3>
                    <p>Please check back later for available buses.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Bus selection functionality for active buses
        function selectBus(busId, busName, busNumber, fareAmount, departureTime, busType, totalSeats, driverPhone) {
            // Get booking data from previous page (online_ticket.php)
            const existingBookingData = sessionStorage.getItem('bookingData');
            let bookingData = {};
            
            if (existingBookingData) {
                bookingData = JSON.parse(existingBookingData);
            }
            
            // Store bus data for seat selection
            const busData = {
                id: busId,
                bus_name: busName,
                bus_number: busNumber,
                fare_amount: fareAmount,
                departure_time: departureTime,
                bus_type: busType || 'AC',
                total_seats: totalSeats,
                driver_phone: driverPhone
            };
            
            // Combine booking data with selected bus
            bookingData.selectedBus = busData;
            
            // Store combined data in sessionStorage for next page
            sessionStorage.setItem('bookingData', JSON.stringify(bookingData));
            sessionStorage.setItem('selectedBus', JSON.stringify(busData));
            
            // Redirect to booked seat page
            window.location.href = 'booked_seat.php';
        }
        
        // Show message for inactive buses
        function showInactiveMessage() {
            alert('⚠️ This bus is currently deactivated and not available for booking. Please select an active bus.');
        }
        
        // Go back to ticket booking page
        function goBackToTicketBooking() {
            window.location.href = 'online_ticket.php';
        }
        
        // Go back to dashboard
        function goBackToDashboard() {
            console.log('Back to Dashboard clicked');
            window.location.href = 'online_ticket.php';
        }
    </script>
</body>
</html>
