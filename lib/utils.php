<?php
// Common utility functions

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getRequestData() {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    return $_POST;
}

function getPatientIdFromRequest() {
    $data = getRequestData();
    return $data['uid'] ?? $data['patient_id'] ?? $_GET['uid'] ?? $_GET['patient_id'] ?? null;
}

function getRequestMethod() {
    return $_SERVER['REQUEST_METHOD'];
}

function corsHeaders() {
    $envFile = __DIR__ . '/../.env';
    $allowedOrigin = 'http://localhost:3000'; // Default
    
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, 'FRONTEND_URL=') === 0) {
                $allowedOrigin = trim(substr($line, 13));
                break;
            }
        }
    }

    header('Access-Control-Allow-Origin: ' . $allowedOrigin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Authorization');
    
    if (getRequestMethod() === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
