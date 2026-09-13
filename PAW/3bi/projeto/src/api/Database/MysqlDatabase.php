<?php

namespace Api\Database;

use PDO;
use PDOException;
use Exception;

// Abre e reaproveita uma unica conexao PDO com o MySQL
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
        $this->host = $config['host'] ?? '127.0.0.1';
        $this->user = $config['user'] ?? 'root';
        $this->password = $config['password'] ?? '';
        $this->database = $config['database'] ?? 'eventos_db';
        $this->port = $config['port'] ?? 3306;
    }

    public function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";
                self::$connection = new PDO($dsn, $this->user, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => true
                ]);
            } catch (PDOException $e) {
                throw new Exception("Falha ao conectar ao MySQL: " . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
