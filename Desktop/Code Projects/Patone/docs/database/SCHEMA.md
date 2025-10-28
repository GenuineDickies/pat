# Database Schema Documentation

Complete documentation of the Patone database structure.

## Overview

The Patone database uses MySQL/MariaDB with InnoDB engine for transactional support and foreign key constraints. All tables use `utf8mb4` character set for full Unicode support.

## Database Diagram

```
┌──────────┐         ┌───────────────┐         ┌──────────┐
│  users   │         │   customers   │         │ drivers  │
└────┬─────┘         └───────┬───────┘         └────┬─────┘
     │                       │                       │
     │                       │                       │
     │              ┌────────▼────────┐             │
     │              │ customer_       │             │
     │              │ vehicles        │             │
     │              └─────────────────┘             │
     │                                              │
     │              ┌─────────────────────────────┐ │
     └──────────────►  service_requests  ◄────────┘
                    └──────────┬──────────┘
                               │
                    ┌──────────▼──────────┐
                    │   service_types     │
                    └─────────────────────┘
```

## Tables

### 1. users

Stores system user accounts with role-based access control.

**Columns:**

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | Unique user identifier |
| `username` | VARCHAR(50) | UNIQUE, NOT NULL | Login username |
| `email` | VARCHAR(100) | UNIQUE, NOT NULL | User email address |
| `password` | VARCHAR(255) | NOT NULL | Bcrypt hashed password |
| `first_name` | VARCHAR(50) | NOT NULL | User's first name |
| `last_name` | VARCHAR(50) | NOT NULL | User's last name |
| `role` | ENUM | NOT NULL, DEFAULT 'dispatcher' | User role: admin, manager, dispatcher, driver |
| `status` | ENUM | NOT NULL, DEFAULT 'active' | Account status: active, inactive, suspended |
| `last_login` | DATETIME | NULL | Last successful login timestamp |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Account creation time |
| `updated_at` | TIMESTAMP | ON UPDATE | Last modification time |

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE INDEX on `username`
- UNIQUE INDEX on `email`
- INDEX on `status`

**Roles:**
- `admin` - Full system access
- `manager` - Management and reporting access
- `dispatcher` - Service request management
- `driver` - Driver-specific features

**Example:**
```sql
SELECT * FROM users WHERE role = 'admin' AND status = 'active';
```

---

### 2. customers

Stores customer information and contact details.

**Columns:**

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | Unique customer identifier |
| `first_name` | VARCHAR(50) | NOT NULL | Customer's first name |
| `last_name` | VARCHAR(50) | NOT NULL | Customer's last name |
| `email` | VARCHAR(100) | NOT NULL | Email address |
| `phone` | VARCHAR(20) | NOT NULL | Primary phone number |
| `emergency_contact` | VARCHAR(20) | NULL | Emergency contact number |
| `date_of_birth` | DATE | NULL | Date of birth |
| `address` | VARCHAR(255) | NOT NULL | Street address |
| `address2` | VARCHAR(255) | NULL | Additional address line |
| `city` | VARCHAR(100) | NOT NULL | City |
| `state` | VARCHAR(50) | NOT NULL | State/Province |
| `zip` | VARCHAR(20) | NOT NULL | ZIP/Postal code |
| `is_vip` | BOOLEAN | DEFAULT FALSE | VIP customer flag |
| `status` | ENUM | DEFAULT 'active' | active, inactive, suspended |
| `notes` | TEXT | NULL | Additional notes |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | ON UPDATE | Last update time |

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX on (`last_name`, `first_name`)
- INDEX on `email`
- INDEX on `phone`
- INDEX on `status`
- INDEX on `is_vip`

**VIP Customers:**
VIP customers (`is_vip` = TRUE) receive priority service and special handling.

**Example:**
```sql
-- Find VIP customers in California
SELECT * FROM customers 
WHERE is_vip = TRUE AND state = 'CA' AND status = 'active'
ORDER BY last_name, first_name;
```

---

### 3. customer_vehicles

Stores vehicle information for customers.

**Columns:**

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | Unique vehicle identifier |
| `customer_id` | INT UNSIGNED | FK, NOT NULL | Reference to customers table |
| `make` | VARCHAR(50) | NOT NULL | Vehicle manufacturer |
| `model` | VARCHAR(50) | NOT NULL | Vehicle model |
| `year` | INT | NOT NULL | Manufacturing year |
| `color` | VARCHAR(30) | NULL | Vehicle color |
| `license_plate` | VARCHAR(20) | NULL | License plate number |
| `vin` | VARCHAR(17) | NULL | Vehicle Identification Number |
| `is_primary` | BOOLEAN | DEFAULT FALSE | Primary vehicle flag |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | ON UPDATE | Last update time |

