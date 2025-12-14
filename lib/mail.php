<?php
// Email helper functions

function loadEnv() {
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

function sendMail($to, $subject, $body) {
    $env = loadEnv();
    
    $headers = "From: " . ($env['SMTP_FROM_NAME'] ?? 'MyJoints') . " <" . ($env['SMTP_FROM'] ?? 'noreply@myjoints.com') . ">\r\n";
    $headers .= "Reply-To: " . ($env['SMTP_FROM'] ?? 'noreply@myjoints.com') . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    $success = mail($to, $subject, $body, $headers);
    
    return $success;
}

function sendPasswordResetEmail($email, $otp) {
    $subject = "Password Reset OTP";
    $body = "
        <html>
        <body>
            <h2>Password Reset Request</h2>
            <p>Your OTP for password reset is: <strong>$otp</strong></p>
            <p>This OTP will expire in 10 minutes.</p>
            <p>If you did not request this, please ignore this email.</p>
        </body>
        </html>
    ";
    
    return sendMail($email, $subject, $body);
}
