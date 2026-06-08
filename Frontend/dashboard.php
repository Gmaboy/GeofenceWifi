<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$fullname = $_SESSION['fullname'] ?? 'Admin';
include '../Database/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>GeoFence System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css"/>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#060b14;--bg-deep:#0a1628;--bg-card:rgba(10,20,40,0.85);
  --border:rgba(0,200,255,0.12);--border-glow:rgba(0,200,255,0.35);
  --cyan:#00c8ff;--blue:#0070f3;--purple:#7c3aed;
  --green:#00f5a0;--red:#ff3860;--amber:#ffb800;--pink:#f472b6;
  --text:#e8f4ff;--muted:#7ba3c8;--faint:#3d6080;
  --sw:230px;--slim:68px;--nh:60px;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);overflow-x:hidden;min-height:100vh;}
body::before{content:'';position:fixed;inset:0;background-image:linear-gradient(rgba(0,200,255,0.025) 1px,transparent 1px),linear-gradient(90deg,rgba(0,200,255,0.025) 1px,transparent 1px);background-size:56px 56px;pointer-events:none;z-index:0;}
.topbar{position:fixed;top:0;left:0;right:0;height:var(--nh);background:rgba(4,8,18,0.97);border-bottom:1px solid var(--border);backdrop-filter:blur(20px);display:flex;align-items:center;padding:0 18px;z-index:1000;gap:14px;}
.topbar-brand{font-family:'Syne',sans-serif;font-weight:800;font-size:14px;letter-spacing:0.1em;text-transform:uppercase;background:linear-gradient(90deg,var(--cyan),var(--blue));-webkit-background-clip:text;-webkit-text-fill-color:transparent;white-space:nowrap;}
.topbar-toggle{background:transparent;border:1px solid var(--border);color:var(--cyan);width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;flex-shrink:0;}
.topbar-toggle:hover{border-color:var(--cyan);background:rgba(0,200,255,0.08);}
.topbar-user{margin-left:auto;font-size:13px;color:var(--muted);display:flex;align-items:center;gap:10px;}
.topbar-avatar{width:32px;height:32px;background:linear-gradient(135deg,var(--cyan),var(--purple));border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:700;font-size:13px;color:white;}
.live-dot{width:7px;height:7px;background:var(--green);border-radius:50%;box-shadow:0 0 8px var(--green);animation:pdot 2s infinite;}
@keyframes pdot{0%,100%{opacity:1;transform:scale(1);}50%{opacity:0.4;transform:scale(0.7);}}
.sidebar{position:fixed;top:var(--nh);left:0;width:var(--sw);height:calc(100vh - var(--nh));background:rgba(4,8,18,0.98);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:20px 10px;transition:width 0.3s cubic-bezier(.4,0,.2,1);z-index:900;overflow:hidden;}
.sidebar.slim{width:var(--slim);}
.sidebar.slim .nlabel,.sidebar.slim .sec-title{display:none;}
.sec-title{font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:var(--faint);padding:0 10px;margin-bottom:6px;white-space:nowrap;}
.nav-i{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;cursor:pointer;color:var(--muted);font-size:14px;font-weight:500;transition:all 0.2s;text-decoration:none;margin-bottom:3px;white-space:nowrap;border-left:2px solid transparent;}
.nav-i:hover{background:rgba(0,200,255,0.07);color:var(--text);}
.nav-i.active{background:linear-gradient(90deg,rgba(0,200,255,0.14),rgba(0,112,243,0.08));color:var(--cyan);border-left-color:var(--cyan);}
.nav-icon{font-size:17px;flex-shrink:0;width:20px;text-align:center;}
.nbadge{margin-left:auto;background:var(--cyan);color:var(--bg);font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;font-family:'Syne',sans-serif;}
.sidebar-bot{margin-top:auto;border-top:1px solid var(--border);padding-top:14px;}
.divider{height:1px;background:var(--border);margin:8px 0;}
.content{margin-left:var(--sw);padding:calc(var(--nh)+22px) 22px 32px;transition:margin-left 0.3s cubic-bezier(.4,0,.2,1);min-height:100vh;position:relative;z-index:1;}
.sidebar.slim~.content{margin-left:var(--slim);}
.page{display:none;}
.page.active{display:block;animation:fadeUp 0.35s ease;}
@keyframes fadeUp{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.ph{margin-bottom:24px;}.ph h1{font-family:'Syne',sans-serif;font-size:26px;font-weight:800;}.ph p{color:var(--muted);font-size:13px;margin-top:3px;}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:24px;}
.stat-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:18px 20px;position:relative;overflow:hidden;transition:all 0.3s;}
.stat-card:hover{border-color:var(--border-glow);transform:translateY(-3px);box-shadow:0 12px 32px rgba(0,0,0,0.4);}
.stat-card::after{content:'';position:absolute;top:0;right:0;width:70px;height:70px;border-radius:0 16px 0 70px;opacity:0.07;}
.sc-cyan::after{background:var(--cyan)}.sc-blue::after{background:var(--blue)}.sc-green::after{background:var(--green)}.sc-purple::after{background:var(--purple)}.sc-amber::after{background:var(--amber)}.sc-pink::after{background:var(--pink)}
.si{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:12px;}
.si-cyan{background:rgba(0,200,255,0.12);color:var(--cyan)}.si-blue{background:rgba(0,112,243,0.12);color:var(--blue)}.si-green{background:rgba(0,245,160,0.12);color:var(--green)}.si-purple{background:rgba(124,58,237,0.12);color:var(--purple)}.si-amber{background:rgba(255,184,0,0.12);color:var(--amber)}.si-pink{background:rgba(244,114,182,0.12);color:var(--pink)}
.sv{font-family:'Syne',sans-serif;font-size:30px;font-weight:800;line-height:1;margin-bottom:3px;}
.sl{font-size:11px;color:var(--faint);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;}
.gp{background:var(--bg-card);border:1px solid var(--border);border-radius:18px;padding:22px;backdrop-filter:blur(10px);}
.gp h3{font-family:'Syne',sans-serif;font-size:14px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.clabel{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--faint);margin-bottom:5px;margin-top:12px;display:block;}
.cinput{width:100%;padding:9px 13px;background:rgba(4,8,18,0.8);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:14px;transition:all 0.2s;outline:none;}
.cinput:focus{border-color:var(--cyan);box-shadow:0 0 0 3px rgba(0,200,255,0.1);}
textarea.cinput{min-height:75px;resize:vertical;}
select.cinput option{background:#0a1628;color:var(--text);}
.sched-block{background:rgba(4,8,18,0.6);border:1px solid var(--border);border-radius:13px;padding:14px;margin-top:12px;}
.sched-row{display:grid;grid-template-columns:1fr;gap:5px;margin-bottom:10px;}
.sched-row:last-child{margin-bottom:0;}
.sched-tag{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--faint);margin-bottom:6px;grid-column:1/-1;display:flex;align-items:center;gap:6px;}
.ds{width:7px;height:7px;background:var(--cyan);border-radius:50%;}
.de{width:7px;height:7px;border:2px solid var(--purple);border-radius:50%;}
.btnp{width:100%;padding:11px;background:linear-gradient(135deg,var(--cyan),var(--blue));border:none;border-radius:11px;color:var(--bg);font-family:'Syne',sans-serif;font-size:13px;font-weight:800;cursor:pointer;transition:all 0.2s;margin-top:12px;letter-spacing:0.02em;}
.btnp:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(0,200,255,0.3);}
.btnd{width:100%;padding:9px;background:rgba(255,56,96,0.09);border:1px solid rgba(255,56,96,0.28);border-radius:11px;color:var(--red);font-family:'Syne',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:0.2s;margin-top:7px;}
.btnd:hover{background:rgba(255,56,96,0.19);}
.statusbar{display:flex;align-items:center;gap:8px;padding:9px 13px;background:rgba(0,200,255,0.06);border:1px solid rgba(0,200,255,0.12);border-radius:9px;font-size:12px;color:var(--muted);margin-top:10px;}
.area-box{background:rgba(0,245,160,0.06);border:1px solid rgba(0,245,160,0.18);border-radius:12px;padding:12px 14px;margin-top:12px;display:none;}
.area-box.show{display:block;}
.area-row{display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;}
.area-row span:first-child{color:var(--muted);}
.area-row span:last-child{font-family:'Syne',sans-serif;font-weight:700;color:var(--green);}
.zone-list{max-height:280px;overflow-y:auto;}
.zone-list::-webkit-scrollbar{width:4px;}
.zone-list::-webkit-scrollbar-thumb{background:var(--faint);border-radius:4px;}
.zone-item{background:rgba(4,8,18,0.8);border:1px solid var(--border);border-radius:11px;padding:12px;margin-bottom:7px;transition:0.2s;}
.zone-item:hover{border-color:var(--border-glow);}
.zone-name{font-family:'Syne',sans-serif;font-size:13px;font-weight:700;margin-bottom:3px;}
.zone-meta{font-size:11px;color:var(--faint);}
.zone-acts{display:flex;gap:5px;margin-top:8px;}
.zbv{flex:1;padding:5px;background:rgba(0,200,255,0.1);border:1px solid rgba(0,200,255,0.2);color:var(--cyan);border-radius:7px;font-size:11px;font-weight:600;cursor:pointer;transition:0.2s;}
.zbv:hover{background:rgba(0,200,255,0.2);}
.zbd{flex:1;padding:5px;background:rgba(255,56,96,0.08);border:1px solid rgba(255,56,96,0.2);color:var(--red);border-radius:7px;font-size:11px;font-weight:600;cursor:pointer;transition:0.2s;}
.zbd:hover{background:rgba(255,56,96,0.18);}
.zbq{flex:1;padding:5px;background:rgba(124,58,237,0.1);border:1px solid rgba(124,58,237,0.2);color:#a78bfa;border-radius:7px;font-size:11px;font-weight:600;cursor:pointer;transition:0.2s;}
.zbq:hover{background:rgba(124,58,237,0.2);}
.map-layout{display:grid;grid-template-columns:310px 1fr;gap:18px;align-items:start;}
#map{height:calc(100vh - 180px);min-height:500px;border-radius:15px;border:1px solid var(--border);}
.ev-card{background:var(--bg-card);border:1px solid var(--border);border-radius:18px;padding:22px;transition:all 0.3s;position:relative;overflow:hidden;height:100%;}
.ev-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--green),var(--cyan),var(--blue));}
.ev-card:hover{transform:translateY(-4px);border-color:var(--border-glow);box-shadow:0 16px 40px rgba(0,0,0,0.4);}
.ev-name{font-family:'Syne',sans-serif;font-size:17px;font-weight:700;margin-bottom:12px;}
.ev-meta{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);margin-bottom:7px;}
.ev-meta i{color:var(--cyan);}
.ev-desc{background:rgba(4,8,18,0.6);border:1px solid var(--border);border-radius:10px;padding:12px;font-size:13px;color:var(--muted);min-height:60px;margin-top:12px;line-height:1.6;}
.ev-foot{display:flex;gap:6px;margin-top:16px;flex-wrap:wrap;}
.ev-chip{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;margin-bottom:10px;}
.chip-active{background:rgba(0,245,160,0.15);color:var(--green);}
.chip-upcoming{background:rgba(255,184,0,0.15);color:var(--amber);}
.chip-past{background:rgba(61,96,128,0.25);color:var(--muted);}
.btn-ev{flex:1;min-width:60px;padding:8px 6px;border-radius:9px;font-weight:700;font-size:12px;cursor:pointer;transition:0.2s;border:1px solid;}
.btn-ev-edit{background:rgba(0,112,243,0.14);border-color:rgba(0,112,243,0.28);color:#60a5fa;}
.btn-ev-edit:hover{background:rgba(0,112,243,0.26);}
.btn-ev-qr{background:rgba(124,58,237,0.12);border-color:rgba(124,58,237,0.25);color:#a78bfa;}
.btn-ev-qr:hover{background:rgba(124,58,237,0.22);}
.btn-ev-att{background:rgba(0,200,255,0.1);border-color:rgba(0,200,255,0.22);color:var(--cyan);}
.btn-ev-att:hover{background:rgba(0,200,255,0.2);}
.btn-ev-del{background:rgba(255,56,96,0.09);border-color:rgba(255,56,96,0.22);color:var(--red);}
.btn-ev-del:hover{background:rgba(255,56,96,0.18);}
.filter-bar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;align-items:center;}
.filter-pill{padding:6px 14px;border-radius:20px;background:rgba(4,8,18,0.8);border:1px solid var(--border);color:var(--muted);font-size:12px;font-weight:600;cursor:pointer;transition:0.2s;font-family:'Syne',sans-serif;}
.filter-pill:hover{border-color:var(--cyan);color:var(--text);}
.filter-pill.active-f{background:linear-gradient(135deg,rgba(0,200,255,0.2),rgba(0,112,243,0.12));border-color:var(--cyan);color:var(--cyan);}
.course-section{margin-bottom:28px;}
.year-section{margin-bottom:16px;}
.year-label{font-family:'Syne',sans-serif;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--muted);margin-bottom:8px;display:flex;align-items:center;gap:8px;}
.year-label::after{content:'';flex:1;height:1px;background:var(--border);}
.section-subsection{margin-bottom:12px;}
.section-label{font-size:10px;font-weight:700;color:var(--faint);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:6px;padding-left:2px;}
.student-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:12px 14px;transition:all 0.25s;display:flex;align-items:center;gap:10px;}
.student-card:hover{border-color:var(--border-glow);transform:translateX(3px);}
.stu-av{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:14px;color:white;flex-shrink:0;}
.stu-info{flex:1;min-width:0;}
.stu-name{font-family:'Syne',sans-serif;font-weight:700;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.stu-email{font-size:11px;color:var(--faint);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.att-table{width:100%;border-collapse:collapse;font-size:13px;}
.att-table th{padding:10px 14px;background:rgba(4,8,18,0.6);color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;text-align:left;border-bottom:1px solid var(--border);}
.att-table td{padding:10px 14px;border-bottom:1px solid rgba(255,255,255,0.03);vertical-align:middle;}
.att-table tr:last-child td{border-bottom:none;}
.att-table tr:hover td{background:rgba(0,200,255,0.03);}
.chart-card{background:var(--bg-card);border:1px solid var(--border);border-radius:18px;padding:22px;}
.chart-card h3{font-family:'Syne',sans-serif;font-size:14px;font-weight:700;margin-bottom:18px;}
.act-item{display:flex;gap:10px;align-items:flex-start;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.04);}
.act-item:last-child{border-bottom:none;}
.act-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;margin-top:5px;}
.act-body{flex:1;font-size:13px;color:var(--muted);line-height:1.5;}
.act-time{font-size:11px;color:var(--faint);white-space:nowrap;}
.modal-content{background:var(--bg-deep)!important;border:1px solid var(--border)!important;border-radius:18px!important;color:var(--text)!important;}
.modal-header,.modal-footer{border-color:var(--border)!important;}
.modal-title{font-family:'Syne',sans-serif!important;font-weight:700!important;}
.form-control,.form-select{background:rgba(4,8,18,0.8)!important;border:1px solid var(--border)!important;border-radius:9px!important;color:var(--text)!important;}
.form-control:focus,.form-select:focus{border-color:var(--cyan)!important;box-shadow:0 0 0 3px rgba(0,200,255,0.1)!important;}
.form-label{font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:var(--faint);}
.form-select option{background:#0a1628;color:var(--text);}
.search-wrap{position:relative;}
.search-wrap i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--faint);font-size:13px;}
.search-wrap input{padding-left:34px;}
.empty{text-align:center;padding:44px;color:var(--faint);}
.empty i{font-size:44px;display:block;margin-bottom:14px;opacity:0.25;}
.empty p{font-size:14px;}
.dur-pill{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;}
@media(max-width:992px){.sidebar{width:var(--slim);}.sidebar .nlabel,.sidebar .sec-title{display:none;}.content{margin-left:var(--slim);}.map-layout{grid-template-columns:1fr;}#map{height:480px;}}
@media(max-width:768px){.content{padding:calc(var(--nh)+14px) 12px 24px;}.stat-grid{grid-template-columns:1fr 1fr;}}
@media(max-width:576px){.stat-grid{grid-template-columns:1fr;}.topbar-brand{display:none;}}
</style>
</head>
<body>

<header class="topbar">
  <button class="topbar-toggle" id="toggleSidebar"><i class="bi bi-list" style="font-size:18px;"></i></button>
  <span class="topbar-brand"><i class="bi bi-geo-alt-fill me-1"></i>GeoFence System</span>
  <div class="topbar-user">
    <div class="live-dot"></div>
    <span class="d-none d-sm-inline"><?php echo htmlspecialchars($fullname); ?></span>
    <div class="topbar-avatar"><?php echo strtoupper(substr($fullname,0,1)); ?></div>
  </div>
</header>

<nav id="sidebar" class="sidebar">
  <div>
    <div class="sec-title">Main</div>
    <a class="nav-i active" id="n-home" onclick="showPage('home')"><span class="nav-icon"><i class="bi bi-grid-fill"></i></span><span class="nlabel">Overview</span></a>
    <a class="nav-i" id="n-map" onclick="showPage('map')"><span class="nav-icon"><i class="bi bi-map-fill"></i></span><span class="nlabel">Geofence Builder</span></a>
    <a class="nav-i" id="n-events" onclick="showPage('events')"><span class="nav-icon"><i class="bi bi-calendar-event-fill"></i></span><span class="nlabel">Events</span><span class="nbadge" id="ev-badge">0</span></a>
    <div class="divider"></div>
    <div class="sec-title">People</div>
    <a class="nav-i" id="n-students" onclick="showPage('students')"><span class="nav-icon"><i class="bi bi-mortarboard-fill"></i></span><span class="nlabel">Students</span></a>
    <a class="nav-i" id="n-users" onclick="showPage('users')"><span class="nav-icon"><i class="bi bi-people-fill"></i></span><span class="nlabel">Admin Users</span></a>
    <div class="divider"></div>
    <div class="sec-title">Reports</div>
    <a class="nav-i" id="n-attendance" onclick="showPage('attendance')"><span class="nav-icon"><i class="bi bi-clock-history"></i></span><span class="nlabel">Attendance Logs</span></a>
    <a class="nav-i" id="n-analytics" onclick="showPage('analytics')"><span class="nav-icon"><i class="bi bi-bar-chart-fill"></i></span><span class="nlabel">Analytics</span></a>
  </div>
  <div class="sidebar-bot">
    <a href="../Backend/logout.php" class="nav-i" style="color:var(--red);"><span class="nav-icon"><i class="bi bi-box-arrow-left"></i></span><span class="nlabel">Logout</span></a>
  </div>
</nav>

<main class="content">

<!-- OVERVIEW -->
<div id="page-home" class="page active">
  <div class="ph"><h1>Overview</h1><p>Live system snapshot</p></div>
  <div class="stat-grid">
    <div class="stat-card sc-cyan"><div class="si si-cyan"><i class="bi bi-geo-alt-fill"></i></div><div class="sv" id="s-zones">—</div><div class="sl">Active Zones</div></div>
    <div class="stat-card sc-blue"><div class="si si-blue"><i class="bi bi-calendar-check-fill"></i></div><div class="sv" id="s-events">—</div><div class="sl">Total Events</div></div>
    <div class="stat-card sc-green"><div class="si si-green"><i class="bi bi-mortarboard-fill"></i></div><div class="sv" id="s-students">—</div><div class="sl">Students</div></div>
    <div class="stat-card sc-amber"><div class="si si-amber"><i class="bi bi-hourglass-split"></i></div><div class="sv" id="s-upcoming">—</div><div class="sl">Upcoming</div></div>
    <div class="stat-card sc-purple"><div class="si si-purple"><i class="bi bi-check2-all"></i></div><div class="sv" id="s-checkins">—</div><div class="sl">Total Check-ins</div></div>
    <div class="stat-card sc-pink"><div class="si si-pink"><i class="bi bi-door-open-fill"></i></div><div class="sv" id="s-inside">—</div><div class="sl">Currently Inside</div></div>
  </div>
  <div class="row g-4">
    <div class="col-lg-8"><div class="chart-card"><h3><i class="bi bi-activity" style="color:var(--cyan);"></i> Monthly Events</h3><canvas id="c-monthly" height="230"></canvas></div></div>
    <div class="col-lg-4"><div class="chart-card"><h3><i class="bi bi-pie-chart-fill" style="color:var(--purple);"></i> Zone Status</h3><canvas id="c-status" height="200"></canvas></div></div>
  </div>
  <div class="row g-4 mt-0">
    <div class="col-lg-6"><div class="gp mt-4"><h3><i class="bi bi-lightning-fill" style="color:var(--amber);"></i> Recent Activity</h3><div id="activity-feed"></div></div></div>
    <div class="col-lg-6"><div class="gp mt-4"><h3><i class="bi bi-calendar2-week-fill" style="color:var(--green);"></i> Upcoming Events</h3><div id="upcoming-list"></div></div></div>
  </div>
</div>

<!-- MAP -->
<div id="page-map" class="page">
  <div class="ph"><h1>Geofence Builder</h1><p>Draw zones · set validation rules · save to database</p></div>
  <div class="map-layout">
    <div class="gp" style="position:sticky;top:76px;max-height:calc(100vh - 90px);overflow-y:auto;">
      <h3><i class="bi bi-pencil-square" style="color:var(--cyan);"></i> Zone Config</h3>
      <button class="btnp" onclick="viewAdminLocation()" style="background:linear-gradient(135deg,var(--green),var(--cyan));margin-bottom:4px;">
  <i class="bi bi-geo-fill me-2"></i>View My Location
</button>
      <span class="clabel">Event Name</span>
      <input type="text" class="cinput" id="ev-name" placeholder="e.g. Campus Assembly 2026">
      <div class="sched-block">
        <div class="sched-tag"><span class="ds"></span> Start</div>
        <div class="sched-row"><input type="date" class="cinput" id="start-date"><input type="time" class="cinput" id="start-time"></div>
        <div class="sched-tag"><span class="de"></span> End</div>
        <div class="sched-row"><input type="date" class="cinput" id="end-date"><input type="time" class="cinput" id="end-time"></div>
      </div>
      <span class="clabel">Description</span>
      <textarea class="cinput" id="ev-desc" placeholder="Describe the event..."></textarea>
      <span class="clabel">Allowed WiFi IPs / Subnets</span>
      <input type="text" class="cinput" id="ev-ips" placeholder="192.168.1.0/24, 10.0.0.5">
      <small style="color:var(--faint);font-size:11px;display:block;margin-top:4px;">Comma-separated. Leave blank to skip IP check.</small>
      <div class="row g-2 mt-1">
        <div class="col-6"><label class="clabel">Require GPS</label><select class="cinput" id="req-gps"><option value="1">Yes</option><option value="0">No</option></select></div>
        <div class="col-6"><label class="clabel">Require IP</label><select class="cinput" id="req-ip"><option value="1">Yes</option><option value="0">No</option></select></div>
      </div>
      <span class="clabel">Min. Side Length (m)</span>
      <input type="number" class="cinput" id="min-dist" value="30">
      <div class="area-box" id="area-box">
        <div class="area-row"><span>Area</span><span id="calc-area">—</span></div>
        <div class="area-row"><span>Perimeter</span><span id="calc-perim">—</span></div>
        <div class="area-row"><span>Vertices</span><span id="calc-verts">—</span></div>
        <div class="area-row"><span>Side lengths</span><span id="calc-sides">—</span></div>
      </div>
      <button class="btnp" id="draw-poly-btn" onclick="startDrawPoly()" style="background:linear-gradient(135deg,var(--purple),var(--blue));margin-bottom:0;">
      <i class="bi bi-pentagon me-2"></i>Draw Polygon
     </button>
      <button class="btnp" onclick="saveFence()"><i class="bi bi-save me-2"></i>Save Geofence</button>
      <button class="btnd" onclick="clearAll()"><i class="bi bi-trash me-2"></i>Clear All</button>
      <div class="statusbar" id="statusbar"><i class="bi bi-info-circle" style="color:var(--cyan);"></i><span id="statustext">Draw a polygon to begin.</span></div>
      <div style="margin-top:18px;">
        <div style="font-family:'Syne',sans-serif;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--faint);margin-bottom:8px;"><i class="bi bi-layers me-1"></i> Saved Zones</div>
        <div class="zone-list" id="zone-list"></div>
      </div>
    </div>
    <div><div id="map"></div></div>
  </div>
</div>

<!-- EVENTS -->
<div id="page-events" class="page">
  <div class="ph d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div><h1>Events</h1><p>All geofence events from database</p></div>
    <div class="search-wrap" style="width:250px;"><i class="bi bi-search"></i><input type="text" class="cinput" id="ev-search" placeholder="Search events..." oninput="filterEvents()"></div>
  </div>
  <div class="row g-4" id="event-cards"></div>
</div>

<!-- STUDENTS -->
<div id="page-students" class="page">
  <div class="ph"><h1>Students</h1><p>Browse by course, year level, and section</p></div>
  <div class="filter-bar" id="filter-bar">
    <span style="font-size:11px;color:var(--faint);font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">Course:</span>
    <button class="filter-pill active-f" onclick="filterStudents('all','all')" id="fp-all">All</button>
    <button class="filter-pill" onclick="filterStudents('BSIT','all')" id="fp-BSIT">BSIT</button>
    <button class="filter-pill" onclick="filterStudents('BSCS','all')" id="fp-BSCS">BSCS</button>
    <button class="filter-pill" onclick="filterStudents('BSBA','all')" id="fp-BSBA">BSBA</button>
    <span style="font-size:11px;color:var(--faint);font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-left:8px;">Year:</span>
    <button class="filter-pill" onclick="filterStudents('all',1)" id="fy-1">1st</button>
    <button class="filter-pill" onclick="filterStudents('all',2)" id="fy-2">2nd</button>
    <button class="filter-pill" onclick="filterStudents('all',3)" id="fy-3">3rd</button>
    <button class="filter-pill" onclick="filterStudents('all',4)" id="fy-4">4th</button>
  </div>
  <div id="students-container"></div>
</div>

<!-- ADMIN USERS -->
<div id="page-users" class="page">
  <div class="ph"><h1>Admin Users</h1><p>Manage admin accounts</p></div>
  <div class="row g-4" id="user-cards">
<?php
if (!$conn->connect_error) {
    $res = $conn->query("SELECT ID, UserName, Email FROM usernamepass WHERE role='admin' OR role IS NULL OR role=''");
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $init = strtoupper(substr($row['UserName'],0,1));
            echo '<div class="col-xl-3 col-lg-4 col-md-6 mb-2" id="user-'.$row['ID'].'"><div class="student-card" style="flex-direction:column;align-items:flex-start;background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:16px;display:flex;"><div style="display:flex;align-items:center;gap:12px;width:100%;"><div class="stu-av" style="background:linear-gradient(135deg,var(--blue),var(--purple));">'.$init.'</div><div class="stu-info"><div class="stu-name">'.htmlspecialchars($row['UserName']).'</div><div class="stu-email">'.htmlspecialchars($row['Email']).'</div></div></div><div style="display:flex;gap:8px;margin-top:12px;width:100%;"><button style="flex:1;padding:7px;background:rgba(0,112,243,0.12);border:1px solid rgba(0,112,243,0.25);color:#60a5fa;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;" class="edit-btn" data-id="'.$row['ID'].'" data-email="'.htmlspecialchars($row['Email']).'" data-bs-toggle="modal" data-bs-target="#editModal"><i class="bi bi-pencil me-1"></i>Edit</button><button style="flex:1;padding:7px;background:rgba(255,56,96,0.09);border:1px solid rgba(255,56,96,0.22);color:var(--red);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;" class="delete-btn" data-id="'.$row['ID'].'" data-bs-toggle="modal" data-bs-target="#deleteModal"><i class="bi bi-trash me-1"></i>Delete</button></div></div></div>';
        }
    } else {
        echo '<div class="col-12"><div class="empty"><i class="bi bi-people"></i><p>No admin users.</p></div></div>';
    }
}
?>
  </div>
