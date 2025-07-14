<?php
/**
 * Configuração de conexão com banco de dados
 * Suporta MySQL e PostgreSQL
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'escala_igreja';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $driver = $_ENV['DB_DRIVER'] ?? 'mysql';
        
        try {
            if ($driver === 'pgsql') {
                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            } else {
                $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            }
            
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro de conexão com banco de dados']);
            exit;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception('Erro na consulta: ' . $e->getMessage());
        }
    }
} 