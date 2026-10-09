<?php
// Setup payments table
require_once 'config.php';

try {
    $pdo = getDBConnection();
    
    // Create payments table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id VARCHAR(20) NOT NULL,
        user_id INT NOT NULL,
        payment_id VARCHAR(20) UNIQUE NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        payment_status ENUM('pending', 'confirmed', 'failed', 'refunded') DEFAULT 'pending',
        payment_date DATETIME NULL,
        transaction_id VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_booking_id (booking_id),
        INDEX idx_user_id (user_id),
        INDEX idx_payment_method (payment_method),
        INDEX idx_payment_status (payment_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    echo "<h2>✅ Payments Table Setup Completed!</h2>";
    echo "<p>The payments table has been created successfully with proper structure to store payment methods.</p>";
    echo "<p><strong>Supported Payment Methods:</strong> eSewa, Khalti, Mobile Banking, Bank Transfer, Credit/Debit Card, Cash</p>";
    echo "<p><a href='online_ticket.php'>Go to Online Ticket Booking</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error Setting Up Payments Table:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Make sure the database connection is working properly.</p>";
}
?>
