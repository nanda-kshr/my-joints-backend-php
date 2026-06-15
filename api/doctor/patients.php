<?php
// GET /api/doctor/patients (alias endpoint)

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
    $stmt = $db->prepare("
        SELECT p.uid as id, p.name, p.email, p.phone, p.age, p.sex, p.weight, p.occupation, p.address
        FROM patients p
        INNER JOIN patient_doctor pd ON p.uid = pd.uid
        WHERE pd.did = ?
        ORDER BY p.uid DESC
    ");
    $stmt->execute([$doctorId]);
    $patients = $stmt->fetchAll();

    jsonResponse($patients, 200);

} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
