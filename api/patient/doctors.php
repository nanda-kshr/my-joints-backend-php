<?php
// GET /api/patient/doctors?uid=X

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$user = JWT::requireAuth();
$patientId = getPatientIdFromRequest();

if (!$patientId) {
    jsonResponse(['error' => 'Missing uid'], 400);
}

// If doctor, verify assignment
if ($user['role'] === 'doctor') {
    requireDoctorAssignedToPatient($patientId);
}

$db = getDB();

try {
    $stmt = $db->prepare("
           SELECT d.did AS id, d.name, d.email, d.phone, d.specialization, d.address 
           FROM Doctors d 
           INNER JOIN Patient_Doctor pd ON d.did = pd.did 
           WHERE pd.uid = ?
    ");
    $stmt->execute([$patientId]);
    $doctors = $stmt->fetchAll();
    
    if (empty($doctors)) {
        jsonResponse(['message' => 'No assigned doctors found', 'doctors' => []], 200);
    }
    
    jsonResponse(['doctors' => $doctors], 200);
    
} catch (Exception $e) {
    jsonResponse(['error' => 'Failed to fetch assigned doctors'], 500);
}
