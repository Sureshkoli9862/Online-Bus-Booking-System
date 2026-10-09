<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Admin not logged in']);
    exit;
}

if (!isset($_GET['booking_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Booking ID required']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $booking_id = $_GET['booking_id'];
    
    // Get payment history for the booking
    $stmt = $pdo->prepare("
        SELECT p.*, b.passenger_name, b.total_amount as booking_amount
        FROM payments p 
        JOIN bookings b ON p.booking_id = b.booking_id 
        WHERE p.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $payment = $stmt->fetch();
    
    if ($payment) {
        echo json_encode([
            'success' => true,
            'payment' => $payment
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'payment' => null,
            'message' => 'No payment record found'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Payment history error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch payment history']);
}
?>
