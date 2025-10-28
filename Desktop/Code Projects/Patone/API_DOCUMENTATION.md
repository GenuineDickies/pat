# Patone v1.0 - RESTful API Documentation

## Overview
This document provides comprehensive documentation for the Patone Roadside Assistance Admin Platform RESTful API. The API enables integration with mobile apps and third-party services.

## Base URL
```
http://your-domain.com/api
```

## Authentication

All API endpoints (except `/api/login`) require authentication using JWT tokens.

### Authentication Header
Include the JWT token in the Authorization header:
```
Authorization: Bearer <your-jwt-token>
```

Alternatively, you can pass the token as a query parameter:
```
?token=<your-jwt-token>
```

---

## Authentication Endpoints

### POST /api/login
Authenticate a user and receive a JWT token.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "your-password"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "email": "user@example.com",
      "name": "John Doe",
      "role": "admin"
    },
    "expires_in": 86400
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "error": "Invalid credentials"
}
```

---

### POST /api/logout
Invalidate the current JWT token.

**Headers:**
```
Authorization: Bearer <your-jwt-token>
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "message": "Logged out successfully"
  }
}
```

---

### POST /api/refresh
Refresh an existing JWT token to extend its validity.

**Headers:**
```
Authorization: Bearer <your-jwt-token>
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "expires_in": 86400
  }
}
```

---

## Customer Endpoints

### GET /api/customers
Retrieve a list of customers with pagination.

**Query Parameters:**
- `limit` (optional, default: 25, max: 100) - Number of results per page
- `offset` (optional, default: 0) - Offset for pagination
- `search` (optional) - Search by name, email, or phone

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "customers": [
      {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "phone": "5551234567",
        "address": "123 Main St",
        "city": "New York",
        "state": "NY",
        "zip": "10001",
        "status": "active",
        "is_vip": 0,
        "created_at": "2024-01-15 10:30:00",
        "total_requests": 5,
        "last_service_date": "2024-10-20 14:22:00"
      }
    ],
    "total": 150,
    "limit": 25,
    "offset": 0
  }
}
```

---

### POST /api/customers
Create a new customer.

**Request Body:**
```json
{
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane@example.com",
  "phone": "5559876543",
  "address": "456 Oak Ave",
  "address2": "Apt 2B",
  "city": "Los Angeles",
  "state": "CA",
  "zip": "90001",
  "emergency_contact": "5551112222",
  "date_of_birth": "1985-05-15",
  "is_vip": false,
  "status": "active",
  "notes": "Prefers morning appointments"
}
```

**Required Fields:**
- `first_name`, `last_name`, `email`, `phone`, `address`, `city`, `state`, `zip`

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "customer_id": 151,
    "message": "Customer created successfully"
  }
}
```

---

### GET /api/customers/{id}
Retrieve details of a specific customer.

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "customer": {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "phone": "5551234567",
      "address": "123 Main St",
      "city": "New York",
      "state": "NY",
      "zip": "10001",
      "status": "active",
      "is_vip": 0,
      "notes": "",
      "created_at": "2024-01-15 10:30:00"
    }
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "success": false,
  "error": "Customer not found"
}
```

---

### PUT /api/customers/{id}
Update an existing customer.

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "phone": "5551234567",
  "address": "789 New Street",
  "city": "New York",
  "state": "NY",
  "zip": "10002",
  "status": "active",
  "is_vip": true
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "customer_id": 1,
    "message": "Customer updated successfully"
  }
}
```

---

### DELETE /api/customers/{id}
Delete a customer (cannot delete if they have active service requests).

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "customer_id": 1,
    "message": "Customer deleted successfully"
  }
}
```

**Error Response (400 Bad Request):**
```json
{
  "success": false,
  "error": "Cannot delete customer with active service requests"
}
```

---

## Service Request Endpoints

### GET /api/requests
Retrieve a list of service requests with pagination.

**Query Parameters:**
- `limit` (optional, default: 25, max: 100)
- `offset` (optional, default: 0)
- `status` (optional) - Filter by status: pending, assigned, in_progress, completed, cancelled

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "requests": [
      {
        "id": 100,
        "customer_id": 1,
        "driver_id": 5,
        "service_type_id": 2,
        "status": "in_progress",
        "priority": "high",
        "location_address": "123 Highway Dr",
        "location_city": "Boston",
        "location_state": "MA",
        "created_at": "2024-10-28 09:15:00",
        "customer_first_name": "John",
        "customer_last_name": "Doe",
        "driver_first_name": "Mike",
        "driver_last_name": "Wilson",
        "service_type_name": "Flat Tire"
      }
    ],
    "total": 250,
    "limit": 25,
    "offset": 0
  }
}
```

---

### POST /api/requests
Create a new service request.

**Request Body:**
```json
{
  "customer_id": 1,
  "service_type_id": 2,
  "vehicle_id": 3,
  "location_address": "456 State Highway",
  "location_city": "Miami",
  "location_state": "FL",
  "location_latitude": 25.7617,
  "location_longitude": -80.1918,
  "priority": "normal",
  "description": "Car won't start, battery seems dead",
  "customer_notes": "I'm in the parking lot near the mall"
}
```

**Required Fields:**
- `customer_id`, `service_type_id`, `location_address`, `location_city`, `location_state`

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "request_id": 251,
    "message": "Service request created successfully"
  }
}
```

