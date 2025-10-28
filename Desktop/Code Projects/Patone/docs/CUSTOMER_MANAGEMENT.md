# Customer Management System Documentation

## Overview
The Customer Management System provides comprehensive tools for managing customer information, vehicles, service history, and segmentation.

## Features Implemented

### 1. Customer Listing
- **Location**: `frontend/pages/customers.php`
- **Features**:
  - Paginated customer list with DataTables
  - Search by name, email, or phone
  - Filter by status and state
  - Bulk selection and actions
  - Quick actions (view, edit, delete)

### 2. Customer Detail View
- **Location**: `frontend/pages/customer_details.php`
- **Features**:
  - Complete customer profile display
  - Contact information with click-to-call/email
  - Full address with Google Maps integration
  - Vehicle information cards
  - Service history table
  - Quick links to edit and create new requests

### 3. Add/Edit Customer
- **Location**: `frontend/pages/customer_form.php`
- **Features**:
  - Complete form validation
  - Support for multiple vehicles
  - Dynamic vehicle form addition/removal
  - Phone number formatting
  - VIP customer designation
  - Status management

### 4. CSV Import
- **Endpoint**: `POST /customers/import`
- **Features**:
  - Bulk customer import from CSV
  - Duplicate detection by email
  - Required field validation
  - Error reporting
  - Skip duplicates option
  
**CSV Format**:
```csv
First Name,Last Name,Email,Phone,Address,City,State,ZIP,VIP,Status,Notes
```

**Sample File**: `database/sample_customers_import.csv`

### 5. CSV Export
- **Endpoint**: `GET /customers/export`
- **Features**:
  - Export all customers or filtered results
  - Export selected customers only
  - UTF-8 BOM for Excel compatibility
  - All customer fields included
  - Service request count included

**Query Parameters**:
- `ids` - Comma-separated customer IDs
- `search` - Search term
- `status` - Filter by status
- `state` - Filter by state

### 6. Customer Segmentation (Tags)
- **Database**: `customer_tags`, `customer_tag_assignments`
- **Features**:
  - Tag customers for segmentation
  - Pre-defined tags: High Priority, Frequent User, New Customer, Premium, Corporate, Needs Follow-up
  - Color-coded tags for visual identification
  - Query customers by tag

**Default Tags**:
- High Priority (Red) - High priority customers requiring immediate attention
- Frequent User (Blue) - Customers who frequently use services
- New Customer (Green) - Recently registered customers
- Premium (Yellow) - Premium service customers
- Corporate (Purple) - Corporate account customers
- Needs Follow-up (Orange) - Customers requiring follow-up

### 7. Activity Tracking
- **Feature**: Customer activity log
- **Implementation**: `getActivityLog()` method in Customer model
- Tracks all customer-related activities (add, edit, delete, service requests)

## API Endpoints

### Customer Routes
```
GET    /customers              - List all customers
GET    /customers/add          - Show add customer form
POST   /customers/add          - Process add customer
GET    /customers/edit/{id}    - Show edit customer form
POST   /customers/edit/{id}    - Process edit customer
GET    /customers/delete/{id}  - Delete customer
GET    /customers/view/{id}    - View customer details
GET    /customers/{id}         - View customer details (alternative)
GET    /customers/export       - Export customers to CSV
POST   /customers/import       - Import customers from CSV
GET    /customers/tags         - Get all customer tags
```

## Database Schema

### customer_tags
```sql
CREATE TABLE `customer_tags` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `color` VARCHAR(7) DEFAULT '#6c757d',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### customer_tag_assignments
```sql
CREATE TABLE `customer_tag_assignments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `customer_tags`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_customer_tag` (`customer_id`, `tag_id`)
);
```

## Customer Model Methods

### Core CRUD
- `getById($id)` - Get customer by ID
- `getByEmail($email)` - Get customer by email
- `getAll($limit, $offset, $search, $filters)` - Get all customers with pagination
- `create($data)` - Create new customer
- `update($id, $data)` - Update customer
- `delete($id)` - Delete customer

### Related Data
- `getVehicles($customerId)` - Get customer vehicles
- `getServiceHistory($customerId, $limit)` - Get service history
- `search($query, $limit)` - Search customers
- `getStats()` - Get customer statistics

### Tags & Segmentation
- `getTags($customerId)` - Get customer tags
- `addTag($customerId, $tagId)` - Add tag to customer
- `removeTag($customerId, $tagId)` - Remove tag from customer
- `getByTag($tagId, $limit, $offset)` - Get customers by tag

### Activity Tracking
- `getActivityLog($customerId, $limit)` - Get customer activity log

## Usage Examples

### Import Customers
1. Navigate to Customers page
2. Click "Import" button
3. Select CSV file with proper format
4. Choose whether to skip duplicates
5. Click "Import"

### Export Customers
1. Navigate to Customers page
2. (Optional) Select specific customers
3. (Optional) Apply filters
4. Click "Export" button
5. CSV file will download automatically

### View Customer Details
1. Navigate to Customers page
2. Click the eye icon on any customer row
3. View complete customer profile with service history

### Tag Management
Tags can be assigned programmatically using the Customer model:
```php
$customer = new Customer();
$customer->addTag($customerId, $tagId);
$tags = $customer->getTags($customerId);
```

## Security Features
- CSRF token validation on all forms
- Input sanitization on all data
- SQL injection prevention via parameterized queries
- Permission-based access control
- Activity logging for audit trail

## Future Enhancements
- Bulk SMS/Email functionality
- Advanced tag management UI
- Customer merge functionality
- Custom fields support
- Integration with payment systems
- Customer portal access
