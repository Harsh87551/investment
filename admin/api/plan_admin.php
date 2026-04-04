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
        $result = $conn->query("SELECT id, amount, percentage, status, hide FROM inv_plan ORDER BY (amount + 0) ASC");
        $plans = [];
        while ($row = $result->fetch_assoc()) {
            $plans[] = $row;
        }
        echo json_encode(["success" => true, "plans" => $plans]);

    } elseif ($action === 'add') {
        $amount = (float)($input['amount'] ?? 0);
        $percentage = (float)($input['percentage'] ?? 0);
        $status = (int)($input['status'] ?? 1);
        $hide = (int)($input['hide'] ?? 0);

        if ($amount <= 0 || $percentage <= 0) throw new Exception("Amount and percentage must be positive.");

        $stmt = $conn->prepare("INSERT INTO inv_plan (amount, percentage, status, hide) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ddii", $amount, $percentage, $status, $hide);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "Plan added."]);

    } elseif ($action === 'update') {
        $id = (int)($input['id'] ?? 0);
        $amount = (float)($input['amount'] ?? 0);
        $percentage = (float)($input['percentage'] ?? 0);
        $status = (int)($input['status'] ?? 1);
        $hide = (int)($input['hide'] ?? 0);

        if (!$id) throw new Exception("Plan ID required.");

        $stmt = $conn->prepare("UPDATE inv_plan SET amount=?, percentage=?, status=?, hide=? WHERE id=?");
        $stmt->bind_param("ddiii", $amount, $percentage, $status, $hide, $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "Plan updated."]);

    } elseif ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) throw new Exception("Plan ID required.");

        $stmt = $conn->prepare("UPDATE inv_plan SET hide=1, status=0 WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "Plan hidden."]);

    } elseif ($action === 'remove') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) throw new Exception("Plan ID required.");

        $stmt = $conn->prepare("DELETE FROM inv_plan WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "Plan permanently deleted."]);

    } else {
        echo json_encode(["success" => false, "error" => "Invalid action."]);
    }

    if (isset($conn)) $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
