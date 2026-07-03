
<?php
session_start();
require_once "../../db.php";
require_once "../../functions.php";
require_once '../../assets/jdf.php';
$db = getDB();
header("Content-Type: application/json; charset=utf-8");

try {

    if (!isset($_POST['edit_system'])) {
        throw new Exception("درخواست نامعتبر است.");
    }

    if (!canEditSystems()) {
        throw new Exception("دسترسی ندارید.");
    }
    $db->beginTransaction();

    $system_id = filter_var($_POST['system_id'], FILTER_VALIDATE_INT);
    $computer_code = htmlspecialchars(trim($_POST['computer_code']));
    $property_code = htmlspecialchars(trim($_POST['property_code']));
    $name = htmlspecialchars(trim($_POST['name']));
    $department_id = !empty($_POST['department_id']) ? filter_var($_POST['department_id'], FILTER_VALIDATE_INT) : null;
    $cpu_id = !empty($_POST['cpu_id']) ? filter_var($_POST['cpu_id'], FILTER_VALIDATE_INT) : null;
    $motherboard_id = !empty($_POST['motherboard_id']) ? filter_var($_POST['motherboard_id'], FILTER_VALIDATE_INT) : null;
    $power_id = !empty($_POST['power_id']) ? filter_var($_POST['power_id'], FILTER_VALIDATE_INT) : null;
    $monitor_id = !empty($_POST['monitor_id']) ? filter_var($_POST['monitor_id'], FILTER_VALIDATE_INT) : null;


// بروزرسانی سیستم
    $updateStmt = $db->prepare("
UPDATE systems SET
computer_code = :computer_code,
property_code = :property_code,
name = :name,
department_id = :department_id,
cpu_id = :cpu_id,
motherboard_id = :motherboard_id,
power_id = :power_id,
monitor_id = :monitor_id
WHERE id = :id
");

    $updateStmt->execute([
        ':computer_code' => $computer_code,
        ':property_code' => $property_code,
        ':name' => $name,
        ':department_id' => $department_id,
        ':cpu_id' => $cpu_id,
        ':motherboard_id' => $motherboard_id,
        ':power_id' => $power_id,
        ':monitor_id' => $monitor_id,

        ':id' => $system_id
    ]);

// حذف رم‌های قبلی
    $deleteRams = $db->prepare("DELETE FROM system_rams WHERE system_id=?");
    $deleteRams->execute([$system_id]);

    $insertRam = $db->prepare("
INSERT INTO system_rams
(system_id,ram_id,created_at,created_by)
VALUES (?,?,?,?)
");

    foreach ($_POST as $key => $value) {

        if (strpos($key, "edit_ram_id_") === 0) {

            $ram_id = (int)$value;

            if ($ram_id > 0) {

                $insertRam->execute([
                    $system_id,
                    $ram_id,
                    jdate('Y/m/d'),
                    $_SESSION['user_id']
                ]);

            }
        }
    }

    // حذف هاردهای قبلی
    $deleteStorages = $db->prepare("DELETE FROM system_storages WHERE system_id=?");
    $deleteStorages->execute([$system_id]);

// آماده‌سازی دستور درج
    $insertStorage = $db->prepare("
INSERT INTO system_storages
(system_id, storage_id, created_at, created_by)
VALUES (?,?,?,?)
");

    foreach ($_POST as $key => $value) {

        if (strpos($key, 'edit_storage_id_') === 0) {

            $storage_id = (int)$value;

            if ($storage_id > 0) {

                $insertStorage->execute([
                    $system_id,
                    $storage_id,
                    jdate('Y/m/d'),
                    $_SESSION['user_id']
                ]);

            }
        }

    }

    // ============================================
// حذف IPهای قبلی
// ============================================

    $deleteIps = $db->prepare("
    DELETE FROM system_ips
    WHERE system_id = ?
");
    $deleteIps->execute([$system_id]);


// ============================================
// درج IPهای جدید
// ============================================

    $insertIp = $db->prepare("
    INSERT INTO system_ips
    (
        system_id,
        ip_address,
        network_type,
        description,
        created_at,
        created_by
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?
    )
");

    foreach ($_POST as $key => $value) {

        if (strpos($key, 'edit_ip_address_') === 0) {

            $index = str_replace('edit_ip_address_', '', $key);

            $ip_address = trim($value);

            if ($ip_address == '') {
                continue;
            }

            $network_type = $_POST["edit_ip_network_$index"] ?? 'LAN';

            $description = trim($_POST["edit_ip_description_$index"] ?? '');

            $insertIp->execute([
                $system_id,
                $ip_address,
                $network_type,
                htmlspecialchars($description),
                jdate('Y/m/d'),
                $_SESSION['user_id']
            ]);

        }

    }

// ============================================
// حذف تجهیزات جانبی قبلی
// ============================================

    $deletePeriph = $db->prepare("
    DELETE FROM system_peripherals
    WHERE system_id = ?
");

    $deletePeriph->execute([$system_id]);


// ============================================
// درج تجهیزات جانبی جدید
// ============================================

    $insertPeriph = $db->prepare("
    INSERT INTO system_peripherals
    (
        system_id,
        peripheral_id,
        created_at,
        created_by
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?
    )
");

    foreach ($_POST as $key => $value) {

        if (strpos($key, 'edit_peripheral_id_') === 0) {

            $peripheral_id = filter_var($value, FILTER_VALIDATE_INT);

            if (!$peripheral_id) {
                continue;
            }

            $insertPeriph->execute([
                $system_id,
                $peripheral_id,
                jdate('Y/m/d'),
                $_SESSION['user_id']
            ]);

        }

    }
    // ثبت نهایی
$db->commit();

    $system = $db->prepare("
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
mon.property_code AS monitor_property_code

FROM systems s

LEFT JOIN departments d ON s.department_id=d.id

LEFT JOIN cpus cpu ON s.cpu_id=cpu.id
LEFT JOIN models cpu_m ON cpu.model_id=cpu_m.id
LEFT JOIN brands cpu_b ON cpu_m.brand_id=cpu_b.id

LEFT JOIN motherboards mb ON s.motherboard_id=mb.id
LEFT JOIN models mb_m ON mb.model_id=mb_m.id
LEFT JOIN brands mb_b ON mb_m.brand_id=mb_b.id

LEFT JOIN powers p ON s.power_id=p.id
LEFT JOIN models p_m ON p.model_id=p_m.id
LEFT JOIN brands p_b ON p_m.brand_id=p_b.id

LEFT JOIN monitors mon ON s.monitor_id=mon.id
LEFT JOIN models mon_m ON mon.model_id=mon_m.id
LEFT JOIN brands mon_b ON mon_m.brand_id=mon_b.id

WHERE s.id=?
");

    $system->execute([$system_id]);

    $data = $system->fetch(PDO::FETCH_ASSOC);

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
    $data['rams']=$stmt->fetchAll(PDO::FETCH_ASSOC);

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
    $data['storages']=$stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("
SELECT
ip_address,
network_type,
description
FROM system_ips
WHERE system_id=?
");

    $stmt->execute([$system_id]);
    $data['ips']=$stmt->fetchAll(PDO::FETCH_ASSOC);


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
    $data['peripherals']=$stmt->fetchAll(PDO::FETCH_ASSOC);



    echo json_encode([
        "success" => true,
        "system" => [
            "id" => $system_id
        ]
    ]);
    exit;

} catch (Exception $e) {

    if ($db->inTransaction()) {
        $db->rollBack();
    }

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

    exit;

}

?>
