<?php
// save_geofence.php
header('Content-Type: application/json');
include '../Database/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing event name.']);
    exit;
}

$name        = $conn->real_escape_string($data['name']);
$start_date  = $conn->real_escape_string($data['startDate'] ?? '');
$start_time  = $conn->real_escape_string($data['startTime'] ?? '');
$end_date    = $conn->real_escape_string($data['endDate'] ?? '');
$end_time    = $conn->real_escape_string($data['endTime'] ?? '');
$description = $conn->real_escape_string($data['description'] ?? '');
$coordinates = $conn->real_escape_string(json_encode($data['coordinates'] ?? []));
$allowed_ips = $conn->real_escape_string(json_encode($data['allowedIps'] ?? []));
$require_gps = (int)($data['requireGps'] ?? 1);
$require_ip  = (int)($data['requireIp'] ?? 1);
$area_sqm    = floatval($data['areaSqm'] ?? 0);
$perimeter_m = floatval($data['perimeterM'] ?? 0);
$created_by  = (int)($_SESSION['user_id'] ?? 0);

session_start();

$sd = $start_date ? "'$start_date'" : "NULL";
$st = $start_time ? "'$start_time'" : "NULL";
$ed = $end_date   ? "'$end_date'"   : "NULL";
$et = $end_time   ? "'$end_time'"   : "NULL";
$cb = $created_by ?: "NULL";

$sql = "INSERT INTO geofences
        (name, start_date, start_time, end_date, end_time, description, coordinates, allowed_ips, require_gps, require_ip, area_sqm, perimeter_m, created_by, created_at)
        VALUES
        ('$name', $sd, $st, $ed, $et, '$description', '$coordinates', '$allowed_ips', $require_gps, $require_ip, $area_sqm, $perimeter_m, $cb, NOW())";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>