</div>

<!-- ATTENDANCE -->
<div id="page-attendance" class="page">
  <div class="ph d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div><h1>Attendance Logs</h1><p>Time-in / time-out per event</p></div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <select class="cinput" id="att-filter-event" onchange="loadAttendance()" style="width:200px;"><option value="">All Events</option></select>
      <div class="search-wrap" style="width:180px;"><i class="bi bi-search"></i><input type="text" class="cinput" id="att-search" placeholder="Search student..." oninput="filterAttendance()"></div>
    </div>
  </div>
  <div class="gp"><div style="overflow-x:auto;"><table class="att-table"><thead><tr><th>Student</th><th>Event</th><th>Time In</th><th>Time Out</th><th>Duration</th><th>IP</th><th>GPS</th><th>Status</th></tr></thead><tbody id="att-tbody"></tbody></table></div></div>
</div>

<!-- ANALYTICS -->
<div id="page-analytics" class="page">
  <div class="ph"><h1>Analytics</h1><p>System-wide insights</p></div>
  <div class="stat-grid">
    <div class="stat-card sc-cyan"><div class="si si-cyan"><i class="bi bi-geo-alt-fill"></i></div><div class="sv" id="a-zones">—</div><div class="sl">Total Zones</div></div>
    <div class="stat-card sc-green"><div class="si si-green"><i class="bi bi-check-circle-fill"></i></div><div class="sv" id="a-active">—</div><div class="sl">Active Now</div></div>
    <div class="stat-card sc-amber"><div class="si si-amber"><i class="bi bi-hourglass-split"></i></div><div class="sv" id="a-up">—</div><div class="sl">Upcoming</div></div>
    <div class="stat-card sc-purple"><div class="si si-purple"><i class="bi bi-archive-fill"></i></div><div class="sv" id="a-past">—</div><div class="sl">Past</div></div>
  </div>
  <div class="row g-4">
    <div class="col-lg-6"><div class="chart-card"><h3><i class="bi bi-bar-chart-fill" style="color:var(--cyan);"></i> Events Per Month</h3><canvas id="a-bar" height="260"></canvas></div></div>
    <div class="col-lg-6"><div class="chart-card"><h3><i class="bi bi-graph-up" style="color:var(--green);"></i> Cumulative Zones</h3><canvas id="a-line" height="260"></canvas></div></div>
    <div class="col-lg-6"><div class="chart-card"><h3><i class="bi bi-people-fill" style="color:var(--purple);"></i> Students by Course</h3><canvas id="a-course" height="260"></canvas></div></div>
    <div class="col-lg-6"><div class="chart-card"><h3><i class="bi bi-clock-history" style="color:var(--amber);"></i> Event Duration (hrs)</h3><canvas id="a-dur" height="260"></canvas></div></div>
  </div>
