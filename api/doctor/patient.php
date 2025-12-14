<?php
// Patient management endpoint (for doctor)
// GET, POST /api/doctor/patient

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/jwt.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

$method = getRequestMethod();

if ($method === 'GET') {
    $user = JWT::requireDoctorAuth();
    $doctorId = $user['id'];
    
    $db = getDB();
    try {
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.email, p.phone, p.age, p.sex, p.weight, p.occupation, p.address
            FROM patients p
            INNER JOIN patient_doctor pd ON p.id = pd.patient_id
            WHERE pd.doctor_id = ?
            ORDER BY p.id DESC
        ");
        $stmt->execute([$doctorId]);
        $patients = $stmt->fetchAll();
        jsonResponse($patients, 200);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} elseif ($method === 'POST') {
    $user = JWT::requireDoctorAuth();
    $doctorId = $user['id'];
    $data = getRequestData();
    
    $patientEmail = $data['patient_email'] ?? null;
    
    if (!$patientEmail) {
        jsonResponse(['error' => 'Missing patient_email'], 400);
    }
    
    $db = getDB();
    try {
        // Find patient by email
        $stmt = $db->prepare("SELECT * FROM patients WHERE email = ?");
        $stmt->execute([$patientEmail]);
        $patient = $stmt->fetch();
        
        if (!$patient) {
            jsonResponse(['error' => 'Patient not found'], 404);
        }
        
        $patientId = $patient['id'];
        
        // Check if already linked
        $stmt = $db->prepare("SELECT * FROM patient_doctor WHERE doctor_id = ? AND patient_id = ?");
        $stmt->execute([$doctorId, $patientId]);
        if ($stmt->fetch()) {
            jsonResponse(['message' => 'Doctor and patient are already linked', 'warning' => 'This doctor-patient relationship already exists'], 200);
        }
        
        // Create link
        $stmt = $db->prepare("INSERT INTO patient_doctor (doctor_id, patient_id) VALUES (?, ?)");
        $stmt->execute([$doctorId, $patientId]);
        
        jsonResponse(['message' => 'Linked doctor and patient'], 201);
        
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            jsonResponse(['message' => 'Doctor and patient are already linked', 'warning' => 'This doctor-patient relationship already exists'], 200);
        }
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
