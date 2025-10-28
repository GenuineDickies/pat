# Architecture Overview

Understanding the Patone platform architecture.

## System Architecture

Patone follows a **Model-View-Controller (MVC)** architecture pattern for clean code organization and maintainability.

```
┌─────────────────────────────────────────────────────────┐
│                     Presentation Layer                   │
│  ┌────────────┐  ┌────────────┐  ┌─────────────────┐   │
│  │  Web UI    │  │  Mobile    │  │  Third-party    │   │
│  │  (Browser) │  │    App     │  │  Integration    │   │
│  └──────┬─────┘  └──────┬─────┘  └────────┬────────┘   │
└─────────┼────────────────┼─────────────────┼────────────┘
          │                │                 │
          ▼                ▼                 ▼
┌─────────────────────────────────────────────────────────┐
│                    Application Layer                     │
│  ┌──────────────────────────────────────────────────┐   │
│  │              Router (index.php)                   │   │
│  └────────────────────┬─────────────────────────────┘   │
│                       │                                  │
│  ┌────────────────────▼─────────────────────────────┐   │
│  │              Controllers                          │   │
│  │  ┌──────────┐  ┌───────────┐  ┌──────────────┐  │   │
│  │  │Dashboard │  │ Customer  │  │   Driver     │  │   │
│  │  │Controller│  │ Controller│  │  Controller  │  │   │
│  │  └──────────┘  └───────────┘  └──────────────┘  │   │
│  │  ┌──────────┐  ┌───────────┐  ┌──────────────┐  │   │
│  │  │ Request  │  │   API     │  │   Report     │  │   │
│  │  │Controller│  │ Controller│  │  Controller  │  │   │
│  │  └──────────┘  └───────────┘  └──────────────┘  │   │
│  └────────────────────┬─────────────────────────────┘   │
└───────────────────────┼─────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│                     Business Layer                       │
│  ┌────────────────────────────────────────────────┐     │
│  │                   Models                        │     │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────────┐  │    │
│  │  │ Customer │  │  Driver  │  │    Service   │  │    │
│  │  │  Model   │  │  Model   │  │    Request   │  │    │
│  │  └──────────┘  └──────────┘  └──────────────┘  │    │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────────┐  │    │
│  │  │   User   │  │ Service  │  │   Setting    │  │    │
│  │  │  Model   │  │   Type   │  │    Model     │  │    │
│  │  └──────────┘  └──────────┘  └──────────────┘  │    │
│  └────────────────────┬───────────────────────────┘     │
└───────────────────────┼─────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│                      Data Layer                          │
│  ┌────────────────────────────────────────────────┐     │
│  │         Database (MySQL/MariaDB)                │     │
│  │  ┌──────┐ ┌────────┐ ┌────────┐ ┌──────────┐  │     │
│  │  │users │ │customers│ │drivers │ │ service_ │  │     │
│  │  │      │ │         │ │        │ │ requests │  │     │
│  │  └──────┘ └────────┘ └────────┘ └──────────┘  │     │
│  └────────────────────────────────────────────────┘     │
└─────────────────────────────────────────────────────────┘
           │
           ▼
┌────────────────────┐         ┌──────────────────┐
│   Python Scripts   │         │   File Storage   │
│  - Report Gen      │         │   - Uploads      │
│  - Analytics       │         │   - Logs         │
│  - Data Analysis   │         │   - Backups      │
└────────────────────┘         └──────────────────┘
```

## Directory Structure

```
patone/
├── backend/                    # Backend PHP code
│   ├── config/                # Configuration files
│   │   └── database.php       # Database connection
│   ├── controllers/           # Request handlers
│   │   ├── Controller.php     # Base controller
│   │   ├── ApiController.php  # API endpoints
│   │   ├── AuthController.php # Authentication
│   │   ├── CustomerController.php
│   │   ├── DriverController.php
│   │   ├── RequestController.php
│   │   ├── ReportController.php
│   │   ├── SettingController.php
│   │   ├── DashboardController.php
│   │   └── Router.php         # URL routing
│   └── models/                # Data models
│       ├── Model.php          # Base model
│       ├── User.php           # User model
│       ├── Customer.php       # Customer model
│       ├── Driver.php         # Driver model
│       ├── ServiceRequest.php # Service request model
│       ├── ServiceType.php    # Service type model
│       └── Setting.php        # Settings model
├── frontend/                  # Frontend views
│   └── pages/                # Page templates
│       ├── layout.php        # Master layout
│       ├── login.php         # Login page
│       ├── dashboard.php     # Dashboard
│       ├── customers.php     # Customer list
│       ├── customer_form.php # Customer add/edit
│       ├── drivers.php       # Driver list
│       ├── driver_form.php   # Driver add/edit
│       ├── requests.php      # Request list
│       ├── request_form.php  # Request add/edit
│       ├── reports.php       # Reports page
│       └── settings.php      # Settings page
├── assets/                    # Static assets
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript
│   └── images/               # Images
├── database/                  # Database files
│   ├── schema.sql            # Database schema
│   └── migrations/           # Migration scripts
│       └── 001_initial_setup.php
├── python/                    # Python analytics
│   ├── report_generator.py   # Report generation
│   ├── data_analyzer.py      # Data analysis
│   ├── config.py             # Python config
│   └── requirements.txt      # Python dependencies
├── uploads/                   # File uploads
├── logs/                      # Application logs
├── tests/                     # Test files
│   ├── BasicTest.php         # Basic tests
│   └── README.md             # Test documentation
├── docs/                      # Documentation
├── config.php                 # Main configuration
├── index.php                  # Application entry point
└── .htaccess                 # Apache configuration
```

