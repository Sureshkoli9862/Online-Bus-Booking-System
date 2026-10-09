<?php
// Redirect to new bookings page
header('Location: my_bookings_new.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Online Bus Booking</title>
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

        /* Main Content */
        .main-content {
            padding: 0 30px 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Two Column Layout */
        .booking-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }

        /* Section Box */
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

        .booking-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fbff 100%);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(66, 133, 244, 0.15);
            border: 3px solid #e3f2fd;
            position: relative;
            overflow: hidden;
        }

        .booking-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4caf50, #8bc34a, #cddc39);
        }

        .booking-card.upcoming::before {
            background: linear-gradient(90deg, #ff9800, #ffc107, #ffeb3b);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .booking-id {
            font-size: 14px;
            font-weight: 600;
            color: #1976d2;
        }

        .booking-status {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-confirmed {
            background: #e8f5e8;
            color: #4caf50;
        }

        .status-upcoming {
            background: #fff3e0;
            color: #ff9800;
        }

        .booking-route {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .booking-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-item {
            background: #f0f7ff;
            padding: 10px 15px;
            border-radius: 8px;
            border-left: 4px solid #4285f4;
        }

        .detail-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 3px;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
        }

        .booking-price {
            text-align: center;
            font-size: 20px;
            font-weight: 800;
            color: #ff5722;
            background: linear-gradient(135deg, #fff3e0, #ffcc80);
            padding: 10px;
            border-radius: 8px;
            border: 2px solid #ffab40;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .booking-grid {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                padding: 0 15px 20px;
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
            <a href="dashboard1.php" class="back-btn">← Back to Dashboard</a>
            <div class="nav-info">
                <span class="username">👤 <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
            </div>
        </div>

        <!-- Header -->
        <div class="header">
            <h1>My Bookings</h1>
        </div>

        <!-- Two Column Layout -->
        <div class="booking-sections">
            <!-- Total Bookings Box -->
            <div class="section-box" onclick="window.location.href='total_bookings.php'">
                <div class="section-icon">📋</div>
                <div class="section-title">Total Bookings</div>
                <div class="section-description">View all your completed and confirmed bus bookings</div>
                <div class="section-count"><?php echo $totalBookings; ?></div>
                <div class="section-label">Total Trips</div>
            </div>

            <!-- Upcoming Buses Box -->
            <div class="section-box upcoming" onclick="window.location.href='upcoming_bookings.php'">
                <div class="section-icon">🚀</div>
                <div class="section-title">Upcoming Buses</div>
                <div class="section-description">Check your future bookings and reserved seats</div>
                <div class="section-count"><?php echo $upcomingBookings; ?></div>
                <div class="section-label">Future Trips</div>
            </div>
        </div>
        
        <!-- Recent Bookings Section -->
        <?php if (!empty($recentBookings)): ?>
        <div class="main-content">
            <h2 style="color: white; text-align: center; margin: 30px 0 20px;">Recent Bookings</h2>
            <div class="booking-grid">
                <?php foreach ($recentBookings as $booking): ?>
                <div class="booking-card <?php echo ($booking['booking_status'] == 'upcoming') ? 'upcoming' : ''; ?>">
                    <div class="booking-header">
                        <div class="booking-id">#<?php echo htmlspecialchars($booking['booking_id']); ?></div>
                        <div class="booking-status <?php echo ($booking['payment_status_actual'] == 'completed') ? 'status-confirmed' : 'status-upcoming'; ?>">
                            <?php echo ucfirst($booking['payment_status_actual'] ?? $booking['payment_status']); ?>
                        </div>
                    </div>
                    
                    <div class="booking-route">
                        <?php echo htmlspecialchars($booking['from_location']); ?> → <?php echo htmlspecialchars($booking['to_location']); ?>
                    </div>
                    
                    <div class="booking-details">
                        <div class="detail-item">
                            <div class="detail-label">Date</div>
                            <div class="detail-value"><?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Time</div>
                            <div class="detail-value"><?php echo htmlspecialchars($booking['departure_time']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Bus</div>
                            <div class="detail-value"><?php echo htmlspecialchars($booking['bus_name']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Seats</div>
                            <div class="detail-value"><?php echo htmlspecialchars($booking['selected_seats']); ?></div>
                        </div>
                    </div>
                    
                    <div class="booking-price">
                        Rs. <?php echo number_format($booking['total_amount'], 0); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        </div>
    </div>
    
    <style>
    .booking-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        padding: 0 30px;
        margin-bottom: 30px;
    }
    </style>
</body>
</html>
