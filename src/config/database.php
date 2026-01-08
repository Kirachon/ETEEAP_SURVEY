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
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'eteeap_survey');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
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
 * Execute a prepared statement and return all rows
 * 
 * @param string $sql
 * @param array $params
 * @return array
 */
function dbFetchAll(string $sql, array $params = []): array
{
    $stmt = getDbConnection()->prepare($sql);
    $stmt->execute($params);
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
    $stmt = getDbConnection()->prepare($sql);
    $stmt->execute($params);
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
    $stmt = getDbConnection()->prepare($sql);
    $stmt->execute($params);
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
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
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
