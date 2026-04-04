<?php
header('Content-Type: application/json');
require_once 'php_db.php';

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => true, "message" => "Method not allowed. Use POST."]);
    exit;
}

try {
    if (!isset($conn) || $conn === null) {
        throw new Exception("Database connection failed: " . (isset($connectionError) ? $connectionError : "Unknown error"));
    }

    // Parse JSON body
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);

    $action = isset($input['action']) ? $input['action'] : '';

    if ($action === 'getPackages') {
        // Fetch all active, non-hidden packages
        $stmt = $conn->prepare("SELECT id, strategy, trip, name FROM inv_package WHERE status = 1 AND hide = 0 ORDER BY id ASC");
        $stmt->execute();
        $result = $stmt->get_result();

        $packages = [];
        while ($row = $result->fetch_assoc()) {
            $packages[] = [
                'id' => (int)$row['id'],
                'strategy' => ucfirst($row['strategy']),
                'trip' => $row['trip'] ? (strpos($row['trip'], 'Dubai') !== false ? '✈️ Luxury ' . $row['trip'] . ' Trip' : '🎁 Free ' . ucfirst($row['trip']) . ' Trip') : null,
                'name' => $row['name'],
                // These might eventually come from DB too, but for now we'll match the UI logic
                'key' => str_replace(' ', '', strtolower($row['name'])) // 'monthly', '6month', etc.
            ];
        }
        $stmt->close();

        echo json_encode([
            "success" => true,
            "packages" => $packages
        ]);

    } else {
        http_response_code(400);
        echo json_encode(["error" => true, "message" => "Invalid action. Use 'getPackages'."]);
    }

    if (isset($conn) && $conn) {
        $conn->close();
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "message" => "A server error occurred: " . $e->getMessage()
    ]);
}
?>
