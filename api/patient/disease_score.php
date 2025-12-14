<?php
// Disease Score endpoint
// POST, GET, DELETE /api/patient/disease_score

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

$method = getRequestMethod();

if ($method === 'POST') {
    $user = JWT::requireDoctorAuth();
    $data = getRequestData();
    
    $patientId = $data['uid'] ?? null;
    $sdai = $data['sdai'] ?? null;
    $das_28_crp = $data['das_28_crp'] ?? null;
    
    if (!$patientId) {
        jsonResponse(['error' => 'Patient uid is required'], 400);
    }
    
    requireDoctorAssignedToPatient($patientId);
    
    $db = getDB();
    try {
        $stmt = $db->prepare("INSERT INTO disease_scores (patient_id, sdai, das_28_crp) VALUES (?, ?, ?)");
        $stmt->execute([$patientId, $sdai, $das_28_crp]);
        jsonResponse(['message' => 'Disease score added'], 201);
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
        $stmt = $db->prepare("SELECT * FROM disease_scores WHERE patient_id = ? ORDER BY created_at DESC LIMIT 20");
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
        $stmt = $db->prepare("DELETE FROM disease_scores WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['message' => 'Disease score deleted'], 200);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
