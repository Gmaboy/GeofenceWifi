<?php
// event_login.php  — Student scans QR / visits URL to check into a geofence event
session_start();
include '../Database/db.php';

$event_id = isset($_GET['event']) ? (int)$_GET['event'] : 0;
$message  = '';
$event    = null;

if ($event_id) {
    $r = $conn->query("SELECT * FROM geofences WHERE id=$event_id LIMIT 1");
    if ($r) $event = $r->fetch_assoc();
}

// Handle logout-from-event
if (isset($_GET['action']) && $_GET['action'] === 'logout_event' && isset($_SESSION['attendance_id'])) {
    $aid = (int)$_SESSION['attendance_id'];
    $conn->query("UPDATE event_attendance SET time_out=NOW(), status='outside' WHERE id=$aid");
    unset($_SESSION['attendance_id'], $_SESSION['event_id']);
    header("Location: event_login.php?event=$event_id&msg=loggedout");
    exit;
}

$msg_param = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $event ? htmlspecialchars($event['name']) : 'Event Access'; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root{
  --bg:#060b14; --panel:rgba(10,22,45,0.96); --border:rgba(0,200,255,0.15);
  --cyan:#00c8ff; --green:#00f5a0; --red:#ff3860; --amber:#ffb800;
  --text:#e8f4ff; --muted:#7ba3c8;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{
  font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text);
  min-height:100vh; display:flex; align-items:center; justify-content:center;
  padding:20px;
}
body::before{
  content:''; position:fixed; inset:0;
  background-image:linear-gradient(rgba(0,200,255,0.03) 1px,transparent 1px),
    linear-gradient(90deg,rgba(0,200,255,0.03) 1px,transparent 1px);
  background-size:50px 50px; pointer-events:none;
}
.card{
  background:var(--panel); border:1px solid var(--border); border-radius:24px;
  padding:36px 32px; width:100%; max-width:420px; position:relative; z-index:1;
  box-shadow:0 24px 64px rgba(0,0,0,0.6);
}
.card-top{ text-align:center; margin-bottom:28px; }
.event-icon{
  width:64px; height:64px; border-radius:16px;
  background:linear-gradient(135deg,rgba(0,200,255,0.2),rgba(0,112,243,0.2));
  border:1px solid var(--border); display:flex; align-items:center; justify-content:center;
  font-size:28px; margin:0 auto 16px;
}
h1{ font-family:'Syne',sans-serif; font-size:22px; font-weight:800; }
.subtitle{ font-size:13px; color:var(--muted); margin-top:6px; }

.info-row{
  display:flex; align-items:center; gap:10px;
  padding:10px 14px; background:rgba(0,200,255,0.05);
  border:1px solid rgba(0,200,255,0.1); border-radius:10px;
  font-size:13px; color:var(--muted); margin-bottom:10px;
}
.info-row i{ color:var(--cyan); font-size:15px; }

.check-block{
  background:rgba(6,11,20,0.8); border:1px solid var(--border); border-radius:14px;
  padding:18px; margin:20px 0;
}
.check-title{
  font-family:'Syne',sans-serif; font-size:13px; font-weight:700;
  text-transform:uppercase; letter-spacing:0.1em; color:var(--muted); margin-bottom:14px;
}
.check-item{
  display:flex; align-items:center; gap:12px; padding:8px 0;
  border-bottom:1px solid rgba(255,255,255,0.04); font-size:14px;
}
.check-item:last-child{ border-bottom:none; }
.check-icon{
  width:32px; height:32px; border-radius:8px; display:flex; align-items:center;
  justify-content:center; font-size:14px; flex-shrink:0;
}
.check-icon.pending{ background:rgba(61,96,128,0.3); color:var(--muted); }
.check-icon.ok{ background:rgba(0,245,160,0.15); color:var(--green); }
.check-icon.fail{ background:rgba(255,56,96,0.15); color:var(--red); }
.check-icon.checking{ background:rgba(255,184,0,0.12); color:var(--amber); }

