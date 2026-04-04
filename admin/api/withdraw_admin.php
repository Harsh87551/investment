<?php
header('Content-Type: application/json');
require_once '../../backend/php_db.php';
require_once '../admin_auth.php';

// Protect the route - only for admin
$admin = protectAdmin();

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);
$action = isset($input['action']) ? $input['action'] : '';

try {
    if (!isset($conn) || $conn === null) {
        throw new Exception("Database connection failed.");
    }

    if ($action === 'list') {
        // Fetch all withdrawal requests (w_status > 0)
        $stmt = $conn->prepare("
            SELECT 
                i.id,
                i.user_id,
                i.w_status,
                i.w_requested,
                i.w_method,
                i.createdon,
                i.w_bankname,
                i.w_account,
                i.w_ifsc_code,
                pl.amount,
                pkg.name as package_name,
                u.name as user_name,
                u.email as user_email
            FROM inv_user_package i
            LEFT JOIN inv_users u ON i.user_id = u.id
            LEFT JOIN inv_package pkg ON i.package_id = pkg.id
            LEFT JOIN inv_plan pl ON i.plan_id = pl.id
            WHERE i.w_status > 0
            ORDER BY i.w_requested DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $withdrawals = [];
        while ($row = $result->fetch_assoc()) {
            $withdrawals[] = $row;
        }

        echo json_encode(["success" => true, "withdrawals" => $withdrawals]);
        $stmt->close();

    } elseif ($action === 'update') {
        $id = (int)($input['id'] ?? 0);
        $status = (int)($input['status'] ?? 0); // 1=requested, 2=approved, 3=rejected

        if (!$id) {
            throw new Exception("Missing request ID.");
        }

        $stmt = $conn->prepare("UPDATE inv_user_package SET w_status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Withdrawal status updated."]);
        } else {
            throw new Exception("Failed to update status.");
        }
        $stmt->close();

    } elseif ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) throw new Exception("Missing ID.");

        // Reset withdrawal fields instead of deleting row
        $stmt = $conn->prepare("UPDATE inv_user_package SET w_status = 0, w_requested = NULL, w_method = NULL, w_createdon = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Withdrawal request removed."]);
        } else {
            throw new Exception("Database error.");
        }
        $stmt->close();

    } else {
        echo json_encode(["success" => false, "message" => "Invalid action."]);
    }

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