---

### GET /api/requests/{id}
Retrieve details of a specific service request.

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "request": {
      "id": 100,
      "customer_id": 1,
      "driver_id": 5,
      "service_type_id": 2,
      "vehicle_id": 3,
      "status": "in_progress",
      "priority": "high",
      "location_address": "123 Highway Dr",
      "location_city": "Boston",
      "location_state": "MA",
      "location_latitude": 42.3601,
      "location_longitude": -71.0589,
      "description": "Flat tire on I-95",
      "estimated_cost": 75.00,
      "final_cost": null,
      "created_at": "2024-10-28 09:15:00",
      "customer_first_name": "John",
      "customer_last_name": "Doe",
      "driver_first_name": "Mike",
      "driver_last_name": "Wilson",
      "service_type_name": "Flat Tire"
    }
  }
}
```

---

### PUT /api/requests/{id}
Update an existing service request.

**Request Body:**
```json
{
  "driver_id": 5,
  "status": "in_progress",
  "priority": "high",
  "estimated_cost": 85.00,
  "driver_notes": "On my way, ETA 15 minutes"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "request_id": 100,
    "message": "Request updated successfully"
  }
}
```

---

### DELETE /api/requests/{id}
Delete a service request.

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "request_id": 100,
    "message": "Request deleted successfully"
  }
}
```

---

### POST /api/requests/{id}/status
Update the status of a service request.

**Request Body:**
```json
{
  "status": "completed",
  "notes": "Successfully changed tire, customer satisfied"
}
```

**Valid Status Values:**
- `pending`, `assigned`, `in_progress`, `completed`, `cancelled`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "request_id": 100,
    "status": "completed",
    "timestamp": "2024-10-28T10:45:00+00:00"
  }
}
```

---

## Driver Endpoints

### GET /api/drivers
Retrieve a list of drivers with pagination.

**Query Parameters:**
- `limit` (optional, default: 25, max: 100)
- `offset` (optional, default: 0)
- `status` (optional) - Filter by status: available, busy, offline

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "drivers": [
      {
        "id": 5,
        "first_name": "Mike",
        "last_name": "Wilson",
        "email": "mike@example.com",
        "phone": "5552223333",
        "license_number": "D1234567",
        "license_state": "MA",
        "status": "available",
        "rating": 4.8,
        "total_jobs": 150,
        "completed_jobs": 145,
        "current_latitude": 42.3601,
        "current_longitude": -71.0589,
        "created_at": "2023-06-10 08:00:00"
      }
    ],
    "total": 25,
    "limit": 25,
    "offset": 0
  }
}
```

---

### GET /api/drivers/{id}
Retrieve details of a specific driver.

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "driver": {
      "id": 5,
      "first_name": "Mike",
      "last_name": "Wilson",
      "email": "mike@example.com",
      "phone": "5552223333",
      "license_number": "D1234567",
      "license_state": "MA",
      "license_expiry": "2026-12-31",
      "vehicle_info": "2022 Ford F-150 Tow Truck",
      "status": "available",
      "rating": 4.8,
      "total_jobs": 150,
      "completed_jobs": 145
    }
  }
}
```

---

### PUT /api/drivers/{id}
Update an existing driver.

**Request Body:**
```json
{
  "first_name": "Mike",
  "last_name": "Wilson",
  "email": "mike.wilson@example.com",
  "phone": "5552223333",
  "license_number": "D1234567",
  "license_state": "MA",
  "license_expiry": "2026-12-31",
  "status": "available",
  "vehicle_info": "2023 Ford F-150 Tow Truck"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "driver_id": 5,
    "message": "Driver updated successfully"
  }
}
```

---

### GET /api/drivers/available
Get list of available drivers near a specific location.

**Query Parameters:**
- `latitude` (required) - Latitude of the location
- `longitude` (required) - Longitude of the location
- `max_distance` (optional, default: 50) - Maximum distance in miles

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "drivers": [
      {
        "id": 5,
        "first_name": "Mike",
        "last_name": "Wilson",
        "phone": "5552223333",
        "status": "available",
        "rating": 4.8,
        "current_latitude": 42.3601,
        "current_longitude": -71.0589,
        "distance": 2.5,
        "active_requests": 0
      }
    ],
    "count": 1
  }
}
```

---

### POST /api/drivers/{id}/location
Update a driver's GPS location.

**Request Body:**
```json
{
  "latitude": 42.3601,
  "longitude": -71.0589
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "driver_id": 5,
    "latitude": 42.3601,
    "longitude": -71.0589,
    "timestamp": "2024-10-28T10:15:00+00:00"
  }
}
```

---

## Report Endpoints

### GET /api/reports/daily
Generate a daily report for a specific date.