## Component Details

### 1. Entry Point (index.php)

The main entry point that:
- Initializes configuration
- Starts session
- Loads routing system
- Handles all HTTP requests

```php
<?php
require_once 'config.php';
require_once BACKEND_PATH . 'controllers/Router.php';

session_start();
$router = new Router();
$router->route($_GET['url'] ?? '');
```

### 2. Router

**File:** `backend/controllers/Router.php`

Responsibilities:
- Parse incoming URLs
- Map URLs to controllers and methods
- Handle HTTP methods (GET, POST, PUT, DELETE)
- Load appropriate controller
- Execute requested action

Example routes:
- `/customers` → `CustomerController::index()`
- `/customers/add` → `CustomerController::add()`
- `/api/customers` → `ApiController::getCustomers()`

### 3. Controllers

**Base Controller:** `backend/controllers/Controller.php`

All controllers extend the base controller which provides:
- View rendering
- JSON response helpers
- Session management
- Input validation
- Authentication checks

**Specialized Controllers:**

**AuthController**
- User login/logout
- Session management
- Password verification

**DashboardController**
- Dashboard statistics
- Quick action handlers
- Recent activity

**CustomerController**
- CRUD operations for customers
- Vehicle management
- Service history

**DriverController**
- CRUD operations for drivers
- Status management
- Location updates
- Performance metrics

**RequestController**
- Service request lifecycle
- Driver assignment
- Status updates
- Request completion

**ApiController**
- RESTful API endpoints
- JSON responses
- API authentication
- Mobile app support

**ReportController**
- Report generation
- Data export
- Analytics display

### 4. Models

**Base Model:** `backend/models/Model.php`

Provides:
- Database connection
- CRUD helper methods
- Query builder basics
- Data validation

**Specialized Models:**

Each model handles:
- Database operations for its table
- Business logic
- Data validation
- Relationships with other models

Example: Customer Model
```php
class Customer extends Model {
    protected $table = 'customers';
    
    public function getAll($limit, $offset, $search = '') {
        // Implementation
    }
    
    public function getById($id) {
        // Implementation
    }
    
    public function getVehicles($customerId) {
        // Relationship method
    }
}
```

### 5. Views

**Layout System:**

Master layout (`frontend/pages/layout.php`) includes:
- HTML structure
- Header with navigation
- Sidebar menu
- Content area
- Footer
- JavaScript includes

Individual pages extend the layout:
```php
<?php
$title = "Customers";
include_once 'layout.php';
?>

<div class="content">
    <!-- Page-specific content -->
</div>
```

### 6. Database Layer

**Connection:** Singleton pattern in `backend/config/database.php`

```php
class Database {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new mysqli(
                DB_HOST, DB_USER, DB_PASS, DB_NAME
            );
        }
        return self::$instance;
    }
}
```

**Prepared Statements:**

All queries use prepared statements:
```php
$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $customerId);
$stmt->execute();
```

### 7. Python Scripts

**Purpose:** Advanced analytics and reporting

**report_generator.py:**
- Generates PDF reports
- Exports data to various formats
- Scheduled report generation

**data_analyzer.py:**
- Statistical analysis
- Trend forecasting
- Performance metrics
- Customer behavior analysis

**Integration:**
- Called from PHP via exec()
- Communicates via database
- Can be scheduled with cron

## Data Flow

### Service Request Creation Flow

```
1. User submits form
   └─> POST /requests/add

2. Router receives request
   └─> RequestController::add()

3. Controller validates input
   └─> Sanitize and validate data

4. Controller calls Model
   └─> ServiceRequest::create($data)

5. Model saves to database
   └─> INSERT INTO service_requests

6. Model returns result
   └─> Success/failure status

7. Controller processes result
   └─> Set success message

8. Controller renders view
   └─> Redirect to request list

9. User sees confirmation
   └─> "Request created successfully"
```

### API Request Flow

