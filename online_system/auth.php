<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    $pdo = getDBConnection();
    
    switch ($action) {
        case 'register':
            handleRegister($pdo, $input);
            break;
        case 'login':
            handleLogin($pdo, $input);
            break;
        case 'logout':
            handleLogout($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function handleRegister($pdo, $input) {
    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    // Validation
    if (empty($username) || strlen($username) < 3) {
        echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters']);
        return;
    }
    
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email or username already exists']);
        return;
    }
    
    // Hash password and create user
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$username, $email, $passwordHash])) {
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed']);
    }
}

function handleLogin($pdo, $input) {
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        return;
    }
    
    // Find user
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        return;
    }
    
    // Create session
    $sessionToken = bin2hex(random_bytes(32)); // 64 characters
    $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
    
    $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $sessionToken, $expiresAt]);
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['session_token'] = $sessionToken;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Login successful',
        'user' => ['id' => $user['id'], 'username' => $user['username']]
    ]);
}

function handleLogout($pdo) {
    if (isset($_SESSION['session_token'])) {
        // Remove session from database
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE session_token = ?");
        $stmt->execute([$_SESSION['session_token']]);
    }
    
    // Destroy session
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}
?>
