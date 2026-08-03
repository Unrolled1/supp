<?php

session_start();
require_once __DIR__ . '/assets/includes/autoload.php';

date_default_timezone_set('Asia/Tehran');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
if (!isAdmin() || !canViewReports()) {
    header('Location: requests.php');
    exit;
}

$db = getDB();

// تشخیص درخواست Ajax
$isAjax = isset($_POST['ajax']) && $_POST['ajax'] == '1';
$isReset = isset($_POST['reset']) && $_POST['reset'] == '1';
$department_id = $_POST['department_id'] ?? '';
$cpu_id        = $_POST['cpu_id'] ?? '';
$date_from     = faToEn($_POST['date_from'] ?? '');
$date_to       = faToEn($_POST['date_to'] ?? ''); 

    $whereConditions = [];
    $params = [];
    
    if (!empty($department_id)) {
    $whereConditions[] = "s.department_id = :department_id";
    $params[':department_id'] = $department_id;
    }

    if (!empty($cpu_id)) {
        $whereConditions[] = "s.cpu_id = :cpu_id";
        $params[':cpu_id'] = $cpu_id;
    }

    if (!empty($date_from)) {
        $whereConditions[] = "s.created_at >= :date_from";
        $params[':date_from'] = $date_from;
    }

    if (!empty($date_to)) {
        $whereConditions[] = "s.created_at <= :date_to";
        $params[':date_to'] = $date_to;
    
    }
$selectedColumns = $_POST['columns'] ?? [
    'computer_code',
    'property_code',
    'name',
    'department_name',
    'cpu_name',
    'motherboard_name',
    'power_name',
    'monitor_name',
    'rams',
    'storages',
    'ip_addresses',
    'peripherals',
    'created_at'
];
if (empty($selectedColumns)) {
    $selectedColumns = [
        'computer_code',
    'property_code',
    'name',
    'department_name',
    'cpu_name',
    'motherboard_name',
    'power_name',
    'monitor_name',
    'rams',
    'storages',
    'ip_addresses',
    'peripherals',
    'created_at'
    ];
    }
    $availableColumns = [
    'computer_code'    => 'کد رایانه',
    'property_code'    => 'کد اموال',
    'name'             => 'نام سیستم',
    'department_name'  => 'بخش',
    'cpu_name'         => 'پردازنده',
    'motherboard_name' => 'مادربرد',
    'power_name'       => 'پاور',
    'monitor_name'     => 'مانیتور',
    'rams'             => 'رم‌ها',  
    'storages'         => 'هاردها', 
    'ip_addresses'     => 'IP',
    'peripherals'      => 'تجهیزات جانبی',
    'created_at'       => 'تاریخ ثبت'
];
    $whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
