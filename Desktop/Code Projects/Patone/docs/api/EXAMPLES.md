# API Examples

This guide provides practical examples for common API operations.

## Table of Contents

- [Authentication](#authentication)
- [Customer Operations](#customer-operations)
- [Driver Operations](#driver-operations)
- [Service Request Operations](#service-request-operations)
- [Dashboard Statistics](#dashboard-statistics)

## Authentication

### Login (Session-based)

Currently, authentication is handled via web login. For API usage:

1. Login through the web interface at `/login`
2. Session cookie is automatically set
3. Use the session cookie for subsequent API requests

**Example with cURL:**

```bash
# Login
curl -X POST http://localhost/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=admin&password=admin123" \
  -c cookies.txt

# Use session for API requests
curl -X GET http://localhost/api/customers \
  -b cookies.txt
```

**Example with JavaScript (fetch):**

```javascript
// Login first through your web app
// Then make API calls with credentials: 'include'

fetch('/api/customers', {
  method: 'GET',
  credentials: 'include', // Include session cookie
  headers: {
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

## Customer Operations

### List Customers

Get a paginated list of customers with optional search.

**Request:**
```bash
curl -X GET "http://localhost/api/customers?limit=10&offset=0&search=john" \
  -H "Content-Type: application/json" \
  -b cookies.txt
```

**Response:**
```json
{
  "success": true,
  "data": {
    "customers": [
      {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com",
        "phone": "+1-555-0123",
        "address": "123 Main St",
        "city": "Springfield",
        "state": "IL",
        "zip": "62701",
        "is_vip": false,
        "status": "active",
        "created_at": "2024-01-15 10:30:00"
      }
    ],
    "total": 1,
    "limit": 10,
    "offset": 0
  }
}
```

### Get Customer by ID

Retrieve detailed information about a specific customer.

**Request:**
```bash
curl -X GET http://localhost/api/customers/1 \
  -H "Content-Type: application/json" \
  -b cookies.txt
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+1-555-0123",
    "emergency_contact": "+1-555-0124",
    "address": "123 Main St",
    "city": "Springfield",
    "state": "IL",
    "zip": "62701",
    "is_vip": false,
    "status": "active",
    "vehicles": [
      {
        "id": 1,
        "make": "Toyota",
        "model": "Camry",
        "year": 2020,
        "color": "Silver",
        "license_plate": "ABC123"
      }
    ],
    "created_at": "2024-01-15 10:30:00"
  }
}
```

### JavaScript Example - Fetch Customers

```javascript
async function getCustomers(limit = 25, offset = 0, search = '') {
  try {
    const params = new URLSearchParams({
      limit: limit,
      offset: offset,
      ...(search && { search })
    });
    
    const response = await fetch(`/api/customers?${params}`, {
      method: 'GET',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json'
      }
    });
    
    const result = await response.json();
    
    if (result.success) {
      console.log('Customers:', result.data.customers);
      console.log('Total:', result.data.total);
      return result.data;
    } else {
      console.error('Error:', result.error);
    }
  } catch (error) {
    console.error('Request failed:', error);
  }
}

// Usage
getCustomers(10, 0, 'john');
```

## Driver Operations

### List Drivers

Get all drivers with their current status.

**Request:**
```bash
curl -X GET "http://localhost/api/drivers?limit=10&offset=0" \
  -H "Content-Type: application/json" \
  -b cookies.txt
```

**Response:**
```json
{
  "success": true,
  "data": {
    "drivers": [
      {
        "id": 1,
        "first_name": "Mike",
        "last_name": "Smith",
        "email": "mike.smith@example.com",
        "phone": "+1-555-0200",
        "status": "available",
        "current_latitude": 37.7749,
        "current_longitude": -122.4194,
        "rating": 4.8,
        "total_jobs": 150,
        "completed_jobs": 145
      }
    ],
    "total": 1
  }
}
```

### Get Available Drivers

Get only drivers who are currently available for assignment.

**Request:**
```bash
curl -X GET http://localhost/api/drivers/available \
  -H "Content-Type: application/json" \
  -b cookies.txt
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "first_name": "Mike",
      "last_name": "Smith",
      "status": "available",
      "current_latitude": 37.7749,
      "current_longitude": -122.4194,
      "distance_km": 2.5
    }
  ]
}
```

### Update Driver Location

Update the GPS coordinates for a driver (typically called from mobile app).

**Request:**
```bash
curl -X PUT http://localhost/api/drivers/1/location \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{
    "latitude": 37.7749,
    "longitude": -122.4194
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Location updated successfully"
}
```

### JavaScript Example - Track Driver Location

```javascript
// Update driver location periodically
function startLocationTracking(driverId) {
  if (!navigator.geolocation) {
    console.error('Geolocation not supported');
    return;
  }
  
  const watchId = navigator.geolocation.watchPosition(
    async (position) => {
      const { latitude, longitude } = position.coords;
      
      try {
        const response = await fetch(`/api/drivers/${driverId}/location`, {
          method: 'PUT',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            latitude: latitude,
            longitude: longitude
          })
        });
        
        const result = await response.json();
        if (result.success) {
          console.log('Location updated');
        }
      } catch (error) {
        console.error('Failed to update location:', error);
      }
    },
    (error) => {
      console.error('Geolocation error:', error);
    },
    {
      enableHighAccuracy: true,
      timeout: 5000,
      maximumAge: 0
    }
  );
  
  return watchId;
}

