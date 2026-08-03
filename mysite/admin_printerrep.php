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

// دریافت پارامترهای فیلتر
$department_id = $_POST['department_id'] ?? '';
$brand_id      = $_POST['brand_id'] ?? '';
$activity_id   = $_POST['activity_id'] ?? '';
// تاریخ شمسی از لیست‌ها
$date_from = faToEn($_POST['date_from'] ?? '');
$date_to   = faToEn($_POST['date_to'] ?? '');

// ساخت کوئری شرطی
$whereConditions = [];
$params = [];

    if (!empty($activity_id)) {
    $whereConditions[] = "p.activity_id = :activity_id";
    $params[':activity_id'] = $activity_id;
    }

    if (!empty($department_id)) {
        $whereConditions[] = "p.department_id = :department_id";
        $params[':department_id'] = $department_id;
    }

    if (!empty($brand_id)) {
        $whereConditions[] = "p.brand_id = :brand_id";
        $params[':brand_id'] = $brand_id;
    }

    if (!empty($date_from)) {
        $whereConditions[] = "p.created_at >= :date_from";
        $params[':date_from'] = $date_from;
    }

    if (!empty($date_to)) {
        $whereConditions[] = "p.created_at <= :date_to";
        $params[':date_to'] = $date_to;
    }
$selectedColumns = $_POST['columns'] ?? [
    'computer_code',
    'property_code',
    'activity_name',
    'department_name',
    'brand_name',
    'serial_number',
    'description',
    'created_at'
];
if (empty($selectedColumns)) {
    $selectedColumns = [
       'computer_code',
    'property_code',
    'activity_name',
    'department_name',
    'brand_name',
    'serial_number',
    'description',
    'created_at'
    ];
}
$availableColumns = [
    'computer_code'   => 'کد رایانه',
    'property_code'   => 'کد اموال',
    'activity_name'   => 'فعالیت',
    'department_name' => 'بخش',
    'brand_name'      => 'برند',
    'serial_number'   => 'سریال',
    'description'     => 'توضیحات',
    'created_at'      => 'تاریخ ثبت'
];
$whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

$sql = "
SELECT
    p.*,
    a.name AS activity_name,
    d.name AS department_name,
    b.name AS brand_name,
    u.fullname AS creator_name

FROM printers p

LEFT JOIN activities a
    ON a.id = p.activity_id

LEFT JOIN departments d
    ON d.id = p.department_id

LEFT JOIN brands b
    ON b.id = p.brand_id

LEFT JOIN users u
    ON u.id = p.created_by

$whereSql

ORDER BY p.created_at DESC
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$printers=$stmt->fetchAll();

$departments = $db->query("SELECT id,name FROM departments  ORDER BY name ASC")->fetchAll();
$brands = $db->query("SELECT id,name FROM brands ORDER BY name")->fetchAll();
$activities  = $db->query("SELECT id,name FROM activities ORDER BY name")->fetchAll();

$activity_name = '';
$department_name = '';
$brand_name = '';

if (!empty($activity_id)) {
    $stmt = $db->prepare("SELECT name FROM activities WHERE id=?");
    $stmt->execute([$activity_id]);
    $activity_name = $stmt->fetchColumn();
}

if (!empty($department_id)) {
    $stmt = $db->prepare("SELECT name FROM departments WHERE id=?");
    $stmt->execute([$department_id]);
    $department_name = $stmt->fetchColumn();
}

if (!empty($brand_id)) {
    $stmt = $db->prepare("SELECT name FROM brands WHERE id=?");
    $stmt->execute([$brand_id]);
    $brand_name = $stmt->fetchColumn();
}

// ساخت تاریخ نمایشی
$display_date_from = $date_from ? fa_number($date_from) : '';
$display_date_to   = $date_to ? fa_number($date_to) : '';

$filters = [];

if (!empty($activity_name))
    $filters[] = "<span>فعالیت:</span> ".$activity_name;

if (!empty($department_name))
    $filters[] = "<span>بخش:</span> ".$department_name;

if (!empty($brand_name))
    $filters[] = "<span>برند:</span> ".$brand_name;

if (!empty($display_date_from))
    $filters[] = "<span>از تاریخ:</span> ".$display_date_from;

if (!empty($display_date_to))
    $filters[] = "<span>تا تاریخ:</span> ".$display_date_to;

