# Integration Guide

Guide for integrating Patone with third-party services and building custom integrations.

## Overview

Patone provides a RESTful API and extensible architecture for integrations with:
- Mobile applications
- GPS devices
- Payment gateways
- Mapping services
- Notification services (Email/SMS)
- CRM systems
- Accounting software

## API Integration

### Authentication

**Current Method: Session-based**

1. Login via web interface or API
2. Session cookie automatically managed
3. Include cookie with all requests

**Example:**
```bash
# Login
curl -X POST http://your-domain.com/login \
  -d "username=admin&password=admin123" \
  -c cookies.txt

# Use session
curl -X GET http://your-domain.com/api/customers \
  -b cookies.txt
```

**Recommended for Production: JWT**

Coming in v1.1:
```bash
# Get token
curl -X POST http://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Response
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}

# Use token
curl -X GET http://your-domain.com/api/customers \
  -H "Authorization: Bearer eyJhbGci..."
```

### API Endpoints

See [API Documentation](../api/README.md) for complete endpoint reference.

**Key Endpoints:**
- `/api/customers` - Customer management
- `/api/drivers` - Driver management
- `/api/requests` - Service requests
- `/api/service-types` - Service catalog
- `/api/dashboard-stats` - Statistics

## Mobile App Integration

### Building a Driver Mobile App

**Required Features:**
1. Driver login
2. View assigned requests
3. Update request status
4. Upload GPS location
5. Navigation integration
6. Push notifications

**API Calls Needed:**

```javascript
// Login
POST /api/auth/login
{
  "username": "driver@example.com",
  "password": "password"
}

// Get assigned requests
GET /api/drivers/{driverId}/requests

// Update location
PUT /api/drivers/{driverId}/location
{
  "latitude": 37.7749,
  "longitude": -122.4194
}

// Update request status
PUT /api/requests/{requestId}/status
{
  "status": "in_progress"
}

// Complete request
POST /api/requests/{requestId}/complete
{
  "notes": "Service completed successfully",
  "final_cost": 75.00
}
```

### Sample React Native App Structure

```javascript
// services/api.js
class PatoneAPI {
  constructor(baseUrl) {
    this.baseUrl = baseUrl;
    this.token = null;
  }

  async login(username, password) {
    const response = await fetch(`${this.baseUrl}/api/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password })
    });
    const data = await response.json();
    this.token = data.token;
    return data;
  }

  async getAssignedRequests(driverId) {
    const response = await fetch(
      `${this.baseUrl}/api/drivers/${driverId}/requests`,
      {
        headers: { 'Authorization': `Bearer ${this.token}` }
      }
    );
    return await response.json();
  }

  async updateLocation(driverId, latitude, longitude) {
    const response = await fetch(
      `${this.baseUrl}/api/drivers/${driverId}/location`,
      {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ latitude, longitude })
      }
    );
    return await response.json();
  }
}

export default PatoneAPI;
```

## GPS Device Integration

### Real-time Location Updates

**Option 1: Direct API Integration**

If GPS device supports HTTP requests:

```bash
# Device POSTs location updates
curl -X PUT http://your-domain.com/api/drivers/{driverId}/location \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "latitude": 37.7749,
    "longitude": -122.4194,
    "speed": 45.5,
    "heading": 180,
    "accuracy": 10
  }'
