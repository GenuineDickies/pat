# Patone API - Quick Start Guide

## Getting Started with the Patone RESTful API

This guide will help you quickly integrate with the Patone Roadside Assistance API.

---

## Step 1: Authentication

First, obtain a JWT token by logging in:

```bash
curl -X POST http://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "your-email@example.com",
    "password": "your-password"
  }'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1Qi...",
    "user": {
      "id": 1,
      "email": "your-email@example.com",
      "name": "John Doe",
      "role": "admin"
    },
    "expires_in": 86400
  }
}
```

Save the token - you'll need it for all subsequent requests!

---

## Step 2: Make Your First API Call

Use the token to fetch customers:

```bash
curl -X GET http://your-domain.com/api/customers \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
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
        "email": "john@example.com",
        "phone": "5551234567",
        "city": "New York",
        "state": "NY"
      }
    ],
    "total": 150,
    "limit": 25,
    "offset": 0
  }
}
```

---

## Common Operations

### Create a New Customer

```bash
curl -X POST http://your-domain.com/api/customers \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@example.com",
    "phone": "5559876543",
    "address": "456 Oak Ave",
    "city": "Los Angeles",
    "state": "CA",
    "zip": "90001"
  }'
```

### Create a Service Request

```bash
curl -X POST http://your-domain.com/api/requests \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "service_type_id": 2,
    "location_address": "123 Highway Rd",
    "location_city": "Miami",
    "location_state": "FL",
    "priority": "high",
    "description": "Flat tire on I-95"
  }'
```

### Update Request Status

```bash
curl -X POST http://your-domain.com/api/requests/100/status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "completed",
    "notes": "Service completed successfully"
  }'
```

### Get Daily Report

```bash
curl -X GET "http://your-domain.com/api/reports/daily?date=2024-10-28" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## JavaScript/TypeScript Example

```javascript
// Login and get token
async function login(email, password) {
  const response = await fetch('http://your-domain.com/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  if (data.success) {
    // Store token (use secure storage in production!)
    localStorage.setItem('api_token', data.data.token);
    return data.data.token;
  }
  throw new Error(data.error);
}

// Make authenticated API call
async function getCustomers(token) {
  const response = await fetch('http://your-domain.com/api/customers', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  const data = await response.json();
  return data.data.customers;
}

// Create a service request
async function createRequest(token, requestData) {
  const response = await fetch('http://your-domain.com/api/requests', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(requestData)
  });
  
  const data = await response.json();
  if (data.success) {
    return data.data.request_id;
  }
  throw new Error(data.error);
}

// Usage
const token = await login('user@example.com', 'password');
const customers = await getCustomers(token);
console.log(customers);
```

---

## Python Example

```python
import requests

class PatoneAPI:
    def __init__(self, base_url):
        self.base_url = base_url
        self.token = None
    
    def login(self, email, password):
        """Login and get JWT token"""
        response = requests.post(
            f'{self.base_url}/login',
            json={'email': email, 'password': password}
        )
        data = response.json()
        
        if data['success']:
            self.token = data['data']['token']
            return self.token
        raise Exception(data['error'])
    
    def get_customers(self, limit=25, offset=0):
        """Get list of customers"""
        headers = {'Authorization': f'Bearer {self.token}'}
        params = {'limit': limit, 'offset': offset}
        
        response = requests.get(
            f'{self.base_url}/customers',
            headers=headers,
            params=params
        )
        data = response.json()
        
        if data['success']:
            return data['data']['customers']
        raise Exception(data['error'])
    
    def create_request(self, request_data):
        """Create a service request"""
        headers = {
            'Authorization': f'Bearer {self.token}',
            'Content-Type': 'application/json'
        }
        
        response = requests.post(
            f'{self.base_url}/requests',
            headers=headers,
            json=request_data
        )
        data = response.json()
        
        if data['success']:
            return data['data']['request_id']
        raise Exception(data['error'])

# Usage
api = PatoneAPI('http://your-domain.com/api')
api.login('user@example.com', 'password')

