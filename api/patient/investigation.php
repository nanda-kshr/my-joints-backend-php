<?php
// Investigation endpoint
// POST, GET, DELETE /api/patient/investigation

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

$method = getRequestMethod();

if ($method === 'POST') {
    $user = JWT::requireDoctorAuth();
    $data = getRequestData();
    // normalize incoming keys to lowercase so clients can send varied casing (e.g. "Hb" or "hb")
    $normalized = array_change_key_case($data, CASE_LOWER);
    
    $patientId = getPatientIdFromRequest();
    if (!$patientId) {
        jsonResponse(['error' => 'Patient uid is required'], 400);
    }
    
    requireDoctorAssignedToPatient($patientId);
    
    $fields = [
        'hb', 'total_leukocyte_count', 'differential_count', 'platelet_count', 
        'esr', 'crp', 'lft_total_bilirubin', 'lft_direct_bilirubin', 'ast', 'alt', 
        'albumin', 'total_protein', 'ggt', 'urea', 'creatinine', 'uric_acid', 
        'urine_routine', 'urine_pcr', 'ra_factor', 'anti_ccp'
    ];
    
    $values = [];
    $placeholders = [];
    $columns = ['uid'];
    $values[] = $patientId;
    
    foreach ($fields as $field) {
        if (isset($normalized[$field])) {
            $columns[] = $field;
            $values[] = $normalized[$field];
            $placeholders[] = '?';
        }
    }
    
    $db = getDB();
    try {
        if (empty($placeholders)) {
            // Only uid provided
            $sql = "INSERT INTO Investigation (uid) VALUES (?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$patientId]);
        } else {
            $sql = "INSERT INTO Investigation (uid, " . implode(', ', array_slice($columns, 1)) . ") VALUES (?, " . implode(', ', $placeholders) . ")";
            $stmt = $db->prepare($sql);
            $stmt->execute($values);
        }
        jsonResponse(['message' => 'Investigation added'], 201);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} elseif ($method === 'GET') {
    $user = JWT::requireAuth();
    $patientId = getPatientIdFromRequest() ?? $user['id'];
    
    if ($user['role'] === 'doctor') {
        requireDoctorAssignedToPatient($patientId);
    } elseif ($user['role'] === 'patient' && $patientId != $user['id']) {
        jsonResponse(['error' => 'Patient can only access their own data'], 403);
    }
    
    $db = getDB();
    try {
        $stmt = $db->prepare("SELECT * FROM Investigation WHERE uid = ? ORDER BY createdAt DESC LIMIT 20");
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
        $stmt = $db->prepare("DELETE FROM Investigation WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['message' => 'Investigation deleted'], 200);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