$sql = "
    SELECT
        s.*,
        d.name AS department_name,
        cpu_b.name AS cpu_brand,
        cpu_m.name AS cpu_model,
        CONCAT(cpu_b.name,' ',cpu_m.name) AS cpu_name,
        CONCAT(mb_b.name,' ',mb_m.name) AS motherboard_name,
        CONCAT(pw_b.name,' ',pw_m.name) AS power_name,
        CONCAT(mn_b.name,' ',mn_m.name,
            IF(mn.property_code IS NOT NULL,
               CONCAT(' - ',mn.property_code),
               '')
        ) AS monitor_name,
        -- دریافت رم‌ها به صورت مستقیم
        (
            SELECT GROUP_CONCAT(
                CONCAT(
                    rb.name, ' ',
                    rm.name, ' (',
                    r.capacity, ' ',
                    r.type, ')'
                )
                SEPARATOR ' , '
            )
            FROM system_rams sr
            JOIN rams r ON sr.ram_id = r.id
            JOIN models rm ON r.model_id = rm.id
            JOIN brands rb ON rm.brand_id = rb.id
            WHERE sr.system_id = s.id
        ) AS rams,
        -- دریافت هاردها به صورت مستقیم
        (
            SELECT GROUP_CONCAT(
                CONCAT(
                    sb.name, ' ',
                    sm.name, ' (',
                    st.capacity, ' ',
                    st.type, ')'
                )
                SEPARATOR ' , '
            )
            FROM system_storages ss
            JOIN storages st ON ss.storage_id = st.id
            JOIN models sm ON st.model_id = sm.id
            JOIN brands sb ON sm.brand_id = sb.id
            WHERE ss.system_id = s.id
        ) AS storages,
 -- دریافت IPها با اطلاعات بیشتر
        (
            SELECT GROUP_CONCAT(
                CONCAT(
                    si.ip_address,
                    IF(si.network_type IS NOT NULL,
                       CONCAT(' (', si.network_type, ')'),
                       ''),
                    IF(si.description IS NOT NULL,
                       CONCAT(' - ', si.description),
                       '')
                )
                SEPARATOR ' , '
            )
            FROM system_ips si
            WHERE si.system_id = s.id
        ) AS ip_addresses,
        
        -- دریافت تجهیزات جانبی با اطلاعات کامل
        (
            SELECT GROUP_CONCAT(
                CONCAT(
                    pt.name,
                    ': ',
                    pb.name, ' ',
                    pm.name,
                    IF(p.property_code IS NOT NULL,
                       CONCAT(' (', p.property_code, ')'),
                       ''),
                    IF(p.connection_type IS NOT NULL,
                       CONCAT(' [', p.connection_type, ']'),
                       '')
                )
                SEPARATOR ' , '
            )
            FROM system_peripherals sp
            JOIN peripherals p ON sp.peripheral_id = p.id
            JOIN peripheral_types pt ON p.type_id = pt.id
            JOIN models pm ON p.model_id = pm.id
            JOIN brands pb ON pm.brand_id = pb.id
            WHERE sp.system_id = s.id
        ) AS peripherals
    FROM systems s

    LEFT JOIN departments d
        ON d.id = s.department_id

    LEFT JOIN cpus c
        ON c.id = s.cpu_id
    LEFT JOIN models cpu_m
        ON c.model_id = cpu_m.id
    LEFT JOIN brands cpu_b
        ON c.brand_id = cpu_b.id

    LEFT JOIN motherboards mb
        ON mb.id = s.motherboard_id
    LEFT JOIN models mb_m
        ON mb.model_id = mb_m.id
    LEFT JOIN brands mb_b
        ON mb.brand_id = mb_b.id

    LEFT JOIN powers pw
        ON pw.id = s.power_id
    LEFT JOIN models pw_m
        ON pw.model_id = pw_m.id
    LEFT JOIN brands pw_b
        ON pw.brand_id = pw_b.id

    LEFT JOIN monitors mn
        ON mn.id = s.monitor_id
    LEFT JOIN models mn_m
        ON mn.model_id = mn_m.id
    LEFT JOIN brands mn_b
        ON mn.brand_id = mn_b.id

    LEFT JOIN system_ips si
        ON si.system_id = s.id

    LEFT JOIN users u
        ON u.id = s.created_by

    LEFT JOIN system_peripherals sp
        ON sp.system_id = s.id

    LEFT JOIN peripherals p
        ON p.id = sp.peripheral_id

    LEFT JOIN peripheral_types pt
        ON pt.id = p.type_id

    LEFT JOIN brands pb
        ON pb.id = p.brand_id

    LEFT JOIN models pm
        ON pm.id = p.model_id

    $whereSql

    GROUP BY s.id
    ORDER BY s.created_at DESC
";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
$systems = $stmt->fetchAll();

// پردازش داده‌ها برای نمایش به صورت تگ
foreach ($systems as &$system) {
    // پردازش رم‌ها
    $system['rams'] = !empty($system['rams']) ? explode(' , ', $system['rams']) : [];
    
    // پردازش هاردها
    $system['storages'] = !empty($system['storages']) ? explode(' , ', $system['storages']) : [];
    
    // پردازش آی‌پی‌ها - تبدیل به آرایه
    $system['ip_addresses'] = !empty($system['ip_addresses']) ? explode(' , ', $system['ip_addresses']) : [];
    
    // پردازش تجهیزات جانبی - تبدیل به آرایه
    $system['peripherals'] = !empty($system['peripherals']) ? explode(' , ', $system['peripherals']) : [];
}
unset($system);

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

