<?php
// attendance_action.php
session_start();
header('Content-Type: application/json');
include '../Database/db.php';

$data   = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'message'=>'Not logged in.']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/* ── CHECK-IN ── */
if ($action === 'checkin') {
    $gf_id = (int)($data['geofence_id'] ?? 0);
    $lat   = floatval($data['lat'] ?? 0);
    $lng   = floatval($data['lng'] ?? 0);
    $ip    = $conn->real_escape_string($data['ip'] ?? $_SERVER['REMOTE_ADDR']);

    if (!$gf_id) { echo json_encode(['success'=>false,'message'=>'Invalid event.']); exit; }

    // Check if already checked in (no timeout)
    $check = $conn->query("SELECT id FROM event_attendance WHERE user_id=$user_id AND geofence_id=$gf_id AND time_out IS NULL LIMIT 1");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $_SESSION['attendance_id'] = $row['id'];
        $_SESSION['event_id']      = $gf_id;
        echo json_encode(['success'=>true,'message'=>'Already checked in.','time_in'=>date('H:i:s')]);
        exit;
    }

    // Insert attendance
    $sql = "INSERT INTO event_attendance
        (geofence_id, user_id, time_in, ip_address, gps_lat, gps_lng, access_method, status)
        VALUES ($gf_id, $user_id, NOW(), '$ip', $lat, $lng, 'gps+ip', 'inside')";

    if ($conn->query($sql)) {
        $aid = $conn->insert_id;
        $_SESSION['attendance_id'] = $aid;
        $_SESSION['event_id']      = $gf_id;
        echo json_encode(['success'=>true,'attendance_id'=>$aid,'time_in'=>date('H:i:s')]);
    } else {
        echo json_encode(['success'=>false,'message'=>$conn->error]);
    }
    exit;
}

/* ── AUTO LOGOUT (GPS triggered) ── */
if ($action === 'auto_logout') {
    $aid = (int)($data['attendance_id'] ?? $_SESSION['attendance_id'] ?? 0);
    if ($aid) {
        $conn->query("UPDATE event_attendance SET time_out=NOW(), status='outside' WHERE id=$aid AND time_out IS NULL");
        unset($_SESSION['attendance_id'], $_SESSION['event_id']);
    }
    echo json_encode(['success'=>true]);
    exit;
}

/* ── MANUAL LOGOUT ── */
if ($action === 'manual_logout') {
    $aid = (int)($data['attendance_id'] ?? $_SESSION['attendance_id'] ?? 0);
    if ($aid) {
        $conn->query("UPDATE event_attendance SET time_out=NOW(), status='outside' WHERE id=$aid AND time_out IS NULL");
        unset($_SESSION['attendance_id'], $_SESSION['event_id']);
    }
    echo json_encode(['success'=>true]);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Unknown action.']);
$conn->close();
?>
