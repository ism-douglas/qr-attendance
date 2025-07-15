<?php
header("Content-Type: application/json");
require_once "../db.php"; // Adjust path if needed

$data = json_decode(file_get_contents("php://input"), true);

$mode = $data['mode'] ?? null;
$action = $data['action'] ?? 'checkin';
$staff_no = $data['staff_no'] ?? ($data['code'] ?? null);

if (!$mode || !$staff_no || !in_array($action, ['checkin', 'checkout'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request. Required data missing."
    ]);
    exit;
}

try {
    // ✅ Step 1: Check if staff exists
    $check = $pdo->prepare("SELECT 1 FROM staff WHERE staff_no = ?");
    $check->execute([$staff_no]);

    if (!$check->fetch()) {
        // ❌ Log failed attempt
        $log = $pdo->prepare("
            INSERT INTO failed_attendance_logs (staff_no, method, action, reason)
            VALUES (?, ?, ?, ?)
        ");
        $log->execute([$staff_no, $mode, $action, "Staff number not found in the system"]);

        echo json_encode([
            "status" => "error",
            "message" => "Staff number '{$staff_no}' not found in the system."
        ]);
        exit;
    }

    // ✅ Step 2: Insert into attendance logs
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
    // ❌ Log any DB error
    $log = $pdo->prepare("
        INSERT INTO failed_attendance_logs (staff_no, method, action, reason)
        VALUES (?, ?, ?, ?)
    ");
    $log->execute([$staff_no, $mode ?? 'unknown', $action ?? 'unknown', $e->getMessage()]);

    if ($e->getCode() === '23000') {
        echo json_encode([
            "status" => "error",
            "message" => "Staff number exists, but the attendance log failed due to a database constraint."
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
}