</div>

</main>

<!-- MODALS -->
<div class="modal fade" id="deleteModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Confirm Delete</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body" style="color:var(--muted);">Delete this user?<input type="hidden" id="deleteUserId"></div><div class="modal-footer"><button type="button" id="confirmDelete" class="btn btn-danger rounded-pill px-4">Delete</button><button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button></div></div></div></div>

<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" id="editForm"><div class="modal-header"><h5 class="modal-title"><i class="bi bi-pencil-square me-2" style="color:var(--cyan);"></i>Edit User</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="editUserId"><div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" id="editUserEmail" required></div><div class="mb-3"><label class="form-label">New Password</label><input type="password" class="form-control" id="editUserPassword" placeholder="Leave blank to keep"></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary rounded-pill px-4">Save</button><button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button></div></form></div></div>

<div class="modal fade" id="addStudentModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" id="addStudentForm"><div class="modal-header"><h5 class="modal-title"><i class="bi bi-person-plus me-2" style="color:var(--green);"></i>Add Student</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label">Full Name</label><input type="text" class="form-control" id="stu-name" required></div><div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" id="stu-email" required></div><div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control" id="stu-password" required></div><div class="row"><div class="col-4 mb-3"><label class="form-label">Course</label><select class="form-select" id="stu-course"><option value="BSIT">BSIT</option><option value="BSCS">BSCS</option><option value="BSBA">BSBA</option></select></div><div class="col-4 mb-3"><label class="form-label">Year</label><select class="form-select" id="stu-year"><option value="1">1st</option><option value="2">2nd</option><option value="3">3rd</option><option value="4">4th</option></select></div><div class="col-4 mb-3"><label class="form-label">Section</label><input type="text" class="form-control" id="stu-section" placeholder="e.g. 1A"></div></div></div><div class="modal-footer"><button type="submit" class="btn btn-success rounded-pill px-4">Add Student</button><button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button></div></form></div></div>

