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

try {
    if (!isset($conn) || $conn === null) {
        throw new Exception("Database connection failed.");
    }

    // 1. Total Users
    $resUsers = $conn->query("SELECT COUNT(*) as count FROM inv_users");
    $totalUsers = $resUsers ? $resUsers->fetch_assoc()['count'] : 0;

    // 2. Total KYC Pending (status=1)
    $resKyc = $conn->query("SELECT COUNT(*) as count FROM inv_kyc WHERE status = 1");
    $pendingKyc = $resKyc ? $resKyc->fetch_assoc()['count'] : 0;

    // 3. Total Deposits Volume (Active paystatus=1)
    $resDeposits = $conn->query("SELECT SUM(pl.amount) as total FROM inv_user_package i JOIN inv_plan pl ON i.plan_id = pl.id WHERE i.paystatus = 1");
    $totalVolume = 0;
    if ($resDeposits) {
        $row = $resDeposits->fetch_assoc();
        $totalVolume = $row['total'] ? (float)$row['total'] : 0;
    }

    // 4. Recently Registered Users
    $resRecent = $conn->query("SELECT id, name, email, phone, createdon FROM inv_users WHERE id != 1 ORDER BY id DESC LIMIT 10");
    $recentUsers = [];
    if ($resRecent) {
        while ($row = $resRecent->fetch_assoc()) {
            $recentUsers[] = $row;
        }
    }

    echo json_encode([
        "success" => true,
        "stats" => [
            "totalUsers" => (int)$totalUsers,
            "pendingKyc" => (int)$pendingKyc,
            "totalVolume" => $totalVolume
        ],
        "users" => $recentUsers
    ]);

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
