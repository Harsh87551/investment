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

// Protect the route - only logged-in users can submit KYC
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

    // Get action
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);
    $action = isset($input['action']) ? $input['action'] : (isset($_POST['action']) ? $_POST['action'] : 'submitKYC');

    if ($action === 'getStatus') {
        $stmt = $conn->prepare("SELECT status FROM inv_kyc WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(["success" => true, "status" => (int)$row['status']]);
        } else {
            echo json_encode(["success" => true, "status" => 0]); // Default to pending (0)
        }
        $stmt->close();
        exit;
    }

    // Default action: submitKYC
    // Get basic text data
    $aadharNumber = isset($_POST['aadharNumber']) ? trim($_POST['aadharNumber']) : '';
    $panNumber = isset($_POST['panNumber']) ? trim($_POST['panNumber']) : '';

    if (empty($aadharNumber) || empty($panNumber)) {
        throw new Exception("Aadhaar and PAN numbers are required.");
    }

    // Directory to store uploads
    $uploadDir = 'uploads/kyc/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Helper to upload files
    function uploadFile($fileKey, $prefix, $userId, $uploadDir) {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error uploading file: " . $fileKey);
        }

        $fileTmpPath = $_FILES[$fileKey]['tmp_name'];
        $fileName = $_FILES[$fileKey]['name'];
        $fileSize = $_FILES[$fileKey]['size'];
        $fileType = $_FILES[$fileKey]['type'];
        
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Invalid file extension: " . $fileExtension);
        }

        // Create a unique filename based on userId
        $newFileName = $prefix . "_" . $userId . "_" . time() . "." . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            return $newFileName;
        } else {
            throw new Exception("Failed to move uploaded file to destination: " . $fileKey);
        }
    }

    // Process uploads
    $aadharPic = uploadFile('aadharPic', 'aadhar', $userId, $uploadDir);
    $panPic = uploadFile('panPic', 'pan', $userId, $uploadDir);
    $profilePic = uploadFile('profilePic', 'profile', $userId, $uploadDir);

    // Initial status and hide values
    // status = 1 (Processing) as the user just submitted documents
    $status = 1; 
    $hide = 0;

    // Check if a KYC record already exists for this ID
    // Note: Assuming 'id' in inv_kyc is the userId as no user_id column exists
    $stmt = $conn->prepare("SELECT id FROM inv_kyc WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // Update existing record
        $stmt->close();
        $stmt = $conn->prepare("UPDATE inv_kyc SET aadhar_number = ?, aadharpic = ?, pancard_number = ?, pancardpic = ?, pic = ?, status = ?, hide = ? WHERE id = ?");
        // sssssiii: aadhar_number(s), aadharpic(s), pancard_number(s), pancardpic(s), pic(s), status(i), hide(i), id(i)
        $stmt->bind_param("sssssiii", $aadharNumber, $aadharPic, $panNumber, $panPic, $profilePic, $status, $hide, $userId);
    } else {
        // Insert new record
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO inv_kyc (id, aadhar_number, aadharpic, pancard_number, pancardpic, pic, status, hide) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        // isssssii: id(i), aadhar_number(s), aadharpic(s), pancard_number(s), pancardpic(s), pic(s), status(i), hide(i)
        $stmt->bind_param("isssssii", $userId, $aadharNumber, $aadharPic, $panNumber, $panPic, $profilePic, $status, $hide);
    }

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "KYC data submitted successfully.",
            "profilePic" => $uploadDir . $profilePic
        ]);
    } else {
        throw new Exception("Error saving KYC record to database.");
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