<div class="modal fade" id="editEventModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><i class="bi bi-calendar-event-fill me-2" style="color:var(--cyan);"></i>Edit Event</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="edit-ev-id"><div class="mb-3"><label class="form-label">Event Name</label><input type="text" class="form-control" id="edit-ev-name"></div><div class="row"><div class="col-6 mb-3"><label class="form-label">Start Date</label><input type="date" class="form-control" id="edit-ev-sd"></div><div class="col-6 mb-3"><label class="form-label">Start Time</label><input type="time" class="form-control" id="edit-ev-st"></div><div class="col-6 mb-3"><label class="form-label">End Date</label><input type="date" class="form-control" id="edit-ev-ed"></div><div class="col-6 mb-3"><label class="form-label">End Time</label><input type="time" class="form-control" id="edit-ev-et"></div></div><div class="mb-3"><label class="form-label">Allowed IPs (comma-separated)</label><input type="text" class="form-control" id="edit-ev-ips" placeholder="192.168.1.0/24"></div><div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" rows="3" id="edit-ev-desc"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-primary rounded-pill px-4" onclick="saveEditedEvent()">Save</button><button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button></div></div></div></div>

<div class="modal fade" id="qrModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered" style="max-width:340px;"><div class="modal-content" style="text-align:center;"><div class="modal-header"><h5 class="modal-title">Event QR Code</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body" style="padding:24px;"><canvas id="qrCanvas" width="256" height="256" style="border-radius:10px;"></canvas><p style="color:var(--muted);font-size:12px;margin-top:12px;" id="qr-url-label"></p></div><div class="modal-footer"><button class="btn btn-outline-light rounded-pill btn-sm" onclick="downloadQR()"><i class="bi bi-download me-1"></i>Download</button><button type="button" class="btn btn-secondary rounded-pill btn-sm" data-bs-dismiss="modal">Close</button></div></div></div></div>