customers = api.get_customers(limit=10)
for customer in customers:
    print(f"{customer['first_name']} {customer['last_name']}")

request_id = api.create_request({
    'customer_id': 1,
    'service_type_id': 2,
    'location_address': '123 Emergency Ln',
    'location_city': 'Boston',
    'location_state': 'MA'
})
print(f'Created request #{request_id}')
```

---

## Mobile App (React Native) Example

```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'http://your-domain.com/api';

// Store token securely
async function storeToken(token) {
  await AsyncStorage.setItem('@api_token', token);
}

async function getToken() {
  return await AsyncStorage.getItem('@api_token');
}

// Login
async function login(email, password) {
  const response = await fetch(`${API_BASE_URL}/login`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  if (data.success) {
    await storeToken(data.data.token);
    return data.data;
  }
  
  throw new Error(data.error);
}

// Get available drivers near location
async function getNearbyDrivers(latitude, longitude) {
  const token = await getToken();
  
  const response = await fetch(
    `${API_BASE_URL}/drivers/available?latitude=${latitude}&longitude=${longitude}&max_distance=25`,
    {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    }
  );
  
  const data = await response.json();
  return data.data.drivers;
}

// Update driver location (for driver app)
async function updateDriverLocation(driverId, latitude, longitude) {
  const token = await getToken();
  
  const response = await fetch(
    `${API_BASE_URL}/drivers/${driverId}/location`,
    {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ latitude, longitude })
    }
  );
  
  const data = await response.json();
  return data.success;
}
```

---

## Rate Limiting

The API limits requests to **100 per minute per IP address**.

Check rate limit headers in responses:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1698451200
```

If you exceed the limit, you'll receive:
```json
{
  "success": false,
  "error": "Rate limit exceeded. Please try again later."
}
```
**HTTP Status:** 429 Too Many Requests

---

## Error Handling

All errors follow this format:

```json
{
  "success": false,
  "error": "Error message here"
}
```

**Common Status Codes:**
- `200` - Success
- `201` - Created
- `400` - Bad Request (validation error)
- `401` - Unauthorized (invalid/missing token)
- `404` - Not Found
- `429` - Rate Limit Exceeded
- `500` - Internal Server Error

---

## Token Refresh

Tokens expire after 24 hours. Refresh before expiry:

```bash
curl -X POST http://your-domain.com/api/refresh \
  -H "Authorization: Bearer YOUR_OLD_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "NEW_TOKEN_HERE",
    "expires_in": 86400
  }
}
```

---

## Best Practices

1. **Store tokens securely**
   - Use secure storage mechanisms (Keychain, Keystore)
   - Never store in localStorage for web apps (use httpOnly cookies instead)

2. **Handle errors gracefully**
   - Always check the `success` field
   - Implement retry logic with exponential backoff
   - Handle rate limiting

3. **Use HTTPS in production**
   - Never send tokens over unencrypted connections
   
4. **Implement token refresh**
   - Refresh tokens before they expire
   - Handle 401 errors by re-authenticating

5. **Respect rate limits**
   - Cache responses when appropriate
   - Batch operations when possible
   - Monitor rate limit headers

---

## Need Help?

- **Full Documentation:** See `API_DOCUMENTATION.md`
- **OpenAPI Spec:** Import `openapi.yaml` into Swagger/Postman
- **Security Info:** Review `SECURITY_ANALYSIS.md`
- **Support:** admin@roadsideassistance.com

---

## Postman Collection

Import this collection to test the API:

```json
{
  "info": {
    "name": "Patone API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Authentication",
      "item": [
        {
          "name": "Login",
          "request": {
            "method": "POST",
            "header": [{"key": "Content-Type", "value": "application/json"}],
            "body": {
              "mode": "raw",
              "raw": "{\"email\": \"user@example.com\", \"password\": \"password\"}"
            },
            "url": "{{base_url}}/api/login"
          }
        }
      ]
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000"
    }
  ]
}
```

Save this as `patone-api.postman_collection.json` and import into Postman.

---

Happy coding! 🚀
