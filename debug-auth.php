<?php
require_once __DIR__ . '/lib/jwt.php';
require_once __DIR__ . '/lib/utils.php';

corsHeaders();

// Debug headers
$headers = getallheaders();
echo "All Headers:\n";
print_r($headers);

echo "\n\nSERVER vars:\n";
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0 || strpos($key, 'AUTH') !== false) {
        echo "$key = $value\n";
    }
}

echo "\n\nAuth Header:\n";
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    echo "Found in HTTP_AUTHORIZATION\n";
}

if (!$authHeader && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    echo "Found in REDIRECT_HTTP_AUTHORIZATION\n";
}

if (!$authHeader) {
    echo "NOT FOUND\n";
} else {
    echo $authHeader . "\n";
}

if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    echo "\nToken extracted: " . $token . "\n";
    
    $decoded = JWT::decode($token);
    echo "\nDecoded payload:\n";
    print_r($decoded);
} else {
    echo "\nNo Bearer token found\n";
}
