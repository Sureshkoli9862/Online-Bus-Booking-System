<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        // Get and validate input data
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        $errors = [];
        
        if (empty($name)) {
            $errors[] = "Name is required";
        } elseif (strlen($name) < 2) {
            $errors[] = "Name must be at least 2 characters long";
        }
        
        if (empty($phone)) {
            $errors[] = "Phone number is required";
        } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
            $errors[] = "Phone number must be exactly 10 digits";
        }
        
        if (empty($address)) {
            $errors[] = "Address is required";
        } elseif (strlen($address) < 5) {
            $errors[] = "Address must be at least 5 characters long";
        }
        
        // Check if phone number is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
        $stmt->execute([$phone, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $errors[] = "Phone number is already registered by another user";
        }
        
        // Password validation (only if user wants to change password)
        if (!empty($new_password) || !empty($confirm_password)) {
            if (empty($current_password)) {
                $errors[] = "Current password is required to change password";
            } else {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if (!password_verify($current_password, $user['password_hash'])) {
                    $errors[] = "Current password is incorrect";
                }
            }
            
            if (strlen($new_password) < 8) {
                $errors[] = "New password must be at least 8 characters long";
            }
            
            if ($new_password !== $confirm_password) {
                $errors[] = "New password and confirm password do not match";
            }
        }
        
        if (!empty($errors)) {
            $response['message'] = implode(', ', $errors);
        } else {
            // Update user profile
            if (!empty($new_password)) {
                // Update with new password
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ?, password_hash = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $address, $password_hash, $_SESSION['user_id']]);
            } else {
                // Update without password change
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $address, $_SESSION['user_id']]);
            }
            
            $response['success'] = true;
            $response['message'] = 'Profile updated successfully!';
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
