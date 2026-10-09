<?php
session_start();
require_once 'config.php';

// Check database connection and tables
try {
    $pdo = getDBConnection();
    echo "<h2>Database Connection: ✅ Success</h2>";
    
    // Check if bookings table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'bookings'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Bookings table exists</p>";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE bookings");
        $columns = $stmt->fetchAll();
        echo "<h3>Bookings Table Structure:</h3>";
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Bookings table does not exist</p>";
    }
    
    // Check if payments table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'payments'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Payments table exists</p>";
    } else {
        echo "<p>❌ Payments table does not exist</p>";
    }
    
    // Check current bookings
    echo "<h3>Current Bookings:</h3>";
    $stmt = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5");
    $bookings = $stmt->fetchAll();
    
    if (empty($bookings)) {
        echo "<p>❌ No bookings found in database</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Booking ID</th><th>User ID</th><th>Passenger Name</th><th>Total Amount</th><th>Created At</th></tr>";
        foreach ($bookings as $booking) {
            echo "<tr>";
            echo "<td>{$booking['id']}</td>";
            echo "<td>{$booking['booking_id']}</td>";
            echo "<td>{$booking['user_id']}</td>";
            echo "<td>{$booking['passenger_name']}</td>";
            echo "<td>{$booking['total_amount']}</td>";
            echo "<td>{$booking['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test session
    echo "<h3>Session Info:</h3>";
    echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
    echo "<p>Username: " . ($_SESSION['username'] ?? 'Not set') . "</p>";
    
    // Test booking save with sample data
    echo "<h3>Testing Booking Save:</h3>";
    if (isset($_SESSION['user_id'])) {
        $test_booking_id = 'TEST' . time();
        $stmt = $pdo->prepare("
            INSERT INTO bookings (
                user_id, booking_id, passenger_name, phone, from_location, to_location,
                travel_date, departure_time, bus_name, bus_number, selected_seats,
                seat_count, price_per_seat, total_amount, payment_method, payment_status, booking_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', 'upcoming')
        ");
        
        $result = $stmt->execute([
            $_SESSION['user_id'],
            $test_booking_id,
            'Test User',
            '9999999999',
            'Kathmandu',
            'Pokhara',
            date('Y-m-d'),
            '10:00 AM',
            'Test Bus',
            'TEST123',
            'A1,A2',
            2,
            1000.00,
            2000.00,
            'esewa'
        ]);
        
        if ($result) {
            echo "<p>✅ Test booking saved successfully with ID: $test_booking_id</p>";
            
            // Delete test booking
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE booking_id = ?");
            $stmt->execute([$test_booking_id]);
            echo "<p>✅ Test booking cleaned up</p>";
        } else {
            echo "<p>❌ Failed to save test booking</p>";
            $error = $stmt->errorInfo();
            echo "<p>Error: " . $error[2] . "</p>";
        }
    } else {
        echo "<p>❌ No user session found - please login first</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Database Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php'>Go to Login</a> | <a href='my_bookings.php'>Go to My Bookings</a></p>";
?>
