<?php
// PUT /api/doctor/notifications/update
// Body: { id, status }

require_once __DIR__ . '/../../../lib/database.php';
require_once __DIR__ . '/../../../lib/mail.php';
require_once __DIR__ . '/../../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'PUT') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$data = getRequestData();
$id = $data['id'] ?? null;
$status = $data['status'] ?? null;

if (!$id || !$status || !in_array($status, ['pending', 'accepted', 'rejected'])) {
    jsonResponse(['error' => 'Missing or invalid id/status'], 400);
}

$db = getDB();

try {
    // Update notification status
    $stmt = $db->prepare("UPDATE doctor_notifications SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    
    if ($stmt->rowCount() === 0) {
        jsonResponse(['error' => 'Notification not found or not updated'], 404);
    }
    
    // Get notification details to send email
    $stmt = $db->prepare("SELECT * FROM doctor_notifications WHERE id = ?");
    $stmt->execute([$id]);
    $notification = $stmt->fetch();
    
    if ($notification && isset($notification['patient_id'])) {
        $stmt = $db->prepare("SELECT email FROM patients WHERE id = ?");
        $stmt->execute([$notification['patient_id']]);
        $patient = $stmt->fetch();
        
        if ($patient && isset($patient['email'])) {
            try {
                sendMail(
                    $patient['email'],
                    'Consultation Request Status Updated',
                    "Your consultation request status has been updated to: $status"
                );
            } catch (Exception $e) {
                // Email failure is not critical
            }
        }
    }
    
    jsonResponse(['message' => 'Notification status updated', 'id' => $id, 'status' => $status], 200);
    
} catch (Exception $e) {
    jsonResponse(['error' => 'Failed to update notification'], 500);
}
