-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `garage_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `garage_db`;

-- Users table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('admin','manager','mechanic','receptionist') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User tokens table (for Remember Me functionality)
CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customers table
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `notes` text,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Vehicles table
CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(4) NOT NULL,
  `license_plate` varchar(20) NOT NULL,
  `vin` varchar(17) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `mileage` int(11) DEFAULT NULL,
  `transmission` enum('automatic','manual') DEFAULT NULL,
  `fuel_type` enum('gasoline','diesel','electric','hybrid') DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Services table
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'in minutes',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Appointments table
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'assigned mechanic',
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('scheduled','confirmed','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `vehicle_id` (`vehicle_id`),
  KEY `service_id` (`service_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `appointments_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Suppliers table
CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `notes` text,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inventory table
CREATE TABLE `inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `part_number` varchar(50) DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `description` text,
  `quantity` int(11) NOT NULL DEFAULT '0',
  `unit` varchar(20) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `reorder_level` int(11) NOT NULL DEFAULT '5',
  `location` varchar(50) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Invoices table
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL COMMENT 'created by',
  `subtotal` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `due_amount` decimal(10,2) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('draft','unpaid','partially_paid','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `customer_id` (`customer_id`),
  KEY `vehicle_id` (`vehicle_id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`),
  CONSTRAINT `invoices_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Invoice items table
CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `item_type` enum('service','part') NOT NULL,
  `item_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payments table
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit_card','debit_card','check','bank_transfer','online') NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `notes` text,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inventory transactions table
CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_id` int(11) NOT NULL,
  `transaction_type` enum('purchase','sale','adjustment','return') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_id` int(11) DEFAULT NULL COMMENT 'invoice_id, purchase_id, etc.',
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'invoice, purchase, adjustment',
  `notes` text,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inventory_id` (`inventory_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`),
  CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Settings table
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data

-- Insert sample users
INSERT INTO `users` (`username`, `password`, `email`, `first_name`, `last_name`, `role`, `phone`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@garage.com', 'Admin', 'User', 'admin', '555-123-4567'),
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager@garage.com', 'Manager', 'User', 'manager', '555-234-5678'),
('mechanic1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mechanic1@garage.com', 'John', 'Smith', 'mechanic', '555-345-6789'),
('mechanic2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mechanic2@garage.com', 'Mike', 'Johnson', 'mechanic', '555-456-7890'),
('receptionist', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receptionist@garage.com', 'Sarah', 'Williams', 'receptionist', '555-567-8901');

-- Insert sample customers
INSERT INTO `customers` (`first_name`, `last_name`, `email`, `phone`, `address`, `city`, `state`, `zip_code`, `notes`, `password`, `created_at`, `updated_at`) VALUES
('John', 'Smith', 'john.smith@example.com', '555-111-2222', '123 Main St', 'Anytown', 'CA', '12345', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-03-22 00:00:00', '2025-03-22 00:00:00'),
('Sarah', 'Williams', 'sarah.williams@example.com', '555-222-3333', '456 Oak Ave', 'Somecity', 'NY', '67890', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-03-22 00:00:00', '2025-03-22 00:00:00'),
('Michael', 'Brown', 'michael.brown@example.com', '555-333-4444', '789 Pine Rd', 'Otherville', 'TX', '54321', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-03-22 00:00:00', '2025-03-22 00:00:00'),
('Emily', 'Davis', 'emily.davis@example.com', '555-444-5555', '321 Elm St', 'Somewhere', 'FL', '98765', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-03-22 00:00:00', '2025-03-22 00:00:00'),
('Robert', 'Johnson', 'robert.johnson@example.com', '555-555-6666', '654 Maple Dr', 'Nowhere', 'WA', '13579', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-03-22 00:00:00', '2025-03-22 00:00:00');

-- Insert sample vehicles
INSERT INTO `vehicles` (`customer_id`, `make`, `model`, `year`, `license_plate`, `vin`, `color`, `mileage`, `transmission`, `fuel_type`) VALUES
(1, 'Toyota', 'Camry', 2019, 'ABC123', '1HGCM82633A123456', 'Silver', 25000, 'automatic', 'gasoline'),
(2, 'Honda', 'Accord', 2020, 'DEF456', '1HGCV87463B654321', 'Blue', 15000, 'automatic', 'gasoline'),
(3, 'Ford', 'F-150', 2018, 'GHI789', '1FTEW1E53JFB12345', 'Black', 35000, 'automatic', 'gasoline'),
(4, 'Nissan', 'Altima', 2021, 'JKL012', '1N4BL4EV2KC123456', 'White', 10000, 'automatic', 'gasoline'),
(5, 'Chevrolet', 'Malibu', 2017, 'MNO345', '1G1ZD5ST7JF123456', 'Red', 45000, 'automatic', 'gasoline'),
(1, 'Hyundai', 'Sonata', 2020, 'PQR678', '5NPE34AF1GH123456', 'Gray', 20000, 'automatic', 'gasoline');

-- Insert sample services
INSERT INTO `services` (`name`, `description`, `price`, `duration`) VALUES
('Oil Change', 'Change oil and oil filter', 49.99, 30),
('Brake Service', 'Inspect and replace brake pads if needed', 149.99, 60),
('Tire Rotation', 'Rotate tires to ensure even wear', 29.99, 30),
('Engine Tune-Up', 'Comprehensive engine maintenance service', 199.99, 120),
('AC Service', 'Check and recharge AC system', 89.99, 60),
('Battery Replacement', 'Replace car battery', 129.99, 30),
('Wheel Alignment', 'Align wheels to manufacturer specifications', 79.99, 60),
('Transmission Service', 'Flush and replace transmission fluid', 149.99, 90);

-- Insert Sample FREE Services
INSERT INTO `services` (`name`, `description`, `price`, `duration`) VALUES
('فحص الإطارات مجانًا', 'فحص بصري سريع لضغط الإطارات وتآكلها', 0.00, 10),
('فحص مستوى السوائل مجانًا', 'فحص وتقرير عن مستويات السوائل الأساسية (غسيل الزجاج، تبريد)', 0.00, 15);

-- Insert sample suppliers
INSERT INTO `suppliers` (`name`, `contact_person`, `email`, `phone`, `address`, `city`, `state`, `zip_code`) VALUES
('AutoParts Inc.', 'David Johnson', 'david@autoparts.com', '555-111-0000', '100 Supply Rd', 'Partsville', 'CA', '12345'),
('Quality Parts Co.', 'Lisa Brown', 'lisa@qualityparts.com', '555-222-0000', '200 Vendor St', 'Supplyton', 'NY', '67890'),
('Premium Auto Supplies', 'Mark Wilson', 'mark@premiumauto.com', '555-333-0000', '300 Distributor Ave', 'Partsfield', 'TX', '54321');

-- Insert sample inventory items
INSERT INTO `inventory` (`name`, `part_number`, `category`, `description`, `quantity`, `unit`, `cost_price`, `selling_price`, `reorder_level`, `location`, `supplier_id`) VALUES
('Oil Filter', 'OF-1234', 'Filters', 'Standard oil filter for most vehicles', 50, 'piece', 5.99, 12.99, 10, 'Shelf A1', 1),
('Brake Pad Set', 'BP-5678', 'Brakes', 'Front brake pad set', 20, 'set', 25.99, 49.99, 5, 'Shelf B2', 1),
('Engine Oil 5W-30', 'EO-5W30', 'Fluids', '5W-30 synthetic engine oil', 100, 'quart', 4.99, 8.99, 20, 'Shelf C3', 2),
('Spark Plug', 'SP-9012', 'Ignition', 'Standard spark plug', 60, 'piece', 2.99, 6.99, 15, 'Shelf D4', 2),
('Wiper Blade', 'WB-3456', 'Exterior', '20-inch wiper blade', 30, 'piece', 7.99, 15.99, 10, 'Shelf E5', 3),
('Air Filter', 'AF-7890', 'Filters', 'Standard air filter', 40, 'piece', 8.99, 18.99, 10, 'Shelf A2', 1),
('Transmission Fluid', 'TF-1234', 'Fluids', 'Automatic transmission fluid', 50, 'quart', 5.99, 11.99, 15, 'Shelf C4', 2),
('Battery', 'BAT-5678', 'Electrical', '12V car battery', 15, 'piece', 65.99, 99.99, 5, 'Shelf F1', 3);

-- Insert sample appointments
INSERT INTO `appointments` (`customer_id`, `vehicle_id`, `service_id`, `user_id`, `date`, `start_time`, `end_time`, `status`, `notes`) VALUES
(1, 1, 1, 3, '2025-03-22', '09:00:00', '09:30:00', 'scheduled', 'Regular oil change'),
(2, 2, 2, 4, '2025-03-22', '10:00:00', '11:00:00', 'scheduled', 'Customer reported squeaking noise when braking'),
(3, 3, 4, 3, '2025-03-23', '13:00:00', '15:00:00', 'scheduled', 'Engine running rough'),
(4, 4, 3, 4, '2025-03-24', '11:00:00', '11:30:00', 'scheduled', 'Regular maintenance'),
(5, 5, 5, 3, '2025-03-25', '14:00:00', '15:00:00', 'scheduled', 'AC not cooling properly');

-- Insert sample invoices
INSERT INTO `invoices` (`invoice_number`, `customer_id`, `vehicle_id`, `appointment_id`, `user_id`, `subtotal`, `tax_rate`, `tax_amount`, `discount_amount`, `total_amount`, `paid_amount`, `due_amount`, `issue_date`, `due_date`, `status`) VALUES
('INV-2023-001', 1, 1, 1, 1, 49.99, 8.00, 4.00, 0.00, 53.99, 53.99, 0.00, '2025-03-10', '2025-03-25', 'paid'),
('INV-2023-002', 2, 2, 2, 1, 149.99, 8.00, 12.00, 0.00, 161.99, 161.99, 0.00, '2025-03-12', '2025-03-27', 'paid'),
('INV-2023-003', 3, 3, 3, 1, 199.99, 8.00, 16.00, 0.00, 215.99, 0.00, 215.99, '2025-03-15', '2025-03-30', 'unpaid');

-- Insert sample invoice items
INSERT INTO `invoice_items` (`invoice_id`, `item_type`, `item_id`, `description`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 'service', 1, 'Oil Change', 1, 49.99, 49.99),
(2, 'service', 2, 'Brake Service', 1, 149.99, 149.99),
(3, 'service', 4, 'Engine Tune-Up', 1, 199.99, 199.99);

-- Insert sample payments
INSERT INTO `payments` (`invoice_id`, `amount`, `payment_method`, `reference_number`, `payment_date`, `created_by`) VALUES
(1, 53.99, 'credit_card', 'CC-12345', '2025-03-10', 1),
(2, 161.99, 'debit_card', 'DC-67890', '2025-03-12', 1);

-- Insert sample settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('garage_name', 'Auto Care Garage'),
('garage_address', '123 Repair Street, Fixitville, CA 12345'),
('garage_phone', '555-123-4567'),
('garage_email', 'info@autocare.com'),
('tax_rate', '8.00'),
('business_hours', '{"monday":"8:00-18:00","tuesday":"8:00-18:00","wednesday":"8:00-18:00","thursday":"8:00-18:00","friday":"8:00-18:00","saturday":"9:00-14:00","sunday":"closed"}'),
('invoice_prefix', 'INV-'),
('invoice_next_number', '004'),
('currency_symbol', '$'),
('date_format', 'Y-m-d'),
('time_format', 'H:i:s');
