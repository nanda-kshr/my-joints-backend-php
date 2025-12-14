<?php
// Database configuration and connection

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        $env = $this->loadEnv();
        
        $host = $env['DB_HOST'] ?? 'localhost';
        $dbname = $env['DB_NAME'] ?? 'myjoints';
        $username = $env['DB_USER'] ?? 'root';
        $password = $env['DB_PASS'] ?? '';
        
        // Parse host and port if provided (format: host:port)
        $port = 3306;
        if (strpos($host, ':') !== false) {
            list($host, $port) = explode(':', $host, 2);
        }
        
        try {
            $this->conn = new PDO(
                "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    
    private function loadEnv() {
        $env = [];
        $envFile = __DIR__ . '/../.env';
        
        if (!file_exists($envFile)) {
            $envFile = __DIR__ . '/../.env.example';
        }
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($key, $value) = explode('=', $line, 2);
                $env[trim($key)] = trim($value);
            }
        }
        
        return $env;
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
}

function getDB() {
    return Database::getInstance()->getConnection();
}