.check-label{ flex:1; }
.check-val{ font-size:12px; color:var(--muted); max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

.btn-main{
  width:100%; padding:14px; border:none; border-radius:14px;
  background:linear-gradient(135deg,var(--cyan),#0070f3);
  color:#060b14; font-family:'Syne',sans-serif; font-size:15px; font-weight:800;
  cursor:pointer; transition:all 0.2s; letter-spacing:0.02em;
}
.btn-main:hover{ transform:translateY(-2px); box-shadow:0 10px 28px rgba(0,200,255,0.35); }
.btn-main:disabled{ opacity:0.4; cursor:not-allowed; transform:none; }

.btn-logout{
  width:100%; padding:12px; border:1px solid rgba(255,56,96,0.3); border-radius:14px;
  background:rgba(255,56,96,0.08); color:var(--red); font-family:'Syne',sans-serif;
  font-size:14px; font-weight:700; cursor:pointer; transition:0.2s; margin-top:10px;
}
.btn-logout:hover{ background:rgba(255,56,96,0.18); }

.alert{
  padding:12px 16px; border-radius:12px; font-size:13px; margin-bottom:16px;
  display:flex; align-items:flex-start; gap:10px;
}
.alert-success{ background:rgba(0,245,160,0.1); border:1px solid rgba(0,245,160,0.25); color:var(--green); }
.alert-error  { background:rgba(255,56,96,0.1);  border:1px solid rgba(255,56,96,0.25);  color:var(--red); }
.alert-info   { background:rgba(0,200,255,0.08); border:1px solid rgba(0,200,255,0.18); color:var(--cyan); }

.attendance-box{
  background:rgba(0,245,160,0.05); border:1px solid rgba(0,245,160,0.2);
  border-radius:14px; padding:18px; text-align:center; margin-bottom:16px;
}
.attendance-box .big-time{
  font-family:'Syne',sans-serif; font-size:28px; font-weight:800; color:var(--green);
}
.attendance-box .small-label{ font-size:12px; color:var(--muted); margin-top:4px; }

.spinner{
  width:16px; height:16px; border:2px solid rgba(255,255,255,0.2);
  border-top-color:var(--amber); border-radius:50%;
  animation:spin 0.7s linear infinite; display:inline-block;
}
@keyframes spin{ to{ transform:rotate(360deg); } }

#mapPreview{
  height:160px; border-radius:12px; margin-bottom:16px;
  border:1px solid var(--border); overflow:hidden;
}
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
</head>
<body>

<div class="card">

<?php if (!$event): ?>
  <div class="card-top">
    <div class="event-icon">⚠️</div>
    <h1>Invalid Event</h1>
    <p class="subtitle">No event ID provided or event not found.</p>
  </div>
<?php else: ?>

  <div class="card-top">
    <div class="event-icon"><i class="bi bi-geo-alt-fill" style="color:var(--cyan);"></i></div>
    <h1><?php echo htmlspecialchars($event['name']); ?></h1>
    <p class="subtitle">Geofence-Protected Event Access</p>
  </div>

  <?php if($msg_param === 'loggedout'): ?>
  <div class="alert alert-info"><i class="bi bi-box-arrow-left"></i> You have been logged out of this event.</div>
  <?php endif; ?>

  <div class="info-row"><i class="bi bi-calendar-check"></i>
    <?php echo ($event['start_date']??'—').' '.($event['start_time']??''); ?></div>
  <div class="info-row"><i class="bi bi-calendar-x"></i>
    <?php echo ($event['end_date']??'—').' '.($event['end_time']??''); ?></div>
  <?php if($event['description']): ?>
  <div class="info-row" style="align-items:flex-start;"><i class="bi bi-info-circle"></i>
    <?php echo htmlspecialchars($event['description']); ?></div>
  <?php endif; ?>

  <!-- Map preview -->
  <div id="mapPreview"></div>

  <!-- Validation checks -->
  <div class="check-block">
    <div class="check-title"><i class="bi bi-shield-check me-1"></i> Access Validation</div>

    <div class="check-item">
      <div class="check-icon checking" id="gps-icon"><span class="spinner"></span></div>
      <div class="check-label">GPS Location</div>
      <div class="check-val" id="gps-val">Detecting…</div>
    </div>

    <div class="check-item">
      <div class="check-icon checking" id="ip-icon"><span class="spinner"></span></div>
      <div class="check-label">WiFi / IP Address</div>
      <div class="check-val" id="ip-val">Checking…</div>
    </div>

    <div class="check-item">
      <div class="check-icon pending" id="fence-icon"><i class="bi bi-hexagon"></i></div>
      <div class="check-label">Inside Geofence</div>
      <div class="check-val" id="fence-val">Waiting for GPS</div>
    </div>

    <div class="check-item">
      <div class="check-icon pending" id="time-icon"><i class="bi bi-clock"></i></div>
      <div class="check-label">Event Time Window</div>
      <div class="check-val" id="time-val">Checking…</div>
    </div>
  </div>

  <!-- Already checked-in -->
  <?php
  $already_in = false;
  $attendance_row = null;
  if (isset($_SESSION['user_id'])) {
      $uid = (int)$_SESSION['user_id'];
      $gid = (int)$event['id'];
      $ar = $conn->query("SELECT * FROM event_attendance WHERE user_id=$uid AND geofence_id=$gid AND time_out IS NULL ORDER BY time_in DESC LIMIT 1");
      if ($ar && $ar->num_rows > 0) {
          $already_in = true;
          $attendance_row = $ar->fetch_assoc();
      }
  }
  ?>

  <?php if ($already_in): ?>
  <div class="attendance-box">
    <div class="big-time"><?php echo date('H:i:s', strtotime($attendance_row['time_in'])); ?></div>
    <div class="small-label">Time In — <?php echo date('M d, Y', strtotime($attendance_row['time_in'])); ?></div>
  </div>
  <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> You are checked in. You will be auto-logged out if you leave the zone.</div>
  <button class="btn-logout" onclick="logoutEvent()"><i class="bi bi-box-arrow-left me-2"></i>Leave Event</button>
  <?php else: ?>
  <button class="btn-main" id="accessBtn" disabled onclick="requestAccess()">
    <i class="bi bi-lock me-2"></i>Verifying Access…
  </button>
  <?php endif; ?>

  <div id="alertBox"></div>

  <!-- Hidden data for JS -->
  <script>
    const FENCE_COORDS  = <?php echo $event['coordinates']; ?>;
    const ALLOWED_IPS   = <?php echo $event['allowed_ips'] ?: '[]'; ?>;
    const REQUIRE_GPS   = <?php echo (int)$event['require_gps']; ?>;
    const REQUIRE_IP    = <?php echo (int)$event['require_ip']; ?>;
    const EVENT_ID      = <?php echo (int)$event['id']; ?>;
    const EVENT_START   = "<?php echo $event['start_date'].' '.$event['start_time']; ?>";
    const EVENT_END     = "<?php echo $event['end_date'].' '.$event['end_time']; ?>";
    const ALREADY_IN    = <?php echo $already_in ? 'true' : 'false'; ?>;
    const ATTENDANCE_ID = <?php echo $already_in ? (int)$attendance_row['id'] : 0; ?>;
  </script>

<?php endif; ?>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ── Map preview ── */
if (typeof FENCE_COORDS !== 'undefined' && FENCE_COORDS.length) {
    const mapEl = document.getElementById('mapPreview');
    if (mapEl) {
        const m = L.map('mapPreview', { zoomControl:false, attributionControl:false, dragging:false, scrollWheelZoom:false });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(m);
        const poly = L.polygon(FENCE_COORDS.map(c=>[c.lat,c.lng]), { color:'#00c8ff', fillColor:'#00c8ff', fillOpacity:0.15, weight:2 }).addTo(m);
        m.fitBounds(poly.getBounds(), { padding:[10,10] });
    }
}

/* ── Point-in-polygon (Ray casting) ── */
function pointInPolygon(lat, lng, coords) {
    let inside = false;
    const n = coords.length;
    for (let i=0, j=n-1; i<n; j=i++) {
        const xi=coords[i].lng, yi=coords[i].lat;
        const xj=coords[j].lng, yj=coords[j].lat;
        const inter = ((yi>lat)!==(yj>lat)) && (lng<(xj-xi)*(lat-yi)/(yj-yi)+xi);
        if (inter) inside = !inside;
    }
    return inside;
}

/* ── Check event time window ── */
function checkTimeWindow() {
    const now  = new Date();
    const start= new Date(EVENT_START.replace(' ','T'));
    const end  = new Date(EVENT_END.replace(' ','T'));
    const ok   = now >= start && now <= end;
    const icon = document.getElementById('time-icon');
    const val  = document.getElementById('time-val');
    if (ok) {
        icon.className = 'check-icon ok'; icon.innerHTML = '<i class="bi bi-check-lg"></i>';
        val.textContent = 'Within window';
    } else {
        icon.className = 'check-icon fail'; icon.innerHTML = '<i class="bi bi-x-lg"></i>';
        val.textContent = now < start ? 'Not started yet' : 'Event ended';
    }
    return ok;
}

/* ── State ── */
let gpsOk=false, ipOk=false, fenceOk=false, timeOk=false;
let userLat=null, userLng=null, userIp=null;

function updateAccessButton() {
    const btn = document.getElementById('accessBtn');
    if (!btn) return;
    const allOk = (!REQUIRE_GPS || fenceOk) && (!REQUIRE_IP || ipOk) && timeOk;
    if (allOk) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-unlock me-2"></i>Access Granted — Tap to Check In';
        btn.style.background = 'linear-gradient(135deg,#00f5a0,#00c8ff)';
    } else if (gpsOk !== null || ipOk !== null) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-lock me-2"></i>Access Denied';
        btn.style.background = 'linear-gradient(135deg,#ff3860,#c0392b)';
    }
}

