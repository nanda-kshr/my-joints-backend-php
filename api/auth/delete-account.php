<?php
// POST /api/auth/delete-account
// Body: { email, password?, role? }

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$data = getRequestData();
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;
$role = $data['role'] ?? null;

if (!$email) {
    jsonResponse(['error' => 'Email is required'], 400);
}

$db = getDB();

try {
    $user = null;
    $userType = '';
    
    if ($role === 'doctor') {
        $stmt = $db->prepare("SELECT * FROM doctors WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $userType = 'doctors';
    } elseif ($role === 'patient') {
        $stmt = $db->prepare("SELECT * FROM patients WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $userType = 'patients';
    } else {
        // Try patients first, then doctors
        $stmt = $db->prepare("SELECT * FROM patients WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $userType = 'patients';
        
        if (!$user) {
            $stmt = $db->prepare("SELECT * FROM doctors WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $userType = 'doctors';
        }
    }
    
    if (!$user) {
        jsonResponse(['error' => 'User not found'], 404);
    }
    
    // If password provided, verify it
    if ($password) {
        if (!password_verify($password, $user['password'])) {
            jsonResponse(['error' => 'Invalid credentials'], 401);
        }
    }
    
    // Delete user
    $stmt = $db->prepare("DELETE FROM $userType WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    $roleLabel = $userType === 'doctors' ? 'Doctor' : 'Patient';
    jsonResponse(['message' => "User deleted ($roleLabel)"], 200);
    
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
