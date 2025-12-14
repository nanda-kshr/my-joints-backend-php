<?php
// GET /api/patient/download?filename=X

require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$filename = $_GET['filename'] ?? null;

if (!$filename) {
    jsonResponse(['error' => 'Missing filename'], 400);
}

// Prevent path traversal
if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
    jsonResponse(['error' => 'Invalid filename'], 400);
}

$filePath = __DIR__ . '/../../public/' . $filename;

if (!file_exists($filePath)) {
    jsonResponse(['error' => 'File not found'], 404);
}

// Set headers for file download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
exit;