// ساخت تاریخ نمایشی
$display_date_from = $date_from ? fa_number($date_from) : '';
$display_date_to   = $date_to ? fa_number($date_to) : '';
$filters = [];


if (!empty($display_date_from))
    $filters[] = "<span>از تاریخ:</span> ".$display_date_from;

if (!empty($display_date_to))
    $filters[] = "<span>تا تاریخ:</span> ".$display_date_to;

$filterText = !empty($filters)
    ? "🔍 فیلترهای اعمال شده: ".implode(" | ", $filters)
    : "📋 نمایش همه سیستم ها";
if ($isAjax) {

    ob_start();
    ?>
    <table>
        <thead>
        <tr>
            <th>ردیف</th>

            <?php foreach ($selectedColumns as $col): ?>
                <?php if (isset($availableColumns[$col])): ?>
                    <th><?= htmlspecialchars($availableColumns[$col]) ?></th>
                <?php endif; ?>
            <?php endforeach; ?>

        </tr>
        </thead>

        <tbody>

        <?php if (empty($systems)): ?>

            <tr>
                <td colspan="<?= count($selectedColumns) + 1 ?>" class="no-data">
                    📭 هیچ داده ای با این فیلترها یافت نشد
                </td>
            </tr>

        <?php else: ?>

            <?php $i = 1; ?>

            <?php foreach ($systems as $s): ?>

                <tr>

                    <td><?= fa_number($i++) ?></td>

                    <?php foreach ($selectedColumns as $col): ?>

                        <?php if (isset($availableColumns[$col])): ?>

<td>
<?php
$value = $s[$col] ?? '-';

if (in_array($col, ['rams','storages','ip_addresses','peripherals'])) {

    if (is_array($value) && !empty($value)) {

        echo '<div class="tags-container">';

        foreach ($value as $item) {
    $class = '';
    switch ($col) {
        case 'rams': $class = 'tag-ram'; break;
        case 'storages': $class = 'tag-storage'; break;
        case 'ip_addresses': $class = 'tag-ip'; break;
        case 'peripherals': $class = 'tag-peripheral'; break;
    }

    echo '<span class="tag '.$class.'">'.htmlspecialchars($item).'</span>';
}

        echo '</div>';

    } else {
        echo '-';
    }

} else {

    if ($col == 'created_at' && $value != '-') {
        $value = fa_number($value);
    }

    echo htmlspecialchars((string)$value);
}
?>
</td>

                        <?php endif; ?>

                    <?php endforeach; ?>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>
    </table>
    <?php

    $tableHtml = ob_get_clean();

    header('Content-Type: application/json');

    echo json_encode([
        'success'    => true,
        'table'      => $tableHtml,
        'filterInfo' => $filterText
    ]);

    exit;
 }
 ?>
 <!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>گزارشات سیستم ها</title>
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
            <h1>📊 گزارشات سیستم ها</h1>
        </div>
        <div class="filter-card">
            <h2>🔍 فیلترها</h2>
            <form method="post" id="filterform">
                <div class="columns-box">
    <div class="checkbox-grid">

        <label><input type="checkbox" name="columns[]"value="computer_code" checked> کد رایانه</label>

        <label><input type="checkbox" name="columns[]" value="property_code" checked> کد اموال</label>

        <label><input type="checkbox" name="columns[]" value="name" checked> نام سیستم</label>

        <label><input type="checkbox" name="columns[]" value="department_name" checked> بخش</label>

        <label><input type="checkbox" name="columns[]" value="cpu_name" checked> پردازنده</label>

        <label><input type="checkbox" name="columns[]" value="motherboard_name" checked> مادربرد</label>

        <label><input type="checkbox" name="columns[]" value="power_name" checked> پاور</label>

        <label><input type="checkbox" name="columns[]" value="monitor_name" checked> مانیتور</label>
        <label><input type="checkbox" name="columns[]" value="rams" checked> رم‌ها</label>   
    <label><input type="checkbox" name="columns[]" value="storages" checked> هاردها</label>
        <label><input type="checkbox" name="columns[]" value="ip_addresses" checked> IP</label>
        <label><input type="checkbox" name="columns[]" value="peripherals" checked> تجهیزات جانبی</label>
        <label><input type="checkbox" name="columns[]" value="created_at" checked> تاریخ ثبت</label>

    </div>
