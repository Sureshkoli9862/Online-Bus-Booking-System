<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Information - Online Bus Booking</title>
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
            flex-direction: column;
        }

        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .back-btn {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            color: #e3f2fd;
            transform: translateX(-3px);
        }

        .header-title {
            color: white;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-info {
            color: #4285f4;
            background: rgba(255, 255, 255, 0.9);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 600;
        }

        .main-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            min-height: calc(100vh - 80px);
        }

        .booking-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
            backdrop-filter: blur(10px);
        }

        .card-header {
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        }

        .card-header h2 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .card-subtitle {
            font-size: 14px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .booking-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .card-body {
            padding: 20px 35px;
            background: linear-gradient(to bottom, #f8f9ff 0%, #e8f0ff 100%);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            margin-bottom: 6px;
        }

        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }

        .info-value {
            font-weight: 600;
            color: #1976d2;
            font-size: 14px;
            text-align: right;
        }

        .action-section {
            padding: 15px 25px;
            background: white;
            text-align: center;
        }

        .next-btn {
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(66, 133, 244, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .next-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(66, 133, 244, 0.4);
        }

        .next-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 20px 15px;
            }
            
            .booking-card {
                max-width: 100%;
            }
            
            .card-header {
                padding: 20px;
            }
            
            .card-body {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <button onclick="goBackToSeatSelection()" class="back-btn">
            ← Back to Seat Selection
        </button>
        <div class="header-title">
            <span class="booking-icon">ℹ️</span>
            Booking Information
        </div>
        <div class="user-info">
            <span>👤</span>
            <span><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></span>
        </div>
    </div>

    <div class="main-container">
        <div class="booking-card">
            <div class="card-header">
                <div class="booking-icon">📋</div>
                <h2>Booking Information</h2>
                <div class="card-subtitle">Please review your booking details before proceeding</div>
            </div>
            
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Bus Name:</span>
                    <span class="info-value" id="busName">Himalayan Express</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Bus Number:</span>
                    <span class="info-value" id="busNumber">Na 4 Kha 2057</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Phone Number:</span>
                    <span class="info-value" id="phoneNumber">📞 9862463805</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Departure Time:</span>
                    <span class="info-value" id="departureTime">10:00 AM</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Selected Seats:</span>
                    <span class="info-value" id="selectedSeats">A1, A6</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Number of Seats:</span>
                    <span class="info-value" id="seatCount">2</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Price per Seat:</span>
                    <span class="info-value" id="pricePerSeat">Rs. 1,800</span>
                </div>
            </div>
            
            <div class="action-section">
                <button class="next-btn" onclick="proceedToPayment()">
                    ➤ Next
                </button>
            </div>
        </div>
    </div>

    <script>
        // Load booking data from sessionStorage
        window.onload = function() {
            const bookingData = JSON.parse(sessionStorage.getItem('bookingData'));
            if (bookingData) {
                document.getElementById('busName').textContent = bookingData.busName;
                document.getElementById('busNumber').textContent = bookingData.busNumber;
                document.getElementById('phoneNumber').textContent = '📞 ' + bookingData.phone;
                document.getElementById('departureTime').textContent = bookingData.departureTime;
                document.getElementById('selectedSeats').textContent = bookingData.selectedSeats;
                document.getElementById('seatCount').textContent = bookingData.seatCount;
                document.getElementById('pricePerSeat').textContent = 'Rs. ' + bookingData.pricePerSeat.toLocaleString();
            }
        };

        function proceedToPayment() {
            window.location.href = 'payment.php';
        }

        // Go back to seat selection page
        function goBackToSeatSelection() {
            window.location.href = 'booked_seat.php';
        }
    </script>
</body>
</html>
