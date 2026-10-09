<?php
// Script to apply seat availability updates to the database
require_once 'config.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>Applying Seat Availability Updates...</h2>";
    
    // Drop existing triggers
    echo "<p>Dropping existing triggers...</p>";
    $pdo->exec("DROP TRIGGER IF EXISTS update_bus_seats_after_booking");
    $pdo->exec("DROP TRIGGER IF EXISTS restore_bus_seats_after_cancel");
    
    // Create new trigger: Only reduce seats when admin confirms booking (payment status changes to confirmed)
    echo "<p>Creating new admin approval trigger...</p>";
    $pdo->exec("
        CREATE TRIGGER update_bus_seats_on_admin_approval
        AFTER UPDATE ON bookings
        FOR EACH ROW
        BEGIN
            -- When admin approves booking (payment status changes from pending to confirmed)
            IF OLD.payment_status = 'pending' AND NEW.payment_status = 'confirmed' THEN
                UPDATE buses 
                SET available_seats = available_seats - NEW.seat_count 
                WHERE bus_number = NEW.bus_number;
            END IF;
            
            -- When booking is cancelled or refunded, restore seats (only if they were previously blocked)
            IF (OLD.payment_status = 'confirmed' OR OLD.payment_status = 'completed') 
               AND (NEW.payment_status = 'refunded' OR NEW.booking_status = 'cancelled') THEN
                UPDATE buses 
                SET available_seats = available_seats + NEW.seat_count 
                WHERE bus_number = NEW.bus_number;
            END IF;
        END
    ");
    
    // Trigger to restore bus seats when booking is cancelled
    echo "<p>Creating booking cancellation trigger...</p>";
    $pdo->exec("
        CREATE TRIGGER restore_bus_seats_after_cancel
        AFTER UPDATE ON bookings
        FOR EACH ROW
        BEGIN
            IF OLD.booking_status != 'cancelled' AND NEW.booking_status = 'cancelled' THEN
                -- Only restore seats if payment was confirmed (seats were actually blocked)
                IF OLD.payment_status = 'confirmed' OR OLD.payment_status = 'completed' THEN
                    UPDATE buses 
                    SET available_seats = available_seats + NEW.seat_count 
                    WHERE bus_number = NEW.bus_number;
                END IF;
            END IF;
        END
    ");
    
    // Remove cash payment trigger since all bookings now require admin approval
    echo "<p>All bookings now require admin approval - no automatic seat blocking...</p>";
    
    // Update existing bookings to reflect correct seat availability
    echo "<p>Resetting bus seat availability...</p>";
    $pdo->exec("UPDATE buses b SET available_seats = total_seats");
    
    echo "<p>Recalculating seat availability based on confirmed bookings...</p>";
    $pdo->exec("
        UPDATE buses b 
        SET available_seats = available_seats - (
            SELECT COALESCE(SUM(seat_count), 0) 
            FROM bookings 
            WHERE bus_number = b.bus_number 
            AND booking_status != 'cancelled'
            AND (payment_status = 'confirmed' OR payment_status = 'completed')
        )
    ");
    
    // Ensure available_seats doesn't go below 0
    $pdo->exec("UPDATE buses SET available_seats = 0 WHERE available_seats < 0");
    
    echo "<div style='color: green; font-weight: bold; margin-top: 20px;'>";
    echo "✅ Seat availability system updated successfully!<br>";
    echo "✅ Seats will now only be blocked when payment is confirmed<br>";
    echo "✅ Pending payments will show as orange (available for selection)<br>";
    echo "✅ Confirmed payments will show as gray (occupied)<br>";
    echo "</div>";
    
    // Show current bus availability
    echo "<h3>Current Bus Availability:</h3>";
    $stmt = $pdo->query("SELECT bus_name, bus_number, total_seats, available_seats FROM buses ORDER BY bus_name");
    echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr><th>Bus Name</th><th>Bus Number</th><th>Total Seats</th><th>Available Seats</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['bus_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['bus_number']) . "</td>";
        echo "<td>" . $row['total_seats'] . "</td>";
        echo "<td>" . $row['available_seats'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>";
    echo "❌ Error updating seat availability system: " . $e->getMessage();
    echo "</div>";
}
?>
