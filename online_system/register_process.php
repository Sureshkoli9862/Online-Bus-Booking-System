<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
if (empty($name) || strlen($name) < 2) {
    $_SESSION['message'] = 'Name must be at least 2 characters long';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

if (empty($phone) || strlen($phone) < 10) {
    $_SESSION['message'] = 'Phone number must be at least 10 digits';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

if (empty($address) || strlen($address) < 5) {
    $_SESSION['message'] = 'Address must be at least 5 characters long';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

if (strlen($password) < PASSWORD_MIN_LENGTH) {
    $_SESSION['message'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        $_SESSION['message'] = 'Phone number already exists';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit;
    }
    
    // Hash password and create user
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, name, phone, address, password_hash) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$name, $name, $phone, $address, $passwordHash])) {
        $_SESSION['message'] = 'Registration successful! Please login.';
        $_SESSION['message_type'] = 'success';
        header('Location: index.php');
    } else {
        $_SESSION['message'] = 'Registration failed. Please try again.';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
    }
    
} catch (Exception $e) {
    $_SESSION['message'] = 'Registration failed. Please try again.';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
}
?>
