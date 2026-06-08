<?php
// get_geofences.php
header('Content-Type: application/json');
include '../Database/db.php';

$sql = "SELECT id, name, start_date, start_time, end_date, end_time,
               description, coordinates, allowed_ips, require_gps, require_ip,
               area_sqm, perimeter_m, created_at
        FROM geofences
        ORDER BY created_at DESC";

$result = $conn->query($sql);
$rows = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Decode JSON fields
        $row['coordinates'] = json_decode($row['coordinates'] ?? '[]', true);
        $row['allowed_ips'] = json_decode($row['allowed_ips'] ?? '[]', true);
        $rows[] = $row;
    }
}

echo json_encode($rows);
$conn->close();
?>
