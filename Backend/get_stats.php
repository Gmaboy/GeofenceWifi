<?php
// get_stats.php
header('Content-Type: application/json');
include '../Database/db.php';

$stats = [];

// User count (admins)
$r = $conn->query("SELECT COUNT(*) as cnt FROM usernamepass WHERE role='admin' OR role IS NULL OR role=''");
$stats['user_count'] = $r ? (int)$r->fetch_assoc()['cnt'] : 0;

// Student count
$r2 = $conn->query("SELECT COUNT(*) as cnt FROM usernamepass WHERE role='student'");
$stats['student_count'] = $r2 ? (int)$r2->fetch_assoc()['cnt'] : 0;

// Total geofences
$r3 = $conn->query("SELECT COUNT(*) as cnt FROM geofences");
$stats['zone_count'] = $r3 ? (int)$r3->fetch_assoc()['cnt'] : 0;

// Total check-ins all time
$r4 = $conn->query("SELECT COUNT(*) as cnt FROM event_attendance");
$stats['total_checkins'] = $r4 ? (int)$r4->fetch_assoc()['cnt'] : 0;

// Currently inside (no time_out)
$r5 = $conn->query("SELECT COUNT(*) as cnt FROM event_attendance WHERE time_out IS NULL AND status='inside'");
$stats['active_inside'] = $r5 ? (int)$r5->fetch_assoc()['cnt'] : 0;

echo json_encode($stats);
$conn->close();
?>
