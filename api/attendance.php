<?php
header("Content-Type: application/json");
require_once "../db.php";

$data = json_decode(file_get_contents("php://input"), true);

$mode = $data['mode'] ?? null;
$action = $data['action'] ?? 'checkin';
$staff_no = $data['staff_no'] ?? ($data['code'] ?? null);

$response = ["status" => "error", "message" => "Invalid request."];

if (!$mode || !$staff_no || !in_array($action, ['checkin', 'checkout'])) {
    echo json_encode($response);
    exit;
}

try {
    // ✅ Step 1: Ensure staff exists
    $check = $pdo->prepare("SELECT 1 FROM staff WHERE id = ? OR staff_no = ?");
    $check->execute([$staff_no, $staff_no]);

    if (!$check->fetch()) {
        echo json_encode([
            "status" => "error",
            "message" => "Staff number '{$staff_no}' not found in the system."
        ]);
        exit;
    }

    // ✅ Step 2: Insert into attendance_logs
    $stmt = $pdo->prepare("
        INSERT INTO attendance_logs (staff_no, method, action, timestamp)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$staff_no, $mode, $action]);

    echo json_encode([
        "status" => "success",
        "message" => ucfirst($action) . " recorded successfully for staff no: $staff_no"
    ]);

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        // Handle foreign key constraint error specifically
        echo json_encode([
            "status" => "error",
            "message" => "Staff number not recognized. Ensure the staff exists in the system before recording attendance."
        ]);
    } else {
        // Generic error fallback
        echo json_encode([
            "status" => "error",
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
}
