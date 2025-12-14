<?php
// Router for API endpoints
// Maps URL paths to corresponding PHP files

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Get the requested path
$path = $_GET['path'] ?? '';
$path = trim($path, '/');

// Map path to file
$filePath = __DIR__ . '/api/' . $path . '.php';

// Check if file exists
if (file_exists($filePath)) {
    require_once $filePath;
} else {
    // Try to handle as a directory with index.php
    $dirPath = __DIR__ . '/api/' . $path . '/index.php';
    if (file_exists($dirPath)) {
        require_once $dirPath;
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Endpoint not found', 'path' => $path]);
    }
}
