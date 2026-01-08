<?php
/**
 * ETEEAP Survey Application - Session Helper
 * 
 * Secure session management functions.
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

/**
 * Initialize session with secure settings
 */
function sessionStart(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    
    // Set session cookie parameters
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => SESSION_SECURE,
        'httponly' => SESSION_HTTPONLY,
        'samesite' => SESSION_SAMESITE
    ]);
    
    session_name(SESSION_NAME);
    session_start();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = time();
    } elseif (time() - $_SESSION['_created'] > 1800) {
        // Regenerate every 30 minutes
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
}

/**
 * Regenerate session ID (call after sensitive actions like login/consent)
 */
function sessionRegenerate(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
}

/**
 * Destroy session completely
 */
function sessionDestroy(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();
    }
}

/**
 * Get a session value with optional default
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function sessionGet(string $key, $default = null)
{
    sessionStart();
    return $_SESSION[$key] ?? $default;
}

/**
 * Set a session value
 * 
 * @param string $key
 * @param mixed $value
 */
function sessionSet(string $key, $value): void
{
    sessionStart();
    $_SESSION[$key] = $value;
}

/**
 * Check if session key exists
 * 
 * @param string $key
 * @return bool
 */
function sessionHas(string $key): bool
{
    sessionStart();
    return isset($_SESSION[$key]);
}

/**
 * Remove a session value
 * 
 * @param string $key
 */
function sessionRemove(string $key): void
{
    sessionStart();
    unset($_SESSION[$key]);
}

/**
 * Get all session data
 * 
 * @return array
 */
function sessionAll(): array
{
    sessionStart();
    return $_SESSION;
}

/**
 * Flash message: Set a message that will be removed after first read
 * 
 * @param string $key
 * @param mixed $value
 */
function flashSet(string $key, $value): void
{
    sessionStart();
    $_SESSION['_flash'][$key] = $value;
}

/**
 * Flash message: Get and remove a flash message
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function flashGet(string $key, $default = null)
{
    sessionStart();
    if (isset($_SESSION['_flash'][$key])) {
        $value = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
    return $default;
}

/**
 * Check if flash message exists
 * 
 * @param string $key
 * @return bool
 */
function flashHas(string $key): bool
{
    sessionStart();
    return isset($_SESSION['_flash'][$key]);
}

// ============================================
// Survey-Specific Session Functions
// ============================================

/**
 * Get or create a survey session
 * 
 * @return array Survey session data
 */
function getSurveySession(): array
{
    sessionStart();
    
    if (!isset($_SESSION['survey'])) {
        $_SESSION['survey'] = [
            'session_id' => bin2hex(random_bytes(32)),
            'current_step' => 1,
            'consent_given' => false,
            'started_at' => time(),
            'data' => []
        ];
    }
    
    return $_SESSION['survey'];
}

/**
 * Update survey session data
 * 
 * @param array $data
 */
function updateSurveySession(array $data): void
{
    sessionStart();
    
    if (!isset($_SESSION['survey'])) {
        getSurveySession();
    }
    
    $_SESSION['survey'] = array_merge($_SESSION['survey'], $data);
}

/**
 * Store step data in survey session
 * 
 * @param int $step
 * @param array $data
 */
function saveSurveyStepData(int $step, array $data): void
{
    sessionStart();
    
    if (!isset($_SESSION['survey'])) {
        getSurveySession();
    }
    
    $_SESSION['survey']['data'][$step] = $data;
    $_SESSION['survey']['current_step'] = max($_SESSION['survey']['current_step'], $step + 1);
}

/**
 * Get step data from survey session
 * 
 * @param int $step
 * @return array
 */
function getSurveyStepData(int $step): array
{
    sessionStart();
    return $_SESSION['survey']['data'][$step] ?? [];
}

/**
 * Get all survey data from session
 * 
 * @return array
 */
function getAllSurveyData(): array
{
    sessionStart();
    $survey = getSurveySession();
    
    $allData = [];
    foreach ($survey['data'] as $stepData) {
        $allData = array_merge($allData, $stepData);
    }
    
    return $allData;
}

/**
 * Clear survey session
 */
function clearSurveySession(): void
{
    sessionStart();
    unset($_SESSION['survey']);
}

/**
 * Check if survey is completed (all steps done)
 * 
 * @return bool
 */
function isSurveyComplete(): bool
{
    $survey = getSurveySession();
    return $survey['current_step'] > SURVEY_TOTAL_STEPS;
}

/**
 * Get current survey step
 * 
 * @return int
 */
function getCurrentSurveyStep(): int
{
    $survey = getSurveySession();
    return min($survey['current_step'], SURVEY_TOTAL_STEPS);
}