</div>
    <div class="filter-row">
    <div class="filter-group">
        <label>بخش</label>
        <select name="department_id" id="department_id">
            <option value="">همه بخش‌ها</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?= $dept['id'] ?>">
                    <?= htmlspecialchars($dept['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
<!--
    <div class="filter-group">
        <label>پردازنده</label>
        <select name="cpu_id" id="cpu_id">
            <option value="">همه پردازنده‌ها</option>
            <?php foreach ($cpus as $cpu): ?>
                <option value="<?= $cpu['id'] ?>">
                    <?= htmlspecialchars($cpu['brand_name'] . ' ' . $cpu['model_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    -->
</div>

<div class="filter-row">
    <div class="search-group">
        <label>از تاریخ</label>
        <input type="text" id="date_from" name="date_from" class="form-control datepicker">
    </div>

    <div class="search-group">
        <label>تا تاریخ</label>
        <input type="text" id="date_to" name="date_to" class="form-control datepicker">
    </div>
</div>


<div class="filter-actions">
                    <button type="button" class="btn-filter" >🔍 اعمال فیلتر</button>
                    <button type="button" class="btn-reset"> 🗑 پاک کردن</button>
                    <button type="button" class="btn-pdf">🖨️ پرینت گزارش</button>
                </div>
                </form>
                </div>
                
                <!-- جدول نتایج -->
        <div class="reports-table data-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th>

                    <?php foreach ($selectedColumns as $col): ?>
                        <?php if(isset($availableColumns[$col])): ?>
                            <th><?= $availableColumns[$col] ?></th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>

<?php if(empty($systems)): ?>

<tr>
    <td colspan="<?= count($selectedColumns)+1 ?>" class="text-center">
        📭 موردی یافت نشد
    </td>
</tr>

<?php else: ?>

<?php $i=1; ?>

<?php foreach($systems as $system): ?>
    <tr>
        <td><?= fa_number($i++) ?></td>
        <?php foreach ($selectedColumns as $col): ?>
    <?php if (isset($availableColumns[$col])): ?>

        <td>
            <?php
$value = $system[$col] ?? '-';

            if (in_array($col, ['rams', 'storages', 'ip_addresses', 'peripherals'])) {
                if (is_array($value) && !empty($value)) {
                    echo '<div class="tags-container">';
                    foreach ($value as $item) {
                        $class = '';
                        switch ($col) {
                            case 'rams': $class = 'tag-ram'; break;
                            case 'storages': $class = 'tag-storage'; break;
                            case 'ip_addresses': $class = 'tag-ip'; break;
                            case 'peripherals': $class = 'tag-peripheral'; break;
                        }
                        echo '<span class="tag '.$class.'">'.htmlspecialchars($item).'</span>';
                    }
                    echo '</div>';
                } else {
                    echo '-';
                }
            } else {
                if ($col == 'created_at' && $value != '-') {
                    $value = fa_number($value);
                }
                echo htmlspecialchars((string)$value);
            }
            ?>
        </td>

    <?php endif; ?>
<?php endforeach; ?>
    </tr>
<?php endforeach; ?>

<?php endif; ?>

</tbody>
</table>
        </div>
    </div>
 
 <script>
    window.reportConfig = {
    url: "admin_systemrep.php",
    printUrl: "assets/print_report.php",
    table: ".reports-table",
    filterInfo: true,
    type: "system"
 };
    </script>
</body>
</html>