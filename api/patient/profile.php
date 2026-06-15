<?php
// GET /api/patient/profile
// Auth: Bearer token required

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() === 'POST') {
    $user = JWT::requireAuth();
    $patientId = $user['id'];
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['phone']) || !isset($data['address'])) {
        jsonResponse(['error' => 'Missing fields'], 400);
    }
    
    $db = getDB();
    try {
        $stmt = $db->prepare("UPDATE patients SET phone = ?, address = ? WHERE uid = ?");
        $stmt->execute([$data['phone'], $data['address'], $patientId]);
        jsonResponse(['message' => 'Profile updated successfully'], 200);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
} else if (getRequestMethod() === 'GET') {
    $user = JWT::requireAuth();
    $patientId = $user['id'];

    $db = getDB();

    try {
        $stmt = $db->prepare("SELECT uid as id, name, email, phone, sex, age, weight, occupation, address FROM patients WHERE uid = ?");
        $stmt->execute([$patientId]);
        $patient = $stmt->fetch();

        if (!$patient) {
            jsonResponse(['error' => 'Patient not found'], 404);
        }

        jsonResponse(['patient' => $patient], 200);

    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
