-- ============================================================
-- HOTEL MANAGEMENT SYSTEM DATABASE SCHEMA & DUMMY DATA
-- Database Name: hotel_management
-- Default Credentials: admin / admin123
-- ============================================================

CREATE DATABASE IF NOT EXISTS `hotel_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hotel_management`;

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('admin', 'staff') DEFAULT 'admin',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Admin: admin / admin123
INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `phone`, `role`) VALUES
(1, 'admin', '$2y$10$jotrbD4j2hodmOrVSrKDsur4l/VGn7EgmN7aP/l/Z9gVDjcFYv15e', 'System Administrator', 'admin@grandroyale.com', '+91 9876543210', 'admin');

-- --------------------------------------------------------
-- Table structure for `rooms`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_number` VARCHAR(20) NOT NULL UNIQUE,
  `room_type` ENUM('Single', 'Double', 'Deluxe', 'Suite') NOT NULL,
  `ac_type` ENUM('AC', 'Non AC') NOT NULL,
  `floor_number` INT NOT NULL,
  `price_per_night` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('Available', 'Booked', 'Occupied', 'Maintenance') DEFAULT 'Available',
  `image` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Sample Rooms
INSERT INTO `rooms` (`id`, `room_number`, `room_type`, `ac_type`, `floor_number`, `price_per_night`, `status`, `image`) VALUES
(1, '101', 'Single', 'AC', 1, 2500.00, 'Available', 'room_101.jpg'),
(2, '102', 'Single', 'Non AC', 1, 1800.00, 'Available', 'room_102.jpg'),
(3, '103', 'Double', 'AC', 1, 3500.00, 'Occupied', 'room_103.jpg'),
(4, '104', 'Double', 'Non AC', 1, 2800.00, 'Booked', 'room_104.jpg'),
(5, '201', 'Deluxe', 'AC', 2, 5000.00, 'Available', 'room_201.jpg'),
(6, '202', 'Deluxe', 'AC', 2, 5200.00, 'Occupied', 'room_202.jpg'),
(7, '301', 'Suite', 'AC', 3, 9500.00, 'Available', 'room_301.jpg'),
(8, '302', 'Suite', 'AC', 3, 10500.00, 'Maintenance', 'room_302.jpg');

-- --------------------------------------------------------
-- Table structure for `customers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `dob` DATE DEFAULT NULL,
  `phone` VARCHAR(20) NOT NULL UNIQUE,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(50) DEFAULT NULL,
  `state` VARCHAR(50) DEFAULT NULL,
  `country` VARCHAR(50) DEFAULT 'India',
  `id_proof_type` ENUM('Aadhar', 'PAN', 'Passport', 'Driving License') NOT NULL,
  `id_number` VARCHAR(50) NOT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Sample Customers
INSERT INTO `customers` (`id`, `full_name`, `gender`, `dob`, `phone`, `email`, `address`, `city`, `state`, `country`, `id_proof_type`, `id_number`, `photo`) VALUES
(1, 'Rahul Sharma', 'Male', '1990-05-15', '9876543211', 'rahul.sharma@example.com', '45 MG Road, Connaught Place', 'New Delhi', 'Delhi', 'India', 'Aadhar', '5489-1234-9876', 'customer_1.jpg'),
(2, 'Priya Patel', 'Female', '1994-08-22', '9876543212', 'priya.patel@example.com', '12 S.G. Highway, Thaltej', 'Ahmedabad', 'Gujarat', 'India', 'PAN', 'ABCDE1234F', 'customer_2.jpg'),
(3, 'Amit Verma', 'Male', '1985-11-03', '9876543213', 'amit.verma@example.com', '88 Park Street', 'Kolkata', 'West Bengal', 'India', 'Passport', 'Z9876543', 'customer_3.jpg'),
(4, 'Sneha Reddy', 'Female', '1996-03-30', '9876543214', 'sneha.reddy@example.com', '201 Jubilee Hills', 'Hyderabad', 'Telangana', 'India', 'Driving License', 'DL-04201996001', 'customer_4.jpg');

