<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Get booking ID from URL or session
$booking_id = $_GET['booking_id'] ?? $_SESSION['last_booking_id'] ?? null;

if (!$booking_id) {
    header('Location: my_bookings_new.php');
    exit;
}

// Get booking details from database
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT b.*, u.name as user_name, u.phone as user_phone, u.address as user_address
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        header('Location: my_bookings_new.php');
        exit;
    }
    
    // Check if booking is in a state that allows receipt printing
    $allowed_statuses = ['confirmed', 'completed'];
    $blocked_statuses = ['cancelled', 'refunded'];
    
    if (in_array($booking['booking_status'], $blocked_statuses) || 
        in_array($booking['payment_status'], $blocked_statuses)) {
        // Redirect to bookings page with error message for cancelled/refunded bookings
        $_SESSION['error_message'] = 'Receipt not available for cancelled or refunded bookings.';
        header('Location: my_bookings_new.php');
        exit;
    }
    
    if (!in_array($booking['payment_status'], $allowed_statuses)) {
        // Redirect to bookings page with message for pending bookings
        $_SESSION['info_message'] = 'Receipt will be available once your booking is confirmed by admin.';
        header('Location: my_bookings_new.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: my_bookings_new.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo $booking['booking_id']; ?></title>
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
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .receipt-container {
            max-width: 320px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            text-align: center;
        }

        .receipt-header {
            margin-bottom: 25px;
        }

        .receipt-title {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .booking-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .booking-id {
            font-size: 16px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }

        .route {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 15px;
        }

        .detail-item {
            text-align: left;
        }

        .detail-label {
            font-size: 10px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .detail-value {
            font-size: 13px;
            font-weight: 700;
            color: #2c3e50;
        }

        .amount {
            font-size: 22px;
            font-weight: 700;
            color: #667eea;
            margin: 15px 0;
        }

        .print-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            margin-left: 8px;
            text-decoration: none;
            display: inline-block;
        }

        /* Popup Styles */
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .popup-content {
            background: white;
            width: 350px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .popup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .popup-title {
            font-size: 16px;
            font-weight: 600;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
        }

        .popup-body {
            padding: 25px;
        }

        .ticket-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            background: white;
        }

        .ticket-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: center;
            position: relative;
        }

        .ticket-id {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .status-badge {
            position: absolute;
            top: 10px;
            right: 15px;
            background: #ffc107;
            color: #856404;
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-confirmed {
            background: #28a745;
            color: white;
        }

        .ticket-body {
            padding: 15px;
        }

        .route-display {
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .ticket-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .ticket-detail {
            text-align: left;
        }

        .ticket-label {
            font-size: 11px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .ticket-value {
            font-size: 12px;
            font-weight: 700;
            color: #2c3e50;
        }

        .ticket-amount {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
        }

        .popup-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }

        .popup-btn {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .popup-btn-primary {
            background: #667eea;
            color: white;
        }

        .popup-btn-secondary {
            background: #6c757d;
            color: white;
        }

        @media (max-width: 480px) {
            .popup-content {
                width: 90%;
                margin: 20px;
            }
            
            .details-grid,
            .ticket-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <div class="receipt-title">🚌 Bus Booking Receipt</div>
        </div>

        <div class="booking-info">
            <div class="booking-id">#<?php echo $booking['booking_id']; ?></div>
            <div class="route"><?php echo ucfirst($booking['from_location']); ?> → <?php echo ucfirst($booking['to_location']); ?></div>
            
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Date</div>
                    <div class="detail-value"><?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Time</div>
                    <div class="detail-value"><?php echo $booking['departure_time']; ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Bus</div>
                    <div class="detail-value"><?php echo $booking['bus_name']; ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Seats</div>
                    <div class="detail-value"><?php echo $booking['selected_seats']; ?></div>
                </div>
            </div>
            
            <div class="amount">Rs. <?php echo number_format($booking['total_amount'], 0); ?></div>
        </div>

        <button onclick="openPrintPopup()" class="print-btn">🖨️ Print Receipt</button>
        <a href="dashboard1.php" class="back-btn">← Back</a>
    </div>

    <!-- Popup Overlay -->
    <div id="popup-overlay" class="popup-overlay">
        <div class="popup-content">
            <div class="popup-header">
                <div class="popup-title">Print Receipt</div>
                <button onclick="closePopup()" class="close-btn">×</button>
            </div>
            
            <div class="popup-body">
                <div class="ticket-card">
                    <div class="ticket-header">
                        <div class="ticket-id">#<?php echo $booking['booking_id']; ?></div>
                        <div class="status-badge status-<?php echo $booking['payment_status']; ?>">
                            <?php echo strtoupper($booking['payment_status']); ?>
                        </div>
                    </div>
                    
                    <div class="ticket-body">
                        <div class="route-display">
                            <?php echo ucfirst($booking['from_location']); ?> → <?php echo ucfirst($booking['to_location']); ?>
                        </div>
                        
                        <div class="ticket-details">
                            <div class="ticket-detail">
                                <div class="ticket-label">Date</div>
                                <div class="ticket-value"><?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></div>
                            </div>
                            <div class="ticket-detail">
                                <div class="ticket-label">Time</div>
                                <div class="ticket-value"><?php echo $booking['departure_time']; ?></div>
                            </div>
                            <div class="ticket-detail">
                                <div class="ticket-label">Bus</div>
                                <div class="ticket-value"><?php echo $booking['bus_name']; ?></div>
                            </div>
                            <div class="ticket-detail">
                                <div class="ticket-label">Seats</div>
                                <div class="ticket-value"><?php echo $booking['selected_seats']; ?></div>
                            </div>
                            <div class="ticket-detail">
                                <div class="ticket-label">Passenger</div>
                                <div class="ticket-value"><?php echo $booking['passenger_name']; ?></div>
                            </div>
                            <div class="ticket-detail">
                                <div class="ticket-label">Phone</div>
                                <div class="ticket-value"><?php echo $booking['phone']; ?></div>
                            </div>
                        </div>
                        
                        <div class="ticket-amount">
                            Rs. <?php echo number_format($booking['total_amount'], 0); ?>
                        </div>
                    </div>
                </div>
                
                <div class="popup-actions">
                    <button onclick="printTicket()" class="popup-btn popup-btn-primary">🖨️ Print</button>
                    <button onclick="closePopup()" class="popup-btn popup-btn-secondary">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openPrintPopup() {
            document.getElementById('popup-overlay').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closePopup() {
            document.getElementById('popup-overlay').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function printTicket() {
            // Create a new window with just the ticket content
            const ticketContent = document.querySelector('.ticket-card').outerHTML;
            const printWindow = window.open('', '_blank', 'width=400,height=600');
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Bus Ticket - <?php echo $booking['booking_id']; ?></title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 20px;
                            background: white;
                        }
                        .ticket-card {
                            border: 2px solid #e9ecef;
                            border-radius: 8px;
                            overflow: hidden;
                            background: white;
                            max-width: 280px;
                            margin: 0 auto;
                        }
                        .ticket-header {
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            padding: 12px;
                            text-align: center;
                            position: relative;
                        }
                        .ticket-id {
                            font-size: 14px;
                            font-weight: 700;
                            margin-bottom: 3px;
                        }
                        .status-badge {
                            position: absolute;
                            top: 8px;
                            right: 12px;
                            background: #28a745;
                            color: white;
                            padding: 3px 6px;
                            border-radius: 8px;
                            font-size: 9px;
                            font-weight: 700;
                            text-transform: uppercase;
                        }
                        .ticket-body {
                            padding: 15px;
                        }
                        .route-display {
                            text-align: center;
                            font-size: 15px;
                            font-weight: 700;
                            color: #2c3e50;
                            margin-bottom: 15px;
                        }
                        .ticket-details {
                            display: grid;
                            grid-template-columns: 1fr 1fr;
                            gap: 10px;
                            margin-bottom: 15px;
                        }
                        .ticket-detail {
                            text-align: left;
                        }
                        .ticket-label {
                            font-size: 9px;
                            color: #6c757d;
                            font-weight: 600;
                            text-transform: uppercase;
                            margin-bottom: 2px;
                        }
                        .ticket-value {
                            font-size: 11px;
                            font-weight: 700;
                            color: #2c3e50;
                        }
                        .ticket-amount {
                            text-align: center;
                            font-size: 18px;
                            font-weight: 700;
                            color: #667eea;
                            margin-top: 12px;
                            padding-top: 12px;
                            border-top: 1px solid #e9ecef;
                        }
                    </style>
                </head>
                <body>
                    ${ticketContent}
                    <script>
                        window.onload = function() {
                            window.print();
                            window.onafterprint = function() {
                                window.close();
                            }
                        }
                    </script>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            closePopup();
        }

        // Close popup when clicking outside
        document.getElementById('popup-overlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closePopup();
            }
        });

        // Close popup with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePopup();
            }
        });
    </script>
</body>
</html>
