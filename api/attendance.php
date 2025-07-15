<?php
header("Content-Type: application/json");

// Simulate database connection
require_once "../db.php"; // adjust if needed

$data = json_decode(file_get_contents("php://input"), true);

$mode = $data['mode'] ?? null;
$action = $data['action'] ?? 'checkin';
$staff_no = $data['staff_no'] ?? ($data['code'] ?? null);

$response = ["status" => "error", "message" => "Invalid request."];

if (!$mode || !$staff_no || !in_array($action, ['checkin', 'checkout'])) {
    echo json_encode($response);
    exit;
}

// Insert into attendance table
try {
    $stmt = $pdo->prepare("INSERT INTO attendance (staff_no, method, action, timestamp) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$staff_no, $mode, $action]);

    $response = [
        "status" => "success",
        "message" => ucfirst($action) . " recorded successfully for staff no: $staff_no"
    ];
} catch (Exception $e) {
    $response = [
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ];
}

echo json_encode($response);
