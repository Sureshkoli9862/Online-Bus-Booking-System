-- Update database triggers to handle seat availability based on payment status
-- This script modifies the existing triggers to only block seats when payment is confirmed

USE `online_system`;

-- Drop existing triggers
DROP TRIGGER IF EXISTS update_bus_seats_after_booking;
DROP TRIGGER IF EXISTS restore_bus_seats_after_cancel;

DELIMITER //

-- New trigger: Only reduce seats when payment status changes to confirmed/completed
CREATE TRIGGER update_bus_seats_on_payment_confirm
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    -- When payment status changes from pending to confirmed/completed
    IF (OLD.payment_status = 'pending' OR OLD.payment_status = 'failed') 
       AND (NEW.payment_status = 'confirmed' OR NEW.payment_status = 'completed') THEN
        UPDATE buses 
        SET available_seats = available_seats - NEW.seat_count 
        WHERE bus_number = NEW.bus_number;
    END IF;
    
    -- When payment status changes from confirmed/completed back to pending/failed
    IF (OLD.payment_status = 'confirmed' OR OLD.payment_status = 'completed') 
       AND (NEW.payment_status = 'pending' OR NEW.payment_status = 'failed') THEN
        UPDATE buses 
        SET available_seats = available_seats + NEW.seat_count 
        WHERE bus_number = NEW.bus_number;
    END IF;
END //

-- Trigger to restore bus seats when booking is cancelled
CREATE TRIGGER restore_bus_seats_after_cancel
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF OLD.booking_status != 'cancelled' AND NEW.booking_status = 'cancelled' THEN
        -- Only restore seats if payment was confirmed (seats were actually blocked)
        IF OLD.payment_status = 'confirmed' OR OLD.payment_status = 'completed' THEN
            UPDATE buses 
            SET available_seats = available_seats + NEW.seat_count 
            WHERE bus_number = NEW.bus_number;
        END IF;
    END IF;
END //

-- Trigger for cash payments (immediate confirmation on insert)
CREATE TRIGGER handle_cash_payment_on_insert
AFTER INSERT ON bookings
FOR EACH ROW
BEGIN
    -- For cash payments, immediately reduce available seats since payment is confirmed
    IF NEW.payment_method = 'cash' AND (NEW.payment_status = 'confirmed' OR NEW.payment_status = 'completed') THEN
        UPDATE buses 
        SET available_seats = available_seats - NEW.seat_count 
        WHERE bus_number = NEW.bus_number;
    END IF;
END //

DELIMITER ;

-- Update existing bookings to reflect correct seat availability
-- First, reset all bus available seats to their total capacity
UPDATE buses b 
SET available_seats = total_seats;

-- Then reduce seats only for confirmed/completed bookings
UPDATE buses b 
SET available_seats = available_seats - (
    SELECT COALESCE(SUM(seat_count), 0) 
    FROM bookings 
    WHERE bus_number = b.bus_number 
    AND booking_status != 'cancelled'
    AND (payment_status = 'confirmed' OR payment_status = 'completed')
);

-- Ensure available_seats doesn't go below 0
UPDATE buses 
SET available_seats = 0 
WHERE available_seats < 0;

SELECT 'Seat availability triggers updated successfully!' as Status;
