<?php
// POST /api/doctor/consult-request
// Body: { patient_id, doctor_id, message }

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$data = getRequestData();
$patientId = $data['patient_id'] ?? null;
$doctorId = $data['doctor_id'] ?? null;
$message = $data['message'] ?? null;

if (!$patientId || !$doctorId) {
    jsonResponse(['error' => 'Missing patient_id or doctor_id'], 400);
}

$db = getDB();

try {
    $stmt = $db->prepare("INSERT INTO doctor_notifications (doctor_id, patient_id, message, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$doctorId, $patientId, $message]);
    
    jsonResponse(['message' => 'Consultation request sent.'], 200);
    
} catch (Exception $e) {
    jsonResponse(['error' => 'Failed to send consultation request'], 500);
}