**Query Parameters:**
- `date` (optional, default: today) - Date in YYYY-MM-DD format

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "date": "2024-10-28",
    "stats": {
      "total": 45,
      "completed": 38,
      "pending": 3,
      "in_progress": 4,
      "cancelled": 0,
      "revenue": 3250.50,
      "avg_response_time": 18
    },
    "requests": [
      {
        "id": 100,
        "status": "completed",
        "priority": "normal",
        "created_at": "2024-10-28 09:15:00",
        "customer_first_name": "John",
        "customer_last_name": "Doe",
        "driver_first_name": "Mike",
        "driver_last_name": "Wilson",
        "service_type_name": "Flat Tire",
        "final_cost": 75.00
      }
    ]
  }
}
```

---

### GET /api/reports/monthly
Generate a monthly report.

**Query Parameters:**
- `year` (optional, default: current year)
- `month` (optional, default: current month) - Month number (1-12)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "year": 2024,
    "month": 10,
    "period": "October 2024",
    "stats": {
      "total": 1250,
      "completed": 1150,
      "pending": 20,
      "in_progress": 50,
      "cancelled": 30,
      "revenue": 95250.75,
      "avg_response_time": 22
    },
    "daily_breakdown": [
      {
        "date": "2024-10-01",
        "total": 42,
        "completed": 38,
        "revenue": 3100.00
      }
    ],
    "service_type_breakdown": [
      {
        "name": "Flat Tire",
        "total": 350,
        "completed": 330,
        "avg_cost": 75.50
      }
    ]
  }
}
```

---

### GET /api/reports/custom
Generate a custom report for a date range.

**Query Parameters:**
- `start_date` (optional, default: 30 days ago) - Start date in YYYY-MM-DD format
- `end_date` (optional, default: today) - End date in YYYY-MM-DD format

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "start_date": "2024-10-01",
    "end_date": "2024-10-28",
    "stats": {
      "total": 1250,
      "completed": 1150,
      "pending": 20,
      "in_progress": 50,
      "cancelled": 30,
      "revenue": 95250.75
    },
    "driver_performance": [
      {
        "id": 5,
        "name": "Mike Wilson",
        "total_jobs": 85,
        "completed_jobs": 82,
        "avg_revenue": 78.50,
        "rating": 4.8
      }
    ],
    "top_customers": [
      {
        "id": 1,
        "name": "John Doe",
        "total_requests": 12,
        "total_spent": 950.00
      }
    ]
  }
}
```

---

## Other Endpoints

### GET /api/dashboard-stats
Get dashboard statistics.

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "customers": {
      "total": 1500,
      "active": 1420,
      "vip": 85,
      "new_this_month": 45
    },
    "drivers": {
      "total": 25,
      "available": 12,
      "busy": 8,
      "offline": 5
    },
    "requests": {
      "total": 450,
      "completed": 380,
      "pending": 15,
      "in_progress": 45,
      "cancelled": 10,
      "revenue": 32500.00
    },
    "timestamp": "2024-10-28T10:30:00+00:00"
  }
}
```

---

### GET /api/service-types
Get list of available service types.

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "service_types": [
      {
        "id": 1,
        "name": "Flat Tire",
        "description": "Tire change or repair",
        "base_price": 75.00,
        "estimated_duration": 30,
        "is_active": 1
      },
      {
        "id": 2,
        "name": "Jump Start",
        "description": "Battery jump start service",
        "base_price": 50.00,
        "estimated_duration": 15,
        "is_active": 1
      }
    ],
    "count": 2
  }
}
```

---

## Error Responses

All error responses follow this format:

```json
{
  "success": false,
  "error": "Error message description"
}
```

### Common HTTP Status Codes

- **200 OK** - Request successful
- **201 Created** - Resource created successfully
- **400 Bad Request** - Invalid request parameters or validation error
- **401 Unauthorized** - Authentication required or invalid token
- **403 Forbidden** - Insufficient permissions
- **404 Not Found** - Resource not found
- **500 Internal Server Error** - Server error

---

## Rate Limiting

The API implements basic rate limiting to prevent abuse:
- **Rate Limit**: 100 requests per minute per IP address
- **Header**: `X-RateLimit-Remaining` shows remaining requests

When rate limit is exceeded:
```json
{
  "success": false,
  "error": "Rate limit exceeded. Please try again later."
}
```

---

## Versioning

The current API version is **v1.0**. Future versions will be accessible via:
```
/api/v2/endpoint
```

---

## Best Practices

1. **Always use HTTPS** in production environments
2. **Store JWT tokens securely** (never in localStorage for web apps)
3. **Include proper error handling** for all API calls
4. **Implement exponential backoff** for retries
5. **Cache responses** when appropriate
6. **Use pagination** for large result sets
7. **Validate all input** on the client side before sending

---

## Support

For API support, contact: admin@roadsideassistance.com

## Changelog

### Version 1.0 (October 2024)
- Initial API release
- Authentication with JWT
- Full CRUD operations for customers, requests, and drivers
- Report generation endpoints
- Dashboard statistics
- Rate limiting implementation