```
1. Mobile app makes API call
   └─> GET /api/drivers/available

2. Router routes to API controller
   └─> ApiController::getAvailableDrivers()

3. Controller authenticates request
   └─> Check session/token

4. Controller calls Model
   └─> Driver::getAvailable()

5. Model queries database
   └─> SELECT * FROM drivers WHERE status='available'

6. Model returns data
   └─> Array of driver records

7. Controller formats response
   └─> JSON with success flag

8. Response sent to client
   └─> {"success": true, "data": [...]}

9. Mobile app processes data
   └─> Display available drivers
```

## Security Architecture

### Authentication Flow

```
1. User enters credentials
2. POST to /login
3. AuthController::login()
4. Validate credentials
5. Check username exists
6. Verify password (bcrypt)
7. Create session
8. Set session variables
9. Redirect to dashboard
```

### Authorization Checks

```php
function requireRole($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header('Location: /access-denied');
        exit;
    }
}
```

### Data Protection

1. **Input Validation:**
   - Sanitize all user inputs
   - Type checking
   - Length validation
   - Format validation

2. **Output Escaping:**
   - Escape HTML output
   - Prevent XSS attacks
   - Use htmlspecialchars()

3. **SQL Injection Prevention:**
   - Prepared statements only
   - No dynamic SQL construction
   - Parameterized queries

4. **CSRF Protection:**
   - Token generation
   - Token validation
   - Token expiration

## Performance Considerations

### Database Optimization

1. **Indexes:**
   - Primary keys
   - Foreign keys
   - Frequently queried columns
   - Status fields

2. **Query Optimization:**
   - Use appropriate JOINs
   - Limit result sets
   - Avoid N+1 queries
   - Use EXPLAIN for analysis

3. **Connection Management:**
   - Singleton pattern
   - Connection pooling (future)
   - Close when done

### Caching Strategy

**Recommended caching layers:**

1. **OPcache:** PHP bytecode caching
2. **Query Cache:** MySQL query results
3. **Redis/Memcached:** Application data
4. **Browser Cache:** Static assets

### Scalability

**Horizontal Scaling:**
- Load balancer distribution
- Stateless application design
- Session storage in Redis
- Database replication

**Vertical Scaling:**
- Increase server resources
- Optimize configuration
- Database tuning
- PHP-FPM optimization

## API Architecture

### RESTful Design

Endpoints follow REST principles:
- GET: Retrieve resources
- POST: Create resources
- PUT: Update resources
- DELETE: Remove resources

### Response Format

Consistent JSON structure:
```json
{
    "success": true|false,
    "data": {...}|[...],
    "error": "Error message" (if success=false),
    "message": "Success message" (if applicable)
}
```

### Versioning

Current: v1 (implicit)
Future: `/api/v2/...`

## Testing Architecture

### Test Structure

```
tests/
├── BasicTest.php          # Basic functionality tests
├── UnitTests/            # Unit tests (future)
├── IntegrationTests/     # Integration tests (future)
└── E2ETests/             # End-to-end tests (future)
```

### Test Coverage

Current:
- Database connectivity
- Model instantiation
- Basic CRUD operations

Recommended additions:
- Controller testing
- API endpoint testing
- Authentication testing
- Permission testing

## Deployment Architecture

### Production Setup

```
┌─────────────────┐
│  Load Balancer  │
└────────┬────────┘
         │
    ┌────┴────┐
    │         │
┌───▼──┐  ┌──▼───┐
│ Web  │  │ Web  │
│Server│  │Server│
│  1   │  │  2   │
└───┬──┘  └──┬───┘
    │         │
    └────┬────┘
         │
    ┌────▼────────┐
    │  Database   │
    │   Server    │
    └─────────────┘
```

### Backup Strategy

1. **Database:** Daily automated dumps
2. **Files:** Weekly uploads backup
3. **Configuration:** Version controlled
4. **Logs:** Retained 30 days

## Future Architecture Enhancements

### Planned Improvements

1. **Microservices:** Split monolith into services
2. **Message Queue:** Asynchronous job processing
3. **WebSocket:** Real-time updates
4. **Containerization:** Docker deployment
5. **CI/CD Pipeline:** Automated testing and deployment

### Technology Stack Evolution

**Current:**
- PHP 7.4+
- MySQL 5.7+
- jQuery (minimal)
- Python 3.8+

**Future Considerations:**
- PHP 8.x features
- MySQL 8.0 features
- Modern JavaScript framework (Vue/React)
- Redis for caching
- Elasticsearch for search

---

## Additional Resources

- [Developer Setup](../developer/SETUP.md)
- [API Documentation](../api/README.md)
- [Database Schema](../database/SCHEMA.md)
- [Deployment Guide](../deployment/README.md)

---

**Version:** 1.0  
**Last Updated:** October 2024
