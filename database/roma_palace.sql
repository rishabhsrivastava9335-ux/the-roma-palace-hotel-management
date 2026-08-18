-- ============================================================================
-- THE ROMA PALACE — LUXURY HOTEL MANAGEMENT SYSTEM
-- BTech CSE DBMS Mini Project Database Schema & Demo Queries
-- Database: roma_palace
-- Target RDBMS: MySQL 8.0+ / MariaDB / SQLite compatible
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `roma_palace` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `roma_palace`;

-- Disable foreign key checks for clean recreation
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `service_order_items`;
DROP TABLE IF EXISTS `service_orders`;
DROP TABLE IF EXISTS `booking_services`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `room_amenities`;
DROP TABLE IF EXISTS `rooms`;
DROP TABLE IF EXISTS `menu_items`;
DROP TABLE IF EXISTS `restaurants`;
DROP TABLE IF EXISTS `experiences`;
DROP TABLE IF EXISTS `offers`;
DROP TABLE IF EXISTS `staff`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `hotels`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 1. USERS TABLE (Authentication & Role Based Access Control)
-- ============================================================================
CREATE TABLE `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'customer', 'staff') NOT NULL DEFAULT 'customer',
    `status` ENUM('active', 'suspended', 'inactive') NOT NULL DEFAULT 'active',
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 2. CUSTOMERS TABLE (Guest Profiles & ID Proof Verification)
-- ============================================================================
CREATE TABLE `customers` (
    `customer_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `full_name` VARCHAR(120) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `address` TEXT NULL,
    `city` VARCHAR(80) NULL,
    `state` VARCHAR(80) NULL,
    `country` VARCHAR(80) DEFAULT 'India',
    `postal_code` VARCHAR(20) NULL,
    `id_type` ENUM('Aadhaar Card', 'Passport', 'Voter ID', 'Driving License', 'PAN Card') NOT NULL DEFAULT 'Aadhaar Card',
    `id_number` VARCHAR(50) NOT NULL,
    `reg_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    INDEX `idx_cust_phone` (`phone`),
    INDEX `idx_cust_name` (`full_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 3. ADMINS TABLE (Management & Reception Staff)
