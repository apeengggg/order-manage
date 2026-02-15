-- ============================================
-- Database: order_management (SaaS Multi-Tenant)
-- ============================================

CREATE DATABASE IF NOT EXISTS `order_management_ssas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `order_management_ssas`;

-- ============================================
-- Table: tenants
-- ============================================
CREATE TABLE `tenants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `domain` VARCHAR(100) DEFAULT NULL COMMENT 'Optional custom domain',
  `is_active` TINYINT(1) DEFAULT 1,
  `max_users` INT DEFAULT 10 COMMENT 'Max users allowed for this tenant',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Table: roles
-- ============================================
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT DEFAULT NULL,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `description` VARCHAR(200) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_tenant_role_name` (`tenant_id`, `name`),
  UNIQUE KEY `uq_tenant_role_slug` (`tenant_id`, `slug`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Table: users (CS & Admin)
-- ============================================
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT DEFAULT NULL COMMENT 'NULL = super admin',
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `role_id` INT NOT NULL DEFAULT 2,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB;

-- ============================================
-- Table: expeditions (Ekspedisi)
-- ============================================
CREATE TABLE `expeditions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_tenant_expedition_code` (`tenant_id`, `code`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Table: orders
-- ============================================
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT DEFAULT NULL,
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
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`expedition_id`) REFERENCES `expeditions`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`exported_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB;

-- ============================================
-- Table: modules (Menu dinamis - GLOBAL, shared across tenants)
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
  `is_superadmin_only` TINYINT(1) DEFAULT 0 COMMENT '1 = only visible to super admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `modules`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- Table: role_permissions
