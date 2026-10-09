<?php
session_start();
require_once 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all requests for debugging
error_log("Save booking request received from user: " . ($_SESSION['user_id'] ?? 'unknown'));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON data
$input = json_decode(file_get_contents('php://input'), true);

// Log the input data for debugging
error_log("Booking input data: " . json_encode($input));

// Check if input is null (JSON decode failed)
if ($input === null) {
    error_log("JSON decode failed - raw input: " . file_get_contents('php://input'));
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Validate required fields
$required_fields = [
    'passenger_name', 'phone', 'from_location', 'to_location', 
    'travel_date', 'departure_time', 'bus_name', 'bus_number',
    'selected_seats', 'seat_count', 'price_per_seat', 'total_amount', 'payment_method'
];

foreach ($required_fields as $field) {
    if (!isset($input[$field]) || $input[$field] === '' || $input[$field] === null) {
        error_log("Missing field '$field' in input: " . json_encode($input));
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => "Missing required field: $field", 
            'received_data' => $input,
            'missing_field' => $field
        ]);
        exit;
    }
}

// Additional validation for payment_method specifically
$valid_payment_methods = ['esewa', 'khalti', 'mobile', 'bank', 'card', 'cash'];
if (empty($input['payment_method']) || !in_array($input['payment_method'], $valid_payment_methods)) {
    error_log("Invalid payment method: " . ($input['payment_method'] ?? 'null'));
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid payment method. Must be one of: ' . implode(', ', $valid_payment_methods),
        'received_payment_method' => $input['payment_method'] ?? 'null'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Generate unique booking ID
    $booking_id = 'BK' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    
    // Insert booking data
    $stmt = $pdo->prepare("
        INSERT INTO bookings (
            user_id, booking_id, passenger_name, phone, from_location, to_location,
            travel_date, departure_time, bus_name, bus_number, selected_seats,
            seat_count, price_per_seat, total_amount, payment_method, payment_status, booking_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'upcoming')
    ");
    
    // All bookings start as confirmed for immediate seat blocking
    $payment_status = 'confirmed';
    
    $result = $stmt->execute([
        $_SESSION['user_id'],
        $booking_id,
        $input['passenger_name'],
        $input['phone'],
        $input['from_location'],
        $input['to_location'],
        $input['travel_date'],
        $input['departure_time'],
        $input['bus_name'],
        $input['bus_number'],
        $input['selected_seats'],
        $input['seat_count'],
        $input['price_per_seat'],
        $input['total_amount'],
        $input['payment_method'],
        $payment_status
    ]);
    
    if ($result) {
        error_log("Booking saved successfully with ID: " . $booking_id);
        
        // Create payment record - all payments start as pending until admin approval
        $payment_id = 'PAY' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));
        $payment_date = null; // Only set when admin confirms
        
        $payment_stmt = $pdo->prepare("
            INSERT INTO payments (booking_id, user_id, payment_id, amount, payment_method, payment_status, payment_date)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $payment_result = $payment_stmt->execute([
            $booking_id,
            $_SESSION['user_id'],
            $payment_id,
            $input['total_amount'],
            $input['payment_method'],
            'confirmed',
            $payment_date
        ]);
        
        if ($payment_result) {
            error_log("Payment record created with ID: " . $payment_id);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Booking saved successfully',
            'booking_id' => $booking_id,
            'payment_id' => $payment_id
        ]);
    } else {
        error_log("Failed to execute booking insert query");
        throw new Exception('Failed to save booking');
    }
    
} catch (Exception $e) {
    error_log("Booking save error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to save booking: ' . $e->getMessage(),
        'error_details' => $e->getMessage()
    ]);
}
?>
