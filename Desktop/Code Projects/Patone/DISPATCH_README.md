# Automated Dispatch System

## Overview
The Automated Dispatch System is an intelligent driver assignment system that automatically matches service requests with the best available drivers based on multiple factors including proximity, workload, driver rating, and availability.

## Features

### Core Functionality
- ✅ **Priority-based Request Queuing**: Emergency, high, normal, and low priority levels
- ✅ **Proximity-based Driver Selection**: Uses Haversine formula for accurate distance calculations
- ✅ **Load Balancing**: Considers driver workload when assigning requests
- ✅ **Availability Checking**: Verifies driver status and location freshness
- ✅ **Emergency Request Handling**: Immediate dispatch attempt for emergency requests
- ✅ **Manual Override Capability**: Dispatchers can manually assign drivers
- ✅ **Driver Performance Scoring**: Multi-factor scoring algorithm

### Dispatch Algorithm
The system uses a weighted scoring algorithm to find the best driver:

- **Proximity (40%)**: Distance from driver to request location
- **Workload (25%)**: Number of active requests assigned to driver
- **Rating (20%)**: Driver's performance rating (0-5 stars)
- **Availability (15%)**: Driver status and location update freshness

### Technical Components
- `DispatchQueue` model - Priority queue management
- `DispatchAlgorithm` model - Intelligent driver selection
- `DispatchController` - API endpoints and operations
- Dispatch dashboard - Real-time monitoring interface
- Database migrations - Schema for queue and history tracking

## Installation

### 1. Run Database Migration
```bash
cd database/migrations
php 002_dispatch_system.php
```

This will create the following tables:
- `dispatch_queue` - Active dispatch queue
- `dispatch_history` - Historical dispatch records
- `driver_certifications` - Driver skills/certifications
- `driver_performance` - Performance metrics tracking

### 2. Verify Routes
Routes are automatically registered in `index.php`. Verify they're present:
```php
GET  /dispatch                          - Dispatch dashboard
POST /dispatch/autoDispatch             - Auto-dispatch next request
POST /dispatch/manualDispatch           - Manual driver assignment
GET  /dispatch/findDriver/{id}          - Find best driver for request
GET  /dispatch/getDriverOptions/{id}    - Get scored driver list
POST /dispatch/enqueue                  - Add request to queue
POST /dispatch/handleEmergency          - Handle emergency request
GET  /dispatch/history                  - View dispatch history
GET  /dispatch/queueStats               - Get queue statistics
```

### 3. Access Dispatch Dashboard
Navigate to: `http://your-site.com/dispatch`

## Usage

### Auto-Dispatch Workflow

1. **Add Request to Queue**
   ```javascript
   fetch('/dispatch/enqueue', {
     method: 'POST',
     body: new FormData([
       ['request_id', 123],
       ['priority', 'high']
     ])
   });
   ```

2. **Auto-Dispatch**
   - Click "Auto-Dispatch Next Request" button
   - Or call API: `POST /dispatch/autoDispatch`
   - System finds best driver and assigns automatically

3. **Monitor Results**
   - View in dispatch dashboard
   - Check dispatch history
   - Review queue statistics

### Manual Dispatch Workflow

1. **Find Best Driver**
   ```javascript
   fetch('/dispatch/findDriver/123')
     .then(res => res.json())
     .then(data => {
       console.log('Best driver:', data.data.driver_name);
       console.log('Score:', data.data.score);
     });
   ```

2. **Manual Assignment**
   ```javascript
   const formData = new FormData();
   formData.append('request_id', 123);
   formData.append('driver_id', 45);
   
   fetch('/dispatch/manualDispatch', {
     method: 'POST',
     body: formData
   });
   ```

### Emergency Request Handling

```javascript
const formData = new FormData();
formData.append('request_id', 123);

fetch('/dispatch/handleEmergency', {
  method: 'POST',
  body: formData
})
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      if (data.queued) {
        alert('Queued with emergency priority');
      } else {
        alert('Dispatched immediately to: ' + data.driver);
      }
    }
  });
```

## Scoring Algorithm

### Proximity Score
- Uses Haversine formula for accurate distance calculation
- 0 km = 100 points
- 50+ km = 0 points
- Linear interpolation between

### Workload Score
- Based on number of active requests
- 0 active = 100 points
- 5+ active = 0 points

### Rating Score
- Converts 0-5 star rating to 0-100
- 5.0 stars = 100 points
- 0.0 stars = 0 points