<div class="modal fade" id="attDetailModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="att-modal-title">Attendance</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body" style="overflow-x:auto;"><table class="att-table"><thead><tr><th>Student</th><th>Course</th><th>Section</th><th>Time In</th><th>Time Out</th><th>Duration</th><th>Status</th></tr></thead><tbody id="att-modal-tbody"></tbody></table></div></div></div></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* NAV */
const PAGES=['home','map','events','students','users','attendance','analytics'];
function showPage(id){
  PAGES.forEach(p=>{document.getElementById('page-'+p).classList.remove('active');document.getElementById('n-'+p).classList.remove('active');});
  document.getElementById('page-'+id).classList.add('active');document.getElementById('n-'+id).classList.add('active');
  if(id==='map')setTimeout(()=>map.invalidateSize(),310);
  if(id==='events')loadEvents();if(id==='students')loadStudents();
  if(id==='home')loadDashboard();if(id==='analytics')loadAnalytics();
  if(id==='attendance')loadAttendance();
}
document.getElementById('toggleSidebar').onclick=()=>{document.getElementById('sidebar').classList.toggle('slim');setTimeout(()=>{if(document.getElementById('page-map').classList.contains('active'))map.invalidateSize();},310);};
const esc=s=>String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const escA=s=>String(s).replace(/'/g,"\\'").replace(/\n/g,' ');
function statusChip(ev){const now=new Date(),s=ev.start_date?new Date(ev.start_date+'T'+(ev.start_time||'00:00')):null,e=ev.end_date?new Date(ev.end_date+'T'+(ev.end_time||'23:59')):null;if(s&&e){if(now>=s&&now<=e)return'<span class="ev-chip chip-active">● ACTIVE</span>';if(now<s)return'<span class="ev-chip chip-upcoming">◷ UPCOMING</span>';return'<span class="ev-chip chip-past">✓ PAST</span>';}return'';}
function duration(tin,tout){if(!tin||!tout)return'—';const ms=new Date(tout)-new Date(tin);if(ms<0)return'—';const h=Math.floor(ms/3600000),m=Math.floor((ms%3600000)/60000);return h?h+'h '+m+'m':m+'m';}
function fmtTime(dt){return dt?new Date(dt).toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit',second:'2-digit'}):'—';}
function fmtDate(dt){return dt?new Date(dt).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}):'—';}

