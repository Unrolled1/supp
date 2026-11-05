<?php
session_start();
require_once '../db.php';
require_once '../functions.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('access denied');
}

$system_id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
if (!$system_id) {
    http_response_code(400);
    exit('invalid id');
}

// شماره ردیف (برای حفظ شماره قبلی بعد از ویرایش)
$rownum = trim((string)($_GET['rownum'] ?? '-'));

$db = getDB();

// ============================================
// اطلاعات اصلی سیستم
// ============================================

$stmt = $db->prepare("
SELECT
    s.*,
    d.name AS department_name,

    cpu_b.name AS cpu_brand,
    cpu_m.name AS cpu_model,

    mb_b.name AS motherboard_brand,
    mb_m.name AS motherboard_model,

    p_b.name AS power_brand,
    p_m.name AS power_model,

    mon_b.name AS monitor_brand,
    mon_m.name AS monitor_model,
    mon.property_code AS monitor_property_code,

    u.fullname AS creator_name

FROM systems s

LEFT JOIN departments d
    ON s.department_id = d.id

LEFT JOIN cpus cpu
    ON s.cpu_id = cpu.id
LEFT JOIN models cpu_m
    ON cpu.model_id = cpu_m.id
LEFT JOIN brands cpu_b
    ON cpu_m.brand_id = cpu_b.id

LEFT JOIN motherboards mb
    ON s.motherboard_id = mb.id
LEFT JOIN models mb_m
    ON mb.model_id = mb_m.id
LEFT JOIN brands mb_b
    ON mb_m.brand_id = mb_b.id

LEFT JOIN powers p
    ON s.power_id = p.id
LEFT JOIN models p_m
    ON p.model_id = p_m.id
LEFT JOIN brands p_b
    ON p_m.brand_id = p_b.id

LEFT JOIN monitors mon
    ON s.monitor_id = mon.id
LEFT JOIN models mon_m
    ON mon.model_id = mon_m.id
LEFT JOIN brands mon_b
    ON mon_m.brand_id = mon_b.id

LEFT JOIN users u
    ON s.created_by = u.id

WHERE s.id = ?
");
$stmt->execute([$system_id]);
$rowData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rowData) {
    http_response_code(404);
    exit('not found');
}

// ============================================
// رم‌ها
// ============================================

$stmt = $db->prepare("
SELECT
r.type,
r.capacity,
rm.name AS model_name,
rb.name AS brand_name
FROM system_rams sr
JOIN rams r ON sr.ram_id=r.id
JOIN models rm ON r.model_id=rm.id
JOIN brands rb ON rm.brand_id=rb.id
WHERE sr.system_id=?
");
$stmt->execute([$system_id]);
$rowData['rams'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// هاردها
// ============================================

$stmt = $db->prepare("
SELECT
st.type,
st.capacity,
sm.name AS model_name,
sb.name AS brand_name
FROM system_storages ss
JOIN storages st ON ss.storage_id=st.id
JOIN models sm ON st.model_id=sm.id
JOIN brands sb ON sm.brand_id=sb.id
WHERE ss.system_id=?
");
$stmt->execute([$system_id]);
$rowData['storages'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// IP ها (با MAC)
// ============================================

$stmt = $db->prepare("
SELECT
ip_address,
mac_address,
network_type,
description
FROM system_ips
WHERE system_id=?
ORDER BY id DESC
");
$stmt->execute([$system_id]);
$rowData['ips'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// تجهیزات جانبی
// ============================================

$stmt = $db->prepare("
SELECT
pt.name AS type_name,
pb.name AS brand_name,
pm.name AS model_name,
p.property_code
FROM system_peripherals sp
JOIN peripherals p ON sp.peripheral_id=p.id
JOIN peripheral_types pt ON p.type_id=pt.id
JOIN models pm ON p.model_id=pm.id
JOIN brands pb ON pm.brand_id=pb.id
WHERE sp.system_id=?
ORDER BY pt.sort_order
");
$stmt->execute([$system_id]);
$rowData['peripherals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// رندر ردیف جدول
// ============================================

include __DIR__ . '/system_row.php';
