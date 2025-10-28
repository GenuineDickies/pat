# Dispatch System - Quick Start Guide

## Installation (5 minutes)

### Step 1: Run Database Migration
```bash
cd database/migrations
php 002_dispatch_system.php
```

Expected output:
```
Starting dispatch system migration...
Executing: CREATE TABLE IF NOT EXISTS `dispatch_queue`...
Executing: CREATE TABLE IF NOT EXISTS `dispatch_history`...
Executing: CREATE TABLE IF NOT EXISTS `driver_certifications`...
Executing: CREATE TABLE IF NOT EXISTS `driver_performance`...
Dispatch system migration completed successfully!
```

### Step 2: Verify Installation
```bash
cd tests
php DispatchSystemTest.php
```

Expected output:
```
===========================================
Dispatch System Test Suite
===========================================

✓ PASS: DispatchQueue model loaded successfully
✓ PASS: DispatchAlgorithm model loaded successfully
...
All tests passed successfully!
```

### Step 3: Access Dashboard
1. Log in to your admin panel
2. Navigate to: `/dispatch`
3. You should see the Dispatch Dashboard

## Basic Usage

### Scenario 1: Auto-Dispatch a Request

1. **Create a service request** (via `/requests/add`)
   - Customer: Select existing customer
   - Service Type: Select service
   - Location: Enter address and coordinates
   - Priority: Select priority level

2. **Add to dispatch queue**
   - Go to Dispatch Dashboard (`/dispatch`)
   - Or use API:
   ```javascript
   fetch('/dispatch/enqueue', {
     method: 'POST',
     body: new FormData([
       ['request_id', YOUR_REQUEST_ID],
       ['priority', 'normal']
     ])
   });
   ```

3. **Auto-dispatch**
   - Click "🚀 Auto-Dispatch Next Request" button
   - System will find the best driver and assign automatically
   - View results in dashboard

### Scenario 2: Manual Dispatch

1. **View pending requests** in Dispatch Dashboard

2. **Click "🔍 Find Driver"** for a request
   - System shows best driver with score breakdown
   - Shows: proximity, workload, rating, availability scores

3. **Confirm or choose different driver**
   - Click "✋ Manual Assign" to choose a different driver
   - Enter driver ID
   - Confirm assignment

### Scenario 3: Emergency Request

1. **Handle emergency request**
   ```javascript
   const formData = new FormData();
   formData.append('request_id', REQUEST_ID);
   
   fetch('/dispatch/handleEmergency', {
     method: 'POST',
     body: formData
   });
   ```

2. **System will**:
   - Set priority to "emergency"
   - Add to front of queue
   - Attempt immediate dispatch
   - If no drivers available, queue with highest priority

## API Quick Reference

### Auto-Dispatch
```bash
POST /dispatch/autoDispatch
```

### Manual Dispatch
```bash
POST /dispatch/manualDispatch
Content: request_id=123&driver_id=45
```

### Find Best Driver
```bash
GET /dispatch/findDriver/{requestId}
```

### Queue Stats
```bash
GET /dispatch/queueStats
```

### View History
```bash
GET /dispatch/history?limit=50&offset=0
```

## Testing Checklist

- [ ] Database migration ran successfully
- [ ] Test suite passes all tests
- [ ] Can access dispatch dashboard
- [ ] Can view pending requests in queue
- [ ] Auto-dispatch works and assigns driver
- [ ] Manual dispatch works
- [ ] Emergency handling works
- [ ] Dispatch history is recorded
- [ ] Queue statistics are accurate

## Common Tasks

### Add Request to Queue (Code)
```php
$queueModel = new DispatchQueue();
$queueModel->enqueue($requestId, 'high');
```

### Find Best Driver (Code)
```php
$algorithm = new DispatchAlgorithm();
$result = $algorithm->findBestDriver($requestId);

if ($result) {
    echo "Best driver: " . $result['driver']['first_name'];
    echo "Score: " . $result['score'];
}
```

### Dispatch Request (Code)
```php
$algorithm = new DispatchAlgorithm();
$algorithm->dispatch($requestId, $driverId, $automated = true);
```

### Get Queue Stats (Code)
```php
$queueModel = new DispatchQueue();
$stats = $queueModel->getStats();
print_r($stats);
```

## Tips for Success

1. **Keep driver locations updated**
   - Update driver GPS coordinates regularly
   - Stale locations reduce accuracy

2. **Monitor queue size**
   - Large queues may indicate insufficient drivers
   - Review queue stats regularly

3. **Review dispatch history**
   - Identify patterns
   - Optimize algorithm weights if needed

4. **Use appropriate priorities**
   - Emergency: Life-threatening situations
   - High: Unsafe conditions, road blockages
   - Normal: Standard service requests
   - Low: Non-urgent, scheduled maintenance

5. **Manual override when needed**
   - Use when you have special knowledge
   - Customer preferences
   - Driver specializations

## Troubleshooting

### "No available drivers found"
- Check driver availability status
- Verify drivers have GPS coordinates
- Ensure drivers are within 50km range

### "Request not found"
- Verify request ID is correct
- Check request status is "pending"

### Dashboard not loading
- Clear browser cache
- Check PHP error logs
- Verify routes are registered

### Tests failing
- Ensure database is set up
- Check database configuration
- Run initial migration first

## Next Steps

After basic setup:
1. Configure driver locations
2. Test with real service requests
3. Monitor dispatch performance
4. Adjust algorithm weights if needed
5. Train staff on manual override procedures

## Support

For detailed documentation:
- **API Reference**: `DISPATCH_API.md`
- **Full Documentation**: `DISPATCH_README.md`
- **Implementation Guide**: `IMPLEMENTATION.md`

For issues:
- Check application logs
- Review dispatch history
- Run test suite
- Contact system administrator
