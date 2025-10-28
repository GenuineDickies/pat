# Settings and Configuration Management

This document describes the comprehensive settings and configuration management system implemented for Patone v1.0.

## Overview

The settings system provides a centralized way to manage:
- System configuration
- User accounts and roles
- Role-based permissions
- Service types
- Settings backup and restore

## Features

### 1. General Settings Management
- Key-value configuration storage
- Type-safe value casting (string, integer, boolean, JSON)
- Public/private settings separation
- Category-based organization

### 2. User Management
- Complete CRUD operations for users
- Role assignment (Admin, Manager, Dispatcher, Driver)
- Status management (Active, Inactive, Suspended)
- Secure password management
- Self-protection (prevent self-deletion, role changes)

### 3. Role-Based Access Control (RBAC)
- Granular permission system with 30+ permissions
- Four predefined roles with appropriate permissions
- Category-based permission grouping
- Permission hierarchy validation
- Admin role protection

### 4. Service Type Configuration
- Manage available service offerings
- Configure pricing and duration
- Set priority levels
- Enable/disable service types
- Complete CRUD operations

### 5. Backup and Restore
- Export settings as JSON
- Import settings from backup
- Timestamp and user tracking
- Safe import with validation

## Database Schema

### Permissions Table
```sql
CREATE TABLE permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    permission_key VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    category VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Role Permissions Table
```sql
CREATE TABLE role_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin', 'manager', 'dispatcher', 'driver') NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);
```

## Permission Categories

1. **Dashboard**: View dashboard, analytics
2. **Customers**: View, add, edit, delete customers
3. **Drivers**: View, add, edit, delete, assign drivers
4. **Requests**: View, create, edit, delete, assign, complete, cancel requests
5. **Reports**: View, generate, export reports
6. **Settings**: Manage settings, users, permissions, service types
7. **Logs**: View activity logs
8. **API**: API access

## Default Role Permissions

### Admin
- Has ALL permissions
- Cannot be modified

### Manager
- All permissions except:
  - Delete customers, drivers
  - Manage users and permissions
  - Delete service types

### Dispatcher
- View dashboard
- Manage customers (view, add, edit)
- View drivers
- Manage requests (view, create, edit, assign, complete)
- View reports

### Driver
- View dashboard
- View requests
- Complete requests

## API Endpoints

### Settings Management
```
GET  /settings                    - Settings page (with tab parameter)
POST /settings                    - Update general settings
GET  /settings/export            - Export settings as JSON
POST /settings/import            - Import settings from JSON
```

### User Management
```
POST /settings/user/add          - Add new user
POST /settings/user/edit         - Edit user details
GET  /settings/user/delete/{id}  - Delete user
POST /settings/user/password     - Change user password
```

### Role & Permission Management
```
POST /settings/role/permissions  - Update role permissions
```

### Service Type Management
```
POST /settings/service/add       - Add service type
POST /settings/service/edit      - Edit service type
GET  /settings/service/delete/{id} - Delete service type
```

## Usage Examples

### PHP - Check Permission
```php
// In controller
$this->requirePermission('manage_settings');

// In view
<?php if (hasPermission('manage_users')): ?>
    <!-- Show user management interface -->
<?php endif; ?>
```

### PHP - Get Setting Value
```php
$settingModel = new Setting();

// Get string value
$siteName = $settingModel->getValue('site_name');

// Get with default
$maxDistance = $settingModel->getValue('max_dispatch_distance', 50);

// Get boolean
$gpsEnabled = $settingModel->getValue('enable_gps_tracking');
```

### PHP - Set Setting Value
```php
$settingModel = new Setting();

// Set string value
$settingModel->setValue('site_name', 'My Site', 'string');

// Set integer value
$settingModel->setValue('max_distance', 100, 'integer');

// Set boolean value
$settingModel->setValue('enable_notifications', true, 'boolean');
```

### PHP - Manage User
```php
$userModel = new User();

// Create user
$userId = $userModel->create([
    'username' => 'johndoe',
    'email' => 'john@example.com',
    'password' => 'SecurePass123!',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'role' => 'dispatcher',
    'status' => 'active'
]);

