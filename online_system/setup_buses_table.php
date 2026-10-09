<?php
require_once 'config.php';

echo "<h2>🚌 Setting up Buses Table</h2>";

try {
    $pdo = getDBConnection();
    echo "✅ Database connection successful<br><br>";
    
    // Create buses table
    echo "<h3>1. Creating buses table...</h3>";
    $create_table = "
    CREATE TABLE IF NOT EXISTS buses (
        id int(11) NOT NULL AUTO_INCREMENT,
        bus_name varchar(100) NOT NULL,
        bus_number varchar(20) NOT NULL UNIQUE,
        bus_type enum('AC','Non-AC','Deluxe','Super Deluxe','Luxury') NOT NULL,
        total_seats int(11) NOT NULL DEFAULT 45,
        route_from varchar(50) NOT NULL,
        route_to varchar(50) NOT NULL,
        departure_time time NOT NULL,
        arrival_time time NOT NULL,
        fare_amount decimal(10,2) NOT NULL,
        amenities text DEFAULT NULL,
        driver_name varchar(100) NOT NULL,
        driver_phone varchar(15) NOT NULL,
        is_active tinyint(1) NOT NULL DEFAULT 1,
        created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_bus_number (bus_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($create_table);
    echo "✅ Buses table created successfully!<br><br>";
    
    // Insert sample buses
    echo "<h3>2. Adding sample buses...</h3>";
    $buses = [
        ['Deluxe Express', 'BA-1-PA-1234', 'Deluxe', 45, 'kanchanpur', 'kathmandu', '06:00:00', '18:00:00', 2200.00, 'WiFi,AC,Charging Port', 'Ram Bahadur', '9841234567'],
        ['Luxury Cruiser', 'BA-2-KA-5678', 'Luxury', 40, 'dhangadi', 'kathmandu', '08:00:00', '19:00:00', 3600.00, 'WiFi,AC,Entertainment', 'Shyam Kumar', '9856789012'],
        ['Super Comfort', 'BA-3-LA-9012', 'Super Deluxe', 42, 'bhasi', 'pokhara', '10:00:00', '18:00:00', 1500.00, 'AC,Charging Port', 'Hari Prasad', '9823456789']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO buses (bus_name, bus_number, bus_type, total_seats, route_from, route_to, departure_time, arrival_time, fare_amount, amenities, driver_name, driver_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $added = 0;
    foreach ($buses as $bus) {
        $stmt->execute($bus);
        if ($stmt->rowCount() > 0) {
            $added++;
            echo "✅ Added: {$bus[0]}<br>";
        } else {
            echo "⚠️ Skipped: {$bus[0]} (already exists)<br>";
        }
    }
    
    echo "<br><strong>✅ Setup completed! Added $added buses.</strong><br><br>";
    
    // Show total count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM buses");
    $total = $stmt->fetch()['total'];
    echo "<strong>Total buses in system: $total</strong><br><br>";
    
} catch (Exception $e) {
    echo "<strong>❌ Error:</strong> " . $e->getMessage() . "<br><br>";
}

echo "<div style='margin-top: 30px;'>";
echo "<a href='admin_dashboard.php' style='padding: 12px 24px; background: #3498db; color: white; text-decoration: none; border-radius: 6px; margin-right: 10px; display: inline-block;'>← Dashboard</a>";
echo "<a href='add_bus.php' style='padding: 12px 24px; background: #27ae60; color: white; text-decoration: none; border-radius: 6px; margin-right: 10px; display: inline-block;'>Add Bus</a>";
echo "<a href='manage_buses.php' style='padding: 12px 24px; background: #e74c3c; color: white; text-decoration: none; border-radius: 6px; display: inline-block;'>Manage Buses</a>";
echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f8f9fa; }
h2, h3 { color: #2c3e50; }
</style>
