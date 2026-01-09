<?php
/**
 * ETEEAP Survey Application - Main Entry Point & Router
 * 
 * All requests are routed through this file.
 */

// Define application root
define('APP_ROOT', dirname(__DIR__));

// Load configuration
require_once APP_ROOT . '/src/config/app.php';
require_once APP_ROOT . '/src/config/database.php';

// Load helpers
require_once APP_ROOT . '/src/helpers/csrf.php';
require_once APP_ROOT . '/src/helpers/session.php';
require_once APP_ROOT . '/src/helpers/validation.php';
require_once APP_ROOT . '/src/helpers/export.php';
require_once APP_ROOT . '/src/helpers/security.php';

// Initialize security measures (headers, error handlers)
initializeSecurity();

// Start session
sessionStart();

// Get request URI and method
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Remove query string from URI
$requestUri = strtok($requestUri, '?');

// Remove base path if present (for subdirectory installations)
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/' && $basePath !== '\\') {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Normalize URI
$requestUri = '/' . trim($requestUri, '/');

// ============================================
// Route Definitions
// ============================================

$routes = [
    // Survey routes
    'GET /' => ['controller' => 'SurveyController', 'action' => 'index'],
    'GET /survey' => ['controller' => 'SurveyController', 'action' => 'index'],

    // Installer (disabled after install.lock is created)
    'GET /install' => ['controller' => 'InstallController', 'action' => 'index'],
    'POST /install' => ['controller' => 'InstallController', 'action' => 'install'],
    'GET /survey/consent' => ['controller' => 'SurveyController', 'action' => 'showStep', 'params' => ['step' => 1]],
    'POST /survey/consent' => ['controller' => 'SurveyController', 'action' => 'saveStep', 'params' => ['step' => 1]],
    'GET /survey/step/{step}' => ['controller' => 'SurveyController', 'action' => 'showStep'],
    'POST /survey/step/{step}' => ['controller' => 'SurveyController', 'action' => 'saveStep'],
    'GET /survey/thank-you' => ['controller' => 'SurveyController', 'action' => 'thankYou'],
    'GET /survey/declined' => ['controller' => 'SurveyController', 'action' => 'declined'],
    
    // Admin routes
    'GET /admin' => ['controller' => 'AdminController', 'action' => 'dashboard'],
    'GET /admin/login' => ['controller' => 'AuthController', 'action' => 'showLogin'],
    'POST /admin/login' => ['controller' => 'AuthController', 'action' => 'login'],
    // GET shows a confirmation page (no state change); POST performs the logout
    'GET /admin/logout' => ['controller' => 'AuthController', 'action' => 'showLogout'],
    'POST /admin/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    'GET /admin/dashboard' => ['controller' => 'AdminController', 'action' => 'dashboard'],
    'GET /admin/responses' => ['controller' => 'AdminController', 'action' => 'responses'],
    'GET /admin/responses/{id}' => ['controller' => 'AdminController', 'action' => 'viewResponse'],
    'GET /admin/export/csv' => ['controller' => 'AdminController', 'action' => 'exportCsv'],
    
    // API routes (for charts)
    'GET /api/stats/demographics' => ['controller' => 'ApiController', 'action' => 'demographics'],
    'GET /api/stats/interest' => ['controller' => 'ApiController', 'action' => 'interest'],
    'GET /api/stats/timeline' => ['controller' => 'ApiController', 'action' => 'timeline'],
];

// ============================================
// Simple Router
// ============================================

/**
 * Match route with parameters
 * 
 * @param string $method
 * @param string $uri
 * @param array $routes
 * @return array|null
 */
function matchRoute(string $method, string $uri, array $routes): ?array
{
    $routeKey = $method . ' ' . $uri;
    
    // Direct match
    if (isset($routes[$routeKey])) {
        return $routes[$routeKey];
    }
    
    // Pattern matching for dynamic routes
    foreach ($routes as $pattern => $handler) {
        // Extract method and path from pattern
        $parts = explode(' ', $pattern, 2);
        if (count($parts) !== 2) continue;
        
        [$routeMethod, $routePath] = $parts;
        
        if ($routeMethod !== $method) continue;
        
        // Convert route pattern to regex
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $routePath);
        $regex = '#^' . $regex . '$#';
        
        if (preg_match($regex, $uri, $matches)) {
            // Extract named parameters
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $handler['params'] = array_merge($handler['params'] ?? [], $params);
            return $handler;
        }
    }
    
    return null;
}

// ============================================
// Request Handler
// ============================================

try {
    $route = matchRoute($requestMethod, $requestUri, $routes);
    
    if ($route === null) {
        // 404 Not Found
        http_response_code(404);
        include VIEWS_PATH . '/errors/404.php';
        exit;
    }
    
    $controllerName = $route['controller'];
    $actionName = $route['action'];
    $params = $route['params'] ?? [];
    
    // Load and instantiate controller
    $controllerFile = CONTROLLERS_PATH . '/' . $controllerName . '.php';
    
    if (!file_exists($controllerFile)) {
        throw new Exception("Controller not found: {$controllerName}");
    }
    
    require_once $controllerFile;
    
    // Load dependencies for controllers that need authentication
    if (in_array($controllerName, ['AdminController', 'ApiController'])) {
        require_once CONTROLLERS_PATH . '/AuthController.php';
    }
    
    if (!class_exists($controllerName)) {
        throw new Exception("Controller class not found: {$controllerName}");
    }
    
    $controller = new $controllerName();
    
    if (!method_exists($controller, $actionName)) {
        throw new Exception("Action not found: {$controllerName}::{$actionName}");
    }
    
    // Call the action with parameters
    call_user_func_array([$controller, $actionName], $params);
    
} catch (Exception $e) {
    // Error handling
    http_response_code(500);
    
    if (APP_DEBUG) {
        echo '<h1>Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        include VIEWS_PATH . '/errors/500.php';
    }
}
