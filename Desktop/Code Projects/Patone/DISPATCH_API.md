# Automated Dispatch System - API Documentation

## Overview
The Automated Dispatch System intelligently assigns service requests to the best available drivers based on multiple factors including proximity, workload, driver rating, and availability.

## Core Components

### 1. DispatchQueue Model
Manages priority-based request queuing.

**Key Methods:**
- `enqueue($requestId, $priority)` - Add request to queue
- `getNext()` - Get next request based on priority
- `getPending($limit)` - Get all pending requests
- `markDispatched($queueId, $driverId)` - Mark as dispatched
- `getStats()` - Get queue statistics

### 2. DispatchAlgorithm Model
Intelligent driver selection algorithm.

**Scoring Factors:**
- **Proximity (40%)**: Distance from driver to request location
- **Workload (25%)**: Number of active requests
- **Rating (20%)**: Driver performance rating
- **Availability (15%)**: Driver status and location freshness

**Key Methods:**
- `findBestDriver($requestId, $options)` - Find optimal driver
- `dispatch($requestId, $driverId, $automated)` - Assign driver
- `setWeights($weights)` - Customize scoring weights

### 3. DispatchController
Handles dispatch operations and routing.

## API Endpoints

### Dispatch Dashboard
```
GET /dispatch
```
Display dispatch dashboard with queue, available drivers, and statistics.

**Response:** HTML page

---

### Auto-Dispatch Next Request
```
POST /dispatch/autoDispatch
```
Automatically dispatch the next request in queue to the best available driver.

**Request:** No parameters required

**Response:**
```json
{
  "success": true,
  "message": "Request dispatched successfully",
  "data": {
    "request_id": 123,
    "driver_id": 45,
    "driver_name": "John Doe",
    "score": 87.5,
    "score_breakdown": {
      "proximity": 95.0,
      "workload": 80.0,
      "rating": 90.0,
      "availability": 85.0,
      "total": 87.5
    }
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "No available drivers found for this request"
}
```

---

### Manual Dispatch
```
POST /dispatch/manualDispatch
```
Manually assign a specific driver to a request.

**Request Body:**
```
request_id=123&driver_id=45
```

**Response:**
```json
{
  "success": true,
  "message": "Request manually dispatched successfully",
  "data": {
    "request_id": 123,
    "driver_id": 45,
    "driver_name": "John Doe"
  }
}
```

---

### Find Best Driver
```
GET /dispatch/findDriver/{requestId}
```
Find the best available driver for a specific request without dispatching.

**Response:**
```json
{
  "success": true,
  "data": {
    "driver_id": 45,
    "driver_name": "John Doe",
    "score": 87.5,
    "score_breakdown": {
      "proximity": 95.0,
      "workload": 80.0,
      "rating": 90.0,
      "availability": 85.0,
      "total": 87.5
    },
    "driver": {
      "id": 45,
      "first_name": "John",
      "last_name": "Doe",
      "rating": 4.5,
      "status": "available",
      "current_latitude": 37.7749,
      "current_longitude": -122.4194
    }
  }
}
```

---