$filterText = !empty($filters)
    ? "🔍 فیلترهای اعمال شده: ".implode(" | ", $filters)
    : "📋 نمایش همه پرینترها";

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

        <?php if (empty($printers)): ?>

            <tr>
                <td colspan="<?= count($selectedColumns) + 1 ?>" class="no-data">
                    📭 هیچ داده ای با این فیلترها یافت نشد
                </td>
            </tr>

        <?php else: ?>

            <?php $i = 1; ?>

            <?php foreach ($printers as $p): ?>

                <tr>

                    <td><?= fa_number($i++) ?></td>

                    <?php foreach ($selectedColumns as $col): ?>

                        <?php if (isset($availableColumns[$col])): ?>

                            <?php
                            $value = $p[$col] ?? '-';

                            if ($col == 'created_at' && $value != '-') {
                                $value = fa_number($value);
                            }
                            ?>

                            <td><?= htmlspecialchars((string)$value) ?></td>

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
    <title>گزارشات پرینتر</title>
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
            <h1>📊 گزارشات پرینتر</h1>
        </div>

        <div class="filter-card">
            <h2>🔍 فیلترها</h2>
            <form method="post" id="filterform">
                <div class="columns-box">
    <div class="checkbox-grid">

        <label><input type="checkbox" name="columns[]" value="computer_code" checked> کد رایانه</label>

        <label><input type="checkbox" name="columns[]" value="property_code" checked> کد اموال</label>

        <label><input type="checkbox" name="columns[]" value="activity_name" checked> فعالیت</label>

        <label><input type="checkbox" name="columns[]" value="department_name" checked> بخش</label>

        <label><input type="checkbox" name="columns[]" value="brand_name" checked> برند</label>

        <label><input type="checkbox" name="columns[]" value="serial_number" checked> سریال</label>

        <label><input type="checkbox" name="columns[]" value="description" checked> توضیحات</label>

        <label><input type="checkbox" name="columns[]" value="created_at" checked> تاریخ ثبت</label>

    </div>
</div>

<div class="filter-row">

    <div class="filter-group">
        <label>فعالیت</label>
        <select name="activity_id" class="form-control">
            <option value="">همه</option>

            <?php foreach($activities as $a): ?>
                <option value="<?= $a['id'] ?>"
                    <?= ($activity_id == $a['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['name']) ?>
                </option>
            <?php endforeach; ?>

        </select>
    </div>

    <div class="filter-group">
        <label>بخش</label>
        <select name="department_id" class="form-control">
            <option value="">همه</option>

            <?php foreach($departments as $d): ?>
                <option value="<?= $d['id'] ?>"
                    <?= ($department_id == $d['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d['name']) ?>
                </option>
            <?php endforeach; ?>

        </select>
    </div>

    <div class="filter-group">
        <label>برند</label>
        <select name="brand_id" class="form-control">
            <option value="">همه</option>

            <?php foreach($brands as $b): ?>
                <option value="<?= $b['id'] ?>"
                    <?= ($brand_id == $b['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($b['name']) ?>
                </option>
            <?php endforeach; ?>

        </select>
    </div>

</div>

<div class="filter-row">

    <div class="search-group">
        <label>از تاریخ</label>
        <input type="text" id="date_from" name="date_from" class="form-control">
    </div>

    <div class="search-group">
        <label>تا تاریخ</label>
        <input type="text" id="date_to" name="date_to" class="form-control">
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

<?php if(empty($printers)): ?>

<tr>
    <td colspan="<?= count($selectedColumns)+1 ?>" class="text-center">
        📭 موردی یافت نشد
    </td>
</tr>

<?php else: ?>

<?php $i=1; ?>

<?php foreach($printers as $printer): ?>

<tr>

    <td><?= fa_number($i++) ?></td>

    <?php foreach($selectedColumns as $col): ?>

        <?php if(isset($availableColumns[$col])): ?>

            <?php
            $value = $printer[$col] ?? '-';

            if($col=='created_at' && $value!='-'){
                $value = fa_number($value);
            }
            ?>

            <td><?= htmlspecialchars((string)$value) ?></td>

        <?php endif; ?>

    <?php endforeach; ?>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</tbody>
</table>
        </div>
    </div>
 </div>
 <script>
    window.reportConfig = {
    url: "admin_printerrep.php",
    printUrl: "assets/print_report.php",
    table: ".reports-table",
    filterInfo: true,
    type: "printer"
 };
    </script>
</body>
</html>