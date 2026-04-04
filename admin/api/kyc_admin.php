<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../backend/php_db.php';
require_once __DIR__ . '/../admin_auth.php';

$admin = protectAdmin();

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);
$action = isset($input['action']) ? $input['action'] : '';

try {
    if (!isset($conn) || $conn === null) {
        throw new Exception("Database connection failed.");
    }

    if ($action === 'list') {
        $stmt = $conn->prepare("SELECT id, aadhar_number, aadharpic, pancard_number, pancardpic, status FROM inv_kyc ORDER BY status ASC, id DESC LIMIT 100");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $kyc = [];
        while ($row = $result->fetch_assoc()) {
            $kyc[] = [
                "user_id" => $row['id'],
                "pan_number" => $row['pancard_number'],
                "aadhaar" => $row['aadhar_number'],
                "pan_image" => $row['pancardpic'],
                "aadhar_image" => $row['aadharpic'],
                "status" => $row['status']
            ];
        }
        $stmt->close();
        echo json_encode(["success" => true, "kyc" => $kyc]);

    } elseif ($action === 'update') {
        $userId = (int)($input['userId'] ?? 0);
        $status = (int)($input['status'] ?? 0);

        if (!$userId || !$status) {
            throw new Exception("Missing userId or status.");
        }

        $stmt = $conn->prepare("UPDATE inv_kyc SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $userId);
        
        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode(["success" => true, "message" => "KYC status updated."]);
        } else {
            throw new Exception("Database update failed: " . $stmt->error);
        }

    } elseif ($action === 'delete') {
        $userId = (int)($input['userId'] ?? 0);
        if (!$userId) throw new Exception("Missing userId.");
        
        $stmt = $conn->prepare("DELETE FROM inv_kyc WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode(["success" => true, "message" => "KYC record deleted."]);
        } else {
            throw new Exception("Failed to delete KYC record.");
        }

    } else {
        echo json_encode(["success" => false, "error" => "Invalid action."]);
    }

    if (isset($conn) && $conn) $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
