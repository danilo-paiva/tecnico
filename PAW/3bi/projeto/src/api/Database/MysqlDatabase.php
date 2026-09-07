<?php

namespace Api\Database;

use PDO;
use PDOException;
use Exception;

/**
 * MysqlDatabase
 * Singleton de conexão PDO. Recebe config por construtor para facilitar testes
 * e reuso entre ambientes (XAMPP, Docker, etc).
 */
class MysqlDatabase
{
    private static ?PDO $connection = null;

    private string $host;
    private string $user;
    private string $password;
    private string $database;
    private int $port;

    public function __construct(array $config = [])
    {
        $this->host     = $config['host'] ?? '127.0.0.1';
        $this->user     = $config['user'] ?? 'root';
        $this->password = $config['password'] ?? '';
        $this->database = $config['database'] ?? 'eventos_db';
        $this->port     = $config['port'] ?? 3306;
    }

    public function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4",
                    $this->host,
                    $this->port,
                    $this->database
                );
                self::$connection = new PDO($dsn, $this->user, $this->password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT         => true,
                ]);
            } catch (PDOException $e) {
                error_log("[MysqlDatabase] Falha de conexão: " . $e->getMessage());
                throw new Exception("Não foi possível conectar ao MySQL: " . $e->getMessage());
            }
        }
        return self::$connection;
    }

    /** Para testes: permite resetar a conexão singleton */
    public static function resetConnection(): void
    {
        self::$connection = null;
    }
}
