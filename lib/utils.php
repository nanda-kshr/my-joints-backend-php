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

function getRequestMethod() {
    return $_SERVER['REQUEST_METHOD'];
}

function corsHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    if (getRequestMethod() === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
