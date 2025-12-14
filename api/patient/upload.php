<?php
// POST /api/patient/upload
// Multipart form data: file, patient_id

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/utils.php';

corsHeaders();

if (getRequestMethod() !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$patientId = $_POST['patient_id'] ?? null;

if (!$patientId || !isset($_FILES['file'])) {
    jsonResponse(['error' => 'File and patient_id are required'], 400);
}

$file = $_FILES['file'];
$originalFilename = $file['name'];
$tmpName = $file['tmp_name'];
$ext = pathinfo($originalFilename, PATHINFO_EXTENSION);

// Generate unique filename
$storedFilename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$destPath = __DIR__ . '/../../public/' . $storedFilename;

$db = getDB();

try {
    // Move uploaded file
    if (!move_uploaded_file($tmpName, $destPath)) {
        jsonResponse(['error' => 'File upload failed'], 500);
    }
    
    // Insert file record
    $stmt = $db->prepare("INSERT INTO patient_files (patient_id, original_filename, stored_filename) VALUES (?, ?, ?)");
    $stmt->execute([$patientId, $originalFilename, $storedFilename]);
    
    jsonResponse(['message' => 'File uploaded', 'filename' => $storedFilename], 200);
    
} catch (Exception $e) {
    jsonResponse(['error' => 'File upload failed'], 500);
}