-- --------------------------------------------------------
-- Table structure for `bookings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_number` VARCHAR(30) NOT NULL UNIQUE,
  `customer_id` INT NOT NULL,
  `room_id` INT NOT NULL,
  `check_in_date` DATE NOT NULL,
  `check_out_date` DATE NOT NULL,
  `actual_check_in_time` DATETIME DEFAULT NULL,
  `actual_check_out_time` DATETIME DEFAULT NULL,
  `adults` INT DEFAULT 1,
  `children` INT DEFAULT 0,
  `special_requests` TEXT DEFAULT NULL,
  `booking_amount` DECIMAL(10, 2) NOT NULL,
  `advance_payment` DECIMAL(10, 2) DEFAULT 0.00,
  `balance_amount` DECIMAL(10, 2) NOT NULL,
  `payment_mode` ENUM('Cash', 'Card', 'UPI') DEFAULT 'Cash',
  `status` ENUM('Pending', 'Confirmed', 'Checked In', 'Completed', 'Cancelled') DEFAULT 'Confirmed',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Sample Bookings
INSERT INTO `bookings` (`id`, `booking_number`, `customer_id`, `room_id`, `check_in_date`, `check_out_date`, `actual_check_in_time`, `actual_check_out_time`, `adults`, `children`, `special_requests`, `booking_amount`, `advance_payment`, `balance_amount`, `payment_mode`, `status`, `created_at`) VALUES
(1, 'HMS-BK-1001', 1, 3, CURRENT_DATE(), DATE_ADD(CURRENT_DATE(), INTERVAL 2 DAY), NOW(), NULL, 2, 0, 'High floor preferred', 7000.00, 2000.00, 5000.00, 'UPI', 'Checked In', NOW()),
(2, 'HMS-BK-1002', 2, 6, CURRENT_DATE(), DATE_ADD(CURRENT_DATE(), INTERVAL 3 DAY), NOW(), NULL, 2, 1, 'Late check-out request', 15600.00, 5600.00, 10000.00, 'Card', 'Checked In', NOW()),
(3, 'HMS-BK-1003', 3, 4, DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY), DATE_ADD(CURRENT_DATE(), INTERVAL 4 DAY), NULL, NULL, 1, 0, 'Airport shuttle needed', 8400.00, 2000.00, 6400.00, 'Cash', 'Confirmed', NOW()),
(4, 'HMS-BK-1004', 4, 1, DATE_SUB(CURRENT_DATE(), INTERVAL 3 DAY), CURRENT_DATE(), DATE_SUB(NOW(), INTERVAL 3 DAY), NOW(), 1, 0, 'Quiet room', 7500.00, 7500.00, 0.00, 'UPI', 'Completed', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- --------------------------------------------------------
-- Table structure for `invoices`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(30) NOT NULL UNIQUE,
  `booking_id` INT NOT NULL,
  `room_charges` DECIMAL(10, 2) NOT NULL,
  `extra_food` DECIMAL(10, 2) DEFAULT 0.00,
  `extra_laundry` DECIMAL(10, 2) DEFAULT 0.00,
  `extra_minibar` DECIMAL(10, 2) DEFAULT 0.00,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  `gst_percent` DECIMAL(5, 2) DEFAULT 12.00,
  `gst_amount` DECIMAL(10, 2) NOT NULL,
  `discount` DECIMAL(10, 2) DEFAULT 0.00,
  `grand_total` DECIMAL(10, 2) NOT NULL,
  `paid_amount` DECIMAL(10, 2) NOT NULL,
  `due_amount` DECIMAL(10, 2) DEFAULT 0.00,
  `status` ENUM('Paid', 'Partial', 'Unpaid') DEFAULT 'Paid',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Sample Invoice for completed booking
INSERT INTO `invoices` (`id`, `invoice_number`, `booking_id`, `room_charges`, `extra_food`, `extra_laundry`, `extra_minibar`, `subtotal`, `gst_percent`, `gst_amount`, `discount`, `grand_total`, `paid_amount`, `due_amount`, `status`, `created_at`) VALUES
(1, 'HMS-INV-2001', 4, 7500.00, 1200.00, 300.00, 500.00, 9500.00, 12.00, 1140.00, 640.00, 10000.00, 10000.00, 0.00, 'Paid', NOW());
