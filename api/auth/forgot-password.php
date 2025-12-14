<?php
// POST /api/auth/forgot-password
// Body: { email }

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/mail.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$data = getRequestData();
$email = $data['email'] ?? null;

if (!$email) {
    jsonResponse(['error' => 'Email is required'], 400);
}

$db = getDB();

try {
    // Check patients first
    $stmt = $db->prepare("SELECT * FROM patients WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    $userType = 'patients';
    
    // If not found in patients, check doctors
    if (!$user) {
        $stmt = $db->prepare("SELECT * FROM doctors WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $userType = 'doctors';
    }
    
    if (!$user) {
        jsonResponse(['error' => 'User not found'], 404);
    }
    
    // Generate 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiryDate = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Update user with OTP
    $stmt = $db->prepare("UPDATE $userType SET otp = ?, otp_expiry = ? WHERE id = ?");
    $stmt->execute([$otp, $expiryDate, $user['id']]);
    
    // Send email
    try {
        sendPasswordResetEmail($email, $otp);
        jsonResponse(['message' => 'OTP sent to your email'], 200);
    } catch (Exception $e) {
        // Rollback OTP on email failure
        $stmt = $db->prepare("UPDATE $userType SET otp = NULL, otp_expiry = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        jsonResponse(['error' => 'Error sending OTP email'], 500);
    }
    
} catch (Exception $e) {
    jsonResponse(['error' => 'Internal server error'], 500);
}
