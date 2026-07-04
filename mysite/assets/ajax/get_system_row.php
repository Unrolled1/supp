<?php
session_start();
require_once "../../db.php";
require_once "../../assets/jdf.php";
require_once "../../functions.php";

$system_id = (int)($_GET['id'] ?? 0);

if (!$system_id) {
    exit;
}

$db = getDB();

// دریافت سیستم‌ها با اطلاعات کامل
$systems = $db->prepare("
    SELECT 
        s.*,
        d.name as department_name,
        
        -- CPU
        cpu_b.name as cpu_brand,
        cpu_m.name as cpu_model,
        
        -- مادربرد
        mb_b.name as motherboard_brand,
        mb_m.name as motherboard_model,
        
        -- پاور
        p_b.name as power_brand,
        p_m.name as power_model,
        
        -- مانیتور
        mon_b.name as monitor_brand,
        mon_m.name as monitor_model,
        mon.property_code as monitor_property_code,
        
        u.fullname as creator_name
        
    FROM systems s
    LEFT JOIN departments d ON s.department_id = d.id
    
    LEFT JOIN cpus cpu ON s.cpu_id = cpu.id
    LEFT JOIN models cpu_m ON cpu.model_id = cpu_m.id
    LEFT JOIN brands cpu_b ON cpu_m.brand_id = cpu_b.id
    
    LEFT JOIN motherboards mb ON s.motherboard_id = mb.id
    LEFT JOIN models mb_m ON mb.model_id = mb_m.id
    LEFT JOIN brands mb_b ON mb_m.brand_id = mb_b.id
    
    LEFT JOIN powers p ON s.power_id = p.id
    LEFT JOIN models p_m ON p.model_id = p_m.id
    LEFT JOIN brands p_b ON p_m.brand_id = p_b.id
    
    LEFT JOIN monitors mon ON s.monitor_id = mon.id
    LEFT JOIN models mon_m ON mon.model_id = mon_m.id
    LEFT JOIN brands mon_b ON mon_m.brand_id = mon_b.id
    
    LEFT JOIN users u ON s.created_by = u.id
    
WHERE s.id = :id

");

$systems->execute([
    ':id' => $system_id
]);
$system = $systems->fetch(PDO::FETCH_ASSOC);

if (!$system) {
    exit('سیستم پیدا نشد');
}

// دریافت اطلاعات چندگانه هر سیستم

    // دریافت رم‌ها
    $ramStmt = $db->prepare("
        SELECT 
            sr.*,
            r.type, r.capacity,
            rm.name as model_name,
            rb.name as brand_name
        FROM system_rams sr
        INNER JOIN rams r ON sr.ram_id = r.id
        INNER JOIN models rm ON r.model_id = rm.id
        INNER JOIN brands rb ON rm.brand_id = rb.id
        WHERE sr.system_id = :system_id
        ORDER BY sr.id DESC
    ");
    $ramStmt->execute([':system_id' => $system['id']]);
$system['rams'] = $ramStmt->fetchAll();

    // دریافت هاردها
    $storageStmt = $db->prepare("
        SELECT 
            ss.*,
            st.type, st.capacity,
            sm.name as model_name,
            sb.name as brand_name
        FROM system_storages ss
        INNER JOIN storages st ON ss.storage_id = st.id
        INNER JOIN models sm ON st.model_id = sm.id
        INNER JOIN brands sb ON sm.brand_id = sb.id
        WHERE ss.system_id = :system_id
        ORDER BY ss.id DESC
    ");
    $storageStmt->execute([':system_id' => $system['id']]);
    $system['storages'] = $storageStmt->fetchAll();

    // دریافت IPها
    $ipStmt = $db->prepare("
        SELECT * FROM system_ips 
        WHERE system_id = :system_id 
        ORDER BY id DESC
    ");
    $ipStmt->execute([':system_id' => $system['id']]);
    $system['ips'] = $ipStmt->fetchAll();

    // دریافت تجهیزات جانبی
    $periphStmt = $db->prepare("
        SELECT 
            sp.*,
            p.computer_code, p.property_code,
            pt.name as type_name,
            pm.name as model_name,
            pb.name as brand_name
        FROM system_peripherals sp
        INNER JOIN peripherals p ON sp.peripheral_id = p.id
        INNER JOIN peripheral_types pt ON p.type_id = pt.id
        INNER JOIN models pm ON p.model_id = pm.id
        INNER JOIN brands pb ON pm.brand_id = pb.id
        WHERE sp.system_id = :system_id
        ORDER BY pt.sort_order ASC
    ");
    $periphStmt->execute([':system_id' => $system['id']]);
    $system['peripherals'] = $periphStmt->fetchAll();

$rownum = 1;
$rowData=$system;
include "../includes/system_row.php";
