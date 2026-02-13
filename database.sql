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
-- Table: modules (Menu dinamis)
-- ============================================
CREATE TABLE `modules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `icon` VARCHAR(50) DEFAULT 'fas fa-circle',
  `url` VARCHAR(200) NOT NULL,
  `parent_id` INT DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `modules`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- Table: role_permissions
-- ============================================
CREATE TABLE `role_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role` ENUM('admin','cs') NOT NULL,
  `module_id` INT NOT NULL,
  `can_view` TINYINT(1) DEFAULT 0,
  `can_add` TINYINT(1) DEFAULT 0,
  `can_edit` TINYINT(1) DEFAULT 0,
  `can_delete` TINYINT(1) DEFAULT 0,
  `can_view_detail` TINYINT(1) DEFAULT 0,
  `can_upload` TINYINT(1) DEFAULT 0,
  `can_download` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `role_module_unique` (`role`, `module_id`),
  FOREIGN KEY (`module_id`) REFERENCES `modules`(`id`) ON DELETE CASCADE
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

-- ============================================
-- Default Modules (Menu)
-- ============================================
INSERT INTO `modules` (`id`, `name`, `slug`, `icon`, `url`, `parent_id`, `sort_order`) VALUES
(1, 'Dashboard',         'dashboard',      'fas fa-tachometer-alt', 'dashboard',         NULL, 1),
(2, 'Input Data Customer','orders-create',  'fas fa-plus-circle',    'orders/create',     NULL, 2),
(3, 'List Order',         'orders',         'fas fa-list-alt',       'orders',            NULL, 3),
(4, 'Export Order',       'admin-export',   'fas fa-file-export',    'admin',             NULL, 4),
(5, 'Kelola Ekspedisi',   'expeditions',    'fas fa-truck',          'expeditions',       NULL, 5),
(6, 'Kelola Permission',  'permissions',    'fas fa-user-shield',    'permissions',       NULL, 6);

-- ============================================
-- Default Permissions: Admin (semua akses)
-- ============================================
INSERT INTO `role_permissions` (`role`, `module_id`, `can_view`, `can_add`, `can_edit`, `can_delete`, `can_view_detail`, `can_upload`, `can_download`) VALUES
('admin', 1, 1, 1, 1, 1, 1, 1, 1),
('admin', 2, 1, 1, 1, 1, 1, 1, 1),
('admin', 3, 1, 1, 1, 1, 1, 1, 1),
('admin', 4, 1, 1, 1, 1, 1, 1, 1),
('admin', 5, 1, 1, 1, 1, 1, 1, 1),
('admin', 6, 1, 1, 1, 1, 1, 1, 1);

-- ============================================
-- Default Permissions: CS (terbatas)
-- ============================================
INSERT INTO `role_permissions` (`role`, `module_id`, `can_view`, `can_add`, `can_edit`, `can_delete`, `can_view_detail`, `can_upload`, `can_download`) VALUES
('cs', 1, 1, 0, 0, 0, 0, 0, 0),
('cs', 2, 1, 1, 0, 0, 0, 0, 0),
('cs', 3, 1, 1, 1, 1, 1, 0, 0),
('cs', 4, 0, 0, 0, 0, 0, 0, 0),
('cs', 5, 0, 0, 0, 0, 0, 0, 0),
('cs', 6, 0, 0, 0, 0, 0, 0, 0);
