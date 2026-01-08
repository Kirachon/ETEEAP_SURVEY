<?php
/**
 * ETEEAP Survey Application - Authentication Controller
 * 
 * Handles admin login/logout.
 */

class AuthController
{
    /**
     * Show login form
     */
    public function showLogin(): void
    {
        // Redirect if already logged in
        if ($this->isLoggedIn()) {
            redirect(appUrl('/admin/dashboard'));
        }
        
        $this->render('admin/login', [
            'pageTitle' => 'Admin Login',
            'errors' => flashGet('validation_errors', []),
            'email' => flashGet('old_email', '')
        ]);
    }
    
    /**
     * Process login
     */
    public function login(): void
    {
        // CSRF protection
        csrfProtect();
        
        // Rate limiting: 5 attempts per 5 minutes
        $rateLimitKey = 'login_' . getClientIp();
        if (!rateLimit($rateLimitKey, 5, 300)) {
            flashSet('error', 'Too many login attempts. Please wait 5 minutes before trying again.');
            redirect(appUrl('/admin/login'));
        }
        
        $email = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Basic validation
        $errors = [];
        if (empty($email)) {
            $errors['email'][] = 'Email is required.';
        }
        if (empty($password)) {
            $errors['password'][] = 'Password is required.';
        }
        
        if (!empty($errors)) {
            flashSet('validation_errors', $errors);
            flashSet('old_email', $email);
            redirect(appUrl('/admin/login'));
        }
        
        // Find user
        $user = dbFetchOne(
            "SELECT * FROM admin_users WHERE email = :email AND is_active = 1 LIMIT 1",
            ['email' => $email]
        );
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            // Log failed attempt (for security monitoring)
            error_log(sprintf(
                "[%s] Failed login attempt for email: %s from IP: %s",
                date('Y-m-d H:i:s'),
                substr($email, 0, 20) . '***',  // Partial email for privacy
                getClientIp()
            ));
            
            // Generic error message to prevent user enumeration
            flashSet('error', 'Invalid email or password.');
            flashSet('old_email', $email);
            redirect(appUrl('/admin/login'));
        }
        
        // Check if active
        if (!$user['is_active']) {
            flashSet('error', 'Your account has been deactivated.');
            redirect(appUrl('/admin/login'));
        }
        
        // Regenerate session for security
        sessionRegenerate();
        
        // Store user in session
        sessionSet('admin_user', [
            'id' => $user['id'],
            'email' => $user['email'],
            'username' => $user['username'],
            'full_name' => $user['full_name']
        ]);
        sessionSet('admin_logged_in', true);
        sessionSet('admin_login_time', time());
        
        // Update last login
        dbExecute(
            "UPDATE admin_users SET last_login_at = NOW() WHERE id = :id",
            ['id' => $user['id']]
        );
        
        flashSet('success', 'Welcome back, ' . htmlspecialchars($user['username']) . '!');
        redirect(appUrl('/admin/dashboard'));
    }
    
    /**
     * Logout
     */
    public function logout(): void
    {
        // Clear admin session
        sessionRemove('admin_user');
        sessionRemove('admin_logged_in');
        sessionRemove('admin_login_time');
        
        // Regenerate session ID
        sessionRegenerate();
        
        flashSet('success', 'You have been logged out successfully.');
        redirect(appUrl('/admin/login'));
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return sessionGet('admin_logged_in', false) === true;
    }
    
    /**
     * Get current admin user
     * 
     * @return array|null
     */
    public function getUser(): ?array
    {
        return sessionGet('admin_user');
    }
    
    /**
     * Require authentication (for use in other controllers)
     */
    public static function requireAuth(): void
    {
        if (sessionGet('admin_logged_in', false) !== true) {
            flashSet('error', 'Please login to access this page.');
            redirect(appUrl('/admin/login'));
        }
        
        // Check session timeout (2 hours)
        $loginTime = sessionGet('admin_login_time', 0);
        if (time() - $loginTime > 7200) {
            sessionRemove('admin_user');
            sessionRemove('admin_logged_in');
            sessionRemove('admin_login_time');
            flashSet('error', 'Your session has expired. Please login again.');
            redirect(appUrl('/admin/login'));
        }
    }
    
    /**
     * Render a view
     * 
     * @param string $view
     * @param array $data
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include view
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo '<div class="p-8 text-red-600">View not found: ' . htmlspecialchars($view) . '</div>';
        }
        
        // Get content
        $content = ob_get_clean();
        
        // For login page, use minimal layout
        echo $content;
    }
}