**Foreign Keys:**
- `customer_id` → `customers.id` (ON DELETE CASCADE)

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX on `customer_id`
- INDEX on `license_plate`

**Notes:**
- Each customer can have multiple vehicles
- One vehicle should be marked as primary (`is_primary` = TRUE)
- Deleting a customer cascades to delete their vehicles

**Example:**
```sql
-- Get all vehicles for a customer
SELECT v.* FROM customer_vehicles v
JOIN customers c ON v.customer_id = c.id
WHERE c.email = 'john.doe@example.com'
ORDER BY v.is_primary DESC, v.year DESC;
```

---

### 4. drivers

Stores driver information and real-time status.

**Columns:**

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | Unique driver identifier |
| `user_id` | INT UNSIGNED | FK, NULL | Reference to users table |
| `first_name` | VARCHAR(50) | NOT NULL | Driver's first name |
| `last_name` | VARCHAR(50) | NOT NULL | Driver's last name |
| `email` | VARCHAR(100) | NOT NULL | Email address |
| `phone` | VARCHAR(20) | NOT NULL | Phone number |
| `license_number` | VARCHAR(50) | NOT NULL | Driver's license number |
| `license_state` | VARCHAR(50) | NOT NULL | License issuing state |
| `license_expiry` | DATE | NOT NULL | License expiration date |
| `vehicle_info` | VARCHAR(255) | NULL | Driver's vehicle information |
| `status` | ENUM | DEFAULT 'offline' | available, busy, offline, on_break |
| `current_latitude` | DECIMAL(10, 8) | NULL | Current GPS latitude |
| `current_longitude` | DECIMAL(11, 8) | NULL | Current GPS longitude |
| `last_location_update` | DATETIME | NULL | Last GPS update time |
| `rating` | DECIMAL(3, 2) | DEFAULT 0.00 | Average rating (0-5) |
| `total_jobs` | INT | DEFAULT 0 | Total jobs assigned |
| `completed_jobs` | INT | DEFAULT 0 | Successfully completed jobs |
| `notes` | TEXT | NULL | Additional notes |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | ON UPDATE | Last update time |

**Foreign Keys:**
- `user_id` → `users.id` (ON DELETE SET NULL)

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX on `user_id`
- INDEX on `status`
- INDEX on `email`

**Driver Status:**
- `available` - Ready to accept jobs
- `busy` - Currently on a job
- `offline` - Not working
- `on_break` - On break, not available

**Example:**
```sql
-- Find available drivers with good ratings
SELECT * FROM drivers 
WHERE status = 'available' 
  AND rating >= 4.0
  AND current_latitude IS NOT NULL
ORDER BY rating DESC, completed_jobs DESC;
```

---

### 5. service_types

Defines available service types and pricing.

**Columns:**

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | Unique service type identifier |
| `name` | VARCHAR(100) | NOT NULL | Service name |
| `description` | TEXT | NULL | Service description |
| `base_price` | DECIMAL(10, 2) | DEFAULT 0.00 | Base price |
| `estimated_duration` | INT | DEFAULT 30 | Duration in minutes |
| `is_active` | BOOLEAN | DEFAULT TRUE | Active status |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | ON UPDATE | Last update time |

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX on `is_active`

**Common Service Types:**
- Jump Start
- Tire Change
- Fuel Delivery
- Lockout Service
- Towing
- Winch Out

**Example:**
```sql
-- Get active services
SELECT * FROM service_types 
WHERE is_active = TRUE 
ORDER BY name;
```

---

### 6. service_requests

Tracks service request lifecycle from creation to completion.

**Columns:**

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | Unique request identifier |
| `customer_id` | INT UNSIGNED | FK, NOT NULL | Reference to customers |
| `driver_id` | INT UNSIGNED | FK, NULL | Assigned driver |
| `service_type_id` | INT UNSIGNED | FK, NOT NULL | Type of service |
| `status` | ENUM | DEFAULT 'pending' | pending, assigned, in_progress, completed, cancelled |
| `priority` | ENUM | DEFAULT 'normal' | low, normal, high, emergency |
| `location_address` | VARCHAR(500) | NOT NULL | Service location address |
| `location_latitude` | DECIMAL(10, 8) | NULL | GPS latitude |
| `location_longitude` | DECIMAL(11, 8) | NULL | GPS longitude |
| `description` | TEXT | NULL | Request description |
| `customer_notes` | TEXT | NULL | Customer notes |
| `driver_notes` | TEXT | NULL | Driver notes |
| `estimated_cost` | DECIMAL(10, 2) | NULL | Estimated cost |
| `final_cost` | DECIMAL(10, 2) | NULL | Final charged amount |
| `customer_rating` | INT | NULL | Customer rating (1-5) |
| `customer_feedback` | TEXT | NULL | Customer feedback |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Request creation time |
| `assigned_at` | DATETIME | NULL | Driver assignment time |
| `started_at` | DATETIME | NULL | Service start time |
| `completed_at` | DATETIME | NULL | Completion time |
| `cancelled_at` | DATETIME | NULL | Cancellation time |
| `updated_at` | TIMESTAMP | ON UPDATE | Last update time |

