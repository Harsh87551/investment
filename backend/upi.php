<?php
header('Content-Type: application/json');
require_once 'php_db.php';

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    if (!isset($conn) || $conn === null) {
        throw new Exception("Database connection failed.");
    }

    // Fetch the active UPI ID
    $stmt = $conn->prepare("SELECT upi_id FROM inv_upi WHERE status = 1 AND hide = 0 ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            "success" => true,
            "upi_id" => $row['upi_id']
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No active UPI ID found."
        ]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
?>
