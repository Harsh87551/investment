<?php
header('Content-Type: application/json');
require_once 'php_db.php';
require_once 'auth_middleware.php';

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Protect the route
$user = protect();
$userId = $user['id'];

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);
$action = isset($input['action']) ? $input['action'] : '';

if ($action === 'getHistory') {
    try {
        if (!isset($conn) || $conn === null) {
            throw new Exception("Database connection failed.");
        }

        // Join inv_investments with inv_package for strategy/reward name
        // and inv_plan for the investment amount
        $stmt = $conn->prepare("
            SELECT 
                i.id,
                i.transcation_id,
                i.paystatus,
                i.createdon,
                i.payment_details,
                i.w_status,
                i.w_requested,
                pl.amount as amount,
                pl.percentage as returns,
                pkg.strategy as strategy_name,
                pkg.name as package_name,
                pkg.trip as reward
            FROM inv_user_package i 
            LEFT JOIN inv_package pkg ON i.package_id = pkg.id
            LEFT JOIN inv_plan pl ON i.plan_id = pl.id
            WHERE i.user_id = ? AND i.hide = 0 
            ORDER BY i.id DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            // Map paystatus: 0=Processing, 1=Completed, 2=Rejected
            if ($row['paystatus'] == 1) {
                $statusLabel = 'Completed';
            } elseif ($row['paystatus'] == 2) {
                $statusLabel = 'Rejected';
            } else {
                $statusLabel = 'Processing';
            }

            $history[] = [
                "id"           => $row['id'],
                "amount"       => $row['amount'] ? (float)$row['amount'] : 0,
                "returns"      => $row['returns'] ? (float)$row['returns'] : 0,
                "strategy"     => $row['strategy_name'] ?: 'Starter',
                "package_name" => $row['package_name'] ?: '-',
                "reward"       => $row['reward'],
                "date"         => $row['createdon'] ? date('d/m/Y', strtotime($row['createdon'])) : date('d/m/Y'),
                "createdon_raw" => $row['createdon'] ? date('c', strtotime($row['createdon'])) : null,
                "w_status"     => (int)($row['w_status'] ?? 0),
                "status"       => $statusLabel,
                "txnId"        => $row['transcation_id']
            ];
        }

        echo json_encode(["success" => true, "history" => $history]);
        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid action."]);
}
?>
