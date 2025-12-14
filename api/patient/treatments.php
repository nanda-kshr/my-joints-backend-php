<?php
// Treatments endpoint
// POST, GET, DELETE /api/patient/treatments

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

$method = getRequestMethod();

if ($method === 'POST') {
    $user = JWT::requireDoctorAuth();
    $data = getRequestData();
    
    $patientId = $data['uid'] ?? null;
    if (!$patientId) {
        jsonResponse(['error' => 'Patient uid is required'], 400);
    }
    
    requireDoctorAssignedToPatient($patientId);
    
    $treatment = $data['treatment'] ?? null;
    $name = $data['name'] ?? null;
    $dose = $data['dose'] ?? null;
    $route = $data['route'] ?? null;
    $frequency = $data['frequency'] ?? null;
    $frequency_text = $data['frequency_text'] ?? null;
    $time_period = $data['Time_Period'] ?? null;
    
    $db = getDB();
    try {
        $stmt = $db->prepare("INSERT INTO treatments (patient_id, treatment, name, dose, route, frequency, frequency_text, time_period) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$patientId, $treatment, $name, $dose, $route, $frequency, $frequency_text, $time_period]);
        jsonResponse(['message' => 'Treatment added'], 201);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} elseif ($method === 'GET') {
    $user = JWT::requireAuth();
    $patientId = $_GET['uid'] ?? $user['id'];
    
    if ($user['role'] === 'doctor') {
        requireDoctorAssignedToPatient($patientId);
    } elseif ($user['role'] === 'patient') {
        $patientId = $user['id'];
    }
    
    $db = getDB();
    try {
        $stmt = $db->prepare("SELECT * FROM treatments WHERE patient_id = ? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([$patientId]);
        $results = $stmt->fetchAll();
        jsonResponse($results, 200);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} elseif ($method === 'DELETE') {
    JWT::requireAuth();
    $data = getRequestData();
    $id = $data['id'] ?? null;
    
    if (!$id) {
        jsonResponse(['error' => 'Missing id'], 400);
    }
    
    $db = getDB();
    try {
        $stmt = $db->prepare("DELETE FROM treatments WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['message' => 'Treatment deleted'], 200);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
