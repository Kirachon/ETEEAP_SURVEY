<?php
/**
 * ETEEAP Survey Application - InfinityFree Entry Point
 * 
 * Upload this to htdocs/index.php on InfinityFree
 */

// Define application root
define('APP_ROOT', __DIR__);

// Load local config FIRST (for InfinityFree database credentials)
if (file_exists(APP_ROOT . '/src/config/config.local.php')) {
    require_once APP_ROOT . '/src/config/config.local.php';
}

// Load configuration
require_once APP_ROOT . '/src/config/app.php';
require_once APP_ROOT . '/src/config/database.php';

// Load helpers
require_once APP_ROOT . '/src/helpers/csrf.php';
require_once APP_ROOT . '/src/helpers/session.php';
require_once APP_ROOT . '/src/helpers/validation.php';
require_once APP_ROOT . '/src/helpers/export.php';
require_once APP_ROOT . '/src/helpers/security.php';

// Initialize security measures
initializeSecurity();

// Start session
sessionStart();

// Get request URI and method
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Remove query string from URI
$requestUri = strtok($requestUri, '?');

// Remove base path if present
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
    'GET /install' => ['controller' => 'InstallController', 'action' => 'index'],
    'POST /install' => ['controller' => 'InstallController', 'action' => 'install'],
    'GET /survey/consent' => ['controller' => 'SurveyController', 'action' => 'showStep', 'params' => ['step' => 1]],
    'POST /survey/consent' => ['controller' => 'SurveyController', 'action' => 'saveStep', 'params' => ['step' => 1]],
    'GET /survey/step/{step}' => ['controller' => 'SurveyController', 'action' => 'showStep'],
    'POST /survey/step/{step}' => ['controller' => 'SurveyController', 'action' => 'saveStep'],
    'GET /survey/verify-email' => ['controller' => 'SurveyController', 'action' => 'showVerifyEmail'],
    'POST /survey/verify-email' => ['controller' => 'SurveyController', 'action' => 'verifyEmail'],
    'POST /survey/verify-email/resend' => ['controller' => 'SurveyController', 'action' => 'resendEmailOtp'],
    'GET /survey/thank-you' => ['controller' => 'SurveyController', 'action' => 'thankYou'],
    'GET /survey/declined' => ['controller' => 'SurveyController', 'action' => 'declined'],
    
    // Admin routes
    'GET /admin' => ['controller' => 'AdminController', 'action' => 'dashboard'],
    'GET /admin/login' => ['controller' => 'AuthController', 'action' => 'showLogin'],
    'POST /admin/login' => ['controller' => 'AuthController', 'action' => 'login'],
    'GET /admin/otp' => ['controller' => 'AuthController', 'action' => 'showOtp'],
    'POST /admin/otp' => ['controller' => 'AuthController', 'action' => 'verifyOtp'],
    'POST /admin/otp/resend' => ['controller' => 'AuthController', 'action' => 'resendOtp'],
    'POST /admin/otp/cancel' => ['controller' => 'AuthController', 'action' => 'cancelOtp'],
    'GET /admin/logout' => ['controller' => 'AuthController', 'action' => 'showLogout'],
    'POST /admin/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    'GET /admin/dashboard' => ['controller' => 'AdminController', 'action' => 'dashboard'],
    'GET /admin/responses' => ['controller' => 'AdminController', 'action' => 'responses'],
    'GET /admin/responses/{id}' => ['controller' => 'AdminController', 'action' => 'viewResponse'],
    'GET /admin/export/csv' => ['controller' => 'AdminController', 'action' => 'exportCsv'],
    'GET /admin/import/csv' => ['controller' => 'AdminController', 'action' => 'importCsvForm'],
    'POST /admin/import/csv' => ['controller' => 'AdminController', 'action' => 'importCsvUpload'],
    'POST /admin/import/template' => ['controller' => 'AdminController', 'action' => 'exportImportTemplate'],

    // Reports
    'GET /admin/reports' => ['controller' => 'AdminController', 'action' => 'reports'],
    'GET /admin/reports/generate' => ['controller' => 'AdminController', 'action' => 'generateReport'],
    'POST /admin/reports/generate' => ['controller' => 'AdminController', 'action' => 'generateReport'],
    'GET /admin/reports/export/{type}' => ['controller' => 'AdminController', 'action' => 'exportReport'],
     
    // API routes (admin)
    'GET /api/stats/summary' => ['controller' => 'ApiController', 'action' => 'summary'],
    'GET /api/stats/demographics' => ['controller' => 'ApiController', 'action' => 'demographics'],
    'GET /api/stats/interest' => ['controller' => 'ApiController', 'action' => 'interest'],
    'GET /api/stats/timeline' => ['controller' => 'ApiController', 'action' => 'timeline'],

    // Public API routes (for survey dropdowns)
    'GET /api/positions' => ['controller' => 'PublicApiController', 'action' => 'positions'],
    'GET /api/ched-programs' => ['controller' => 'PublicApiController', 'action' => 'chedPrograms'],
    'GET /api/obs' => ['controller' => 'PublicApiController', 'action' => 'obs'],
    'GET /api/attached-agencies' => ['controller' => 'PublicApiController', 'action' => 'attachedAgencies'],
    'GET /api/courses' => ['controller' => 'PublicApiController', 'action' => 'courses'],

    // Survey submission API
    'POST /api/survey/submit' => ['controller' => 'SurveyApiController', 'action' => 'submit'],
];

// ============================================
// Simple Router
// ============================================

function matchRoute(string $method, string $uri, array $routes): ?array
{
    $routeKey = $method . ' ' . $uri;
    
    if (isset($routes[$routeKey])) {
        return $routes[$routeKey];
    }
    
    foreach ($routes as $pattern => $handler) {
        $parts = explode(' ', $pattern, 2);
        if (count($parts) !== 2) continue;
        
        [$routeMethod, $routePath] = $parts;
        
        if ($routeMethod !== $method) continue;
        
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $routePath);
        $regex = '#^' . $regex . '$#';
        
        if (preg_match($regex, $uri, $matches)) {
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
        http_response_code(404);
        include VIEWS_PATH . '/errors/404.php';
        exit;
    }
    
    $controllerName = $route['controller'];
    $actionName = $route['action'];
    $params = $route['params'] ?? [];
    
    // Load dependencies for controllers that need authentication.
    // IMPORTANT: ApiController extends AuthController, so AuthController MUST be loaded first.
    if (in_array($controllerName, ['AdminController', 'ApiController'], true)) {
        require_once CONTROLLERS_PATH . '/AuthController.php';
    }

    $controllerFile = CONTROLLERS_PATH . '/' . $controllerName . '.php';

    if (!file_exists($controllerFile)) {
        throw new Exception("Controller not found: {$controllerName}");
    }

    require_once $controllerFile;
    
    if (!class_exists($controllerName)) {
        throw new Exception("Controller class not found: {$controllerName}");
    }
    
    $controller = new $controllerName();
    
    if (!method_exists($controller, $actionName)) {
        throw new Exception("Action not found: {$controllerName}::{$actionName}");
    }
    
    call_user_func_array([$controller, $actionName], $params);
    
} catch (Exception $e) {
    http_response_code(500);
    
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo '<h1>Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        include VIEWS_PATH . '/errors/500.php';
    }
}