```

**Option 2: GPS Platform Integration**

For platforms like Geotab, Samsara, or Verizon Connect:

1. **Webhook Handler:**

```php
// backend/controllers/GpsWebhookController.php
class GpsWebhookController extends Controller {
    public function receiveUpdate() {
        // Verify webhook signature
        $signature = $_SERVER['HTTP_X_GPS_SIGNATURE'];
        if (!$this->verifySignature($signature, file_get_contents('php://input'))) {
            http_response_code(401);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        // Map GPS device ID to driver
        $driverId = $this->getDriverByDeviceId($data['device_id']);
        
        if ($driverId) {
            $driver = new Driver();
            $driver->updateLocation($driverId, [
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'speed' => $data['speed'],
                'heading' => $data['heading']
            ]);
        }

        http_response_code(200);
        echo json_encode(['success' => true]);
    }
}
```

2. **Configure Webhook URL:**

In GPS platform dashboard, set webhook URL to:
```
https://your-domain.com/gps/webhook
```

## Mapping Services Integration

### Google Maps Integration

**Frontend Implementation:**

```html
<!-- Add Google Maps script -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>

<div id="map" style="height: 400px; width: 100%;"></div>

<script>
function initMap() {
  // Create map
  const map = new google.maps.Map(document.getElementById('map'), {
    zoom: 12,
    center: { lat: 37.7749, lng: -122.4194 }
  });

  // Add driver markers
  fetch('/api/drivers/available')
    .then(response => response.json())
    .then(data => {
      data.data.forEach(driver => {
        new google.maps.Marker({
          position: {
            lat: parseFloat(driver.current_latitude),
            lng: parseFloat(driver.current_longitude)
          },
          map: map,
          title: driver.first_name + ' ' + driver.last_name,
          icon: {
            url: '/assets/images/driver-marker.png',
            scaledSize: new google.maps.Size(32, 32)
          }
        });
      });
    });
}

initMap();
</script>
```

### Mapbox Integration

```javascript
mapboxgl.accessToken = 'YOUR_MAPBOX_TOKEN';
const map = new mapboxgl.Map({
  container: 'map',
  style: 'mapbox://styles/mapbox/streets-v11',
  center: [-122.4194, 37.7749],
  zoom: 12
});

// Add driver markers
fetch('/api/drivers/available')
  .then(response => response.json())
  .then(data => {
    data.data.forEach(driver => {
      new mapboxgl.Marker()
        .setLngLat([driver.current_longitude, driver.current_latitude])
        .setPopup(new mapboxgl.Popup().setHTML(
          `<h3>${driver.first_name} ${driver.last_name}</h3>
           <p>Rating: ${driver.rating}/5</p>`
        ))
        .addTo(map);
    });
  });
```

## Payment Gateway Integration

### Stripe Integration

**1. Install Stripe PHP Library:**

```bash
composer require stripe/stripe-php
```

**2. Create Payment Controller:**

```php
<?php
require_once 'vendor/autoload.php';

class PaymentController extends Controller {
    private $stripe;

    public function __construct() {
        parent::__construct();
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    }

    public function createPaymentIntent() {
        $requestId = intval($_POST['request_id']);
        $amount = floatval($_POST['amount']) * 100; // Convert to cents

        try {
            $intent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                'metadata' => [
                    'request_id' => $requestId
                ]
            ]);

            $this->jsonSuccess([
                'clientSecret' => $intent->client_secret
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->jsonError($e->getMessage(), 400);
        }
    }

    public function handleWebhook() {
        $payload = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, STRIPE_WEBHOOK_SECRET
            );
        } catch (\Exception $e) {
            http_response_code(400);
            exit();
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handleSuccessfulPayment($paymentIntent);
                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $this->handleFailedPayment($paymentIntent);
                break;
        }

        http_response_code(200);
    }

    private function handleSuccessfulPayment($paymentIntent) {
        $requestId = $paymentIntent->metadata->request_id;
        
        // Update service request
        $request = new ServiceRequest();
        $request->update($requestId, [
            'payment_status' => 'paid',
            'payment_id' => $paymentIntent->id
        ]);
        
        // Send receipt to customer
        $this->sendReceipt($requestId);
    }
}
```

**3. Frontend Integration:**

```html
<script src="https://js.stripe.com/v3/"></script>

<form id="payment-form">
  <div id="card-element"></div>
  <button id="submit-button">Pay Now</button>
  <div id="payment-result"></div>
</form>

<script>
const stripe = Stripe('YOUR_PUBLISHABLE_KEY');
const elements = stripe.elements();
const cardElement = elements.create('card');
cardElement.mount('#card-element');

