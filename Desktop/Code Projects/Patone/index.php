<?php
/**
 * Roadside Assistance Admin Platform - Main Entry Point
 * Routes requests and initializes the application
 */

// Include configuration
require_once 'config.php';

// Include router
require_once BACKEND_PATH . 'controllers/Router.php';

// Initialize router
$router = new Router();

// Define routes
$router->addRoute('GET', '/', 'DashboardController', 'index');
$router->addRoute('GET', '/login', 'AuthController', 'login');
$router->addRoute('POST', '/login', 'AuthController', 'doLogin');
$router->addRoute('GET', '/logout', 'AuthController', 'logout');
$router->addRoute('GET', '/dashboard', 'DashboardController', 'index');
$router->addRoute('GET', '/customers', 'CustomerController', 'index');
$router->addRoute('GET', '/customers/add', 'CustomerController', 'add');
$router->addRoute('POST', '/customers/add', 'CustomerController', 'doAdd');
$router->addRoute('GET', '/customers/edit/{id}', 'CustomerController', 'edit');
$router->addRoute('POST', '/customers/edit/{id}', 'CustomerController', 'doEdit');
$router->addRoute('GET', '/customers/delete/{id}', 'CustomerController', 'delete');
$router->addRoute('GET', '/requests', 'RequestController', 'index');
$router->addRoute('GET', '/requests/add', 'RequestController', 'add');
$router->addRoute('POST', '/requests/add', 'RequestController', 'doAdd');
$router->addRoute('GET', '/requests/{id}', 'RequestController', 'view');
$router->addRoute('POST', '/requests/update/{id}', 'RequestController', 'update');
$router->addRoute('GET', '/drivers', 'DriverController', 'index');
$router->addRoute('GET', '/drivers/add', 'DriverController', 'add');
$router->addRoute('POST', '/drivers/add', 'DriverController', 'doAdd');
$router->addRoute('GET', '/drivers/edit/{id}', 'DriverController', 'edit');
$router->addRoute('POST', '/drivers/edit/{id}', 'DriverController', 'doEdit');
$router->addRoute('GET', '/reports', 'ReportController', 'index');
$router->addRoute('GET', '/reports/daily', 'ReportController', 'daily');
$router->addRoute('GET', '/reports/monthly', 'ReportController', 'monthly');
$router->addRoute('GET', '/settings', 'SettingController', 'index');
$router->addRoute('POST', '/settings', 'SettingController', 'update');

// API routes - Authentication
$router->addRoute('POST', '/api/login', 'ApiController', 'login');
$router->addRoute('POST', '/api/logout', 'ApiController', 'logout');
$router->addRoute('POST', '/api/refresh', 'ApiController', 'refresh');

// API routes - Customers
$router->addRoute('GET', '/api/customers', 'ApiController', 'getCustomers');
$router->addRoute('POST', '/api/customers', 'ApiController', 'createCustomer');
$router->addRoute('GET', '/api/customers/{id}', 'ApiController', 'getCustomer');
$router->addRoute('PUT', '/api/customers/{id}', 'ApiController', 'updateCustomer');
$router->addRoute('DELETE', '/api/customers/{id}', 'ApiController', 'deleteCustomer');

// API routes - Service Requests
$router->addRoute('GET', '/api/requests', 'ApiController', 'getRequests');
$router->addRoute('POST', '/api/requests', 'ApiController', 'createRequest');
$router->addRoute('GET', '/api/requests/{id}', 'ApiController', 'getRequest');
$router->addRoute('PUT', '/api/requests/{id}', 'ApiController', 'updateRequest');
$router->addRoute('DELETE', '/api/requests/{id}', 'ApiController', 'deleteRequest');

// API routes - Drivers
$router->addRoute('GET', '/api/drivers', 'ApiController', 'getDrivers');
$router->addRoute('GET', '/api/drivers/{id}', 'ApiController', 'getDriver');
$router->addRoute('PUT', '/api/drivers/{id}', 'ApiController', 'updateDriver');

// API routes - Reports
$router->addRoute('GET', '/api/reports/daily', 'ApiController', 'getDailyReport');
$router->addRoute('GET', '/api/reports/monthly', 'ApiController', 'getMonthlyReport');
$router->addRoute('GET', '/api/reports/custom', 'ApiController', 'getCustomReport');

// API routes - Other
$router->addRoute('GET', '/api/dashboard-stats', 'ApiController', 'getDashboardStats');
$router->addRoute('GET', '/api/service-types', 'ApiController', 'getServiceTypes');
$router->addRoute('GET', '/api/drivers/available', 'ApiController', 'getAvailableDrivers');
$router->addRoute('POST', '/api/drivers/{id}/location', 'ApiController', 'updateDriverLocation');
$router->addRoute('POST', '/api/requests/{id}/status', 'ApiController', 'updateRequestStatus');

// Handle request
$router->dispatch();
?>