-- ============================================
CREATE TABLE `role_permissions` (
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
-- Table: files (Generic file storage for all modules)
-- ============================================
CREATE TABLE `files` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT DEFAULT NULL,
  `module` VARCHAR(50) NOT NULL COMMENT 'Module name e.g. expeditions, orders',
  `module_id` INT NOT NULL COMMENT 'ID of the related record in the module',
  `file_name` VARCHAR(255) NOT NULL COMMENT 'Original file name',
  `file_path` VARCHAR(500) NOT NULL COMMENT 'Stored file path relative to uploads/',
  `file_type` VARCHAR(100) DEFAULT NULL COMMENT 'MIME type',
  `file_size` INT DEFAULT 0 COMMENT 'File size in bytes',
  `uploaded_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_module` (`module`, `module_id`),
  INDEX `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB;

-- ============================================
-- Table: app_settings (per-tenant settings)
-- ============================================
CREATE TABLE `app_settings` (
  `tenant_id` INT DEFAULT NULL,
  `setting_key` VARCHAR(50) NOT NULL,
  `setting_value` LONGTEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tenant_id`, `setting_key`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Table: expedition_templates
-- ============================================
CREATE TABLE `expedition_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT DEFAULT NULL,
  `expedition_id` INT NOT NULL,
  `file_id` INT DEFAULT NULL COMMENT 'Reference to files table for uploaded XLSX',
  `sheet_name` VARCHAR(100) NOT NULL COMMENT 'Sheet name where headers were found',
  `columns` JSON NOT NULL COMMENT 'Array of {name, clean_name, position, is_required, input_type, options}',
  `uploaded_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_expedition_template` (`expedition_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`expedition_id`) REFERENCES `expeditions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- Default Data
-- ============================================

-- Default tenant
INSERT INTO `tenants` (`id`, `name`, `slug`) VALUES
(1, 'Default Company', 'default');

-- Roles: super admin (tenant_id=NULL), tenant admin & CS (tenant_id=1)
INSERT INTO `roles` (`id`, `tenant_id`, `name`, `slug`, `description`) VALUES
(1, NULL, 'Super Admin', 'superadmin', 'Super admin - manages all tenants'),
(2, 1, 'Admin', 'admin', 'Tenant admin - full access within tenant'),
(3, 1, 'Customer Service', 'cs', 'Limited access');

-- Password: admin123
INSERT INTO `users` (`tenant_id`, `username`, `password`, `name`, `role_id`) VALUES
(NULL, 'superadmin', '$2y$10$J9wEl7EGZsqfZy4pdA9zJOlPpulqqah3aBbvRZGYSBgsw4Oa9Br.W', 'Super Administrator', 1),
(1, 'admin', '$2y$10$J9wEl7EGZsqfZy4pdA9zJOlPpulqqah3aBbvRZGYSBgsw4Oa9Br.W', 'Administrator', 2),
(1, 'cs1', '$2y$10$J9wEl7EGZsqfZy4pdA9zJOlPpulqqah3aBbvRZGYSBgsw4Oa9Br.W', 'Customer Service 1', 3),
(1, 'cs2', '$2y$10$J9wEl7EGZsqfZy4pdA9zJOlPpulqqah3aBbvRZGYSBgsw4Oa9Br.W', 'Customer Service 2', 3);

INSERT INTO `expeditions` (`tenant_id`, `name`, `code`) VALUES
(1, 'JNE', 'JNE'),
(1, 'J&T Express', 'JNT'),
(1, 'SiCepat', 'SICEPAT'),
(1, 'AnterAja', 'ANTERAJA'),
(1, 'Ninja Express', 'NINJA'),
(1, 'POS Indonesia', 'POS'),
(1, 'TIKI', 'TIKI');

-- ============================================
-- Default Modules (Menu) - GLOBAL
-- ============================================
INSERT INTO `modules` (`id`, `name`, `slug`, `icon`, `url`, `parent_id`, `sort_order`, `is_superadmin_only`) VALUES
(1, 'Dashboard',          'dashboard',      'fas fa-tachometer-alt', 'dashboard',     NULL, 1, 0),
(2, 'Input Data Customer', 'orders-create',  'fas fa-plus-circle',    'orders/create', NULL, 2, 0),
(3, 'List Order',          'orders',         'fas fa-list-alt',       'orders',        NULL, 3, 0),
(4, 'Export Order',        'admin-export',   'fas fa-file-export',    'admin',         NULL, 4, 0),
(5, 'Kelola Ekspedisi',    'expeditions',    'fas fa-truck',          'expeditions',   NULL, 5, 0),
(6, 'Kelola Modul',        'modules',        'fas fa-cubes',          'modules',       NULL, 6, 1),
(7, 'Kelola Permission',   'permissions',    'fas fa-user-shield',    'permissions',   NULL, 7, 0),
(8, 'Kelola Role',         'roles',          'fas fa-user-tag',       'roles',         NULL, 8, 0),
(9, 'Kelola User',         'users',          'fas fa-users',          'users',         NULL, 9, 0),
(10, 'Pengaturan',         'settings',       'fas fa-cog',            'settings',      NULL, 10, 0),
(11, 'Kelola Tenant',      'tenants',        'fas fa-building',       'tenants',       NULL, 11, 1);

-- ============================================
-- Default App Settings (per tenant)
-- ============================================
INSERT INTO `app_settings` (`tenant_id`, `setting_key`, `setting_value`) VALUES
(1, 'app_name', 'Order Management System'),
(1, 'primary_color', '#007bff'),
(1, 'login_bg_color', '#667eea'),
(1, 'logo_file_id', NULL),
(1, 'login_bg_file_id', NULL);

-- ============================================
-- Default Permissions: Super Admin role (id=1) - all modules
-- ============================================
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
(1, 10, 1, 1, 1, 1, 1, 1, 1),
(1, 11, 1, 1, 1, 1, 1, 1, 1);

-- ============================================
-- Default Permissions: Tenant Admin role (id=2)
-- ============================================
INSERT INTO `role_permissions` (`role_id`, `module_id`, `can_view`, `can_add`, `can_edit`, `can_delete`, `can_view_detail`, `can_upload`, `can_download`) VALUES
(2, 1, 1, 1, 1, 1, 1, 1, 1),
(2, 2, 1, 1, 1, 1, 1, 1, 1),
(2, 3, 1, 1, 1, 1, 1, 1, 1),
(2, 4, 1, 1, 1, 1, 1, 1, 1),
(2, 5, 1, 1, 1, 1, 1, 1, 1),
(2, 7, 1, 1, 1, 1, 1, 1, 1),
(2, 8, 1, 1, 1, 1, 1, 1, 1),
(2, 9, 1, 1, 1, 1, 1, 1, 1),
(2, 10, 1, 1, 1, 1, 1, 1, 1);

-- ============================================
-- Default Permissions: CS role (id=3) - limited
-- ============================================
INSERT INTO `role_permissions` (`role_id`, `module_id`, `can_view`, `can_add`, `can_edit`, `can_delete`, `can_view_detail`, `can_upload`, `can_download`) VALUES
(3, 1, 1, 0, 0, 0, 0, 0, 0),
(3, 2, 1, 1, 0, 0, 0, 0, 0),
(3, 3, 1, 1, 1, 1, 1, 0, 0),
(3, 4, 0, 0, 0, 0, 0, 0, 0),
(3, 5, 0, 0, 0, 0, 0, 0, 0),
(3, 7, 0, 0, 0, 0, 0, 0, 0),
(3, 8, 0, 0, 0, 0, 0, 0, 0),
(3, 9, 0, 0, 0, 0, 0, 0, 0),
(3, 10, 0, 0, 0, 0, 0, 0, 0);


-- ============================================
-- MIGRATION QUERIES
-- Run these if upgrading from single-tenant to multi-tenant
-- ============================================

-- [Migration 7] Add multi-tenant support

-- Step 1: Create tenants table
CREATE TABLE IF NOT EXISTS `tenants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `domain` VARCHAR(100) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `max_users` INT DEFAULT 10,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Step 2: Insert default tenant
INSERT IGNORE INTO `tenants` (`id`, `name`, `slug`) VALUES (1, 'Default Company', 'default');