### Availability Score
- Status-based:
  - Available: 100 points
  - Busy: 30 points
  - On Break: 20 points
  - Offline: 0 points
- Penalty for stale location (>1 hour): -5 points per hour

### Customizing Weights
```php
$algorithm = new DispatchAlgorithm();
$algorithm->setWeights([
    'proximity' => 0.50,  // Increase proximity importance
    'workload' => 0.20,
    'rating' => 0.20,
    'availability' => 0.10
]);
```

## Testing

### Run Dispatch System Tests
```bash
cd tests
php DispatchSystemTest.php
```

Tests cover:
- DispatchQueue model operations
- DispatchAlgorithm scoring
- Proximity calculations
- Priority queue ordering
- Driver scoring components

### Manual Testing Checklist
- [ ] Create a service request
- [ ] Add request to dispatch queue
- [ ] Verify request appears in dashboard
- [ ] Test auto-dispatch functionality
- [ ] Verify driver is assigned
- [ ] Check dispatch history
- [ ] Test manual override
- [ ] Test emergency handling
- [ ] Verify queue statistics

## API Documentation

Full API documentation available in: `DISPATCH_API.md`

Key endpoints:
- `POST /dispatch/autoDispatch` - Automatic dispatch
- `POST /dispatch/manualDispatch` - Manual assignment
- `GET /dispatch/findDriver/{id}` - Find best driver
- `GET /dispatch/queueStats` - Queue statistics

## Database Schema

### dispatch_queue
```sql
- id (primary key)
- request_id (foreign key to service_requests)
- driver_id (foreign key to drivers)
- priority (emergency, high, normal, low)
- priority_order (1-4 for sorting)
- status (pending, processing, dispatched, failed)
- processing_at, dispatched_at
- failure_reason
- created_at, updated_at
```

### dispatch_history
```sql
- id (primary key)
- request_id (foreign key)
- driver_id (foreign key)
- dispatch_method (automated, manual)
- score (algorithm score)
- dispatched_at
- dispatched_by (user who dispatched, for manual)
```

## Performance Considerations

### Optimization Tips
1. **Keep driver locations updated** - Stale locations reduce accuracy
2. **Monitor queue size** - Large queues may indicate insufficient drivers
3. **Review failed dispatches** - Adjust algorithm weights if needed
4. **Use indexes** - Database tables include proper indexes
5. **Cache statistics** - Consider caching queue stats for high traffic

### Scalability
- Queue is optimized for fast priority-based retrieval
- History table indexed for efficient queries
- Algorithm is stateless and can scale horizontally
- Consider job queue (Redis/RabbitMQ) for high volume

## Troubleshooting

### No Drivers Found
**Cause**: No drivers within range or all drivers busy
**Solution**: 
- Check driver availability status
- Verify driver GPS locations are current
- Increase search radius (modify `$maxDistance` in DispatchAlgorithm)

### Low Scores
**Cause**: All drivers scoring poorly
**Solution**:
- Review scoring weights
- Check driver ratings
- Verify location data accuracy
- Consider manual assignment

### Queue Not Processing
**Cause**: Dispatch function not being called
**Solution**:
- Verify cron job or scheduler is running
- Check application logs
- Manually trigger auto-dispatch
- Verify database connectivity

### Dispatch History Not Recording
**Cause**: Database insert failing
**Solution**:
- Check database permissions
- Verify foreign key constraints
- Review error logs
- Ensure migration ran successfully

## Future Enhancements

Planned improvements:
- [ ] Real-time push notifications to drivers
- [ ] SMS/email notifications
- [ ] Machine learning for weight optimization
- [ ] Route optimization for multiple pickups
- [ ] Driver skill/certification matching
- [ ] Time-based dispatch windows
- [ ] Predictive dispatch based on demand patterns
- [ ] Mobile app integration
- [ ] Advanced analytics dashboard

## Security

### Access Control
- All dispatch endpoints require authentication
- Role-based access (admin, manager, dispatcher only)
- Manual overrides logged with user ID
- Activity tracking in dispatch history

### Best Practices
- Validate all input parameters
- Use prepared statements (already implemented)
- Sanitize output in views
- Log all dispatch actions
- Monitor for unusual patterns

## Support

For issues or questions:
1. Check `DISPATCH_API.md` for API details
2. Review test suite in `tests/DispatchSystemTest.php`
3. Check application error logs
4. Review dispatch history for patterns
5. Contact system administrator

## License

Part of the Roadside Assistance Admin Platform.
See main project LICENSE for details.