/* MAP */
const map=L.map('map').setView([14.6510,121.0490],15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OSM',maxZoom:19}).addTo(map);
const drawn=new L.FeatureGroup();map.addLayer(drawn);
const drawCtrl=new L.Control.Draw({edit:{featureGroup:drawn},draw:{polyline:false,circle:false,marker:false,circlemarker:false,rectangle:false,polygon:{allowIntersection:false,showArea:true,shapeOptions:{color:'#00c8ff',fillColor:'#00c8ff',fillOpacity:0.12,weight:2}}}});
map.addControl(drawCtrl);
let curLayer=null,geofences=[];
function haverDist(a,b){const R=6371000,dl=(b.lat-a.lat)*Math.PI/180,dg=(b.lng-a.lng)*Math.PI/180,x=Math.sin(dl/2)**2+Math.cos(a.lat*Math.PI/180)*Math.cos(b.lat*Math.PI/180)*Math.sin(dg/2)**2;return R*2*Math.atan2(Math.sqrt(x),Math.sqrt(1-x));}
function polyArea(lls){let a=0,n=lls.length;for(let i=0;i<n;i++){const j=(i+1)%n;a+=lls[i].lng*lls[j].lat-lls[j].lng*lls[i].lat;}const lr=lls[0].lat*Math.PI/180;return Math.abs(a/2)*111320*(111320*Math.cos(lr));}
function calcStats(lls){let p=0,n=lls.length,sides=[];for(let i=0;i<n;i++){const d=haverDist(lls[i],lls[(i+1)%n]);p+=d;sides.push(d.toFixed(1)+'m');}return{area:polyArea(lls),perim:p,verts:n,sides:sides.join(', ')};}
function validatePoly(lls){const min=parseFloat(document.getElementById('min-dist').value)||30;for(let i=0;i<lls.length;i++){const d=haverDist(lls[i],lls[(i+1)%lls.length]);if(d<min)return{valid:false,msg:'Side '+(i+1)+' too short: '+d.toFixed(1)+'m (min '+min+'m)'};} return{valid:true,msg:'Polygon valid. Fill in details and save.'};}
map.on(L.Draw.Event.CREATED,e=>{
  const lls=e.layerType==='polygon'?e.layer.getLatLngs()[0]:[];
  const r=validatePoly(lls);if(!r.valid){setStatus(r.msg,true);alert(r.msg);return;}
  drawn.addLayer(e.layer);curLayer=e.layer;setStatus(r.msg);
  const st=calcStats(lls);
  document.getElementById('calc-area').textContent=st.area.toFixed(1)+' m²';
  document.getElementById('calc-perim').textContent=st.perim.toFixed(1)+' m';
  document.getElementById('calc-verts').textContent=st.verts;
  document.getElementById('calc-sides').textContent=st.sides;
  document.getElementById('area-box').classList.add('show');
});
function setStatus(msg,err=false){document.getElementById('statustext').textContent=msg;document.getElementById('statusbar').style.borderColor=err?'rgba(255,56,96,0.3)':'rgba(0,200,255,0.12)';}

async function saveFence(){
  const name=document.getElementById('ev-name').value.trim();
  if(!curLayer){alert('Draw a geofence first.');return;}if(!name){alert('Enter event name.');return;}
  const coords=curLayer.getLatLngs()[0].map(p=>({lat:p.lat,lng:p.lng}));
  const st=calcStats(curLayer.getLatLngs()[0]);
  const ipsRaw=document.getElementById('ev-ips').value.trim();
  const ips=ipsRaw?ipsRaw.split(',').map(s=>s.trim()).filter(Boolean):[];
  const payload={name,startDate:document.getElementById('start-date').value,startTime:document.getElementById('start-time').value,endDate:document.getElementById('end-date').value,endTime:document.getElementById('end-time').value,description:document.getElementById('ev-desc').value.trim(),allowedIps:ips,requireGps:document.getElementById('req-gps').value,requireIp:document.getElementById('req-ip').value,areaSqm:st.area.toFixed(2),perimeterM:st.perim.toFixed(2),coordinates:coords};
  try{
    const res=await fetch('../Backend/save_geofence.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    const r=await res.json();
    if(r.success){geofences.push({...payload,id:r.id,layer:curLayer});renderZoneList();setStatus('Saved!');['ev-name','ev-desc','ev-ips','start-date','start-time','end-date','end-time'].forEach(id=>document.getElementById(id).value='');document.getElementById('area-box').classList.remove('show');curLayer=null;updateBadge();}
    else alert(r.message||'Save failed.');
  }catch(e){alert('DB error.');}
}
function renderZoneList(){
  const el=document.getElementById('zone-list');
  if(!geofences.length){el.innerHTML='<div style="color:var(--faint);font-size:12px;text-align:center;padding:16px;">No zones yet.</div>';return;}
  el.innerHTML=geofences.map(f=>`<div class="zone-item"><div class="zone-name">${esc(f.name)}</div><div class="zone-meta"><i class="bi bi-calendar2 me-1"></i>${f.startDate||'—'} ${f.startTime||''}</div>${f.areaSqm?'<div class="zone-meta" style="color:var(--green)"><i class="bi bi-bounding-box me-1"></i>'+parseFloat(f.areaSqm).toFixed(0)+' m²</div>':''}<div class="zone-acts"><button class="zbq" onclick="openQR(${f.id},'${escA(f.name)}')"><i class="bi bi-qr-code"></i></button><button class="zbd" onclick="delLocal('${f.id}')"><i class="bi bi-trash"></i></button></div></div>`).join('');
}
function zoomTo(id){const f=geofences.find(x=>String(x.id)===String(id));if(f&&f.layer){showPage('map');setTimeout(()=>map.fitBounds(f.layer.getBounds()),310);}}
function delLocal(id){const i=geofences.findIndex(x=>String(x.id)===String(id));if(i!==-1){drawn.removeLayer(geofences[i].layer);geofences.splice(i,1);renderZoneList();updateBadge();}}
function clearAll(){if(!confirm('Clear all?'))return;drawn.clearLayers();geofences=[];renderZoneList();setStatus('Cleared.');}

/* EVENTS */
let allEvents=[];
async function loadEvents(){
  try{const r=await fetch('../Backend/get_geofences.php');allEvents=await r.json();renderEvents(allEvents);updateBadge(allEvents.length);
    const sel=document.getElementById('att-filter-event');sel.innerHTML='<option value="">All Events</option>'+allEvents.map(e=>'<option value="'+e.id+'">'+esc(e.name)+'</option>').join('');}
  catch(e){console.error(e);}
}
function filterEvents(){const q=document.getElementById('ev-search').value.toLowerCase();renderEvents(allEvents.filter(f=>f.name.toLowerCase().includes(q)||(f.description||'').toLowerCase().includes(q)));}
function renderEvents(list){
  const el=document.getElementById('event-cards');
  if(!list.length){el.innerHTML='<div class="col-12"><div class="empty"><i class="bi bi-calendar-x"></i><p>No events found.</p></div></div>';return;}
  el.innerHTML=list.map(f=>{const ips=Array.isArray(f.allowed_ips)?f.allowed_ips:(f.allowed_ips?JSON.parse(f.allowed_ips||'[]'):[]);
    return`<div class="col-xl-4 col-lg-6 col-md-6 mb-4"><div class="ev-card">${statusChip(f)}<div class="ev-name">${esc(f.name)}</div><div class="ev-meta"><i class="bi bi-calendar-check"></i>Start: ${f.start_date||'N/A'} ${f.start_time||''}</div><div class="ev-meta"><i class="bi bi-calendar-x"></i>End: ${f.end_date||'N/A'} ${f.end_time||''}</div>${f.area_sqm?'<div class="ev-meta"><i class="bi bi-bounding-box"></i>Area: '+parseFloat(f.area_sqm).toFixed(0)+' m²</div>':''}${ips.length?'<div class="ev-meta"><i class="bi bi-wifi"></i>IPs: '+ips.join(', ')+'</div>':''}<div class="ev-desc">${esc(f.description||'No description')}</div><div class="ev-foot"><button class="btn-ev btn-ev-edit" onclick="openEditEvent(${f.id})"><i class="bi bi-pencil-square me-1"></i>Edit</button><button class="btn-ev btn-ev-qr" onclick="openQR(${f.id},'${escA(f.name)}')"><i class="bi bi-qr-code me-1"></i>QR</button><button class="btn-ev btn-ev-att" onclick="showEventAtt(${f.id},'${escA(f.name)}')"><i class="bi bi-people me-1"></i>Att</button><button class="btn-ev btn-ev-del" onclick="deleteEvent(${f.id})"><i class="bi bi-trash"></i></button></div></div></div>`}).join('');
}
function openEditEvent(id) {
  const f = allEvents.find(x => x.id === id);
  if (!f) { console.error('Event not found:', id); return; }

  // Safely parse allowed_ips regardless of format
  let ips = [];
  try {
    if (Array.isArray(f.allowed_ips)) {
      ips = f.allowed_ips;
    } else if (typeof f.allowed_ips === 'string' && f.allowed_ips.trim()) {
      ips = JSON.parse(f.allowed_ips);
    }
  } catch(e) {
    ips = [];
  }

  document.getElementById('edit-ev-id').value    = f.id;
  document.getElementById('edit-ev-name').value  = f.name || '';
  document.getElementById('edit-ev-sd').value    = f.start_date || '';
  document.getElementById('edit-ev-st').value    = f.start_time || '';
  document.getElementById('edit-ev-ed').value    = f.end_date || '';
  document.getElementById('edit-ev-et').value    = f.end_time || '';
  document.getElementById('edit-ev-ips').value   = ips.join(', ');
  document.getElementById('edit-ev-desc').value  = f.description || '';

  // Reuse existing instance or create new one
  const modalEl = document.getElementById('editEventModal');
  const existing = bootstrap.Modal.getInstance(modalEl);
  if (existing) {
    existing.show();
  } else {
    new bootstrap.Modal(modalEl).show();
  }
}

async function saveEditedEvent() {
  const ipsRaw = document.getElementById('edit-ev-ips').value.trim();
  const ips = ipsRaw ? ipsRaw.split(',').map(s => s.trim()).filter(Boolean) : [];

  const payload = {
    id:          document.getElementById('edit-ev-id').value,
    name:        document.getElementById('edit-ev-name').value,
    startDate:   document.getElementById('edit-ev-sd').value,
    startTime:   document.getElementById('edit-ev-st').value,
    endDate:     document.getElementById('edit-ev-ed').value,
    endTime:     document.getElementById('edit-ev-et').value,
    description: document.getElementById('edit-ev-desc').value,
    allowedIps:  ips
  };

  try {
    const res = await fetch('../Backend/update_geofence.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const r = await res.json();
    if (r.success) {
      const modalEl = document.getElementById('editEventModal');
      const instance = bootstrap.Modal.getInstance(modalEl);
      if (instance) instance.hide();
      loadEvents();
    } else {
      alert(r.message || 'Failed to save.');
    }
  } catch(e) {
    alert('Network error: ' + e.message);
  }
}
/* QR */
function openQR(id,name){
  const base=window.location.href.replace(/[^\/]*$/,'');
  const url=base+'event_login.php?event='+id;
  document.getElementById('qr-url-label').textContent=url;
  const canvas=document.getElementById('qrCanvas');
  const ctx=canvas.getContext('2d');ctx.fillStyle='#060b14';ctx.fillRect(0,0,256,256);
  ctx.fillStyle='#00c8ff';ctx.font='bold 13px Syne,sans-serif';ctx.textAlign='center';ctx.fillText('Scan for event: '+name.slice(0,24),128,30);
  ctx.fillStyle='#7ba3c8';ctx.font='10px monospace';ctx.fillText(url.slice(0,50),128,240);
  // Draw simple QR-like placeholder bars
  for(let i=0;i<8;i++){for(let j=0;j<8;j++){if((i+j)%2===0){ctx.fillStyle='rgba(0,200,255,0.8)';ctx.fillRect(40+i*22,50+j*22,18,18);}}}
  ctx.strokeStyle='#00c8ff';ctx.lineWidth=2;ctx.strokeRect(38,48,178,178);
  new bootstrap.Modal(document.getElementById('qrModal')).show();
}
function downloadQR(){const a=document.createElement('a');a.href=document.getElementById('qrCanvas').toDataURL('image/png');a.download='event-qr.png';a.click();}

/* ATTENDANCE */
let allAtt=[];
async function loadAttendance(){const evId=document.getElementById('att-filter-event').value;try{const r=await fetch('../Backend/get_attendance.php'+(evId?'?event_id='+evId:''));allAtt=await r.json();renderAttendance(allAtt);}catch(e){console.error(e);}}
function filterAttendance(){const q=document.getElementById('att-search').value.toLowerCase();renderAttendance(allAtt.filter(a=>(a.username||'').toLowerCase().includes(q)||(a.event_name||'').toLowerCase().includes(q)));}
function renderAttendance(list){
  const tb=document.getElementById('att-tbody');
  if(!list.length){tb.innerHTML='<tr><td colspan="8" style="text-align:center;color:var(--faint);padding:28px;">No records.</td></tr>';return;}
  tb.innerHTML=list.map(a=>`<tr><td><strong>${esc(a.username||'—')}</strong><br><span style="color:var(--faint);font-size:11px;">${esc(a.course||'')} ${a.year?a.year+'Y':''} ${esc(a.section||'')}</span></td><td>${esc(a.event_name||'—')}</td><td><span style="color:var(--green);">${fmtTime(a.time_in)}</span><br><span style="color:var(--faint);font-size:11px;">${fmtDate(a.time_in)}</span></td><td>${a.time_out?'<span style="color:var(--red);">'+fmtTime(a.time_out)+'</span><br><span style="color:var(--faint);font-size:11px;">'+fmtDate(a.time_out)+'</span>':'<span style="color:var(--amber);">Still inside</span>'}</td><td><span class="dur-pill" style="background:rgba(0,200,255,0.1);color:var(--cyan);">${duration(a.time_in,a.time_out)}</span></td><td style="font-size:11px;color:var(--faint);">${esc(a.ip_address||'—')}</td><td style="font-size:11px;color:var(--faint);">${a.gps_lat?parseFloat(a.gps_lat).toFixed(5)+',<br>'+parseFloat(a.gps_lng).toFixed(5):'—'}</td><td><span style="color:${a.status==='inside'?'var(--green)':a.status==='outside'?'var(--muted)':'var(--amber)'};font-size:11px;font-weight:700;">${(a.status||'—').toUpperCase()}</span></td></tr>`).join('');
}
async function showEventAtt(id,name){document.getElementById('att-modal-title').textContent='Attendance — '+name;try{const r=await fetch('../Backend/get_attendance.php?event_id='+id);const list=await r.json();const tb=document.getElementById('att-modal-tbody');tb.innerHTML=list.length?list.map(a=>`<tr><td><strong>${esc(a.username||'—')}</strong></td><td>${esc(a.course||'—')}</td><td>${esc(a.section||'—')}</td><td style="color:var(--green);">${fmtTime(a.time_in)}</td><td>${a.time_out?'<span style="color:var(--red);">'+fmtTime(a.time_out)+'</span>':'<span style="color:var(--amber);">Inside</span>'}</td><td>${duration(a.time_in,a.time_out)}</td><td style="color:${a.status==='inside'?'var(--green)':'var(--muted)'};">${(a.status||'—').toUpperCase()}</td></tr>`).join(''):'<tr><td colspan="7" style="text-align:center;color:var(--faint);padding:24px;">No check-ins yet.</td></tr>';new bootstrap.Modal(document.getElementById('attDetailModal')).show();}catch(e){alert('Error.');}}

/* STUDENTS */
let allStudents=[];
async function loadStudents(){try{const r=await fetch('../Backend/get_students.php');allStudents=await r.json();renderStudents(allStudents);}catch(e){console.error(e);}}
const CC={BSIT:'linear-gradient(135deg,#0070f3,#00c8ff)',BSCS:'linear-gradient(135deg,#7c3aed,#c084fc)',BSBA:'linear-gradient(135deg,#f97316,#fbbf24)'};
const CB={BSIT:'background:rgba(0,112,243,0.18);color:#60a5fa;',BSCS:'background:rgba(124,58,237,0.18);color:#a78bfa;',BSBA:'background:rgba(249,115,22,0.18);color:#fb923c;'};
let activeCourse='all',activeYear='all';
function filterStudents(c,y){
  activeCourse=c;activeYear=y;
  document.querySelectorAll('.filter-pill').forEach(p=>p.classList.remove('active-f'));
  const cp=document.getElementById('fp-'+(c==='all'?'all':c));if(cp)cp.classList.add('active-f');
  if(y!=='all'){const yp=document.getElementById('fy-'+y);if(yp)yp.classList.add('active-f');}
  renderStudents(allStudents);
}
function renderStudents(all){
  const cont=document.getElementById('students-container');
  const courses=activeCourse==='all'?['BSIT','BSCS','BSBA']:[activeCourse];
  const years=activeYear==='all'?[1,2,3,4]:[parseInt(activeYear)];
  let html='<div style="display:flex;justify-content:flex-end;margin-bottom:16px;"><button class="filter-pill" style="background:linear-gradient(135deg,rgba(0,245,160,0.15),rgba(0,200,255,0.1));border-color:var(--green);color:var(--green);" data-bs-toggle="modal" data-bs-target="#addStudentModal"><i class="bi bi-person-plus me-1"></i>Add Student</button></div>';
  let total=0;
  courses.forEach(course=>{
    const cs=all.filter(s=>s.course===course);if(!cs.length)return;
    html+=`<div class="course-section"><div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border);"><span style="font-family:Syne,sans-serif;font-size:13px;font-weight:800;padding:4px 14px;border-radius:20px;${CB[course]||''}">${course}</span><span style="font-size:12px;color:var(--faint);">${cs.length} student${cs.length!==1?'s':''}</span></div>`;
    years.forEach(yr=>{
      const ys=cs.filter(s=>parseInt(s.year)===yr);if(!ys.length)return;
      const sections=[...new Set(ys.map(s=>s.section||'—'))].sort();
      html+=`<div class="year-section"><div class="year-label">${['1st','2nd','3rd','4th'][yr-1]||yr+'th'} Year</div>`;
      sections.forEach(sec=>{
        const ss=ys.filter(s=>(s.section||'—')===sec);
        html+=`<div class="section-subsection"><div class="section-label">Section ${sec} (${ss.length})</div><div class="row g-2">`;
        ss.forEach(s=>{
          const init=(s.username||'?').split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2);
          html+=`<div class="col-xl-3 col-lg-4 col-md-6 col-sm-12"><div class="student-card"><div class="stu-av" style="background:${CC[s.course]||'#475569'};">${init}</div><div class="stu-info"><div class="stu-name">${esc(s.username)}</div><div class="stu-email">${esc(s.email)}</div></div><button style="padding:5px 8px;background:rgba(255,56,96,0.09);border:1px solid rgba(255,56,96,0.22);color:var(--red);border-radius:7px;cursor:pointer;" onclick="deleteStudent(${s.id})"><i class="bi bi-trash"></i></button></div></div>`;
          total++;
        });
        html+='</div></div>';
      });
      html+='</div>';
    });
    html+='</div>';
  });
  if(!total)html+='<div class="empty"><i class="bi bi-mortarboard"></i><p>No students match.</p></div>';
  cont.innerHTML=html;
}
async function deleteStudent(id){if(!confirm('Delete?'))return;const r=await fetch('../Backend/deleteuser.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'user_id='+id});const t=await r.text();alert(t);loadStudents();}
$('#addStudentForm').submit(async function(e){e.preventDefault();const payload={username:$('#stu-name').val(),email:$('#stu-email').val(),password:$('#stu-password').val(),course:$('#stu-course').val(),year:$('#stu-year').val(),section:$('#stu-section').val(),role:'student'};const r=await fetch('../Backend/add_student.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});const d=await r.json();if(d.success){bootstrap.Modal.getInstance(document.getElementById('addStudentModal')).hide();loadStudents();}else alert(d.message||'Failed.');});

/* DASHBOARD */
let chM,chS,chB,chL,chC,chD;
async function loadDashboard(){
  try{
    const [ev,st]=await Promise.all([fetch('../Backend/get_geofences.php'),fetch('../Backend/get_stats.php')]);
    const events=await ev.json(),stats=await st.json();
    const now=new Date();
    const active=events.filter(e=>now>=new Date(e.start_date+'T'+(e.start_time||'00:00'))&&now<=new Date(e.end_date+'T'+(e.end_time||'23:59')));
    const upcoming=events.filter(e=>new Date(e.start_date+'T'+(e.start_time||'00:00'))>now);
    document.getElementById('s-zones').textContent=active.length;document.getElementById('s-events').textContent=events.length;
    document.getElementById('s-students').textContent=stats.student_count||0;document.getElementById('s-upcoming').textContent=upcoming.length;
    document.getElementById('s-checkins').textContent=stats.total_checkins||0;document.getElementById('s-inside').textContent=stats.active_inside||0;
    const m=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],c=new Array(12).fill(0);
    events.forEach(e=>{if(e.start_date)c[parseInt(e.start_date.split('-')[1])-1]++;});
    const ctx1=document.getElementById('c-monthly').getContext('2d');if(chM)chM.destroy();
    chM=new Chart(ctx1,{type:'bar',data:{labels:m,datasets:[{data:c,backgroundColor:'rgba(0,200,255,0.2)',borderColor:'#00c8ff',borderWidth:2,borderRadius:8,borderSkipped:false}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{grid:{color:'rgba(255,255,255,0.05)'},ticks:{color:'#7ba3c8'},border:{color:'transparent'}},x:{grid:{display:false},ticks:{color:'#7ba3c8'},border:{color:'transparent'}}}}});
    const ctx2=document.getElementById('c-status').getContext('2d');if(chS)chS.destroy();
    chS=new Chart(ctx2,{type:'doughnut',data:{labels:['Active','Upcoming','Past'],datasets:[{data:[active.length,upcoming.length,events.length-active.length-upcoming.length],backgroundColor:['rgba(0,245,160,0.7)','rgba(255,184,0,0.7)','rgba(61,96,128,0.7)'],borderColor:['#00f5a0','#ffb800','#3d6080'],borderWidth:2}]},options:{responsive:true,cutout:'68%',plugins:{legend:{position:'bottom',labels:{color:'#7ba3c8',font:{size:11},padding:12}}}}});
    const feed=document.getElementById('activity-feed');
    feed.innerHTML=[...events].sort((a,b)=>b.id-a.id).slice(0,6).map(e=>'<div class="act-item"><div class="act-dot" style="background:var(--cyan);box-shadow:0 0 5px var(--cyan);"></div><div class="act-body">Zone <strong style="color:var(--text);">'+esc(e.name)+'</strong> created</div><div class="act-time">'+( e.start_date||'—')+'</div></div>').join('')||'<div style="color:var(--faint);font-size:13px;">No activity.</div>';
    document.getElementById('upcoming-list').innerHTML=upcoming.slice(0,5).map(e=>'<div class="act-item"><div class="act-dot" style="background:var(--amber);box-shadow:0 0 5px var(--amber);"></div><div class="act-body"><strong style="color:var(--text);">'+esc(e.name)+'</strong><br><span style="font-size:11px;">'+e.start_date+' '+(e.start_time||'')+'</span></div></div>').join('')||'<div style="color:var(--faint);font-size:13px;">None.</div>';
  }catch(e){console.error(e);}
}

/* ANALYTICS */
async function loadAnalytics(){
  try{
    const [evR,stuR]=await Promise.all([fetch('../Backend/get_geofences.php'),fetch('../Backend/get_students.php')]);
    const events=await evR.json(),students=await stuR.json();
    const now=new Date();
    const active=events.filter(e=>now>=new Date(e.start_date+'T'+(e.start_time||'00:00'))&&now<=new Date(e.end_date+'T'+(e.end_time||'23:59')));
    const upcoming=events.filter(e=>new Date(e.start_date+'T'+(e.start_time||'00:00'))>now);
    const past=events.filter(e=>new Date(e.end_date+'T'+(e.end_time||'23:59'))<now);
    document.getElementById('a-zones').textContent=events.length;document.getElementById('a-active').textContent=active.length;document.getElementById('a-up').textContent=upcoming.length;document.getElementById('a-past').textContent=past.length;
    const m=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],c=new Array(12).fill(0);events.forEach(e=>{if(e.start_date)c[parseInt(e.start_date.split('-')[1])-1]++;});
    const c1=document.getElementById('a-bar').getContext('2d');if(chB)chB.destroy();chB=new Chart(c1,{type:'bar',data:{labels:m,datasets:[{data:c,backgroundColor:'rgba(0,200,255,0.2)',borderColor:'#00c8ff',borderWidth:2,borderRadius:8,borderSkipped:false}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{grid:{color:'rgba(255,255,255,0.05)'},ticks:{color:'#7ba3c8'},border:{color:'transparent'}},x:{grid:{display:false},ticks:{color:'#7ba3c8'},border:{color:'transparent'}}}}});
    const sorted=[...events].sort((a,b)=>(a.start_date||'').localeCompare(b.start_date||''));
    const c2=document.getElementById('a-line').getContext('2d');if(chL)chL.destroy();chL=new Chart(c2,{type:'line',data:{labels:sorted.map(e=>e.start_date||'?'),datasets:[{data:sorted.map((_,i)=>i+1),borderColor:'#00f5a0',backgroundColor:'rgba(0,245,160,0.08)',tension:0.4,fill:true,pointRadius:4,pointBackgroundColor:'#00f5a0'}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{grid:{color:'rgba(255,255,255,0.05)'},ticks:{color:'#7ba3c8'},border:{color:'transparent'}},x:{grid:{display:false},ticks:{color:'#7ba3c8',maxRotation:30},border:{color:'transparent'}}}}});
    const cc={BSIT:0,BSCS:0,BSBA:0,Other:0};students.forEach(s=>{if(cc[s.course]!==undefined)cc[s.course]++;else cc.Other++;});
    const c3=document.getElementById('a-course').getContext('2d');if(chC)chC.destroy();chC=new Chart(c3,{type:'doughnut',data:{labels:Object.keys(cc),datasets:[{data:Object.values(cc),backgroundColor:['rgba(0,112,243,0.7)','rgba(124,58,237,0.7)','rgba(249,115,22,0.7)','rgba(61,96,128,0.7)'],borderWidth:2}]},options:{responsive:true,cutout:'60%',plugins:{legend:{position:'bottom',labels:{color:'#7ba3c8',font:{size:12},padding:12}}}}});
    const bins=[0,1,2,4,8,12,24,48,72,Infinity],lbls=['<1h','1-2h','2-4h','4-8h','8-12h','12-24h','1-2d','2-3d','>3d'],db=new Array(lbls.length).fill(0);
    events.forEach(e=>{if(!e.start_date||!e.end_date)return;const d=(new Date(e.end_date+'T'+(e.end_time||'23:59'))-new Date(e.start_date+'T'+(e.start_time||'00:00')))/3600000;for(let i=0;i<bins.length-1;i++){if(d>=bins[i]&&d<bins[i+1]){db[i]++;break;}}});
    const c4=document.getElementById('a-dur').getContext('2d');if(chD)chD.destroy();chD=new Chart(c4,{type:'bar',data:{labels:lbls,datasets:[{data:db,backgroundColor:'rgba(124,58,237,0.25)',borderColor:'#7c3aed',borderWidth:2,borderRadius:6,borderSkipped:false}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{grid:{color:'rgba(255,255,255,0.05)'},ticks:{color:'#7ba3c8',stepSize:1},border:{color:'transparent'}},x:{grid:{display:false},ticks:{color:'#7ba3c8'},border:{color:'transparent'}}}}});
  }catch(e){console.error(e);}
}
/*DRAW POLY*/
function startDrawPoly(){
  new L.Draw.Polygon(map, drawCtrl.options.draw.polygon).enable();
}
let adminMarker = null;
/*VIEW LOCATION*/
function viewAdminLocation() {
  if (!navigator.geolocation) {
    Swal.fire({
      icon: 'error', title: 'Not Supported',
      text: 'Geolocation is not supported by your browser.',
      background: '#0a1628', color: '#e8f4ff', confirmButtonColor: '#00c8ff'
    });
    return;
  }

  Swal.fire({
    title: 'Locating you…',
    text: 'Fetching your current GPS position.',
    background: '#0a1628', color: '#e8f4ff',
    didOpen: () => Swal.showLoading(),
    allowOutsideClick: false,
    showConfirmButton: false
  });

  navigator.geolocation.getCurrentPosition(
    pos => {
      Swal.close();
      const lat = pos.coords.latitude, lng = pos.coords.longitude;

      if (adminMarker) map.removeLayer(adminMarker);

      const pulseIcon = L.divIcon({
        className: '',
        html: `<div style="
          width:18px;height:18px;
          background:#00c8ff;
          border:3px solid white;
          border-radius:50%;
          box-shadow:0 0 0 6px rgba(0,200,255,0.25),0 0 18px rgba(0,200,255,0.6);
          animation:pdot 1.5s infinite;
        "></div>`,
        iconSize: [18, 18],
        iconAnchor: [9, 9]
      });

      adminMarker = L.marker([lat, lng], { icon: pulseIcon })
        .addTo(map)
        .bindPopup(`
          <div style="font-family:'Syne',sans-serif;font-size:13px;color:#0a1628;">
            <strong>📍 Your Location</strong><br>
            <span style="font-size:11px;">Lat: ${lat.toFixed(6)}</span><br>
            <span style="font-size:11px;">Lng: ${lng.toFixed(6)}</span><br>
            <span style="font-size:10px;color:#666;">Accuracy: ±${pos.coords.accuracy.toFixed(0)}m</span>
          </div>
        `, { maxWidth: 200 })
        .openPopup();

      setTimeout(() => map.invalidateSize(), 310);
      map.flyTo([lat, lng], 17, { animate: true, duration: 1.4 });
    },
    err => {
      const msgs = {
        1: 'Location permission denied. Please allow access in your browser settings.',
        2: 'Position unavailable. Check your device GPS.',
        3: 'Location request timed out. Try again.'
      };
      Swal.fire({
        icon: 'error', title: 'Location Error',
        text: msgs[err.code] || 'Unknown error occurred.',
        background: '#0a1628', color: '#e8f4ff', confirmButtonColor: '#00c8ff'
      });
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  );
}
/* BADGE */
function updateBadge(n){const b=document.getElementById('ev-badge');if(n!==undefined){b.textContent=n;return;}fetch('../Backend/get_geofences.php').then(r=>r.json()).then(d=>b.textContent=d.length).catch(()=>{});}

/* ADMIN CRUD */
$(document).on('click','.edit-btn',function(){$('#editUserId').val($(this).data('id'));$('#editUserEmail').val($(this).data('email'));$('#editUserPassword').val('');});
$('#editForm').submit(function(e){e.preventDefault();$.ajax({url:'../Backend/updateuser.php',method:'POST',data:{user_id:$('#editUserId').val(),email:$('#editUserEmail').val(),password:$('#editUserPassword').val()},success:r=>{alert(r);location.reload();},error:()=>alert('Failed.')});});
$(document).on('click','.delete-btn',function(){$('#deleteUserId').val($(this).data('id'));});
$('#confirmDelete').click(function(){const id=$('#deleteUserId').val();$.ajax({url:'../Backend/deleteuser.php',method:'POST',data:{user_id:id},success:r=>{alert(r);$('#user-'+id).remove();$('#deleteModal').modal('hide');},error:()=>alert('Failed.')});});

window.addEventListener('resize',()=>{if(document.getElementById('page-map').classList.contains('active'))setTimeout(()=>map.invalidateSize(),300);});
loadDashboard();updateBadge();
</script>
</body>
</html>