document.getElementById('payment-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  // Create payment intent
  const response = await fetch('/payment/create-intent', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      request_id: requestId,
      amount: amount
    })
  });
  
  const { clientSecret } = await response.json();
  
  // Confirm payment
  const { error, paymentIntent } = await stripe.confirmCardPayment(
    clientSecret,
    {
      payment_method: {
        card: cardElement,
        billing_details: {
          name: customerName
        }
      }
    }
  );
  
  if (error) {
    document.getElementById('payment-result').textContent = error.message;
  } else if (paymentIntent.status === 'succeeded') {
    document.getElementById('payment-result').textContent = 'Payment successful!';
  }
});
</script>
```

## Email Notifications

### Using PHPMailer

**1. Install PHPMailer:**

```bash
composer require phpmailer/phpmailer
```

**2. Create Email Service:**

```php
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // SMTP Configuration
        $this->mailer->isSMTP();
        $this->mailer->Host = EMAIL_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = EMAIL_USERNAME;
        $this->mailer->Password = EMAIL_PASSWORD;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = EMAIL_PORT;
        
        $this->mailer->setFrom(EMAIL_FROM, 'Patone Roadside Assistance');
    }

    public function sendServiceRequestNotification($customerId, $requestId) {
        $customer = (new Customer())->getById($customerId);
        $request = (new ServiceRequest())->getById($requestId);

        $this->mailer->addAddress($customer['email'], 
            $customer['first_name'] . ' ' . $customer['last_name']);
        
        $this->mailer->isHTML(true);
        $this->mailer->Subject = 'Service Request Confirmation #' . $requestId;
        $this->mailer->Body = $this->getEmailTemplate('request_created', [
            'customer_name' => $customer['first_name'],
            'request_id' => $requestId,
            'service_type' => $request['service_type'],
            'location' => $request['location_address']
        ]);

        try {
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email send failed: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    private function getEmailTemplate($template, $data) {
        ob_start();
        extract($data);
        include "templates/email/$template.php";
        return ob_get_clean();
    }
}
```

## SMS Notifications (Twilio)

**1. Install Twilio SDK:**

```bash
composer require twilio/sdk
```

**2. Create SMS Service:**

```php
<?php
use Twilio\Rest\Client;

class SMSService {
    private $client;
    private $fromNumber;

    public function __construct() {
        $this->client = new Client(
            TWILIO_ACCOUNT_SID,
            TWILIO_AUTH_TOKEN
        );
        $this->fromNumber = TWILIO_PHONE_NUMBER;
    }

    public function sendDriverAssignment($driverId, $requestId) {
        $driver = (new Driver())->getById($driverId);
        $request = (new ServiceRequest())->getById($requestId);

        $message = "New service request assigned! " .
                   "Request #{$requestId} at {$request['location_address']}. " .
                   "View details in the app.";

        try {
            $this->client->messages->create(
                $driver['phone'],
                [
                    'from' => $this->fromNumber,
                    'body' => $message
                ]
            );
            return true;
        } catch (\Exception $e) {
            error_log("SMS send failed: " . $e->getMessage());
            return false;
        }
    }

    public function sendRequestUpdate($customerId, $requestId, $status) {
        $customer = (new Customer())->getById($customerId);
        
        $statusMessages = [
            'assigned' => 'A driver has been assigned to your request.',
            'in_progress' => 'The driver is now working on your service.',
            'completed' => 'Your service has been completed. Thank you!'
        ];

        $message = "Service Request #{$requestId}: " . 
                   $statusMessages[$status];

        try {
            $this->client->messages->create(
                $customer['phone'],
                [
                    'from' => $this->fromNumber,
                    'body' => $message
                ]
            );
            return true;
        } catch (\Exception $e) {
            error_log("SMS send failed: " . $e->getMessage());
            return false;
        }
    }
}
```

## CRM Integration

### Salesforce Integration

```php
<?php
class SalesforceIntegration {
    private $client;
    private $accessToken;

    public function __construct() {
        // Authenticate with Salesforce
        $this->authenticate();
    }

    private function authenticate() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, SALESFORCE_AUTH_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'password',
            'client_id' => SALESFORCE_CLIENT_ID,
            'client_secret' => SALESFORCE_CLIENT_SECRET,
            'username' => SALESFORCE_USERNAME,
            'password' => SALESFORCE_PASSWORD
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        $this->accessToken = $response['access_token'];
        $this->instanceUrl = $response['instance_url'];
    }

    public function syncCustomer($customerId) {
        $customer = (new Customer())->getById($customerId);
        
        // Create or update contact in Salesforce
        $data = [
            'FirstName' => $customer['first_name'],
            'LastName' => $customer['last_name'],
            'Email' => $customer['email'],
            'Phone' => $customer['phone'],
            'MailingStreet' => $customer['address'],
            'MailingCity' => $customer['city'],
            'MailingState' => $customer['state'],
            'MailingPostalCode' => $customer['zip']
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 
            $this->instanceUrl . '/services/data/v54.0/sobjects/Contact/');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}
```

## Webhook Implementation

### Creating Webhook System

**1. Webhook Model:**

```php
<?php
class Webhook extends Model {
    protected $table = 'webhooks';

    public function trigger($event, $data) {
        // Get all webhooks subscribed to this event
        $webhooks = $this->getByEvent($event);

        foreach ($webhooks as $webhook) {
            $this->sendWebhook($webhook['url'], $event, $data, $webhook['secret']);
        }
    }

    private function sendWebhook($url, $event, $data, $secret) {
        $payload = json_encode([
            'event' => $event,
            'data' => $data,
            'timestamp' => time()
        ]);

        // Create signature
        $signature = hash_hmac('sha256', $payload, $secret);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Patone-Signature: ' . $signature,
            'X-Patone-Event: ' . $event
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log webhook delivery
        $this->logWebhookDelivery($webhook['id'], $httpCode, $response);

        return $httpCode >= 200 && $httpCode < 300;
    }
}
```

**2. Triggering Webhooks:**

```php
// In RequestController after creating a request
$webhook = new Webhook();
$webhook->trigger('request.created', [
    'request_id' => $requestId,
    'customer_id' => $customerId,
    'service_type' => $serviceType,
    'location' => $location
]);
```

**3. Receiving Webhooks:**

```php
// Client code to receive webhooks
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PATONE_SIGNATURE'];
$event = $_SERVER['HTTP_X_PATONE_EVENT'];

// Verify signature
$expectedSignature = hash_hmac('sha256', $payload, YOUR_WEBHOOK_SECRET);
if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(401);
    exit;
}

