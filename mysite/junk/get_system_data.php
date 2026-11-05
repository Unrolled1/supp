<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_GET['system_id']) || empty($_GET['system_id'])) {
    echo json_encode(['error' => 'system_id required']);
    exit;
}

$system_id = filter_var($_GET['system_id'], FILTER_VALIDATE_INT);
$type = $_GET['type'] ?? 'all'; // all, rams, storages, ips, peripherals

$db = getDB();
$response = [];

switch ($type) {
    case 'rams':
        // دریافت رم‌های سیستم
        $stmt = $db->prepare("
            SELECT 
                sr.*,
                r.type, r.capacity,
                rm.name as model_name,
                rb.name as brand_name
            FROM system_rams sr
            LEFT JOIN rams r ON sr.ram_id = r.id
            LEFT JOIN models rm ON r.model_id = rm.id
            LEFT JOIN brands rb ON rm.brand_id = rb.id
            WHERE sr.system_id = :system_id
            ORDER BY sr.id DESC
        ");
        $stmt->execute([':system_id' => $system_id]);
        $response = $stmt->fetchAll();
        break;

    case 'storages':
        // دریافت هاردهای سیستم
        $stmt = $db->prepare("
            SELECT 
                ss.*,
                st.type, st.capacity,
                sm.name as model_name,
                sb.name as brand_name
            FROM system_storages ss
            LEFT JOIN storages st ON ss.storage_id = st.id
            LEFT JOIN models sm ON st.model_id = sm.id
            LEFT JOIN brands sb ON sm.brand_id = sb.id
            WHERE ss.system_id = :system_id
            ORDER BY ss.id DESC
        ");
        $stmt->execute([':system_id' => $system_id]);
        $response = $stmt->fetchAll();
        break;

    case 'ips':
        // دریافت IPهای سیستم
        $stmt = $db->prepare("
            SELECT * FROM system_ips 
            WHERE system_id = :system_id 
            ORDER BY id DESC
        ");
        $stmt->execute([':system_id' => $system_id]);
        $response = $stmt->fetchAll();
        break;

    case 'peripherals':
        // دریافت تجهیزات جانبی سیستم
        $stmt = $db->prepare("
            SELECT 
                sp.*,
                p.computer_code, p.property_code,
                pt.name as type_name, 
                pm.name as model_name,
                pb.name as brand_name
            FROM system_peripherals sp
            LEFT JOIN peripherals p ON sp.peripheral_id = p.id
            LEFT JOIN peripheral_types pt ON p.type_id = pt.id
            LEFT JOIN models pm ON p.model_id = pm.id
            LEFT JOIN brands pb ON pm.brand_id = pb.id
            WHERE sp.system_id = :system_id
            ORDER BY pt.sort_order ASC
        ");
        $stmt->execute([':system_id' => $system_id]);
        $response = $stmt->fetchAll();
        break;

    case 'all':
    default:
        // دریافت همه اطلاعات
        $rams = $db->prepare("
            SELECT 
                sr.*,
                r.type, r.capacity,
                rm.name as model_name,
                rb.name as brand_name
            FROM system_rams sr
            LEFT JOIN rams r ON sr.ram_id = r.id
            LEFT JOIN models rm ON r.model_id = rm.id
            LEFT JOIN brands rb ON rm.brand_id = rb.id
            WHERE sr.system_id = :system_id
            ORDER BY sr.id DESC
        ");
        $rams->execute([':system_id' => $system_id]);
        $response['rams'] = $rams->fetchAll();

        $storages = $db->prepare("
            SELECT 
                ss.*,
                st.type, st.capacity,
                sm.name as model_name,
                sb.name as brand_name
            FROM system_storages ss
            LEFT JOIN storages st ON ss.storage_id = st.id
            LEFT JOIN models sm ON st.model_id = sm.id
            LEFT JOIN brands sb ON sm.brand_id = sb.id
            WHERE ss.system_id = :system_id
            ORDER BY ss.id DESC
        ");
        $storages->execute([':system_id' => $system_id]);
        $response['storages'] = $storages->fetchAll();

        $ips = $db->prepare("
            SELECT * FROM system_ips 
            WHERE system_id = :system_id 
            ORDER BY id DESC
        ");
        $ips->execute([':system_id' => $system_id]);
        $response['ips'] = $ips->fetchAll();

        $peripherals = $db->prepare("
            SELECT 
                sp.*,
                p.computer_code, p.property_code, 
                pm.name as model_name,
                pb.name as brand_name
            FROM system_peripherals sp
            LEFT JOIN peripherals p ON sp.peripheral_id = p.id
            LEFT JOIN peripheral_types pt ON p.type_id = pt.id
            LEFT JOIN models pm ON p.model_id = pm.id
            LEFT JOIN brands pb ON pm.brand_id = pb.id
            WHERE sp.system_id = :system_id
            ORDER BY pt.sort_order ASC
        ");
        $peripherals->execute([':system_id' => $system_id]);
        $response['peripherals'] = $peripherals->fetchAll();
        break;
}

echo json_encode($response);
?>