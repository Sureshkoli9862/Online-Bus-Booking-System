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
    <title>Seat Booking</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            min-height: 100vh;
            padding: 10px;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* Background decorative elements */
        body::before {
            content: '';
            position: fixed;
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

        .main-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* Top Navigation Box */
        .top-nav-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.15);
            padding: 15px 20px;
            margin-bottom: 20px;
            border: 2px solid rgba(66, 133, 244, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 100%;
        }

        .back-to-selection-btn {
            background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(66, 133, 244, 0.3);
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .back-to-selection-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(66, 133, 244, 0.4);
            color: white;
            text-decoration: none;
        }

        .back-to-selection-btn:active {
            transform: translateY(0);
        }

        .page-title {
            color: #1976d2;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Left Side - Seat Selection */
        .seat-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            text-align: center;
            flex: 1;
        }

        .header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .bus-info {
            background: #f5f5f5;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .bus-info h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .bus-info p {
            color: #666;
            font-size: 14px;
        }

        .bus-contact {
            color: #4285f4 !important;
            font-weight: 600;
            margin-top: 5px;
        }

        /* Driver Section */
        .driver-section {
            background: #333;
            color: white;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
        }

        /* Seat Grid */
        .seat-grid {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 10px;
            align-items: center;
        }

        .seat-group {
            text-align: center;
            background: linear-gradient(145deg, #ffffff 0%, #f8fbff 100%);
            border-radius: 15px;
            padding: 12px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15), inset 0 1px 0 rgba(255,255,255,0.8);
            border: 2px solid #e3f2fd;
            transition: all 0.3s ease;
            position: relative;
            max-width: 320px;
            margin: 0 auto;
        }

        .seat-group::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #4285f4, #1976d2, #42a5f5);
            border-radius: 15px 15px 0 0;
        }

        .seat-group:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.25), inset 0 1px 0 rgba(255,255,255,0.9);
        }

        .group-label {
            font-size: 16px;
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 12px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .group-seats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 5px;
            justify-items: center;
            justify-content: center;
        }

        .seat {
            width: 45px;
            height: 45px;
            border: none;
            border-radius: 6px;
            background: #4caf50;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            color: white;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3), inset 0 1px 0 rgba(255,255,255,0.3);
        }

        .seat:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4), inset 0 1px 0 rgba(255,255,255,0.4);
        }

        .seat.selected {
            background: linear-gradient(145deg, #f44336 0%, #d32f2f 100%);
            box-shadow: 0 6px 20px rgba(244, 67, 54, 0.4), inset 0 1px 0 rgba(255,255,255,0.3);
        }


        .seat.occupied {
            background: linear-gradient(145deg, #9e9e9e 0%, #757575 100%);
            cursor: not-allowed;
            box-shadow: 0 2px 8px rgba(158, 158, 158, 0.3), inset 0 1px 0 rgba(255,255,255,0.2);
        }

        .seat.occupied:hover {
            transform: none;
            box-shadow: 0 2px 8px rgba(158, 158, 158, 0.3), inset 0 1px 0 rgba(255,255,255,0.2);
        }

        .seat.pending {
            background: linear-gradient(145deg, #ff9800 0%, #f57c00 100%);
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3), inset 0 1px 0 rgba(255,255,255,0.3);
        }

        .seat.pending:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(255, 152, 0, 0.4), inset 0 1px 0 rgba(255,255,255,0.4);
        }

        /* Seat Legend Styles */
        .seat-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(227, 242, 253, 0.3);
            border-radius: 10px;
            border: 1px solid #e3f2fd;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #555;
            font-weight: 500;
        }

        .legend-seat {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: bold;
            color: white;
        }

        .legend-seat.available {
            background: linear-gradient(145deg, #4caf50 0%, #388e3c 100%);
        }

        .legend-seat.pending {
            background: linear-gradient(145deg, #ff9800 0%, #f57c00 100%);
        }

        .legend-seat.occupied {
            background: linear-gradient(145deg, #9e9e9e 0%, #757575 100%);
        }

        .legend-seat.selected {
            background: linear-gradient(145deg, #f44336 0%, #d32f2f 100%);
        }


        /* Booking Summary - Enhanced Design */
        .booking-summary {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 340px;
            background: linear-gradient(145deg, #ffffff 0%, #f8fbff 100%);
            border: 3px solid #00bcd4;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 15px 40px rgba(0, 188, 212, 0.2), 0 5px 15px rgba(0,0,0,0.1), inset 0 1px 0 rgba(255,255,255,0.8);
            z-index: 1000;
            display: none;
            backdrop-filter: blur(10px);
        }

        .booking-summary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #00bcd4, #4285f4, #00acc1);
            border-radius: 20px 20px 0 0;
        }

        .booking-summary.show {
            display: block;
            animation: slideUpBounce 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes slideUpBounce {
            0% {
                transform: translateY(100%) scale(0.8);
                opacity: 0;
            }
            60% {
                transform: translateY(-10px) scale(1.05);
                opacity: 0.8;
            }
            100% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .summary-header {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e3f2fd;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            position: relative;
        }

        .summary-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #00bcd4, #4285f4);
            border-radius: 2px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 13px;
            padding: 6px 10px;
            background: rgba(227, 242, 253, 0.3);
            border-radius: 6px;
            border-left: 3px solid #4285f4;
        }

        .summary-label {
            color: #666;
            font-weight: 500;
        }

        .summary-value {
            color: #333;
            font-weight: bold;
        }

        .selected-seats-display {
            color: #f44336;
            font-weight: bold;
        }

        .total-row {
            background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);
            color: white;
            padding: 12px 15px;
            border-radius: 10px;
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 15px;
            box-shadow: 0 4px 15px rgba(0, 188, 212, 0.3), inset 0 1px 0 rgba(255,255,255,0.2);
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .clear-btn {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3), inset 0 1px 0 rgba(255,255,255,0.2);
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        .clear-btn:hover {
            background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4), inset 0 1px 0 rgba(255,255,255,0.3);
        }

        .book-btn {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3), inset 0 1px 0 rgba(255,255,255,0.2);
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        .book-btn:hover {
            background: linear-gradient(135deg, #0056b3 0%, #003d82 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4), inset 0 1px 0 rgba(255,255,255,0.3);
        }

        .book-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .booking-summary {
                width: 90%;
                right: 5%;
                left: 5%;
            }
            
            .seat-grid {
                gap: 6px;
            }
            
            .seat {
                width: 40px;
                height: 40px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Top Navigation Box -->
        <div class="top-nav-box">
            <button onclick="goBackToTicketBooking()" class="back-to-selection-btn">
                ← Back to Ticket Booking
            </button>
            <div class="page-title">
                🚌 Seat Selection
            </div>
        </div>

        <!-- Left Side - Seat Selection -->
        <div class="seat-section">
            <!-- Header -->
            <div class="header">
                <h1>🚌 Select Your Seats</h1>
            </div>

            <!-- Bus Info -->
            <div class="bus-info">
                <h2 id="busName">Loading...</h2>
                <p id="busDetails">Loading bus details...</p>
                <p class="bus-contact" id="busContact">📞 Loading...</p>
            </div>

            <!-- Driver Section -->
            <div class="driver-section">
                <span>🚗 Driver</span>
            </div>

            <!-- Seat Status Legend -->
            <div class="seat-legend">
                <div class="legend-item">
                    <div class="legend-seat available"></div>
                    <span>Available</span>
                </div>
                <div class="legend-item">
                    <div class="legend-seat pending"></div>
                    <span>Payment Pending</span>
                </div>
                <div class="legend-item">
                    <div class="legend-seat occupied"></div>
                    <span>Occupied</span>
                </div>
                <div class="legend-item">
                    <div class="legend-seat selected"></div>
                    <span>Selected</span>
                </div>
            </div>

            <!-- Dynamic Seat Grid -->
            <div class="seat-grid" id="seatGrid">
                <!-- Seats will be generated dynamically based on database -->
            </div>

        </div>

        <!-- Right Side - Booking Summary (Matching your image) -->
        <div class="booking-summary">
            <div class="summary-header">
                Booking Summary
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Bus Name:</span>
                <span class="summary-value" id="summaryBusName">Loading...</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Bus Number:</span>
                <span class="summary-value" id="summaryBusNumber">Loading...</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Phone Number:</span>
                <span class="summary-value" id="summaryPhoneNumber">📞 Loading...</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Departure Time:</span>
                <span class="summary-value" id="summaryDepartureTime">Loading...</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Selected Seats:</span>
                <span class="summary-value selected-seats-display" id="selectedSeatsDisplay">None</span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Number of Seats:</span>
                <span class="summary-value" id="seatCount">0</span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Price per Seat:</span>
                <span class="summary-value" id="pricePerSeat">Rs. 0</span>
            </div>
            
            <div class="total-row">
                <span>Total Amount:</span>
                <span id="totalAmount">Rs. 0</span>
            </div>
            
            <div class="action-buttons">
                <button class="clear-btn" onclick="clearSelection()">
                    ✕ Clear
                </button>
                <button class="book-btn" id="bookBtn" onclick="bookNow()">
                    📋 Book Now
                </button>
            </div>
        </div>
    </div>

    <script>
        let selectedSeats = []; // Start with no selected seats
        let pricePerSeat = 1800; // Will be updated from database
        let busNumber = null;
        let travelDate = new Date().toISOString().split('T')[0]; // Default to today's date
        let selectedBusData = null;

        // Load seat status on page load
        window.addEventListener('load', function() {
            console.log('Page loaded, loading bus data...');
            loadBusData();
        });

        // Also try DOMContentLoaded as fallback
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, loading bus data...');
            loadBusData();
        });

        // Load bus data and setup
        function loadBusData() {
            console.log('loadBusData called');
            const busData = sessionStorage.getItem('selectedBus');
            const bookingData = sessionStorage.getItem('bookingData');
            
            console.log('Bus data from sessionStorage:', busData);
            console.log('Booking data from sessionStorage:', bookingData);
            
            if (busData) {
                selectedBusData = JSON.parse(busData);
                console.log('Parsed bus data:', selectedBusData);
                
                // Update bus info display
                const busNameEl = document.getElementById('busName');
                const busDetailsEl = document.getElementById('busDetails');
                const busContactEl = document.getElementById('busContact');
                
                if (busNameEl) {
                    busNameEl.textContent = selectedBusData.bus_name || 'Unknown Bus';
                }
                if (busDetailsEl) {
                    busDetailsEl.textContent = 
                        `${selectedBusData.bus_number || 'N/A'} • Departure: ${selectedBusData.departure_time || 'N/A'} • ${selectedBusData.bus_type || 'AC'}`;
                }
                if (busContactEl) {
                    busContactEl.textContent = `📞 ${selectedBusData.driver_phone || '9862463805'}`;
                }
                
                // Update booking summary with actual bus data
                const summaryBusName = document.getElementById('summaryBusName');
                const summaryBusNumber = document.getElementById('summaryBusNumber');
                const summaryPhoneNumber = document.getElementById('summaryPhoneNumber');
                const summaryDepartureTime = document.getElementById('summaryDepartureTime');
                
                if (summaryBusName) {
                    summaryBusName.textContent = selectedBusData.bus_name || 'Unknown Bus';
                }
                if (summaryBusNumber) {
                    summaryBusNumber.textContent = selectedBusData.bus_number || 'N/A';
                }
                if (summaryPhoneNumber) {
                    summaryPhoneNumber.textContent = `📞 ${selectedBusData.driver_phone || '9862463805'}`;
                }
                if (summaryDepartureTime) {
                    summaryDepartureTime.textContent = selectedBusData.departure_time || 'N/A';
                }
                
                // Generate dynamic seat layout
                const totalSeats = parseInt(selectedBusData.total_seats) || 40;
                console.log('Total seats:', totalSeats);
                generateSeatLayout(totalSeats);
                
                // Update price per seat
                const newPricePerSeat = parseFloat(selectedBusData.fare_amount) || 1500;
                pricePerSeat = newPricePerSeat;
                
                // Update price display
                const priceEl = document.getElementById('pricePerSeat');
                if (priceEl) {
                    priceEl.textContent = `Rs. ${newPricePerSeat.toLocaleString()}`;
                }
                
                // Store price for calculations
                window.currentPricePerSeat = newPricePerSeat;
                
                // Update summary with new price
                updateSummary();
            } else {
                console.log('No bus data found, generating default layout');
                // Generate default layout with 40 seats
                generateSeatLayout(40);
                
                // Show default data
                const busNameEl = document.getElementById('busName');
                const busDetailsEl = document.getElementById('busDetails');
                const busContactEl = document.getElementById('busContact');
                
                if (busNameEl) {
                    busNameEl.textContent = 'Himalayan Express';
                }
                if (busDetailsEl) {
                    busDetailsEl.textContent = 'Na 4 Kha 2057 • Departure: 10:00 AM • AC Bus';
                }
                if (busContactEl) {
                    busContactEl.textContent = '📞 9862463805';
                }
            }
            
            // Load booking data for travel date
            if (bookingData) {
                const booking = JSON.parse(bookingData);
                travelDate = booking.travel_date;
                busNumber = selectedBusData ? selectedBusData.bus_number : null;
                
                if (busNumber && travelDate) {
                    loadSeatStatus();
                }
            }
            
            // Also check URL parameters as fallback
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('travel_date')) {
                travelDate = urlParams.get('travel_date');
            }
            if (urlParams.get('bus_number')) {
                busNumber = urlParams.get('bus_number');
                
                if (busNumber && travelDate) {
                    loadSeatStatus();
                }
            }
        }

        // Generate dynamic seat layout based on total seats with Group A and Group B
        function generateSeatLayout(totalSeats) {
            console.log('generateSeatLayout called with totalSeats:', totalSeats);
            const seatGrid = document.getElementById('seatGrid');
            
            if (!seatGrid) {
                console.error('seatGrid element not found!');
                return;
            }
            
            seatGrid.innerHTML = '';
            
            // Calculate seats per group (A and B groups)
            const seatsPerGroup = Math.ceil(totalSeats / 2);
            
            console.log('Seats per group:', seatsPerGroup);
            
            // Generate Group A and Group B
            ['A', 'B'].forEach((group, groupIndex) => {
                const groupSeats = groupIndex === 0 ? seatsPerGroup : (totalSeats - seatsPerGroup);
                
                // Create group container
                const seatGroup = document.createElement('div');
                seatGroup.className = 'seat-group';
                
                // Create group label
                const groupLabel = document.createElement('div');
                groupLabel.className = 'group-label';
                groupLabel.textContent = `Group ${group}`;
                seatGroup.appendChild(groupLabel);
                
                // Create group seats container
                const groupSeatsContainer = document.createElement('div');
                groupSeatsContainer.className = 'group-seats';
                
                // Generate seats for this group
                for (let seatNum = 1; seatNum <= groupSeats; seatNum++) {
                    const seat = document.createElement('div');
                    seat.className = 'seat';
                    const seatId = `${group}${seatNum}`;
                    seat.setAttribute('data-seat', seatId);
                    seat.textContent = seatId;
                    
                    // Add click event listener
                    seat.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        console.log('Seat clicked:', this.getAttribute('data-seat'));
                        
                        if (this.classList.contains('occupied') || this.classList.contains('pending')) {
                            console.log('Seat is occupied/pending, cannot select');
                            return;
                        }
                        
                        const seatNum = this.getAttribute('data-seat');
                        
                        if (this.classList.contains('selected')) {
                            // Deselect seat
                            this.classList.remove('selected');
                            selectedSeats = selectedSeats.filter(s => s !== seatNum);
                            console.log('Seat deselected:', seatNum);
                        } else {
                            // Select seat
                            this.classList.add('selected');
                            selectedSeats.push(seatNum);
                            console.log('Seat selected:', seatNum);
                        }
                        
                        console.log('Current selected seats:', selectedSeats);
                        updateSummary();
                    });
                    
                    groupSeatsContainer.appendChild(seat);
                }
                
                seatGroup.appendChild(groupSeatsContainer);
                seatGrid.appendChild(seatGroup);
            });
        }

        function loadSeatStatus() {
            if (!busNumber || !travelDate) {
                console.error('Missing bus number or travel date');
                return;
            }
            
            // First cleanup expired bookings
            fetch('cleanup_expired_bookings.php')
                .then(response => response.json())
                .then(cleanupData => {
                    console.log('Cleanup completed:', cleanupData);
                    
                    // Then load current seat status
                    return fetch(`get_seat_status.php?bus_number=${encodeURIComponent(busNumber)}&travel_date=${encodeURIComponent(travelDate)}`);
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Seat status loaded:', data.seat_status);
                        updateSeatDisplay(data.seat_status);
                    } else {
                        console.error('Failed to load seat status:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading seat status:', error);
                });
        }

        function updateSeatDisplay(seatStatus) {
            const today = new Date().toISOString().split('T')[0];
            const isToday = travelDate === today;
            
            document.querySelectorAll('.seat').forEach(seat => {
                const seatNumber = seat.getAttribute('data-seat');
                seat.classList.remove('occupied', 'pending');
                
                // Apply seat status for both today and future dates
                if (seatStatus[seatNumber]) {
                    seat.classList.add(seatStatus[seatNumber]);
                }
            });
            
            // Update page info to show selected date
            const dateInfo = document.createElement('div');
            dateInfo.style.cssText = 'text-align: center; margin-bottom: 15px; padding: 10px; background: rgba(66, 133, 244, 0.1); border-radius: 8px; color: #1976d2; font-weight: 600;';
            dateInfo.innerHTML = `📅 Travel Date: ${formatDate(travelDate)} ${!isToday ? '(Future Date)' : '(Today)'}`;
            
            // Remove existing date info if present
            const existingDateInfo = document.querySelector('.date-info');
            if (existingDateInfo) {
                existingDateInfo.remove();
            }
            
            // Add new date info
            dateInfo.className = 'date-info';
            const busInfo = document.querySelector('.bus-info');
            busInfo.parentNode.insertBefore(dateInfo, busInfo.nextSibling);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }

        function setupSeatClickEvents() {
            // Add click event to all available seats (not occupied)
            document.querySelectorAll('.seat:not(.occupied)').forEach(seat => {
                seat.addEventListener('click', function() {
                    const seatNumber = this.getAttribute('data-seat');
                    
                    if (this.classList.contains('selected')) {
                        // Deselect seat
                        this.classList.remove('selected');
                        selectedSeats = selectedSeats.filter(s => s !== seatNumber);
                    } else {
                        // Select seat (including pending seats)
                        this.classList.add('selected');
                        selectedSeats.push(seatNumber);
                    }
                    
                    updateSummary();
                });
            });
        }

        function updateSummary() {
            console.log('updateSummary called with selectedSeats:', selectedSeats);
            
            const selectedSeatsDisplay = document.getElementById('selectedSeatsDisplay');
            const seatCount = document.getElementById('seatCount');
            const totalAmount = document.getElementById('totalAmount');
            const bookBtn = document.getElementById('bookBtn');
            const bookingSummary = document.querySelector('.booking-summary');
            
            if (!selectedSeatsDisplay || !seatCount || !totalAmount || !bookBtn) {
                console.error('Summary elements not found');
                return;
            }
            
            // Use current price per seat from loaded bus data
            const currentPrice = window.currentPricePerSeat || pricePerSeat;
            console.log('Current price per seat:', currentPrice);
            
            if (selectedSeats.length === 0) {
                // Hide booking summary when no seats selected
                bookingSummary.classList.remove('show');
                selectedSeatsDisplay.textContent = 'None';
                seatCount.textContent = '0';
                totalAmount.textContent = 'Rs. 0';
                bookBtn.disabled = true;
                bookBtn.style.opacity = '0.5';
                bookBtn.style.cursor = 'not-allowed';
            } else {
                // Show booking summary when seats are selected
                bookingSummary.classList.add('show');
                selectedSeatsDisplay.textContent = selectedSeats.join(', ');
                seatCount.textContent = selectedSeats.length;
                
                const total = selectedSeats.length * currentPrice;
                totalAmount.textContent = `Rs. ${total.toLocaleString()}`;
                bookBtn.disabled = false;
                bookBtn.style.opacity = '1';
                bookBtn.style.cursor = 'pointer';
            }
            
            console.log('Summary updated successfully');
        }

        function clearSelection() {
            document.querySelectorAll('.seat.selected').forEach(seat => {
                seat.classList.remove('selected');
            });
            selectedSeats = [];
            updateSummary();
        }

        function bookNow() {
            if (selectedSeats.length === 0) {
                alert('Please select at least one seat.');
                return;
            }
            
            // Get current booking data from sessionStorage
            const existingBookingData = sessionStorage.getItem('bookingData');
            let bookingData = {};
            
            if (existingBookingData) {
                bookingData = JSON.parse(existingBookingData);
            }
            
            // Update with selected seat information and actual bus data
            const currentPrice = window.currentPricePerSeat || pricePerSeat;
            
            bookingData.selectedSeats = selectedSeats;
            bookingData.seatCount = selectedSeats.length;
            bookingData.pricePerSeat = currentPrice;
            bookingData.totalAmount = selectedSeats.length * currentPrice;
            
            // Use actual bus data if available
            if (selectedBusData) {
                bookingData.busName = selectedBusData.bus_name;
                bookingData.busNumber = selectedBusData.bus_number;
                bookingData.departureTime = selectedBusData.departure_time;
                bookingData.busType = selectedBusData.bus_type;
                bookingData.driverPhone = selectedBusData.driver_phone;
                bookingData.fareAmount = selectedBusData.fare_amount;
            } else {
                // Fallback data
                bookingData.busName = 'Himalayan Express';
                bookingData.busNumber = busNumber || 'Na 4 Kha 2057';
                bookingData.driverPhone = '9862463805';
                bookingData.departureTime = '10:00 AM';
                bookingData.busType = 'AC';
                bookingData.fareAmount = currentPrice;
            }
            
            console.log('Booking data to pass:', bookingData);
            
            // Store in sessionStorage
            sessionStorage.setItem('bookingData', JSON.stringify(bookingData));
            
            // Redirect to booking information page
            window.location.href = 'booking_information.php';
        }

        // Go back to ticket booking page
        function goBackToTicketBooking() {
            window.location.href = 'bus_selection.php';
        }
    </script>
</body>
</html>
