<?php
/**
 * ETEEAP Survey Application - Security Helper
 * 
 * Security hardening functions to prevent information leakage.
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

// ============================================
// Security Headers
// ============================================

/**
 * Ensure a per-request CSP nonce exists and return it.
 *
 * @return string Base64-encoded nonce - MUST be HTML-escaped when output into HTML attributes.
 */
function cspNonce(): string
{
    static $nonce = null;

    if (!is_string($nonce) || $nonce === '') {
        // 256-bit nonce, base64 encoded
        $nonce = base64_encode(random_bytes(32));
        // Basic sanity check: ensure the nonce is safe to embed in attributes after HTML-escaping.
        if (!preg_match('/^[A-Za-z0-9+\\/]+=*$/', $nonce)) {
            throw new RuntimeException('Invalid CSP nonce generated.');
        }
    }

    return $nonce;
}

/**
 * HTML-escaped CSP nonce for use in attributes.
 */
function cspNonceEscaped(): string
{
    return htmlspecialchars(cspNonce(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Set comprehensive security headers
 */
function setSecurityHeaders(): void
{
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // XSS Protection (legacy browsers)
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer Policy - prevent leaking URLs
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    $nonce = cspNonce();

    // Content Security Policy - restrict resource loading
    // Note: Inline styles are intentionally blocked; keep styles in compiled CSS files.
    $csp = "default-src 'self'; " .
           "script-src 'self' 'nonce-{$nonce}'; " .
           "script-src-attr 'none'; " .
           "style-src 'self' https://fonts.googleapis.com; " .
           "style-src-attr 'none'; " .
           "font-src 'self' https://fonts.gstatic.com; " .
           "img-src 'self' data: https:; " .
           "connect-src 'self'; " .
           "object-src 'none'; " .
           "frame-ancestors 'self'; " .
           "frame-src 'self'; " .
           "form-action 'self'; " .
           "base-uri 'self';";

    // Enforce HTTPS upgrades only in production (avoid breaking local HTTP dev)
    if (isProduction()) {
        $csp .= " upgrade-insecure-requests;";
    }
    header("Content-Security-Policy: " . $csp);
    
    // Permissions Policy - restrict browser features
    header("Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()");
    
    // Prevent caching of sensitive pages
    if (isAdminRoute()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
    }
    
    // HTTPS only in production
    if (isProduction()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/**
 * Check if current request is to admin route
 */
function isAdminRoute(): bool
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return strpos($uri, '/admin') === 0;
}

// ============================================
// Input Sanitization
// ============================================

/**
 * Sanitize all input data recursively
 * 
 * @param mixed $data
 * @return mixed
 */
function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    if (is_string($data)) {
        // Remove null bytes
        $data = str_replace(chr(0), '', $data);
        // Trim whitespace
        $data = trim($data);
        // Remove potentially dangerous characters
        $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $data);
    }
    
    return $data;
}

/**
 * Sanitize output for HTML display
 * 
 * @param string $data
 * @return string
 */
function sanitizeOutput(string $data): string
{
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ============================================
// Error Handling
// ============================================

/**
 * Custom error handler that prevents information leakage
 */
function secureErrorHandler(int $errno, string $errstr, string $errfile, int $errline): bool
{
    // Log error details internally
    $logMessage = sprintf(
        "[%s] Error %d: %s in %s on line %d",
        date('Y-m-d H:i:s'),
        $errno,
        $errstr,
        basename($errfile), // Only log filename, not full path
        $errline
    );
    
    error_log($logMessage);
    
    // In production, don't expose error details
    if (!APP_DEBUG) {
        return true; // Prevents default error handler
    }
    
    return false; // Let default handler show error in development
}

/**
 * Custom exception handler that prevents information leakage
 */
function secureExceptionHandler(Throwable $exception): void
{
    // Log exception details internally
    $logMessage = sprintf(
        "[%s] Exception: %s in %s on line %d\nStack trace: %s",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        basename($exception->getFile()),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    
    error_log($logMessage);
    
    // In production, show generic error
    if (!APP_DEBUG) {
        http_response_code(500);
        include VIEWS_PATH . '/errors/500.php';
        exit;
    }
    
    // In development, show detailed error
    throw $exception;
}

/**
 * Shutdown handler for fatal errors
 */
function secureShutdownHandler(): void
{
    $error = error_get_last();
    
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        error_log(sprintf(
            "[%s] Fatal Error: %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $error['message'],
            basename($error['file']),
            $error['line']
        ));
        
        if (!APP_DEBUG) {
            // Clean any output
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            http_response_code(500);
            echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
            echo '<h1>An error occurred</h1>';
            echo '<p>We apologize for the inconvenience. Please try again later.</p>';
            echo '</body></html>';
        }
    }
}

// ============================================
// Rate Limiting
// ============================================

/**
 * Simple rate limiter using session
 * 
 * @param string $key Unique identifier for the action
 * @param int $maxAttempts Maximum attempts allowed
 * @param int $windowSeconds Time window in seconds
 * @return bool True if allowed, false if rate limited
 */
function rateLimit(string $key, int $maxAttempts = 5, int $windowSeconds = 60): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return true; // Can't rate limit without session
    }
    
    $now = time();
    $sessionKey = 'rate_limit_' . $key;
    
    $attempts = $_SESSION[$sessionKey] ?? [];
    
    // Remove old attempts outside the window
    $attempts = array_filter($attempts, fn($timestamp) => $timestamp > ($now - $windowSeconds));
    
    // Check if limit exceeded
    if (count($attempts) >= $maxAttempts) {
        return false;
    }
    
    // Add current attempt
    $attempts[] = $now;
    $_SESSION[$sessionKey] = $attempts;
    
    return true;
}

/**
 * Simple persistent rate limiter using the filesystem (best-effort).
 *
 * This is harder to bypass than the session-based limiter because it survives cookie/session rotation.
 * It is still approximate and should be replaced with a shared store (Redis/DB) for multi-server deployments.
 */
function rateLimitPersistent(string $key, int $maxAttempts = 5, int $windowSeconds = 60): bool
{
    $now = time();
    $file = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
        . 'eteeap_rate_limit_' . hash('sha256', $key) . '.json';

    $handle = @fopen($file, 'c+');
    if ($handle === false) {
        return true; // best-effort: do not block if storage isn't writable
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return true; // best-effort
        }

        $raw = stream_get_contents($handle);
        $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
        $attempts = is_array($decoded) ? $decoded : [];

        $attempts = array_values(array_filter($attempts, static fn($ts) => is_int($ts) && $ts > ($now - $windowSeconds)));

        if (count($attempts) >= $maxAttempts) {
            return false;
        }

        $attempts[] = $now;

        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, json_encode($attempts));
        fflush($handle);

        return true;
    } finally {
        @flock($handle, LOCK_UN);
        fclose($handle);
    }
}

