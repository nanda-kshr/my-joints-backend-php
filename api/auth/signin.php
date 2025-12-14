<?php
// POST /api/auth/signin
// Body: { email, password, role }

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$data = getRequestData();
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;
$role = $data['role'] ?? null;

if (!$email || !$password || !$role) {
    jsonResponse(['error' => 'Missing required fields'], 400);
}

if (!in_array($role, ['doctor', 'patient'])) {
    jsonResponse(['error' => 'Invalid role'], 400);
}

$db = getDB();
$table = $role === 'doctor' ? 'doctors' : 'patients';

try {
    $stmt = $db->prepare("SELECT * FROM $table WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['error' => 'Invalid credentials'], 401);
    }
    
    if (!password_verify($password, $user['password'])) {
        jsonResponse(['error' => 'Invalid credentials'], 401);
    }
    
    $payload = [
        'id' => $user['id'],
        'email' => $user['email'],
        'role' => $role
    ];
    
    $token = JWT::encode($payload);
    
    jsonResponse([
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'role' => $role
        ]
    ], 200);
    
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
