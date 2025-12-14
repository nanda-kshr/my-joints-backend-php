<?php
// GET /api/doctor/notifications

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
    $stmt = $db->prepare("SELECT * FROM doctor_notifications WHERE doctor_id = ? AND status = 'pending' ORDER BY created_at DESC");
    $stmt->execute([$doctorId]);
    $notifications = $stmt->fetchAll();
    
    jsonResponse(['notifications' => $notifications], 200);
    
} catch (Exception $e) {
    jsonResponse(['error' => 'Failed to fetch notifications'], 500);
}
