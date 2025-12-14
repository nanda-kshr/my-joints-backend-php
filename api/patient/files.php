<?php
// GET /api/patient/files?patient_id=X

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$patientId = $_GET['patient_id'] ?? null;

if (!$patientId) {
    jsonResponse(['error' => 'Missing patient_id'], 400);
}

$db = getDB();

try {
    $stmt = $db->prepare("SELECT id, original_filename, stored_filename, uploaded_at FROM patient_files WHERE patient_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$patientId]);
    $files = $stmt->fetchAll();
    
    if (empty($files)) {
        jsonResponse(['message' => 'No files found', 'files' => []], 200);
    }
    
    jsonResponse(['files' => $files], 200);
    
} catch (Exception $e) {
    jsonResponse(['error' => 'Failed to fetch files'], 500);
}
