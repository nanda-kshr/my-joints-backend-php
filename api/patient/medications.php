<?php
// Medications endpoint
// POST, GET, DELETE /api/patient/medications

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

$method = getRequestMethod();

if ($method === 'POST') {
    $user = JWT::requireDoctorAuth();
    $data = getRequestData();
    
    $patientId = $data['uid'] ?? null;
    $medications = $data['medications'] ?? null;
    
    if (!$patientId || !$medications) {
        jsonResponse(['error' => 'Patient uid and medications are required'], 400);
    }
    
    requireDoctorAssignedToPatient($patientId);
    
    $db = getDB();
    try {
        $stmt = $db->prepare("INSERT INTO medications (patient_id, medications) VALUES (?, ?)");
        $stmt->execute([$patientId, $medications]);
        jsonResponse(['message' => 'Medication added'], 201);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} elseif ($method === 'GET') {
    $user = JWT::requireAuth();
    $patientId = $_GET['uid'] ?? $user['id'];
    
    $db = getDB();
    try {
        $stmt = $db->prepare("SELECT * FROM medications WHERE patient_id = ? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([$patientId]);
        $results = $stmt->fetchAll();
        jsonResponse($results, 200);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} elseif ($method === 'DELETE') {
    JWT::requireAuth();
    $data = getRequestData();
    $id = $data['id'] ?? null;
    
    if (!$id) {
        jsonResponse(['error' => 'Missing id'], 400);
    }
    
    $db = getDB();
    try {
        $stmt = $db->prepare("DELETE FROM medications WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['message' => 'Medication deleted'], 200);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
