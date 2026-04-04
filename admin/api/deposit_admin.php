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
        // Join with inv_plan to get amount + inv_package to get package name
        $stmt = $conn->prepare("
            SELECT i.id, i.user_id, i.transcation_id, i.screenshot, i.paystatus, i.createdon,
                   i.payment_details,
                   pl.amount as amount,
                   pkg.name as package_name,
                   pkg.trip as trip
            FROM inv_user_package i 
            LEFT JOIN inv_plan pl ON i.plan_id = pl.id
            LEFT JOIN inv_package pkg ON i.package_id = pkg.id
            ORDER BY i.paystatus ASC, i.id DESC 
            LIMIT 50
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $deposits = [];
        while ($row = $result->fetch_assoc()) {
            $deposits[] = $row;
        }
        $stmt->close();
        echo json_encode(["success" => true, "deposits" => $deposits]);

    } elseif ($action === 'update') {
        $id = (int)($input['id'] ?? 0);
        $status = (int)($input['status'] ?? -1);

        if (!$id || $status < 0) {
            throw new Exception("Missing id or status.");
        }

        $stmt = $conn->prepare("UPDATE inv_user_package SET paystatus = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode(["success" => true, "message" => "Deposit status updated."]);
        } else {
            throw new Exception("Database update failed: " . $stmt->error);
        }

    } elseif ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) throw new Exception("Missing deposit ID.");
        
        $stmt = $conn->prepare("DELETE FROM inv_user_package WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode(["success" => true, "message" => "Deposit record deleted."]);
        } else {
            throw new Exception("Failed to delete deposit.");
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
