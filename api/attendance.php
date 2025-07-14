
<?php
require '../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$mode = $data['mode'];

if ($mode === 'qr') {
    $staff_no = $data['code'];
} elseif ($mode === 'manual') {
    $staff_no = $data['staff_no'];
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid mode']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_no = ?");
$stmt->execute([$staff_no]);
$user = $stmt->fetch();

if ($user) {
    $log = $pdo->prepare("INSERT INTO attendance_logs (staff_id, method) VALUES (?, ?)");
    $log->execute([$user['id'], $mode]);
    echo json_encode(['status' => 'success', 'message' => 'Attendance logged for ' . $user['name']]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Staff not found']);
}
?>
