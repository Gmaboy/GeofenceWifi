<?php
header('Content-Type: application/json');
include '../Database/db.php';

function getRealIP(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

function ipInCIDR(string $ip, string $cidr): bool {
    $cidr = trim($cidr);
    if (strpos($cidr,'/') === false) return $ip === $cidr;
    list($subnet, $bits) = explode('/', $cidr, 2);
    $bits = (int)$bits;
    $ipL  = ip2long($ip);
    $subL = ip2long($subnet);
    if ($ipL === false || $subL === false) return false;
    $mask = $bits === 0 ? 0 : (~0 << (32 - $bits));
    return ($ipL & $mask) === ($subL & $mask);
}

$clientIp = getRealIP();
$event_id = (int)($_GET['event'] ?? 0);
$allowed  = false;
$reason   = '';

if ($event_id) {
    $r = $conn->query("SELECT allowed_ips, require_ip FROM geofences WHERE id=$event_id LIMIT 1");
    if ($r && $row = $r->fetch_assoc()) {
        if (!(int)$row['require_ip']) {
            $allowed = true; $reason = 'IP check not required';
        } else {
            $ranges = json_decode($row['allowed_ips'] ?? '[]', true) ?: [];
            if (empty($ranges)) {
                $allowed = true; $reason = 'No IP restrictions set';
            } else {
                foreach ($ranges as $range) {
                    if (ipInCIDR($clientIp, $range)) { $allowed = true; $reason = 'Matched: '.$range; break; }
                }
                if (!$allowed) $reason = 'IP not in allowed ranges';
            }
        }
    } else { $reason = 'Event not found'; }
} else { $allowed = true; $reason = 'No event context'; }

echo json_encode(['ip' => $clientIp, 'allowed' => $allowed, 'reason' => $reason, 'event_id' => $event_id]);
$conn->close();
