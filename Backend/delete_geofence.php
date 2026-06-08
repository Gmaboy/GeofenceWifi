<?php
// delete_geofence.php
header('Content-Type: application/json');
include '../Database/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID.']);
    exit;
}

$id = (int)$data['id'];

// Delete attendance records first (FK safety)
$conn->query("DELETE FROM event_attendance WHERE geofence_id = $id");

// Delete geofence
if ($conn->query("DELETE FROM geofences WHERE id = $id")) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>