/* ── GPS Check ── */
function checkGPS() {
    if (!navigator.geolocation) {
        setCheck('gps','fail','<i class="bi bi-x-lg"></i>','Not supported');
        return;
    }
    navigator.geolocation.watchPosition(pos => {
        userLat = pos.coords.latitude;
        userLng = pos.coords.longitude;
        gpsOk = true;
        setCheck('gps','ok','<i class="bi bi-check-lg"></i>', userLat.toFixed(5)+','+userLng.toFixed(5));

        // Check fence
        if (FENCE_COORDS && FENCE_COORDS.length) {
            fenceOk = pointInPolygon(userLat, userLng, FENCE_COORDS);
            if (fenceOk) {
                setCheck('fence','ok','<i class="bi bi-check-lg"></i>','Inside zone');
            } else {
                setCheck('fence','fail','<i class="bi bi-x-lg"></i>','Outside zone');
            }
        }
        updateAccessButton();

        // Auto-logout if already checked in and now outside
        if (ALREADY_IN && !fenceOk) {
            fetch('../Backend/attendance_action.php', {
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify({ action:'auto_logout', attendance_id: ATTENDANCE_ID })
            }).then(()=> {
                showAlert('error','You have left the geofence zone. Logging out…');
                setTimeout(()=> location.reload(), 2500);
            });
        }
    },
    err => {
        setCheck('gps','fail','<i class="bi bi-x-lg"></i>','Permission denied');
        setCheck('fence','fail','<i class="bi bi-x-lg"></i>','No GPS');
        updateAccessButton();
    },
    { enableHighAccuracy:true, maximumAge:5000, timeout:15000 }
    );
}

