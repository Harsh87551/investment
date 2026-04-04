<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../../backend/php_db.php';
require_once __DIR__ . '/../admin_auth.php';

$admin = protectAdmin();

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);
$action = isset($input['action']) ? $input['action'] : '';

try {
    if (!isset($conn) || $conn === null) throw new Exception("DB connection failed.");

    if ($action === 'list') {
        $result = $conn->query("SELECT id, name, email, phone, createdon, bankname, account, ifsc_code FROM inv_users WHERE id != 1 ORDER BY id DESC LIMIT 100");
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode(["success" => true, "users" => $users]);

    } elseif ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) throw new Exception("Missing user ID.");
        if ($id === 1) throw new Exception("Cannot delete admin user.");
        
        // Delete related records first
        $conn->query("DELETE FROM inv_kyc WHERE id = $id");
        $conn->query("DELETE FROM inv_user_package WHERE user_id = $id");
        
        $stmt = $conn->prepare("DELETE FROM inv_users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "User deleted."]);
        } else {
            throw new Exception("Failed to delete user.");
        }
        $stmt->close();

    } else {
        echo json_encode(["success" => false, "error" => "Invalid action."]);
    }

    if (isset($conn)) $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
