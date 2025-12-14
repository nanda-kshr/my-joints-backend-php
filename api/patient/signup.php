<?php
// POST /api/patient/signup
// Body: { name, email, phone, age, weight, sex, occupation, address, password }

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
$age = $data['age'] ?? null;
$weight = $data['weight'] ?? null;
$sex = $data['sex'] ?? null;
$occupation = $data['occupation'] ?? null;
$address = $data['address'] ?? null;
$password = $data['password'] ?? null;

if (!$email || !$password) {
    jsonResponse(['error' => 'Email and password are required'], 400);
}

$db = getDB();

try {
    // Check if user already exists
    $stmt = $db->prepare("SELECT * FROM patients WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'User already exists'], 409);
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert patient
    $stmt = $db->prepare("INSERT INTO patients (name, email, phone, age, weight, sex, occupation, address, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $age, $weight, $sex, $occupation, $address, $hashedPassword]);
    
    jsonResponse(['message' => 'Patient registered'], 201);
    
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
