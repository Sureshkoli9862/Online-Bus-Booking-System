<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get bus details from request
$bus_number = $_GET['bus_number'] ?? '';
$travel_date = $_GET['travel_date'] ?? date('Y-m-d');

if (empty($bus_number)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Bus number is required']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get all bookings for this bus and date with different payment statuses
    // Only show bookings that are still valid (within 24 hours or for future travel dates)
    $stmt = $pdo->prepare("
        SELECT 
            selected_seats,
            payment_status,
            booking_status,
            created_at,
            travel_date
        FROM bookings 
        WHERE bus_number = ? 
        AND travel_date = ? 
        AND booking_status != 'cancelled'
        AND (
            travel_date > CURDATE() 
            OR (travel_date = CURDATE() AND TIMESTAMPDIFF(HOUR, created_at, NOW()) < 24)
        )
        ORDER BY created_at ASC
    ");
    
    $stmt->execute([$bus_number, $travel_date]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $seat_status = [];
    
    foreach ($bookings as $booking) {
        $seats = explode(',', $booking['selected_seats']);
        foreach ($seats as $seat) {
            $seat = trim($seat);
            if (!empty($seat)) {
                // Only mark seats as occupied if payment is confirmed/completed
                if ($booking['payment_status'] === 'confirmed' || $booking['payment_status'] === 'completed') {
                    $seat_status[$seat] = 'occupied';
                } else {
                    // For pending payments, mark as pending (not occupied)
                    $seat_status[$seat] = 'pending';
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'seat_status' => $seat_status,
        'bus_number' => $bus_number,
        'travel_date' => $travel_date
    ]);
    
} catch (Exception $e) {
    error_log("Seat status error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to get seat status: ' . $e->getMessage()
    ]);
}
?>