**Foreign Keys:**
- `customer_id` → `customers.id` (ON DELETE CASCADE)
- `driver_id` → `drivers.id` (ON DELETE SET NULL)
- `service_type_id` → `service_types.id` (ON DELETE RESTRICT)

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX on `customer_id`
- INDEX on `driver_id`
- INDEX on `service_type_id`
- INDEX on `status`
- INDEX on `priority`
- INDEX on `created_at`

**Request Lifecycle:**
1. `pending` - Created, awaiting assignment
2. `assigned` - Driver assigned, en route
3. `in_progress` - Service in progress
4. `completed` - Service completed successfully
5. `cancelled` - Request cancelled

**Priority Levels:**
- `emergency` - Immediate attention required
- `high` - High priority
- `normal` - Standard priority
- `low` - Low priority

**Example:**
```sql
-- Get pending requests sorted by priority
SELECT sr.*, c.first_name, c.last_name, st.name as service_type
FROM service_requests sr
JOIN customers c ON sr.customer_id = c.id
JOIN service_types st ON sr.service_type_id = st.id
WHERE sr.status = 'pending'
ORDER BY 
  FIELD(sr.priority, 'emergency', 'high', 'normal', 'low'),
  sr.created_at ASC;
```

---

### 7. settings

System-wide configuration settings.

**Columns:**

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | Unique setting identifier |
| `key` | VARCHAR(100) | UNIQUE, NOT NULL | Setting key |
| `value` | TEXT | NULL | Setting value |
| `type` | ENUM | DEFAULT 'string' | string, integer, boolean, json |
| `description` | VARCHAR(255) | NULL | Setting description |
| `is_public` | BOOLEAN | DEFAULT FALSE | Public visibility flag |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation time |
| `updated_at` | TIMESTAMP | ON UPDATE | Last update time |

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE INDEX on `key`

**Common Settings:**
- `site_name` - Application name
- `enable_gps_tracking` - GPS tracking toggle
- `default_service_radius` - Default service radius in km
- `business_hours_start` - Business hours start time
- `business_hours_end` - Business hours end time
- `emergency_phone` - Emergency contact number

**Example:**
```sql
-- Get all public settings
SELECT `key`, `value` FROM settings 
WHERE is_public = TRUE;
```

---

### 8. activity_logs

Audit trail for system activities.

**Columns:**

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | Unique log identifier |
| `user_id` | INT UNSIGNED | FK, NULL | User who performed action |
| `action` | VARCHAR(100) | NOT NULL | Action type |
| `entity_type` | VARCHAR(50) | NULL | Affected entity type |
| `entity_id` | INT UNSIGNED | NULL | Affected entity ID |
| `details` | TEXT | NULL | Additional details (JSON) |
| `ip_address` | VARCHAR(45) | NULL | User's IP address |
| `user_agent` | VARCHAR(255) | NULL | User's browser/client |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Action timestamp |

**Foreign Keys:**
- `user_id` → `users.id` (ON DELETE SET NULL)

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX on `user_id`
- INDEX on `action`
- INDEX on `entity_type`
- INDEX on `created_at`

**Common Actions:**
- `user_login` - User logged in
- `user_logout` - User logged out
- `customer_created` - New customer created
- `request_created` - New service request
- `driver_assigned` - Driver assigned to request
- `request_completed` - Request completed

**Example:**
```sql
-- Get recent activity for a user
SELECT * FROM activity_logs 
WHERE user_id = 1 
ORDER BY created_at DESC 
LIMIT 50;
```

---

## Relationships

### One-to-Many Relationships

1. **customers → customer_vehicles**
   - One customer can have multiple vehicles
   
2. **customers → service_requests**
   - One customer can have multiple service requests
   
3. **drivers → service_requests**
   - One driver can have multiple assigned requests
   
4. **service_types → service_requests**
   - One service type can be used in multiple requests
   
5. **users → activity_logs**
   - One user can have multiple activity log entries

### Optional Relationships

