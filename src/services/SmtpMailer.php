<?php
/**
 * ETEEAP Survey Application - SMTP Mailer (no external deps)
 *
 * Minimal SMTP client for sending OTP emails via SMTP+STARTTLS (e.g., smtp.gmail.com:587).
 *
 * Security notes:
 * - Do NOT log credentials.
 * - Keep SMTP settings in src/config/config.local.php (gitignored) via env vars.
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

class SmtpMailer
{
    /**
     * @return array{host:string,connectHosts:string[],port:int,encryption:string,useAuth:bool,username:string,password:string,fromEmail:string,fromName:string,debug:bool}
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

        $host = $get('SMTP_HOST', '');
        $port = (int) $get('SMTP_PORT', '587');
        $forceIpv4 = filter_var($get('SMTP_FORCE_IPV4', '0'), FILTER_VALIDATE_BOOLEAN) === true;
        $debug = filter_var($get('SMTP_DEBUG_LOG', '0'), FILTER_VALIDATE_BOOLEAN) === true;

        $encryption = strtolower(trim($get('SMTP_ENCRYPTION', '')));
        if ($encryption === '') {
            // Backward-compat: SMTP_TLS=true means STARTTLS; false means plain.
            $useTls = filter_var($get('SMTP_TLS', '1'), FILTER_VALIDATE_BOOLEAN) === true;
            $encryption = $useTls ? 'starttls' : 'none';
        }
        // Accept common synonyms
        if (in_array($encryption, ['tls', 'start_tls'], true)) {
            $encryption = 'starttls';
        }
        if (in_array($encryption, ['ssl', 'smtps', 'implicit_tls', 'implicit'], true)) {
            $encryption = 'ssl';
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

        if ($host === '' || $port <= 0 || $fromEmail === '') {
            throw new RuntimeException('SMTP configuration is incomplete. Check SMTP settings in config.local.php.');
        }
        if ($useAuth && ($username === '' || $password === '')) {
            throw new RuntimeException('SMTP authentication is enabled but credentials are missing.');
        }

        $connectHosts = [$host];
        if ($forceIpv4) {
            // Some networks have broken IPv6 and prefer AAAA records, causing timeouts/hangs.
            // Allow forcing IPv4 by resolving A-records and trying multiple IPv4 addresses.
            $ips = @gethostbynamel($host);
            if (is_array($ips)) {
                $ips = array_values(array_filter($ips, static fn($ip) => is_string($ip) && $ip !== ''));
                if (!empty($ips)) {
                    $connectHosts = $ips;
                }
            }
        }

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
            'debug' => $debug,
        ];
    }

    private static function encodeHeader(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        // RFC 2047 encoded-word, Base64, UTF-8
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
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

        $fromEmail = $cfg['fromEmail'];
        $fromName = $cfg['fromName'];

        $headers = [];
        $headers[] = 'From: ' . self::encodeHeader($fromName) . " <{$fromEmail}>";
        $headers[] = "To: <{$toEmail}>";
        $headers[] = 'Subject: ' . self::encodeHeader($subject);
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'Content-Transfer-Encoding: 8bit';

        $messageId = sprintf('<%s.%s@%s>', bin2hex(random_bytes(8)), time(), preg_replace('/^www\\./i', '', parse_url(APP_URL, PHP_URL_HOST) ?: 'localhost'));
        $headers[] = 'Message-ID: ' . $messageId;

        $data = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n", "\r\n", $bodyText) . "\r\n";

        self::smtpSend(
            $cfg['host'],
            $cfg['connectHosts'],
            $cfg['port'],
            $cfg['encryption'],
            $cfg['useAuth'],
            $cfg['username'],
            $cfg['password'],
            $fromEmail,
            $toEmail,
            $data,
            (bool) $cfg['debug']
        );
    }

    private static function smtpSend(
        string $peerHost,
        array $connectHosts,
        int $port,
        string $encryption,
        bool $useAuth,
        string $username,
        string $password,
        string $fromEmail,
        string $toEmail,
        string $data,
        bool $debug
    ): void {
        $connectHosts = array_values(array_filter($connectHosts, static fn($h) => is_string($h) && $h !== ''));
        if (empty($connectHosts)) {
            $connectHosts = [$peerHost];
        }

        // Try multiple endpoints (helpful when some IPs are blocked/blackholed on certain networks).
        // Keep error messages safe (no credentials).
        $errors = [];
        foreach ($connectHosts as $connectHost) {
            try {
                self::smtpSendOnce($peerHost, $connectHost, $port, $encryption, $useAuth, $username, $password, $fromEmail, $toEmail, $data, $debug);
                return;
            } catch (Throwable $e) {
                $msg = trim((string) $e->getMessage());
                $msg = $msg !== '' ? $msg : 'unknown error';
                $errors[] = $connectHost . ': ' . $msg;
                // Try next address
            }
        }

        if (empty($errors)) {
            throw new RuntimeException('SMTP connect failed.');
        }
        $errors = array_slice($errors, 0, 5);
        throw new RuntimeException('SMTP connect failed. Attempts: ' . implode(' | ', $errors));
    }

    private static function smtpSendOnce(
        string $peerHost,
        string $connectHost,
        int $port,
        string $encryption,
        bool $useAuth,
        string $username,
        string $password,
        string $fromEmail,
        string $toEmail,
        string $data,
        bool $debug
    ): void {
        $timeout = 20;
        $remote = ($encryption === 'ssl' ? ('ssl://' . $connectHost) : $connectHost) . ':' . $port;

        $context = null;
        if ($encryption === 'ssl') {
            // When connecting via resolved IPs, ensure SNI uses the original hostname.
            $context = stream_context_create([
                'ssl' => [
                    'SNI_enabled' => true,
                    'peer_name' => $peerHost,
                    // Avoid strict verification surprises on minimal containers.
                    // (If you want strict verification, configure a CA bundle in the container and flip these.)
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);
        }

        $fp = @stream_socket_client(
            $remote,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context ?: null
        );
        if ($fp === false) {
            $safeErr = trim((string) $errstr);
            $safeErr = $safeErr !== '' ? $safeErr : 'unknown error';
            throw new RuntimeException("SMTP connect failed ({$connectHost}:{$port}, errno={$errno}): {$safeErr}");
        }

        stream_set_timeout($fp, $timeout);

        if ($debug) {
            self::debugLog("connect ok ({$connectHost}:{$port}, enc={$encryption})");
        }

        try {
            self::expect($fp, [220], 'greeting', $connectHost, $port);
            self::cmd($fp, 'EHLO ' . self::localHostname(), [250], 'EHLO', $connectHost, $port, $debug);

            if ($encryption === 'starttls') {
                self::cmd($fp, 'STARTTLS', [220], 'STARTTLS', $connectHost, $port, $debug);
                $cryptoOk = @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if ($cryptoOk !== true) {
                    throw new RuntimeException("SMTP STARTTLS failed ({$connectHost}:{$port}).");
                }
                if ($debug) {
                    self::debugLog("STARTTLS ok ({$connectHost}:{$port})");
                }
                // Re-EHLO after STARTTLS
                self::cmd($fp, 'EHLO ' . self::localHostname(), [250], 'EHLO2', $connectHost, $port, $debug);
            }

            if ($useAuth) {
                // AUTH LOGIN (Gmail supports this)
                self::cmd($fp, 'AUTH LOGIN', [334], 'AUTH', $connectHost, $port, $debug);
                self::cmd($fp, base64_encode($username), [334], 'AUTH-USER', $connectHost, $port, false);
                self::cmd($fp, base64_encode($password), [235], 'AUTH-PASS', $connectHost, $port, false);
                if ($debug) {
                    self::debugLog("AUTH ok ({$connectHost}:{$port})");
                }
            }

            self::cmd($fp, 'MAIL FROM:<' . $fromEmail . '>', [250], 'MAIL FROM', $connectHost, $port, $debug);
            self::cmd($fp, 'RCPT TO:<' . $toEmail . '>', [250, 251], 'RCPT TO', $connectHost, $port, $debug);
            self::cmd($fp, 'DATA', [354], 'DATA', $connectHost, $port, $debug);

            // DATA must end with <CRLF>.<CRLF> and dot-stuff lines beginning with '.'
            $safeData = preg_replace('/\\r?\\n\\./', "\r\n..", $data) ?? $data;
            fwrite($fp, $safeData . "\r\n.\r\n");
            self::expect($fp, [250], 'DATA_END', $connectHost, $port);

            self::cmd($fp, 'QUIT', [221], 'QUIT', $connectHost, $port, $debug);
        } finally {
            fclose($fp);
        }
    }

    private static function localHostname(): string
    {
        $host = gethostname();
        if (is_string($host) && $host !== '') {
            return $host;
        }
        return 'localhost';
    }

    /**
     * @param int[] $expected
     */
    private static function cmd($fp, string $command, array $expected, string $stage, string $connectHost, int $port, bool $debug): void
    {
        fwrite($fp, $command . "\r\n");
        self::expect($fp, $expected, $stage, $connectHost, $port);
        if ($debug) {
            self::debugLog("ok {$stage} ({$connectHost}:{$port})");
        }
    }

    /**
     * @param int[] $expectedCodes
     */
    private static function expect($fp, array $expectedCodes, string $stage, string $connectHost, int $port): void
    {
        $line = '';
        $code = null;

        while (($l = fgets($fp, 515)) !== false) {
            $line .= $l;
            if (preg_match('/^(\\d{3})([ -])/', $l, $m) === 1) {
                $code = (int) $m[1];
                if ($m[2] === ' ') {
                    break; // last line of multi-line response
                }
            }
        }

        if ($code === null) {
            $meta = stream_get_meta_data($fp);
            $timedOut = (is_array($meta) && !empty($meta['timed_out']));
            $suffix = $timedOut ? ' (timed out)' : '';
            throw new RuntimeException("SMTP server did not respond{$suffix} ({$stage}, {$connectHost}:{$port}).");
        }

        if (!in_array($code, $expectedCodes, true)) {
            $trim = trim($line);
            throw new RuntimeException("SMTP error {$code} ({$stage}, {$connectHost}:{$port}): {$trim}");
        }
    }

    private static function debugLog(string $message): void
    {
        $logPath = (defined('STORAGE_PATH') ? STORAGE_PATH : (APP_ROOT . '/storage')) . '/logs/otp.log';
        @error_log('[SMTP] ' . $message . "\n", 3, $logPath);
    }
}
