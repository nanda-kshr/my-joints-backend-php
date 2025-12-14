<?php
// POST /api/auth/verify-otp
// Body: { email, otp }

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$data = getRequestData();
$email = $data['email'] ?? null;
$otp = $data['otp'] ?? null;

if (!$email || !$otp) {
    jsonResponse(['error' => 'Email and OTP are required'], 400);
}

$db = getDB();

try {
    // Check patients first
    $stmt = $db->prepare("SELECT * FROM patients WHERE email = ? AND otp = ? AND otp_expiry > NOW()");
    $stmt->execute([$email, $otp]);
    $user = $stmt->fetch();
    
    // If not found in patients, check doctors
    if (!$user) {
        $stmt = $db->prepare("SELECT * FROM doctors WHERE email = ? AND otp = ? AND otp_expiry > NOW()");
        $stmt->execute([$email, $otp]);
        $user = $stmt->fetch();
    }
    
    if (!$user) {
        jsonResponse(['error' => 'Invalid or expired OTP'], 400);
    }
    
    jsonResponse(['message' => 'OTP verified successfully'], 200);
    
} catch (Exception $e) {
    jsonResponse(['error' => 'Internal server error'], 500);
}