/**
 * Get remaining rate limit attempts
 * 
 * @param string $key
 * @param int $maxAttempts
 * @param int $windowSeconds
 * @return int
 */
function getRateLimitRemaining(string $key, int $maxAttempts = 5, int $windowSeconds = 60): int
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return $maxAttempts;
    }
    
    $now = time();
    $sessionKey = 'rate_limit_' . $key;
    $attempts = $_SESSION[$sessionKey] ?? [];
    $attempts = array_filter($attempts, fn($timestamp) => $timestamp > ($now - $windowSeconds));
    
    return max(0, $maxAttempts - count($attempts));
}

// ============================================
// SQL Injection Prevention
// ============================================

/**
 * Validate that a string contains only safe characters for identifiers
 * 
 * @param string $identifier
 * @return bool
 */
function isSafeIdentifier(string $identifier): bool
{
    return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier) === 1;
}

/**
 * Whitelist table names for queries
 * 
 * @param string $tableName
 * @return bool
 */
function isAllowedTable(string $tableName): bool
{
    $allowedTables = [
        'survey_responses',
        'response_program_assignments',
        'response_sw_tasks',
        'response_expertise_areas',
        'response_dswd_courses',
        'response_motivations',
        'response_barriers',
        'admin_users'
    ];
    
    return in_array($tableName, $allowedTables, true);
}

// ============================================
// XSS Prevention
// ============================================

/**
 * Escape HTML entities
 * 
 * @param string|null $string
 * @return string
 */
function e(?string $string): string
{
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Escape for use in JavaScript
 * 
 * @param string $string
 * @return string
 */
function escapeJs(string $string): string
{
    return json_encode($string, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
}

// ============================================
// IP and Request Validation
// ============================================

/**
 * Get client IP address safely
 * 
 * @return string
 */
function getClientIp(): string
{
    // Direct connection
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Validate IP format
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return '0.0.0.0';
    }
    
    return $ip;
}

/**
 * Validate request method
 * 
 * @param string $expected Expected method (GET, POST, etc.)
 * @return bool
 */
function validateRequestMethod(string $expected): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === strtoupper($expected);
}

/**
 * Check if request is AJAX
 * 
 * @return bool
 */
function isAjaxRequest(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// ============================================
// File Security
// ============================================

/**
 * Validate file path to prevent directory traversal
 * 
 * @param string $path
 * @param string $basePath
 * @return bool
 */
function isPathSafe(string $path, string $basePath): bool
{
    $realBase = realpath($basePath);
    $realPath = realpath($path);
    
    if ($realBase === false || $realPath === false) {
        return false;
    }
    
    return strpos($realPath, $realBase) === 0;
}

// ============================================
// Password Security
// ============================================

/**
 * Check password strength
 * 
 * @param string $password
 * @return array ['valid' => bool, 'errors' => array]
 */
function checkPasswordStrength(string $password): array
{
    $errors = [];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

// ============================================
// Initialize Security
// ============================================

/**
 * Initialize all security measures
 */
function initializeSecurity(): void
{
    // Ensure CSP nonce is generated early so templates can use it
    cspNonce();

    // Set error handlers (only in production)
    if (!APP_DEBUG) {
        set_error_handler('secureErrorHandler');
        set_exception_handler('secureExceptionHandler');
        register_shutdown_function('secureShutdownHandler');
    }
    
    // Set security headers
    setSecurityHeaders();
    
    // Hide PHP version
    header_remove('X-Powered-By');
    
    // Sanitize superglobals
    $_GET = sanitizeInput($_GET);
    $_POST = sanitizeInput($_POST);
    $_REQUEST = sanitizeInput($_REQUEST);
}
