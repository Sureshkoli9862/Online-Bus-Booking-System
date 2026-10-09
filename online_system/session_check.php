<?php
require_once 'config.php';

function checkUserSession() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
        header('Location: index.php');
        exit;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Check if session is valid and not expired
        $stmt = $pdo->prepare("
            SELECT s.user_id, s.expires_at, u.is_active 
            FROM user_sessions s 
            JOIN users u ON s.user_id = u.id 
            WHERE s.session_token = ? AND s.expires_at > NOW()
        ");
        $stmt->execute([$_SESSION['session_token']]);
        $session = $stmt->fetch();
        
        if (!$session || !$session['is_active']) {
            // Invalid or expired session
            session_destroy();
            header('Location: index.php');
            exit;
        }
        
        // Session is valid, extend expiration
        $newExpiry = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        $stmt = $pdo->prepare("UPDATE user_sessions SET expires_at = ? WHERE session_token = ?");
        $stmt->execute([$newExpiry, $_SESSION['session_token']]);
        
    } catch (Exception $e) {
        session_destroy();
        header('Location: index.php');
        exit;
    }
}

// Call the function to check session
checkUserSession();
?>
