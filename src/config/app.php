<?php
/**
 * ETEEAP Survey Application - Application Configuration
 * 
 * Core application constants and settings.
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

// ============================================
// Application Settings
// ============================================
define('APP_NAME', 'ETEEAP Survey');
define('APP_VERSION', '1.0.0');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', APP_ENV === 'development');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

// ============================================
// Survey Configuration
// ============================================
define('SURVEY_TOTAL_STEPS', 8);
define('SURVEY_SESSION_LIFETIME', 86400); // 24 hours in seconds
define('SURVEY_STEP_NAMES', [
    1 => 'Data Privacy Consent',
    2 => 'Basic Information',
    3 => 'Office & Employment Data',
    4 => 'Work Experience',
    5 => 'Social Work Competencies',
    6 => 'Educational Background',
    7 => 'DSWD Academy Courses',
    8 => 'ETEEAP Interest'
]);

// ============================================
// Path Definitions
// ============================================
define('SRC_PATH', APP_ROOT . '/src');
define('VIEWS_PATH', SRC_PATH . '/views');
define('CONTROLLERS_PATH', SRC_PATH . '/controllers');
define('MODELS_PATH', SRC_PATH . '/models');
define('HELPERS_PATH', SRC_PATH . '/helpers');
define('PUBLIC_PATH', APP_ROOT . '/public');
define('ASSETS_PATH', '/assets');

// ============================================
// Session Configuration
// ============================================
define('SESSION_NAME', 'eteeap_session');
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_SECURE', APP_ENV === 'production');
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Lax');

// ============================================
// Security Settings
// ============================================
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('BCRYPT_COST', 12);

// ============================================
// Pagination Defaults
// ============================================
define('PAGINATION_PER_PAGE', 20);
define('PAGINATION_MAX_PAGES', 10);

// ============================================
// Export Settings
// ============================================
define('EXPORT_DATETIME_FORMAT', 'Y-m-d_His');
define('EXPORT_CSV_DELIMITER', ',');
define('EXPORT_CSV_ENCLOSURE', '"');

// ============================================
// Error Handling
// ============================================
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

// Set default timezone
date_default_timezone_set('Asia/Manila');

// ============================================
// Helper Functions
// ============================================

/**
 * Get the base URL for the application
 * 
 * @param string $path Optional path to append
 * @return string
 */
function appUrl(string $path = ''): string
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Get the asset URL
 * 
 * @param string $path
 * @return string
 */
function assetUrl(string $path): string
{
    return ASSETS_PATH . '/' . ltrim($path, '/');
}

/**
 * Redirect to a URL
 * 
 * @param string $url
 * @param int $statusCode
 */
function redirect(string $url, int $statusCode = 302): void
{
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Get environment variable with default fallback
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env(string $key, $default = null)
{
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

/**
 * Check if current environment is production
 * 
 * @return bool
 */
function isProduction(): bool
{
    return APP_ENV === 'production';
}

/**
 * Check if current environment is development
 * 
 * @return bool
 */
function isDevelopment(): bool
{
    return APP_ENV === 'development';
}
