<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();
    
    // Update admin password hash for 'admin' user
    $adminPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$adminPasswordHash]);
    
    // Update manager password hash for 'manager' user  
    $managerPasswordHash = password_hash('manager123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE username = 'manager'");
    $stmt->execute([$managerPasswordHash]);
    
    // Update user password hashes
    $userPasswordHash = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?");
    $stmt->execute([$userPasswordHash]);
    
    echo "<h2>✅ Password Hashes Updated Successfully!</h2>";
    echo "<p><strong>Admin Login:</strong> Username: admin, Password: admin123</p>";
    echo "<p><strong>Manager Login:</strong> Username: manager, Password: manager123</p>";
    echo "<p><strong>User Login:</strong> Any user phone number, Password: password123</p>";
    echo "<p><a href='admin_login.php'>Go to Admin Login</a></p>";
    echo "<p><a href='index.php'>Go to User Login</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error Updating Passwords:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
