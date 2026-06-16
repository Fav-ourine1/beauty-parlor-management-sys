<?php
/**
 * Database
 * PDO singleton. All queries go through prepared statements.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            Logger::error('Database connection failed: ' . $e->getMessage());
            http_response_code(503);
            die(json_encode(['error' => 'Database unavailable']));
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Execute a query and return the PDOStatement */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Return all rows */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /** Return a single row */
    public function fetchOne(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    /** Return a single scalar value */
    public function fetchScalar(string $sql, array $params = []): mixed
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    /** INSERT / UPDATE / DELETE — returns affected row count */
    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    /** INSERT — returns last insert ID */
    public function insert(string $sql, array $params = []): string
    {
        $this->query($sql, $params);
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): void  { $this->pdo->beginTransaction(); }
    public function commit(): void            { $this->pdo->commit(); }
    public function rollBack(): void          { $this->pdo->rollBack(); }

    /** Prevent cloning of the singleton */
    private function __clone() {}
}