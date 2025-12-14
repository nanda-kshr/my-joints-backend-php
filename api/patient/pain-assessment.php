<?php
// Pain Assessment endpoint
// POST, GET /api/patient/pain-assessment

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

$method = getRequestMethod();

if ($method === 'POST') {
    $data = getRequestData();
    
    $patientId = $data['patient_id'] ?? null;
    $painScore = $data['pain_score'] ?? null;
    
    if (!$patientId || !is_numeric($painScore)) {
        jsonResponse(['error' => 'Missing or invalid patient_id or pain_score'], 400);
    }
    
    $db = getDB();
    try {
        $stmt = $db->prepare("INSERT INTO pain_assessments (patient_id, pain_score) VALUES (?, ?)");
        $stmt->execute([$patientId, $painScore]);
        jsonResponse(['message' => 'Pain score recorded'], 200);
    } catch (Exception $e) {
        jsonResponse(['error' => 'Failed to record pain score'], 500);
    }
    
} elseif ($method === 'GET') {
    $patientId = $_GET['patient_id'] ?? null;
    
    if (!$patientId) {
        jsonResponse(['error' => 'Missing patient_id'], 400);
    }
    
    $db = getDB();
    try {
        $stmt = $db->prepare("SELECT pain_score, recorded_at FROM pain_assessments WHERE patient_id = ? ORDER BY recorded_at DESC LIMIT 10");
        $stmt->execute([$patientId]);
        $scores = $stmt->fetchAll();
        jsonResponse(['scores' => $scores], 200);
    } catch (Exception $e) {
        jsonResponse(['error' => 'Failed to fetch pain scores'], 500);
    }
    
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
