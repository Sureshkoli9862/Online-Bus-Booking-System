-- =============================================
-- BUS MANAGEMENT SYSTEM - SQL QUERIES
-- =============================================

-- 1. CREATE BUSES TABLE
CREATE TABLE IF NOT EXISTS buses (
    id int(11) NOT NULL AUTO_INCREMENT,
    bus_name varchar(100) NOT NULL,
    bus_number varchar(20) NOT NULL UNIQUE,
    bus_type enum('AC','Non-AC','Deluxe','Super Deluxe','Luxury') NOT NULL,
    total_seats int(11) NOT NULL DEFAULT 45,
    route_from varchar(50) NOT NULL,
    route_to varchar(50) NOT NULL,
    departure_time time NOT NULL,
    arrival_time time NOT NULL,
    fare_amount decimal(10,2) NOT NULL,
    amenities text DEFAULT NULL,
    driver_name varchar(100) NOT NULL,
    driver_phone varchar(15) NOT NULL,
    is_active tinyint(1) NOT NULL DEFAULT 1,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_bus_number (bus_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ADD BUS FUNCTIONALITY - SQL QUERIES
-- =============================================

-- 2. CHECK IF BUS NUMBER EXISTS (before adding)
SELECT id FROM buses WHERE bus_number = ?;

-- 3. INSERT NEW BUS
INSERT INTO buses (
    bus_name, bus_number, bus_type, total_seats, 
    route_from, route_to, departure_time, arrival_time, 
    fare_amount, amenities, driver_name, driver_phone, 
    is_active, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW());

-- 4. SAMPLE INSERT QUERIES
INSERT INTO buses (bus_name, bus_number, bus_type, total_seats, route_from, route_to, departure_time, arrival_time, fare_amount, amenities, driver_name, driver_phone) VALUES
('Deluxe Express', 'BA-1-PA-1234', 'Deluxe', 45, 'kanchanpur', 'kathmandu', '06:00:00', '18:00:00', 2200.00, 'WiFi,AC,Charging Port', 'Ram Bahadur', '9841234567'),
('Luxury Cruiser', 'BA-2-KA-5678', 'Luxury', 40, 'dhangadi', 'kathmandu', '08:00:00', '19:00:00', 3600.00, 'WiFi,AC,Entertainment', 'Shyam Kumar', '9856789012'),
('Super Comfort', 'BA-3-LA-9012', 'Super Deluxe', 42, 'bhasi', 'pokhara', '10:00:00', '18:00:00', 1500.00, 'AC,Charging Port', 'Hari Prasad', '9823456789'),
('Mountain Express', 'BA-4-PO-3456', 'AC', 48, 'attariya', 'nepaljung', '12:00:00', '14:00:00', 800.00, 'AC,Water Bottle', 'Krishna Bahadur', '9812345678'),
('Valley Rider', 'BA-5-KA-7890', 'Non-AC', 50, 'dadeldhura', 'lalitpur', '14:00:00', '03:00:00', 2800.00, 'Charging Port,Water Bottle', 'Gopal Singh', '9834567890');

-- =============================================
-- MANAGE BUS FUNCTIONALITY - SQL QUERIES
-- =============================================

-- 5. GET ALL BUSES (for manage page)
SELECT * FROM buses ORDER BY created_at DESC;

-- 6. GET ACTIVE BUSES ONLY
SELECT * FROM buses WHERE is_active = 1 ORDER BY created_at DESC;

-- 7. GET BUS BY ID
SELECT * FROM buses WHERE id = ?;

-- 8. TOGGLE BUS STATUS (activate/deactivate)
UPDATE buses SET is_active = NOT is_active WHERE id = ?;

-- 9. ACTIVATE BUS
UPDATE buses SET is_active = 1 WHERE id = ?;

-- 10. DEACTIVATE BUS
UPDATE buses SET is_active = 0 WHERE id = ?;

-- 11. DELETE BUS
DELETE FROM buses WHERE id = ?;

-- 12. UPDATE BUS INFORMATION
UPDATE buses SET 
    bus_name = ?, 
    bus_number = ?, 
    bus_type = ?, 
    total_seats = ?, 
    route_from = ?, 
    route_to = ?, 
    departure_time = ?, 
    arrival_time = ?, 
    fare_amount = ?, 
    amenities = ?, 
    driver_name = ?, 
    driver_phone = ?,
    updated_at = NOW()
WHERE id = ?;

-- =============================================
-- SEARCH AND FILTER QUERIES
-- =============================================

-- 13. SEARCH BUSES BY NAME
SELECT * FROM buses WHERE bus_name LIKE ? ORDER BY created_at DESC;

-- 14. SEARCH BUSES BY ROUTE
SELECT * FROM buses WHERE route_from LIKE ? OR route_to LIKE ? ORDER BY created_at DESC;

-- 15. FILTER BY BUS TYPE
SELECT * FROM buses WHERE bus_type = ? ORDER BY created_at DESC;

-- 16. FILTER BY FARE RANGE
SELECT * FROM buses WHERE fare_amount BETWEEN ? AND ? ORDER BY fare_amount ASC;

-- =============================================
-- STATISTICS QUERIES
-- =============================================

-- 17. COUNT TOTAL BUSES
SELECT COUNT(*) as total_buses FROM buses;

-- 18. COUNT ACTIVE BUSES
SELECT COUNT(*) as active_buses FROM buses WHERE is_active = 1;

-- 19. COUNT INACTIVE BUSES
SELECT COUNT(*) as inactive_buses FROM buses WHERE is_active = 0;

-- 20. COUNT BUSES BY TYPE
SELECT bus_type, COUNT(*) as count FROM buses GROUP BY bus_type;

-- 21. AVERAGE FARE AMOUNT
SELECT AVG(fare_amount) as average_fare FROM buses WHERE is_active = 1;

-- 22. BUSES WITH HIGHEST FARE
SELECT * FROM buses WHERE is_active = 1 ORDER BY fare_amount DESC LIMIT 5;

-- 23. BUSES WITH LOWEST FARE
SELECT * FROM buses WHERE is_active = 1 ORDER BY fare_amount ASC LIMIT 5;

-- =============================================
-- ROUTE-BASED QUERIES
-- =============================================

-- 24. GET BUSES FOR SPECIFIC ROUTE
SELECT * FROM buses WHERE route_from = ? AND route_to = ? AND is_active = 1 ORDER BY departure_time ASC;

-- 25. GET ALL UNIQUE ROUTES
SELECT DISTINCT route_from, route_to FROM buses WHERE is_active = 1;

-- 26. GET POPULAR ROUTES (most buses)
SELECT route_from, route_to, COUNT(*) as bus_count 
FROM buses WHERE is_active = 1 
GROUP BY route_from, route_to 
ORDER BY bus_count DESC;

-- =============================================
-- MAINTENANCE QUERIES
-- =============================================

-- 27. BACKUP BUSES TABLE
CREATE TABLE buses_backup AS SELECT * FROM buses;

-- 28. RESTORE FROM BACKUP
INSERT INTO buses SELECT * FROM buses_backup WHERE id NOT IN (SELECT id FROM buses);

-- 29. CLEAN UP OLD INACTIVE BUSES (older than 1 year)
DELETE FROM buses WHERE is_active = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- 30. UPDATE ALL BUSES TIMESTAMP
UPDATE buses SET updated_at = NOW();

-- =============================================
-- VALIDATION QUERIES
-- =============================================

-- 31. CHECK FOR DUPLICATE BUS NUMBERS
SELECT bus_number, COUNT(*) as count FROM buses GROUP BY bus_number HAVING count > 1;

-- 32. FIND BUSES WITH INVALID DATA
SELECT * FROM buses WHERE 
    bus_name = '' OR 
    bus_number = '' OR 
    total_seats < 10 OR 
    total_seats > 60 OR 
    fare_amount < 100;

-- 33. CHECK BUSES WITHOUT DRIVER INFO
SELECT * FROM buses WHERE driver_name = '' OR driver_phone = '';

-- =============================================
-- REPORTING QUERIES
-- =============================================

-- 34. MONTHLY BUS ADDITION REPORT
SELECT 
    YEAR(created_at) as year,
    MONTH(created_at) as month,
    COUNT(*) as buses_added
FROM buses 
GROUP BY YEAR(created_at), MONTH(created_at) 
ORDER BY year DESC, month DESC;

-- 35. BUS TYPE DISTRIBUTION
SELECT 
    bus_type,
    COUNT(*) as total,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
FROM buses 
GROUP BY bus_type;

-- =============================================
-- END OF SQL QUERIES
-- =============================================
