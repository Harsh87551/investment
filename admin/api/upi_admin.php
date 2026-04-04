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
        $result = $conn->query("SELECT id, upi_id, status, hide FROM inv_upi ORDER BY id DESC");
        $upis = [];
        while ($row = $result->fetch_assoc()) {
            $upis[] = $row;
        }
        echo json_encode(["success" => true, "upis" => $upis]);

    } elseif ($action === 'add') {
        $upi_id = trim($input['upi_id'] ?? '');
        if (empty($upi_id)) throw new Exception("UPI ID is required.");

        // Deactivate all existing
        $conn->query("UPDATE inv_upi SET status=0 WHERE status=1");

        $stmt = $conn->prepare("INSERT INTO inv_upi (upi_id, status, hide) VALUES (?, 1, 0)");
        $stmt->bind_param("s", $upi_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "UPI added and set active."]);

    } elseif ($action === 'activate') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) throw new Exception("UPI record ID required.");

        $conn->query("UPDATE inv_upi SET status=0 WHERE status=1");
        $stmt = $conn->prepare("UPDATE inv_upi SET status=1 WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "UPI activated."]);

    } elseif ($action === 'update') {
        $id = (int)($input['id'] ?? 0);
        $upi_id = trim($input['upi_id'] ?? '');
        if (!$id || empty($upi_id)) throw new Exception("UPI record ID and new UPI ID are required.");

        $stmt = $conn->prepare("UPDATE inv_upi SET upi_id=? WHERE id=?");
        $stmt->bind_param("si", $upi_id, $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "UPI updated."]);

    } elseif ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) throw new Exception("UPI record ID required.");

        $stmt = $conn->prepare("UPDATE inv_upi SET hide=1, status=0 WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "UPI removed."]);

    } else {
        echo json_encode(["success" => false, "error" => "Invalid action."]);
    }

    if (isset($conn)) $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
