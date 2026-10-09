<?php
session_start();
require_once 'config.php';

// Check if this is a login attempt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['admin_username'] ?? '');
    $password = $_POST['admin_password'] ?? '';
    
    if (empty($username) || empty($password)) {
        header('Location: admin_login.php?error=1');
        exit();
    }
    
    try {
        $pdo = getDBConnection();
        
        // Find admin by username
        $stmt = $pdo->prepare("SELECT id, username, password_hash, full_name, role FROM admins WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            header('Location: admin_login.php?error=1');
            exit();
        }
        
        // Update last login
        $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$admin['id']]);
        
        // Set admin session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_name'] = $admin['full_name'];
        
    } catch (Exception $e) {
        error_log("Admin login error: " . $e->getMessage());
        header('Location: admin_login.php?error=1');
        exit();
    }
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: admin_login.php');
    exit();
}

// Fetch real data from database
try {
    $pdo = getDBConnection();
    
    // Get statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE is_active = 1");
    $totalUsers = $stmt->fetch()['total_users'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_bookings FROM bookings");
    $totalBookings = $stmt->fetch()['total_bookings'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as active_buses FROM buses WHERE is_active = 1");
    $activeBuses = $stmt->fetch()['active_buses'];
    
    $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total_revenue FROM bookings WHERE payment_status = 'confirmed'");
    $totalRevenue = $stmt->fetch()['total_revenue'];
    
    // Get users data
    $stmt = $pdo->query("SELECT id, username, name, phone, created_at, last_login, is_active FROM users ORDER BY created_at DESC LIMIT 10");
    $users = $stmt->fetchAll();
    
    // Get bookings data with payment info
    $stmt = $pdo->query("
        SELECT b.*, u.username, u.name as user_name, p.payment_status as payment_status_actual, p.payment_method as payment_method_actual
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        LEFT JOIN payments p ON b.booking_id = p.booking_id
        ORDER BY b.created_at DESC 
        LIMIT 20
    ");
    $bookings = $stmt->fetchAll();
    
} catch (Exception $e) {
    $totalUsers = 0;
    $totalBookings = 0;
    $activeBuses = 0;
    $totalRevenue = 0;
    $users = [];
    $bookings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Bus Booking System</title>
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
        }

        .admin-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .admin-title {
            color: white;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-user {
            color: white;
            background: rgba(233, 30, 99, 0.8);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 600;
        }

        .logout-btn {
            color: white;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .dashboard-container {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 36px;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #e91e63;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .data-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .data-table th {
            background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .data-table th:first-child {
            border-radius: 10px 0 0 0;
        }

        .data-table th:last-child {
            border-radius: 0 10px 0 0;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
            color: #555;
        }

        .data-table tr:hover {
            background: #f8f9ff;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-pending {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-completed {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-rejected {
            background: #ffebee;
            color: #d32f2f;
        }

        .status-approved {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .approve-btn, .reject-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 70px;
        }

        .approve-btn {
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
            color: white;
        }

        .approve-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(76, 175, 80, 0.3);
        }

        .reject-btn {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
        }

        .reject-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(244, 67, 54, 0.3);
        }

        .approve-btn:disabled, .reject-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .refresh-btn {
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            float: right;
            margin-bottom: 15px;
        }

        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(66, 133, 244, 0.3);
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 20px 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .data-section {
                padding: 20px 15px;
            }
            
            .data-table {
                font-size: 12px;
            }
            
            .data-table th,
            .data-table td {
                padding: 8px 6px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="admin-title">
            <span>👨‍💼</span>
            Admin Dashboard
        </div>
        <div class="admin-info">
            <div class="admin-user">
                <span>🔐</span>
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            </div>
            <a href="admin_login.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number" id="totalUsers"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎫</div>
                <div class="stat-number" id="totalBookings"><?php echo $totalBookings; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🚌</div>
                <div class="stat-number" id="activeBuses"><?php echo $activeBuses; ?></div>
                <div class="stat-label">Active Buses</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number" id="totalRevenue">Rs. <?php echo number_format($totalRevenue, 0); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <!-- Quick Actions Section -->
        <div class="data-section">
            <div class="section-title">
                <span>⚡</span>
                Quick Actions
            </div>
            <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
                <a href="add_bus.php" class="action-btn" style="background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; padding: 12px 20px; text-decoration: none; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; font-weight: 500; box-shadow: 0 3px 10px rgba(46, 204, 113, 0.3); transition: all 0.3s ease;">
                    <span>🚌</span> Add Bus
                </a>
                <a href="manage_buses.php" class="action-btn" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white; padding: 12px 20px; text-decoration: none; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; font-weight: 500; box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3); transition: all 0.3s ease;">
                    <span>🔧</span> Manage Buses
                </a>
            </div>
        </div>

        <!-- Users Section -->
        <div class="data-section">
            <button class="refresh-btn" onclick="refreshData()">🔄 Refresh</button>
            <div class="section-title">
                <span>👤</span>
                Registered Users
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Registration Date</th>
                        <th>Last Login</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="usersTable">
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                        <td><?php echo $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                        <td><span class="status-badge <?php echo $user['is_active'] ? 'status-active' : 'status-rejected'; ?>"><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #666;">No users found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Booking Information Section -->
        <div class="data-section">
            <div class="section-title">
                <span>📋</span>
                Recent Booking Information
            </div>
            <table class="table table-striped bookings-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Bus Name</th>
                        <th>Bus Number</th>
                        <th>Seats</th>
                        <th>Departure</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <th>Booking Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bookingsTable">
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($booking['username']); ?></td>
                        <td><?php echo htmlspecialchars($booking['bus_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['bus_number']); ?></td>
                        <td><?php echo htmlspecialchars($booking['selected_seats']); ?></td>
                        <td><?php echo htmlspecialchars($booking['departure_time']); ?></td>
                        <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                        <td>Rs. <?php echo number_format($booking['total_amount'], 0); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $booking['payment_status_actual'] ?? $booking['payment_status']; ?>">
                                <?php echo ucfirst($booking['payment_status_actual'] ?? $booking['payment_status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $booking['booking_status']; ?>">
                                <?php echo ucfirst($booking['booking_status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($booking['booking_status'] == 'upcoming'): ?>
                                <button class="action-btn accept-btn" onclick="acceptBooking('<?php echo $booking['booking_id']; ?>')">Accept</button>
                                <button class="action-btn reject-btn" onclick="rejectBooking('<?php echo $booking['booking_id']; ?>')">Reject</button>
                            <?php else: ?>
                                <button class="action-btn info-btn" onclick="viewPaymentHistory('<?php echo $booking['booking_id']; ?>')">Payment History</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; color: #666;">No bookings found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function changeStatus(bookingId, newStatus) {
            const row = document.querySelector(`tr:has(td:first-child:contains('${bookingId}'))`);
            if (!row) {
                // Find row by booking ID
                const rows = document.querySelectorAll('#bookingsTable tr');
                let targetRow = null;
                rows.forEach(r => {
                    if (r.cells[0].textContent === bookingId) {
                        targetRow = r;
                    }
                });
                
                if (!targetRow) return;
                
                const statusCell = targetRow.cells[8];
                const actionCell = targetRow.cells[9];
                
                if (newStatus === 'approved') {
                    statusCell.innerHTML = '<span class="status-badge status-approved">Approved</span>';
                    actionCell.innerHTML = `
                        <div class="action-buttons">
                            <button class="approve-btn" onclick="changeStatus('${bookingId}', 'approved')" disabled>✓ Approved</button>
                            <button class="reject-btn" onclick="changeStatus('${bookingId}', 'rejected')">✗ Reject</button>
                        </div>
                    `;
                    alert(`Booking ${bookingId} has been approved!`);
                } else if (newStatus === 'rejected') {
                    statusCell.innerHTML = '<span class="status-badge status-rejected">Rejected</span>';
                    actionCell.innerHTML = `
                        <div class="action-buttons">
                            <button class="approve-btn" onclick="changeStatus('${bookingId}', 'approved')">✓ Approve</button>
                            <button class="reject-btn" onclick="changeStatus('${bookingId}', 'rejected')" disabled>✗ Rejected</button>
                        </div>
                    `;
                    alert(`Booking ${bookingId} has been rejected!`);
                }
            }
        }

        function refreshData() {
            // Simulate data refresh
            const refreshBtn = document.querySelector('.refresh-btn');
            refreshBtn.innerHTML = '⏳ Refreshing...';
            refreshBtn.disabled = true;
            
            setTimeout(() => {
                refreshBtn.innerHTML = '🔄 Refresh';
                refreshBtn.disabled = false;
                
                // Update stats with random values for demo
                document.getElementById('totalUsers').textContent = Math.floor(Math.random() * 50) + 10;
                document.getElementById('totalBookings').textContent = Math.floor(Math.random() * 100) + 20;
                document.getElementById('activeBuses').textContent = Math.floor(Math.random() * 15) + 5;
                
                alert('Data refreshed successfully!');
            }, 2000);
        }

        // Auto-refresh every 5 minutes
        setInterval(refreshData, 300000);
        
        // Add some interactivity
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'translateY(-5px)';
                }, 150);
            });
        });

        function acceptBooking(bookingId) {
            if (confirm('Are you sure you want to accept this booking?')) {
                fetch('admin_booking_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'accept',
                        booking_id: bookingId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Booking accepted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to accept booking');
                });
            }
        }

        function rejectBooking(bookingId) {
            if (confirm('Are you sure you want to reject this booking?')) {
                fetch('admin_booking_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'reject',
                        booking_id: bookingId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Booking rejected successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to reject booking');
                });
            }
        }

        function viewPaymentHistory(bookingId) {
            fetch('get_payment_history.php?booking_id=' + bookingId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<div class="payment-history">';
                        if (data.payment) {
                            const payment = data.payment;
                            html += `
                                <div class="payment-item">
                                    <h3>Payment Details</h3>
                                    <p><strong>Payment ID:</strong> ${payment.payment_id}</p>
                                    <p><strong>Amount:</strong> Rs. ${parseFloat(payment.amount).toLocaleString()}</p>
                                    <p><strong>Method:</strong> ${payment.payment_method.toUpperCase()}</p>
                                    <p><strong>Status:</strong> <span class="status-${payment.payment_status}">${payment.payment_status.toUpperCase()}</span></p>
                                    <p><strong>Date:</strong> ${payment.payment_date || 'N/A'}</p>
                                    <p><strong>Transaction ID:</strong> ${payment.transaction_id || 'N/A'}</p>
                                </div>
                            `;
                        } else {
                            html += '<p>No payment record found.</p>';
                        }
                        html += '</div>';
                        document.getElementById('paymentDetails').innerHTML = html;
                        document.getElementById('paymentModal').style.display = 'block';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load payment history');
                });
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('paymentModal');
            if (event.target === modal) {
                closePaymentModal();
            }
        }
    </script>

    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-completed, .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-upcoming {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-cancelled, .status-refunded {
            background: #f8d7da;
            color: #721c24;
        }
        
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            position: relative;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 10px;
        }
        
        .close:hover {
            color: black;
        }
        
        .payment-history {
            margin-top: 20px;
        }
        
        .payment-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .payment-item h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .payment-item p {
            margin: 5px 0;
            color: #666;
        }
        
        /* Enhanced Action Buttons */
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin: 0 2px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .accept-btn {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
        }
        
        .accept-btn:hover {
            background: linear-gradient(135deg, #45a049, #4CAF50);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.4);
        }
        
        .reject-btn {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
            box-shadow: 0 2px 4px rgba(244, 67, 54, 0.3);
        }
        
        .reject-btn:hover {
            background: linear-gradient(135deg, #d32f2f, #f44336);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(244, 67, 54, 0.4);
        }
        
        .info-btn {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            box-shadow: 0 2px 4px rgba(33, 150, 243, 0.3);
        }
        
        .info-btn:hover {
            background: linear-gradient(135deg, #1976D2, #2196F3);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(33, 150, 243, 0.4);
        }
        
        /* Enhanced Table Styling */
        .bookings-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .bookings-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 8px;
            font-weight: 600;
            text-align: center;
            border: none;
        }
        
        .bookings-table td {
            padding: 10px 8px;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        .bookings-table tr:hover {
            background: #f8f9ff;
        }
    </style>
</body>
</html>
