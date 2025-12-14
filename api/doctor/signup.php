<?php
// POST /api/doctor/signup
// Body: { name, email, phone, specialization, address, password }

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$data = getRequestData();
$name = $data['name'] ?? null;
$email = $data['email'] ?? null;
$phone = $data['phone'] ?? null;
$specialization = $data['specialization'] ?? null;
$address = $data['address'] ?? null;
$password = $data['password'] ?? null;

if (!$email || !$password) {
    jsonResponse(['error' => 'Email and password are required'], 400);
}

$db = getDB();

try {
    // Check if doctor already exists
    $stmt = $db->prepare("SELECT * FROM doctors WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'User already exists'], 409);
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert doctor
    $stmt = $db->prepare("INSERT INTO doctors (name, email, phone, specialization, address, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $specialization, $address, $hashedPassword]);
    
    jsonResponse(['message' => 'Doctor registered'], 201);
    
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
