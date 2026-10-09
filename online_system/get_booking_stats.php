<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get total bookings count for the logged-in user
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $totalBookings = $stmt->fetch()['total'];
    
    // Get upcoming bookings count
    $stmt = $pdo->prepare("SELECT COUNT(*) as upcoming FROM bookings WHERE user_id = ? AND booking_status = 'upcoming' AND travel_date >= CURDATE()");
    $stmt->execute([$_SESSION['user_id']]);
    $upcomingBookings = $stmt->fetch()['upcoming'];
    
    echo json_encode([
        'success' => true,
        'total_bookings' => $totalBookings,
        'upcoming_bookings' => $upcomingBookings
    ]);
    
} catch (Exception $e) {
    error_log("Booking stats error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch booking statistics']);
}
?>
