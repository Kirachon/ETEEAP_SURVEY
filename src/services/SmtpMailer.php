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
     * @return array{host:string,port:int,useTls:bool,username:string,password:string,fromEmail:string,fromName:string}
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
        $useTls = filter_var($get('SMTP_TLS', '1'), FILTER_VALIDATE_BOOLEAN) === true;
        $username = $get('SMTP_USER', '');
        $password = $get('SMTP_PASS', '');
        $fromEmail = $get('SMTP_FROM_EMAIL', $username);
        $fromName = $get('SMTP_FROM_NAME', APP_NAME . ' OTP');

        if ($host === '' || $port <= 0 || $username === '' || $password === '' || $fromEmail === '') {
            throw new RuntimeException('SMTP configuration is incomplete. Check SMTP settings in config.local.php.');
        }

        return [
            'host' => $host,
            'port' => $port,
            'useTls' => $useTls,
            'username' => $username,
            'password' => $password,
            'fromEmail' => $fromEmail,
            'fromName' => $fromName,
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
            $cfg['port'],
            $cfg['useTls'],
            $cfg['username'],
            $cfg['password'],
            $fromEmail,
            $toEmail,
            $data
        );
    }

    private static function smtpSend(
        string $host,
        int $port,
        bool $useTls,
        string $username,
        string $password,
        string $fromEmail,
        string $toEmail,
        string $data
    ): void {
        $remote = $host . ':' . $port;
        $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
        if ($fp === false) {
            throw new RuntimeException('SMTP connect failed.');
        }

        stream_set_timeout($fp, 15);

        try {
            self::expect($fp, [220]);
            self::cmd($fp, 'EHLO ' . self::localHostname(), [250]);

            if ($useTls) {
                self::cmd($fp, 'STARTTLS', [220]);
                $cryptoOk = @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if ($cryptoOk !== true) {
                    throw new RuntimeException('SMTP STARTTLS failed.');
                }
                // Re-EHLO after STARTTLS
                self::cmd($fp, 'EHLO ' . self::localHostname(), [250]);
            }

            // AUTH LOGIN (Gmail supports this)
            self::cmd($fp, 'AUTH LOGIN', [334]);
            self::cmd($fp, base64_encode($username), [334]);
            self::cmd($fp, base64_encode($password), [235]);

            self::cmd($fp, 'MAIL FROM:<' . $fromEmail . '>', [250]);
            self::cmd($fp, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
            self::cmd($fp, 'DATA', [354]);

            // DATA must end with <CRLF>.<CRLF> and dot-stuff lines beginning with '.'
            $safeData = preg_replace('/\\r?\\n\\./', "\r\n..", $data) ?? $data;
            fwrite($fp, $safeData . "\r\n.\r\n");
            self::expect($fp, [250]);

            self::cmd($fp, 'QUIT', [221]);
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
    private static function cmd($fp, string $command, array $expected): void
    {
        fwrite($fp, $command . "\r\n");
        self::expect($fp, $expected);
    }

    /**
     * @param int[] $expectedCodes
     */
    private static function expect($fp, array $expectedCodes): void
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
            throw new RuntimeException('SMTP server did not respond.');
        }

        if (!in_array($code, $expectedCodes, true)) {
            $trim = trim($line);
            throw new RuntimeException("SMTP error {$code}: {$trim}");
        }
    }
}
