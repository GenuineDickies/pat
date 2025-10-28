# Driver Management Module - Implementation Guide

## Overview
This document describes the enhanced driver management features implemented for Patone v1.0, including certifications tracking, document management, availability scheduling, and workload balancing.

## Features Implemented

### 1. Driver Detail Pages
Enhanced driver information views with comprehensive metrics and real-time data.

#### Views Created:
- **driver_details.php** - Basic driver information with performance metrics
- **driver_dashboard.php** - Comprehensive dashboard with all driver data

#### Key Features:
- Personal and license information display
- GPS location tracking with map integration
- Performance metrics (last 30 days)
- Career statistics
- Quick status change actions
- Real-time availability status

### 2. Certification Management
Track and manage driver certifications with automated expiry monitoring.

#### Database Table: `driver_certifications`
```sql
Fields:
- id (Primary Key)
- driver_id (Foreign Key to drivers)
- certification_type (e.g., CDL, First Aid, Towing License)
- certification_number
- issuing_authority
- issue_date
- expiry_date
- status (active, expired, pending, suspended)
- document_path (optional file attachment)
- notes
```

#### Features:
- Add/edit/delete certifications
- Track multiple certification types per driver
- Automatic expiry alerts (30-day warning)
- Document attachment support
- Status tracking

#### Usage:
```php
// Add a certification
$driverModel->addCertification($driverId, [
    'certification_type' => 'CDL Class A',
    'certification_number' => 'CDL-123456',
    'issuing_authority' => 'State DMV',
    'issue_date' => '2023-01-15',
    'expiry_date' => '2028-01-15',
    'status' => 'active'
]);

// Get expiring certifications
$expiring = $driverModel->getExpiringCertifications(30); // Within 30 days
```

### 3. Document Management
Centralized storage for driver-related documents with version tracking.

#### Database Table: `driver_documents`
```sql
Fields:
- id (Primary Key)
- driver_id (Foreign Key to drivers)
- document_type (Insurance, Registration, Background Check, etc.)
- document_name
- file_path
- file_size
- mime_type
- expiry_date (optional)
- status (active, expired, pending_review)
- uploaded_by (Foreign Key to users)
- notes
```

#### Supported Document Types:
- Vehicle Insurance
- Vehicle Registration
- Background Check
- Drug Test Results
- Medical Certificate
- W-9 Form
- Emergency Contact Form
- Safety Training Certificate

#### Features:
- Upload documents (PDF, DOC, DOCX, JPG, PNG)
- Track document expiry
- View/download documents
- Document status workflow
- File size and type validation (max 10MB)

#### Usage:
```php
// Add a document
$driverModel->addDocument($driverId, [
    'document_type' => 'Vehicle Insurance',
    'document_name' => 'Insurance Certificate 2024',
    'file_path' => 'drivers/123/insurance_2024.pdf',
    'file_size' => 524288,
    'mime_type' => 'application/pdf',
    'expiry_date' => '2024-12-31',
    'status' => 'active',
    'uploaded_by' => $userId
]);
```

### 4. Availability Scheduling
Configure driver availability on a weekly schedule.

#### Database Table: `driver_availability_schedule`
```sql
Fields:
- id (Primary Key)
- driver_id (Foreign Key to drivers)
- day_of_week (0=Sunday, 1=Monday, ..., 6=Saturday)
- start_time
- end_time
- is_available (boolean)
- notes
```

#### Features:
- Day-by-day schedule configuration
- Multiple time slots per day
- Mark specific times as available/unavailable
- Schedule notes for special circumstances
- Real-time availability checking
- "Standard hours" preset (9 AM - 5 PM, Mon-Fri)

#### Usage:
```php
// Set availability schedule
$driverModel->setAvailabilitySchedule(
    $driverId,
    1, // Monday
    '09:00:00',
    '17:00:00',
    true, // is_available
    'Regular shift'
);

// Check if driver is scheduled to be available now
$isAvailable = $driverModel->isScheduledAvailable($driverId);
```

### 5. Workload Balancing
Monitor and distribute workload across the driver fleet.

#### Database Enhancements:
Added to `drivers` table:
- `current_workload` - Number of active requests
- `max_workload` - Maximum concurrent requests (default: 3)
- `availability_notes` - Notes about driver availability

#### Features:
- Track current workload vs maximum capacity
- Calculate utilization percentage
- Identify overloaded drivers (>=80% utilization)
- Identify underutilized drivers (<30% utilization)
- Visual workload distribution dashboard
- Capacity-based driver recommendations
- Configurable max workload per driver

#### Usage:
```php
// Get driver workload
$workload = $driverModel->getWorkload($driverId);
// Returns: ['current' => 2, 'max' => 3, 'available_capacity' => 1, 'utilization_percentage' => 66.67]

// Get workload distribution across all drivers
$distribution = $driverModel->getWorkloadDistribution();

// Get drivers with available capacity
$available = $driverModel->getDriversWithCapacity($latitude, $longitude, $maxDistance);

// Update max workload
$driverModel->setMaxWorkload($driverId, 5);

// Update current workload (automatic)
$driverModel->updateWorkload($driverId);
```

## Installation

### 1. Run Migration
```bash
cd database/migrations
php 002_driver_management_enhancements.php
```

