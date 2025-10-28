# Database Schema

This document provides a comprehensive overview of the Patone Roadside Assistance platform database schema.

## Overview

- **Database Name**: `roadside_assistance` (production), `roadside_assistance_test` (testing)
- **Character Set**: UTF8MB4 with `utf8mb4_unicode_ci` collation
- **Engine**: InnoDB (supports transactions and foreign keys)

## Source Files

- `database/schema.sql` — Complete schema with sample data
- `database/migrations/` — Future migrations (if implemented)
- `database/sample_customers_import.csv` — Sample customer import data

## Core Tables

### Users (`users`)
**Purpose**: System users (admin, managers, dispatchers, drivers)

| Column | Type | Notes |
|--------|------|-------|
| `id` | INT UNSIGNED AUTO_INCREMENT | Primary key |
| `username` | VARCHAR(50) UNIQUE | Login username |
| `email` | VARCHAR(100) UNIQUE | Email address |
| `password` | VARCHAR(255) | Hashed password |
| `first_name` | VARCHAR(50) | User's first name |
| `last_name` | VARCHAR(50) | User's last name |
| `role` | ENUM | 'admin', 'manager', 'dispatcher', 'driver' |
| `status` | ENUM | 'active', 'inactive', 'suspended' |
| `last_login` | DATETIME | Last login timestamp |

**Indexes**: username, email, status

### Customers (`customers`)
**Purpose**: Customer profiles and contact information

| Column | Type | Notes |
|--------|------|-------|
| `id` | INT UNSIGNED AUTO_INCREMENT | Primary key |
| `first_name` | VARCHAR(50) | Customer's first name |
| `last_name` | VARCHAR(50) | Customer's last name |
| `email` | VARCHAR(100) | Email address |
| `phone` | VARCHAR(20) | Primary phone number |
| `emergency_contact` | VARCHAR(20) | Emergency contact number |
| `address` | VARCHAR(255) | Street address |
| `city` | VARCHAR(100) | City |
| `state` | VARCHAR(50) | State/province |
| `zip` | VARCHAR(20) | Postal code |
| `is_vip` | BOOLEAN | VIP customer status |
| `status` | ENUM | 'active', 'inactive', 'suspended' |

**Indexes**: name (last_name, first_name), email, phone, status, is_vip

### Customer Vehicles (`customer_vehicles`)
**Purpose**: Vehicle information for each customer

| Column | Type | Notes |
|--------|------|-------|
| `id` | INT UNSIGNED AUTO_INCREMENT | Primary key |
| `customer_id` | INT UNSIGNED | FK to customers.id |
| `make` | VARCHAR(50) | Vehicle make |
| `model` | VARCHAR(50) | Vehicle model |
| `year` | INT | Model year |
| `license_plate` | VARCHAR(20) | License plate number |
| `vin` | VARCHAR(17) | Vehicle identification number |
| `is_primary` | BOOLEAN | Primary vehicle for customer |

**Foreign Keys**: customer_id → customers(id) ON DELETE CASCADE

### Drivers (`drivers`)
**Purpose**: Driver profiles, status, and location

| Column | Type | Notes |
|--------|------|-------|
| `id` | INT UNSIGNED AUTO_INCREMENT | Primary key |
| `user_id` | INT UNSIGNED | FK to users.id (optional) |
| `license_number` | VARCHAR(50) | Driver's license number |
| `license_state` | VARCHAR(50) | Issuing state |
| `license_expiry` | DATE | License expiration |
| `status` | ENUM | 'available', 'busy', 'offline', 'on_break' |
| `current_latitude` | DECIMAL(10, 8) | GPS latitude |
| `current_longitude` | DECIMAL(11, 8) | GPS longitude |
| `last_location_update` | DATETIME | Last GPS update |
| `rating` | DECIMAL(3, 2) | Driver rating (0.00-5.00) |
| `total_jobs` | INT | Total jobs assigned |
| `completed_jobs` | INT | Successfully completed jobs |

**Foreign Keys**: user_id → users(id) ON DELETE SET NULL  
**Indexes**: name, status, location (lat/lng)

### Service Types (`service_types`)
**Purpose**: Catalog of available services

| Column | Type | Notes |
|--------|------|-------|
| `id` | INT UNSIGNED AUTO_INCREMENT | Primary key |
| `name` | VARCHAR(100) | Service name |
| `description` | TEXT | Service description |
| `base_price` | DECIMAL(10, 2) | Base price |
| `estimated_duration` | INT | Duration in minutes |
| `is_active` | BOOLEAN | Active/inactive status |
| `priority` | INT | Service priority level |

**Default Services**: Flat Tire Change, Jump Start, Fuel Delivery, Lockout Service, Towing, Winch Out, Battery Replacement, Minor Repair

### Service Requests (`service_requests`)
**Purpose**: Core table for service requests/jobs

