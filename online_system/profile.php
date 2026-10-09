<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch user data from database
try {
    $pdo = getDBConnection();
    
    // Get user information
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Get total bookings count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $totalBookings = $stmt->fetch()['total'];
    
    // Get upcoming bookings count
    $stmt = $pdo->prepare("SELECT COUNT(*) as upcoming FROM bookings WHERE user_id = ? AND booking_status = 'upcoming' AND travel_date >= CURDATE()");
    $stmt->execute([$_SESSION['user_id']]);
    $upcomingBookings = $stmt->fetch()['upcoming'];
    
    // Get total amount spent
    $stmt = $pdo->prepare("SELECT SUM(total_amount) as total_spent FROM bookings WHERE user_id = ? AND payment_status = 'confirmed'");
    $stmt->execute([$_SESSION['user_id']]);
    $totalSpent = $stmt->fetch()['total_spent'] ?? 0;
    
    if (!$user) {
        // Fallback data if user not found
        $user = [
            'username' => $_SESSION['username'] ?? 'User',
            'name' => $_SESSION['username'] ?? 'User Name',
            'phone' => 'Not Available',
            'address' => 'Not Available',
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => date('Y-m-d H:i:s')
        ];
        $totalBookings = 0;
        $upcomingBookings = 0;
        $totalSpent = 0;
    }
    
} catch (Exception $e) {
    // Fallback data on error
    $user = [
        'username' => $_SESSION['username'] ?? 'User',
        'name' => $_SESSION['username'] ?? 'User Name',
        'phone' => 'Database Error',
        'address' => 'Database Error',
        'created_at' => date('Y-m-d H:i:s'),
        'last_login' => date('Y-m-d H:i:s')
    ];
    $totalBookings = 0;
    $upcomingBookings = 0;
    $totalSpent = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Online Bus Booking</title>
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
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
        }

        .sidebar-title {
            color: white;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .sidebar-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: 5px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar-nav a i {
            margin-right: 10px;
            font-size: 16px;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            max-width: calc(100vw - 250px);
            overflow-x: hidden;
        }

        .page-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .page-title {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
        }

        /* Background decorative elements */
        .main-content::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            animation: float 20s infinite linear;
            z-index: -1;
        }

        @keyframes float {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(-50px) translateY(-50px); }
        }

        /* Profile Card */
        .profile-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 32px;
            color: white;
        }

        .profile-name {
            color: white;
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .profile-role {
            color: white;
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }

            .main-content {
                margin-left: 0;
            }

            .profile-details {
                grid-template-columns: 1fr;
            }
        }

        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .detail-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .detail-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .detail-value {
            color: white;
            font-size: 16px;
            font-weight: 600;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #4285f4, #1976d2, #42a5f5);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: white;
            margin: 0 auto 25px;
            box-shadow: 0 8px 25px rgba(66, 133, 244, 0.3);
        }

        .profile-name {
            font-size: 32px;
            font-weight: 700;
            color: #1976d2;
            margin-bottom: 10px;
        }

        .profile-email {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .profile-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-box {
            background: #f0f7ff;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #4285f4;
        }

        .detail-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .detail-value {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }

        .edit-btn {
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(66, 133, 244, 0.3);
            margin: 10px 5px;
        }

        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(66, 133, 244, 0.4);
        }

        .cancel-btn {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
            margin: 10px 5px;
        }

        .cancel-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
        }

        .edit-form {
            display: none;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 30px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            max-width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #4285f4;
            box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.2);
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .password-section {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 20px;
            margin-top: 20px;
        }

        .password-section h4 {
            color: white;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #28a745;
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #4285f4;
            margin-bottom: 5px;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }

            .main-content {
                margin-left: 0;
                max-width: 100vw;
                padding: 15px;
            }

            .profile-details {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            
            .profile-content {
                padding: 0 15px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-title">Dashboard</div>
            <div class="sidebar-subtitle">Welcome, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?></div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="dashboard1.php"><i>🏠</i> Dashboard</a></li>
            <li><a href="online_ticket.php"><i>🎫</i> Online Ticket</a></li>
            <li><a href="my_bookings_new.php"><i>📋</i> My Bookings</a></li>
            <li><a href="profile.php" class="active"><i>👤</i> Profile</a></li>
            <li><a href="help_support.php"><i>❓</i> Help & Support</a></li>
            <li><a href="logout.php"><i>🚪</i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">User Profile</h1>
            <p class="page-subtitle">Manage your account information and preferences</p>
        </div>

        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    👤
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($user['name'] ?? $user['username']); ?></div>
                <div class="profile-role">Bus Booking User</div>
            </div>
            
            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $totalBookings; ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $upcomingBookings; ?></div>
                    <div class="stat-label">Upcoming Trips</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">Rs. <?php echo number_format($totalSpent); ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></div>
                    <div class="stat-label">Account Status</div>
                </div>
            </div>
            
            <!-- Profile Details -->
            <div class="profile-details">
                <div class="detail-box">
                    <div class="detail-label">Full Name</div>
                    <div class="detail-value"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Username</div>
                    <div class="detail-value"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Phone Number</div>
                    <div class="detail-value"><?php echo htmlspecialchars($user['phone']); ?></div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Address</div>
                    <div class="detail-value"><?php echo htmlspecialchars($user['address']); ?></div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Member Since</div>
                    <div class="detail-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Last Login</div>
                    <div class="detail-value"><?php echo $user['last_login'] ? date('M d, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?></div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div style="text-align: center; margin-top: 30px;">
                <button class="edit-btn" onclick="toggleEditForm()">Edit Profile</button>
            </div>
            
            <!-- Edit Form -->
            <div id="editForm" class="edit-form">
                <div id="alertContainer"></div>
                
                <form id="profileForm">
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone']); ?>" required pattern="[0-9]{10}" placeholder="9812345678">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="address">Address *</label>
                        <input type="text" id="address" name="address" class="form-input" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                    </div>
                    
                    <div class="password-section">
                        <h4>Change Password (Optional)</h4>
                        
                        <div class="form-group">
                            <label class="form-label" for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-input" placeholder="Enter current password">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-input" placeholder="Enter new password (min 8 characters)">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Confirm new password">
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 30px;">
                        <button type="submit" class="edit-btn">Save Changes</button>
                        <button type="button" class="cancel-btn" onclick="toggleEditForm()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    <script>
        function toggleEditForm() {
            const form = document.getElementById('editForm');
            const isVisible = form.style.display === 'block';
            form.style.display = isVisible ? 'none' : 'block';
            
            if (!isVisible) {
                // Clear any previous alerts
                document.getElementById('alertContainer').innerHTML = '';
            }
        }
        
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            alertContainer.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
        }
        
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Basic client-side validation
            const name = formData.get('name').trim();
            const phone = formData.get('phone').trim();
            const address = formData.get('address').trim();
            const newPassword = formData.get('new_password');
            const confirmPassword = formData.get('confirm_password');
            
            if (!name || name.length < 2) {
                showAlert('Name must be at least 2 characters long', 'error');
                return;
            }
            
            if (!phone || !/^[0-9]{10}$/.test(phone)) {
                showAlert('Phone number must be exactly 10 digits', 'error');
                return;
            }
            
            if (!address || address.length < 5) {
                showAlert('Address must be at least 5 characters long', 'error');
                return;
            }
            
            if (newPassword && newPassword !== confirmPassword) {
                showAlert('New password and confirm password do not match', 'error');
                return;
            }
            
            if (newPassword && newPassword.length < 8) {
                showAlert('New password must be at least 8 characters long', 'error');
                return;
            }
            
            // Submit form
            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    // Reload page after 2 seconds to show updated data
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('An error occurred while updating profile', 'error');
                console.error('Error:', error);
            });
        });
        
        // Password field validation
        document.getElementById('new_password').addEventListener('input', function() {
            const confirmField = document.getElementById('confirm_password');
            const currentField = document.getElementById('current_password');
            
            if (this.value) {
                confirmField.required = true;
                currentField.required = true;
            } else {
                confirmField.required = false;
                currentField.required = false;
            }
        });
    </script>
</body>
</html>
