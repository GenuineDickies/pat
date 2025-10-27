<?php
/**
 * Roadside Assistance Admin Platform - Router
 * Handles URL routing and request dispatching
 */

class Router {
    private $routes = [];

    // Add route to router
    public function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    // Dispatch request to appropriate controller and action
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = $this->getRequestPath();

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestPath)) {
                $this->callController($route['controller'], $route['action'], $this->extractParameters($route['path'], $requestPath));
                return;
            }
        }

        // Route not found
        $this->handleNotFound();
    }

    // Get clean request path
    private function getRequestPath() {
        $path = $_SERVER['REQUEST_URI'];

        // Remove query string
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }

        // Remove trailing slash (except for root)
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = substr($path, 0, -1);
        }

        return $path;
    }

    // Check if route path matches request path
    private function matchPath($routePath, $requestPath) {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        for ($i = 0; $i < count($routeParts); $i++) {
            if ($routeParts[$i] !== $requestParts[$i] && !preg_match('/^\{.*\}$/', $routeParts[$i])) {
                return false;
            }
        }

        return true;
    }

    // Extract parameters from route
    private function extractParameters($routePath, $requestPath) {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));
        $parameters = [];

        for ($i = 0; $i < count($routeParts); $i++) {
            if (preg_match('/^\{(.*)\}$/', $routeParts[$i], $matches)) {
                $parameters[$matches[1]] = $requestParts[$i];
            }
        }

        return $parameters;
    }

    // Call controller method
    private function callController($controllerName, $actionName, $parameters = []) {
        $controllerFile = BACKEND_PATH . 'controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            $this->handleError(500, "Controller file not found: $controllerFile");
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            $this->handleError(500, "Controller class not found: $controllerName");
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $actionName)) {
            $this->handleError(500, "Controller method not found: $controllerName::$actionName");
            return;
        }

        // Set parameters for dynamic routes
        if (!empty($parameters)) {
            foreach ($parameters as $key => $value) {
                $_GET[$key] = $value;
            }
        }

        try {
            $controller->$actionName();
        } catch (Exception $e) {
            error_log("Controller error: " . $e->getMessage());
            $this->handleError(500, "Internal server error");
        }
    }

    // Handle 404 errors
    private function handleNotFound() {
        header("HTTP/1.0 404 Not Found");
        $this->showErrorPage(404, 'Page Not Found', 'The requested page could not be found.');
    }

    // Handle general errors
    private function handleError($code, $message) {
        header("HTTP/1.0 $code " . $this->getStatusText($code));
        $this->showErrorPage($code, $this->getStatusText($code), $message);
    }

    // Show error page
    private function showErrorPage($code, $title, $message) {
        // In a real application, you'd want to render a proper error template
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>$title</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .error-container { max-width: 600px; margin: 0 auto; }
                .error-code { font-size: 72px; color: #dc3545; margin-bottom: 20px; }
                .error-message { font-size: 18px; color: #666; }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <div class='error-code'>$code</div>
                <h1>$title</h1>
                <p class='error-message'>$message</p>
                <p><a href='" . SITE_URL . "'>Return to Home</a></p>
            </div>
        </body>
        </html>";
        exit;
    }

    // Get HTTP status text
    private function getStatusText($code) {
        $statusTexts = [
            404 => 'Not Found',
            500 => 'Internal Server Error',
            403 => 'Forbidden',
            401 => 'Unauthorized'
        ];

        return $statusTexts[$code] ?? 'Unknown Error';
    }

    // Debug method to list all routes
    public function getRoutes() {
        return $this->routes;
    }
}
?>
