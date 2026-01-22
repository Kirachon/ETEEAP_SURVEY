<?php
/**
 * ETEEAP Survey Application - OTP Service
 *
 * Provides email-based OTP challenges for:
 * - Survey email verification
 * - Admin login (MFA)
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

class OtpService
{
    private const PURPOSE_SURVEY = 'survey_email_verify';
    private const PURPOSE_ADMIN = 'admin_login';

    private const OTP_DIGITS = 6;
    private const OTP_EXPIRES_SECONDS = 600; // 10 minutes
    private const OTP_MAX_ATTEMPTS = 5;
    private const OTP_RESEND_COOLDOWN_SECONDS = 60;

    private static function dummyHash(): string
    {
        return str_repeat('0', 64);
    }

    private static function isValidSurveySessionId(string $surveySessionId): bool
    {
        return $surveySessionId !== '' && preg_match('/^[a-f0-9]{16,128}$/i', $surveySessionId) === 1;
    }

    private static function otpSecret(): string
    {
        $cfgGet = function_exists('installConfigGet') ? 'installConfigGet' : null;
        $secret = getenv('OTP_SECRET');
        if (!is_string($secret) || $secret === '') {
            if ($cfgGet) {
                $secret = $cfgGet('OTP_SECRET', '');
            }
        }
        $secret = is_string($secret) ? trim($secret) : '';
        if ($secret === '') {
            throw new RuntimeException('OTP_SECRET is not configured. Set OTP_SECRET in config.local.php.');
        }
        return $secret;
    }

    private static function nowSql(): string
    {
        return date('Y-m-d H:i:s');
    }

    private static function expiresAtSql(): string
    {
        return date('Y-m-d H:i:s', time() + self::OTP_EXPIRES_SECONDS);
    }

    private static function normalizeEmail(string $email): string
    {
        $email = sanitizeEmail($email);
        $email = $email !== '' ? strtolower($email) : '';
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return '';
        }
        return $email;
    }

    private static function generateOtp(): string
    {
        $max = (10 ** self::OTP_DIGITS) - 1;
        $n = random_int(0, $max);
        return str_pad((string) $n, self::OTP_DIGITS, '0', STR_PAD_LEFT);
    }

    private static function hashOtp(string $otp): string
    {
        $otp = trim($otp);
        return hash_hmac('sha256', $otp, self::otpSecret());
    }

    private static function canResend(string $purpose, string $email, ?string $surveySessionId, ?int $adminUserId): bool
    {
        $params = [
            'purpose' => $purpose,
            'email' => $email,
        ];
        $where = 'purpose = :purpose AND email = :email AND consumed_at IS NULL';

        if ($surveySessionId !== null) {
            $where .= ' AND survey_session_id = :survey_session_id';
            $params['survey_session_id'] = $surveySessionId;
        }
        if ($adminUserId !== null) {
            $where .= ' AND admin_user_id = :admin_user_id';
            $params['admin_user_id'] = $adminUserId;
        }

        $row = dbFetchOne(
            "SELECT last_sent_at FROM otp_challenges WHERE {$where} ORDER BY id DESC LIMIT 1",
            $params
        );
        $last = $row['last_sent_at'] ?? null;
        if (!is_string($last) || $last === '') {
            return true;
        }
        $ts = strtotime($last);
        if ($ts === false) {
            return true;
        }
        return (time() - $ts) >= self::OTP_RESEND_COOLDOWN_SECONDS;
    }

    private static function invalidateActive(string $purpose, string $email, ?string $surveySessionId, ?int $adminUserId): void
    {
        $params = [
            'purpose' => $purpose,
            'email' => $email,
            'now' => self::nowSql(),
        ];
        $where = 'purpose = :purpose AND email = :email AND consumed_at IS NULL';

        if ($surveySessionId !== null) {
            $where .= ' AND survey_session_id = :survey_session_id';
            $params['survey_session_id'] = $surveySessionId;
        }
        if ($adminUserId !== null) {
            $where .= ' AND admin_user_id = :admin_user_id';
            $params['admin_user_id'] = $adminUserId;
        }

        dbExecute("UPDATE otp_challenges SET consumed_at = :now WHERE {$where}", $params);
    }

    private static function createChallenge(
        string $purpose,
        string $email,
        ?string $surveySessionId,
        ?int $adminUserId,
        string $ip,
        string $userAgent
    ): array {
        if (!function_exists('rateLimitPersistent')) {
            throw new RuntimeException('Rate limiting is not available.');
        }

        // Basic rate limiting (avoid abuse / account throttling)
        $emailKey = hash('sha256', $email);
        $ipKey = preg_replace('/[^0-9a-fA-F:.]/', '', $ip);
        $rateKey = "otp_send_{$purpose}_" . $ipKey . '_' . substr($emailKey, 0, 16);

        $globalEmailKey = "otp_send_{$purpose}_email_" . substr($emailKey, 0, 16);
        $globalIpKey = "otp_send_{$purpose}_ip_" . substr(hash('sha256', $ipKey !== '' ? $ipKey : 'unknown'), 0, 16);

        if (!rateLimitPersistent($globalEmailKey, 20, 3600)) {
            throw new RuntimeException('Too many OTP requests for this email. Please wait and try again.');
        }
        if (!rateLimitPersistent($globalIpKey, 60, 3600)) {
            throw new RuntimeException('Too many OTP requests. Please wait and try again.');
        }

        if (!rateLimitPersistent($rateKey, 10, 3600)) {
            throw new RuntimeException('Too many OTP requests. Please wait and try again.');
        }

        if (!self::canResend($purpose, $email, $surveySessionId, $adminUserId)) {
            throw new RuntimeException('Please wait before requesting a new OTP.');
        }

        $otp = self::generateOtp();
        $hash = self::hashOtp($otp);

        dbBeginTransaction();
        try {
            self::invalidateActive($purpose, $email, $surveySessionId, $adminUserId);

            dbInsert(
                "INSERT INTO otp_challenges (
                    purpose, email, code_hash, expires_at,
                    attempts, max_attempts, last_sent_at,
                    consumed_at, ip_address, user_agent,
                    survey_session_id, admin_user_id, created_at
                ) VALUES (
                    :purpose, :email, :code_hash, :expires_at,
                    0, :max_attempts, :last_sent_at,
                    NULL, :ip_address, :user_agent,
                    :survey_session_id, :admin_user_id, :created_at
                )",
                [
                    'purpose' => $purpose,
                    'email' => $email,
                    'code_hash' => $hash,
                    'expires_at' => self::expiresAtSql(),
                    'max_attempts' => self::OTP_MAX_ATTEMPTS,
                    'last_sent_at' => self::nowSql(),
                    'ip_address' => sanitizeString($ip),
                    'user_agent' => mb_substr(sanitizeString($userAgent), 0, 255, 'UTF-8'),
                    'survey_session_id' => $surveySessionId,
                    'admin_user_id' => $adminUserId,
                    'created_at' => self::nowSql(),
                ]
            );
            dbCommit();
        } catch (Throwable $e) {
            dbRollback();
            throw $e;
        }

        return [
            'otp' => $otp,
            'expires_seconds' => self::OTP_EXPIRES_SECONDS,
        ];
    }

    private static function sendOtpEmail(string $toEmail, string $purposeLabel, string $otp, int $expiresSeconds): void
    {
        $minutes = (int) ceil(max($expiresSeconds, 1) / 60);

        $subject = "{$purposeLabel} - One-Time Password (OTP)";
        $body = "Your OTP is: {$otp}\n\n"
            . "This code expires in {$minutes} minute(s).\n\n"
            . "If you did not request this, you can ignore this email.";

        // Default to PHPMailer (download into /PHPMailer). Fall back only if PHPMailer is not deployed.
        $phpMailerFile = SRC_PATH . '/services/PhpMailerMailer.php';
        if (is_file($phpMailerFile)) {
            require_once $phpMailerFile;
            if (class_exists('PhpMailerMailer') && PhpMailerMailer::isAvailable()) {
                PhpMailerMailer::sendText($toEmail, $subject, $body);
                return;
            }
        }

        require_once SRC_PATH . '/services/SmtpMailer.php';
        SmtpMailer::sendText($toEmail, $subject, $body);
    }

    /**
     * Send OTP for survey email verification.
     */
    public static function sendSurveyOtp(string $email, string $surveySessionId, string $ip, string $userAgent): void
    {
        $email = self::normalizeEmail($email);
        if ($email === '') {
            throw new InvalidArgumentException('Email is required.');
        }
        if (!self::isValidSurveySessionId($surveySessionId)) {
            throw new InvalidArgumentException('Survey session is invalid.');
        }

        $challenge = self::createChallenge(self::PURPOSE_SURVEY, $email, $surveySessionId, null, $ip, $userAgent);
        try {
            self::sendOtpEmail($email, 'Survey Email Verification', $challenge['otp'], (int) $challenge['expires_seconds']);
        } catch (Throwable $e) {
            // If delivery fails (SMTP/network), allow retry without waiting for cooldown.
            self::invalidateActive(self::PURPOSE_SURVEY, $email, $surveySessionId, null);
            throw $e;
        }
    }

    /**
     * Verify survey OTP.
     */
    public static function verifySurveyOtp(string $email, string $surveySessionId, string $otp): bool
    {
        $email = self::normalizeEmail($email);
        $otp = preg_replace('/\\D+/', '', (string) $otp) ?? '';

        if ($email === '' || !self::isValidSurveySessionId($surveySessionId) || $otp === '') {
            return false;
        }

        $actual = self::hashOtp($otp);

        dbBeginTransaction();
        try {
            $row = dbFetchOne(
                "SELECT id, code_hash, attempts, max_attempts, expires_at
                 FROM otp_challenges
                 WHERE purpose = :purpose
                   AND email = :email
                   AND survey_session_id = :survey_session_id
                   AND consumed_at IS NULL
                 ORDER BY id DESC
                 LIMIT 1
                 FOR UPDATE",
                [
                    'purpose' => self::PURPOSE_SURVEY,
                    'email' => $email,
                    'survey_session_id' => $surveySessionId,
                ]
            );

            if (!$row) {
                // Best-effort timing equalization
                hash_equals(self::dummyHash(), $actual);
                dbCommit();
                return false;
            }

            $expected = (string) ($row['code_hash'] ?? '');
            $expected = $expected !== '' ? $expected : self::dummyHash();
            $hashMatch = hash_equals($expected, $actual);

            $expiresAt = strtotime((string) ($row['expires_at'] ?? ''));
            $expiresOk = ($expiresAt !== false && $expiresAt >= time());

            $attempts = (int) ($row['attempts'] ?? 0);
            $maxAttempts = (int) ($row['max_attempts'] ?? self::OTP_MAX_ATTEMPTS);
            $attemptsOk = ($attempts < $maxAttempts);

            if (!$expiresOk || !$attemptsOk) {
                dbCommit();
                return false;
            }

            if (!$hashMatch) {
                dbExecute(
                    "UPDATE otp_challenges SET attempts = attempts + 1 WHERE id = :id",
                    ['id' => (int) $row['id']]
                );
                dbCommit();
                return false;
            }

            dbExecute(
                "UPDATE otp_challenges SET consumed_at = :now WHERE id = :id",
                ['now' => self::nowSql(), 'id' => (int) $row['id']]
            );
            dbCommit();
            return true;
        } catch (Throwable $e) {
            dbRollback();
            throw $e;
        }
    }

    /**
     * Send OTP for admin login.
     */
    public static function sendAdminLoginOtp(int $adminUserId, string $email, string $ip, string $userAgent): void
    {
        $email = self::normalizeEmail($email);
        if ($email === '' || $adminUserId <= 0) {
            throw new InvalidArgumentException('Invalid admin user.');
        }

        $challenge = self::createChallenge(self::PURPOSE_ADMIN, $email, null, $adminUserId, $ip, $userAgent);
        try {
            self::sendOtpEmail($email, 'Admin Login', $challenge['otp'], (int) $challenge['expires_seconds']);
        } catch (Throwable $e) {
            // If delivery fails (SMTP/network), allow retry without waiting for cooldown.
            self::invalidateActive(self::PURPOSE_ADMIN, $email, null, $adminUserId);
            throw $e;
        }
    }

    /**
     * Verify admin login OTP.
     */
    public static function verifyAdminLoginOtp(int $adminUserId, string $email, string $otp): bool
    {
        $email = self::normalizeEmail($email);
        $otp = preg_replace('/\\D+/', '', (string) $otp) ?? '';

        if ($email === '' || $adminUserId <= 0 || $otp === '') {
            return false;
        }

        $actual = self::hashOtp($otp);

        dbBeginTransaction();
        try {
            $row = dbFetchOne(
                "SELECT id, code_hash, attempts, max_attempts, expires_at
                 FROM otp_challenges
                 WHERE purpose = :purpose
                   AND email = :email
                   AND admin_user_id = :admin_user_id
                   AND consumed_at IS NULL
                 ORDER BY id DESC
                 LIMIT 1
                 FOR UPDATE",
                [
                    'purpose' => self::PURPOSE_ADMIN,
                    'email' => $email,
                    'admin_user_id' => $adminUserId,
                ]
            );

            if (!$row) {
                // Best-effort timing equalization
                hash_equals(self::dummyHash(), $actual);
                dbCommit();
                return false;
            }

            $expected = (string) ($row['code_hash'] ?? '');
            $expected = $expected !== '' ? $expected : self::dummyHash();
            $hashMatch = hash_equals($expected, $actual);

            $expiresAt = strtotime((string) ($row['expires_at'] ?? ''));
            $expiresOk = ($expiresAt !== false && $expiresAt >= time());

            $attempts = (int) ($row['attempts'] ?? 0);
            $maxAttempts = (int) ($row['max_attempts'] ?? self::OTP_MAX_ATTEMPTS);
            $attemptsOk = ($attempts < $maxAttempts);

            if (!$expiresOk || !$attemptsOk) {
                dbCommit();
                return false;
            }

            if (!$hashMatch) {
                dbExecute(
                    "UPDATE otp_challenges SET attempts = attempts + 1 WHERE id = :id",
                    ['id' => (int) $row['id']]
                );
                dbCommit();
                return false;
            }

            dbExecute(
                "UPDATE otp_challenges SET consumed_at = :now WHERE id = :id",
                ['now' => self::nowSql(), 'id' => (int) $row['id']]
            );
            dbCommit();
            return true;
        } catch (Throwable $e) {
            dbRollback();
            throw $e;
        }
    }
}