// Stop tracking
function stopLocationTracking(watchId) {
  navigator.geolocation.clearWatch(watchId);
}
```

## Service Request Operations

### Create Service Request

Create a new service request.

**Request:**
```bash
curl -X POST http://localhost/api/requests \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{
    "customer_id": 1,
    "service_type_id": 2,
    "location_address": "123 Main St, Springfield, IL 62701",
    "location_latitude": 39.7817,
    "location_longitude": -89.6501,
    "description": "Car won'\''t start, need jump start",
    "priority": "normal"
  }'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 42,
    "customer_id": 1,
    "service_type_id": 2,
    "status": "pending",
    "priority": "normal",
    "location_address": "123 Main St, Springfield, IL 62701",
    "description": "Car won't start, need jump start",
    "created_at": "2024-10-28 12:30:00"
  }
}
```

### List Service Requests

Get service requests with optional status filter.

**Request:**
```bash
curl -X GET "http://localhost/api/requests?status=pending&limit=10" \
  -H "Content-Type: application/json" \
  -b cookies.txt
```

**Response:**
```json
{
  "success": true,
  "data": {
    "requests": [
      {
        "id": 42,
        "customer_id": 1,
        "customer_name": "John Doe",
        "driver_id": null,
        "driver_name": null,
        "service_type": "Jump Start",
        "status": "pending",
        "priority": "normal",
        "location_address": "123 Main St, Springfield, IL 62701",
        "created_at": "2024-10-28 12:30:00"
      }
    ],
    "total": 1
  }
}
```

### Update Request Status

Update the status of a service request.

**Request:**
```bash
curl -X PUT http://localhost/api/requests/42/status \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{
    "status": "in_progress"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Request status updated successfully"
}
```

### Python Example - Create and Track Request

```python
import requests

class RoadsideAPI:
    def __init__(self, base_url, session_cookie):
        self.base_url = base_url
        self.session = requests.Session()
        self.session.cookies.set('PHPSESSID', session_cookie)
    
    def create_request(self, customer_id, service_type_id, location, description):
        """Create a new service request"""
        url = f"{self.base_url}/requests"
        payload = {
            "customer_id": customer_id,
            "service_type_id": service_type_id,
            "location_address": location['address'],
            "location_latitude": location.get('latitude'),
            "location_longitude": location.get('longitude'),
            "description": description,
            "priority": "normal"
        }
        
        response = self.session.post(url, json=payload)
        return response.json()
    
    def get_request_status(self, request_id):
        """Get current status of a request"""
        url = f"{self.base_url}/requests/{request_id}"
        response = self.session.get(url)
        return response.json()
    
    def update_status(self, request_id, status):
        """Update request status"""
        url = f"{self.base_url}/requests/{request_id}/status"
        payload = {"status": status}
        response = self.session.put(url, json=payload)
        return response.json()

# Usage
api = RoadsideAPI('http://localhost/api', 'your-session-cookie')

# Create request
result = api.create_request(
    customer_id=1,
    service_type_id=2,
    location={
        'address': '123 Main St, Springfield, IL',
        'latitude': 39.7817,
        'longitude': -89.6501
    },
    description='Flat tire needs replacement'
)

