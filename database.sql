-- ============================================
-- Database: order_management
-- ============================================

CREATE DATABASE IF NOT EXISTS `order_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `order_management`;

-- ============================================
-- Table: users (CS & Admin)
-- ============================================
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `role` ENUM('admin','cs') NOT NULL DEFAULT 'cs',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Table: expeditions (Ekspedisi)
-- ============================================
CREATE TABLE `expeditions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Table: orders
-- ============================================
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(20) NOT NULL,
  `customer_address` TEXT NOT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `qty` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `total` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `expedition_id` INT DEFAULT NULL,
  `resi` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `is_exported` TINYINT(1) DEFAULT 0,
  `exported_at` DATETIME DEFAULT NULL,
  `exported_by` INT DEFAULT NULL,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`expedition_id`) REFERENCES `expeditions`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`exported_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Default Data
-- ============================================

-- Password: admin123
INSERT INTO `users` (`username`, `password`, `name`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin'),
('cs1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Customer Service 1', 'cs'),
('cs2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Customer Service 2', 'cs');

INSERT INTO `expeditions` (`name`, `code`) VALUES
('JNE', 'JNE'),
('J&T Express', 'JNT'),
('SiCepat', 'SICEPAT'),
('AnterAja', 'ANTERAJA'),
('Ninja Express', 'NINJA'),
('POS Indonesia', 'POS'),
('TIKI', 'TIKI');
