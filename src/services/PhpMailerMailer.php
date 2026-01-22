<?php
/**
 * ETEEAP Survey Application - PHPMailer SMTP Mailer
 *
 * Uses the vendored PHPMailer library under /PHPMailer.
 *
 * Security notes:
 * - Do NOT log credentials.
 * - Keep SMTP settings in src/config/config.local.php (gitignored) via env vars.
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

class PhpMailerMailer
{
    public static function isAvailable(): bool
    {
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            return true;
        }
        return is_file(self::libPath('src/PHPMailer.php'))
            && is_file(self::libPath('src/SMTP.php'))
            && is_file(self::libPath('src/Exception.php'));
    }

    /**
     * @return array{host:string,connectHosts:string[],port:int,encryption:string,useAuth:bool,username:string,password:string,fromEmail:string,fromName:string,timeout:int,debug:bool}
     */
    private static function config(): array
    {
        $cfgGet = function_exists('installConfigGet') ? 'installConfigGet' : null;
        $get = static function (string $key, string $default = '') use ($cfgGet): string {
            $v = getenv($key);
            if (is_string($v) && $v !== '') {
                return $v;
            }
            if ($cfgGet) {
                $vv = $cfgGet($key, $default);
                if (is_string($vv) && $vv !== '') {
                    return $vv;
                }
            }
            return $default;
        };

        $host = trim($get('SMTP_HOST', ''));
        $port = (int) $get('SMTP_PORT', '587');

        $encryption = strtolower(trim($get('SMTP_ENCRYPTION', '')));
        if ($encryption === '') {
            // Backward compatibility with SMTP_TLS=1
            $tls = strtolower(trim($get('SMTP_TLS', '')));
            $encryption = in_array($tls, ['1', 'true', 'yes', 'on'], true) ? 'starttls' : 'none';
        }
        if (!in_array($encryption, ['none', 'starttls', 'ssl'], true)) {
            throw new RuntimeException('Invalid SMTP_ENCRYPTION. Use none|starttls|ssl.');
        }

        $useAuth = filter_var($get('SMTP_AUTH', '1'), FILTER_VALIDATE_BOOLEAN) === true;
        $username = $get('SMTP_USER', '');
        $password = $get('SMTP_PASS', '');
        $fromEmail = $get('SMTP_FROM_EMAIL', $username !== '' ? $username : 'no-reply@localhost');
        $fromName = $get('SMTP_FROM_NAME', '');
        if ($fromName === '') {
            // Backward-compat alias sometimes used in env files
            $fromName = $get('SMTP_NAME', '');
        }
        if ($fromName === '') {
            $fromName = APP_NAME . ' OTP';
        }

        $timeout = (int) $get('SMTP_TIMEOUT', '15');
        $timeout = $timeout > 0 ? $timeout : 15;

        $debug = filter_var($get('SMTP_DEBUG_LOG', '0'), FILTER_VALIDATE_BOOLEAN) === true;

        if ($host === '' || $port <= 0 || $fromEmail === '') {
            throw new RuntimeException('SMTP configuration is incomplete. Check SMTP settings in config.local.php.');
        }
        if ($useAuth && ($username === '' || $password === '')) {
            throw new RuntimeException('SMTP authentication is enabled but credentials are missing.');
        }

        $connectHosts = [];

        $connectList = trim($get('SMTP_CONNECT_HOSTS', ''));
        if ($connectList !== '') {
            $parts = preg_split('/[;,\\s]+/', $connectList, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach ($parts as $p) {
                $p = trim((string) $p);
                if ($p !== '') {
                    $connectHosts[] = $p;
                }
            }
        }

        $forceIpv4 = filter_var($get('SMTP_FORCE_IPV4', '0'), FILTER_VALIDATE_BOOLEAN) === true;
        if ($forceIpv4 && filter_var($host, FILTER_VALIDATE_IP) === false) {
            $ips = @gethostbynamel($host);
            if (is_array($ips)) {
                foreach ($ips as $ip) {
                    $ip = trim((string) $ip);
                    if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                        $connectHosts[] = $ip;
                    }
                }
            }
        }

        // Always try the hostname too (some SMTP servers require SNI/valid hostname).
        array_unshift($connectHosts, $host);

        // Unique + keep order
        $seen = [];
        $connectHosts = array_values(array_filter($connectHosts, static function (string $h) use (&$seen): bool {
            $h = trim($h);
            if ($h === '') {
                return false;
            }
            $k = strtolower($h);
            if (isset($seen[$k])) {
                return false;
            }
            $seen[$k] = true;
            return true;
        }));

        return [
            'host' => $host,
            'connectHosts' => $connectHosts,
            'port' => $port,
            'encryption' => $encryption,
            'useAuth' => $useAuth,
            'username' => $username,
            'password' => $password,
            'fromEmail' => $fromEmail,
            'fromName' => $fromName,
            'timeout' => $timeout,
            'debug' => $debug,
        ];
    }

    private static function libPath(string $rel): string
    {
        return rtrim(APP_ROOT, '/\\') . DIRECTORY_SEPARATOR . 'PHPMailer' . DIRECTORY_SEPARATOR . ltrim($rel, '/\\');
    }

    private static function ensureLoaded(): void
    {
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            return;
        }

        $phpMailer = self::libPath('src/PHPMailer.php');
        $smtp = self::libPath('src/SMTP.php');
        $exc = self::libPath('src/Exception.php');

        if (!is_file($phpMailer) || !is_file($smtp) || !is_file($exc)) {
            throw new RuntimeException('PHPMailer library is missing. Ensure /PHPMailer is deployed.');
        }

        require_once $exc;
        require_once $phpMailer;
        require_once $smtp;
    }

    private static function safeLog(string $message): void
    {
        $logPath = (defined('STORAGE_PATH') ? STORAGE_PATH : (APP_ROOT . '/storage')) . '/logs/otp.log';
        $msg = trim(preg_replace('/\\s+/', ' ', $message) ?? '');
        $msg = mb_substr($msg, 0, 260, 'UTF-8');
        @error_log('SMTP[PHPMailer] ' . $msg . PHP_EOL, 3, $logPath);
    }

    /**
     * Send a plain-text email.
     */
    public static function sendText(string $toEmail, string $subject, string $bodyText): void
    {
        $toEmail = sanitizeEmail($toEmail);
        if ($toEmail === '' || filter_var($toEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Recipient email is invalid.');
        }

        $cfg = self::config();
        self::ensureLoaded();

        $errors = [];
        foreach ($cfg['connectHosts'] as $connectHost) {
            try {
                if ($cfg['debug']) {
                    self::safeLog("connect {$connectHost}:{$cfg['port']} enc={$cfg['encryption']}");
                }
                self::sendOnce(
                    $connectHost,
                    (int) $cfg['port'],
                    (string) $cfg['encryption'],
                    (bool) $cfg['useAuth'],
                    (string) $cfg['username'],
                    (string) $cfg['password'],
                    (string) $cfg['fromEmail'],
                    (string) $cfg['fromName'],
                    $toEmail,
                    $subject,
                    $bodyText,
                    (int) $cfg['timeout']
                );
                if ($cfg['debug']) {
                    self::safeLog('sent ok');
                }
                return;
            } catch (Throwable $e) {
                $msg = trim((string) $e->getMessage());
                $msg = $msg !== '' ? $msg : 'unknown error';
                if ($cfg['debug']) {
                    self::safeLog($connectHost . ': ' . $msg);
                }
                $errors[] = $connectHost;
            }
        }

        // Keep thrown errors generic to avoid leaking internal hostnames/IPs.
        // Enable SMTP_DEBUG_LOG=1 to get safe troubleshooting hints in storage/logs/otp.log.
        throw new RuntimeException('SMTP connect failed.');
    }

    private static function sendOnce(
        string $host,
        int $port,
        string $encryption,
        bool $useAuth,
        string $username,
        string $password,
        string $fromEmail,
        string $fromName,
        string $toEmail,
        string $subject,
        string $bodyText,
        int $timeout
    ): void {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->Timeout = $timeout;

        $mail->SMTPAuth = $useAuth;
        if ($useAuth) {
            $mail->Username = $username;
            $mail->Password = $password;
        }

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = '8bit';

        if ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->SMTPAutoTLS = false;
        } elseif ($encryption === 'starttls') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPAutoTLS = true;
        } else {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        }

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail);
        $mail->Subject = $subject;
        $mail->Body = $bodyText;
        $mail->isHTML(false);

        $mail->send();
    }
}
