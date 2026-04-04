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

try {
    if (!isset($conn) || $conn === null) {
        throw new Exception("Database connection failed.");
    }

    if (!isset($_FILES['profilePic']) || $_FILES['profilePic']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Profile picture is required.");
    }

    // Handle file upload
    $uploadDir = 'uploads/kyc/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileTmpPath = $_FILES['profilePic']['tmp_name'];
    $fileName = $_FILES['profilePic']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Invalid file type. Only JPG/PNG allowed.");
    }

    $newFileName = "profile_" . $userId . "_" . time() . "." . $fileExtension;
    $destPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        throw new Exception("Failed to save the uploaded picture.");
    }

    // Check if KYC record exists for this user
    $stmt = $conn->prepare("SELECT id FROM inv_kyc WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update existing record
        $stmt->close();
        $stmt = $conn->prepare("UPDATE inv_kyc SET pic = ? WHERE id = ?");
        $stmt->bind_param("si", $newFileName, $userId);
    } else {
        // Insert new record with just the pic
        $stmt->close();
        $status = 0;
        $hide = 0;
        $empty = '';
        $stmt = $conn->prepare("INSERT INTO inv_kyc (id, pic, status, hide, aadhar_number, aadharpic, pancard_number, pancardpic) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isiissss", $userId, $newFileName, $status, $hide, $empty, $empty, $empty, $empty);
    }

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Profile picture updated.",
            "profile_pic" => "uploads/kyc/" . $newFileName
        ]);
    } else {
        throw new Exception("Failed to update profile picture.");
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