This creates:
- `driver_certifications` table
- `driver_documents` table
- `driver_availability_schedule` table
- Adds workload columns to `drivers` table

### 2. Set Permissions
Ensure the uploads directory is writable:
```bash
chmod 755 uploads/
chmod 755 uploads/drivers/
```

### 3. Run Tests
```bash
cd tests
php DriverManagementTest.php
```

## API Routes

### Driver Details & Dashboard
- `GET /drivers/view/{id}` - View driver details
- `GET /drivers/dashboard/{id}` - Comprehensive dashboard

### Certification Management
- `GET /drivers/certifications/{id}` - List certifications
- `POST /drivers/addCertification/{id}` - Add certification
- `GET /drivers/deleteCertification/{driverId}/{certId}` - Delete certification

### Document Management
- `GET /drivers/documents/{id}` - List documents
- `POST /drivers/uploadDocument/{id}` - Upload document
- `GET /drivers/deleteDocument/{driverId}/{docId}` - Delete document

### Availability Scheduling
- `GET /drivers/schedule/{id}` - View/edit schedule
- `POST /drivers/saveSchedule/{id}` - Save schedule

### Workload Management
- `GET /drivers/workload/{id}` - View driver workload
- `GET /drivers/workload` - View workload distribution
- `POST /drivers/updateMaxWorkload/{id}` - Update max workload

## Usage Examples

### Certification Expiry Monitoring
```php
// Get certifications expiring in next 30 days
$expiring = $driverModel->getExpiringCertifications(30);

foreach ($expiring as $cert) {
    // Send reminder email
    sendEmail($cert['email'], 
        "Certification Expiry Reminder",
        "Your {$cert['certification_type']} expires on {$cert['expiry_date']}"
    );
}
```

### Workload-Based Dispatch
```php
// Find best driver for new request based on capacity
$customerLat = 40.7128;
$customerLng = -74.0060;
$maxDistance = 25; // miles

$availableDrivers = $driverModel->getDriversWithCapacity($customerLat, $customerLng, $maxDistance);

// Drivers are sorted by available_capacity DESC, rating DESC
$bestDriver = $availableDrivers[0] ?? null;

if ($bestDriver) {
    assignRequest($requestId, $bestDriver['id']);
    $driverModel->updateWorkload($bestDriver['id']);
}
```

### Scheduled Availability Check
```php
// Check if driver is scheduled to work now
if ($driverModel->isScheduledAvailable($driverId)) {
    // Driver is scheduled to be available
    // Send dispatch notification
    notifyDriver($driverId, "New request available in your area");
} else {
    // Driver is not scheduled to work
    // Don't disturb
}
```

## Best Practices

### Certification Management
1. Set up automated reminders for expiring certifications (30, 14, 7 days before)
2. Require document upload for all certifications
3. Regular audits of certification status
4. Immediate notification when certification expires

### Document Management
1. Implement document review workflow
2. Set expiry dates for all time-sensitive documents
3. Regular document refresh schedule
4. Secure storage with access logging

### Availability Scheduling
1. Encourage drivers to maintain up-to-date schedules
2. Allow schedule changes with advance notice
3. Consider time zones for multi-region operations
4. Regular schedule reviews with drivers

### Workload Balancing
1. Monitor utilization daily
2. Avoid assigning to drivers at >80% capacity
3. Adjust max_workload based on driver experience
4. Balance workload across team for fair distribution
5. Track metrics to optimize max_workload settings

## Performance Considerations

### Database Indexes
The migration creates indexes on:
- `driver_certifications.driver_id`
- `driver_certifications.expiry_date`
- `driver_documents.driver_id`
- `driver_documents.expiry_date`
- `driver_availability_schedule.driver_id`
- `driver_availability_schedule.day_of_week`

### Caching Recommendations
Consider caching:
- Driver availability schedules (TTL: 1 hour)
- Workload distribution data (TTL: 5 minutes)
- Active certifications list (TTL: 1 hour)

### File Storage
- Documents stored in `uploads/drivers/{driver_id}/`
- Consider implementing cloud storage (S3, etc.) for larger deployments
- Regular backups of document directory

## Security Considerations

1. **File Upload Security**
   - Validate file types and sizes
   - Scan uploaded files for malware
   - Store files outside web root if possible
   - Generate unique filenames

2. **Access Control**
   - Verify user permissions before showing/editing data
   - Log all document access
   - Restrict document downloads to authorized users

3. **Data Privacy**
   - Encrypt sensitive documents at rest
   - Implement data retention policies
   - Comply with data protection regulations (GDPR, etc.)

## Troubleshooting

### Migration Issues
If migration fails:
```bash
# Check if tables already exist
mysql -u user -p database -e "SHOW TABLES LIKE 'driver_%'"

# Rollback if needed
php 002_driver_management_enhancements.php down
```

### File Upload Issues
- Check directory permissions
- Verify PHP upload_max_filesize setting
- Check disk space

### Test Failures
- Ensure database is configured in config.php
- Verify migrations have been run
- Check for existing test data conflicts

## Support

For issues or questions:
- Check test results: `php tests/DriverManagementTest.php`
- Review error logs in `logs/` directory
- Verify database structure matches migration

---

**Version**: 1.0  
**Last Updated**: 2024  
**Status**: Production Ready
