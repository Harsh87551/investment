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
        $result = $conn->query("SELECT id, strategy, trip, name, status, hide FROM inv_package ORDER BY id ASC");
        $packages = [];
        while ($row = $result->fetch_assoc()) {
            $packages[] = $row;
        }
        echo json_encode(["success" => true, "packages" => $packages]);

    } elseif ($action === 'add') {
        $name = trim($input['name'] ?? '');
        $strategy = trim($input['strategy'] ?? '');
        $trip = trim($input['trip'] ?? '');
        $status = (int)($input['status'] ?? 1);
        $hide = (int)($input['hide'] ?? 0);

        if (empty($name) || empty($strategy)) throw new Exception("Name and strategy are required.");

        $stmt = $conn->prepare("INSERT INTO inv_package (name, strategy, trip, status, hide) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $name, $strategy, $trip, $status, $hide);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "Package added."]);

    } elseif ($action === 'update') {
        $id = (int)($input['id'] ?? 0);
        $name = trim($input['name'] ?? '');
        $strategy = trim($input['strategy'] ?? '');
        $trip = trim($input['trip'] ?? '');
        $status = (int)($input['status'] ?? 1);
        $hide = (int)($input['hide'] ?? 0);

        if (!$id) throw new Exception("Package ID required.");

        $stmt = $conn->prepare("UPDATE inv_package SET name=?, strategy=?, trip=?, status=?, hide=? WHERE id=?");
        $stmt->bind_param("sssiii", $name, $strategy, $trip, $status, $hide, $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "Package updated."]);

    } elseif ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) throw new Exception("Package ID required.");

        $stmt = $conn->prepare("UPDATE inv_package SET hide=1, status=0 WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "Package hidden."]);

    } elseif ($action === 'remove') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) throw new Exception("Package ID required.");

        $stmt = $conn->prepare("DELETE FROM inv_package WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "Package permanently deleted."]);

    } else {
        echo json_encode(["success" => false, "error" => "Invalid action."]);
    }

    if (isset($conn)) $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