// Update user
$userModel->update($userId, [
    'role' => 'manager',
    'status' => 'active'
]);

// Change password
$userModel->updatePassword($userId, 'NewPassword123!');
```

### PHP - Check Role Permissions
```php
$permissionModel = new Permission();

// Get permissions for role
$permissions = $permissionModel->getKeysForRole('manager');

// Check if role has permission
$hasPermission = $permissionModel->roleHasPermission('dispatcher', 'assign_requests');

// Get all roles with permissions
$roles = $permissionModel->getAllRolesWithPermissions();
```

## Security Features

1. **CSRF Protection**: All forms include CSRF tokens
2. **Permission Validation**: All operations check user permissions
3. **Self-Protection**: Users cannot delete themselves or change their own admin role
4. **Password Security**: Minimum length validation, bcrypt hashing
5. **Audit Logging**: All changes are logged with user and timestamp
6. **Admin Protection**: Admin role permissions cannot be modified
7. **Input Sanitization**: All user inputs are sanitized
8. **SQL Injection Prevention**: Prepared statements used throughout

## Testing

Run the settings test suite:
```bash
cd tests
php SettingsTest.php
```

Tests include:
- Permission model operations
- Setting model CRUD operations
- Type casting (string, integer, boolean)
- Role permission management
- Permission hierarchy validation
- User management operations
- Service type management
- Backup/restore functionality

## Migration

To set up the permissions system:

1. Run the migration script:
```bash
mysql -u username -p database_name < database/migrations/002_permissions_setup.sql
```

2. This will create:
   - `permissions` table
   - `role_permissions` table
   - 30+ default permissions
   - Default role-permission mappings

## UI Components

### Tabbed Interface
The settings page uses a tabbed interface with 5 sections:
1. **General**: System configuration
2. **Users**: User management
3. **Roles & Permissions**: Role permission configuration
4. **Service Types**: Service catalog management
5. **Backup & Restore**: Export/import settings

### Modals
- Add User Modal
- Add Service Type Modal
- Edit modals (to be implemented)

### Forms
- General settings form
- User add/edit forms
- Role permission forms
- Service type forms
- Backup import form

## File Structure

```
backend/
  controllers/
    SettingController.php      - Settings controller with all operations
  models/
    Permission.php             - Permission model
    Setting.php                - Setting model
    User.php                   - User model (extended)
    ServiceType.php            - Service type model

frontend/
  pages/
    settings.php               - Settings UI with tabbed interface

database/
  migrations/
    002_permissions_setup.sql  - Permission system migration

tests/
  SettingsTest.php            - Comprehensive test suite
```

## Best Practices

1. **Always check permissions**: Use `requirePermission()` in controllers
2. **Log changes**: Use `logActivity()` for audit trail
3. **Validate inputs**: Use built-in validation methods
4. **Use transactions**: For multi-step operations
5. **Regular backups**: Export settings regularly
6. **Test changes**: Run test suite after modifications
7. **Secure passwords**: Enforce minimum length and complexity

## Future Enhancements

- Two-factor authentication (2FA)
- Email/SMS notification settings
- Third-party API integration settings
- Dashboard customization settings
- Custom permission creation
- Role creation and management
- Advanced audit logging with filters
- Settings change history
- Bulk user operations
- Password complexity rules
- Session management settings

## Troubleshooting

### Common Issues

1. **Permission denied errors**
   - Verify user has appropriate role
   - Check role permissions in database
   - Ensure admin role has all permissions

2. **Setting not saving**
   - Check data type matches setting type
   - Verify CSRF token is valid
   - Check database connection

3. **User creation fails**
   - Verify all required fields are provided
   - Check for duplicate username/email
   - Ensure password meets minimum length

4. **Cannot modify admin role**
   - This is by design for security
   - Admin role always has all permissions

## Support

For issues or questions:
- Review error logs in `logs/` directory
- Run test suite to identify issues
- Check database for migration completion
- Verify file permissions on settings directories
