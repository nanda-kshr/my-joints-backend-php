<?php
// Complaints endpoint
// POST, GET, DELETE /api/patient/complaints

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

$method = getRequestMethod();

if ($method === 'POST') {
    $user = JWT::requireAuth();
    $data = getRequestData();
    
    $text = $data['text'] ?? null;
    $patientId = $data['uid'] ?? null;
    $doctorId = $data['did'] ?? null;
    
    if ($user['role'] === 'doctor') {
        $doctorId = $user['id'];
        if (!$patientId) {
            jsonResponse(['error' => 'Patient ID is required'], 400);
        }
        requireDoctorAssignedToPatient($patientId);
    } elseif ($user['role'] === 'patient') {
        $patientId = $user['id'];
        if (!$doctorId) {
            jsonResponse(['error' => 'Doctor ID is required'], 400);
        }
    } else {
        jsonResponse(['error' => 'Forbidden'], 403);
    }
    
    $db = getDB();
    try {
        $stmt = $db->prepare("INSERT INTO complaints (patient_id, doctor_id, complaint) VALUES (?, ?, ?)");
        $stmt->execute([$patientId, $doctorId, $text]);
        jsonResponse(['message' => 'Complaint added'], 201);
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
        $stmt = $db->prepare("SELECT * FROM complaints WHERE patient_id = ? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([$patientId]);
        $results = $stmt->fetchAll();
        jsonResponse($results, 200);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} elseif ($method === 'DELETE') {
    $user = JWT::requireAuth();
    $data = getRequestData();
    $id = $data['id'] ?? null;
    
    if (!$id) {
        jsonResponse(['error' => 'Missing id'], 400);
    }
    
    $db = getDB();
    try {
        // Check ownership
        $stmt = $db->prepare("SELECT * FROM complaints WHERE id = ?");
        $stmt->execute([$id]);
        $complaint = $stmt->fetch();
        
        if (!$complaint) {
            jsonResponse(['error' => 'Not found'], 404);
        }
        
        $isOwner = ($user['role'] === 'doctor' && $complaint['doctor_id'] == $user['id']) ||
                   ($user['role'] === 'patient' && $complaint['patient_id'] == $user['id']);
        
        if (!$isOwner) {
            jsonResponse(['error' => 'Forbidden'], 403);
        }
        
        $stmt = $db->prepare("DELETE FROM complaints WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['message' => 'Complaint deleted'], 200);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
