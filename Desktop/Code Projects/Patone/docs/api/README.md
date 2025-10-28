# API Documentation

## Overview

The Patone API provides RESTful endpoints for managing roadside assistance operations. This API is designed for mobile applications, third-party integrations, and custom client implementations.

## Base URL

```
Production: https://your-domain.com/api
Development: http://localhost/api
```

## Authentication

Currently, the API uses session-based authentication. For production use with mobile apps or third-party integrations, we recommend implementing JWT authentication.

### Session Authentication (Current)

1. Login via the web interface or `/api/login` endpoint
2. Session cookie is automatically managed
3. Include the session cookie with all API requests

### JWT Authentication (Recommended for Production)

Coming in v1.1:
- Token-based authentication
- Refresh token support
- API key authentication for server-to-server calls

## Response Format

All API responses follow a consistent JSON format:

### Success Response

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  }
}
```

### Error Response

```json
{
  "success": false,
  "error": "Error message describing what went wrong"
}
```

## HTTP Status Codes

- `200 OK` - Request successful
- `201 Created` - Resource created successfully
- `400 Bad Request` - Invalid request parameters
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `500 Internal Server Error` - Server error

## Rate Limiting

**Note**: Rate limiting is not currently implemented but recommended for production.

Recommended limits:
- 100 requests per minute per IP
- 1000 requests per hour per authenticated user

## Available Endpoints

### Customers

- `GET /api/customers` - List all customers
- `GET /api/customers/{id}` - Get customer details
- `POST /api/customers` - Create new customer (Admin only)
- `PUT /api/customers/{id}` - Update customer (Admin only)
- `DELETE /api/customers/{id}` - Delete customer (Admin only)

### Drivers

- `GET /api/drivers` - List all drivers
- `GET /api/drivers/{id}` - Get driver details
- `GET /api/drivers/available` - Get available drivers
- `PUT /api/drivers/{id}/location` - Update driver location (Driver only)
- `PUT /api/drivers/{id}/status` - Update driver status

### Service Requests

- `GET /api/requests` - List service requests
- `GET /api/requests/{id}` - Get request details
- `POST /api/requests` - Create new service request
- `PUT /api/requests/{id}` - Update service request
- `PUT /api/requests/{id}/status` - Update request status
- `POST /api/requests/{id}/assign` - Assign driver to request

### Service Types

- `GET /api/service-types` - List all service types
- `GET /api/service-types/{id}` - Get service type details

### Dashboard

- `GET /api/dashboard-stats` - Get dashboard statistics

## Pagination

List endpoints support pagination using `limit` and `offset` parameters:

```
GET /api/customers?limit=25&offset=0
```

Response includes pagination metadata:

```json
{
  "success": true,
  "data": {
    "customers": [...],
    "total": 150,
    "limit": 25,
    "offset": 0
  }
}
```

## Filtering and Search

Most list endpoints support search via the `search` parameter:

```
GET /api/customers?search=john
```

## Versioning

Current API version: `v1`

Future versions will be accessible via URL prefix:
```
/api/v1/customers
/api/v2/customers
```

## CORS

Configure CORS headers appropriately for your domain in production.

Current configuration allows same-origin requests only.

## WebSocket Support

Real-time features (driver location updates, request status changes) will be available via WebSocket in v1.1.

## SDK and Client Libraries

Official client libraries coming soon:
- JavaScript/TypeScript
- PHP
- Python
- Swift (iOS)
- Kotlin (Android)

## OpenAPI Specification

The complete API specification is available in OpenAPI 3.0 format:
- [openapi.yaml](./openapi.yaml)

You can use this with tools like:
- Swagger UI for interactive documentation
- Postman for API testing
- Code generators for client libraries

## Examples

See [EXAMPLES.md](./EXAMPLES.md) for detailed examples of common API operations.

## Changelog

See [API_CHANGELOG.md](./API_CHANGELOG.md) for API version history and breaking changes.

## Support

For API support:
- Documentation issues: Open a GitHub issue
- Integration questions: Contact developers@roadsideassistance.com
- Bug reports: Open a GitHub issue with API label

---

**API Version**: 1.0  
**Last Updated**: October 2024
