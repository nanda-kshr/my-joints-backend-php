<?php
// JWT helper functions using Firebase JWT library
// Install via: composer require firebase/php-jwt

require_once __DIR__ . '/database.php';

class JWT {
    private static function getSecret() {
        $env = self::loadEnv();
        return $env['JWT_SECRET'] ?? 'default_secret_change_this';
    }
    
    private static function loadEnv() {
        $env = [];
        $envFile = __DIR__ . '/../.env';
        
        if (!file_exists($envFile)) {
            $envFile = __DIR__ . '/../.env.example';
        }
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, '=') === false) continue;
                list($key, $value) = explode('=', $line, 2);
                $env[trim($key)] = trim($value);
            }
        }
        
        return $env;
    }
    
    public static function encode($payload) {
        $secret = self::getSecret();
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['exp'] = time() + (24 * 60 * 60); // 1 day expiry
        $payload = json_encode($payload);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    public static function decode($jwt) {
        $secret = self::getSecret();
        $tokenParts = explode('.', $jwt);
        
        if (count($tokenParts) !== 3) {
            return null;
        }
        
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $signatureProvided = $tokenParts[2];
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if ($base64UrlSignature !== $signatureProvided) {
            return null;
        }
        
        $payloadArray = json_decode($payload, true);
        
        if (isset($payloadArray['exp']) && $payloadArray['exp'] < time()) {
            return null;
        }
        
        return $payloadArray;
    }
    
    public static function verifyToken() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        // Fallback: Check HTTP_AUTHORIZATION server variable (for Apache)
        if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }
        
        // Fallback: Check REDIRECT_HTTP_AUTHORIZATION (for some Apache configs)
        if (!$authHeader && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        
        // Fallback: Check X-Authorization custom header (workaround for Apache issues)
        if (!$authHeader) {
            $authHeader = $headers['X-Authorization'] ?? $headers['x-authorization'] ?? null;
            if (!$authHeader && isset($_SERVER['HTTP_X_AUTHORIZATION'])) {
                $authHeader = $_SERVER['HTTP_X_AUTHORIZATION'];
            }
        }
        
        if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            return null;
        }
        
        $token = substr($authHeader, 7);
        return self::decode($token);
    }
    
    public static function requireAuth() {
        $user = self::verifyToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            exit;
        }
        return $user;
    }
    
    public static function requireDoctorAuth() {
        $user = self::requireAuth();
        if ($user['role'] !== 'doctor') {
            http_response_code(403);
            echo json_encode(['error' => 'Doctor access required']);
            exit;
        }
        return $user;
    }
    
    public static function requirePatientAuth() {
        $user = self::requireAuth();
        if ($user['role'] !== 'patient') {
            http_response_code(403);
            echo json_encode(['error' => 'Patient access required']);
            exit;
        }
        return $user;
    }
}

function requireDoctorAssignedToPatient($patientId) {
    $user = JWT::requireDoctorAuth();
    $doctorId = $user['id'];
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM patient_doctor WHERE doctor_id = ? AND patient_id = ?");
    $stmt->execute([$doctorId, $patientId]);
    $link = $stmt->fetch();
    
    if (!$link) {
        http_response_code(403);
        echo json_encode(['error' => 'Doctor not assigned to this patient']);
        exit;
    }
    
    return $user;
}
