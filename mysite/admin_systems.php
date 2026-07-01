<?php
session_start();
require_once 'config/config.php';
require_once 'db.php';
require_once 'assets/jdf.php';
require_once 'functions.php';

date_default_timezone_set('Asia/Tehran');

// مدیریت پیام‌ها
$successMessage = '';
$errorMessage = '';

if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// بررسی لاگین
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isAdmin() || !canViewSystems()) {
    header('Location: requests.php');
    exit;
}

$db = getDB();

// ============================================
// گرفتن لیست‌های مورد نیاز برای فرم‌ها
// ============================================

// بخش‌ها
$departments = $db->query("SELECT id, name FROM departments WHERE status = 'active' ORDER BY name ASC")->fetchAll();

// برندها
$brands = $db->query("SELECT id, name FROM brands ORDER BY name ASC")->fetchAll();

// مدل‌ها
$models = $db->query("SELECT id, name FROM models ORDER BY name ASC")->fetchAll();

// CPUها
$cpus = $db->query("
    SELECT c.*, m.name AS model_name, b.name AS brand_name
    FROM cpus c
    INNER JOIN models m ON c.model_id = m.id
    INNER JOIN brands b ON m.brand_id = b.id
    ORDER BY b.name, m.name
")->fetchAll();

// مادربردها
$motherboards = $db->query("
    SELECT mb.*, m.name as model_name, b.name as brand_name 
    FROM motherboards mb
    INNER JOIN models m ON mb.model_id = m.id
    INNER JOIN brands b ON m.brand_id = b.id
    ORDER BY b.name, m.name
")->fetchAll();

// رم‌ها
$rams = $db->query("
    SELECT r.*, m.name as model_name, b.name as brand_name 
    FROM rams r
    INNER JOIN models m ON r.model_id = m.id
    INNER JOIN brands b ON m.brand_id = b.id
    ORDER BY b.name, m.name
")->fetchAll();

// هاردها
$storages = $db->query("
    SELECT s.*, m.name as model_name, b.name as brand_name 
    FROM storages s
    INNER JOIN models m ON s.model_id = m.id
    INNER JOIN brands b ON m.brand_id = b.id
    ORDER BY b.name, m.name
")->fetchAll();

// پاورها
$powers = $db->query("
    SELECT p.*, m.name as model_name, b.name as brand_name 
    FROM powers p
    INNER JOIN models m ON p.model_id = m.id
    INNER JOIN brands b ON m.brand_id = b.id
    ORDER BY b.name, m.name
")->fetchAll();

// مانیتورها
$monitors = $db->query("
    SELECT mon.*, m.name as model_name, b.name as brand_name 
    FROM monitors mon
    INNER JOIN models m ON mon.model_id = m.id
    INNER JOIN brands b ON m.brand_id = b.id
    ORDER BY b.name, m.name
")->fetchAll();

// انواع تجهیزات جانبی
$peripheralTypes = $db->query("SELECT * FROM peripheral_types ORDER BY sort_order")->fetchAll();

// ============================================
// پردازش فرم حذف
// ============================================

if (isset($_POST['delete_system']) && canDeleteSystems()) {
    $system_id = filter_var($_POST['system_id'], FILTER_VALIDATE_INT);

    if ($system_id) {
        $deleteStmt = $db->prepare("DELETE FROM systems WHERE id = :id");
        $deleteStmt->execute([':id' => $system_id]);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $system_id]);
            exit;
        }
    }
    header('Location: admin_systems.php');
    exit;
}
// ============================================
// پردازش افزودن قطعه جدید (AJAX)
// ============================================

if (isset($_POST['add_component']) && canEditSystems()) {
    $componentType = $_POST['component_type'];
    $brand_id = filter_var($_POST['brand_id'], FILTER_VALIDATE_INT);
    $model_id = filter_var($_POST['model_id'], FILTER_VALIDATE_INT);
    $jalaliDate = jdate('Y-m-d');

    $tableMap = [
        'cpu' => 'cpus',
        'motherboard' => 'motherboards',
        'ram' => 'rams',
        'storage' => 'storages',
        'power' => 'powers',
        'monitor' => 'monitors'
    ];

    $table = $tableMap[$componentType];

    // فیلدهای پایه
    $fields = ['brand_id', 'model_id',  'created_at'];
    $values = [
        ':brand_id' => $brand_id,
        ':model_id' => $model_id,
        ':created_at' => $jalaliDate
    ];

    // فیلدهای اختصاصی
    switch ($componentType) {
        case 'ram':
            $fields[] = 'type';
            $fields[] = 'capacity';
            $values[':type'] = htmlspecialchars(trim($_POST['ram_type'] ?? ''));
            $values[':capacity'] = htmlspecialchars(trim($_POST['ram_capacity'] ?? ''));
            break;

        case 'storage':
            $fields[] = 'type';
            $fields[] = 'capacity';
            $values[':type'] = htmlspecialchars(trim($_POST['storage_type'] ?? ''));
            $values[':capacity'] = htmlspecialchars(trim($_POST['storage_capacity'] ?? ''));
            break;

        case 'monitor':
            $fields[] = 'property_code';
            $values[':property_code'] = htmlspecialchars(trim($_POST['monitor_property_code'] ?? ''));
            break;

    }

    // ساخت کوئری
    $fieldList = implode(', ', $fields);
    $valueList = implode(', ', array_keys($values));

    $stmt = $db->prepare("INSERT INTO $table ($fieldList) VALUES ($valueList)");
    $stmt->execute($values);

    $component_id = $db->lastInsertId();

    // دریافت اطلاعات برند و مدل
    $infoStmt = $db->prepare("
        SELECT b.name as brand_name, m.name as model_name 
        FROM brands b, models m 
        WHERE b.id = :brand_id AND m.id = :model_id
    ");
    $infoStmt->execute([':brand_id' => $brand_id, ':model_id' => $model_id]);
    $info = $infoStmt->fetch();

    $displayName = $info['brand_name'] . ' ' . $info['model_name'];

    // اطلاعات اضافی برای رم و هارد
    if ($componentType === 'ram') {
        $displayName .= ' (' . ($_POST['ram_capacity'] ?? '') . ' ' . ($_POST['ram_type'] ?? '') . ')';
    } elseif ($componentType === 'storage') {
        $displayName .= ' (' . ($_POST['storage_capacity'] ?? '') . ' ' . ($_POST['storage_type'] ?? '') . ')';
    } elseif ($componentType === 'monitor' && !empty($_POST['monitor_property_code'])) {
        $displayName .= ' (' . $_POST['monitor_property_code'] . ')';
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'id' => $component_id,
        'display_name' => $displayName
    ]);
    exit;
}
// ============================================
// پردازش افزودن تجهیز جانبی جدید (AJAX)
// ============================================

if (isset($_POST['add_peripheral']) && canEditSystems()) {
    $type_id = filter_var($_POST['type_id'], FILTER_VALIDATE_INT);
    $brand_id = !empty($_POST['brand_id']) ? filter_var($_POST['brand_id'], FILTER_VALIDATE_INT) : null;
    $model_id = !empty($_POST['model_id']) ? filter_var($_POST['model_id'], FILTER_VALIDATE_INT) : null;
    $computer_code = htmlspecialchars(trim($_POST['computer_code'] ?? ''));
    $property_code = htmlspecialchars(trim($_POST['property_code'] ?? ''));
    $connection_type = htmlspecialchars(trim($_POST['connection_type'] ?? 'USB'));
    $jalaliDate = jdate('Y-m-d');

    // ثبت در جدول peripherals (بدون serial_number و description)
    $stmt = $db->prepare("
        INSERT INTO peripherals (
            type_id, brand_id, model_id, computer_code, property_code, connection_type, created_at, created_by
        ) VALUES (
            :type_id, :brand_id, :model_id, :computer_code, :property_code, :connection_type, :created_at, :created_by
        )
    ");

    $stmt->execute([
        ':type_id' => $type_id,
        ':brand_id' => $brand_id,
        ':model_id' => $model_id,
        ':computer_code' => $computer_code,
        ':property_code' => $property_code,
        ':connection_type' => $connection_type,
        ':created_at' => $jalaliDate,
        ':created_by' => $_SESSION['user_id']
    ]);

    $peripheral_id = $db->lastInsertId();

    // دریافت نام برای نمایش
    $infoStmt = $db->prepare("
        SELECT pt.name as type_name,  b.name as brand_name, m.name as model_name
        FROM peripherals p
        INNER JOIN peripheral_types pt ON p.type_id = pt.id
        INNER JOIN brands b ON p.brand_id = b.id
        INNER JOIN models m ON p.model_id = m.id
        WHERE p.id = :id
    ");
    $infoStmt->execute([':id' => $peripheral_id]);
    $info = $infoStmt->fetch();

    $displayName = trim(($info['brand_name'] ?? '') . ' ' . ($info['model_name'] ?? ''));

    if (!empty($property_code)) {
        $displayName .= " - {$property_code}";
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'id' => $peripheral_id,
        'display_name' => $displayName
    ]);
    exit;
}
// ============================================
// پردازش فرم ویرایش
// ============================================

if (isset($_POST['edit_system']) && canEditSystems()) {
    try {
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
        $deleteRams = $db->prepare("DELETE FROM system_rams WHERE system_id = :system_id");
        $deleteRams->execute([':system_id' => $system_id]);

        // ذخیره رم‌های جدید
        $ramIndex = 0;
        while (isset($_POST["ram_id_$ramIndex"])) {
            $ram_id = filter_var($_POST["ram_id_$ramIndex"], FILTER_VALIDATE_INT);
            if ($ram_id) {
                $ramStmt = $db->prepare("
                    INSERT INTO system_rams (system_id, ram_id,  created_at, created_by)
                    VALUES (:system_id, :ram_id,  :created_at, :created_by)
                ");
                $ramStmt->execute([
                    ':system_id' => $system_id,
                    ':ram_id' => $ram_id,
                    ':created_at' => jdate('Y-m-d'),
                    ':created_by' => $_SESSION['user_id']
                ]);
            }
            $ramIndex++;
        }

        // حذف هاردهای قبلی
        $deleteStorages = $db->prepare("DELETE FROM system_storages WHERE system_id = :system_id");
        $deleteStorages->execute([':system_id' => $system_id]);

        // ذخیره هاردهای جدید
        $storageIndex = 0;
        while (isset($_POST["storage_id_$storageIndex"])) {
            $storage_id = filter_var($_POST["storage_id_$storageIndex"], FILTER_VALIDATE_INT);
            if ($storage_id) {

                $storageStmt = $db->prepare("
                    INSERT INTO system_storages (system_id, storage_id,  created_at, created_by)
                    VALUES (:system_id, :storage_id,  :created_at, :created_by)
                ");
                $storageStmt->execute([
                    ':system_id' => $system_id,
                    ':storage_id' => $storage_id,
                    ':created_at' => jdate('Y-m-d'),
                    ':created_by' => $_SESSION['user_id']
                ]);
            }
            $storageIndex++;
        }

        // حذف IPهای قبلی
        $deleteIps = $db->prepare("DELETE FROM system_ips WHERE system_id = :system_id");
        $deleteIps->execute([':system_id' => $system_id]);

        // ذخیره IPهای جدید
        $ipIndex = 0;
        while (isset($_POST["ip_address_$ipIndex"])) {
            $ip_address = trim($_POST["ip_address_$ipIndex"]);
            if (!empty($ip_address)) {
                $network_type = $_POST["ip_network_$ipIndex"] ?? 'LAN';

                $ip_description = htmlspecialchars(trim($_POST["ip_description_$ipIndex"] ?? ''));

                $ipStmt = $db->prepare("
                    INSERT INTO system_ips (system_id, ip_address, network_type,  description, created_at, created_by)
                    VALUES (:system_id, :ip_address, :network_type,  :description, :created_at, :created_by)
                ");
                $ipStmt->execute([
                    ':system_id' => $system_id,
                    ':ip_address' => $ip_address,
                    ':network_type' => $network_type,
                    ':description' => $ip_description,
                    ':created_at' => jdate('Y-m-d'),
                    ':created_by' => $_SESSION['user_id']
                ]);
            }
            $ipIndex++;
        }

        // حذف تجهیزات جانبی قبلی
        $deletePeriph = $db->prepare("DELETE FROM system_peripherals WHERE system_id = :system_id");
        $deletePeriph->execute([':system_id' => $system_id]);

        // ذخیره تجهیزات جانبی جدید
        $periphIndex = 0;
        while (isset($_POST["peripheral_id_$periphIndex"])) {
            $peripheral_id = filter_var($_POST["peripheral_id_$periphIndex"], FILTER_VALIDATE_INT);
            if ($peripheral_id) {

                $periphStmt = $db->prepare("
                    INSERT INTO system_peripherals (system_id, peripheral_id,  created_at, created_by)
                    VALUES (:system_id, :peripheral_id,  :created_at, :created_by)
                ");
                $periphStmt->execute([
                    ':system_id' => $system_id,
                    ':peripheral_id' => $peripheral_id,
                    ':created_at' => jdate('Y-m-d'),
                    ':created_by' => $_SESSION['user_id']
                ]);
            }
            $periphIndex++;
        }

        $db->commit();
        $_SESSION['success_message'] = "✅ سیستم با موفقیت ویرایش شد";

    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error_message'] = "❌ خطا در ویرایش سیستم: " . $e->getMessage();
    }

    header('Location: admin_systems.php');
    exit;
}

// ============================================
// پردازش فرم افزودن سیستم
// ============================================

if (isset($_POST['add_system']) && canEditSystems()) {
    try {
        $db->beginTransaction();

        $computer_code = htmlspecialchars(trim($_POST['computer_code']));
        $property_code = htmlspecialchars(trim($_POST['property_code']));
        $name = htmlspecialchars(trim($_POST['name']));
        $department_id = !empty($_POST['department_id']) ? filter_var($_POST['department_id'], FILTER_VALIDATE_INT) : null;
        $cpu_id = !empty($_POST['cpu_id']) ? filter_var($_POST['cpu_id'], FILTER_VALIDATE_INT) : null;
        $motherboard_id = !empty($_POST['motherboard_id']) ? filter_var($_POST['motherboard_id'], FILTER_VALIDATE_INT) : null;
        $power_id = !empty($_POST['power_id']) ? filter_var($_POST['power_id'], FILTER_VALIDATE_INT) : null;
        $monitor_id = !empty($_POST['monitor_id']) ? filter_var($_POST['monitor_id'], FILTER_VALIDATE_INT) : null;
        $jalaliDate = jdate('Y-m-d');

        // ثبت سیستم
        $insertStmt = $db->prepare("
            INSERT INTO systems (
                computer_code, property_code, name, department_id,cpu_id, motherboard_id, power_id, monitor_id,
                 created_at, created_by
            ) VALUES (
                :computer_code, :property_code, :name, :department_id,
                :cpu_id, :motherboard_id, :power_id, :monitor_id,
                 :created_at, :created_by
            )
        ");

        $insertStmt->execute([
            ':computer_code' => $computer_code,
            ':property_code' => $property_code,
            ':name' => $name,
            ':department_id' => $department_id,
            ':cpu_id' => $cpu_id,
            ':motherboard_id' => $motherboard_id,
            ':power_id' => $power_id,
            ':monitor_id' => $monitor_id,
            ':created_at' => $jalaliDate,
            ':created_by' => $_SESSION['user_id']
        ]);

        $system_id = $db->lastInsertId();

        // ذخیره رم‌ها
        $ramIndex = 0;
        while (isset($_POST["ram_id_$ramIndex"])) {
            $ram_id = filter_var($_POST["ram_id_$ramIndex"], FILTER_VALIDATE_INT);
            if ($ram_id) {

                $ramStmt = $db->prepare("
                    INSERT INTO system_rams (system_id, ram_id,  created_at, created_by)
                    VALUES (:system_id, :ram_id,  :created_at, :created_by)
                ");
                $ramStmt->execute([
                    ':system_id' => $system_id,
                    ':ram_id' => $ram_id,
                    ':created_at' => $jalaliDate,
                    ':created_by' => $_SESSION['user_id']
                ]);
            }
            $ramIndex++;
        }

        // ذخیره هاردها
        $storageIndex = 0;
        while (isset($_POST["storage_id_$storageIndex"])) {
            $storage_id = filter_var($_POST["storage_id_$storageIndex"], FILTER_VALIDATE_INT);
            if ($storage_id) {

                $storageStmt = $db->prepare("
                    INSERT INTO system_storages (system_id, storage_id,  created_at, created_by)
                    VALUES (:system_id, :storage_id,  :created_at, :created_by)
                ");
                $storageStmt->execute([
                    ':system_id' => $system_id,
                    ':storage_id' => $storage_id,
                    ':created_at' => $jalaliDate,
                    ':created_by' => $_SESSION['user_id']
                ]);
            }
            $storageIndex++;
        }

        // ذخیره IPها
        $ipIndex = 0;
        while (isset($_POST["ip_address_$ipIndex"])) {
            $ip_address = trim($_POST["ip_address_$ipIndex"]);
            if (!empty($ip_address)) {
                $network_type = $_POST["ip_network_$ipIndex"] ?? 'LAN';

                $ip_description = htmlspecialchars(trim($_POST["ip_description_$ipIndex"] ?? ''));

                $ipStmt = $db->prepare("
                    INSERT INTO system_ips (system_id, ip_address, network_type,  description, created_at, created_by)
                    VALUES (:system_id, :ip_address, :network_type, :description, :created_at, :created_by)
                ");
                $ipStmt->execute([
                    ':system_id' => $system_id,
                    ':ip_address' => $ip_address,
                    ':network_type' => $network_type,
                    ':description' => $ip_description,
                    ':created_at' => $jalaliDate,
                    ':created_by' => $_SESSION['user_id']
                ]);
            }
            $ipIndex++;
        }

        // ذخیره تجهیزات جانبی
        $periphIndex = 0;
        while (isset($_POST["peripheral_id_$periphIndex"])) {
            $peripheral_id = filter_var($_POST["peripheral_id_$periphIndex"], FILTER_VALIDATE_INT);
            if ($peripheral_id) {

                $periphStmt = $db->prepare("
                    INSERT INTO system_peripherals (system_id, peripheral_id, created_at, created_by)
                    VALUES (:system_id, :peripheral_id, :created_at, :created_by)
                ");
                $periphStmt->execute([
                    ':system_id' => $system_id,
                    ':peripheral_id' => $peripheral_id,
                    ':created_at' => $jalaliDate,
                    ':created_by' => $_SESSION['user_id']
                ]);
            }
            $periphIndex++;
        }

        $db->commit();
        $_SESSION['success_message'] = "✅ سیستم با موفقیت ثبت شد";

    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error_message'] = "❌ خطا در ثبت سیستم: " . $e->getMessage();
    }

    header('Location: admin_systems.php');
    exit;
}

// ============================================
// گرفتن لیست سیستم‌ها با فیلتر
// ============================================

$where = [];
$params = [];

if (isset($_GET['computer_code']) && !empty($_GET['computer_code'])) {
    $where[] = "s.computer_code LIKE :computer_code";
    $params[':computer_code'] = '%' . $_GET['computer_code'] . '%';
}
if (isset($_GET['name']) && !empty($_GET['name'])) {
    $where[] = "s.name LIKE :name";
    $params[':name'] = '%' . $_GET['name'] . '%';
}
if (isset($_GET['department']) && !empty($_GET['department'])) {
    $where[] = "s.department_id = :department";
    $params[':department'] = filter_var($_GET['department'], FILTER_VALIDATE_INT);
}
if (isset($_GET['cpu']) && !empty($_GET['cpu'])) {
    $where[] = "s.cpu_id = :cpu";
    $params[':cpu'] = filter_var($_GET['cpu'], FILTER_VALIDATE_INT);
}
if (isset($_GET['motherboard']) && !empty($_GET['motherboard'])) {
    $where[] = "s.motherboard_id = :motherboard";
    $params[':motherboard'] = filter_var($_GET['motherboard'], FILTER_VALIDATE_INT);
}

$whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

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
    
    $whereClause
    ORDER BY s.id DESC
");
$systems->execute($params);
$systems = $systems->fetchAll();

// دریافت اطلاعات چندگانه هر سیستم
foreach ($systems as $key => $system) {
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
    $systems[$key]['rams'] = $ramStmt->fetchAll();

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
    $systems[$key]['storages'] = $storageStmt->fetchAll();

    // دریافت IPها
    $ipStmt = $db->prepare("
        SELECT * FROM system_ips 
        WHERE system_id = :system_id 
        ORDER BY id DESC
    ");
    $ipStmt->execute([':system_id' => $system['id']]);
    $systems[$key]['ips'] = $ipStmt->fetchAll();

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
    $systems[$key]['peripherals'] = $periphStmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت سیستم‌ها</title>
    <?php load_assets(); ?>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="main-header">
            <div class="user-info">
                <span>👨‍💼</span>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
            </div>
            <div>
                <span class="clock-display" id="liveClock"><?php echo fa_number(now()); ?></span>
                <a href="logout.php" class="logout-btn-sidebar">🚪 خروج</a>
            </div>
        </div>

        <div class="main-title">
            <h1>💻 مدیریت سیستم‌ها</h1>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- فرم ثبت سیستم -->
        <!-- ============================================ -->
        <?php if (canEditSystems()): ?>
            <div class="add-card">
                <h2>➕ ثبت سیستم جدید</h2>
                <form method="post" class="systems-form" id="systemForm">

                    <!-- اطلاعات اصلی -->
                    <div class="form-row">

                        <div class="pc-id">
                            <label>کد رایانه *</label>
                            <input type="text" name="computer_code" required>
                        </div>
                        <div class="property-id">
                            <label>کد اموال</label>
                            <input type="text" name="property_code">
                        </div>
                        <div class="computer-group">
                            <label>نام سیستم *</label>
                            <input type="text" name="name" required>
                        </div>

                        <div class="department-group">
                            <label>بخش</label>
                            <select name="department_id">
                                <option value="">-- انتخاب --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group">
                            <label>CPU</label>
                            <div style="display: flex; gap: 8px;">
                                <select name="cpu_id" id="cpu_id">
                                    <option value="">-- انتخاب --</option>
                                    <?php foreach ($cpus as $cpu): ?>
                                        <option value="<?php echo $cpu['id']; ?>">
                                            <?php echo htmlspecialchars($cpu['brand_name'] . ' ' . $cpu['model_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn-add-quick" onclick="openComponentModal('cpu')" title="افزودن CPU جدید">➕</button>
                            </div>

                        </div>

                        <!-- مادربرد -->
                        <div class="form-group">
                            <label>مادربرد</label>
                            <div style="display: flex; gap: 8px;">
                                <select name="motherboard_id" id="motherboard_id" style="flex: 1;">
                                    <option value="">-- انتخاب --</option>
                                    <?php foreach ($motherboards as $mb): ?>
                                        <option value="<?php echo $mb['id']; ?>">
                                            <?php echo htmlspecialchars($mb['brand_name'] . ' ' . $mb['model_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn-add-quick" onclick="openComponentModal('motherboard')" title="افزودن مادربرد جدید">➕</button>
                            </div>
                        </div>

                        <!-- پاور -->
                        <div class="form-group">
                            <label>پاور</label>
                            <div style="display: flex; gap: 8px;">
                                <select name="power_id" id="power_id" style="flex: 1;">
                                    <option value="">-- انتخاب --</option>
                                    <?php foreach ($powers as $power): ?>
                                        <option value="<?php echo $power['id']; ?>">
                                            <?php echo htmlspecialchars($power['brand_name'] . ' ' . $power['model_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn-add-quick" onclick="openComponentModal('power')" title="افزودن پاور جدید">➕</button>
                            </div>
                        </div>
                        <!-- مانیتور -->
                        <div class="form-group">
                            <label>مانیتور</label>
                            <div style="display: flex; gap: 8px;">
                                <select name="monitor_id" id="monitor_id" style="flex: 1;">
                                    <option value="">-- انتخاب --</option>
                                    <?php foreach ($monitors as $monitor): ?>
                                        <option value="<?php echo $monitor['id']; ?>">
                                            <?php echo htmlspecialchars($monitor['brand_name'] . ' ' . $monitor['model_name']); ?>
                                            <?php if ($monitor['property_code']): ?>
                                                (<?php echo htmlspecialchars($monitor['property_code']); ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn-add-quick" onclick="openComponentModal('monitor')" title="افزودن مانیتور جدید">➕</button>
                            </div>
                        </div>

                    </div>


                    <div class="rhid-row">
                        <!-- بخش رم -->
                        <div class="ram-section">
                            <div id="rams_container">
                                <div class="ram-row" data-row="0">
                                    <label class="section-label"> رم‌</label>
                                    <div class="select-wrapper">
                                        <select name="ram_id_0" class="ram-select">
                                            <option value="">-- انتخاب --</option>
                                            <?php foreach ($rams as $ram): ?>
                                                <option value="<?php echo $ram['id']; ?>">
                                                    <?php echo htmlspecialchars($ram['brand_name'] . ' ' . $ram['model_name'] . ' (' . $ram['capacity'] . ' ' . $ram['type'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <!-- دکمه افزودن سریع کنار سلکت رم -->
                                        <button type="button" class="btn-add-quick" onclick="openComponentModal('ram')" title="افزودن رم جدید">➕</button>
                                    </div>
                                    <div class="row-remove">
                                        <button type="button" class="btn-remove-ram" onclick="removeRamRow(this)" hidden="">🗑️</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-row" onclick="addRamRow()">➕ افزودن رم</button>
                        </div>

                        <!-- بخش هارد -->
                        <div class="hard-section">

                            <div id="storages_container">
                                <div class="storage-row" data-row="0">
                                    <label class="section-label"> هارد</label>
                                    <div class="select-wrapper">
                                        <select name="storage_id_0" class="storage-select">
                                            <option value="">-- انتخاب --</option>
                                            <?php foreach ($storages as $storage): ?>
                                                <option value="<?php echo $storage['id']; ?>">
                                                    <?php echo htmlspecialchars($storage['brand_name'] . ' ' . $storage['model_name'] . ' (' . $storage['capacity'] . ' ' . $storage['type'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn-add-quick" onclick="openComponentModal('storage')" title="افزودن هارد جدید">➕</button>
                                    </div>

                                    <div class="row-remove">
                                        <button type="button" class="btn-remove-storage" onclick="removeStorageRow(this)" hidden="">🗑️</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-row" onclick="addStorageRow()">➕ افزودن هارد</button>
                        </div>

                        <!-- بخش IP -->
                        <div class="ip-section">
                            <div id="ips_container">
                                <div class="ip-row" data-row="0">

                                    <label class="section-label">IP</label>

                                    <div class="select-wrapper">

                                        <input type="text" name="ip_address_0">

                                        <select name="ip_network_0">
                                            <option value="LAN">LAN</option>
                                            <option value="WAN">WAN</option>
                                            <option value="VPN">VPN</option>
                                            <option value="WiFi">WiFi</option>
                                            <option value="Other">سایر</option>
                                        </select>

                                    </div>
                                    <div class="row-remove">
                                        <button type="button"
                                                class="btn-remove-ip"
                                                onclick="removeIpRow(this)"
                                                hidden>
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-row" onclick="addIpRow()">➕ افزودن IP</button>
                        </div>
                        <!-- بخش تجهیزات جانبی -->
                        <div class="peripheral-section">
                            <div id="peripherals_container">
                                <div class="peripheral-row" data-row="0">
                                    <label class="section-label"> تجهیزات جانبی</label>

                                    <div class="select-wrapper">
                                        <select name="peripheral_id_0" class="peripheral-select">
                                            <option value="">-- انتخاب --</option>
                                            <?php foreach ($peripheralTypes as $type): ?>
                                                <optgroup label="<?php echo $type['name']; ?>">
                                                    <?php
                                                    $periphStmt = $db->prepare("
                                    SELECT p.*, m.name as model_name, b.name as brand_name
                                    FROM peripherals p
                                    INNER JOIN models m ON p.model_id = m.id
                                    INNER JOIN brands b ON m.brand_id = b.id
                                    WHERE p.type_id = :type_id
                                    ORDER BY b.name, m.name
                                ");
                                                    $periphStmt->execute([':type_id' => $type['id']]);
                                                    $periphs = $periphStmt->fetchAll();
                                                    foreach ($periphs as $periph):
                                                        ?>
                                                        <option value="<?php echo $periph['id']; ?>">
                                                            <?php echo htmlspecialchars($periph['brand_name'] . ' ' . $periph['model_name']); ?>
                                                            <?php if ($periph['property_code']): ?>
                                                                (<?php echo htmlspecialchars($periph['property_code']); ?>)
                                                            <?php endif; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn-add-quick" onclick="openPeripheralModal()" title="افزودن تجهیز جانبی جدید">➕</button>
                                    </div>
                                    <div class="row-remove">
                                        <button type="button" class="btn-remove-peripheral" onclick="removePeripheralRow(this)" style="display: none;">🗑️</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-row" onclick="addPeripheralRow()">➕ افزودن تجهیز جانبی</button>
                        </div>

                    </div>

                    <div class="form-group">
                        <button type="submit" name="add_system" class="btn-add">💾 ذخیره سیستم</button>
                    </div>

                </form>
            </div>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- مودال افزودن قطعه جدید -->
        <!-- ============================================ -->
        <div id="componentModal" class="modal">
            <div class="modal-content" style="max-width: 550px;">
                <h3 id="componentModalTitle">➕ افزودن قطعه جدید</h3>
                <form id="componentForm">
                    <input type="hidden" name="component_type" id="component_type">

                    <div class="form-row">
                        <div class="form-group">
                            <label>برند *</label>
                            <select name="brand_id" id="comp_brand_id" required>
                                <option value="">-- انتخاب --</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>مدل *</label>
                            <select name="model_id" id="comp_model_id" required>
                                <option value="">-- انتخاب --</option>
                                <?php foreach ($models as $model): ?>
                                    <option value="<?php echo $model['id']; ?>"><?php echo htmlspecialchars($model['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- فیلدهای اختصاصی رم -->
                    <div id="ram_fields" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>نوع رم</label>
                                <select name="ram_type">
                                    <option value="">-- انتخاب --</option>
                                    <option value="DDR3">DDR3</option>
                                    <option value="DDR4">DDR4</option>
                                    <option value="DDR5">DDR5</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>ظرفیت</label>
                                <select name="ram_capacity">
                                    <option value="">-- انتخاب --</option>
                                    <option value="4GB">4GB</option>
                                    <option value="8GB">8GB</option>
                                    <option value="16GB">16GB</option>
                                    <option value="32GB">32GB</option>
                                    <option value="64GB">64GB</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- فیلدهای اختصاصی هارد -->
                    <div id="storage_fields" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>نوع</label>
                                <select name="storage_type">
                                    <option value="">-- انتخاب --</option>
                                    <option value="HDD">HDD</option>
                                    <option value="SSD">SSD</option>
                                    <option value="NVMe">NVMe</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>ظرفیت</label>
                                <select name="storage_capacity">
                                    <option value="">-- انتخاب --</option>
                                    <option value="128GB">128GB</option>
                                    <option value="256GB">256GB</option>
                                    <option value="512GB">512GB</option>
                                    <option value="1TB">1TB</option>
                                    <option value="2TB">2TB</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- فیلدهای اختصاصی مانیتور -->
                    <div id="monitor_fields" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>کد اموال</label>
                                <input type="text" name="monitor_property_code" >
                            </div>
                        </div>
                    </div>

                    <!-- فیلدهای اختصاصی تجهیزات جانبی -->
                    <div id="peripheral_fields" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>نوع تجهیز *</label>
                                <select name="peripheral_type_id" required>
                                    <option value="">-- انتخاب --</option>
                                    <?php foreach ($peripheralTypes as $type): ?>
                                        <option value="<?php echo $type['id']; ?>"><?php echo  $type['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>کد رایانه</label>
                                <input type="text" name="peripheral_computer_code" placeholder="کد رایانه">
                            </div>
                            <div class="form-group">
                                <label>کد اموال</label>
                                <input type="text" name="peripheral_property_code" placeholder="کد اموال">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>سریال</label>
                                <input type="text" name="peripheral_serial_number" placeholder="شماره سریال">
                            </div>
                            <div class="form-group">
                                <label>نوع اتصال</label>
                                <select name="peripheral_connection_type">
                                    <option value="USB">USB</option>
                                    <option value="Network">Network</option>
                                    <option value="Other">سایر</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h4 id="componentListTitle">قطعات ثبت شده</h4>

                    <table class="components-table" id="componentTable">

                        <thead id="componentTableHead">
                        <tr>
                            <th>برند</th>
                            <th>مدل</th>
                            <th>ظرفیت</th>
                            <th>نوع</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>

                        <tbody id="componentTableBody">

                        </tbody>
                    </table>

                    <div class="modal-buttons">
                        <button type="button" class="btn-add" onclick="saveComponent()">💾 ذخیره</button>
                        <button type="button" class="btn-cancel" onclick="closeModal('componentModal')">لغو</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- مودال افزودن تجهیز جانبی جدید -->
        <!-- ============================================ -->
        <div id="peripheralModal" class="modal">
            <div class="modal-content" style="max-width: 550px;">
                <h3>➕ افزودن دستگاه جانبی جدید</h3>
                <form id="peripheralForm">
                    <input type="hidden" name="add_peripheral" value="1">

                    <div class="form-row">
                        <div class="form-group">
                            <label>نوع دستگاه *</label>
                            <select name="type_id" required>
                                <option value="">-- انتخاب --</option>
                                <?php foreach ($peripheralTypes as $type): ?>
                                    <option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?> </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>کد اموال</label>
                            <input type="text" name="property_code" placeholder="کد اموال">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>برند</label>
                            <select name="brand_id">
                                <option value="">-- انتخاب --</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>مدل</label>
                            <select name="model_id">
                                <option value="">-- انتخاب --</option>
                                <?php foreach ($models as $model): ?>
                                    <option value="<?php echo $model['id']; ?>"><?php echo htmlspecialchars($model['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">

                        <div class="form-group">
                            <label>نوع اتصال</label>
                            <select name="connection_type">
                                <option value="USB">USB</option>
                                <option value="Network">Network</option>
                                <option value="Wireless">Wireless</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <h4>تجهیزات جانبی ثبت شده</h4>

                    <table class="components-table" id="peripheralTable">


                        <thead id="peripheralTableHead">
                        <tr>
                            <th>نوع تجهیز</th>
                            <th>برند</th>
                            <th>مدل</th>
                            <th>کد اموال</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>

                        <tbody id="peripheralTableBody">

                        </tbody>
                    </table>

                    <div class="modal-buttons">
                        <button type="button" class="btn-add" onclick="savePeripheral()">💾 ذخیره</button>
                        <button type="button" class="btn-cancel" onclick="closeModal('peripheralModal')">لغو</button>
                    </div>

                </form>
            </div>
        </div>
        <!-- ============================================ -->
        <!-- بخش جستجو -->
        <!-- ============================================ -->
        <div class="search-card">
             <h2>🔍 جستجوی سیستم‌ها</h2>
             <div class="search-form">
                <div class="search-row">
                    <div class="form-group">
                        <label>کد رایانه</label>
                        <input type="text" id="search_computer_code" value="<?php echo htmlspecialchars($_GET['computer_code'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>نام سیستم</label>
                        <input type="text" id="search_name" value="<?php echo htmlspecialchars($_GET['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>بخش</label>
                        <select id="search_department">
                            <option value="">همه</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo (isset($_GET['department']) && $_GET['department'] == $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="search-row">
                    <div class="search-group search-actions">
                        <button type="button" id="search_btn" class="btn-search">🔍 جستجو</button>
                        <button type="button" id="reset_btn" class="btn-reset-search">🗑️ پاک کردن</button>
                    </div>
                </div>
            </div>
        </div>

         <!-- ============================================ -->
         <!-- جدول سیستم‌ها -->
        <!-- ============================================ -->
        <div class="systems-table data-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>کد رایانه</th>
                    <th>نام سیستم</th>
                    <th>بخش</th>
                    <th>CPU</th>
                    <th>مادربرد</th>
                    <th>رم</th>
                    <th>هارد</th>
                    <th>پاور</th>
                    <th>مانیتور</th>
                    <th>IP</th>
                    <th>تجهیزات</th>
                    <th>تاریخ</th>
                    <th>عملیات</th>
                </tr>
                </thead>

                <tbody>

                <?php if (empty($systems)): ?>
                    <tr>
                        <td colspan="14" style="text-align: center; padding: 40px;">💻 هیچ سیستمی ثبت نشده است</td>
                    </tr>
                <?php else: ?>
                    <?php $row_num = 1; foreach ($systems as $system): ?>
                        <tr>
                            <td><?php echo fa_number($row_num); ?></td>

                             <!-- کد رایانه -->
                            <td>
                                <strong><?php echo htmlspecialchars($system['computer_code'] ?? '-'); ?></strong>
                                <?php if ($system['property_code']): ?>
                                    <br><small class="text-muted">اموال: <?php echo htmlspecialchars($system['property_code']); ?></small>
                                <?php endif; ?>
                            </td>

                             <!-- نام سیستم -->
                            <td><?php echo htmlspecialchars($system['name'] ?? '-'); ?></td>

                             <!-- بخش -->
                            <td><?php echo htmlspecialchars($system['department_name'] ?? '-'); ?></td>

                             <!-- CPU -->
                            <td>
                                <?php if ($system['cpu_brand'] && $system['cpu_model']): ?>
                                    <strong><?php echo htmlspecialchars($system['cpu_brand']); ?></strong>
                                    <br><small><?php echo htmlspecialchars($system['cpu_model']); ?></small>
                                <?php else: ?>
                                    <span class="badge badge-secondary">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- مادربرد -->
                            <td>
                                <?php if ($system['motherboard_brand'] && $system['motherboard_model']): ?>
                                    <strong><?php echo htmlspecialchars($system['motherboard_brand']); ?></strong>
                                    <br><small><?php echo htmlspecialchars($system['motherboard_model']); ?></small>
                                <?php else: ?>
                                    <span class="badge badge-secondary">-</span>
                                <?php endif; ?>
                            </td>

                             <!-- رم -->
                            <td>
                                <?php if (!empty($system['rams'])): ?>
                                    <?php foreach ($system['rams'] as $ram): ?>
                                        <div class="item-small">
                                            <?php echo htmlspecialchars($ram['brand_name'] ?? ''); ?>
                                            <small><?php echo htmlspecialchars($ram['model_name'] ?? ''); ?></small>
                                            <?php if ($ram['capacity']): ?>
                                                <small>(<?php echo htmlspecialchars($ram['capacity']); ?>)</small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="badge badge-secondary">-</span>
                                <?php endif; ?>
                            </td>

                             <!-- هارد -->
                            <td>
                                <?php if (!empty($system['storages'])): ?>
                                    <?php foreach ($system['storages'] as $storage): ?>
                                        <div class="item-small">
                                            <?php echo htmlspecialchars($storage['brand_name'] ?? ''); ?>
                                            <small><?php echo htmlspecialchars($storage['model_name'] ?? ''); ?></small>
                                            <?php if ($storage['capacity']): ?>
                                                <small>(<?php echo htmlspecialchars($storage['capacity']); ?>)</small>
                                            <?php endif; ?>
                                         </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                     <span class="badge badge-secondary">-</span>
                                <?php endif; ?>
                            </td>

                             <!-- پاور -->
                            <td>
                                <?php if ($system['power_brand'] && $system['power_model']): ?>
                                     <strong><?php echo htmlspecialchars($system['power_brand']); ?></strong>
                                     <br><small><?php echo htmlspecialchars($system['power_model']); ?></small>
                                <?php else: ?>
                                     <span class="badge badge-secondary">-</span>
                                <?php endif; ?>
                            </td>

                             <!-- مانیتور -->
                            <td>
                                <?php if ($system['monitor_brand'] && $system['monitor_model']): ?>
                                     <strong><?php echo htmlspecialchars($system['monitor_brand']); ?></strong>
                                     <br><small><?php echo htmlspecialchars($system['monitor_model']); ?></small>
                                    <?php if ($system['monitor_property_code']): ?>
                                        <br><small class="text-muted">اموال: <?php echo htmlspecialchars($system['monitor_property_code']); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-secondary">-</span>
                                <?php endif; ?>
                             </td>

                            <!-- IP -->
                            <td>
                                <?php if (!empty($system['ips'])): ?>
                                     <?php foreach ($system['ips'] as $ip): ?>
                                        <div class="item-small">
                                            <span class="ip-address"><?php echo htmlspecialchars($ip['ip_address']); ?></span>
                                             <br><small class="text-muted"><?php echo htmlspecialchars($ip['network_type']); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                     <span class="badge badge-secondary">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- تجهیزات جانبی -->
                             <td>
                                <?php if (!empty($system['peripherals'])): ?>

                                    <?php
                                    $grouped = [];

                                    foreach ($system['peripherals'] as $periph) {
                                        $grouped[$periph['type_name']][] = $periph;
                                    }
                                    foreach ($grouped as $type => $items): ?>
                                        <div class="peripheral-group">
                                            <strong><?= htmlspecialchars($type) ?></strong>

                                            <?php foreach ($items as $item): ?>
                                                <div class="peripheral-item">
                                                    <?= htmlspecialchars($item['brand_name']) ?>
                                                    <?= htmlspecialchars($item['model_name']) ?>

                                                    <?php if (!empty($item['property_code'])): ?>
                                                        (<?= htmlspecialchars($item['property_code']) ?>)
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="badge badge-secondary">-</span>
                                <?php endif; ?>
                            </td>

                             <!-- تاریخ -->
                             <td class="date">
                                <?php echo fa_number(htmlspecialchars($system['created_at'] ?? '-')); ?>
                                <br><small><?php echo htmlspecialchars($system['creator_name'] ?? '-'); ?></small>
                            </td>

                            <!-- عملیات -->
                             <td class="action-buttons">
                                <?php if (canEditSystems()): ?> <button class="edit-btn" onclick='openEditModal(<?php echo json_encode($system); ?>)' title="ویرایش">✏️ویرایش</button>
                                <?php endif; ?>
                                <?php if (canDeleteSystems()): ?>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $system['id']; ?>, '<?php echo htmlspecialchars($system['name']); ?>')" title="حذف">🗑️حذف</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php $row_num++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

   <!-- ============================================ -->
   <!-- مودال ویرایش سیستم -->
   <!-- ============================================ -->
 <div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش سیستم</h3>
        <form method="post" id="editForm">
            <input type="hidden" name="system_id" id="edit_system_id">
            <input type="hidden" name="edit_system" value="1">

            <div class="form-row">
                <div class="form-group">
                    <label>کد رایانه *</label>
                    <input type="text" name="computer_code" id="edit_computer_code" required>
                </div>
                <div class="form-group">
                    <label>کد اموال</label>
                    <input type="text" name="property_code" id="edit_property_code">
                </div>
                <div class="form-group">
                    <label>نام سیستم *</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>بخش</label>
                    <select name="department_id" id="edit_department_id">
                        <option value="">-- انتخاب --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label>پردازنده (CPU)</label>
                     <select name="cpu_id" id="edit_cpu_id">
                        <option value="">-- انتخاب --</option>
                        <?php foreach ($cpus as $cpu): ?>
                            <option value="<?php echo $cpu['id']; ?>">
                                <?php echo htmlspecialchars($cpu['brand_name'] . ' ' . $cpu['model_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                     <label>مادربرد</label>
                    <select name="motherboard_id" id="edit_motherboard_id">
                        <option value="">-- انتخاب --</option>
                        <?php foreach ($motherboards as $mb): ?>
                            <option value="<?php echo $mb['id']; ?>">
                                <?php echo htmlspecialchars($mb['brand_name'] . ' ' . $mb['model_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>پاور</label>
                    <select name="power_id" id="edit_power_id">
                        <option value="">-- انتخاب --</option>
                        <?php foreach ($powers as $power): ?>
                            <option value="<?php echo $power['id']; ?>">
                                <?php echo htmlspecialchars($power['brand_name'] . ' ' . $power['model_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>مانیتور</label>
                    <select name="monitor_id" id="edit_monitor_id">
                        <option value="">-- انتخاب --</option>
                        <?php foreach ($monitors as $monitor): ?>
                            <option value="<?php echo $monitor['id']; ?>">
                                <?php echo htmlspecialchars($monitor['brand_name'] . ' ' . $monitor['model_name']); ?>
                                <?php if ($monitor['property_code']): ?>
                                    (<?php echo htmlspecialchars($monitor['property_code']); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="rhid-row">

                <div class="ram-section">
                    <div id="edit_rams_container"></div>

                    <button
                            type="button"
                            class="btn-add-row"
                            onclick="addEditRamRow()">
                        ➕ افزودن رم
                    </button>
                </div>

                <div class="hard-section">
                    <div id="edit_storages_container"></div>

                    <button
                            type="button"
                            class="btn-add-row"
                            onclick="addEditStorageRow()">
                        ➕ افزودن هارد
                    </button>
                </div>

                <div class="ip-section">
                    <div id="edit_ips_container"></div>

                    <button
                            type="button"
                            class="btn-add-row"
                            onclick="addEditIpRow()">
                        ➕ افزودن IP
                    </button>
                </div>

                <div class="peripheral-section">
                    <div id="edit_peripherals_container"></div>

                    <button
                            type="button"
                            class="btn-add-row"
                            onclick="addEditPeripheralRow()">
                        ➕ افزودن تجهیز جانبی
                    </button>
                </div>

            </div>

            <div class="modal-buttons">
                <button type="submit" class="btn-add">💾 ذخیره</button>
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
         </form>
    </div>
</div>
</body>
</html>