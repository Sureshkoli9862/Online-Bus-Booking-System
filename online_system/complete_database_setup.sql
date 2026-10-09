-- Complete Database Setup for Online Bus Booking System
-- This script creates the database and all required tables with sample data

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `online_system` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `online_system`;

-- Drop existing tables if they exist (in correct order to handle foreign keys)
DROP TABLE IF EXISTS `user_sessions`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `buses`;
DROP TABLE IF EXISTS `routes`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `admins`;

-- =============================================
-- USERS TABLE
-- =============================================
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `name` varchar(100) NOT NULL,
    `phone` varchar(15) NOT NULL UNIQUE,
    `address` text NOT NULL,
    `password_hash` varchar(255) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_login` timestamp NULL DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_phone` (`phone`),
    KEY `idx_username` (`username`),
    KEY `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- USER SESSIONS TABLE
-- =============================================
CREATE TABLE `user_sessions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `session_token` varchar(128) NOT NULL,
    `expires_at` timestamp NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_session_token` (`session_token`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_expires_at` (`expires_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ROUTES TABLE
-- =============================================
CREATE TABLE `routes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `from_location` varchar(100) NOT NULL,
    `to_location` varchar(100) NOT NULL,
    `distance_km` int(11) NOT NULL DEFAULT 0,
    `estimated_duration` varchar(20) DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_from_to` (`from_location`, `to_location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- BUSES TABLE
-- =============================================
CREATE TABLE `buses` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `bus_name` varchar(100) NOT NULL,
    `bus_number` varchar(20) NOT NULL UNIQUE,
    `bus_type` varchar(50) NOT NULL,
    `total_seats` int(11) NOT NULL DEFAULT 40,
    `available_seats` int(11) NOT NULL DEFAULT 40,
    `price_per_seat` decimal(10,2) NOT NULL,
    `departure_time` time NOT NULL,
    `arrival_time` time DEFAULT NULL,
    `phone` varchar(15) DEFAULT NULL,
    `amenities` text DEFAULT NULL,
    `route_id` int(11) DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_bus_number` (`bus_number`),
    KEY `idx_bus_name` (`bus_name`),
    KEY `idx_departure_time` (`departure_time`),
    KEY `idx_route_id` (`route_id`),
    FOREIGN KEY (`route_id`) REFERENCES `routes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- BOOKINGS TABLE
-- =============================================
CREATE TABLE `bookings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `booking_id` varchar(20) NOT NULL UNIQUE,
    `passenger_name` varchar(100) NOT NULL,
    `phone` varchar(15) NOT NULL,
    `from_location` varchar(100) NOT NULL,
    `to_location` varchar(100) NOT NULL,
    `travel_date` date NOT NULL,
    `departure_time` varchar(20) NOT NULL,
    `bus_name` varchar(100) NOT NULL,
    `bus_number` varchar(20) NOT NULL,
    `selected_seats` text NOT NULL,
    `seat_count` int(11) NOT NULL,
    `price_per_seat` decimal(10,2) NOT NULL,
    `total_amount` decimal(10,2) NOT NULL,
    `payment_method` varchar(50) NOT NULL,
    `payment_status` enum('pending','confirmed','failed','refunded') NOT NULL DEFAULT 'pending',
    `booking_status` enum('upcoming','completed','cancelled') NOT NULL DEFAULT 'upcoming',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_booking_id` (`booking_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_travel_date` (`travel_date`),
    KEY `idx_booking_status` (`booking_status`),
    KEY `idx_payment_status` (`payment_status`),
    KEY `idx_bus_number` (`bus_number`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ADMINS TABLE
-- =============================================
CREATE TABLE `admins` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL UNIQUE,
    `password_hash` varchar(255) NOT NULL,
    `full_name` varchar(100) NOT NULL,
    `email` varchar(100) DEFAULT NULL,
    `role` enum('super_admin','admin','moderator') NOT NULL DEFAULT 'admin',
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_login` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_username` (`username`),
    KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INSERT SAMPLE DATA
-- =============================================

-- Insert sample users
INSERT INTO `users` (`username`, `name`, `phone`, `address`, `password_hash`, `created_at`, `last_login`) VALUES
('suresh', 'Suresh Kumar', '9862463805', 'Kathmandu, Nepal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-15 10:30:00', '2024-01-22 14:15:00'),
('john_doe', 'John Doe', '9841234567', 'Pokhara, Nepal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-16 11:00:00', '2024-01-21 09:45:00'),
('sarah_smith', 'Sarah Smith', '9876543210', 'Chitwan, Nepal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-18 09:15:00', '2024-01-20 16:30:00'),
('mike_wilson', 'Mike Wilson', '9823456789', 'Lalitpur, Nepal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-20 14:20:00', '2024-01-21 11:10:00');

-- Insert sample routes
INSERT INTO `routes` (`from_location`, `to_location`, `distance_km`, `estimated_duration`) VALUES
('Kathmandu', 'Pokhara', 200, '6 hours'),
('Kathmandu', 'Chitwan', 150, '4 hours'),
('Pokhara', 'Chitwan', 120, '3 hours'),
('Kathmandu', 'Butwal', 280, '8 hours'),
('Pokhara', 'Butwal', 150, '4 hours');

-- Insert sample buses
INSERT INTO `buses` (`bus_name`, `bus_number`, `bus_type`, `total_seats`, `available_seats`, `price_per_seat`, `departure_time`, `phone`, `amenities`, `route_id`) VALUES
('Himalayan Express', 'Na 4 Kha 2057', 'AC Deluxe', 45, 43, 1100.00, '10:00:00', '9862463805', 'AC, WiFi, Charging Points', 1),
('Mountain Rider', 'Na 4 Kha 2225', 'Non AC', 40, 38, 800.00, '12:00:00', '9812711512', 'Comfortable Seats, Music System', 1),
('Valley Cruiser', 'Na 4 Kha 2027', 'Sleeper', 38, 36, 1400.00, '13:00:00', '9848726227', 'Sleeper Beds, AC, Blankets', 1),
('City Express', 'Na 4 Gha 2458', 'Luxurious', 42, 40, 1800.00, '14:00:00', '9806438787', 'Luxury Seats, Entertainment, Meals', 2),
('Royal Cruiser', 'Na 5 Kha 3156', 'Deluxe', 35, 33, 700.00, '15:00:00', '9845123456', 'Comfortable Journey, AC', 2),
('Night Rider', 'Na 5 Gha 4789', 'Super Deluxe', 40, 38, 1500.00, '16:00:00', '9823456789', 'Premium Comfort, AC, Entertainment', 3),
('Speed Express', 'Na 6 Kha 1234', 'AC', 45, 0, 1100.00, '17:00:00', '9834567890', 'Fast Service, AC, Comfortable', 3),
('Special Express', 'Na 6 Gha 5678', 'Sleeper', 38, 0, 1400.00, '18:00:00', '9845678901', 'Sleeper Service, Premium', 4),
('Golden Express', 'Na 7 Kha 9012', 'Luxurious', 50, 48, 1800.00, '19:00:00', '9856789012', 'Gold Class, Premium Service', 4);

-- Insert sample bookings
INSERT INTO `bookings` (`user_id`, `booking_id`, `passenger_name`, `phone`, `from_location`, `to_location`, `travel_date`, `departure_time`, `bus_name`, `bus_number`, `selected_seats`, `seat_count`, `price_per_seat`, `total_amount`, `payment_method`, `payment_status`, `booking_status`) VALUES
(1, 'BK001A2B3C', 'Suresh Kumar', '9862463805', 'Kathmandu', 'Pokhara', '2024-02-15', '10:00 AM', 'Himalayan Express', 'Na 4 Kha 2057', 'A1,A2', 2, 1100.00, 2200.00, 'esewa', 'confirmed', 'upcoming'),
(2, 'BK002D4E5F', 'John Doe', '9841234567', 'Kathmandu', 'Chitwan', '2024-02-16', '14:00 PM', 'City Express', 'Na 4 Gha 2458', 'B3,B4', 2, 1800.00, 3600.00, 'khalti', 'confirmed', 'upcoming'),
(3, 'BK003G6H7I', 'Sarah Smith', '9876543210', 'Pokhara', 'Chitwan', '2024-02-17', '16:00 PM', 'Night Rider', 'Na 5 Gha 4789', 'C1', 1, 1500.00, 1500.00, 'cash', 'confirmed', 'upcoming'),
(4, 'BK004J8K9L', 'Mike Wilson', '9823456789', 'Kathmandu', 'Butwal', '2024-02-18', '19:00 PM', 'Golden Express', 'Na 7 Kha 9012', 'D5,D6', 2, 1800.00, 3600.00, 'esewa', 'pending', 'upcoming');

-- Insert sample admin users
INSERT INTO `admins` (`username`, `password_hash`, `full_name`, `email`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@busbook.com', 'super_admin'),
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bus Manager', 'manager@busbook.com', 'admin');

-- =============================================
-- CREATE INDEXES FOR PERFORMANCE
-- =============================================

-- Additional indexes for better query performance
CREATE INDEX `idx_bookings_user_date` ON `bookings`(`user_id`, `travel_date`);
CREATE INDEX `idx_bookings_bus_date` ON `bookings`(`bus_number`, `travel_date`);
CREATE INDEX `idx_users_active` ON `users`(`is_active`);
CREATE INDEX `idx_buses_active` ON `buses`(`is_active`);
CREATE INDEX `idx_sessions_cleanup` ON `user_sessions`(`expires_at`);

-- =============================================
-- CREATE VIEWS FOR COMMON QUERIES
-- =============================================

-- View for active bookings with user details
CREATE VIEW `active_bookings_view` AS
SELECT 
    b.id,
    b.booking_id,
    b.passenger_name,
    b.phone,
    b.from_location,
    b.to_location,
    b.travel_date,
    b.departure_time,
    b.bus_name,
    b.bus_number,
    b.selected_seats,
    b.seat_count,
    b.total_amount,
    b.payment_status,
    b.booking_status,
    u.username,
    u.name as user_name
FROM bookings b
JOIN users u ON b.user_id = u.id
WHERE b.booking_status = 'upcoming' 
AND b.travel_date >= CURDATE();

-- View for booking statistics
CREATE VIEW `booking_stats_view` AS
SELECT 
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN booking_status = 'upcoming' THEN 1 END) as upcoming_bookings,
    COUNT(CASE WHEN booking_status = 'completed' THEN 1 END) as completed_bookings,
    COUNT(CASE WHEN payment_status = 'confirmed' THEN 1 END) as confirmed_payments,
    SUM(CASE WHEN payment_status = 'confirmed' THEN total_amount ELSE 0 END) as total_revenue
FROM bookings;

-- =============================================
-- STORED PROCEDURES
-- =============================================

DELIMITER //

-- Procedure to clean expired sessions
CREATE PROCEDURE CleanExpiredSessions()
BEGIN
    DELETE FROM user_sessions WHERE expires_at < NOW();
END //

-- Procedure to get user booking statistics
CREATE PROCEDURE GetUserBookingStats(IN user_id INT)
BEGIN
    SELECT 
        COUNT(*) as total_bookings,
        COUNT(CASE WHEN booking_status = 'upcoming' AND travel_date >= CURDATE() THEN 1 END) as upcoming_bookings,
        COUNT(CASE WHEN booking_status = 'completed' THEN 1 END) as completed_bookings,
        SUM(CASE WHEN payment_status = 'confirmed' THEN total_amount ELSE 0 END) as total_spent
    FROM bookings 
    WHERE user_id = user_id;
END //

DELIMITER ;

-- =============================================
-- TRIGGERS
-- =============================================

DELIMITER //

-- Trigger to update bus available seats when booking is made
CREATE TRIGGER update_bus_seats_after_booking
AFTER INSERT ON bookings
FOR EACH ROW
BEGIN
    UPDATE buses 
    SET available_seats = available_seats - NEW.seat_count 
    WHERE bus_number = NEW.bus_number;
END //

-- Trigger to restore bus seats when booking is cancelled
CREATE TRIGGER restore_bus_seats_after_cancel
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF OLD.booking_status != 'cancelled' AND NEW.booking_status = 'cancelled' THEN
        UPDATE buses 
        SET available_seats = available_seats + NEW.seat_count 
        WHERE bus_number = NEW.bus_number;
    END IF;
END //

DELIMITER ;

-- =============================================
-- SAMPLE DATA VERIFICATION
-- =============================================

-- Display summary of created data
SELECT 'Database Setup Complete!' as Status;
SELECT COUNT(*) as Total_Users FROM users;
SELECT COUNT(*) as Total_Bookings FROM bookings;
SELECT COUNT(*) as Total_Buses FROM buses;
SELECT COUNT(*) as Total_Routes FROM routes;
SELECT COUNT(*) as Total_Admins FROM admins;

-- Show default login credentials
SELECT 
    'Default Credentials' as Info,
    'Username: suresh, Password: password123' as User_Login,
    'Username: admin, Password: admin123' as Admin_Login;
