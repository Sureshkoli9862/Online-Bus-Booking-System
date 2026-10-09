<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        $bus_name = trim($_POST['bus_name']);
        $bus_number = trim($_POST['bus_number']);
        $bus_type = $_POST['bus_type'];
        $total_seats = (int)$_POST['total_seats'];
        $route_from = $_POST['route_from'];
        $route_to = $_POST['route_to'];
        $departure_time = $_POST['departure_time'];
        $arrival_time = $_POST['arrival_time'];
        $fare_amount = (float)$_POST['fare_amount'];
        $amenities = $_POST['amenities'] ?? [];
        $driver_name = trim($_POST['driver_name']);
        $driver_phone = trim($_POST['driver_phone']);
        
        // Validation
        if (empty($bus_name) || empty($bus_number) || empty($driver_name) || empty($driver_phone)) {
            throw new Exception('All required fields must be filled');
        }
        
        if ($total_seats < 10 || $total_seats > 60) {
            throw new Exception('Total seats must be between 10 and 60');
        }
        
        if ($fare_amount < 100) {
            throw new Exception('Fare amount must be at least Rs. 100');
        }
        
        // Check if bus number already exists
        $stmt = $pdo->prepare("SELECT id FROM buses WHERE bus_number = ?");
        $stmt->execute([$bus_number]);
        if ($stmt->fetch()) {
            throw new Exception('Bus number already exists. Please use a unique bus number.');
        }
        
        // Insert bus
        $stmt = $pdo->prepare("
            INSERT INTO buses (
                bus_name, bus_number, bus_type, total_seats, 
                route_from, route_to, departure_time, arrival_time, 
                fare_amount, amenities, driver_name, driver_phone, 
                is_active, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        
        $amenities_string = is_array($amenities) ? implode(',', $amenities) : '';
        
        $stmt->execute([
            $bus_name, $bus_number, $bus_type, $total_seats,
            $route_from, $route_to, $departure_time, $arrival_time,
            $fare_amount, $amenities_string, $driver_name, $driver_phone
        ]);
        
        $message = 'Bus added successfully!';
        $message_type = 'success';
        
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Bus - Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .form-container {
            padding: 40px;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-item input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            margin-right: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚌 Add New Bus</h1>
            <p>Add a new bus to your fleet</p>
        </div>
        
        <div class="form-container">
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="bus_name">Bus Name *</label>
                        <input type="text" id="bus_name" name="bus_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bus_number">Bus Number *</label>
                        <input type="text" id="bus_number" name="bus_number" placeholder="e.g., BA-1-PA-1234" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bus_type">Bus Type</label>
                        <select id="bus_type" name="bus_type" required>
                            <option value="">Select Bus Type</option>
                            <option value="AC">AC</option>
                            <option value="Non-AC">Non-AC</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="Super Deluxe">Super Deluxe</option>
                            <option value="Luxury">Luxury</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="total_seats">Total Seats</label>
                        <input type="number" id="total_seats" name="total_seats" min="10" max="60" value="45" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="route_from">Route From</label>
                        <input type="text" id="route_from" name="route_from" placeholder="e.g., Kathmandu" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="route_to">Route To</label>
                        <input type="text" id="route_to" name="route_to" placeholder="e.g., Pokhara" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="departure_time">Departure Time</label>
                        <input type="time" id="departure_time" name="departure_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="arrival_time">Arrival Time</label>
                        <input type="time" id="arrival_time" name="arrival_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fare_amount">Fare Amount (Rs.)</label>
                        <input type="number" id="fare_amount" name="fare_amount" min="100" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="driver_name">Driver Name *</label>
                        <input type="text" id="driver_name" name="driver_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="driver_phone">Driver Phone *</label>
                        <input type="tel" id="driver_phone" name="driver_phone" placeholder="98XXXXXXXX" required>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label>Amenities</label>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="wifi" name="amenities[]" value="WiFi">
                            <label for="wifi">WiFi</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="ac" name="amenities[]" value="AC">
                            <label for="ac">Air Conditioning</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="charging" name="amenities[]" value="Charging Port">
                            <label for="charging">Charging Port</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="entertainment" name="amenities[]" value="Entertainment">
                            <label for="entertainment">Entertainment</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="blanket" name="amenities[]" value="Blanket">
                            <label for="blanket">Blanket</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="water" name="amenities[]" value="Water Bottle">
                            <label for="water">Water Bottle</label>
                        </div>
                    </div>
                </div>
                
                <div class="button-group">
                    <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                    <button type="submit" class="btn">Add Bus</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
