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
    <title>Payment - Online Bus Booking</title>
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

        .payment-card {
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

        .payment-icon {
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

        .total-row {
            background: linear-gradient(135deg, #00bcd4 0%, #4285f4 100%);
            color: white;
            padding: 12px;
            border-radius: 10px;
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            font-size: 16px;
        }

        .payment-methods {
            margin: 20px 0;
        }

        .payment-method-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
        }

        .payment-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .payment-option {
            background: white;
            border: 2px solid #e3f2fd;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .payment-option:hover {
            border-color: #4285f4;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(66, 133, 244, 0.2);
        }

        .payment-option.selected {
            border-color: #4285f4;
            background: linear-gradient(135deg, #e3f2fd 0%, #f0f7ff 100%);
        }

        .payment-option-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .payment-option-name {
            font-size: 12px;
            font-weight: 600;
            color: #2c3e50;
        }

        .card-details {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            border: 2px solid #e3f2fd;
            display: none;
        }

        .card-details.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 600;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #4285f4;
        }

        .form-row {
            display: flex;
            gap: 10px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .action-section {
            padding: 15px 25px;
            background: white;
            text-align: center;
        }

        .pay-btn {
            background: linear-gradient(135deg, #00bcd4 0%, #4285f4 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 188, 212, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: center;
        }

        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 188, 212, 0.4);
        }

        .pay-btn:active {
            transform: translateY(0);
        }

        .pay-btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Payment Method Modal */
        .payment-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .payment-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 20px;
            padding: 30px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: #f0f0f0;
            color: #333;
        }

        .modal-payment-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .modal-payment-option {
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            border: 2px solid #e3f2fd;
            border-radius: 15px;
            padding: 20px 15px;
            text-align: left;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: 0 2px 8px rgba(66, 133, 244, 0.1);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            min-height: 90px;
        }

        .modal-payment-option:hover {
            border-color: #4285f4;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(66, 133, 244, 0.25);
            background: linear-gradient(135deg, #f0f7ff 0%, #e3f2fd 100%);
        }

        .modal-payment-option.selected {
            border-color: #4285f4;
            background: linear-gradient(135deg, #e3f2fd 0%, #f0f7ff 100%);
            box-shadow: 0 4px 15px rgba(66, 133, 244, 0.3);
        }

        .modal-payment-option.selected::after {
            content: '✓';
            position: absolute;
            top: 10px;
            right: 12px;
            background: #4285f4;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .modal-payment-icon {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .modal-payment-name {
            font-size: 14px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .modal-payment-desc {
            font-size: 11px;
            color: #666;
            line-height: 1.3;
            margin-top: auto;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-btn-cancel {
            background: #f8f9fa;
            color: #666;
            border: 2px solid #e9ecef;
        }

        .modal-btn-cancel:hover {
            background: #e9ecef;
            color: #333;
        }

        .modal-btn-confirm {
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(66, 133, 244, 0.3);
        }

        .modal-btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(66, 133, 244, 0.4);
        }

        .modal-btn-confirm:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 20px 15px;
            }
            
            .payment-card {
                max-width: 100%;
            }
            
            .card-header {
                padding: 20px;
            }
            
            .card-body {
                padding: 25px 20px;
            }

            .payment-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <button onclick="goBackToBookingInfo()" class="back-btn">
            ← Back to Booking Info
        </button>
        <div class="header-title">
            <span class="payment-icon">💳</span>
            Payment
        </div>
        <div class="user-info">
            <span>👤</span>
            <span><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'suresh'; ?></span>
        </div>
    </div>

    <div class="main-container">
        <div class="payment-card">
            <div class="card-header">
                <div class="payment-icon">💳</div>
                <h2>Payment Details</h2>
                <div class="card-subtitle">Review your booking information and complete payment</div>
            </div>
            
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Passenger Name:</span>
                    <span class="info-value" id="userName">John Doe</span>
                </div>
                
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

                <div class="total-row">
                    <span>Total Amount:</span>
                    <span id="totalAmount">Rs. 3,600</span>
                </div>

            </div>
            
            <div class="action-section">
                <button class="pay-btn" id="payBtn" onclick="showPaymentModal()">
                    💳 Pay Now
                </button>
            </div>
        </div>
    </div>

    <!-- Payment Method Modal -->
    <div class="payment-modal" id="paymentModal">
        <div class="payment-modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    💳 Choose Payment Method
                </div>
                <button class="close-modal" onclick="closePaymentModal()">×</button>
            </div>
            
            <div class="modal-payment-options">
                <div class="modal-payment-option" onclick="selectModalPaymentMethod('esewa')">
                    <div class="modal-payment-icon">💚</div>
                    <div class="modal-payment-name">eSewa</div>
                    <div class="modal-payment-desc">Pay with your eSewa wallet</div>
                </div>
                
                <div class="modal-payment-option" onclick="selectModalPaymentMethod('khalti')">
                    <div class="modal-payment-icon">💜</div>
                    <div class="modal-payment-name">Khalti</div>
                    <div class="modal-payment-desc">Pay with your Khalti wallet</div>
                </div>
                
                <div class="modal-payment-option" onclick="selectModalPaymentMethod('mobile')">
                    <div class="modal-payment-icon">🏦</div>
                    <div class="modal-payment-name">Mobile Banking</div>
                    <div class="modal-payment-desc">Pay through mobile banking</div>
                </div>
                
                <div class="modal-payment-option" onclick="selectModalPaymentMethod('bank')">
                    <div class="modal-payment-icon">🏛️</div>
                    <div class="modal-payment-name">Bank Transfer</div>
                    <div class="modal-payment-desc">Direct bank account transfer</div>
                </div>
                
                <div class="modal-payment-option" onclick="selectModalPaymentMethod('card')">
                    <div class="modal-payment-icon">💳</div>
                    <div class="modal-payment-name">Credit/Debit Card</div>
                    <div class="modal-payment-desc">Pay using your card</div>
                </div>
                
                <div class="modal-payment-option" onclick="selectModalPaymentMethod('cash')">
                    <div class="modal-payment-icon">💵</div>
                    <div class="modal-payment-name">Cash Payment</div>
                    <div class="modal-payment-desc">Pay in cash at counter</div>
                </div>
            </div>
            
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" onclick="closePaymentModal()">Cancel</button>
                <button class="modal-btn modal-btn-confirm" id="confirmPaymentBtn" onclick="confirmPayment()" disabled>Confirm Payment</button>
            </div>
        </div>
    </div>

    <script>
        // Load booking data from sessionStorage
        window.onload = function() {
            try {
                const bookingDataStr = sessionStorage.getItem('bookingData');
                console.log('Raw booking data from sessionStorage:', bookingDataStr);
                
                if (!bookingDataStr) {
                    console.error('No booking data found in sessionStorage');
                    alert('No booking data found. Please go back and select your seats again.');
                    window.location.href = 'booked_seat.php';
                    return;
                }
                
                const bookingData = JSON.parse(bookingDataStr);
                console.log('Parsed booking data:', bookingData);
                
                if (bookingData) {
                    // Handle different possible data structures
                    const userName = bookingData.userName || bookingData.passengerName || 'Guest User';
                    const busName = bookingData.busName || 'Unknown Bus';
                    const busNumber = bookingData.busNumber || 'N/A';
                    const phone = bookingData.phone || bookingData.driverPhone || '9862463805';
                    const departureTime = bookingData.departureTime || '10:00 AM';
                    const selectedSeats = bookingData.selectedSeats || 'N/A';
                    const seatCount = bookingData.seatCount || 0;
                    const pricePerSeat = parseFloat(bookingData.pricePerSeat) || 1800;
                    const totalAmount = parseFloat(bookingData.totalAmount) || (seatCount * pricePerSeat);
                    
                    document.getElementById('userName').textContent = userName;
                    document.getElementById('busName').textContent = busName;
                    document.getElementById('busNumber').textContent = busNumber;
                    document.getElementById('phoneNumber').textContent = '📞 ' + phone;
                    document.getElementById('departureTime').textContent = departureTime;
                    document.getElementById('selectedSeats').textContent = selectedSeats;
                    document.getElementById('seatCount').textContent = seatCount;
                    document.getElementById('pricePerSeat').textContent = 'Rs. ' + pricePerSeat.toLocaleString();
                    document.getElementById('totalAmount').textContent = 'Rs. ' + totalAmount.toLocaleString();
                } else {
                    throw new Error('Booking data is null or empty');
                }
            } catch (error) {
                console.error('Error loading booking data:', error);
                alert('Error loading booking data: ' + error.message + '\n\nPlease go back and select your seats again.');
                // Don't redirect immediately, let user see the error
            }
        };

        let selectedPaymentMethod = null;

        function showPaymentModal() {
            document.getElementById('paymentModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore scrolling
            
            // Reset selections
            document.querySelectorAll('.modal-payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            selectedPaymentMethod = null;
            document.getElementById('confirmPaymentBtn').disabled = true;
        }

        function selectModalPaymentMethod(method) {
            // Remove previous selection
            document.querySelectorAll('.modal-payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selection to clicked option
            event.target.closest('.modal-payment-option').classList.add('selected');
            selectedPaymentMethod = method;
            
            // Enable confirm button
            document.getElementById('confirmPaymentBtn').disabled = false;
        }

        function confirmPayment() {
            if (!selectedPaymentMethod) {
                alert('Please select a payment method.');
                return;
            }
            
            // Close modal
            closePaymentModal();
            
            // Show processing state
            document.getElementById('payBtn').innerHTML = '⏳ Processing...';
            document.getElementById('payBtn').disabled = true;
            
            // Get booking data from sessionStorage
            let bookingData = {};
            try {
                const bookingDataStr = sessionStorage.getItem('bookingData');
                if (!bookingDataStr) {
                    throw new Error('No booking data found in sessionStorage');
                }
                bookingData = JSON.parse(bookingDataStr);
                console.log('Retrieved booking data for payment:', bookingData);
            } catch (error) {
                console.error('Error retrieving booking data:', error);
                alert('Error retrieving booking data. Please go back and select your seats again.');
                window.location.href = 'booked_seat.php';
                return;
            }
            
            // Prepare booking data for saving with proper validation
            const saveData = {
                passenger_name: bookingData.userName || bookingData.passengerName || '<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest User'; ?>',
                phone: bookingData.phone || bookingData.driverPhone || '9862463805',
                from_location: bookingData.from || bookingData.fromLocation || 'Kathmandu',
                to_location: bookingData.to || bookingData.toLocation || 'Pokhara',
                travel_date: bookingData.travel_date || bookingData.travelDate || new Date().toISOString().split('T')[0],
                departure_time: bookingData.departureTime || '10:00 AM',
                bus_name: bookingData.busName || 'Himalayan Express',
                bus_number: bookingData.busNumber || 'Na 4 Kha 2057',
                selected_seats: Array.isArray(bookingData.selectedSeats) ? bookingData.selectedSeats.join(', ') : (bookingData.selectedSeats || 'A1'),
                seat_count: parseInt(bookingData.seatCount) || 1,
                price_per_seat: parseFloat(bookingData.pricePerSeat) || 1800,
                total_amount: parseFloat(bookingData.totalAmount) || 1800,
                payment_method: selectedPaymentMethod || 'esewa'
            };
            
            // Validate payment method is not null/undefined
            if (!saveData.payment_method) {
                alert('Payment method selection error. Please try again.');
                document.getElementById('payBtn').innerHTML = '💳 Pay Now';
                document.getElementById('payBtn').disabled = false;
                return;
            }
            
            // Debug: Log the data being sent
            console.log('Selected payment method:', selectedPaymentMethod);
            console.log('Booking data from storage:', bookingData);
            console.log('Final save data:', saveData);
            
            // Save booking to database
            fetch('save_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(saveData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text().then(text => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('Parsed response:', data);
                if (data.success) {
                    alert('🎉 Payment Successful!\n\nYour booking has been confirmed.\nBooking ID: ' + data.booking_id + '\n\nThank you for choosing our service!');
                    // Clear booking data from session storage
                    sessionStorage.removeItem('bookingData');
                    window.location.href = 'dashboard1.php';
                } else {
                    throw new Error(data.message || 'Failed to save booking');
                }
            })
            .catch(error => {
                console.error('Error saving booking:', error);
                alert('Error: ' + error.message + '\n\nPlease check the console for more details.');
                // Don't redirect on error, let user try again
                document.getElementById('payBtn').innerHTML = '💳 Pay Now';
                document.getElementById('payBtn').disabled = false;
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('paymentModal');
            if (event.target === modal) {
                closePaymentModal();
            }
        }

        // Go back to booking information page
        function goBackToBookingInfo() {
            window.location.href = 'booking_information.php';
        }
    </script>
</body>
</html>
