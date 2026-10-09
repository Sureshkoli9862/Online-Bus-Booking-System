<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit feedback']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get form data
    $feedback_type = trim($_POST['feedback_type'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Validate required fields
    if (empty($feedback_type) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit;
    }

    // Connect to database
    $pdo = getDBConnection();

    // Create feedback table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            feedback_type ENUM('suggestion', 'other') NOT NULL,
            message TEXT NOT NULL,
            status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ";
    $pdo->exec($createTableSQL);

    // Insert feedback
    $stmt = $pdo->prepare("
        INSERT INTO feedback (user_id, feedback_type, message) 
        VALUES (?, ?, ?)
    ");
    
    $result = $stmt->execute([$user_id, $feedback_type, $message]);

    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Thank you for your feedback! We appreciate your input and will review it shortly.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit feedback. Please try again.']);
    }

} catch (Exception $e) {
    error_log("Feedback submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while submitting feedback. Please try again later.']);
}
?>