| Column | Type | Notes |
|--------|------|-------|
| `id` | INT UNSIGNED AUTO_INCREMENT | Primary key |
| `customer_id` | INT UNSIGNED | FK to customers.id |
| `driver_id` | INT UNSIGNED | FK to drivers.id (nullable) |
| `service_type_id` | INT UNSIGNED | FK to service_types.id |
| `vehicle_id` | INT UNSIGNED | FK to customer_vehicles.id |
| `status` | ENUM | 'pending', 'assigned', 'in_progress', 'completed', 'cancelled' |
| `priority` | ENUM | 'low', 'normal', 'high', 'emergency' |
| `location_address` | VARCHAR(255) | Service location address |
| `location_latitude` | DECIMAL(10, 8) | GPS coordinates |
| `location_longitude` | DECIMAL(11, 8) | GPS coordinates |
| `estimated_cost` | DECIMAL(10, 2) | Estimated cost |
| `final_cost` | DECIMAL(10, 2) | Actual final cost |
| `assigned_at` | DATETIME | When assigned to driver |
| `started_at` | DATETIME | When service started |
| `completed_at` | DATETIME | When service completed |
| `rating` | INT | Customer rating (1-5) |

**Foreign Keys**:
- customer_id → customers(id) ON DELETE CASCADE
- driver_id → drivers(id) ON DELETE SET NULL
- service_type_id → service_types(id) ON DELETE RESTRICT
- vehicle_id → customer_vehicles(id) ON DELETE SET NULL

## Security & Audit Tables

### Activity Logs (`activity_logs`)
**Purpose**: Audit trail for user actions

| Column | Type | Notes |
|--------|------|-------|
| `user_id` | INT UNSIGNED | FK to users.id |
| `action` | VARCHAR(100) | Action performed |
| `entity_type` | VARCHAR(50) | Type of entity modified |
| `entity_id` | INT UNSIGNED | ID of entity modified |
| `ip_address` | VARCHAR(45) | User's IP address |

### Login Attempts (`login_attempts`)
**Purpose**: Security monitoring for login attempts

| Column | Type | Notes |
|--------|------|-------|
| `username` | VARCHAR(100) | Attempted username |
| `ip_address` | VARCHAR(45) | Source IP |
| `success` | BOOLEAN | Login success/failure |

### User Sessions (`user_sessions`)
**Purpose**: Active user session management

| Column | Type | Notes |
|--------|------|-------|
| `user_id` | INT UNSIGNED | FK to users.id |
| `session_token` | VARCHAR(255) UNIQUE | Session token |
| `expires_at` | DATETIME | Session expiration |

## Configuration Tables

### Settings (`settings`)
**Purpose**: System configuration key-value pairs

| Column | Type | Notes |
|--------|------|-------|
| `setting_key` | VARCHAR(100) UNIQUE | Configuration key |
| `setting_value` | TEXT | Configuration value |
| `setting_type` | ENUM | 'string', 'integer', 'boolean', 'json' |
| `is_public` | BOOLEAN | Public/private setting |

**Default Settings**: site_name, max_dispatch_distance, business_hours, GPS tracking, notifications

### Reports (`reports`)
**Purpose**: Generated report metadata and file tracking

| Column | Type | Notes |
|--------|------|-------|
| `report_type` | VARCHAR(50) | Type of report |
| `generated_by` | INT UNSIGNED | FK to users.id |
| `parameters` | TEXT | JSON encoded parameters |
| `file_path` | VARCHAR(255) | Path to generated file |
| `status` | ENUM | 'pending', 'processing', 'completed', 'failed' |

## Database Operations

### Initial Setup
```sql
-- Create databases
CREATE DATABASE roadside_assistance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE roadside_assistance_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import schema
mysql -u root -p roadside_assistance < database/schema.sql
mysql -u root -p roadside_assistance_test < database/schema.sql
```

### Common Queries

**Get active service requests**:
```sql
SELECT sr.id, c.first_name, c.last_name, st.name as service_type, sr.status, sr.created_at
FROM service_requests sr
JOIN customers c ON sr.customer_id = c.id
JOIN service_types st ON sr.service_type_id = st.id
WHERE sr.status IN ('pending', 'assigned', 'in_progress')
ORDER BY sr.created_at DESC;
```

**Driver performance**:
```sql
SELECT d.first_name, d.last_name, d.rating, d.total_jobs, d.completed_jobs,
       (d.completed_jobs / NULLIF(d.total_jobs, 0) * 100) as completion_rate
FROM drivers d
WHERE d.status = 'available'
ORDER BY d.rating DESC;
```

**Customer service history**:
```sql
SELECT sr.id, st.name, sr.status, sr.created_at, sr.final_cost, sr.rating
FROM service_requests sr
JOIN service_types st ON sr.service_type_id = st.id
WHERE sr.customer_id = ? 
ORDER BY sr.created_at DESC;
```

## Schema Maintenance

### Backup
```bash
# Full backup with data
mysqldump -u root -p roadside_assistance > backup_$(date +%Y%m%d).sql

# Schema only
mysqldump --no-data -u root -p roadside_assistance > schema_backup.sql
```

### Migrations
- Keep `database/schema.sql` as the canonical source
- For production updates, create migration scripts in `database/migrations/`
- Test all schema changes against `roadside_assistance_test` first

### Performance Monitoring
- Monitor slow query log for optimization opportunities
- Key indexes are already in place for common queries
- Consider partitioning `activity_logs` and `login_attempts` by date for large datasets

## Data Import/Export

### Sample Data Import
```bash
# Import sample customers (if CSV import script exists)
mysql -u root -p roadside_assistance -e "
LOAD DATA LOCAL INFILE 'database/sample_customers_import.csv'
INTO TABLE customers
FIELDS TERMINATED BY ','
ENCLOSED BY '\"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;"
```

### Regular Maintenance
- Archive old activity logs (older than 1 year)
- Clean up expired user sessions
- Monitor and optimize table sizes and indexes