// Process webhook
$data = json_decode($payload, true);

switch ($event) {
    case 'request.created':
        handleNewRequest($data['data']);
        break;
    case 'request.completed':
        handleCompletedRequest($data['data']);
        break;
}

http_response_code(200);
```

## Best Practices

### Security

1. **API Keys:** Store securely, never commit to version control
2. **Webhooks:** Always verify signatures
3. **Rate Limiting:** Implement to prevent abuse
4. **HTTPS:** Always use HTTPS in production
5. **Input Validation:** Validate all external input

### Error Handling

```php
try {
    $result = $externalService->callAPI();
} catch (\Exception $e) {
    // Log error
    error_log("Integration error: " . $e->getMessage());
    
    // Queue for retry (implement retry logic)
    $this->queueForRetry($operation, $data);
    
    // Return graceful error
    return ['success' => false, 'error' => 'Service temporarily unavailable'];
}
```

### Logging

```php
function logIntegrationCall($service, $operation, $data, $response) {
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'service' => $service,
        'operation' => $operation,
        'request' => $data,
        'response' => $response,
        'duration' => $duration
    ];
    
    file_put_contents(
        LOG_PATH . 'integrations.log',
        json_encode($log) . PHP_EOL,
        FILE_APPEND
    );
}
```

## Testing Integrations

### Using Postman

1. Import OpenAPI specification
2. Configure environment variables
3. Test each endpoint
4. Create test collections
5. Automate with Newman

### Creating Test Sandbox

```php
// config_test.php
define('DB_NAME', 'roadside_assistance_test');
define('STRIPE_SECRET_KEY', 'sk_test_...');
define('TWILIO_TEST_MODE', true);
```

## Support and Resources

- [API Documentation](../api/README.md)
- [OpenAPI Specification](../api/openapi.yaml)
- [API Examples](../api/EXAMPLES.md)
- GitHub Issues for support

---

**Need help with integration?** Open an issue on GitHub or contact our support team.