if result['success']:
    request_id = result['data']['id']
    print(f"Request created: {request_id}")
    
    # Check status
    status = api.get_request_status(request_id)
    print(f"Current status: {status['data']['status']}")
```

## Dashboard Statistics

### Get Dashboard Stats

Retrieve current statistics for the dashboard.

**Request:**
```bash
curl -X GET http://localhost/api/dashboard-stats \
  -H "Content-Type: application/json" \
  -b cookies.txt
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_requests": 1250,
    "pending_requests": 5,
    "active_requests": 12,
    "completed_today": 28,
    "available_drivers": 8,
    "total_drivers": 15,
    "total_customers": 450,
    "average_response_time": 18.5
  }
}
```

### JavaScript Example - Live Dashboard

```javascript
class DashboardMonitor {
  constructor(updateInterval = 30000) {
    this.updateInterval = updateInterval;
    this.intervalId = null;
  }
  
  async fetchStats() {
    try {
      const response = await fetch('/api/dashboard-stats', {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json'
        }
      });
      
      const result = await response.json();
      
      if (result.success) {
        this.updateDashboard(result.data);
      }
    } catch (error) {
      console.error('Failed to fetch dashboard stats:', error);
    }
  }
  
  updateDashboard(stats) {
    // Update DOM elements
    document.getElementById('pending-requests').textContent = stats.pending_requests;
    document.getElementById('active-requests').textContent = stats.active_requests;
    document.getElementById('available-drivers').textContent = stats.available_drivers;
    document.getElementById('completed-today').textContent = stats.completed_today;
  }
  
  start() {
    // Initial fetch
    this.fetchStats();
    
    // Set up periodic updates
    this.intervalId = setInterval(() => {
      this.fetchStats();
    }, this.updateInterval);
  }
  
  stop() {
    if (this.intervalId) {
      clearInterval(this.intervalId);
      this.intervalId = null;
    }
  }
}

// Usage
const monitor = new DashboardMonitor(30000); // Update every 30 seconds
monitor.start();

// Stop monitoring when leaving page
window.addEventListener('beforeunload', () => {
  monitor.stop();
});
```

## Error Handling

### Standard Error Response

All errors follow this format:

```json
{
  "success": false,
  "error": "Error message describing what went wrong"
}
```

### Common Error Scenarios

**Unauthorized (401):**
```json
{
  "success": false,
  "error": "Unauthorized"
}
```

**Not Found (404):**
```json
{
  "success": false,
  "error": "Resource not found"
}
```

**Validation Error (400):**
```json
{
  "success": false,
  "error": "Invalid input: customer_id is required"
}
```

### JavaScript Error Handling

```javascript
async function makeAPIRequest(url, options = {}) {
  try {
    const response = await fetch(url, {
      ...options,
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        ...options.headers
      }
    });
    
    const result = await response.json();
    
    if (!response.ok) {
      throw new Error(result.error || `HTTP ${response.status}`);
    }
    
    if (!result.success) {
      throw new Error(result.error || 'Request failed');
    }
    
    return result.data;
  } catch (error) {
    console.error('API request failed:', error.message);
    throw error;
  }
}

// Usage with error handling
try {
  const customers = await makeAPIRequest('/api/customers');
  console.log('Customers:', customers);
} catch (error) {
  // Handle error appropriately
  alert(`Failed to load customers: ${error.message}`);
}
```

## Best Practices

1. **Always handle errors gracefully**
   - Check `success` field in response
   - Provide user-friendly error messages
   - Log errors for debugging

2. **Use appropriate HTTP methods**
   - GET for reading data
   - POST for creating resources
   - PUT for updating resources
   - DELETE for removing resources

3. **Implement retry logic**
   - Retry failed requests with exponential backoff
   - Set maximum retry attempts

4. **Cache when appropriate**
   - Cache static data (service types)
   - Invalidate cache when data changes

5. **Rate limit your requests**
   - Don't flood the API with requests
   - Implement request queuing if needed

6. **Validate input before sending**
   - Client-side validation reduces errors
   - Provides better user experience

---

**For more information, see:**
- [API Overview](./README.md)
- [OpenAPI Specification](./openapi.yaml)
- [Authentication Guide](./AUTHENTICATION.md)
