<?php
// Complete Database setup script
require_once 'config.php';

try {
    // Connect to MySQL server (without database)
    $host = 'localhost';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute the complete SQL file
    $sql = file_get_contents('complete_database_setup.sql');
    
    // Remove comments and split by semicolon
    $sql = preg_replace('/--.*$/m', '', $sql);
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
            } catch (Exception $e) {
                // Continue on duplicate key errors
                if (strpos($e->getMessage(), 'Duplicate') === false) {
                    throw $e;
                }
            }
        }
    }
    
    echo "<h2>✅ Database Setup Completed Successfully!</h2>";
    echo "<p>All tables created and sample data inserted.</p>";
    echo "<p><strong>Default Admin:</strong> Username: admin, Password: admin123</p>";
    echo "<p><strong>Sample User:</strong> Username: suresh, Password: password123</p>";
    echo "<p><a href='index.php'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error Setting Up Database:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Make sure XAMPP/WAMP is running and MySQL is accessible.</p>";
}
?>
