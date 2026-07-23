<?php

session_start();
require_once 'config/config.php';
require_once 'db.php';
require_once 'assets/jdf.php';
require_once 'functions.php';

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
$service_name = $_POST['service_name'] ?? '';

// تاریخ شمسی از لیست‌ها
$date_from = faToEn($_POST['date_from'] ?? '');
$date_to   = faToEn($_POST['date_to'] ?? '');

// ساخت کوئری شرطی
$whereConditions = [];
$params = [];

if (!empty($service_name)) {
    $whereConditions[] = "sr.service_name = :service_name";
    $params[':service_name'] = $service_name;
}
$department_name = '';
if (!empty($department_id)) {
    $whereConditions[] = "sr.department_id = :department_id";
    $params[':department_id'] = $department_id;
}

if (!empty($date_from)) {
    $whereConditions[] = "sr.created_at >= :date_from";
    $params[':date_from'] = $date_from;
}

if (!empty($date_to)) {
    $whereConditions[] = "sr.created_at <= :date_to";
    $params[':date_to'] = $date_to;
}

$selectedColumns = $_POST['columns'] ?? [
    'service_name',
    'department_name',
    'brand_name',
    'receiver_name',
    'serial_number',
    'computer_code',
    'created_at'
];
$availableColumns = [
    'service_name' => 'فعالیت',
    'department_name' => 'بخش',
    'brand_name' => 'برند',
    'receiver_name' => 'تحویل گیرنده',
    'serial_number' => 'سریال',
    'computer_code' => 'کد رایانه',
    'created_at' => 'تاریخ ثبت'
];
$whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// گرفتن تیکت‌ها
$sql = "
    SELECT
    sr.*,
    d.name  AS department_name,
    b.name  AS brand_name,
    rp.name AS receiver_name,
    u.fullname AS creator_name
FROM service_requests sr
LEFT JOIN departments d
    ON d.id = sr.department_id
LEFT JOIN brands b
    ON b.id = sr.brand_id
LEFT JOIN persons rp
    ON rp.id = sr.receiver_person_id
LEFT JOIN users u
    ON u.id = sr.created_by
    $whereSql
ORDER BY sr.created_at DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();


$departments = $db->query("SELECT id,name FROM departments  ORDER BY name ASC")->fetchAll();
$servicesList = $db->query("
SELECT DISTINCT service_name
FROM service_requests
ORDER BY service_name
")->fetchAll(PDO::FETCH_COLUMN);

if (!empty($department_id)) {
    $stmt = $db->prepare("SELECT name FROM departments WHERE id = ?");
    $stmt->execute([$department_id]);
    $department_name = $stmt->fetchColumn();
}
// ساخت تاریخ نمایشی
$display_date_from = !empty($date_from) ? fa_number($date_from) : '';
$display_date_to   = !empty($date_to) ? fa_number($date_to) : '';

// ساخت اطلاعات فیلترها برای نمایش
$filterText = '';
$filters = [];
if (!empty($service_name)) $filters[] = "<span>فعالیت:</span> " . htmlspecialchars($service_name);
if (!empty($department_name)) $filters[] = "<span>بخش:</span> " . htmlspecialchars($department_name);
if (!empty($display_date_from)) $filters[] = "<span>از تاریخ:</span> " . htmlspecialchars($display_date_from);
if (!empty($display_date_to)) $filters[] = "<span>تا تاریخ:</span> " . htmlspecialchars($display_date_to);


// اگر درخواست Ajax است، فقط داده‌های JSON را برگردان
if ($isAjax) {
// ساخت HTML جدول
    ob_start();

    ?>
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
        <?php if (empty($services)): ?>
            <tr>
                <td colspan="8" class="no-data">📭 هیچ تیکتی با این فیلترها یافت نشد</td>
            </tr>
        <?php else: ?>
            <?php $i = 1; ?>
            <?php foreach ($services as $s): ?>
                <tr>
                    <td><?= fa_number($i++) ?></td>
                    <?php foreach ($selectedColumns as $col): ?>
                        <?php if(isset($availableColumns[$col])): ?>
                            <?php
                            $value = $s[$col] ?? '-';
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

    // ارسال پاسخ JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'table' => $tableHtml,
        'filterInfo' => $filterText
    ]);
    exit;
}

?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>گزارشات فعالیت</title>
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
            <h1>📊 گزارشات فعالیت</h1>
        </div>

        <div class="filter-card">
            <h2>🔍 فیلترها</h2>
            <form method="post" id="filterform">
            <div class="columns-box">
                <div class="checkbox-grid">

                    <label><input type="checkbox" name="columns[]" value="service_name" checked> فعالیت</label>
                    <label><input type="checkbox" name="columns[]" value="department_name" checked> بخش</label>
                    <label><input type="checkbox" name="columns[]" value="brand_name" checked> برند</label>
                    <label><input type="checkbox" name="columns[]" value="receiver_name" checked> تحویل گیرنده</label>
                    <label><input type="checkbox" name="columns[]" value="serial_number" checked> سریال</label>
                    <label><input type="checkbox" name="columns[]" value="computer_code" checked> کد رایانه</label>
                    <label><input type="checkbox" name="columns[]" value="created_at" checked> تاریخ ثبت</label>

                </div>
            </div>

                <div class="filter-row">
                    <!-- انتخابگر تاریخ سه سطحی -->

                    <div class="search-group">
                        <label>از تاریخ </label>
                        <input type="text" id="date_from" name="date_from" class="form-control" placeholder="انتخاب کنید">
                        
                    </div>

                    <div class="search-group">
                        <label>تا تاریخ </label>
                        <input type="text" id="date_to" name="date_to" class="form-control" placeholder="انتخاب کنید" >

                    </div>
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn-filter" onclick="searchReport()" >🔍 اعمال فیلتر</button>
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
                <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="8" class="text-center">📭 هیچ تیکتی با این فیلترها یافت نشد</td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1; ?>
                    <?php foreach ($services as $s): ?>
                        <tr>
                            <td><?= fa_number($i++) ?></td>

                            <?php foreach ($selectedColumns as $col): ?>
                                <?php if(isset($availableColumns[$col])): ?>

                                    <?php
                                    $value = $s[$col] ?? '-';

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
        </div>
    </div>
</div>
</body>
</html>