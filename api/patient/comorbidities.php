<?php
// Comorbidities endpoint
// POST, GET, DELETE /api/patient/comorbidities

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

$method = getRequestMethod();

if ($method === 'POST') {
    $user = JWT::requireDoctorAuth();
    $data = getRequestData();
    
    $patientId = $data['uid'] ?? null;
    $text = $data['text'] ?? null;
    
    if (!$patientId || !$text) {
        jsonResponse(['error' => 'Patient uid and text are required'], 400);
    }
    
    requireDoctorAssignedToPatient($patientId);
    
    $db = getDB();
    try {
        $stmt = $db->prepare("INSERT INTO comorbidities (patient_id, text) VALUES (?, ?)");
        $stmt->execute([$patientId, $text]);
        jsonResponse(['message' => 'Comorbidity added'], 201);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} elseif ($method === 'GET') {
    $user = JWT::requireAuth();
    $patientId = $_GET['uid'] ?? $user['id'];
    
    if ($user['role'] === 'doctor') {
        requireDoctorAssignedToPatient($patientId);
    } elseif ($user['role'] === 'patient' && $patientId != $user['id']) {
        jsonResponse(['error' => 'Patient can only access their own data'], 403);
    }
    
    $db = getDB();
    try {
        $stmt = $db->prepare("SELECT * FROM comorbidities WHERE patient_id = ? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([$patientId]);
        $results = $stmt->fetchAll();
        jsonResponse($results, 200);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} elseif ($method === 'DELETE') {
    $user = JWT::requireAuth();
    $data = getRequestData();
    $id = $data['id'] ?? null;
    
    if (!$id) {
        jsonResponse(['error' => 'Missing id'], 400);
    }
    
    if ($user['role'] === 'doctor') {
        requireDoctorAssignedToPatient($user['id']);
    }
    
    $db = getDB();
    try {
        // Verify ownership
        $stmt = $db->prepare("SELECT * FROM comorbidities WHERE id = ? AND patient_id = ?");
        $stmt->execute([$id, $user['id']]);
        if (!$stmt->fetch()) {
            jsonResponse(['error' => 'Comorbidity not found or access denied'], 404);
        }
        
        $stmt = $db->prepare("DELETE FROM comorbidities WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['message' => 'Comorbidity deleted'], 200);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
