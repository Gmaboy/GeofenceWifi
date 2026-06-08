<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Allow only students
if (($_SESSION['role'] ?? '') !== 'student') {
    header("Location: dashboard.php");
    exit;
}

$fullname = $_SESSION['fullname'] ?? 'Student';
include '../Database/db.php';

if (!isset($_SESSION['user_id']))         { header("Location: index.php"); exit; }
if (($_SESSION['role'] ?? '') !== 'student') { header("Location: dashboard.php"); exit; }


$uid = (int)$_SESSION['user_id'];

// Get student info
$r    = $conn->query("SELECT UserName, course, year, section FROM usernamepass WHERE ID=$uid LIMIT 1");
$user = $r ? $r->fetch_assoc() : [];

// Get active/upcoming events
date_default_timezone_set('Asia/Manila');
$now_ts = time();
$events  = [];
$res     = $conn->query("SELECT id, name, start_date, start_time, end_date, end_time, description
                          FROM geofences
                          WHERE end_date >= CURDATE()
                          ORDER BY start_date ASC");
if ($res) while ($row = $res->fetch_assoc()) $events[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root { --bg:#060b14; --card:rgba(10,20,40,0.9); --border:rgba(0,200,255,0.15); --cyan:#00c8ff; --green:#00f5a0; --amber:#ffb800; --text:#e8f4ff; --muted:#7ba3c8; }
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; padding:24px 16px; }
body::before { content:''; position:fixed; inset:0; background-image:linear-gradient(rgba(0,200,255,0.025) 1px,transparent 1px),linear-gradient(90deg,rgba(0,200,255,0.025) 1px,transparent 1px); background-size:50px 50px; pointer-events:none; }
.topbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:32px; position:relative; z-index:1; }
.brand { font-family:'Syne',sans-serif; font-weight:800; font-size:16px; background:linear-gradient(90deg,var(--cyan),#0070f3); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
.logout { padding:8px 18px; background:rgba(255,56,96,0.1); border:1px solid rgba(255,56,96,0.3); border-radius:20px; color:#ff3860; font-size:13px; font-weight:600; text-decoration:none; transition:0.2s; }
.logout:hover { background:rgba(255,56,96,0.2); }
.welcome-card { background:var(--card); border:1px solid var(--border); border-radius:20px; padding:24px 28px; margin-bottom:28px; position:relative; z-index:1; display:flex; align-items:center; gap:18px; }
.avatar { width:56px; height:56px; border-radius:14px; background:linear-gradient(135deg,#0070f3,#00c8ff); display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:22px; flex-shrink:0; }
.welcome-name { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; }
.welcome-sub { font-size:13px; color:var(--muted); margin-top:4px; }
.badge-pill { display:inline-block; padding:3px 12px; border-radius:20px; font-size:11px; font-weight:700; margin-top:8px; background:rgba(0,200,255,0.12); color:var(--cyan); border:1px solid rgba(0,200,255,0.2); }
h2 { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; margin-bottom:16px; position:relative; z-index:1; }
.events-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px; position:relative; z-index:1; }
.ev-card { background:var(--card); border:1px solid var(--border); border-radius:18px; padding:22px; transition:all 0.3s; position:relative; overflow:hidden; }
.ev-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,var(--green),var(--cyan)); }
.ev-card:hover { transform:translateY(-4px); border-color:rgba(0,200,255,0.35); box-shadow:0 16px 40px rgba(0,0,0,0.4); }
.ev-name { font-family:'Syne',sans-serif; font-size:16px; font-weight:700; margin-bottom:10px; }
.ev-meta { font-size:13px; color:var(--muted); margin-bottom:6px; display:flex; align-items:center; gap:8px; }
.ev-meta i { color:var(--cyan); }
.ev-chip { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; margin-bottom:12px; }
.chip-active { background:rgba(0,245,160,0.15); color:var(--green); }
.chip-upcoming { background:rgba(255,184,0,0.15); color:var(--amber); }
.btn-checkin { display:block; width:100%; margin-top:14px; padding:12px; background:linear-gradient(135deg,var(--cyan),#0070f3); border:none; border-radius:12px; color:#060b14; font-family:'Syne',sans-serif; font-size:14px; font-weight:800; cursor:pointer; text-align:center; text-decoration:none; transition:all 0.2s; }
.btn-checkin:hover { transform:translateY(-2px); box-shadow:0 8px 22px rgba(0,200,255,0.35); color:#060b14; }
.empty { text-align:center; padding:48px; color:var(--muted); opacity:0.5; }
.empty i { font-size:48px; display:block; margin-bottom:12px; }
</style>
</head>
<body>

<div class="topbar">
  <span class="brand"><i class="bi bi-geo-alt-fill me-1"></i>GeoFence Portal</span>
  <a href="../Backend/logout.php" class="logout"><i class="bi bi-box-arrow-left me-1"></i>Logout</a>
</div>

<div class="welcome-card">
  <div class="avatar"><?php echo strtoupper(substr($user['UserName']??'S',0,1)); ?></div>
  <div>
    <div class="welcome-name">Welcome, <?php echo htmlspecialchars($user['UserName']??'Student'); ?>!</div>
    <div class="welcome-sub">Student Portal</div>
    <span class="badge-pill">
      <?php echo htmlspecialchars($user['course']??''); ?>
      <?php if($user['year']) echo ' · ' . ['','1st','2nd','3rd','4th'][$user['year']] . ' Year'; ?>
      <?php if($user['section']) echo ' · Sec. ' . htmlspecialchars($user['section']); ?>
    </span>
  </div>
</div>

<h2><i class="bi bi-calendar-event me-2" style="color:var(--cyan);"></i>Available Events</h2>

<?php if (empty($events)): ?>
<div class="empty"><i class="bi bi-calendar-x"></i><p>No events available right now.</p></div>
<?php else: ?>
<div class="events-grid">
<?php foreach ($events as $ev):
    $now_ts  = time();
    $start   = strtotime($ev['start_date'].' '.($ev['start_time']??'00:00'));
    $end     = strtotime($ev['end_date'].' '.($ev['end_time']??'23:59'));
    $active  = $now_ts >= $start && $now_ts <= $end;
    $upcoming= $now_ts < $start;
?>
<div class="ev-card">
  <?php if($active): ?>
    <span class="ev-chip chip-active">● ACTIVE</span>
  <?php elseif($upcoming): ?>
    <span class="ev-chip chip-upcoming">◷ UPCOMING</span>
  <?php endif; ?>
  <div class="ev-name"><?php echo htmlspecialchars($ev['name']); ?></div>
  <div class="ev-meta"><i class="bi bi-calendar-check"></i><?php echo $ev['start_date'].' '.($ev['start_time']??''); ?></div>
  <div class="ev-meta"><i class="bi bi-calendar-x"></i><?php echo $ev['end_date'].' '.($ev['end_time']??''); ?></div>
  <?php if($ev['description']): ?>
  <div class="ev-meta" style="align-items:flex-start;"><i class="bi bi-info-circle"></i><?php echo htmlspecialchars($ev['description']); ?></div>
  <?php endif; ?>
  <?php if($active): ?>
  <a href="event_login.php?event=<?php echo $ev['id']; ?>" class="btn-checkin">
    <i class="bi bi-box-arrow-in-right me-2"></i>Check In Now
  </a>
  <?php else: ?>
  <div style="margin-top:14px;padding:10px;background:rgba(255,184,0,0.08);border:1px solid rgba(255,184,0,0.2);border-radius:10px;text-align:center;font-size:13px;color:var(--amber);">
    <i class="bi bi-clock me-1"></i>Opens <?php echo date('M d, g:i A', $start); ?>
  </div>
  <?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

</body>
</html>