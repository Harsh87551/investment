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
    echo json_encode(["error" => true, "message" => "Method not allowed. Use POST."]);
    exit;
}

try {
    if (!isset($conn) || $conn === null) {
        throw new Exception("Database connection failed.");
    }

    // --- Values from form post ---
    $amount       = isset($_POST['amount'])     ? (float)$_POST['amount']      : 0;
    $planId       = isset($_POST['plan_id'])    ? (int)$_POST['plan_id']       : null;
    $packageId    = isset($_POST['package_id']) ? (int)$_POST['package_id']    : null;
    $txnId        = isset($_POST['txnId'])      ? trim($_POST['txnId'])        : '';
    $strategy     = isset($_POST['strategy'])   ? trim($_POST['strategy'])     : '';
    $reward       = isset($_POST['reward'])     ? trim($_POST['reward'])       : '';

    if (empty($txnId)) {
        throw new Exception("Transaction ID is required.");
    }

    if (!isset($_FILES['screenshot']) || $_FILES['screenshot']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Payment screenshot is required.");
    }

    // --- Handle screenshot upload ---
    $uploadDir = 'uploads/payments/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName      = $_FILES['screenshot']['name'];
    $fileTmpPath   = $_FILES['screenshot']['tmp_name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Invalid file type. Only JPG/PNG allowed.");
    }

    $newFileName = "deposit_u{$userId}_" . time() . ".{$fileExtension}";
    $destPath    = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        throw new Exception("Failed to save the uploaded screenshot.");
    }

    // --- Map to the actual table schema from the screenshot ---
    // Columns: id, status, hide, plan_id, package_id, user_id, paymode, payment_details, screenshot, transcation_id, paystatus, w_status, w_createdon, w_requested, w_method, createdon
    $paymode        = 'upi';
    $paymentDetails = $strategy . ($reward ? ' | Reward: ' . $reward : '');
    $paystatus      = 0; // Pending admin approval
    $status         = 1; // Active
    $hide           = 0;

    $stmt = $conn->prepare(
        "INSERT INTO inv_user_package 
         (status, hide, plan_id, package_id, user_id, paymode, payment_details, screenshot, transcation_id, paystatus, createdon) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
    );
    $stmt->bind_param("iiiiissssi",
        $status,
        $hide,
        $planId,
        $packageId,
        $userId,
        $paymode,
        $paymentDetails,
        $newFileName,
        $txnId,
        $paystatus
    );

    if ($stmt->execute()) {
        echo json_encode([
            "success"    => true,
            "message"    => "Payment proof submitted. Awaiting admin approval.",
            "deposit_id" => $stmt->insert_id
        ]);
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error"   => true,
        "message" => $e->getMessage()
    ]);
}
?>
