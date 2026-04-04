<?php
header('Content-Type: application/json');
require_once 'php_db.php';
require_once 'jwt_utils.php';
require_once 'auth_middleware.php';

// Allow CORS if needed
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Check for database connection error from php_db.php
    if (!isset($conn) || $conn === null) {
        throw new Exception("Database connection failed: " . (isset($connectionError) ? $connectionError : "Unknown error"));
    }

    // Get the action
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    // Parse JSON payload
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);

    if ($action === 'signup') {
        $name = isset($input['name']) ? trim($input['name']) : '';
        $email = isset($input['email']) ? trim($input['email']) : '';
        $password = isset($input['password']) ? trim($input['password']) : '';
        $phone = isset($input['phone']) ? trim($input['phone']) : '';

        if (empty($email) || empty($password)) {
            echo json_encode(["message" => "Email and password are required"]);
            exit;
        }

        $stmt = $conn->prepare("SELECT id FROM inv_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode(["message" => "inv_User already exists"]);
            $stmt->close();
            exit;
        }
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO inv_users (name, email, password, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $phone);
        if ($stmt->execute()) {
            $newUserId = $conn->insert_id;
            $jwtSecret = getenv('JWT_SECRET') ?: 'mysecretkey';
            $token = generateToken(['id' => $newUserId], $jwtSecret);

            echo json_encode([
                "message" => "Signup successful",
                "token" => $token,
                "user" => [
                    "name" => $name,
                    "email" => $email,
                    "phone" => $phone
                ]
            ]);
        } else {
            throw new Exception("Error creating inv_user");
        }
        $stmt->close();

    } elseif ($action === 'login') {
        $email = isset($input['email']) ? trim($input['email']) : '';
        $password = isset($input['password']) ? trim($input['password']) : '';

        $stmt = $conn->prepare("SELECT id, name, email, phone, password FROM inv_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(["message" => "User not found"]);
            $stmt->close();
            exit;
        }

        $inv_user = $result->fetch_assoc();
        // Comparing plain text passwords to match Node codebase
        $match = ($password === $inv_user['password']);

        if (!$match) {
            echo json_encode(["message" => "Invalid password"]);
            $stmt->close();
            exit;
        }

        $jwtSecret = getenv('JWT_SECRET') ?: 'mysecretkey';
        $token = generateToken(['id' => $inv_user['id']], $jwtSecret);

        echo json_encode([
            "message" => "Login successful",
            "token" => $token,
            "user" => [
                "name" => $inv_user['name'],
                "email" => $inv_user['email'],
                "phone" => $inv_user['phone'],
                "bankname" => $inv_user['bankname'] ?? null,
                "account" => $inv_user['account'] ?? null,
                "ifsc_code" => $inv_user['ifsc_code'] ?? null,
                "is_admin" => ((int)$inv_user['id'] == 1)
            ]
        ]);
        $stmt->close();

    } elseif ($action === 'profile') {
        // Fetch user profile using JWT token
        $decoded = protect();
        $userId = $decoded['id'];

        $stmt = $conn->prepare("SELECT name, email, phone, bankname, account, ifsc_code FROM inv_users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(["success" => false, "message" => "User not found"]);
            $stmt->close();
            exit;
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Fetch profile pic from inv_kyc table
        $profilePic = null;
        $stmtKyc = $conn->prepare("SELECT pic, status FROM inv_kyc WHERE id = ?");
        $stmtKyc->bind_param("i", $userId);
        $stmtKyc->execute();
        $kycResult = $stmtKyc->get_result();
        if ($kycRow = $kycResult->fetch_assoc()) {
            if (!empty($kycRow['pic'])) {
                $profilePic = 'uploads/kyc/' . $kycRow['pic'];
            }
        }
        $stmtKyc->close();

        echo json_encode([
            "success" => true,
            "user" => [
                "name" => $user['name'],
                "email" => $user['email'],
                "phone" => $user['phone'],
                "bankname" => $user['bankname'] ?? null,
                "account" => $user['account'] ?? null,
                "ifsc_code" => $user['ifsc_code'] ?? null,
                "is_admin" => ((int)$userId == 1),
                "profile_pic" => $profilePic
            ]
        ]);


    } else {
        http_response_code(404);
        echo json_encode(["message" => "Action not found"]);
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
