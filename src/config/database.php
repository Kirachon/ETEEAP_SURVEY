<?php
/**
 * ETEEAP Survey Application - Database Configuration
 * 
 * MySQL PDO Connection with secure defaults.
 * Configure your database credentials below.
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

// Database configuration
$cfgGet = function_exists('installConfigGet') ? 'installConfigGet' : null;

define('DB_HOST', getenv('DB_HOST') ?: ($cfgGet ? $cfgGet('DB_HOST', 'localhost') : 'localhost'));
define('DB_PORT', getenv('DB_PORT') ?: ($cfgGet ? $cfgGet('DB_PORT', '3306') : '3306'));
define('DB_NAME', getenv('DB_NAME') ?: ($cfgGet ? $cfgGet('DB_NAME', 'eteeap_survey') : 'eteeap_survey'));
define('DB_USER', getenv('DB_USER') ?: ($cfgGet ? $cfgGet('DB_USER', 'root') : 'root'));
define('DB_PASS', getenv('DB_PASS') ?: ($cfgGet ? $cfgGet('DB_PASS', '') : ''));
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection instance (singleton pattern)
 * 
 * @return PDO
 * @throws PDOException
 */
function getDbConnection(): PDO
{
    static $pdo = null;
    
    if ($pdo === null) {
        if (function_exists('isProduction') && isProduction()) {
            if (DB_USER === 'root' || DB_PASS === '') {
                throw new PDOException('Database connection failed. Please try again later.');
            }
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error in production, show generic message
            error_log('Database connection failed: ' . $e->getMessage());
            throw new PDOException('Database connection failed. Please try again later.');
        }
    }
    
    return $pdo;
}

/**
 * Prepare and execute a PDO statement with best-effort typed bindings.
 *
 * @throws InvalidArgumentException when a param value type is not supported
 */
function dbPrepareAndExecute(string $sql, array $params = []): PDOStatement
{
    $pdo = getDbConnection();
    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $param = null;
        if (is_int($key)) {
            // 0-based array becomes 1-based positional params
            $param = $key + 1;
        } elseif (is_string($key)) {
            $param = str_starts_with($key, ':') ? $key : (':' . $key);
        }

        if ($param === null) {
            throw new InvalidArgumentException('Invalid parameter key type.');
        }

        if (is_null($value)) {
            $stmt->bindValue($param, null, PDO::PARAM_NULL);
        } elseif (is_bool($value)) {
            $stmt->bindValue($param, $value ? 1 : 0, PDO::PARAM_INT);
        } elseif (is_int($value)) {
            $stmt->bindValue($param, $value, PDO::PARAM_INT);
        } elseif (is_float($value)) {
            $stmt->bindValue($param, (string) $value, PDO::PARAM_STR);
        } elseif (is_string($value)) {
            $stmt->bindValue($param, $value, PDO::PARAM_STR);
        } else {
            throw new InvalidArgumentException('Unsupported parameter value type.');
        }
    }

    $stmt->execute();
    return $stmt;
}

/**
 * Execute a prepared statement and return all rows
 * 
 * @param string $sql
 * @param array $params
 * @return array
 */
function dbFetchAll(string $sql, array $params = []): array
{
    $stmt = dbPrepareAndExecute($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Execute a prepared statement and return single row
 * 
 * @param string $sql
 * @param array $params
 * @return array|null
 */
function dbFetchOne(string $sql, array $params = []): ?array
{
    $stmt = dbPrepareAndExecute($sql, $params);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Execute an INSERT/UPDATE/DELETE and return affected rows
 * 
 * @param string $sql
 * @param array $params
 * @return int
 */
function dbExecute(string $sql, array $params = []): int
{
    $stmt = dbPrepareAndExecute($sql, $params);
    return $stmt->rowCount();
}

/**
 * Insert a row and return the last insert ID
 * 
 * @param string $sql
 * @param array $params
 * @return int
 */
function dbInsert(string $sql, array $params = []): int
{
    $pdo = getDbConnection();
    dbPrepareAndExecute($sql, $params);
    return (int) $pdo->lastInsertId();
}

/**
 * Begin a database transaction
 */
function dbBeginTransaction(): void
{
    getDbConnection()->beginTransaction();
}

/**
 * Commit a database transaction
 */
function dbCommit(): void
{
    getDbConnection()->commit();
}

/**
 * Rollback a database transaction
 */
function dbRollback(): void
{
    if (getDbConnection()->inTransaction()) {
        getDbConnection()->rollBack();
    }
}
