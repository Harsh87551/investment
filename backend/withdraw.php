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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => true, "message" => "Method not allowed."]);
    exit;
}

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);
$action = isset($input['action']) ? $input['action'] : '';

try {
    if (!isset($conn) || $conn === null) {
        throw new Exception("Database connection failed.");
    }

    if ($action === 'request') {
        $depositId = (int)($input['deposit_id'] ?? 0);
        $method = isset($input['method']) ? trim($input['method']) : 'bank';

        if (!$depositId) {
            throw new Exception("Missing deposit ID.");
        }

        // Verify this deposit belongs to the user and is approved (paystatus=1)
        $stmt = $conn->prepare("SELECT i.id, i.paystatus, i.createdon, i.w_status, pkg.name as package_name 
                                FROM inv_user_package i 
                                LEFT JOIN inv_package pkg ON i.package_id = pkg.id 
                                WHERE i.id = ? AND i.user_id = ?");
        $stmt->bind_param("ii", $depositId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Deposit not found or not yours.");
        }

        $deposit = $result->fetch_assoc();
        $stmt->close();

        // Check if already approved
        if ($deposit['paystatus'] != 1) {
            throw new Exception("This deposit is not active/approved yet.");
        }

        // Check if withdrawal already requested
        if ($deposit['w_status'] > 0) {
            throw new Exception("Withdrawal already requested for this deposit.");
        }

        // Verify time period has elapsed
        $pkgName = strtolower($deposit['package_name'] ?? 'monthly');
        $months = 1;
        if (strpos($pkgName, '12') !== false) $months = 12;
        elseif (strpos($pkgName, '9') !== false) $months = 9;
        elseif (strpos($pkgName, '6') !== false) $months = 6;

        $createdDate = new DateTime($deposit['createdon']);
        $maturityDate = clone $createdDate;
        $maturityDate->modify("+{$months} months");
        $now = new DateTime();

        if ($now < $maturityDate) {
            $remaining = $now->diff($maturityDate);
            throw new Exception("Package matures on " . $maturityDate->format('d/m/Y') . ". " . $remaining->days . " days remaining.");
        }

        // All checks passed — submit withdrawal request
        $now_str = date('Y-m-d H:i:s');
        $w_status = 1; // 1 = requested
        
        $bankName = isset($input['bankname']) ? trim($input['bankname']) : '';
        $account = isset($input['account']) ? trim($input['account']) : '';
        $ifsc = isset($input['ifsc']) ? trim($input['ifsc']) : '';
        $saveToProfile = isset($input['save_to_profile']) ? (bool)$input['save_to_profile'] : false;

        if (empty($bankName) || empty($account) || empty($ifsc)) {
            throw new Exception("Please provide all bank details (Bank Name, Account, IFSC).");
        }

        // 1. Update investment record with withdrawal request
        $stmt = $conn->prepare("UPDATE inv_user_package SET w_status = ?, w_requested = 1, w_method = ?, w_createdon = ?, w_bankname = ?, w_account = ?, w_ifsc_code = ? WHERE id = ?");
        $stmt->bind_param("isssssi", $w_status, $method, $now_str, $bankName, $account, $ifsc, $depositId);

        if ($stmt->execute()) {
            // 2. If requested, save these details to the user profile
            if ($saveToProfile) {
                $upd = $conn->prepare("UPDATE inv_users SET bankname = ?, account = ?, ifsc_code = ? WHERE id = ?");
                $upd->bind_param("sssi", $bankName, $account, $ifsc, $userId);
                $upd->execute();
                $upd->close();
            }

            echo json_encode([
                "success" => true,
                "message" => "Withdrawal request submitted successfully."
            ]);
        } else {
            throw new Exception("Failed to submit withdrawal request.");
        }
        $stmt->close();

    } else {
        echo json_encode(["success" => false, "message" => "Invalid action."]);
    }

    if (isset($conn)) $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>
