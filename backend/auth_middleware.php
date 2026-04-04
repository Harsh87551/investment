<?php
require_once 'php_db.php';
require_once 'jwt_utils.php';

function protect() {
    $headers = apache_request_headers();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    // Fallback if apache_request_headers doesn't have it
    if (empty($authHeader) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }

    if ($authHeader) {
        $parts = explode(" ", $authHeader);
        if (count($parts) === 2 && $parts[0] === 'Bearer') {
            $token = $parts[1];
            $jwtSecret = getenv('JWT_SECRET') ?: 'mysecretkey';
            
            $decoded = verifyToken($token, $jwtSecret);
            if ($decoded) {
                return $decoded; // Returns payload e.g. ['id' => 1]
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Invalid token"]);
                exit;
            }
        }
    }

    http_response_code(401);
    echo json_encode(["message" => "No token"]);
    exit;
}
?>
