<?php

namespace App\Database;

use PDO;
use PDOStatement;
use Exception;

/**
 * Secure Database Connection Handler
 * Implements prepared statements and connection security
 */
class DatabaseConnection
{
    private PDO $pdo;
    private string $lastQuery = '';
    private array $lastParams = [];
    private array $config = [];

    /**
     * Initialize database connection
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * Establish secure database connection
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $this->config['host'],
                $this->config['port'] ?? 3306,
                $this->config['dbname'],
                $this->config['charset'] ?? 'utf8mb4'
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4, sql_mode='STRICT_TRANS_TABLES'",
            ];

            // Add SSL options if configured
            if (!empty($this->config['ssl_ca'])) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $this->config['ssl_ca'];
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
            }

            $this->pdo = new PDO(
                $dsn,
                $this->config['user'],
                $this->config['password'],
                $options
            );

        } catch (Exception $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Prepare and execute query safely
     */
    public function execute(string $query, array $params = []): PDOStatement
    {
        try {
            $this->lastQuery = $query;
            $this->lastParams = $params;

            // Validate query
            $this->validateQuery($query);

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            return $stmt;

        } catch (Exception $e) {
            $this->logSecurityEvent('SQL_ERROR', $query, $params);
            throw $e;
        }
    }

    /**
     * Fetch all results
     */
    public function fetchAll(string $query, array $params = []): array
    {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Fetch single result
     */
    public function fetch(string $query, array $params = []): ?array
    {
        $stmt = $this->execute($query, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get row count
     */
    public function rowCount(string $query, array $params = []): int
    {
        $stmt = $this->execute($query, $params);
        return $stmt->rowCount();
    }

    /**
     * Get last inserted ID
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Validate query for dangerous patterns
     */
    private function validateQuery(string $query): void
    {
        // Check for dangerous SQL patterns (case-insensitive)
        $dangerous = [
            'DROP DATABASE',
            'DROP TABLE',
            'TRUNCATE TABLE',
            'DELETE FROM',
            'UPDATE ',
            'ALTER TABLE',
        ];

        $query_upper = strtoupper($query);

        foreach ($dangerous as $pattern) {
            if (strpos($query_upper, $pattern) !== false && stripos($query, '?') === false) {
                throw new Exception('Potentially dangerous query detected');
            }
        }
    }

    /**
     * Log security events
     */
    private function logSecurityEvent(string $event, string $query, array $params): void
    {
        $logFile = '/var/log/cfh-crm/security.log';
        $message = sprintf(
            "[%s] %s - Query: %s - Params: %s\n",
            date('Y-m-d H:i:s'),
            $event,
            substr($query, 0, 100),
            json_encode($params)
        );

        error_log($message, 3, $logFile);
    }

    /**
     * Close connection
     */
    public function close(): void
    {
        $this->pdo = null;
    }
}
