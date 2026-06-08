<?php
// get_attendance.php
header('Content-Type: application/json');
include '../Database/db.php';

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

$where = $event_id ? "WHERE a.geofence_id = $event_id" : "";

$sql = "SELECT
            a.id,
            a.geofence_id,
            a.user_id,
            a.time_in,
            a.time_out,
            a.ip_address,
            a.gps_lat,
            a.gps_lng,
            a.access_method,
            a.status,
            u.UserName   AS username,
            u.Email      AS email,
            u.course,
            u.year,
            u.section,
            g.name       AS event_name
        FROM event_attendance a
        LEFT JOIN usernamepass u ON u.ID = a.user_id
        LEFT JOIN geofences    g ON g.id = a.geofence_id
        $where
        ORDER BY a.time_in DESC
        LIMIT 500";

$result = $conn->query($sql);
$rows = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

echo json_encode($rows);
$conn->close();
?>
