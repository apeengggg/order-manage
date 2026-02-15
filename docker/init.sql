-- ============================================
-- Docker Init: Clean schema for fresh install (SaaS Multi-Tenant)
-- ============================================

-- ============================================
-- Table: tenants
-- ============================================
CREATE TABLE IF NOT EXISTS `tenants` (
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
CREATE TABLE IF NOT EXISTS `roles` (
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
-- Table: users
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
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
-- Table: expeditions
-- ============================================
CREATE TABLE IF NOT EXISTS `expeditions` (
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
CREATE TABLE IF NOT EXISTS `orders` (
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
  `extra_fields` JSON DEFAULT NULL,
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
-- Table: modules (GLOBAL)
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
  `is_superadmin_only` TINYINT(1) DEFAULT 0,
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
  `tenant_id` INT DEFAULT NULL,
  `module` VARCHAR(50) NOT NULL,
  `module_id` INT NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_type` VARCHAR(100) DEFAULT NULL,
  `file_size` INT DEFAULT 0,
  `uploaded_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_module` (`module`, `module_id`),
  INDEX `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB;

-- ============================================
-- Table: app_settings (per-tenant)
-- ============================================
CREATE TABLE IF NOT EXISTS `app_settings` (
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
CREATE TABLE IF NOT EXISTS `expedition_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT DEFAULT NULL,
  `expedition_id` INT NOT NULL,
  `file_id` INT DEFAULT NULL,
  `sheet_name` VARCHAR(100) NOT NULL,
  `columns` JSON NOT NULL,
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

INSERT INTO `tenants` (`id`, `name`, `slug`) VALUES
(1, 'Default Company', 'default');

INSERT INTO `roles` (`id`, `tenant_id`, `name`, `slug`, `description`) VALUES
(1, NULL, 'Super Admin', 'superadmin', 'Super admin - manages all tenants'),
(2, 1, 'Admin', 'admin', 'Tenant admin - full access within tenant'),
(3, 1, 'Customer Service', 'cs', 'Limited access');

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

INSERT INTO `app_settings` (`tenant_id`, `setting_key`, `setting_value`) VALUES
(1, 'app_name', 'Order Management System'),
(1, 'primary_color', '#007bff'),
(1, 'login_bg_color', '#667eea'),
(1, 'logo_file_id', NULL),
(1, 'login_bg_file_id', NULL);

-- Super Admin: all modules
INSERT INTO `role_permissions` (`role_id`, `module_id`, `can_view`, `can_add`, `can_edit`, `can_delete`, `can_view_detail`, `can_upload`, `can_download`) VALUES
(1, 1, 1, 1, 1, 1, 1, 1, 1), (1, 2, 1, 1, 1, 1, 1, 1, 1), (1, 3, 1, 1, 1, 1, 1, 1, 1),
(1, 4, 1, 1, 1, 1, 1, 1, 1), (1, 5, 1, 1, 1, 1, 1, 1, 1), (1, 6, 1, 1, 1, 1, 1, 1, 1),
(1, 7, 1, 1, 1, 1, 1, 1, 1), (1, 8, 1, 1, 1, 1, 1, 1, 1), (1, 9, 1, 1, 1, 1, 1, 1, 1),
(1, 10, 1, 1, 1, 1, 1, 1, 1), (1, 11, 1, 1, 1, 1, 1, 1, 1);

-- Tenant Admin: all except modules & tenants
INSERT INTO `role_permissions` (`role_id`, `module_id`, `can_view`, `can_add`, `can_edit`, `can_delete`, `can_view_detail`, `can_upload`, `can_download`) VALUES
(2, 1, 1, 1, 1, 1, 1, 1, 1), (2, 2, 1, 1, 1, 1, 1, 1, 1), (2, 3, 1, 1, 1, 1, 1, 1, 1),
(2, 4, 1, 1, 1, 1, 1, 1, 1), (2, 5, 1, 1, 1, 1, 1, 1, 1),
(2, 7, 1, 1, 1, 1, 1, 1, 1), (2, 8, 1, 1, 1, 1, 1, 1, 1), (2, 9, 1, 1, 1, 1, 1, 1, 1),
(2, 10, 1, 1, 1, 1, 1, 1, 1);

-- CS: limited
INSERT INTO `role_permissions` (`role_id`, `module_id`, `can_view`, `can_add`, `can_edit`, `can_delete`, `can_view_detail`, `can_upload`, `can_download`) VALUES
(3, 1, 1, 0, 0, 0, 0, 0, 0), (3, 2, 1, 1, 0, 0, 0, 0, 0), (3, 3, 1, 1, 1, 1, 1, 0, 0),
(3, 4, 0, 0, 0, 0, 0, 0, 0), (3, 5, 0, 0, 0, 0, 0, 0, 0),
(3, 7, 0, 0, 0, 0, 0, 0, 0), (3, 8, 0, 0, 0, 0, 0, 0, 0), (3, 9, 0, 0, 0, 0, 0, 0, 0),
(3, 10, 0, 0, 0, 0, 0, 0, 0);
