-- Add password reset tokens table for forgot password functionality
USE `online_system`;

-- Create password reset tokens table
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `token` varchar(64) NOT NULL,
    `expires_at` timestamp NOT NULL,
    `used` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_token` (`token`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_expires_at` (`expires_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clean up expired tokens (optional cleanup procedure)
DELIMITER //
CREATE PROCEDURE CleanExpiredPasswordTokens()
BEGIN
    DELETE FROM password_reset_tokens WHERE expires_at < NOW() OR used = 1;
END //
DELIMITER ;

SELECT 'Password reset tokens table created successfully!' as Status;