### Get Driver Options
```
GET /dispatch/getDriverOptions/{requestId}
```
Get all available drivers with scores for a request.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 45,
      "name": "John Doe",
      "score": 87.5,
      "score_breakdown": {
        "proximity": 95.0,
        "workload": 80.0,
        "rating": 90.0,
        "availability": 85.0,
        "total": 87.5
      },
      "distance": 5.2,
      "active_requests": 1,
      "rating": 4.5
    },
    {
      "id": 46,
      "name": "Jane Smith",
      "score": 82.3,
      "distance": 8.5,
      "active_requests": 2,
      "rating": 4.7
    }
  ]
}
```

---

### Enqueue Request
```
POST /dispatch/enqueue
```
Add a request to the dispatch queue.

**Request Body:**
```
request_id=123&priority=high
```

**Priorities:**
- `emergency` (highest)
- `high`
- `normal` (default)
- `low`

**Response:**
```json
{
  "success": true,
  "message": "Request added to dispatch queue"
}
```

---

### Handle Emergency Request
```
POST /dispatch/handleEmergency
```
Process an emergency request with immediate dispatch attempt.

**Request Body:**
```
request_id=123
```

**Response:**
```json
{
  "success": true,
  "message": "Emergency request dispatched immediately",
  "driver": "John Doe"
}
```

Or if no drivers available:
```json
{
  "success": true,
  "message": "Emergency request queued (no available drivers)",
  "queued": true
}
```

---

### Queue Statistics
```
GET /dispatch/queueStats
```
Get current queue statistics.

**Response:**
```json
{
  "success": true,
  "data": {
    "total_queued": 15,
    "pending": 8,
    "processing": 2,
    "dispatched": 5,
    "failed": 0,
    "emergency_requests": 1
  }
}
```

---

### Dispatch History
```
GET /dispatch/history?limit=50&offset=0
```
View dispatch history with pagination.

**Query Parameters:**
- `limit` - Records per page (default: 50)
- `offset` - Starting record (default: 0)

**Response:** HTML page with dispatch history

---

## Algorithm Details

### Proximity Score Calculation
Uses Haversine formula to calculate distance between driver and request location.

```
Score = max(0, 100 - (distance / maxDistance * 100))
```

- 0 km = 100 points
- 50 km = 0 points
- Linear interpolation between

### Workload Score Calculation
Based on number of active requests assigned to driver.

```
Score = max(0, 100 - (activeRequests / maxJobs * 100))
```

- 0 active requests = 100 points
- 5+ active requests = 0 points

### Rating Score Calculation
Converts driver's 0-5 star rating to 0-100 score.

```
Score = (rating / 5) * 100
```

- 5.0 rating = 100 points
- 0.0 rating = 0 points

### Availability Score Calculation
Based on driver status and location freshness.

**Status Base Scores:**
- Available: 100 points
- Busy: 30 points
- On Break: 20 points
- Offline: 0 points

**Penalties:**
- Location stale (>1 hour): -5 points per hour (max -30)

### Overall Score
```
Total Score = (proximity * 0.40) + (workload * 0.25) + (rating * 0.20) + (availability * 0.15)
```

Weights can be customized via `setWeights()` method.

---

## Database Tables

### dispatch_queue
Stores pending dispatch requests.

**Columns:**
- `id` - Queue item ID
- `request_id` - Service request ID
- `driver_id` - Assigned driver (if dispatched)
- `priority` - Request priority
- `priority_order` - Numeric priority for sorting
- `status` - pending, processing, dispatched, failed
- `processing_at` - When processing started
- `dispatched_at` - When dispatched
- `failure_reason` - Why dispatch failed
- `created_at`, `updated_at` - Timestamps

### dispatch_history
Historical record of all dispatches.

**Columns:**
- `id` - History record ID
- `request_id` - Service request ID
- `driver_id` - Assigned driver ID
- `dispatch_method` - automated or manual
- `score` - Algorithm score (if automated)
- `dispatched_at` - Timestamp
- `dispatched_by` - User who dispatched (if manual)

### driver_certifications
Driver skills and certifications (for future enhancements).

### driver_performance
Daily driver performance metrics (for future enhancements).

---

## Usage Examples

### Example 1: Auto-Dispatch Workflow
```javascript
// Get queue stats
fetch('/dispatch/queueStats')
  .then(res => res.json())
  .then(data => console.log(data.data));

// Auto-dispatch next request
fetch('/dispatch/autoDispatch', { method: 'POST' })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      console.log(`Dispatched to ${data.data.driver_name}`);
      console.log(`Score: ${data.data.score}`);
    }
  });
```

### Example 2: Manual Dispatch with Options
```javascript
// Find best driver for request
fetch('/dispatch/findDriver/123')
  .then(res => res.json())
  .then(data => {
    const driver = data.data;
    console.log(`Best driver: ${driver.driver_name}`);
    console.log(`Score: ${driver.score}`);
    
    // Manual dispatch to this driver
    const formData = new FormData();
    formData.append('request_id', 123);
    formData.append('driver_id', driver.driver_id);
    
    return fetch('/dispatch/manualDispatch', {
      method: 'POST',
      body: formData
    });
  })
  .then(res => res.json())
  .then(data => console.log(data.message));
```

### Example 3: Emergency Request Handling
```javascript
// Handle emergency request
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
        alert('Request queued - no available drivers');
      } else {
        alert(`Emergency dispatched to ${data.driver}`);
      }
    }
  });
```

---

## Best Practices

1. **Always check for available drivers** before attempting dispatch
2. **Use emergency handling** for time-critical requests
3. **Monitor queue statistics** to identify bottlenecks
4. **Review dispatch history** to optimize algorithm weights
5. **Keep driver locations updated** for accurate proximity scoring
6. **Use manual override** when domain knowledge suggests a better match

---

## Future Enhancements

- Real-time notifications to drivers
- Multi-factor authentication for manual overrides
- Machine learning for weight optimization
- Integration with external mapping services
- Driver skill/certification matching
- Route optimization for multiple pickups
- Predictive dispatch based on demand patterns
