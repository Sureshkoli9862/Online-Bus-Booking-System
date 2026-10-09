<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Get user info
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT name, username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (Exception $e) {
    $user = ['name' => 'User', 'username' => 'user'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - Online Bus Booking</title>
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

        /* Support Content */
        .support-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .support-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }

        .support-card:hover {
            transform: translateY(-5px);
        }

        .support-icon {
            font-size: 32px;
            margin-bottom: 15px;
        }

        .support-title {
            color: white;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .support-description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .support-link {
            color: #4fc3f7;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .support-link:hover {
            color: #29b6f6;
        }

        /* FAQ Section */
        .faq-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 30px;
        }

        .faq-title {
            color: white;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .faq-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .faq-question {
            color: white;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .faq-answer {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            line-height: 1.5;
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

        .contact-btn {
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }

        .contact-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(66, 133, 244, 0.4);
        }

        /* Feedback Form Styles */
        .feedback-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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

        .form-input, .form-textarea {
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

        .form-textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #4285f4;
            box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.2);
        }

        .form-input::placeholder, .form-textarea::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-input option {
            background: #333;
            color: white;
        }

        .rating-container {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .rating-star {
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0.3;
        }

        .rating-star:hover,
        .rating-star.active {
            opacity: 1;
            transform: scale(1.1);
        }

        .feedback-alert {
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-weight: 500;
            text-align: center;
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

        .quick-links {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 30px;
        }

        .quick-links h3 {
            color: white;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .quick-link {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .quick-link:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.15);
        }

        .quick-link a {
            color: #4fc3f7;
            text-decoration: none;
            font-weight: 500;
        }

        .quick-link a:hover {
            color: #29b6f6;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 3% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 25px 30px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }

        .modal-title {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .modal-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            margin-top: 5px;
        }

        .modal-body {
            padding: 25px 30px 30px;
        }

        .close-modal {
            color: rgba(255, 255, 255, 0.8);
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .map-content {
            background: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .close-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 10px;
        }

        .close-btn:hover {
            color: #000;
        }

        .map-header {
            margin-bottom: 20px;
            padding-right: 40px;
        }

        .map-header h3 {
            color: #1976d2;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .location-info {
            background: #f0f7ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #4285f4;
        }

        .map-container {
            width: 100%;
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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

            .support-grid {
                grid-template-columns: 1fr;
            }
            
            .links-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
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
            <li><a href="profile.php"><i>👤</i> Profile</a></li>
            <li><a href="help_support.php" class="active"><i>❓</i> Help & Support</a></li>
            <li><a href="logout.php"><i>🚪</i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Help & Support</h1>
            <p class="page-subtitle">Get assistance with your bus booking experience</p>
        </div>

        <!-- Support Cards -->
        <div class="support-grid">
            <div class="support-card">
                <div class="support-icon">📞</div>
                <div class="support-title">24/7 Phone Support</div>
                <div class="support-description">Get instant help from our customer service team. Available round the clock for all your bus booking needs.</div>
                <div style="margin-top: 15px;">
                    <strong style="color: white;">Hotline:</strong> <span style="color: #4fc3f7;">+977-1-4567890</span><br>
                    <strong style="color: white;">WhatsApp:</strong> <span style="color: #4fc3f7;">+977-9862463805</span>
                </div>
                <a href="tel:+977-1-4567890" class="contact-btn">Call Now</a>
            </div>


            <div class="support-card">
                <div class="support-icon">🏢</div>
                <div class="support-title">Visit Our Office</div>
                <div class="support-description">Visit our customer service center for in-person assistance and ticket booking services.</div>
                <div style="margin-top: 15px;">
                    <strong style="color: white;">Address:</strong> <span style="color: #4fc3f7;">Bus Park, Kanchanpur</span><br>
                    <strong style="color: white;">Hours:</strong> <span style="color: #4fc3f7;">6:00 AM - 10:00 PM</span>
                </div>
                <a href="#" class="contact-btn" onclick="showDirections()">Get Directions</a>
            </div>

            <div class="support-card">
                <div class="support-icon">💬</div>
                <div class="support-title">Share Your Feedback</div>
                <div class="support-description">We value your opinion! Share suggestions, complaints, or compliments to help us improve our services.</div>
                <div style="margin-top: 15px;">
                    <strong style="color: white;">Email:</strong> <span style="color: #4fc3f7;">feedback@busservice.com</span><br>
                    <strong style="color: white;">Response Time:</strong> <span style="color: #4fc3f7;">Within 24 hours</span>
                </div>
                <button class="contact-btn" onclick="openFeedbackModal()">💬 Give Feedback</button>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="quick-links">
            <h3>🔗 Quick Links</h3>
            <div class="links-grid">
                <div class="quick-link">
                    <div style="font-size: 24px; margin-bottom: 8px;">🎫</div>
                    <a href="online_ticket.php">Book New Ticket</a>
                </div>
                <div class="quick-link">
                    <div style="font-size: 24px; margin-bottom: 8px;">📋</div>
                    <a href="my_bookings_new.php">My Bookings</a>
                </div>
                <div class="quick-link">
                    <div style="font-size: 24px; margin-bottom: 8px;">👤</div>
                    <a href="profile.php">Update Profile</a>
                </div>
            </div>
        </div>

        <!-- Feedback Modal -->
        <div id="feedbackModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">💬 Share Your Feedback</h2>
                    <p class="modal-subtitle">We value your opinion! Help us improve our services.</p>
                    <span class="close-modal" onclick="closeFeedbackModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="feedbackForm" class="feedback-form">
                        <div class="form-group">
                            <label class="form-label">Feedback Type *</label>
                            <select name="feedback_type" class="form-input" required>
                                <option value="">Select feedback type</option>
                                <option value="suggestion">💡 Suggestion</option>
                                <option value="other">📝 Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Your Message *</label>
                            <textarea name="message" class="form-textarea" required placeholder="Please share your feedback or suggestions..."></textarea>
                        </div>
                        
                        <div style="text-align: center; margin-top: 25px;">
                            <button type="submit" class="contact-btn">📤 Submit Feedback</button>
                            <button type="button" class="contact-btn" onclick="closeFeedbackModal()" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); margin-left: 10px;">Cancel</button>
                        </div>
                    </form>
                    
                    <div id="feedbackAlert" style="margin-top: 20px;"></div>
                </div>
            </div>
        </div>


        <!-- Map Modal -->
        <div id="mapModal" class="modal">
            <div class="map-content">
                <span class="close-btn" onclick="closeMap()">&times;</span>
                <div class="map-header">
                    <h3>📍 Our Office Location</h3>
                    <div class="location-info">
                        <strong>Address:</strong> Bus Park, Kanchanpur, Nepal<br>
                        <strong>Contact:</strong> +977-1-4567890<br>
                        <strong>Hours:</strong> 6:00 AM - 10:00 PM (Daily)
                    </div>
                </div>
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3532.8!2d80.2!3d28.9!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjjCsDU0JzAwLjAiTiA4MMKwMTInMDAuMCJF!5e0!3m2!1sen!2snp!4v1234567890!5m2!1sen!2snp&q=Kanchanpur,Nepal"
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <div style="margin-top: 15px; text-align: center;">
                    <a href="https://maps.google.com/?q=Kanchanpur,Nepal" target="_blank" class="contact-btn">Open in Google Maps</a>
                </div>
            </div>
        </div>

        <script>
            function showDirections() {
                document.getElementById('mapModal').style.display = 'block';
            }

            function closeMap() {
                document.getElementById('mapModal').style.display = 'none';
            }

            // Feedback modal functions
            function openFeedbackModal() {
                document.getElementById('feedbackModal').style.display = 'block';
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            }
            
            function closeFeedbackModal() {
                document.getElementById('feedbackModal').style.display = 'none';
                document.body.style.overflow = 'auto'; // Restore scrolling
                
                // Reset form and alerts
                document.getElementById('feedbackForm').reset();
                document.getElementById('rating').value = '';
                document.querySelectorAll('.rating-star').forEach(s => s.classList.remove('active'));
                document.getElementById('feedbackAlert').innerHTML = '';
            }
            
            // Rating system
            document.querySelectorAll('.rating-star').forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('data-rating');
                    document.getElementById('rating').value = rating;
                    
                    // Update visual feedback
                    document.querySelectorAll('.rating-star').forEach((s, index) => {
                        if (index < rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
            });
            
            // Feedback form submission
            document.getElementById('feedbackForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const alertContainer = document.getElementById('feedbackAlert');
                const submitBtn = this.querySelector('button[type="submit"]');
                
                // Basic validation
                const feedbackType = formData.get('feedback_type');
                const message = formData.get('message').trim();
                
                if (!feedbackType || !message) {
                    alertContainer.innerHTML = '<div class="feedback-alert alert-error">Please fill in all required fields.</div>';
                    return;
                }
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.textContent = '📤 Submitting...';
                alertContainer.innerHTML = '';
                
                // Submit to backend
                fetch('submit_feedback.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alertContainer.innerHTML = '<div class="feedback-alert alert-success">' + data.message + '</div>';
                        
                        // Reset form
                        this.reset();
                        
                        // Auto close modal after 2 seconds
                        setTimeout(() => {
                            closeFeedbackModal();
                        }, 2000);
                    } else {
                        alertContainer.innerHTML = '<div class="feedback-alert alert-error">' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alertContainer.innerHTML = '<div class="feedback-alert alert-error">Network error. Please check your connection and try again.</div>';
                })
                .finally(() => {
                    // Restore button state
                    submitBtn.disabled = false;
                    submitBtn.textContent = '📤 Submit Feedback';
                    
                    // Scroll to alert
                    alertContainer.scrollIntoView({ behavior: 'smooth' });
                });
            });

            // Close modals when clicking outside
            window.onclick = function(event) {
                const mapModal = document.getElementById('mapModal');
                const feedbackModal = document.getElementById('feedbackModal');
                
                if (event.target == mapModal) {
                    mapModal.style.display = 'none';
                }
                
                if (event.target == feedbackModal) {
                    closeFeedbackModal();
                }
            }
        </script>
    </div>
</body>
</html>
