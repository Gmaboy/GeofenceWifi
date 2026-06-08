<?php
// update_geofence.php
header('Content-Type: application/json');
include '../Database/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID.']);
    exit;
}

$id          = (int)$data['id'];
$name        = $conn->real_escape_string($data['name'] ?? '');
$start_date  = $conn->real_escape_string($data['startDate'] ?? '');
$start_time  = $conn->real_escape_string($data['startTime'] ?? '');
$end_date    = $conn->real_escape_string($data['endDate'] ?? '');
$end_time    = $conn->real_escape_string($data['endTime'] ?? '');
$description = $conn->real_escape_string($data['description'] ?? '');
$allowed_ips = $conn->real_escape_string(json_encode($data['allowedIps'] ?? []));

$sd = $start_date ? "'$start_date'" : "NULL";
$st = $start_time ? "'$start_time'" : "NULL";
$ed = $end_date   ? "'$end_date'"   : "NULL";
$et = $end_time   ? "'$end_time'"   : "NULL";

$sql = "UPDATE geofences SET
            name        = '$name',
            start_date  = $sd,
            start_time  = $st,
            end_date    = $ed,
            end_time    = $et,
            description = '$description',
            allowed_ips = '$allowed_ips'
        WHERE id = $id";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>