/* ── IP Check ── */
async function checkIP() {
    try {
        const res = await fetch('../Backend/check_ip.php');
        const d   = await res.json();
        userIp = d.ip;
        ipOk   = d.allowed;
        setCheck('ip', ipOk?'ok':'fail', ipOk?'<i class="bi bi-check-lg"></i>':'<i class="bi bi-x-lg"></i>', d.ip+(ipOk?' ✓':' ✗'));
    } catch(e) {
        setCheck('ip','fail','<i class="bi bi-x-lg"></i>','Check failed');
        ipOk = false;
    }
    updateAccessButton();
}

function setCheck(id, state, iconHtml, valText) {
    const icon = document.getElementById(id+'-icon');
    const val  = document.getElementById(id+'-val');
    if(icon){ icon.className='check-icon '+state; icon.innerHTML=iconHtml; }
    if(val)  val.textContent = valText;
}

/* ── Request Access (Check-in) ── */
async function requestAccess() {
    const btn = document.getElementById('accessBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Processing…';
    try {
        const res = await fetch('../Backend/attendance_action.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({
                action: 'checkin',
                geofence_id: EVENT_ID,
                lat: userLat, lng: userLng,
                ip: userIp
            })
        });
        const d = await res.json();
        if (d.success) {
            showAlert('success','✓ Checked in at '+d.time_in+'. Welcome!');
            setTimeout(()=>location.reload(), 1500);
        } else {
            showAlert('error', d.message || 'Check-in failed.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-unlock me-2"></i>Access Granted — Tap to Check In';
        }
    } catch(e) {
        showAlert('error','Server error. Try again.');
        btn.disabled = false;
    }
}

function logoutEvent() {
    window.location.href = 'event_login.php?event='+EVENT_ID+'&action=logout_event';
}

function showAlert(type, msg) {
    const box = document.getElementById('alertBox');
    if(box) box.innerHTML = `<div class="alert alert-${type}" style="margin-top:12px;"><i class="bi bi-${type==='success'?'check-circle':'exclamation-triangle'}"></i> ${msg}</div>`;
}

/* ── Init ── */
if (!ALREADY_IN) {
    timeOk = checkTimeWindow();
    checkGPS();
    checkIP();
} else {
    checkTimeWindow();
    // Still watch GPS for auto-logout
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(pos => {
            fenceOk = pointInPolygon(pos.coords.latitude, pos.coords.longitude, FENCE_COORDS);
            if (!fenceOk && ALREADY_IN) {
                fetch('../Backend/attendance_action.php',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({action:'auto_logout',attendance_id:ATTENDANCE_ID})
                }).then(()=>{
                    document.body.innerHTML='<div style="display:flex;align-items:center;justify-content:center;height:100vh;font-family:Syne,sans-serif;color:#ff3860;font-size:20px;font-weight:700;">You left the zone. Session ended.</div>';
                    setTimeout(()=>location.reload(),3000);
                });
            }
        },null,{enableHighAccuracy:true,maximumAge:5000,timeout:20000});
    }
}
</script>
</body>
</html>
