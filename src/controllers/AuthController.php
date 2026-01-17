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

        // If an OTP challenge is pending, continue it.
        if (sessionGet('admin_pending_otp', false) === true) {
            redirect(appUrl('/admin/otp'));
        }
        
        $this->render('admin/login', [
            'pageTitle' => 'Admin Login',
            'errors' => flashGet('validation_errors', []),
            'email' => flashGet('old_email', '')
        ]);
    }

    /**
     * Show logout confirmation page (GET).
     *
     * GET must not perform state changes; logout is performed via POST with CSRF protection.
     */
    public function showLogout(): void
    {
        if (!$this->isLoggedIn()) {
            redirect(appUrl('/admin/login'));
        }

        $this->render('admin/logout', [
            'pageTitle' => 'Confirm Logout',
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
        if (!rateLimitPersistent($rateLimitKey, 5, 300)) {
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

        // Start OTP challenge (MFA)
        try {
            require_once SRC_PATH . '/services/OtpService.php';
            OtpService::sendAdminLoginOtp(
                (int) $user['id'],
                (string) $user['email'],
                (string) (getClientIp() ?? ''),
                (string) ($_SERVER['HTTP_USER_AGENT'] ?? '')
            );
        } catch (Throwable $e) {
            $msg = trim(preg_replace('/\s+/', ' ', (string) $e->getMessage()) ?? '');
            $msg = mb_substr($msg, 0, 220, 'UTF-8');

            $logPath = (defined('STORAGE_PATH') ? STORAGE_PATH : (APP_ROOT . '/storage')) . '/logs/otp.log';
            @error_log('Admin OTP send failed: ' . $msg . ' | IP=' . (string) getClientIp(), 3, $logPath);

            $hint = APP_DEBUG && $msg !== '' ? (' Details: ' . $msg) : '';
            flashSet('error', 'Failed to send OTP. Please try again.' . $hint);
            redirect(appUrl('/admin/login'));
            return;
        }

        // Store pending admin identity in session (not fully logged in yet)
        sessionSet('admin_pending_otp', true);
        sessionSet('admin_pending_user', [
            'id' => (int) $user['id'],
            'email' => (string) $user['email'],
            'username' => (string) $user['username'],
            'full_name' => (string) $user['full_name'],
        ]);
        sessionSet('admin_pending_started_at', time());

        flashSet('success', 'OTP sent to your email. Please enter it to continue.');
        redirect(appUrl('/admin/otp'));
    }
    
    /**
     * Logout
     */
    public function logout(): void
    {
        // CSRF protection (logout is a state-changing action)
        csrfProtect();

        // Clear admin session
        sessionRemove('admin_user');
        sessionRemove('admin_logged_in');
        sessionRemove('admin_login_time');
        sessionRemove('admin_pending_otp');
        sessionRemove('admin_pending_user');
        sessionRemove('admin_pending_started_at');
        
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

    /**
     * Show OTP entry page for admin login.
     */
    public function showOtp(): void
    {
        if ($this->isLoggedIn()) {
            redirect(appUrl('/admin/dashboard'));
        }

        $pending = sessionGet('admin_pending_user');
        if (!is_array($pending) || sessionGet('admin_pending_otp', false) !== true) {
            flashSet('error', 'Please login to continue.');
            redirect(appUrl('/admin/login'));
        }

        $this->render('admin/otp', [
            'pageTitle' => 'Admin OTP Verification',
            'errors' => flashGet('validation_errors', []),
            'pendingEmail' => (string) ($pending['email'] ?? ''),
        ]);
    }

    /**
     * Verify OTP for admin login.
     */
    public function verifyOtp(): void
    {
        csrfProtect();

        if ($this->isLoggedIn()) {
            redirect(appUrl('/admin/dashboard'));
        }

        $pending = sessionGet('admin_pending_user');
        if (!is_array($pending) || sessionGet('admin_pending_otp', false) !== true) {
            flashSet('error', 'Please login to continue.');
            redirect(appUrl('/admin/login'));
        }

        $otp = sanitizeString($_POST['otp'] ?? '');
        if ($otp === '') {
            flashSet('error', 'OTP is required.');
            redirect(appUrl('/admin/otp'));
        }

        $userId = (int) ($pending['id'] ?? 0);
        $email = (string) ($pending['email'] ?? '');

        require_once SRC_PATH . '/services/OtpService.php';
        $ok = OtpService::verifyAdminLoginOtp($userId, $email, $otp);
        if (!$ok) {
            error_log(sprintf(
                "[%s] Failed admin OTP verification for user_id=%d email=%s from IP=%s",
                date('Y-m-d H:i:s'),
                $userId,
                substr((string) $email, 0, 20) . '***',
                getClientIp()
            ));
            flashSet('error', 'Invalid or expired OTP. Please try again.');
            redirect(appUrl('/admin/otp'));
        }

        // Now mark as fully logged in
        sessionRegenerate();
        sessionSet('admin_user', [
            'id' => $userId,
            'email' => $email,
            'username' => (string) ($pending['username'] ?? ''),
            'full_name' => (string) ($pending['full_name'] ?? ''),
        ]);
        sessionSet('admin_logged_in', true);
        sessionSet('admin_login_time', time());

        sessionRemove('admin_pending_otp');
        sessionRemove('admin_pending_user');
        sessionRemove('admin_pending_started_at');

        // Update last login only after MFA succeeds
        dbExecute(
            "UPDATE admin_users SET last_login_at = NOW() WHERE id = :id",
            ['id' => $userId]
        );

        flashSet('success', 'Welcome back!');
        redirect(appUrl('/admin/dashboard'));
    }

    /**
     * Resend OTP for admin login.
     */
    public function resendOtp(): void
    {
        csrfProtect();

        if ($this->isLoggedIn()) {
            redirect(appUrl('/admin/dashboard'));
        }

        $pending = sessionGet('admin_pending_user');
        if (!is_array($pending) || sessionGet('admin_pending_otp', false) !== true) {
            flashSet('error', 'Please login to continue.');
            redirect(appUrl('/admin/login'));
        }

        try {
            require_once SRC_PATH . '/services/OtpService.php';
            OtpService::sendAdminLoginOtp(
                (int) ($pending['id'] ?? 0),
                (string) ($pending['email'] ?? ''),
                (string) (getClientIp() ?? ''),
                (string) ($_SERVER['HTTP_USER_AGENT'] ?? '')
            );
            flashSet('success', 'OTP resent. Please check your email.');
        } catch (Throwable $e) {
            $msg = trim(preg_replace('/\s+/', ' ', (string) $e->getMessage()) ?? '');
            $msg = mb_substr($msg, 0, 220, 'UTF-8');

            $logPath = (defined('STORAGE_PATH') ? STORAGE_PATH : (APP_ROOT . '/storage')) . '/logs/otp.log';
            @error_log('Admin OTP resend failed: ' . $msg . ' | IP=' . (string) getClientIp(), 3, $logPath);

            $hint = APP_DEBUG && $msg !== '' ? (' Details: ' . $msg) : '';
            flashSet('error', 'Failed to resend OTP.' . $hint);
        }

        redirect(appUrl('/admin/otp'));
    }

    /**
     * Cancel OTP flow and return to login.
     */
    public function cancelOtp(): void
    {
        csrfProtect();

        sessionRemove('admin_pending_otp');
        sessionRemove('admin_pending_user');
        sessionRemove('admin_pending_started_at');

        flashSet('success', 'OTP verification cancelled.');
        redirect(appUrl('/admin/login'));
    }
}
