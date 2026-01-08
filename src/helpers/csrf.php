<?php
/**
 * ETEEAP Survey Application - CSRF Protection Helper
 * 
 * Provides CSRF token generation and validation.
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

/**
 * Generate a new CSRF token and store in session
 * 
 * @return string
 */
function csrfEnsureSessionStarted(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Prefer the application's hardened session bootstrap when available.
    if (function_exists('sessionStart')) {
        sessionStart();
        return;
    }

    if (!session_start()) {
        throw new RuntimeException('Failed to start session for CSRF protection.');
    }
}

function csrfGenerateToken(): string
{
    csrfEnsureSessionStarted();
    
    $token = bin2hex(random_bytes(32));
    $_SESSION[CSRF_TOKEN_NAME] = [
        'token' => $token,
        'expires' => time() + CSRF_TOKEN_LIFETIME
    ];
    
    return $token;
}

/**
 * Get the current CSRF token (generates new one if expired or missing)
 * 
 * @return string
 */
function csrfGetToken(): string
{
    csrfEnsureSessionStarted();
    
    // Check if token exists and is not expired
    if (
        isset($_SESSION[CSRF_TOKEN_NAME]) &&
        isset($_SESSION[CSRF_TOKEN_NAME]['expires']) &&
        $_SESSION[CSRF_TOKEN_NAME]['expires'] > time()
    ) {
        return $_SESSION[CSRF_TOKEN_NAME]['token'];
    }
    
    // Generate new token
    return csrfGenerateToken();
}

/**
 * Validate a CSRF token from form submission
 * 
 * @param string|null $token Token from form submission
 * @return bool
 */
function csrfValidateToken(?string $token): bool
{
    csrfEnsureSessionStarted();
    
    if (
        empty($token) ||
        !isset($_SESSION[CSRF_TOKEN_NAME]) ||
        !isset($_SESSION[CSRF_TOKEN_NAME]['token']) ||
        !isset($_SESSION[CSRF_TOKEN_NAME]['expires'])
    ) {
        return false;
    }
    
    // Check if token is expired
    if ($_SESSION[CSRF_TOKEN_NAME]['expires'] < time()) {
        unset($_SESSION[CSRF_TOKEN_NAME]);
        return false;
    }
    
    // Constant-time comparison to prevent timing attacks
    return hash_equals($_SESSION[CSRF_TOKEN_NAME]['token'], $token);
}

/**
 * Regenerate CSRF token (call after successful form submission)
 * 
 * @return string New token
 */
function csrfRegenerateToken(): string
{
    if (isset($_SESSION[CSRF_TOKEN_NAME])) {
        unset($_SESSION[CSRF_TOKEN_NAME]);
    }
    return csrfGenerateToken();
}

/**
 * Output a hidden CSRF input field
 * 
 * @return string HTML input element
 */
function csrfInputField(): string
{
    $token = csrfGetToken();
    return sprintf(
        '<input type="hidden" name="%s" value="%s">',
        htmlspecialchars(CSRF_TOKEN_NAME, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
    );
}

/**
 * Output a meta tag for CSRF token (useful for AJAX)
 * 
 * @return string HTML meta element
 */
function csrfMetaTag(): string
{
    $token = csrfGetToken();
    return sprintf(
        '<meta name="csrf-token" content="%s">',
        htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
    );
}

/**
 * Validate CSRF token from POST request and die if invalid
 * 
 * @param string $errorMessage Optional custom error message
 */
function csrfProtect(string $errorMessage = 'Invalid security token. Please refresh and try again.'): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!csrfValidateToken($token)) {
            http_response_code(403);
            if (APP_DEBUG) {
                die('CSRF validation failed: ' . $errorMessage);
            } else {
                die($errorMessage);
            }
        }
    }
}
