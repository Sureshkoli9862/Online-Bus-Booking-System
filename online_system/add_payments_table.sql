-- Add payments table to track all payment transactions
USE `online_system`;

-- Create payments table
CREATE TABLE IF NOT EXISTS `payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `booking_id` varchar(20) NOT NULL,
    `user_id` int(11) NOT NULL,
    `payment_id` varchar(50) NOT NULL UNIQUE,
    `amount` decimal(10,2) NOT NULL,
    `payment_method` enum('esewa','khalti','cash','bank_transfer') NOT NULL,
    `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
    `transaction_id` varchar(100) DEFAULT NULL,
    `payment_gateway_response` text DEFAULT NULL,
    `payment_date` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_payment_id` (`payment_id`),
    KEY `idx_booking_id` (`booking_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_payment_status` (`payment_status`),
    KEY `idx_payment_method` (`payment_method`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample payment records for existing bookings
INSERT INTO `payments` (`booking_id`, `user_id`, `payment_id`, `amount`, `payment_method`, `payment_status`, `transaction_id`, `payment_date`) VALUES
('BK001A2B3C', 1, 'PAY001ESW2024', 2200.00, 'esewa', 'completed', 'ESW123456789', '2024-02-15 09:30:00'),
('BK002D4E5F', 2, 'PAY002KHL2024', 3600.00, 'khalti', 'completed', 'KHL987654321', '2024-02-16 13:45:00'),
('BK003G6H7I', 3, 'PAY003CSH2024', 1500.00, 'cash', 'completed', 'CASH001', '2024-02-17 15:20:00'),
('BK004J8K9L', 4, 'PAY004ESW2024', 3600.00, 'esewa', 'pending', NULL, NULL);

SELECT 'Payments table created and sample data inserted!' as Status;