-- Step 3: Add tenant_id to users
ALTER TABLE `users` ADD COLUMN `tenant_id` INT DEFAULT NULL AFTER `id`;
ALTER TABLE `users` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE;
UPDATE `users` SET `tenant_id` = 1;

-- Step 4: Add tenant_id to roles
ALTER TABLE `roles` ADD COLUMN `tenant_id` INT DEFAULT NULL AFTER `id`;
ALTER TABLE `roles` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE;
UPDATE `roles` SET `tenant_id` = 1;
ALTER TABLE `roles` DROP INDEX `name`, DROP INDEX `slug`;
ALTER TABLE `roles` ADD UNIQUE KEY `uq_tenant_role_name` (`tenant_id`, `name`);
ALTER TABLE `roles` ADD UNIQUE KEY `uq_tenant_role_slug` (`tenant_id`, `slug`);

-- Step 5: Add tenant_id to expeditions
ALTER TABLE `expeditions` ADD COLUMN `tenant_id` INT DEFAULT NULL AFTER `id`;
ALTER TABLE `expeditions` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE;
UPDATE `expeditions` SET `tenant_id` = 1;
ALTER TABLE `expeditions` DROP INDEX `code`;
ALTER TABLE `expeditions` ADD UNIQUE KEY `uq_tenant_expedition_code` (`tenant_id`, `code`);

-- Step 6: Add tenant_id to orders
ALTER TABLE `orders` ADD COLUMN `tenant_id` INT DEFAULT NULL AFTER `id`;
ALTER TABLE `orders` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE;
UPDATE `orders` SET `tenant_id` = 1;
ALTER TABLE `orders` ADD INDEX `idx_tenant` (`tenant_id`);

-- Step 7: Add tenant_id to files
ALTER TABLE `files` ADD COLUMN `tenant_id` INT DEFAULT NULL AFTER `id`;
ALTER TABLE `files` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE;
UPDATE `files` SET `tenant_id` = 1;
ALTER TABLE `files` ADD INDEX `idx_tenant_files` (`tenant_id`);

-- Step 8: Add tenant_id to expedition_templates
ALTER TABLE `expedition_templates` ADD COLUMN `tenant_id` INT DEFAULT NULL AFTER `id`;
ALTER TABLE `expedition_templates` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE;
UPDATE `expedition_templates` SET `tenant_id` = 1;

-- Step 9: Convert app_settings to per-tenant
ALTER TABLE `app_settings` ADD COLUMN `tenant_id` INT DEFAULT NULL FIRST;
UPDATE `app_settings` SET `tenant_id` = 1;
ALTER TABLE `app_settings` DROP PRIMARY KEY, ADD PRIMARY KEY (`tenant_id`, `setting_key`);
ALTER TABLE `app_settings` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE;

-- Step 10: Add is_superadmin_only to modules
ALTER TABLE `modules` ADD COLUMN `is_superadmin_only` TINYINT(1) DEFAULT 0 AFTER `is_active`;

-- Step 11: Create super admin role
INSERT INTO `roles` (`tenant_id`, `name`, `slug`, `description`) VALUES
(NULL, 'Super Admin', 'superadmin', 'Super admin - manages all tenants');

-- Step 12: Convert existing superadmin user OR create new one
-- If 'superadmin' user already exists (from pre-SaaS), update it to be the super admin
UPDATE `users` SET
  `tenant_id` = NULL,
  `role_id` = (SELECT id FROM roles WHERE slug = 'superadmin' AND tenant_id IS NULL),
  `name` = 'Super Administrator'
WHERE `username` = 'superadmin';

-- If no superadmin user exists, create one (password: admin123)
INSERT IGNORE INTO `users` (`tenant_id`, `username`, `password`, `name`, `role_id`) VALUES
(NULL, 'superadmin', '$2y$10$J9wEl7EGZsqfZy4pdA9zJOlPpulqqah3aBbvRZGYSBgsw4Oa9Br.W', 'Super Administrator',
  (SELECT id FROM roles WHERE slug = 'superadmin' AND tenant_id IS NULL));

-- Step 13: Add Kelola Tenant module
INSERT IGNORE INTO `modules` (`name`, `slug`, `icon`, `url`, `parent_id`, `sort_order`, `is_superadmin_only`)
VALUES ('Kelola Tenant', 'tenants', 'fas fa-building', 'tenants', NULL, 11, 1);

-- Step 14: Mark modules as superadmin-only
UPDATE `modules` SET `is_superadmin_only` = 1 WHERE `slug` = 'modules';

-- Step 15: Add permissions for super admin role
INSERT IGNORE INTO `role_permissions` (`role_id`, `module_id`, `can_view`, `can_add`, `can_edit`, `can_delete`, `can_view_detail`, `can_upload`, `can_download`)
SELECT r.id, m.id, 1, 1, 1, 1, 1, 1, 1
FROM `roles` r CROSS JOIN `modules` m
WHERE r.slug = 'superadmin' AND r.tenant_id IS NULL AND m.is_active = 1;
