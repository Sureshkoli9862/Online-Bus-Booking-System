<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
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
    $user_id = $_SESSION['user_id'];
    
    // Verify booking belongs to the user
    $stmt = $pdo->prepare("SELECT booking_status, payment_status FROM bookings WHERE booking_id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Booking not found or access denied']);
        exit;
    }
    
    if ($action === 'request_refund') {
        // Check if booking is eligible for refund request
        if ($booking['booking_status'] === 'cancelled' || $booking['payment_status'] === 'refunded') {
            echo json_encode(['success' => false, 'message' => 'Booking is already cancelled or refunded']);
            exit;
        }
        
        // Update booking to request refund status
        $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'refund_requested', payment_status = 'refund_pending' WHERE booking_id = ? AND user_id = ?");
        $result = $stmt->execute([$booking_id, $user_id]);
        
        // Update payment record
        $stmt = $pdo->prepare("UPDATE payments SET payment_status = 'refund_pending' WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Refund request submitted successfully. Admin will review your request.']);
        } else {
            throw new Exception('Failed to submit refund request');
        }
        
    } elseif ($action === 'cancel_booking') {
        // Check if booking is eligible for cancellation
        if ($booking['booking_status'] === 'cancelled' || $booking['booking_status'] === 'confirmed') {
            echo json_encode(['success' => false, 'message' => 'Cannot cancel this booking. Contact support for assistance.']);
            exit;
        }
        
        // Cancel the booking
        $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled', payment_status = 'cancelled' WHERE booking_id = ? AND user_id = ?");
        $result = $stmt->execute([$booking_id, $user_id]);
        
        // Update payment record
        $stmt = $pdo->prepare("UPDATE payments SET payment_status = 'cancelled' WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully.']);
        } else {
            throw new Exception('Failed to cancel booking');
        }
        
    } elseif ($action === 'contact_support') {
        // Log support request (you can extend this to create a support ticket system)
        $reason = $input['reason'] ?? 'General inquiry';
        $message = $input['message'] ?? '';
        
        // For now, just return success - you can implement actual support ticket creation later
        echo json_encode([
            'success' => true, 
            'message' => 'Support request submitted. We will contact you within 24 hours.',
            'support_info' => [
                'phone' => '+977-1-4567890',
                'email' => 'support@busbooking.np',
                'whatsapp' => '+977-9862463805'
            ]
        ]);
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("User booking action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to process booking action: ' . $e->getMessage()]);
}
?>
