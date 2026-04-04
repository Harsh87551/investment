<?php
require_once __DIR__ . '/../backend/jwt_utils.php';

function protectAdmin() {
    // Match the same token extraction logic as auth_middleware.php
    $authHeader = '';
    
    // Try apache_request_headers first (most reliable)
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        }
    }
    
    // Fallback to $_SERVER
    if (empty($authHeader) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    
    // Fallback to REDIRECT_HTTP_AUTHORIZATION (some Apache configs)
    if (empty($authHeader) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    
    // Last fallback: token in query string
    if (empty($authHeader) && isset($_GET['token'])) {
        $authHeader = 'Bearer ' . $_GET['token'];
    }

    if (empty($authHeader)) {
        http_response_code(401);
        echo json_encode(["error" => "No authorization token provided."]);
        exit;
    }

    $parts = explode(" ", $authHeader);
    if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
        http_response_code(401);
        echo json_encode(["error" => "Invalid authorization format."]);
        exit;
    }

    $token = $parts[1];
    $jwtSecret = getenv('JWT_SECRET') ?: 'mysecretkey';
    $decoded = verifyToken($token, $jwtSecret);

    if (!$decoded || !isset($decoded['id'])) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid or expired token."]);
        exit;
    }

    // STRICT ADMIN CHECK: ONLY USER ID = 1
    if ((int)$decoded['id'] !== 1) {
        http_response_code(403);
        echo json_encode(["error" => "Forbidden: You do not have admin privileges."]);
        exit;
    }

    return $decoded;
}
?>
