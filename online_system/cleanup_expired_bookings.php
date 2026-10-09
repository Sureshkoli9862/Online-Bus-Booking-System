<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();
    
    // Update expired bookings to cancelled status
    // Bookings expire after 2 hours if travel date is today or past
    $stmt = $pdo->prepare("
        UPDATE bookings 
        SET booking_status = 'expired', payment_status = 'expired'
        WHERE booking_status = 'upcoming'
        AND payment_status = 'pending'
        AND (
            travel_date < CURDATE()
            OR (travel_date = CURDATE() AND TIMESTAMPDIFF(HOUR, created_at, NOW()) >= 2)
        )
    ");
    
    $result = $stmt->execute();
    $affected_rows = $stmt->rowCount();
    
    if ($affected_rows > 0) {
        error_log("Cleanup: $affected_rows expired bookings updated to expired status");
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Cleanup completed. $affected_rows bookings expired.",
        'expired_count' => $affected_rows
    ]);
    
} catch (Exception $e) {
    error_log("Cleanup error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Cleanup failed: ' . $e->getMessage()
    ]);
}
?>
