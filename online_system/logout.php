<?php
require_once 'config.php';

if (isset($_SESSION['session_token'])) {
    try {
        $pdo = getDBConnection();
        // Remove session from database
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE session_token = ?");
        $stmt->execute([$_SESSION['session_token']]);
    } catch (Exception $e) {
        // Continue with logout even if database operation fails
    }
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: index.php');
exit;
?>
