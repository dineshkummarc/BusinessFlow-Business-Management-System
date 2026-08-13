<?php
/**
 * Database
 * Singleton PDO wrapper — the single source of DB connections for the app.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Bug-fix note: this used to swallow the real PDO exception
            // entirely, which made a misconfigured host/port (the actual
            // cause of a connection failure) very hard to diagnose. We
            // still never want to leak raw DB errors to end users in
            // production, so the detailed message only shows when PHP's
            // own display_errors is on (i.e. during local development) —
            // exactly the setting config.php enables by default. In
            // production, set ini_set('display_errors', 0) in config.php
            // and this will fall back to the safe generic message.
            if (ini_get('display_errors')) {
                die(
                    '<div style="font-family:sans-serif;max-width:640px;margin:60px auto;'
                    . 'padding:24px;border:1px solid #f1b0b7;background:#fff5f5;border-radius:8px;">'
                    . '<h2 style="color:#c0392b;margin-top:0;">Database connection failed</h2>'
                    . '<p><strong>PDO error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>'
                    . '<p>Check <code>config/config.php</code> — most commonly this means '
                    . '<code>DB_HOST</code>, <code>DB_PORT</code>, <code>DB_NAME</code>, <code>DB_USER</code> '
                    . 'or <code>DB_PASS</code> don\'t match your local MySQL/MariaDB setup, or the '
                    . 'database service isn\'t running.</p>'
                    . '</div>'
                );
            }
            die('Database connection failed. Please verify your database is running '
                . 'and the credentials in config/config.php are correct.');
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /** Run a prepared SELECT and return all rows. */
    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Run a prepared SELECT and return a single row (or null). */
    public function selectOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Run INSERT/UPDATE/DELETE. Returns affected row count. */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    // Prevent cloning / unserialization of the singleton.
    private function __clone() {}
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize a Database singleton.');
    }
}
