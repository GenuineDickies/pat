# Service Request Management System - Implementation Guide

## Overview
The Service Request Management System provides comprehensive tracking and management of roadside assistance service requests. This implementation includes request creation, status tracking, driver assignment, notifications, and complete audit trails.

## Features Implemented

### 1. Request Details View (`request_details.php`)
A comprehensive view that displays:
- Customer information (name, email, phone)
- Driver assignment details
- Service information (type, vehicle, pricing)
- Location details with map integration
- Complete timeline of request lifecycle
- Notes and communication logs
- Status management controls
- Rating system for completed requests

### 2. Request History Tracking (`RequestHistory` model)
Maintains a complete audit trail of all request activities:
- Status changes
- Driver assignments
- Notes additions
- Completion events
- Cancellations
- Timestamps and user attribution

Key methods:
- `addEntry($requestId, $actionType, $oldValue, $newValue, $notes)` - Add history entry
- `getByRequest($requestId)` - Get complete history for a request
- `logStatusChange($requestId, $oldStatus, $newStatus, $notes)` - Log status changes
- `logDriverAssignment($requestId, $driverId, $driverName)` - Log driver assignments
- `logCompletion($requestId, $finalCost)` - Log request completion
- `logCancellation($requestId, $reason)` - Log cancellation with reason

### 3. Communication Logs (`RequestCommunication` model)
Tracks all communications related to service requests:
- Phone calls (inbound/outbound)
- Emails
- SMS messages
- Internal notes
- System messages

Key methods:
- `addLog($requestId, $type, $message, $direction, $subject, $recipient)` - Add communication log
- `getByRequest($requestId)` - Get all communications for a request
- `addNote($requestId, $message)` - Add internal note
- `logEmail($requestId, $recipient, $subject, $message)` - Log email sent
- `logSMS($requestId, $recipient, $message)` - Log SMS sent
- `logCall($requestId, $direction, $notes)` - Log phone call
- `logSystem($requestId, $message)` - Log system message

### 4. Notification System (`Notification` model)
Real-time notifications for system events:
- Request assignments
- Status changes
- Completions
- Cancellations

Key methods:
- `createNotification($userId, $type, $title, $message, $relatedType, $relatedId)` - Create notification
- `getUnread($userId)` - Get unread notifications
- `getByUser($userId, $limit)` - Get all notifications for user
- `markAsRead($id)` - Mark notification as read
- `markAllAsRead($userId)` - Mark all as read
- `getUnreadCount($userId)` - Get count of unread notifications
- `notifyRequestAssigned($userId, $requestId, $requestDetails)` - Notify on assignment
- `notifyStatusChanged($userId, $requestId, $oldStatus, $newStatus)` - Notify on status change
- `notifyRequestCompleted($userId, $requestId)` - Notify on completion

### 5. Auto-Dispatch Algorithm
Intelligent driver assignment based on:
- Driver availability
- Rating/performance
- Completed jobs history
- (Future enhancements: distance, specialization, workload)

Method: `RequestController::autoDispatch()` - Auto-assigns pending requests to best available drivers

### 6. Enhanced Request Controller
Updated `RequestController` with:
- Full history tracking integration
- Notification triggers on all status changes
- Enhanced driver assignment with notifications
- Communication logging for all actions
- Auto-dispatch functionality

## Database Schema

### `request_history` table
```sql
- id (INT, PRIMARY KEY)
- request_id (INT, FOREIGN KEY)
- user_id (INT, FOREIGN KEY)
- action_type (VARCHAR) - e.g., 'status_change', 'driver_assigned', 'note_added'
- old_value (VARCHAR)
- new_value (VARCHAR)
- notes (TEXT)
- created_at (TIMESTAMP)
```

### `request_communications` table
```sql
- id (INT, PRIMARY KEY)
- request_id (INT, FOREIGN KEY)
- user_id (INT, FOREIGN KEY)
- communication_type (ENUM) - 'call', 'email', 'sms', 'note', 'system'
- direction (ENUM) - 'inbound', 'outbound', 'internal'
- subject (VARCHAR)
- message (TEXT)
- recipient (VARCHAR)
- status (ENUM) - 'pending', 'sent', 'delivered', 'failed'
- created_at (TIMESTAMP)
```

### `notifications` table
```sql
- id (INT, PRIMARY KEY)
- user_id (INT, FOREIGN KEY)
- type (VARCHAR) - e.g., 'request_assigned', 'status_changed'
- title (VARCHAR)
- message (TEXT)
- related_type (VARCHAR) - 'request', 'driver', 'customer'
- related_id (INT)
- is_read (BOOLEAN)
- read_at (DATETIME)
- created_at (TIMESTAMP)
```

## API Endpoints

### Request Management
- `POST /api/requests/{id}/assign-driver` - Assign driver to request
- `POST /api/requests/{id}/status` - Update request status
- `POST /api/requests/{id}/rating` - Add customer rating
- `POST /requests/complete/{id}` - Mark request as completed
- `POST /requests/cancel/{id}` - Cancel request
- `POST /requests/auto-dispatch` - Auto-dispatch pending requests

## Usage Examples

### Creating a Request with History Tracking
```php
// Create request
$requestId = $requestModel->create($data);

// Log history
$historyModel->addEntry($requestId, 'created', null, null, 'Request created');

// Log communication
$communicationModel->logSystem($requestId, 'Service request created');
```

### Assigning a Driver
```php
// Assign driver
$requestModel->assignDriver($requestId, $driverId);

// Update driver status
$driverModel->updateStatus($driverId, 'busy');

// Log history
$historyModel->logDriverAssignment($requestId, $driverId, $driverName);

// Send notification
$notificationModel->notifyRequestAssigned($driverUserId, $requestId, $details);
```

### Updating Request Status
```php
// Get current status
$request = $requestModel->getById($requestId);
$oldStatus = $request['status'];

// Update status
$requestModel->updateStatus($requestId, $newStatus, $notes);

// Log history
$historyModel->logStatusChange($requestId, $oldStatus, $newStatus, $notes);

// Send notification
$notificationModel->notifyStatusChanged($userId, $requestId, $oldStatus, $newStatus);
```

## Testing

Run the static tests to verify all components are properly implemented:
```bash
php tests/ServiceRequestStaticTest.php
```

This validates:
- File existence
- Class definitions
- Method signatures
- No database connection required

## Future Enhancements
1. Real-time GPS tracking integration
2. Distance-based dispatch algorithm
3. Email/SMS notification delivery
4. Mobile app integration
5. Advanced analytics and reporting
6. Customer self-service portal
7. Driver mobile app
8. Webhook integrations

## Security Considerations
- All database operations use prepared statements
- CSRF token validation on all POST requests
- Permission checks on sensitive operations
- Activity logging for audit trails
- Input sanitization throughout

## Performance Optimization
- Indexed database columns for fast queries
- Efficient SQL joins for related data
- Pagination on list views
- Lazy loading of history/communications