1. **users ← drivers**
   - A driver may optionally have a user account
   - Allows drivers to login to the system

## Data Integrity

### Foreign Key Constraints

- **CASCADE**: Automatically delete related records (e.g., deleting customer deletes their vehicles)
- **SET NULL**: Set foreign key to NULL when parent is deleted (e.g., deleting user sets activity_logs.user_id to NULL)
- **RESTRICT**: Prevent deletion if related records exist (e.g., cannot delete service_type if used in requests)

### Indexes

Indexes improve query performance on frequently searched columns:
- Primary keys (automatic)
- Foreign keys
- Status fields
- Date fields
- Search fields (names, emails)

## Query Examples

### Complex Queries

#### Get driver performance metrics

```sql
SELECT 
    d.id,
    CONCAT(d.first_name, ' ', d.last_name) as driver_name,
    d.rating,
    d.total_jobs,
    d.completed_jobs,
    ROUND((d.completed_jobs / NULLIF(d.total_jobs, 0)) * 100, 2) as completion_rate,
    COUNT(CASE WHEN sr.status = 'completed' AND sr.completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as jobs_last_30_days,
    AVG(CASE WHEN sr.customer_rating IS NOT NULL THEN sr.customer_rating END) as avg_customer_rating
FROM drivers d
LEFT JOIN service_requests sr ON d.id = sr.driver_id
WHERE d.status != 'offline'
GROUP BY d.id
ORDER BY d.rating DESC, completion_rate DESC;
```

#### Get pending requests with customer and service type info

```sql
SELECT 
    sr.id as request_id,
    sr.created_at,
    sr.priority,
    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
    c.phone as customer_phone,
    st.name as service_type,
    sr.location_address,
    sr.description
FROM service_requests sr
JOIN customers c ON sr.customer_id = c.id
JOIN service_types st ON sr.service_type_id = st.id
WHERE sr.status = 'pending'
ORDER BY 
    FIELD(sr.priority, 'emergency', 'high', 'normal', 'low'),
    sr.created_at ASC;
```

#### Get customer service history

```sql
SELECT 
    c.id,
    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
    COUNT(sr.id) as total_requests,
    COUNT(CASE WHEN sr.status = 'completed' THEN 1 END) as completed_requests,
    COUNT(CASE WHEN sr.status = 'cancelled' THEN 1 END) as cancelled_requests,
    SUM(sr.final_cost) as total_spent,
    MAX(sr.created_at) as last_request_date,
    AVG(sr.customer_rating) as avg_rating_given
FROM customers c
LEFT JOIN service_requests sr ON c.id = sr.customer_id
WHERE c.status = 'active'
GROUP BY c.id
HAVING total_requests > 0
ORDER BY total_requests DESC;
```

## Backup and Maintenance

### Backup Strategy

```bash
# Full database backup
mysqldump -u root -p roadside_assistance > backup_$(date +%Y%m%d).sql

# Backup with compression
mysqldump -u root -p roadside_assistance | gzip > backup_$(date +%Y%m%d).sql.gz

# Restore from backup
mysql -u root -p roadside_assistance < backup_20241028.sql
```

### Maintenance Queries

```sql
-- Optimize all tables
OPTIMIZE TABLE users, customers, customer_vehicles, drivers, 
              service_types, service_requests, settings, activity_logs;

-- Check table integrity
CHECK TABLE service_requests;

-- Analyze tables for query optimization
ANALYZE TABLE service_requests, drivers, customers;
```

### Cleanup Old Data

```sql
-- Archive old completed requests (older than 1 year)
INSERT INTO service_requests_archive 
SELECT * FROM service_requests 
WHERE status = 'completed' AND completed_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Delete archived requests
DELETE FROM service_requests 
WHERE status = 'completed' AND completed_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Archive old activity logs (older than 6 months)
DELETE FROM activity_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
```

## Performance Optimization

### Query Optimization Tips

1. **Use indexes** on frequently queried columns
2. **Limit result sets** with LIMIT clause
3. **Avoid SELECT *** - specify columns needed
4. **Use JOINs efficiently** - join on indexed columns
5. **Cache frequent queries** in application layer

### Monitoring

```sql
-- Show slow queries
SHOW VARIABLES LIKE 'slow_query_log';
SHOW VARIABLES LIKE 'long_query_time';

-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- View table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size (MB)"
FROM information_schema.TABLES
WHERE table_schema = 'roadside_assistance'
ORDER BY (data_length + index_length) DESC;
```

---

**See Also:**
- [Developer Setup](../developer/SETUP.md)
- [API Documentation](../api/README.md)
- [Deployment Guide](../deployment/README.md)
