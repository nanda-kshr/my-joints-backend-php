<?php
// GET /api/patient/doctors?patient_id=X

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$user = JWT::requireAuth();
$patientId = $_GET['patient_id'] ?? null;

if (!$patientId) {
    jsonResponse(['error' => 'Missing patient_id'], 400);
}

// If doctor, verify assignment
if ($user['role'] === 'doctor') {
    requireDoctorAssignedToPatient($patientId);
}

$db = getDB();

try {
    $stmt = $db->prepare("
        SELECT d.id, d.name, d.email, d.phone, d.specialization, d.address 
        FROM doctors d
        INNER JOIN patient_doctor pd ON d.id = pd.doctor_id
        WHERE pd.patient_id = ?
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
