<?php
function base64UrlEncode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

function base64UrlDecode($data) {
    $padLength = 4 - strlen($data) % 4;
    $data .= str_repeat('=', $padLength);
    return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
}

function generateToken($payload, $secret) {
    // Add exp to payload (1 day = 86400 seconds)
    $payload['exp'] = time() + 86400;

    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payloadData = json_encode($payload);

    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payloadData);

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = base64UrlEncode($signature);

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function verifyToken($token, $secret) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }

    list($header, $payload, $signature) = $parts;

    $validSignature = hash_hmac('sha256', $header . "." . $payload, $secret, true);
    $base64UrlValidSignature = base64UrlEncode($validSignature);

    if (hash_equals($base64UrlValidSignature, $signature)) {
        $decodedPayload = json_decode(base64UrlDecode($payload), true);
        if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) {
            return false; // Token expired
        }
        return $decodedPayload;
    }
    return false;
}
?>
