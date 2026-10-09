<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch user bookings from database
try {
    $pdo = getDBConnection();
    
    // Get user info
    $stmt = $pdo->prepare("SELECT name, username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Get total bookings count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $totalBookings = $stmt->fetch()['total'];
    
    // Get upcoming bookings count
    $stmt = $pdo->prepare("SELECT COUNT(*) as upcoming FROM bookings WHERE user_id = ? AND booking_status = 'upcoming' AND travel_date >= CURDATE()");
    $stmt->execute([$_SESSION['user_id']]);
    $upcomingBookings = $stmt->fetch()['upcoming'];
    
    // Get completed bookings count
    $stmt = $pdo->prepare("SELECT COUNT(*) as completed FROM bookings WHERE user_id = ? AND booking_status = 'confirmed'");
    $stmt->execute([$_SESSION['user_id']]);
    $completedBookings = $stmt->fetch()['completed'];
    
    // Get recent bookings with payment info for display - include all statuses
    $stmt = $pdo->prepare("
        SELECT b.*, p.payment_status as payment_status_actual, p.payment_method as payment_method_actual, p.payment_date
        FROM bookings b 
        LEFT JOIN payments p ON b.booking_id = p.booking_id 
        WHERE b.user_id = ? 
        ORDER BY 
            CASE 
                WHEN b.payment_status = 'pending' THEN 1
                WHEN b.booking_status = 'refund_requested' THEN 2
                WHEN b.payment_status = 'confirmed' THEN 3
                ELSE 4
            END,
            b.created_at DESC 
        LIMIT 15
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recentBookings = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Error fetching bookings: " . $e->getMessage());
    $totalBookings = 0;
    $upcomingBookings = 0;
    $completedBookings = 0;
    $recentBookings = [];
}
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
            overflow-x: hidden;
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

        /* Stats Cards */
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
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            cursor: pointer;
            width: 100%;
            box-sizing: border-box;
            min-width: 0;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .stat-description {
            color: #888;
            font-size: 13px;
            margin-top: 8px;
        }

        /* Bookings Section */
        .bookings-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 10px;
            color: #667eea;
        }

        /* Booking Cards Grid */
        .bookings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .booking-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
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
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .booking-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .booking-id {
            font-weight: 600;
            color: #667eea;
            font-size: 14px;
        }

        .booking-status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-completed {
            background: #e8f5e8;
            color: #4caf50;
        }

        .status-refunded {
            background: #ffebee;
            color: #f44336;
        }

        .status-pending {
            background: #fff3e0;
            color: #ff9800;
        }

        .status-upcoming {
            background: #e3f2fd;
            color: #2196f3;
        }

        .status-refund_requested {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-cancelled {
            background: #ffebee;
            color: #f44336;
        }

        .booking-route {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            text-align: center;
        }

        .booking-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .detail-value {
            font-size: 13px;
            color: #333;
            font-weight: 500;
        }

        .booking-price {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
            margin-bottom: 15px;
        }

        .receipt-section {
            text-align: center;
            padding-top: 10px;
        }

        .receipt-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .receipt-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .receipt-btn i {
            font-size: 14px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            min-width: 80px;
            justify-content: center;
        }

        .action-btn:hover {
            transform: translateY(-1px);
        }

        .refund-btn {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
        }

        .refund-btn:hover {
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
        }

        .cancel-btn {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
        }

        .cancel-btn:hover {
            box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
        }

        .support-btn {
            background: linear-gradient(135deg, #9c27b0 0%, #7b1fa2 100%);
            color: white;
        }

        .support-btn:hover {
            box-shadow: 0 4px 15px rgba(156, 39, 176, 0.3);
        }

        .status-message {
            text-align: center;
            padding: 8px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .status-info {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-warning {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-success {
            background: #e8f5e8;
            color: #4caf50;
        }

        .no-bookings {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .no-bookings i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 15px;
        }

        /* Responsive Design */
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

            .bookings-grid {
                grid-template-columns: 1fr;
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
            <li><a href="online_ticket.php"><i>🎫</i> Online Ticket</a></li>
            <li><a href="my_bookings_new.php" class="active"><i>📋</i> My Bookings</a></li>
            <li><a href="profile.php"><i>👤</i> Profile</a></li>
            <li><a href="help_support.php"><i>❓</i> Help & Support</a></li>
            <li><a href="logout.php"><i>🚪</i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">My Bookings</h1>
            <p class="page-subtitle">View and manage your bus ticket bookings</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number"><?php echo $totalBookings; ?></div>
                <div class="stat-label">Total Bookings</div>
                <div class="stat-description">All your bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🚀</div>
                <div class="stat-number"><?php echo $upcomingBookings; ?></div>
                <div class="stat-label">Active Tickets</div>
                <div class="stat-description">Current bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-number"><?php echo $completedBookings; ?></div>
                <div class="stat-label">Completed Trips</div>
                <div class="stat-description">Travel history</div>
            </div>
        </div>

        <!-- Recent Bookings Section -->
        <div class="bookings-section">
            <h2 class="section-title">
                <i>🎫</i>
                Recent Bookings
            </h2>
            
            <?php if (!empty($recentBookings)): ?>
            <div class="bookings-grid">
                <?php foreach ($recentBookings as $booking): ?>
                <div class="booking-card" data-booking-id="<?php echo $booking['booking_id']; ?>">
                    <div class="booking-header">
                        <div class="booking-id">#<?php echo htmlspecialchars($booking['booking_id']); ?></div>
                        <div class="booking-status status-<?php 
                            $display_status = $booking['payment_status_actual'] ?? $booking['payment_status'];
                            $booking_status = $booking['booking_status'];
                            
                            // Determine status class
                            if ($display_status == 'completed' || $display_status == 'confirmed') echo 'completed';
                            elseif ($display_status == 'refunded') echo 'refunded';
                            elseif ($display_status == 'refund_pending' || $booking_status == 'refund_requested') echo 'refund_requested';
                            elseif ($booking_status == 'cancelled') echo 'cancelled';
                            elseif ($display_status == 'pending') echo 'pending';
                            else echo 'pending';
                        ?>">
                            <?php 
                            // Determine status text
                            if ($display_status == 'completed' || $display_status == 'confirmed') echo 'COMPLETED';
                            elseif ($display_status == 'refunded') echo 'REFUNDED';
                            elseif ($display_status == 'refund_pending' || $booking_status == 'refund_requested') echo 'REFUND REQUESTED';
                            elseif ($booking_status == 'cancelled') echo 'CANCELLED';
                            elseif ($display_status == 'pending') echo 'PENDING';
                            else echo 'PENDING';
                            ?>
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
                    
                    <?php 
                    $payment_status = $booking['payment_status_actual'] ?? $booking['payment_status'];
                    $booking_status = $booking['booking_status'];
                    
                    // Show receipt for confirmed/completed bookings
                    if (($payment_status == 'confirmed' || $payment_status == 'completed') && 
                        ($booking_status != 'cancelled' && $payment_status != 'refunded')): 
                    ?>
                    <div class="receipt-section">
                        <button class="receipt-btn" onclick="printReceipt('<?php echo $booking['booking_id']; ?>')">
                            <i>🖨️</i> Print Receipt
                        </button>
                    </div>
                    
                    <?php elseif ($payment_status == 'pending'): ?>
                    <div class="receipt-section">
                        <div class="status-message status-info">
                            ⏳ Waiting for admin approval
                        </div>
                        <div class="action-buttons">
                            <button class="action-btn refund-btn" onclick="requestRefund('<?php echo $booking['booking_id']; ?>')">
                                <i>💰</i> Request Refund
                            </button>
                            <button class="action-btn cancel-btn" onclick="cancelBooking('<?php echo $booking['booking_id']; ?>')">
                                <i>❌</i> Cancel
                            </button>
                            <button class="action-btn support-btn" onclick="contactSupport('<?php echo $booking['booking_id']; ?>')">
                                <i>📞</i> Support
                            </button>
                        </div>
                    </div>
                    
                    <?php elseif ($payment_status == 'refund_pending' || $booking_status == 'refund_requested'): ?>
                    <div class="receipt-section">
                        <div class="status-message status-warning">
                            ⏳ Refund request pending admin review
                        </div>
                        <div class="action-buttons">
                            <button class="action-btn support-btn" onclick="contactSupport('<?php echo $booking['booking_id']; ?>')">
                                <i>📞</i> Contact Support
                            </button>
                        </div>
                    </div>
                    
                    <?php elseif ($payment_status == 'refunded'): ?>
                    <div class="receipt-section">
                        <!-- Refunded status - no additional message needed -->
                    </div>
                    
                    <?php elseif ($booking_status == 'cancelled'): ?>
                    <div class="receipt-section">
                        <div class="status-message status-warning">
                            ❌ Booking cancelled
                        </div>
                    </div>
                    
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-bookings">
                <i>📋</i>
                <h3>No Bookings Found</h3>
                <p>You haven't made any bookings yet. <a href="online_ticket.php">Book your first ticket!</a></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Receipt Popup Overlay -->
    <div id="receipt-popup" class="receipt-popup-overlay" style="display: none;">
        <div class="receipt-popup-content">
            <div class="receipt-popup-header">
                <h3>Payment Receipt</h3>
                <button onclick="closeReceiptPopup()" class="close-popup-btn">×</button>
            </div>
            <div class="receipt-popup-body" id="receipt-content">
                <!-- Receipt content will be loaded here -->
            </div>
            <div class="receipt-popup-footer">
                <button onclick="printReceiptContent()" class="popup-print-btn">🖨️ Print</button>
                <button onclick="closeReceiptPopup()" class="popup-close-btn">Close</button>
            </div>
        </div>
    </div>

    <style>
        .receipt-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .receipt-popup-content {
            background: white;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .receipt-popup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .receipt-popup-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .close-popup-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-popup-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .receipt-popup-body {
            padding: 25px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .receipt-popup-footer {
            background: #f8f9fa;
            padding: 15px 25px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            border-top: 1px solid #e9ecef;
        }

        .popup-print-btn, .popup-close-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .popup-print-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .popup-print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .popup-close-btn {
            background: #6c757d;
            color: white;
        }

        .popup-close-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* Receipt Content Styles */
        .receipt-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }

        .receipt-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            text-align: center;
        }

        .company-logo {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .company-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .receipt-title {
            font-size: 12px;
            opacity: 0.9;
        }

        .receipt-body {
            padding: 20px;
        }

        .booking-id-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .booking-id-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .booking-id-value {
            font-size: 16px;
            font-weight: 700;
            color: #667eea;
            background: #f8f9ff;
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
        }

        .user-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .user-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .user-value {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            background: #f8f9fa;
            padding: 6px 12px;
            border-radius: 15px;
            display: inline-block;
        }

        .total-amount-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            margin-top: 20px;
        }

        .total-label {
            font-size: 11px;
            opacity: 0.8;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .total-value {
            font-size: 22px;
            font-weight: 700;
        }

        .route-section {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8f4fd 100%);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #667eea;
        }

        .route-text {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
        }

        .booking-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            border: 1px solid #e9ecef;
        }

        .detail-icon {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .detail-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 3px;
            font-weight: 600;
        }

        .detail-value {
            font-size: 12px;
            color: #2c3e50;
            font-weight: 600;
        }

        .status-section {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .status-text {
            font-size: 14px;
            font-weight: 700;
            color: #155724;
        }

        .amount-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
        }

        .amount-label {
            font-size: 11px;
            opacity: 0.8;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .amount-value {
            font-size: 22px;
            font-weight: 700;
        }

        .receipt-footer {
            background: #f8f9fa;
            padding: 15px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }

        .footer-text {
            font-size: 11px;
            color: #666;
            margin-bottom: 8px;
        }

        .support-info {
            font-size: 12px;
            color: #667eea;
            font-weight: 600;
        }

        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }
            
            html, body {
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }
            
            * {
                visibility: hidden !important;
            }
            
            .receipt-popup-overlay,
            .receipt-popup-overlay * {
                visibility: visible !important;
            }
            
            .receipt-popup-overlay {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100vh !important;
                background: white !important;
                display: flex !important;
                align-items: flex-start !important;
                justify-content: center !important;
                z-index: 9999 !important;
                page-break-after: avoid !important;
            }
            
            .receipt-popup-content {
                width: 100% !important;
                max-width: 210mm !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                max-height: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }
            
            .receipt-popup-header,
            .receipt-popup-footer {
                display: none !important;
            }
            
            .receipt-popup-body {
                padding: 0 !important;
                max-height: none !important;
                overflow: visible !important;
            }
            
            .receipt-container {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 15mm !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                height: fit-content !important;
                max-height: 277mm !important;
                overflow: hidden !important;
            }
            
            .receipt-header {
                padding: 8mm 0 !important;
                margin-bottom: 6mm !important;
            }
            
            .company-logo {
                font-size: 18px !important;
                margin-bottom: 3mm !important;
            }
            
            .company-name {
                font-size: 14px !important;
                margin-bottom: 0 !important;
            }
            
            .receipt-body {
                padding: 0 !important;
            }
            
            .booking-id-section {
                margin-bottom: 6mm !important;
            }
            
            .booking-id-label {
                font-size: 9px !important;
            }
            
            .booking-id-value {
                font-size: 11px !important;
                padding: 2mm 4mm !important;
            }

            .user-section {
                margin-bottom: 6mm !important;
            }

            .user-label {
                font-size: 8px !important;
                margin-bottom: 1mm !important;
            }

            .user-value {
                font-size: 10px !important;
                padding: 2mm 4mm !important;
            }

            .total-amount-section {
                padding: 4mm !important;
                margin-top: 6mm !important;
                margin-bottom: 0 !important;
            }

            .total-label {
                font-size: 8px !important;
                margin-bottom: 1mm !important;
            }

            .total-value {
                font-size: 12px !important;
            }
            
            .route-section {
                padding: 6mm !important;
                margin-bottom: 8mm !important;
            }
            
            .route-text {
                font-size: 12px !important;
            }
            
            .booking-details-grid {
                gap: 5mm !important;
                margin-bottom: 8mm !important;
            }
            
            .detail-card {
                padding: 4mm !important;
            }
            
            .detail-label {
                font-size: 7px !important;
                margin-bottom: 1mm !important;
            }
            
            .detail-value {
                font-size: 9px !important;
            }
            
            .amount-section {
                padding: 6mm !important;
                margin-bottom: 0 !important;
            }
            
            .amount-value {
                font-size: 14px !important;
            }
        }
    </style>

    <script>
        function printReceipt(bookingId) {
            // Get booking data from the current page
            const bookingCard = document.querySelector(`[data-booking-id="${bookingId}"]`);
            
            if (!bookingCard) {
                alert('Booking details not found for ID: ' + bookingId);
                return;
            }
            
            // Extract booking information
            const bookingIdText = bookingCard.querySelector('.booking-id').textContent;
            const route = bookingCard.querySelector('.booking-route').textContent;
            const details = bookingCard.querySelectorAll('.detail-value');
            const price = bookingCard.querySelector('.booking-price').textContent;
            
            const date = details[0]?.textContent || '';
            const time = details[1]?.textContent || '';
            const busName = details[2]?.textContent || '';
            const seats = details[3]?.textContent || '';
            
            // Get bus number from booking card (we'll need to extract it from the booking data)
            const busNumber = 'Na 7 Kha 9012'; // Default bus number
            
            // Get user name from sidebar
            const userName = document.querySelector('.sidebar-subtitle')?.textContent?.replace('Welcome, ', '') || 'User';
            
            // Generate simplified receipt HTML for popup
            const receiptHTML = `
                <div class="receipt-container">
                    <div class="receipt-header">
                        <div class="company-logo">🚌</div>
                        <div class="company-name">Bus Booking Receipt</div>
                    </div>
                    
                    <div class="receipt-body">
                        <div class="booking-id-section">
                            <div class="booking-id-label">Booking ID</div>
                            <div class="booking-id-value">${bookingIdText}</div>
                        </div>
                        
                        <div class="user-section">
                            <div class="user-label">Passenger Name</div>
                            <div class="user-value">${userName}</div>
                        </div>
                        
                        <div class="route-section">
                            <div class="route-text">${route}</div>
                        </div>
                        
                        <div class="booking-details-grid">
                            <div class="detail-card">
                                <div class="detail-label">Date</div>
                                <div class="detail-value">${date}</div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-label">Time</div>
                                <div class="detail-value">${time}</div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-label">Bus Name</div>
                                <div class="detail-value">${busName}</div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-label">Bus Number</div>
                                <div class="detail-value">${busNumber}</div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-label">Phone Number</div>
                                <div class="detail-value">+977-1-4567890</div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-label">Seats</div>
                                <div class="detail-value">${seats}</div>
                            </div>
                        </div>
                        
                        <div class="total-amount-section">
                            <div class="total-label">Total Amount</div>
                            <div class="total-value">${price}</div>
                        </div>
                    </div>
                </div>
            `;
            
            // Show receipt in popup
            document.getElementById('receipt-content').innerHTML = receiptHTML;
            document.getElementById('receipt-popup').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeReceiptPopup() {
            document.getElementById('receipt-popup').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function printReceiptContent() {
            window.print();
            
            // Close popup and redirect after print dialog
            setTimeout(() => {
                closeReceiptPopup();
                // Redirect to booking dashboard
                window.location.href = 'my_bookings_new.php';
            }, 1000);
        }

        // Close popup when clicking outside
        document.getElementById('receipt-popup').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReceiptPopup();
            }
        });

        // Close popup with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeReceiptPopup();
            }
        });

        // User booking actions
        function requestRefund(bookingId) {
            if (confirm('Are you sure you want to request a refund for this booking? This action will be reviewed by admin.')) {
                performBookingAction(bookingId, 'request_refund');
            }
        }

        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
                performBookingAction(bookingId, 'cancel_booking');
            }
        }

        function contactSupport(bookingId) {
            const reason = prompt('Please briefly describe your issue or question:');
            if (reason && reason.trim()) {
                performBookingAction(bookingId, 'contact_support', { reason: reason.trim() });
            }
        }

        function performBookingAction(bookingId, action, extraData = {}) {
            const actionData = {
                booking_id: bookingId,
                action: action,
                ...extraData
            };

            fetch('user_booking_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(actionData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    if (data.support_info) {
                        const supportInfo = `\n\nSupport Contact Information:\nPhone: ${data.support_info.phone}\nEmail: ${data.support_info.email}\nWhatsApp: ${data.support_info.whatsapp}`;
                        alert(data.message + supportInfo);
                    }
                    // Reload page to show updated status
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request. Please try again.');
            });
        }
    </script>
</body>
</html>
