<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working!\n";
echo "PHP Version: " . phpversion() . "\n";

// Test database connection
try {
    $env = [];
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            list($key, $value) = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
    }
    
    $host = $env['DB_HOST'] ?? 'localhost';
    $dbname = $env['DB_NAME'] ?? 'myjoints';
    $username = $env['DB_USER'] ?? 'root';
    $password = $env['DB_PASS'] ?? '';
    
    echo "DB Config: host=$host, db=$dbname, user=$username\n";
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "Database connection successful!\n";
} catch(PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
