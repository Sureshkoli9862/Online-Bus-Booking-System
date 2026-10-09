<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Admin not logged in']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['action']) || !isset($input['booking_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $booking_id = $input['booking_id'];
    $action = $input['action'];
    
    if ($action === 'accept') {
        // Update booking status to confirmed and payment status to confirmed
        $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'confirmed', payment_status = 'confirmed' WHERE booking_id = ?");
        $result = $stmt->execute([$booking_id]);
        
        // Update payment record
        $stmt = $pdo->prepare("UPDATE payments SET payment_status = 'completed', payment_date = NOW() WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Booking accepted successfully']);
        } else {
            throw new Exception('Failed to accept booking');
        }
        
    } elseif ($action === 'reject') {
        // Update booking status to cancelled and payment status to refunded
        $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled', payment_status = 'refunded' WHERE booking_id = ?");
        $result = $stmt->execute([$booking_id]);
        
        // Update payment record
        $stmt = $pdo->prepare("UPDATE payments SET payment_status = 'refunded', payment_date = NOW() WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Booking rejected successfully']);
        } else {
            throw new Exception('Failed to reject booking');
        }
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("Admin booking action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to process booking action']);
}
?>
