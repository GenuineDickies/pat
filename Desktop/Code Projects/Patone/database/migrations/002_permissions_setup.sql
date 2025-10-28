-- ============================================
-- Permissions and Role Management Migration
-- ============================================

-- Permissions Table
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `permission_key` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `category` VARCHAR(50) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`permission_key`),
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role Permissions Mapping Table
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role` ENUM('admin', 'manager', 'dispatcher', 'driver') NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_role_permission` (`role`, `permission_id`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Permissions
INSERT INTO `permissions` (`permission_key`, `name`, `description`, `category`) VALUES
-- Dashboard
('view_dashboard', 'View Dashboard', 'Access to main dashboard', 'Dashboard'),
('view_analytics', 'View Analytics', 'Access to analytics and reports', 'Dashboard'),

-- Customer Management
('view_customers', 'View Customers', 'View customer list and details', 'Customers'),
('add_customers', 'Add Customers', 'Create new customers', 'Customers'),
('edit_customers', 'Edit Customers', 'Modify customer information', 'Customers'),
('delete_customers', 'Delete Customers', 'Remove customers from system', 'Customers'),

-- Driver Management
('view_drivers', 'View Drivers', 'View driver list and details', 'Drivers'),
('add_drivers', 'Add Drivers', 'Create new drivers', 'Drivers'),
('edit_drivers', 'Edit Drivers', 'Modify driver information', 'Drivers'),
('delete_drivers', 'Delete Drivers', 'Remove drivers from system', 'Drivers'),
('assign_drivers', 'Assign Drivers', 'Assign drivers to service requests', 'Drivers'),

-- Service Request Management
('view_requests', 'View Requests', 'View service requests', 'Requests'),
('create_requests', 'Create Requests', 'Create new service requests', 'Requests'),
('edit_requests', 'Edit Requests', 'Modify service requests', 'Requests'),
('delete_requests', 'Delete Requests', 'Delete service requests', 'Requests'),
('assign_requests', 'Assign Requests', 'Assign requests to drivers', 'Requests'),
('complete_requests', 'Complete Requests', 'Mark requests as completed', 'Requests'),
('cancel_requests', 'Cancel Requests', 'Cancel service requests', 'Requests'),

-- Report Management
('view_reports', 'View Reports', 'Access reports section', 'Reports'),
('generate_reports', 'Generate Reports', 'Create new reports', 'Reports'),
('export_reports', 'Export Reports', 'Export report data', 'Reports'),

-- Settings Management
('manage_settings', 'Manage Settings', 'Access and modify system settings', 'Settings'),
('manage_users', 'Manage Users', 'Create, edit, and delete users', 'Settings'),
('manage_permissions', 'Manage Permissions', 'Modify role permissions', 'Settings'),
('manage_service_types', 'Manage Service Types', 'Configure service types', 'Settings'),

-- Activity Logs
('view_activity_logs', 'View Activity Logs', 'Access system activity logs', 'Logs'),

-- API Access
('api_access', 'API Access', 'Access to API endpoints', 'API');

-- Assign Permissions to Admin Role (all permissions)
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'admin', id FROM `permissions`;

-- Assign Permissions to Manager Role
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'manager', id FROM `permissions` WHERE permission_key IN (
    'view_dashboard', 'view_analytics',
    'view_customers', 'add_customers', 'edit_customers',
    'view_drivers', 'add_drivers', 'edit_drivers', 'assign_drivers',
    'view_requests', 'create_requests', 'edit_requests', 'assign_requests', 'complete_requests', 'cancel_requests',
    'view_reports', 'generate_reports', 'export_reports',
    'manage_service_types',
    'view_activity_logs'
);

-- Assign Permissions to Dispatcher Role
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'dispatcher', id FROM `permissions` WHERE permission_key IN (
    'view_dashboard',
    'view_customers', 'add_customers', 'edit_customers',
    'view_drivers',
    'view_requests', 'create_requests', 'edit_requests', 'assign_requests', 'complete_requests',
    'view_reports'
);

-- Assign Permissions to Driver Role
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'driver', id FROM `permissions` WHERE permission_key IN (
    'view_dashboard',
    'view_requests',
    'complete_requests'
);

-- ============================================
-- End of Migration
-- ============================================
