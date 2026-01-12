<?php
/**
 * ETEEAP Survey Application - Web Installer
 *
 * Beginner-friendly setup wizard for non-Docker installs (e.g., Windows + XAMPP).
 * Creates storage/config.php, initializes database schema+migrations, and locks itself.
 */

class InstallController
{
    private function storagePath(string $suffix = ''): string
    {
        $base = defined('STORAGE_PATH') ? STORAGE_PATH : (APP_ROOT . '/storage');
        return rtrim($base, DIRECTORY_SEPARATOR) . ($suffix !== '' ? (DIRECTORY_SEPARATOR . ltrim($suffix, DIRECTORY_SEPARATOR)) : '');
    }

    private function isLocked(): bool
    {
        return is_file($this->storagePath('install.lock'));
    }

    private function isInstallerAllowed(): bool
    {
        // Allow in development by default. In production, require explicit env flag.
        if (defined('APP_ENV') && APP_ENV === 'production') {
            return filter_var(getenv('INSTALLER_ALLOW'), FILTER_VALIDATE_BOOLEAN) === true;
        }
        return true;
    }

    private function ensureStorageDir(): void
    {
        $dir = $this->storagePath();
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);

        $pageTitle = $pageTitle ?? 'Installer';

        ob_start();
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo '<div class="p-8 text-red-600">View not found: ' . htmlspecialchars($view) . '</div>';
        }
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/app.php';
    }

    public function index(): void
    {
        if (!$this->isInstallerAllowed()) {
            http_response_code(404);
            include VIEWS_PATH . '/errors/404.php';
            return;
        }

        $this->ensureStorageDir();

        $data = [
            'pageTitle' => 'Installer',
            'locked' => $this->isLocked(),
            'storageWritable' => is_writable($this->storagePath()),
            'phpVersion' => PHP_VERSION,
            'errors' => flashGet('install_errors', []),
            'messages' => flashGet('install_messages', []),
            'old' => flashGet('install_old', []),
            'csrfToken' => csrfGetToken(),
        ];

        $this->render('install/index', $data);
    }

    public function install(): void
    {
        if (!$this->isInstallerAllowed()) {
            http_response_code(404);
            include VIEWS_PATH . '/errors/404.php';
            return;
        }

        if ($this->isLocked()) {
            flashSet('error', 'Installer is locked. Delete `storage/install.lock` to re-run.');
            redirect(appUrl('/install'));
            return;
        }

        csrfProtect();
        $this->ensureStorageDir();

        $errors = [];

        $get = static function (string $key, string $default = ''): string {
            $v = $_POST[$key] ?? $default;
            $v = is_string($v) ? trim($v) : $default;
            return $v;
        };

        $dbHost = $get('db_host', 'localhost');
        $dbPort = $get('db_port', '3306');
        $dbName = $get('db_name', 'eteeap_survey');
        $dbUser = $get('db_user', 'eteeap_user');
        $dbPass = (string) ($_POST['db_pass'] ?? '');

        $adminDbUser = $get('admin_db_user', '');
        $adminDbPass = (string) ($_POST['admin_db_pass'] ?? '');

        $appUrl = $get('app_url', '');
        $appEnv = $get('app_env', 'development');
        $appDebug = isset($_POST['app_debug']) ? true : false;

        $createDb = isset($_POST['create_db']);
        $installSampleData = isset($_POST['install_sample_data']);

        $adminUsername = $get('admin_username', 'admin');
        $adminEmail = $get('admin_email', 'admin@dswd.gov.ph');
        $adminPassword = (string) ($_POST['admin_password'] ?? '');
        $adminPasswordConfirm = (string) ($_POST['admin_password_confirm'] ?? '');

        // Basic validation
        if (!preg_match('/^[A-Za-z0-9_]+$/', $dbName)) {
            $errors['db_name'][] = 'DB name may only contain letters, numbers, and underscores.';
        }
        // SECURITY: Strict validation for DB username to prevent SQL injection
        if (!preg_match('/^[A-Za-z0-9_]+$/', $dbUser)) {
            $errors['db_user'][] = 'DB user may only contain letters, numbers, and underscores.';
        }
        if ($dbHost === '') $errors['db_host'][] = 'DB host is required.';
        if ($dbPort === '') $errors['db_port'][] = 'DB port is required.';
        if ($dbName === '') $errors['db_name'][] = 'DB name is required.';
        if ($dbUser === '') $errors['db_user'][] = 'DB user is required.';

        if (!in_array($appEnv, ['development', 'production'], true)) {
            $errors['app_env'][] = 'Invalid environment.';
        }

        if ($adminPassword === '' || strlen($adminPassword) < (defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8)) {
            $errors['admin_password'][] = 'Admin password must be at least 8 characters.';
        } elseif ($adminPassword !== $adminPasswordConfirm) {
            $errors['admin_password_confirm'][] = 'Passwords do not match.';
        }

        if (!is_writable($this->storagePath())) {
            $errors['storage'][] = 'Storage folder is not writable: ' . $this->storagePath();
        }

        if ($createDb && $adminDbUser === '') {
            $errors['admin_db_user'][] = 'Admin DB user is required when “Create database/user” is enabled.';
        }

        if (!empty($errors)) {
            flashSet('install_errors', $errors);
            flashSet('install_old', $_POST);
            redirect(appUrl('/install'));
            return;
        }

        try {
            $messages = [];

            if (!extension_loaded('mysqli')) {
                throw new RuntimeException('PHP extension "mysqli" is required for the installer.');
            }

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $connect = static function (string $host, string $port, string $user, string $pass, ?string $db = null): mysqli {
                $mysqli = new mysqli($host, $user, $pass, $db ?? '', (int) $port);
                $mysqli->set_charset('utf8mb4');
                return $mysqli;
            };

            $assertSafeIdentifier = static function (string $value, string $label): void {
                // Allow only simple MySQL identifiers (no spaces, quotes, or punctuation).
                // This prevents SQL injection in installer-generated statements.
                if ($value === '' || preg_match('/^[A-Za-z0-9_]+$/', $value) !== 1) {
                    throw new InvalidArgumentException("Invalid {$label}. Use only letters, numbers, and underscore.");
                }
            };

            $assertSafeIdentifier($dbName, 'database name');
            $assertSafeIdentifier($dbUser, 'database username');

            $rewriteDbName = static function (string $sql, string $dbName): string {
                $dbEsc = '`' . str_replace('`', '``', $dbName) . '`';
                $sql = preg_replace('/\\bUSE\\s+`?eteeap_survey`?\\s*;/i', 'USE ' . $dbEsc . ';', $sql);
                $sql = preg_replace('/CREATE\\s+DATABASE\\s+IF\\s+NOT\\s+EXISTS\\s+`?eteeap_survey`?/i', 'CREATE DATABASE IF NOT EXISTS ' . $dbEsc, $sql);
                return $sql;
            };

            $runMultiQuery = static function (mysqli $mysqli, string $sql): void {
                $mysqli->multi_query($sql);
                do {
                    // Consume results (required even for statements without result sets)
                    if ($result = $mysqli->store_result()) {
                        $result->free();
                    }
                } while ($mysqli->more_results() && $mysqli->next_result());
            };

            // 1) Create DB/user (optional) using admin credentials
            if ($createDb) {
                $adminConn = $connect($dbHost, $dbPort, $adminDbUser, $adminDbPass, null);
                $dbEsc = '`' . str_replace('`', '``', $dbName) . '`';
                $userEsc = str_replace("'", "''", $dbUser);

                $adminConn->query("CREATE DATABASE IF NOT EXISTS {$dbEsc} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                // Create or update app user
                // For typical Windows/XAMPP installs, local-only access is safest.
                $adminConn->query("CREATE USER IF NOT EXISTS '{$userEsc}'@'localhost' IDENTIFIED BY '" . $adminConn->real_escape_string($dbPass) . "'");
                $adminConn->query("ALTER USER '{$userEsc}'@'localhost' IDENTIFIED BY '" . $adminConn->real_escape_string($dbPass) . "'");
                $adminConn->query("GRANT ALL PRIVILEGES ON {$dbEsc}.* TO '{$userEsc}'@'localhost'");
                $adminConn->query("FLUSH PRIVILEGES");

                $adminConn->close();
                $messages[] = "Database/user ready: {$dbName} / {$dbUser}";
            }

            // 2) Initialize schema + migrations using app DB credentials
            $appConn = $connect($dbHost, $dbPort, $dbUser, $dbPass, $dbName);

            $schemaPath = APP_ROOT . '/database/schema.sql';
            if (!is_file($schemaPath)) {
                throw new RuntimeException('Missing schema file: database/schema.sql');
            }
            $schemaSql = file_get_contents($schemaPath);
            $schemaSql = $rewriteDbName($schemaSql, $dbName);
            $runMultiQuery($appConn, $schemaSql);
            $messages[] = 'Database schema applied.';

            // Apply migrations in sorted order
            $migrationFiles = glob(APP_ROOT . '/database/migrations/*.sql') ?: [];
            sort($migrationFiles, SORT_STRING);
            foreach ($migrationFiles as $file) {
                $sql = file_get_contents($file);
                $sql = $rewriteDbName($sql, $dbName);
                $runMultiQuery($appConn, $sql);
                $messages[] = 'Applied migration: ' . basename($file);
            }

            // Optional sample data
            if ($installSampleData) {
                $seedPath = APP_ROOT . '/database/seed.sql';
                if (is_file($seedPath)) {
                    $seedSql = file_get_contents($seedPath);
                    $seedSql = $rewriteDbName($seedSql, $dbName);
                    $runMultiQuery($appConn, $seedSql);
                    $messages[] = 'Sample data inserted (seed.sql).';
                }
            }

            // 3) Create/update admin user (using chosen password)
            $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
            $stmt = $appConn->prepare(
                "INSERT INTO admin_users (username, email, password_hash, full_name, is_active)
                 VALUES (?, ?, ?, 'System Administrator', 1)
                 ON DUPLICATE KEY UPDATE email=VALUES(email), password_hash=VALUES(password_hash), is_active=1"
            );
            $stmt->bind_param('sss', $adminUsername, $adminEmail, $passwordHash);
            $stmt->execute();
            $stmt->close();
            $messages[] = 'Admin user created/updated.';

            $appConn->close();

            // 4) Write storage/config.php
            $config = [
                'APP_ENV' => $appEnv,
                'APP_DEBUG' => $appDebug,
                // If blank, the app will derive the base URL from the current request host.
                'APP_URL' => $appUrl !== '' ? $appUrl : '',
                'DB_HOST' => $dbHost,
                'DB_PORT' => $dbPort,
                'DB_NAME' => $dbName,
                'DB_USER' => $dbUser,
                'DB_PASS' => $dbPass,
            ];

            $configPath = $this->storagePath('config.php');
            $php = "<?php\nreturn " . var_export($config, true) . ";\n";
            file_put_contents($configPath, $php, LOCK_EX);

            // Best-effort file permission hardening
            @chmod($configPath, 0640);

            // 5) Lock installer
            file_put_contents($this->storagePath('install.lock'), 'installed ' . date('c'), LOCK_EX);

            flashSet('install_messages', $messages);
            flashSet('success', 'Installation completed. You can now log in as admin.');
            redirect(appUrl('/admin/login'));
        } catch (Throwable $e) {
            error_log('Installer failed: ' . $e->getMessage());
            $errors['installer'][] = $e->getMessage();
            flashSet('install_errors', $errors);
            flashSet('install_old', $_POST);
            redirect(appUrl('/install'));
        }
    }
}
