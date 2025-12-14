<?php
// GET /api/patient/profile
// Auth: Bearer token required

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$user = JWT::requireAuth();
$patientId = $user['id'];

$db = getDB();

try {
    $stmt = $db->prepare("SELECT id, name, email, phone, sex, age, weight, occupation, address FROM patients WHERE id = ?");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch();
    
    if (!$patient) {
        jsonResponse(['error' => 'Patient not found'], 404);
    }
    
    jsonResponse(['patient' => $patient], 200);
    
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
