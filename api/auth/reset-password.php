<?php
// POST /api/auth/reset-password
// Body: { email, otp, password }

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$data = getRequestData();
$email = $data['email'] ?? null;
$otp = $data['otp'] ?? null;
$password = $data['password'] ?? null;

if (!$email || !$otp || !$password) {
    jsonResponse(['error' => 'Email, OTP, and password are required'], 400);
}

$db = getDB();

try {
    // Check patients first
    $stmt = $db->prepare("SELECT * FROM patients WHERE email = ? AND otp = ? AND otp_expiry > NOW()");
    $stmt->execute([$email, $otp]);
    $user = $stmt->fetch();
    $userType = 'patients';
    
    // If not found in patients, check doctors
    if (!$user) {
        $stmt = $db->prepare("SELECT * FROM doctors WHERE email = ? AND otp = ? AND otp_expiry > NOW()");
        $stmt->execute([$email, $otp]);
        $user = $stmt->fetch();
        $userType = 'doctors';
    }
    
    if (!$user) {
        jsonResponse(['error' => 'Invalid or expired OTP'], 400);
    }
    
    // Hash new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Update password and clear OTP
    $stmt = $db->prepare("UPDATE $userType SET password = ?, otp = NULL, otp_expiry = NULL WHERE id = ?");
    $stmt->execute([$hashedPassword, $user['id']]);
    
    jsonResponse(['message' => 'Password reset successfully'], 200);
    
} catch (Exception $e) {
    jsonResponse(['error' => 'Internal server error'], 500);
}
