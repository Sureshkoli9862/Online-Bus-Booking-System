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

// Handle bus status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $pdo = getDBConnection();
        
        if ($_POST['action'] === 'toggle_status') {
            $bus_id = (int)$_POST['bus_id'];
            $stmt = $pdo->prepare("UPDATE buses SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$bus_id]);
            $message = 'Bus status updated successfully!';
            $message_type = 'success';
        }
        
        if ($_POST['action'] === 'delete_bus') {
            $bus_id = (int)$_POST['bus_id'];
            $stmt = $pdo->prepare("DELETE FROM buses WHERE id = ?");
            $stmt->execute([$bus_id]);
            $message = 'Bus deleted successfully!';
            $message_type = 'success';
        }
        
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Fetch buses data
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM buses ORDER BY created_at DESC");
    $buses = $stmt->fetchAll();
} catch (Exception $e) {
    $buses = [];
    $error_message = 'Failed to load buses data';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Buses - Admin Panel</title>
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
            max-width: 1200px;
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
        
        .content {
            padding: 30px;
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
        
        .table-container {
            overflow-x: auto;
            margin: 20px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 10px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        tr:hover {
            background: #f8f9ff;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
            transition: all 0.3s ease;
        }
        
        .btn-toggle {
            background: #007bff;
            color: white;
        }
        
        .btn-toggle:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            font-size: 16px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 12px 25px;
            font-size: 16px;
            margin-right: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .action-buttons {
            margin-bottom: 20px;
        }
        
        .no-buses {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .no-buses h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }
            
            .content {
                padding: 20px;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 8px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Manage Buses</h1>
            <p>View, edit, and manage your bus fleet</p>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                <a href="add_bus.php" class="btn btn-primary">+ Add New Bus</a>
            </div>
            
            <?php if (count($buses) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Bus Name</th>
                                <th>Bus Number</th>
                                <th>Type</th>
                                <th>Seats</th>
                                <th>Route</th>
                                <th>Departure</th>
                                <th>Fare</th>
                                <th>Driver</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($buses as $bus): ?>
                                <tr>
                                    <td><?php echo $bus['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($bus['bus_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($bus['bus_number']); ?></td>
                                    <td><?php echo htmlspecialchars($bus['bus_type']); ?></td>
                                    <td><?php echo $bus['total_seats']; ?></td>
                                    <td><?php echo htmlspecialchars($bus['route_from'] . ' → ' . $bus['route_to']); ?></td>
                                    <td><?php echo date('g:i A', strtotime($bus['departure_time'])); ?></td>
                                    <td>Rs. <?php echo number_format($bus['fare_amount'], 0); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($bus['driver_name']); ?><br>
                                        <small><?php echo htmlspecialchars($bus['driver_phone']); ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $bus['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $bus['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to toggle bus status?')">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="bus_id" value="<?php echo $bus['id']; ?>">
                                            <button type="submit" class="btn btn-toggle">
                                                <?php echo $bus['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </form>
                                        
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this bus? This action cannot be undone.')">
                                            <input type="hidden" name="action" value="delete_bus">
                                            <input type="hidden" name="bus_id" value="<?php echo $bus['id']; ?>">
                                            <button type="submit" class="btn btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-buses">
                    <h3>No buses found</h3>
                    <p>You haven't added any buses yet. Click "Add New Bus" to get started.</p>
                    <br>
                    <a href="add_bus.php" class="btn btn-primary">+ Add Your First Bus</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
