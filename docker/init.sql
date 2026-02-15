-- ============================================
-- Docker Init: Clean schema for fresh install
-- This file is auto-used by docker-compose on first run
-- ============================================

-- ============================================
-- Table: roles
-- ============================================
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `description` VARCHAR(200) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Table: users
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `role_id` INT NOT NULL DEFAULT 2,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB;

-- ============================================
-- Table: expeditions
-- ============================================
CREATE TABLE IF NOT EXISTS `expeditions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Table: orders
-- ============================================
CREATE TABLE IF NOT EXISTS `orders` (
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
  `extra_fields` JSON DEFAULT NULL COMMENT 'Template-specific fields as JSON',
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
-- Table: modules
-- ============================================
CREATE TABLE IF NOT EXISTS `modules` (
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
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT NOT NULL,
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
  UNIQUE KEY `role_module_unique` (`role_id`, `module_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`module_id`) REFERENCES `modules`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Table: files
-- ============================================
CREATE TABLE IF NOT EXISTS `files` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `module` VARCHAR(50) NOT NULL COMMENT 'Module name e.g. expeditions, orders',
  `module_id` INT NOT NULL COMMENT 'ID of the related record in the module',
  `file_name` VARCHAR(255) NOT NULL COMMENT 'Original file name',
  `file_path` VARCHAR(500) NOT NULL COMMENT 'Stored file path relative to uploads/',
  `file_type` VARCHAR(100) DEFAULT NULL COMMENT 'MIME type',
  `file_size` INT DEFAULT 0 COMMENT 'File size in bytes',
  `uploaded_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_module` (`module`, `module_id`)
) ENGINE=InnoDB;

-- ============================================
-- Table: app_settings
-- ============================================
CREATE TABLE IF NOT EXISTS `app_settings` (
  `setting_key` VARCHAR(50) PRIMARY KEY,
  `setting_value` LONGTEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Table: expedition_templates
-- ============================================
CREATE TABLE IF NOT EXISTS `expedition_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `expedition_id` INT NOT NULL,
  `file_id` INT DEFAULT NULL COMMENT 'Reference to files table for uploaded XLSX',
  `sheet_name` VARCHAR(100) NOT NULL COMMENT 'Sheet name where headers were found',
  `columns` JSON NOT NULL COMMENT 'Array of {name, clean_name, position, is_required, input_type, options}',
  `uploaded_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_expedition_template` (`expedition_id`),
  FOREIGN KEY (`expedition_id`) REFERENCES `expeditions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- Default Data
-- ============================================

INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Admin', 'admin', 'Full access'),
(2, 'Customer Service', 'cs', 'Limited access');

-- Default users (password: admin123)
INSERT INTO `users` (`username`, `password`, `name`, `role_id`) VALUES
('admin', '$2y$10$J9wEl7EGZsqfZy4pdA9zJOlPpulqqah3aBbvRZGYSBgsw4Oa9Br.W', 'Administrator', 1),
('cs1', '$2y$10$J9wEl7EGZsqfZy4pdA9zJOlPpulqqah3aBbvRZGYSBgsw4Oa9Br.W', 'Customer Service 1', 2),
('cs2', '$2y$10$J9wEl7EGZsqfZy4pdA9zJOlPpulqqah3aBbvRZGYSBgsw4Oa9Br.W', 'Customer Service 2', 2);

INSERT INTO `expeditions` (`name`, `code`) VALUES
('JNE', 'JNE'),
('J&T Express', 'JNT'),
('SiCepat', 'SICEPAT'),
('AnterAja', 'ANTERAJA'),
('Ninja Express', 'NINJA'),
('POS Indonesia', 'POS'),
('TIKI', 'TIKI');

-- Default Modules (Menu)
INSERT INTO `modules` (`id`, `name`, `slug`, `icon`, `url`, `parent_id`, `sort_order`) VALUES
(1, 'Dashboard',          'dashboard',      'fas fa-tachometer-alt', 'dashboard',     NULL, 1),
(2, 'Input Data Customer', 'orders-create',  'fas fa-plus-circle',    'orders/create', NULL, 2),
(3, 'List Order',          'orders',         'fas fa-list-alt',       'orders',        NULL, 3),
(4, 'Export Order',        'admin-export',   'fas fa-file-export',    'admin',         NULL, 4),
(5, 'Kelola Ekspedisi',    'expeditions',    'fas fa-truck',          'expeditions',   NULL, 5),
(6, 'Kelola Modul',        'modules',        'fas fa-cubes',          'modules',       NULL, 6),
(7, 'Kelola Permission',   'permissions',    'fas fa-user-shield',    'permissions',   NULL, 7),
(8, 'Kelola Role',         'roles',          'fas fa-user-tag',       'roles',         NULL, 8),
(9, 'Kelola User',         'users',          'fas fa-users',          'users',         NULL, 9),
(10, 'Pengaturan',         'settings',       'fas fa-cog',            'settings',      NULL, 10);

-- Default Permissions: Admin (full access)
INSERT INTO `role_permissions` (`role_id`, `module_id`, `can_view`, `can_add`, `can_edit`, `can_delete`, `can_view_detail`, `can_upload`, `can_download`) VALUES
(1, 1, 1, 1, 1, 1, 1, 1, 1),
(1, 2, 1, 1, 1, 1, 1, 1, 1),
(1, 3, 1, 1, 1, 1, 1, 1, 1),
(1, 4, 1, 1, 1, 1, 1, 1, 1),
(1, 5, 1, 1, 1, 1, 1, 1, 1),
(1, 6, 1, 1, 1, 1, 1, 1, 1),
(1, 7, 1, 1, 1, 1, 1, 1, 1),
(1, 8, 1, 1, 1, 1, 1, 1, 1),
(1, 9, 1, 1, 1, 1, 1, 1, 1),
(1, 10, 1, 1, 1, 1, 1, 1, 1);

-- Default App Settings
INSERT INTO `app_settings` (`setting_key`, `setting_value`) VALUES
('app_name', 'Order Management System'),
('dark_mode', '0'),
('primary_color', '#007bff'),
('login_bg_color', '#667eea'),
('logo_file_id', NULL),
('login_bg_file_id', NULL);

-- Default Permissions: CS (limited access)
INSERT INTO `role_permissions` (`role_id`, `module_id`, `can_view`, `can_add`, `can_edit`, `can_delete`, `can_view_detail`, `can_upload`, `can_download`) VALUES
(2, 1, 1, 0, 0, 0, 0, 0, 0),
(2, 2, 1, 1, 0, 0, 0, 0, 0),
(2, 3, 1, 1, 1, 1, 1, 0, 0),
(2, 4, 0, 0, 0, 0, 0, 0, 0),
(2, 5, 0, 0, 0, 0, 0, 0, 0),
(2, 6, 0, 0, 0, 0, 0, 0, 0),
(2, 7, 0, 0, 0, 0, 0, 0, 0),
(2, 8, 0, 0, 0, 0, 0, 0, 0),
(2, 9, 0, 0, 0, 0, 0, 0, 0),
(2, 10, 0, 0, 0, 0, 0, 0, 0);
