<?php
// GET /api/doctor/profile
// Auth: Bearer token required

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$user = JWT::requireDoctorAuth();
$doctorId = $user['id'];

$db = getDB();

try {
    $stmt = $db->prepare("SELECT id, name, email, phone, address, specialization FROM doctors WHERE id = ?");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch();
    
    if (!$doctor) {
        jsonResponse(['error' => 'Doctor not found'], 404);
    }
    
    jsonResponse(['doctor' => $doctor], 200);
    
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
