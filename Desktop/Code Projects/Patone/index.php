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

// API routes
$router->addRoute('GET', '/api/customers', 'ApiController', 'getCustomers');
$router->addRoute('GET', '/api/requests', 'ApiController', 'getRequests');
$router->addRoute('GET', '/api/drivers', 'ApiController', 'getDrivers');
$router->addRoute('GET', '/api/dashboard-stats', 'ApiController', 'getDashboardStats');
$router->addRoute('GET', '/api/dashboard/stats', 'DashboardController', 'getStats');
$router->addRoute('GET', '/api/dashboard/recent-requests', 'DashboardController', 'getRecentRequests');
$router->addRoute('GET', '/api/dashboard/driver-status', 'DashboardController', 'getDriverStatus');
$router->addRoute('GET', '/api/dashboard/chart-data', 'DashboardController', 'getChartData');
$router->addRoute('GET', '/api/dashboard/recent-activity', 'DashboardController', 'getRecentActivity');
$router->addRoute('GET', '/api/dashboard/performance-metrics', 'DashboardController', 'getPerformanceMetrics');

// Handle request
$router->dispatch();
?>