-- ============================================================================
CREATE TABLE `admins` (
    `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `full_name` VARCHAR(120) NOT NULL,
    `role_title` VARCHAR(80) NOT NULL DEFAULT 'General Manager',
    `department` VARCHAR(80) NOT NULL DEFAULT 'Executive Management',
    `phone` VARCHAR(20) NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 4. HOTELS TABLE (Palaces & Destinations)
-- ============================================================================
CREATE TABLE `hotels` (
    `hotel_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `city` VARCHAR(80) NOT NULL,
    `state` VARCHAR(80) NOT NULL,
    `tagline` VARCHAR(200) NOT NULL,
    `address` TEXT NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `email` VARCHAR(120) NOT NULL,
    `star_rating` DECIMAL(2,1) DEFAULT 5.0,
    `starting_price` DECIMAL(10,2) NOT NULL,
    `total_rooms` INT NOT NULL DEFAULT 30,
    `image_url` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `highlights` TEXT NULL,
    `status` ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_hotels_city` (`city`),
    INDEX `idx_hotels_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 5. ROOMS TABLE (Inventory & Category Management)
-- ============================================================================
CREATE TABLE `rooms` (
    `room_id` INT AUTO_INCREMENT PRIMARY KEY,
    `hotel_id` INT NOT NULL,
    `room_number` VARCHAR(20) NOT NULL,
    `room_type` ENUM('Deluxe Room', 'Premium Room', 'Executive Room', 'Luxury Suite', 'Royal Suite') NOT NULL,
    `floor` INT NOT NULL DEFAULT 1,
    `capacity` INT NOT NULL DEFAULT 2,
    `bed_type` VARCHAR(50) NOT NULL DEFAULT 'King Bed',
    `size_sqft` INT NOT NULL DEFAULT 450,
    `price_per_night` DECIMAL(10,2) NOT NULL,
    `status` ENUM('Available', 'Reserved', 'Occupied', 'Maintenance') NOT NULL DEFAULT 'Available',
    `image_url` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `view_type` VARCHAR(80) DEFAULT 'Palace Courtyard / Lake View',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`hotel_id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_hotel_room` (`hotel_id`, `room_number`),
    INDEX `idx_rooms_type` (`room_type`),
    INDEX `idx_rooms_status` (`status`),
    INDEX `idx_rooms_price` (`price_per_night`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 6. ROOM_AMENITIES TABLE (Normalized Multi-Value Amenities)
-- ============================================================================
CREATE TABLE `room_amenities` (
    `amenity_id` INT AUTO_INCREMENT PRIMARY KEY,
    `room_id` INT NOT NULL,
    `amenity_name` VARCHAR(100) NOT NULL,
    `icon_class` VARCHAR(50) DEFAULT 'fa-solid fa-check',
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`room_id`) ON DELETE CASCADE,
    INDEX `idx_amenity_room` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 7. BOOKINGS TABLE (Master Reservation Ledger)
-- ============================================================================
CREATE TABLE `bookings` (
    `booking_id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_ref` VARCHAR(30) NOT NULL UNIQUE,
    `customer_id` INT NOT NULL,
    `hotel_id` INT NOT NULL,
    `room_id` INT NOT NULL,
    `check_in_date` DATE NOT NULL,
    `check_out_date` DATE NOT NULL,
    `total_guests` INT NOT NULL DEFAULT 2,
    `num_rooms` INT NOT NULL DEFAULT 1,
    `promo_code` VARCHAR(50) NULL,
    `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
    `room_charges` DECIMAL(10,2) NOT NULL,
    `service_charges` DECIMAL(10,2) DEFAULT 0.00,
    `tax_amount` DECIMAL(10,2) NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `payment_status` ENUM('Pending', 'Paid', 'Refunded', 'Failed') NOT NULL DEFAULT 'Pending',
    `booking_status` ENUM('Confirmed', 'Checked-In', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Confirmed',
    `special_requests` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`) ON DELETE CASCADE,
    FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`hotel_id`) ON DELETE CASCADE,
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`room_id`) ON DELETE CASCADE,
    INDEX `idx_book_dates` (`check_in_date`, `check_out_date`),
    INDEX `idx_book_status` (`booking_status`),
    INDEX `idx_book_ref` (`booking_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 8. SERVICES TABLE (Hotel Add-on Services & Experiences)
-- ============================================================================
CREATE TABLE `services` (
    `service_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(120) NOT NULL,
    `category` ENUM('Dining', 'Wellness & Spa', 'Transport', 'Housekeeping', 'Recreation', 'Special') NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `unit` VARCHAR(50) DEFAULT 'Per Guest / Per Day',
    `description` TEXT NOT NULL,
    `icon_class` VARCHAR(60) DEFAULT 'fa-solid fa-concierge-bell',
    `status` ENUM('Available', 'Unavailable') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 9. BOOKING_SERVICES TABLE (Services attached during initial reservation)
-- ============================================================================
CREATE TABLE `booking_services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL,
    `service_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `total_price` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`booking_id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`service_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 10. PAYMENTS TABLE (Financial Transactions & Audit Trail)
-- ============================================================================
CREATE TABLE `payments` (
    `payment_id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL,
    `customer_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_method` ENUM('UPI', 'Credit Card', 'Debit Card', 'Net Banking', 'Cash at Hotel') NOT NULL,
    `transaction_id` VARCHAR(100) NOT NULL UNIQUE,
    `gateway_response` TEXT NULL,
    `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('Paid', 'Pending', 'Refunded', 'Failed') NOT NULL DEFAULT 'Paid',
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`booking_id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`) ON DELETE CASCADE,
    INDEX `idx_pay_status` (`status`),
    INDEX `idx_pay_tx` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 11. SERVICE_ORDERS TABLE (On-Demand Room Service Requests during Stay)
-- ============================================================================
CREATE TABLE `service_orders` (
    `order_id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL,
    `customer_id` INT NOT NULL,
    `order_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('Pending', 'In Progress', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending',
    `instructions` TEXT NULL,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`booking_id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`) ON DELETE CASCADE,
    INDEX `idx_service_orders_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 12. SERVICE_ORDER_ITEMS TABLE (Line items in room service orders)
-- ============================================================================
CREATE TABLE `service_order_items` (
    `order_item_id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `service_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `price` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `service_orders`(`order_id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`service_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 13. STAFF TABLE (Employee Directory & Human Resources)
-- ============================================================================
CREATE TABLE `staff` (
    `staff_id` INT AUTO_INCREMENT PRIMARY KEY,
    `hotel_id` INT NOT NULL,
    `name` VARCHAR(120) NOT NULL,
    `department` ENUM('Reception', 'Housekeeping', 'Restaurant', 'Security', 'Management', 'Maintenance') NOT NULL,
    `position` VARCHAR(80) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(120) NOT NULL,
    `joining_date` DATE NOT NULL,
    `salary` DECIMAL(10,2) NOT NULL,
    `status` ENUM('Active', 'On Leave', 'Resigned') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`hotel_id`) ON DELETE CASCADE,
    INDEX `idx_staff_dept` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 14. RESTAURANTS TABLE (Culinary Outlets & Venues)
-- ============================================================================
CREATE TABLE `restaurants` (
    `restaurant_id` INT AUTO_INCREMENT PRIMARY KEY,
    `hotel_id` INT NOT NULL,
    `name` VARCHAR(120) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `cuisine` VARCHAR(100) NOT NULL,
    `opening_hours` VARCHAR(100) NOT NULL,
    `location_desc` VARCHAR(150) NOT NULL,
    `dress_code` VARCHAR(80) DEFAULT 'Smart Casual / Elegant',
    `image_url` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`hotel_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 15. MENU_ITEMS TABLE (Restaurant Menu Catalog)
-- ============================================================================
CREATE TABLE `menu_items` (
    `item_id` INT AUTO_INCREMENT PRIMARY KEY,
    `restaurant_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `category` ENUM('Appetizers', 'Main Course', 'Royal Thali', 'Desserts', 'Beverages & Wine', 'Breakfast') NOT NULL,
    `price` DECIMAL(8,2) NOT NULL,
    `dietary_flag` ENUM('Veg', 'Non-Veg', 'Vegan', 'Jain Option') NOT NULL DEFAULT 'Veg',
    `description` TEXT NOT NULL,
    `is_chef_special` TINYINT(1) DEFAULT 0,
    `is_available` TINYINT(1) DEFAULT 1,
    FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`restaurant_id`) ON DELETE CASCADE,
    INDEX `idx_menu_cat` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 16. OFFERS TABLE (Promotions, Seasonal Packages & Promo Codes)
-- ============================================================================
CREATE TABLE `offers` (
    `offer_id` INT AUTO_INCREMENT PRIMARY KEY,
    `hotel_id` INT NULL,
    `code` VARCHAR(30) NOT NULL UNIQUE,
    `title` VARCHAR(150) NOT NULL,
    `tag` VARCHAR(80) NOT NULL,
    `discount_percent` INT DEFAULT 0,
    `flat_discount` DECIMAL(10,2) DEFAULT 0.00,
    `description` TEXT NOT NULL,
    `benefits` TEXT NOT NULL,
    `validity_date` DATE NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `price_note` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`hotel_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 17. EXPERIENCES TABLE (Curated Royal Tours & Masterclasses)
-- ============================================================================
CREATE TABLE `experiences` (
    `experience_id` INT AUTO_INCREMENT PRIMARY KEY,
    `hotel_id` INT NULL,
    `title` VARCHAR(150) NOT NULL,
    `category` VARCHAR(80) NOT NULL DEFAULT 'Royal Heritage',
    `duration` VARCHAR(50) NOT NULL,
    `timing` VARCHAR(50) NOT NULL,
    `price_per_person` DECIMAL(10,2) NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `short_desc` VARCHAR(255) NOT NULL,
    `full_desc` TEXT NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`hotel_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 18. REVIEWS TABLE (Customer Ratings & Testimonials)
-- ============================================================================
CREATE TABLE `reviews` (
    `review_id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `hotel_id` INT NOT NULL,
    `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `review_title` VARCHAR(150) NOT NULL,
    `comments` TEXT NOT NULL,
    `stay_date` VARCHAR(50) NULL,
    `is_approved` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`) ON DELETE CASCADE,
    FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`hotel_id`) ON DELETE CASCADE,
    INDEX `idx_reviews_hotel` (`hotel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================================
-- SAMPLE DATA INSERTION (Realistic Indian Palaces & INR Currency)
-- ============================================================================

-- A. USERS (Admin: admin@romapalace.com / Admin@123, Guest: guest@romapalace.com / Guest@123)
-- Using actual bcrypt hashes:
-- Admin@123 -> $2y$10$wTf7m33G6fAOxY6uX6q2u.9gZ0l0i5dEvu0EaY718aV3gA0xGg052
-- Guest@123 -> $2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W
INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 'admin@romapalace.com', '$2y$10$wTf7m33G6fAOxY6uX6q2u.9gZ0l0i5dEvu0EaY718aV3gA0xGg052', 'admin', 'active'),
(2, 'guest@romapalace.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active'),
(3, 'rajesh.sharma@example.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active'),
(4, 'priya.nair@example.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active'),
(5, 'arjun.singh@example.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active'),
(6, 'ananya.deshmukh@example.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active'),
(7, 'vikram.mehta@example.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active'),
(8, 'sneha.reddy@example.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active'),
(9, 'rohit.verma@example.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active'),
(10, 'kavita.patel@example.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active'),
(11, 'siddharth.roy@example.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active'),
(12, 'meera.iyer@example.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active'),
(13, 'aditya.kapoor@example.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active'),
(14, 'tanya.bhatia@example.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active'),
(15, 'deepak.gupta@example.com', '$2y$10$0sR6.yZ2ZkV6k8mB5Q1CFeT0c8uC1l5YpM6qA6e8h.wDqRkC5vL1W', 'customer', 'active');

-- B. ADMIN PROFILE
INSERT INTO `admins` (`admin_id`, `user_id`, `full_name`, `role_title`, `department`, `phone`) VALUES
(1, 1, 'Ranvijay Singh Rathore', 'General Manager & Director', 'Executive Management', '+91 98290 12345');

-- C. CUSTOMER PROFILES (15 Customers with ID verification)
INSERT INTO `customers` (`customer_id`, `user_id`, `full_name`, `phone`, `address`, `city`, `state`, `country`, `postal_code`, `id_type`, `id_number`) VALUES
(1, 2, 'Rohan Malhotra', '+91 98110 54321', 'A-42, Vasant Vihar', 'New Delhi', 'Delhi', 'India', '110057', 'Aadhaar Card', '4589 1234 9876'),
(2, 3, 'Rajesh Sharma', '+91 98201 23456', 'Flat 12B, Sea Green Towers, Worli', 'Mumbai', 'Maharashtra', 'India', '400018', 'Passport', 'Z9876543'),
(3, 4, 'Priya Nair', '+91 94470 98765', 'Palm Grove Villa, Panampilly Nagar', 'Kochi', 'Kerala', 'India', '682036', 'Aadhaar Card', '7845 6123 9045'),
(4, 5, 'Arjun Singh Shekhawat', '+91 94140 11223', 'Civil Lines, C-Scheme', 'Jaipur', 'Rajasthan', 'India', '302001', 'Driving License', 'RJ14 2018004567'),
(5, 6, 'Ananya Deshmukh', '+91 97654 33221', 'Senapati Bapat Road, Shivaji Nagar', 'Pune', 'Maharashtra', 'India', '411016', 'Passport', 'P5432189'),
(6, 7, 'Vikram Mehta', '+91 98791 22334', 'Satellite Road, Bodakdev', 'Ahmedabad', 'Gujarat', 'India', '380015', 'Aadhaar Card', '6541 2309 8765'),
(7, 8, 'Sneha Reddy', '+91 98480 55667', 'Jubilee Hills, Road No. 36', 'Hyderabad', 'Telangana', 'India', '500033', 'PAN Card', 'ABCDE1234F'),
(8, 9, 'Rohit Verma', '+91 99350 44556', 'Hazratganj Main Road', 'Lucknow', 'Uttar Pradesh', 'India', '226001', 'Voter ID', 'UP/45/213/9876'),
(9, 10, 'Kavita Patel', '+91 98250 77889', 'Alkapuri Royal Mansions', 'Vadodara', 'Gujarat', 'India', '390007', 'Aadhaar Card', '3214 9876 5432'),
(10, 11, 'Siddharth Roy', '+91 98300 88990', 'Ballygunge Circular Road', 'Kolkata', 'West Bengal', 'India', '700019', 'Passport', 'K3214567'),
(11, 12, 'Meera Iyer', '+91 98400 11224', 'Boat Club Road, R.A. Puram', 'Chennai', 'Tamil Nadu', 'India', '600028', 'Aadhaar Card', '8901 2345 6789'),
(12, 13, 'Aditya Kapoor', '+91 98100 99887', 'Golf Course Road, DLF Phase 5', 'Gurugram', 'Haryana', 'India', '122002', 'Passport', 'M8765432'),
(13, 14, 'Tanya Bhatia', '+91 98140 66778', 'Sector 9-D, Inner Ring', 'Chandigarh', 'Punjab', 'India', '160009', 'Aadhaar Card', '5678 9012 3456'),
(14, 15, 'Deepak Gupta', '+91 94250 33445', 'Arera Colony, E-3', 'Bhopal', 'Madhya Pradesh', 'India', '462016', 'Driving License', 'MP04 2016007890'),
(15, 1, 'Devendra Rathore', '+91 97110 00112', 'Palace Enclave, Fort Road', 'Jodhpur', 'Rajasthan', 'India', '342001', 'Passport', 'R1029384');

-- D. 4 LUXURY PALACES & HOTELS
INSERT INTO `hotels` (`hotel_id`, `name`, `slug`, `city`, `state`, `tagline`, `address`, `phone`, `email`, `star_rating`, `starting_price`, `total_rooms`, `image_url`, `description`, `highlights`) VALUES
(1, 'The Roma Palace Jaipur', 'roma-palace-jaipur', 'Jaipur', 'Rajasthan', 'A Royal Heritage Sanctuary of Kings', 'Bhawani Singh Road, Rambagh Enclave, Jaipur, Rajasthan 302005', '+91 141 278 9000', 'jaipur@romapalace.com', 5.0, 18500.00, 32, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1600&q=85', 'Set amidst 24 acres of manicured Mughal gardens, The Roma Palace Jaipur stands as an epitome of Rajputana grandeur and timeless royal architecture.', 'Heritage Architecture, Mughal Gardens, Royal Courtyard, Temperature-controlled Pool, 24/7 Butler Service'),
(2, 'The Roma Palace Goa', 'roma-palace-goa', 'Goa', 'Goa', 'Contemporary Coastal Escape & Oceanfront Bliss', 'Sinquerim Beachfront, Candolim, North Goa 403515', '+91 832 671 2000', 'goa@romapalace.com', 5.0, 22000.00, 28, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1600&q=85', 'Perched gently along the golden sands of the Arabian Sea, The Roma Palace Goa blends Portuguese architectural refinement with serene coastal luxury.', 'Private Beach Access, Infinity Ocean Pool, Ayurvedic Spa Pavilion, Sunset Deck Lounge, Water Sports'),
(3, 'The Roma Palace Udaipur', 'roma-palace-udaipur', 'Udaipur', 'Rajasthan', 'Lakefront Opulence & Timeless Romance', 'Haridas Ji Ki Magri, Lake Pichola Waterfront, Udaipur, Rajasthan 313001', '+91 294 243 5000', 'udaipur@romapalace.com', 5.0, 28000.00, 30, 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1600&q=85', 'Rising serenely upon the tranquil waters of Lake Pichola with panoramic views of the Aravalli Hills, offering an unforgettable fairy-tale palace escape.', 'Private Boat Jetty, Lakefront Fine Dining, Royal Spa Suites, Heritage Peepal Courtyard, Sunset Flute Recitals'),
(4, 'The Roma Palace Lucknow', 'roma-palace-lucknow', 'Lucknow', 'Uttar Pradesh', 'Nawabi Hospitality & Refined Aristocratic Elegance', 'Vipin Khand, Gomti Nagar Riverfront, Lucknow, Uttar Pradesh 226010', '+91 522 409 8000', 'lucknow@romapalace.com', 5.0, 14500.00, 30, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1600&q=85', 'Echoing the golden era of Awadhi Nawabs with grand chandeliers, arched corridors, and legendary culinary art along the serene Gomti River.', 'Awadhi Culinary Masterclasses, Grand Ballroom, Imperial Spa, Executive Business Lounge, Royal Chariot Welcome');

-- E. 25 REALISTIC ROOMS
INSERT INTO `rooms` (`room_id`, `hotel_id`, `room_number`, `room_type`, `floor`, `capacity`, `bed_type`, `size_sqft`, `price_per_night`, `status`, `image_url`, `description`, `view_type`) VALUES
-- Jaipur Rooms
(1, 1, 'JP-101', 'Deluxe Room', 1, 2, 'King Bed', 480, 18500.00, 'Available', 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=85', 'Elegantly appointed room featuring authentic Rajasthani jharokhas, handcrafted wooden furnishings, and garden views.', 'Mughal Garden View'),
(2, 1, 'JP-102', 'Deluxe Room', 1, 2, 'Twin Beds', 480, 18500.00, 'Available', 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=85', 'Warm tones with twin beds, Italian marble bath with soaking tub, and high-speed Wi-Fi.', 'Palace Courtyard View'),
(3, 1, 'JP-201', 'Premium Room', 2, 2, 'King Bed', 560, 24000.00, 'Occupied', 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1200&q=85', 'Expanded luxury with a private sit-out balcony overlooking the illuminated fountain courtyards.', 'Fountain & Pool View'),
(4, 1, 'JP-202', 'Executive Room', 2, 3, 'King Bed + Daybed', 650, 32000.00, 'Available', 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=85', 'Spacious executive sanctuary with dedicated work bureau, lounge seating, and complimentary evening canapes.', 'Heritage Garden View'),
(5, 1, 'JP-301', 'Luxury Suite', 3, 3, 'King Bed', 950, 48000.00, 'Reserved', 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1200&q=85', 'Opulent master bedroom, separate dining salon, Jacuzzi bath, and dedicated 24-hour butler service.', 'Panoramic Palace Skyline'),
(6, 1, 'JP-302', 'Royal Suite', 3, 4, 'Emperor Bed', 1600, 85000.00, 'Available', 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1200&q=85', 'The pinnacle of Jaipur aristocracy: gold-leaf ceilings, private terrace plunge pool, dining room for six, and private lift access.', '360-Degree Royal Estate View'),

-- Goa Rooms
(7, 2, 'GA-101', 'Deluxe Room', 1, 2, 'King Bed', 520, 22000.00, 'Available', 'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=1200&q=85', 'Airy coastal decor with teakwood finishes, open-plan marble bath, and private garden patio.', 'Tropical Garden View'),
(8, 2, 'GA-102', 'Deluxe Room', 1, 2, 'Twin Beds', 520, 22000.00, 'Available', 'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?auto=format&fit=crop&w=1200&q=85', 'Contemporary coastal living with soft linen tones, espresso machine, and rain shower.', 'Lush Lawn View'),
(9, 2, 'GA-201', 'Premium Room', 2, 2, 'King Bed', 600, 29000.00, 'Occupied', 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=85', 'Direct ocean breeze, expansive glass doors opening to private sea-view deck.', 'Arabian Sea Front View'),
(10, 2, 'GA-202', 'Executive Room', 2, 3, 'King Bed', 700, 38000.00, 'Available', 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1200&q=85', 'Designed for coastal indulgence with private sun lounger balcony and bespoke bar cabinet.', 'Oceanfront & Sunset Deck'),
(11, 2, 'GA-301', 'Luxury Suite', 3, 3, 'King Bed', 1100, 56000.00, 'Available', 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=85', 'Stunning corner suite with 180-degree ocean views, freestanding bathtub overlooking the sea, and champagne bar.', 'Full Ocean Sunset View'),
(12, 2, 'GA-302', 'Royal Suite', 3, 4, 'Emperor Bed', 1850, 95000.00, 'Available', 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1200&q=85', 'Private beach bungalow with infinity plunge pool, personal chef on request, and private cabana.', 'Direct Beachfront & Private Deck'),

-- Udaipur Rooms
(13, 3, 'UD-101', 'Deluxe Room', 1, 2, 'King Bed', 500, 28000.00, 'Available', 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=85', 'Traditional Mewari inlays with mother-of-pearl decor and window seats overlooking Lake Pichola.', 'Lake Pichola Water View'),
(14, 3, 'UD-102', 'Premium Room', 1, 2, 'King Bed', 620, 36000.00, 'Reserved', 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1200&q=85', 'Archways carved from Rajasthani marble, private balcony hovering directly above the gentle lake ripples.', 'Lake Pichola & Jag Mandir View'),
(15, 3, 'UD-201', 'Premium Room', 2, 2, 'Twin Beds', 620, 36000.00, 'Available', 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=85', 'Twin bedroom with heritage tapestries, marble ensuite with deep soaking tub, and lake vistas.', 'Lake & City Palace View'),
(16, 3, 'UD-202', 'Executive Room', 2, 3, 'King Bed', 750, 48000.00, 'Available', 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1200&q=85', 'Lavish executive room with separate drawing alcove, silver-embossed mirrors, and afternoon high tea service.', 'Aravalli Hills & Lake View'),
(17, 3, 'UD-301', 'Luxury Suite', 3, 3, 'King Bed', 1200, 72000.00, 'Occupied', 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1200&q=85', 'Heritage living salon, brass telescope for starry nights over the lake, and round-the-clock royal butler.', 'Lake Pichola Master View'),
(18, 3, 'UD-302', 'Royal Suite', 3, 4, 'Emperor Bed', 2100, 125000.00, 'Available', 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1200&q=85', 'The Maharana Presidential Suite: hand-painted frescoes, private heated terrace pool, private dining pavilion, and royal yacht transfers.', 'Panoramic Pichola & Palace Fortress'),

-- Lucknow Rooms
(19, 4, 'LK-101', 'Deluxe Room', 1, 2, 'King Bed', 460, 14500.00, 'Available', 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=85', 'Chikankari-inspired wall motifs, plush velvet headboards, and serene courtyard perspectives.', 'Gomti River Garden View'),
(20, 4, 'LK-102', 'Deluxe Room', 1, 2, 'Twin Beds', 460, 14500.00, 'Available', 'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?auto=format&fit=crop&w=1200&q=85', 'Refined city retreat with dual workstation, smart control panel, and premium sound insulation.', 'City Skyline View'),
(21, 4, 'LK-201', 'Premium Room', 2, 2, 'King Bed', 540, 19500.00, 'Available', 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1200&q=85', 'High ceilings, crystal chandelier accents, and expansive glass bay windows looking across the riverfront.', 'Gomti Riverfront View'),
(22, 4, 'LK-202', 'Executive Room', 2, 3, 'King Bed', 680, 26000.00, 'Available', 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=85', 'Executive privilege suite with meeting table for four, espresso bar, and airport chauffeur service.', 'Riverfront & Executive Park'),
(23, 4, 'LK-301', 'Luxury Suite', 3, 3, 'King Bed', 980, 42000.00, 'Available', 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1200&q=85', 'Awadhi elegance with separate living lounge, walk-in dressing wardrobe, and marble bathroom with Jacuzzi.', 'Panoramic River Promenade'),
(24, 4, 'LK-302', 'Royal Suite', 3, 4, 'Emperor Bed', 1500, 75000.00, 'Available', 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1200&q=85', 'The Nawab Wajid Ali Shah Suite: antique silver tea sets, royal diwan seating, private library, and personal majordomo.', 'Grand 360 Riverfront View'),
(25, 1, 'JP-103', 'Deluxe Room', 1, 2, 'King Bed', 480, 18500.00, 'Maintenance', 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=85', 'Undergoing annual bespoke polishing of heritage teak woodwork and silk tapestries.', 'Courtyard Garden View');

-- F. ROOM AMENITIES
INSERT INTO `room_amenities` (`room_id`, `amenity_name`, `icon_class`) VALUES
(1, 'High-Speed Wi-Fi 6', 'fa-solid fa-wifi'),
(1, '55" Ultra HD Smart TV', 'fa-solid fa-tv'),
(1, 'Italian Marble Bath & Rain Shower', 'fa-solid fa-bath'),
(1, '24-Hour In-Room Dining', 'fa-solid fa-bell-concierge'),
(1, 'Nespresso Gourmet Coffee Bar', 'fa-solid fa-mug-hot'),
(1, 'In-room Digital Safe', 'fa-solid fa-shield-halved'),
(3, 'High-Speed Wi-Fi 6', 'fa-solid fa-wifi'),
(3, 'Private Balcony', 'fa-solid fa-umbrella-beach'),
(3, 'Forest Essentials Luxury Toiletries', 'fa-solid fa-spa'),
(3, '24/7 Dedicated Butler Call', 'fa-solid fa-user-tie'),
(5, 'Private Jacuzzi & Bathrobe Suite', 'fa-solid fa-hot-tub-person'),
(5, 'Complimentary Airport Chauffeur', 'fa-solid fa-car-side'),
(5, 'Evening High Tea & Cocktails', 'fa-solid fa-wine-glass'),
(6, 'Private Terrace Plunge Pool', 'fa-solid fa-water-ladder'),
(6, '24/7 Dedicated Royal Majordomo', 'fa-solid fa-crown'),
(6, 'Bespoke In-Suite Private Dining', 'fa-solid fa-utensils'),
(9, 'Direct Ocean View Balcony', 'fa-solid fa-water'),
(9, 'Espresso Machine & Minibar', 'fa-solid fa-martini-glass-citrus'),
(11, 'Freestanding Sea-View Bathtub', 'fa-solid fa-bath'),
(11, 'Sunset Champagne Service', 'fa-solid fa-champagne-glasses'),
(12, 'Private Oceanfront Infinity Pool', 'fa-solid fa-water-ladder'),
(12, 'Personal Yacht Charter Access', 'fa-solid fa-ship'),
(14, 'Balcony over Lake Pichola', 'fa-solid fa-water'),
(14, 'Heritage Mother-of-Pearl Inlays', 'fa-solid fa-gem'),
(17, 'Panoramic Lake Balcony', 'fa-solid fa-mountain-sun'),
(17, 'Royal Heritage Silver Service', 'fa-solid fa-crown'),
(18, 'Private Heated Lakefront Pool', 'fa-solid fa-water-ladder'),
(18, 'Private Lake Boat Jetty Transfers', 'fa-solid fa-sailboat'),
(23, 'Awadhi Diwan Lounge Salon', 'fa-solid fa-couch'),
(24, 'Antique Silver Tea & Majordomo Service', 'fa-solid fa-crown');

-- G. 12 HOTEL SERVICES
INSERT INTO `services` (`service_id`, `name`, `category`, `price`, `unit`, `description`, `icon_class`, `status`) VALUES
(1, 'Royal Gourmet Breakfast Buffet', 'Dining', 1850.00, 'Per Guest / Per Day', 'Elaborate sunrise spread featuring international delicacies, Awadhi kebabs, and farm-fresh bakery items.', 'fa-solid fa-utensils', 'Available'),
(2, 'Luxury Airport Chauffeur Transfer', 'Transport', 3500.00, 'Per Vehicle / One Way', 'Private airport pickup and drop-off in a luxury Mercedes/BMW sedan with cold towels and refreshments.', 'fa-solid fa-car-rear', 'Available'),
(3, 'Ayurvedic Abhyanga Spa & Therapy', 'Wellness & Spa', 5500.00, 'Per Person / 90 Mins', 'Traditional full-body synchronized herbal massage using warm therapeutic oils for total rejuvenation.', 'fa-solid fa-spa', 'Available'),
(4, 'Candlelight Royal Dining by the Lake', 'Dining', 8500.00, 'Per Couple', 'Exclusive 5-course curated degustation menu with premium beverages in a private poolside or lake cabana.', 'fa-solid fa-champagne-glasses', 'Available'),
(5, 'Express Garment Laundry & Pressing', 'Housekeeping', 1200.00, 'Per Laundry Bag', 'Same-day luxury laundry, delicate fabric dry cleaning, and hand-pressed garment service.', 'fa-solid fa-shirt', 'Available'),
(6, 'Rollaway Luxury Extra Bed', 'Special', 4000.00, 'Per Night', 'Premium orthopedic plush mattress with 500-thread-count Egyptian cotton linens and pillow menu.', 'fa-solid fa-bed', 'Available'),
(7, 'Private Heritage Walking Tour', 'Recreation', 2500.00, 'Per Group (up to 4)', 'Guided private historical exploration with palace historians uncovering royal architecture and legends.', 'fa-solid fa-landmark', 'Available'),
(8, 'Sunset Champagne Cruise', 'Recreation', 6500.00, 'Per Couple', 'Private motorboat / yacht cruise across the lake with chilled bubbly, fresh canapes, and live flute music.', 'fa-solid fa-sailboat', 'Available'),
(9, 'Traditional Indian High Tea', 'Dining', 1950.00, 'Per Couple', 'Afternoon royal tea service with artisanal teas, Darjeeling blends, silver tiers of savoury snacks and pastries.', 'fa-solid fa-mug-saucer', 'Available'),
(10, 'Couples Imperial Hammam Ritual', 'Wellness & Spa', 9500.00, 'Per Couple / 120 Mins', 'Exotic Turkish-style hammam bath with mineral mud scrub, aromatic steam, and customized massage.', 'fa-solid fa-hot-tub-person', 'Available'),
(11, 'Private Yoga & Meditation Session', 'Wellness & Spa', 2000.00, 'Per Session / 60 Mins', 'One-on-one session with our resident yogic master at the sunrise pavilion.', 'fa-solid fa-peace', 'Available'),
(12, 'Royal Chef Masterclass', 'Recreation', 4500.00, 'Per Person', 'Interactive cooking session with the Executive Chef mastering royal Mughlai or coastal spice recipes.', 'fa-solid fa-hat-chef', 'Available');

-- H. 15 BOOKINGS
INSERT INTO `bookings` (`booking_id`, `booking_ref`, `customer_id`, `hotel_id`, `room_id`, `check_in_date`, `check_out_date`, `total_guests`, `num_rooms`, `promo_code`, `discount_amount`, `room_charges`, `service_charges`, `tax_amount`, `total_amount`, `payment_status`, `booking_status`, `special_requests`, `created_at`) VALUES
(1, 'RP-2026-0801', 1, 1, 1, '2026-08-20', '2026-08-23', 2, 1, 'WELCOME10', 5550.00, 55500.00, 5350.00, 9954.00, 65254.00, 'Paid', 'Confirmed', 'High floor preferred. Honeymoon anniversary setup requested.', '2026-08-10 10:30:00'),
(2, 'RP-2026-0802', 2, 2, 7, '2026-08-22', '2026-08-26', 2, 1, NULL, 0.00, 88000.00, 7000.00, 17100.00, 112100.00, 'Paid', 'Confirmed', 'Late check-in at 8:00 PM. Airport transfer required.', '2026-08-11 14:15:00'),
(3, 'RP-2026-0803', 3, 3, 14, '2026-08-18', '2026-08-21', 2, 1, 'ROMAINDULGE', 10800.00, 108000.00, 14000.00, 19980.00, 131180.00, 'Paid', 'Checked-In', 'Vegetarian breakfast options and non-feather pillows.', '2026-08-05 11:20:00'),
(4, 'RP-2026-0804', 4, 1, 3, '2026-08-17', '2026-08-19', 2, 1, NULL, 0.00, 48000.00, 3700.00, 9306.00, 61006.00, 'Paid', 'Checked-In', 'Quiet room facing the inner Mughal gardens.', '2026-08-12 09:45:00'),
(5, 'RP-2026-0805', 5, 4, 19, '2026-08-25', '2026-08-28', 2, 1, 'WEEKENDROYAL', 4350.00, 43500.00, 1850.00, 7380.00, 48380.00, 'Pending', 'Confirmed', 'Early check-in request if room is available.', '2026-08-14 16:00:00'),
(6, 'RP-2026-0806', 6, 2, 9, '2026-08-18', '2026-08-22', 2, 1, NULL, 0.00, 116000.00, 12000.00, 23040.00, 151040.00, 'Paid', 'Checked-In', 'Celebrating wedding anniversary. Champagne on arrival.', '2026-08-08 17:30:00'),
(7, 'RP-2026-0807', 7, 3, 17, '2026-08-16', '2026-08-19', 2, 1, NULL, 0.00, 216000.00, 15000.00, 41580.00, 272580.00, 'Paid', 'Checked-In', 'Private boat transfer across Lake Pichola.', '2026-08-02 12:10:00'),
(8, 'RP-2026-0808', 8, 4, 21, '2026-08-28', '2026-08-30', 2, 1, NULL, 0.00, 39000.00, 2400.00, 7452.00, 48852.00, 'Paid', 'Confirmed', 'Twin bed setup requested.', '2026-08-15 10:05:00'),
(9, 'RP-2026-0701', 9, 1, 4, '2026-07-10', '2026-07-13', 2, 1, 'SUMMER15', 14400.00, 96000.00, 8500.00, 16218.00, 106318.00, 'Paid', 'Completed', 'Everything was seamless. Thank you for the royal stay.', '2026-07-01 08:30:00'),
(10, 'RP-2026-0702', 10, 2, 11, '2026-07-15', '2026-07-18', 2, 1, NULL, 0.00, 168000.00, 19000.00, 33660.00, 220660.00, 'Paid', 'Completed', 'Extra towels and ocean view balcony setup.', '2026-07-05 15:40:00'),
(11, 'RP-2026-0703', 11, 3, 13, '2026-07-20', '2026-07-22', 2, 1, NULL, 0.00, 56000.00, 5500.00, 11070.00, 72570.00, 'Paid', 'Completed', 'Sunset yoga session booked in advance.', '2026-07-12 11:15:00'),
(12, 'RP-2026-0704', 12, 4, 23, '2026-07-25', '2026-07-27', 2, 1, NULL, 0.00, 84000.00, 9500.00, 16830.00, 110330.00, 'Paid', 'Completed', 'Awadhi royal dinner reservations arranged.', '2026-07-18 19:20:00'),
(13, 'RP-2026-0809', 13, 1, 6, '2026-08-30', '2026-09-02', 4, 1, 'ROYALTY20', 51000.00, 255000.00, 25000.00, 41220.00, 270220.00, 'Paid', 'Confirmed', 'Family holiday with two children. Extra child bed and stroller.', '2026-08-16 13:00:00'),
(14, 'RP-2026-0810', 14, 2, 8, '2026-08-01', '2026-08-03', 2, 1, NULL, 0.00, 44000.00, 3500.00, 8550.00, 56050.00, 'Refunded', 'Cancelled', 'Cancelled due to personal emergency. Refund processed.', '2026-07-28 10:00:00'),
(15, 'RP-2026-0811', 15, 3, 16, '2026-08-24', '2026-08-27', 3, 1, NULL, 0.00, 144000.00, 11000.00, 27900.00, 182900.00, 'Paid', 'Confirmed', 'Business executive stay. High-speed LAN and printing.', '2026-08-15 18:45:00');

-- I. 15 PAYMENTS
INSERT INTO `payments` (`payment_id`, `booking_id`, `customer_id`, `amount`, `payment_method`, `transaction_id`, `payment_date`, `status`) VALUES
(1, 1, 1, 65254.00, 'UPI', 'UPI/20260810/8932478190', '2026-08-10 10:32:15', 'Paid'),
(2, 2, 2, 112100.00, 'Credit Card', 'CC_HDFC_89437291823', '2026-08-11 14:18:22', 'Paid'),
(3, 3, 3, 131180.00, 'Net Banking', 'NET_ICICI_7483920192', '2026-08-05 11:24:00', 'Paid'),
(4, 4, 4, 61006.00, 'UPI', 'UPI/20260812/1029384756', '2026-08-12 09:48:10', 'Paid'),
(5, 5, 5, 48380.00, 'Cash at Hotel', 'CASH_DESK_LK00582', '2026-08-14 16:00:00', 'Pending'),
(6, 6, 6, 151040.00, 'Credit Card', 'CC_AMEX_99887766554', '2026-08-08 17:34:50', 'Paid'),
(7, 7, 7, 272580.00, 'Net Banking', 'NET_SBIN_4455667788', '2026-08-02 12:15:30', 'Paid'),
(8, 8, 8, 48852.00, 'Debit Card', 'DC_AXIS_1122334455', '2026-08-15 10:08:44', 'Paid'),
(9, 9, 9, 106318.00, 'Credit Card', 'CC_HDFC_3344556677', '2026-07-01 08:35:12', 'Paid'),
(10, 10, 10, 220660.00, 'Credit Card', 'CC_CITI_5566778899', '2026-07-05 15:45:00', 'Paid'),
(11, 11, 11, 72570.00, 'UPI', 'UPI/20260712/9900112233', '2026-07-12 11:18:25', 'Paid'),
(12, 12, 12, 110330.00, 'Net Banking', 'NET_KOTAK_8899001122', '2026-07-18 19:24:10', 'Paid'),
(13, 13, 13, 270220.00, 'Credit Card', 'CC_AMEX_1234509876', '2026-08-16 13:05:40', 'Paid'),
(14, 14, 14, 56050.00, 'UPI', 'UPI/20260728/6677889900', '2026-07-28 10:04:12', 'Refunded'),
(15, 15, 15, 182900.00, 'Credit Card', 'CC_HDFC_9911223344', '2026-08-15 18:49:30', 'Paid');

-- J. BOOKING SERVICES
INSERT INTO `booking_services` (`booking_id`, `service_id`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 2, 1850.00, 3700.00),
(1, 2, 1, 3500.00, 3500.00),
(2, 2, 2, 3500.00, 7000.00),
(3, 4, 1, 8500.00, 8500.00),
(3, 3, 1, 5500.00, 5500.00),
(4, 1, 2, 1850.00, 3700.00),
(6, 4, 1, 8500.00, 8500.00),
(6, 2, 1, 3500.00, 3500.00),
(7, 8, 1, 6500.00, 6500.00),
(7, 4, 1, 8500.00, 8500.00),
(13, 6, 1, 4000.00, 4000.00),
(13, 1, 4, 1850.00, 7400.00),
(13, 10, 1, 9500.00, 9500.00);

-- K. 12 STAFF MEMBERS
INSERT INTO `staff` (`staff_id`, `hotel_id`, `name`, `department`, `position`, `phone`, `email`, `joining_date`, `salary`, `status`) VALUES
(1, 1, 'Bhawani Singh', 'Management', 'Resident Palace Manager', '+91 98290 88771', 'bhawani.singh@romapalace.com', '2018-04-15', 180000.00, 'Active'),
(2, 1, 'Manish Chouhan', 'Reception', 'Chief Concierge & Front Office Head', '+91 98290 88772', 'manish.c@romapalace.com', '2020-02-10', 75000.00, 'Active'),
(3, 1, 'Chef Sanjeev Sisodia', 'Restaurant', 'Executive Chef - Rajputana Cuisine', '+91 98290 88773', 'chef.sanjeev@romapalace.com', '2017-09-01', 160000.00, 'Active'),
(4, 1, 'Sunita Rathore', 'Housekeeping', 'Executive Housekeeper', '+91 98290 88774', 'sunita.r@romapalace.com', '2019-06-20', 65000.00, 'Active'),
(5, 2, 'Antonio Fernandes', 'Management', 'Resort General Manager', '+91 832 998871', 'antonio.f@romapalace.com', '2016-11-05', 190000.00, 'Active'),
(6, 2, 'Chef Marco D’Souza', 'Restaurant', 'Head of Ocean Grill & Mediterranean', '+91 832 998872', 'marco.d@romapalace.com', '2019-01-15', 150000.00, 'Active'),
(7, 2, 'Pooja Naik', 'Reception', 'Front Desk Supervisor', '+91 832 998873', 'pooja.n@romapalace.com', '2021-07-01', 55000.00, 'Active'),
(8, 3, 'Gajendra Singh Mewada', 'Management', 'Lake Palace Operations Director', '+91 294 991122', 'gajendra.s@romapalace.com', '2015-03-01', 210000.00, 'Active'),
(9, 3, 'Karan Rawat', 'Security', 'Chief of Security & Maritime Safety', '+91 294 991123', 'karan.r@romapalace.com', '2018-08-10', 60000.00, 'Active'),
(10, 3, 'Leela Sharma', 'Wellness & Spa', 'Lead Ayurvedic Spa Practitioner', '+91 294 991124', 'leela.s@romapalace.com', '2020-05-15', 58000.00, 'Active'),
(11, 4, 'Mirza Asad Beg', 'Restaurant', 'Master Chef of Royal Dastarkhwan', '+91 522 993344', 'mirza.asad@romapalace.com', '2016-08-01', 170000.00, 'Active'),
(12, 4, 'Alok Tandon', 'Maintenance', 'Chief Engineer & Facility Head', '+91 522 993345', 'alok.t@romapalace.com', '2019-10-12', 70000.00, 'Active');

-- L. 4 RESTAURANTS
INSERT INTO `restaurants` (`restaurant_id`, `hotel_id`, `name`, `slug`, `cuisine`, `opening_hours`, `location_desc`, `dress_code`, `image_url`, `description`) VALUES
(1, 1, 'The Roma Table', 'the-roma-table', 'Indian Royal & Contemporary Global', '07:00 AM – 11:30 PM', 'Grand Central Courtyard, Jaipur', 'Formal / Smart Casual', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=85', 'An ode to aristocratic gastronomy: heritage Rajasthani Thalis served alongside progressive European classics under carved vaulted ceilings.'),
(2, 2, 'Palazzo Café & Ocean Grill', 'palazzo-cafe', 'Coastal Seafood & All-Day Dining', '06:30 AM – 12:00 Midnight', 'Beachfront Deck, Goa', 'Resort Chic / Casual Elegance', 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=85', 'Al-fresco oceanfront dining with fresh Arabian Sea catch, wood-fired artisanal pizzas, and hand-crafted botanical cocktails.'),
(3, 4, 'Spice Route — Royal Dastarkhwan', 'spice-route', 'Authentic Awadhi & Mughlai Heritage', '12:30 PM – 03:30 PM & 07:00 PM – 11:30 PM', 'Nawabi Pavilion, Lucknow', 'Traditional Elegant / Formal', 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=85', 'Preserving centuries-old dum-pukht techniques, succulent Galawati kebabs, and aromatic saffron biryanis perfected by Nawab court culinary lineages.'),
(4, 3, 'Azure Lounge & Sunset Terrace', 'azure-lounge', 'Signature Beverages, Wine & Patisserie', '11:00 AM – 01:00 AM', 'Lake Pichola Terrace, Udaipur', 'Evening Chic', 'https://images.unsplash.com/photo-1572116469696-31de0f17cc34?auto=format&fit=crop&w=1200&q=85', 'A refined rooftop sanctuary for vintage champagnes, rare single malts, artisanal tapas, and sunset serenades overlooking the illuminated waters.');

-- M. 30 GOURMET MENU ITEMS
INSERT INTO `menu_items` (`item_id`, `restaurant_id`, `name`, `category`, `price`, `dietary_flag`, `description`, `is_chef_special`, `is_available`) VALUES
-- The Roma Table (Jaipur)
(1, 1, 'Royal Rajputana Laal Maas', 'Main Course', 1450.00, 'Non-Veg', 'Tender Mathania-chilli braised lamb slow-cooked in copper vessels with smoked cloves.', 1, 1),
(2, 1, 'Shahi Paneer Tikka Angaar', 'Appetizers', 950.00, 'Veg', 'Housemade cottage cheese cubes stuffed with saffron mawa and char-grilled in tandoor.', 0, 1),
(3, 1, 'Dal Baati Churma Rajwadi', 'Royal Thali', 1650.00, 'Veg', 'The quintessential Rajasthani feast: five-lentil Panchmel dal, ghee-soaked baatis, and almond churma.', 1, 1),
(4, 1, 'Kesar Pistachio Kulfi Falooda', 'Desserts', 550.00, 'Veg', 'Traditional dense saffron ice cream served with rose syrup, basil seeds, and hand-spun falooda.', 0, 1),
(5, 1, 'Smoked Truffle Wild Mushroom Soup', 'Appetizers', 650.00, 'Veg', 'Forest mushrooms with aged Parmesan foam and roasted hazelnut crunch.', 0, 1),
(6, 1, 'Pan-Seared Chilean Sea Bass', 'Main Course', 2250.00, 'Non-Veg', 'Served over saffron risotto with lemon butter emulsion and asparagus.', 1, 1),
(7, 1, 'The Roma Imperial Thali (Gold Edition)', 'Royal Thali', 2850.00, 'Non-Veg', 'A 14-course royal banquet served on handcrafted silver tableware.', 1, 1),
(8, 1, 'Darjeeling First Flush Royal Tea', 'Beverages & Wine', 450.00, 'Vegan', 'Handpicked organic single-estate tea brewed at table with wild mountain honey.', 0, 1),

-- Palazzo Café (Goa)
(9, 2, 'Recheado King Prawns Grill', 'Appetizers', 1350.00, 'Non-Veg', 'Jumbo Arabian Sea prawns stuffed with spicy-tangy homemade Goan red masala.', 1, 1),
(10, 2, 'Goan Crab Xec Xec', 'Main Course', 1750.00, 'Non-Veg', 'Mud crab simmered in a roasted coconut, coriander seed, and Kashmiri spice gravy.', 1, 1),
(11, 2, 'Woodfired Burrata & Heirloom Pizza', 'Main Course', 1150.00, 'Veg', 'Creamy Italian burrata, San Marzano pomodoro, fresh basil, and extra virgin olive oil.', 0, 1),
(12, 2, 'Traditional Goan Bebinca', 'Desserts', 500.00, 'Veg', 'Seven-layered coconut milk pudding baked slowly with ghee and nutmeg.', 0, 1),
(13, 2, 'Avocado & Quinoa Citrus Salad', 'Appetizers', 750.00, 'Vegan', 'Hass avocado, organic Peruvian quinoa, pomegranate pearls, and yuzu vinaigrette.', 0, 1),
(14, 2, 'Lobster Thermidor on the Grill', 'Main Course', 3200.00, 'Non-Veg', 'Whole butterflied butter lobster gratinated with Dijon mustard, gruyere, and cognac cream.', 1, 1),
(15, 2, 'Goan Kokum Passionfruit Cooler', 'Beverages & Wine', 420.00, 'Vegan', 'Indigenous kokum extract infused with fresh passionfruit, mint, and sea salt.', 0, 1),

-- Spice Route (Lucknow)
(16, 3, 'Melt-in-Mouth Galawati Kebab', 'Appetizers', 1250.00, 'Non-Veg', 'Legendary Awadhi minced lamb patties infused with 160 secret spices and rose petal water.', 1, 1),
(17, 3, 'Kakori Kebab Nawabi', 'Appetizers', 1350.00, 'Non-Veg', 'Ultra-refined skewered mutton kebab prepared with saffron, raw papaya, and khoya.', 1, 1),
(18, 3, 'Awadhi Dum Murgh Biryani', 'Main Course', 1450.00, 'Non-Veg', 'Long-grain Basmati and spring chicken sealed with purdah dough in clay handis.', 1, 1),
(19, 3, 'Paneer Nazakat Pasanda', 'Main Course', 1050.00, 'Veg', 'Layered paneer escallops filled with dry fruits in a silky cashew-saffron gravy.', 0, 1),
(20, 3, 'Nalli Nihari Gosht with Sheermal', 'Main Course', 1650.00, 'Non-Veg', 'Slow-cooked lamb shanks braised overnight, served alongside saffron-scented Sheermal.', 1, 1),
(21, 3, 'Shahi Tukda with Gold Vark', 'Desserts', 580.00, 'Veg', 'Crisp brioche soaked in cardamom syrup, topped with thick rabri and 24K edible gold.', 1, 1),
(22, 3, 'Lucknowi Nimish / Makhan Malai', 'Desserts', 480.00, 'Veg', 'Feather-light whipped milk foam scented with saffron, dew drops, and pistachio slivers.', 1, 1),

-- Azure Lounge (Udaipur)
(23, 4, 'Truffle Edamame Dumplings', 'Appetizers', 850.00, 'Vegan', 'Translucent steamed crystal dumplings infused with black summer truffle essence.', 0, 1),
(24, 4, 'Charcuterie & Artisanal Cheese Board', 'Appetizers', 1850.00, 'Non-Veg', 'Aged Manchego, Truffle Gouda, Parma Ham, fig preserve, and lavash crisps.', 0, 1),
(25, 4, 'The Royal Maharaja Cocktail', 'Beverages & Wine', 1250.00, 'Non-Veg', 'Single malt scotch, saffron-infused vermouth, orange bitters, smoked with applewood.', 1, 1),
(26, 4, 'Moët & Chandon Brut Impérial (Glass)', 'Beverages & Wine', 2400.00, 'Vegan', 'Crisp French champagne with notes of green apple, citrus, and toasted brioche.', 0, 1),
(27, 4, 'Valrhona Dark Chocolate Fondant', 'Desserts', 680.00, 'Veg', 'Warm molten 70% French chocolate cake with housemade Madagascar vanilla bean gelato.', 1, 1),
(28, 4, 'Lakeview High Tea Tower', 'Breakfast', 2200.00, 'Veg', 'Three-tier stand with smoked salmon blinis, cucumber finger sandwiches, scones, and macarons.', 0, 1),
(29, 4, 'Spiced Saffron Bellini', 'Beverages & Wine', 950.00, 'Vegan', 'Italian Prosecco swirled with peach puree and Kashmiri saffron threads.', 0, 1),
(30, 4, 'Masala Chai Gelato with Almond Tuile', 'Desserts', 480.00, 'Veg', 'Aromatic spiced tea gelato served with crispy honey almond crisp.', 0, 1);

-- N. 8 SIGNATURE OFFERS
INSERT INTO `offers` (`offer_id`, `hotel_id`, `code`, `title`, `tag`, `discount_percent`, `flat_discount`, `description`, `benefits`, `validity_date`, `image_url`, `price_note`) VALUES
(1, NULL, 'WELCOME10', 'THE ROMA ESCAPE', 'Long Stay Special', 10, 0.00, 'Stay 3 or more nights across any Roma Palace and enjoy curated royal experiences with 10% privilege savings.', 'Daily Royal Breakfast Buffet, 20% Spa Privilege, Guaranteed Late Checkout till 4 PM, Airport Chauffeur', '2026-12-31', 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=85', 'Save 10% on stays of 3+ nights'),
(2, NULL, 'WEEKENDROYAL', 'WEEKEND ROYALTY', 'Weekend Getaway', 15, 0.00, 'Transform your Friday to Sunday into an unforgettable aristocratic getaway with gourmet meals included.', 'Lavish Champagne Breakfast, 4-Course Dinner on Saturday, Royal High Tea, High-speed Wi-Fi', '2026-11-30', 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=85', '15% Off + Dinner Included'),
(3, NULL, 'FAMILYJOURNEY', 'FAMILY JOURNEY', 'Family Holiday', 12, 0.00, 'Create timeless memories with your loved ones with complimentary extra beds and curated children’s activities.', 'Complimentary Extra Bed for Child, Junior Chef Masterclass, Heritage Treasure Hunt, 25% Off Connecting Rooms', '2026-10-31', 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=85', 'Kids Stay Free + 12% Off'),
(4, NULL, 'ROMAINDULGE', 'ROMA INDULGENCE', 'Suite Special', 20, 0.00, 'Reserve any Luxury or Royal Suite and receive complimentary airport transfers and customized couple spa therapy.', 'Round-trip Airport Chauffeur, 90-Min Couple Ayurvedic Spa, Daily In-Suite Breakfast, 24/7 Majordomo Service', '2026-12-31', 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1200&q=85', '20% Off on All Suites'),
(5, 1, 'JAIPURROYAL', 'JAIPUR HERITAGE TRAIL', 'Destination Exclusive', 0, 5000.00, 'Experience the Pink City like royalty with private palace tour passes and vintage car pickup.', 'Private Guided City Palace Tour, Vintage Car Transfer, Evening Peacock Courtyard Cocktails', '2026-10-15', 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=85', 'Flat ₹5,000 Off Package'),
(6, 2, 'GOASUNSET', 'GOA COASTAL BLISS', 'Beach Retreat', 15, 0.00, 'Four nights of seaside bliss with daily sunset cocktail cruises and beach cabana privileges.', 'Daily Sunset Cocktails, Private Beach Cabana, Water Sports Credits worth ₹3,000, Seafood BBQ', '2026-11-15', 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=85', '15% Off Seaside Stays'),
(7, 3, 'UDAIPURROMANCE', 'UDAIPUR LAKE ROMANCE', 'Honeymoon Special', 18, 0.00, 'The ultimate romantic getaway with candlelit dinner on Lake Pichola and private boat tours.', 'Lake Pichola Candlelight Dinner, Sunset Boat Cruise, Floral Room Setup, Couple Massage', '2026-12-20', 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1200&q=85', '18% Off Couple Stays'),
(8, 4, 'AWADHICULINARY', 'AWADHI GASTRONOMY TOUR', 'Culinary Focus', 10, 0.00, 'A gourmet trail through Lucknow’s royal culinary history guided by Master Chef Mirza Asad Beg.', 'Private Dastarkhwan Tasting, Cooking Masterclass with Head Chef, Recipe Souvenir Book', '2026-10-31', 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=85', '10% Off + Cooking Class');

-- O. 10 EXPERIENCES
INSERT INTO `experiences` (`experience_id`, `hotel_id`, `title`, `category`, `duration`, `timing`, `price_per_person`, `image_url`, `short_desc`, `full_desc`) VALUES
(1, 1, 'Heritage Palace & Rampart Walk', 'Royal Heritage', '2.5 Hours', '07:30 AM & 04:30 PM', 2500.00, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=85', 'Walk through private palace corridors, battlements, and Mughal gardens with our resident historian.', 'Discover the centuries-old history, hidden subterranean chambers, and Rajputana royal architecture under the guidance of our chief palace curator.'),
(2, 3, 'Private Sunset Boat Cruise on Pichola', 'Lake & Romance', '1.5 Hours', '05:00 PM – 06:30 PM', 4500.00, 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=85', 'Glide across the shimmering waters of Lake Pichola with champagne and live santoor melodies.', 'Watch the sun sink behind the Aravalli mountains while sipping French champagne on our private teakwood royal barge.'),
(3, 4, 'Royal Awadhi Dastarkhwan Masterclass', 'Culinary Art', '3.0 Hours', '11:00 AM – 02:00 PM', 3800.00, 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=85', 'Learn the legendary secrets of Galawati kebabs and dum biryani from court culinary descendants.', 'An interactive culinary journey inside the palace kitchen where you learn to balance 160 herbs and spices.'),
(4, 2, 'Private Oceanfront Candlelight Dinner', 'Fine Dining', '3.0 Hours', '07:30 PM – 10:30 PM', 8500.00, 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=85', 'Five-course seafood degustation served in a private floral cabana right on the beach.', 'Dine under a canopy of stars with the gentle sound of the Arabian Sea waves and a private butler at your service.'),
(5, 1, 'Peacock Courtyard Cultural Evenings', 'Art & Culture', '2.0 Hours', '07:00 PM – 09:00 PM', 1800.00, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=85', 'Mesmerizing Rajasthani folk dances, fire performances, and live classical sitar recitals.', 'Immerse yourself in traditional folk artistry with Kalbelia dancers, puppet maestros, and royal refreshments.'),
(6, 3, 'Ayurvedic Sunrise Yoga & Pranayama', 'Wellness', '1.5 Hours', '06:30 AM – 08:00 AM', 1500.00, 'https://images.unsplash.com/photo-1545205597-3d9d02c29597?auto=format&fit=crop&w=1200&q=85', 'Realign your mind and body on the lakefront yoga pavilion with panoramic mountain vistas.', 'Conducted by our certified Himalayan yogic master, blending mindful asanas, breathwork, and sound bath meditation.'),
(7, 2, 'Dolphin Safari & Coastal Trail', 'Nature & Wildlife', '3.0 Hours', '06:30 AM – 09:30 AM', 3200.00, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=85', 'Early morning speedboat cruise spotting playful Indo-Pacific humpback dolphins.', 'Cruise through pristine coastal backwaters and headland cliffs with light refreshments and snorkeling opportunities.'),
(8, 4, 'Lucknowi Chikankari & Attar Trail', 'Crafts & Heritage', '4.0 Hours', '02:00 PM – 06:00 PM', 2800.00, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=85', 'Private excursion to centuries-old perfume distilleries and artisan embroidery ateliers in Old Lucknow.', 'Explore traditional attar-making (rose, mitti, shamama) and meet master craftspeople preserving delicate hand embroidery.'),
(9, 1, 'Royal Vintage Car City Tour', 'Royal Luxury', '2.5 Hours', '04:00 PM – 06:30 PM', 6000.00, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=85', 'Explore the historic gates of Jaipur in a restored 1948 Rolls Royce or vintage Cadillac.', 'A regal tour through Hawa Mahal, Jal Mahal, and the Pink City bazars with a liveried chauffeur.'),
(10, 3, 'Stargazing with Royal Astronomer', 'Evening Wonder', '1.5 Hours', '09:00 PM – 10:30 PM', 2000.00, 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=1200&q=85', 'Observe the cosmic wonders of Rajasthan’s unpolluted skies through our Celestron telescope.', 'An enchanting astronomy session on the palace rooftop with storytelling inspired by ancient Indian celestial navigation.');

-- P. 10 GUEST REVIEWS
INSERT INTO `reviews` (`review_id`, `customer_id`, `hotel_id`, `rating`, `review_title`, `comments`, `stay_date`, `is_approved`) VALUES
(1, 1, 1, 5, 'An Unmatched Epitome of Indian Royal Hospitality', 'Staying at The Roma Palace Jaipur felt like living inside a royal fairy tale. The staff anticipated our every wish, the Mughal gardens are breathtaking, and the Laal Maas at The Roma Table is truly out of this world.', 'August 2026', 1),
(2, 2, 2, 5, 'Serene Coastal Luxury at Its Absolute Finest', 'The oceanfront suite in Goa gave us stunning sunset views every single evening. The private beach access and personal butler made our wedding anniversary unforgettable.', 'August 2026', 1),
(3, 3, 3, 5, 'Magical Lakefront Oasis in Udaipur', 'The sunrise over Lake Pichola from our balcony was nothing short of divine. The boat transfer, the evening flute recitals, and the Ayurvedic spa treatments were top notch.', 'August 2026', 1),
(4, 4, 1, 5, 'Heritage Charm with World-Class Modern Comfort', 'Flawless blend of antique architecture and modern high-tech convenience. Everything from the check-in to the royal chariot welcome made us feel like royalty.', 'August 2026', 1),
(5, 5, 4, 5, 'The Nawabi Gastronomy & Hospitality are Legendary', 'The Galawati kebabs and Dum Biryani at Spice Route are perfection. The palace architecture in Lucknow along the Gomti river is grand and serene.', 'July 2026', 1),
(6, 6, 2, 5, 'Exceptional Service and Food', 'The Palazzo Cafe seafood grill is one of the best dining experiences in India. Impeccable hygiene, luxurious linens, and great cocktail craftsmanship.', 'August 2026', 1),
(7, 7, 3, 5, 'Breathtaking Views and Royal Service', 'The staff at Roma Palace Udaipur redefined hospitality for our family. The private boat ride and candlelight dinner were highlights of our entire trip.', 'August 2026', 1),
(8, 8, 4, 5, 'Aristocratic Elegance in Every Detail', 'From the crystal chandeliers to the fragrant jasmine courtyards, Roma Palace Lucknow is a masterpiece. Highly recommended for both business and leisure.', 'July 2026', 1),
(9, 9, 1, 5, 'A Benchmark for 5-Star Luxury Hotels', 'The suite was enormous and immaculate. The concierge arranged a fabulous vintage car tour of Jaipur. We will certainly return every winter.', 'July 2026', 1),
(10, 10, 2, 5, 'Unforgettable Family Vacation', 'The kids loved the infinity pool and junior chef class, while we enjoyed the sunset champagne cruise. Outstanding management and warmth.', 'July 2026', 1);
