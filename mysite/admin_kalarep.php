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

// تاریخ شمسی از لیست‌ها
$date_from = faToEn($_POST['date_from'] ?? '');
$date_to   = faToEn($_POST['date_to'] ?? '');

// ساخت کوئری شرطی
$whereConditions = [];
$params = [];

    if (!empty($department_id)) {
        $whereConditions[] = "k.department_id = :department_id";
        $params[':department_id'] = $department_id;
    }

    if (!empty($brand_id)) {
        $whereConditions[] = "k.brand_id = :brand_id";
        $params[':brand_id'] = $brand_id;
    }

    if (!empty($date_from)) {
        $whereConditions[] = "k.created_at >= :date_from";
        $params[':date_from'] = $date_from;
    }

    if (!empty($date_to)) {
        $whereConditions[] = "k.created_at <= :date_to";
        $params[':date_to'] = $date_to;
    }

    $selectedColumns = $_POST['columns'] ?? [
    'computer_code',
    'property_code',
    'name',
    'department_name',
    'receiver_name',
    'quantity',
    'brand_name',
    'serial_number',
    'created_at'
    ];

    if (empty($selectedColumns)) {
    $selectedColumns = [
        'computer_code',
        'property_code',
        'name',
        'department_name',
        'receiver_name',
        'quantity',
        'brand_name',
        'serial_number',
        'created_at'
    ];
    }
$availableColumns = [
    'computer_code'   => 'کد رایانه',
    'property_code'   => 'کد اموال',
    'name'            => 'نام کالا',
    'department_name' => 'بخش',
    'receiver_name'   => 'تحویل گیرنده',
    'quantity'        => 'تعداد',
    'brand_name'      => 'برند',
    'serial_number'   => 'سریال',
    'created_at'      => 'تاریخ ثبت'
];

$whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// گرفتن تیکت‌ها
$sql = "
    SELECT
k.*,
d.name AS department_name,
b.name AS brand_name,
p.name AS receiver_name,
u.fullname AS creator_name
FROM kala k

LEFT JOIN departments d
ON d.id=k.department_id

LEFT JOIN brands b
ON b.id=k.brand_id

LEFT JOIN persons p
ON p.id=k.receiver_person_id

LEFT JOIN users u
ON u.id=k.created_by
    $whereSql
ORDER BY k.created_at DESC, k.id DESC  ";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$kalas=$stmt->fetchAll();

$departments = $db->query("SELECT id,name FROM departments  ORDER BY name ASC")->fetchAll();
$brands = $db->query("SELECT id,name FROM brands ORDER BY name")->fetchAll();
$department_name = '';
$brand_name = '';

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

$filterItems = [];

if ($department_name)
    $filterItems[] = "<span>بخش:</span> ".htmlspecialchars($department_name);

if ($brand_name)
    $filterItems[] = "<span>برند:</span> ".htmlspecialchars($brand_name);

if ($display_date_from)
    $filterItems[] = "<span>از تاریخ:</span> ".$display_date_from;

if ($display_date_to)
    $filterItems[] = "<span>تا تاریخ:</span> ".$display_date_to;

$filterText = empty($filterItems)? "📋 نمایش همه کالاها" : "🔍 فیلترهای اعمال شده: ".implode(" | ", $filterItems);
$filterItems[] = "<span>تعداد:</span> ".fa_number(count($kalas));
// اگر درخواست Ajax است، فقط داده‌های JSON را برگردان
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

        <?php if (empty($kalas)): ?>

            <tr>
                <td colspan="<?= count($selectedColumns) + 1 ?>" class="no-data">
                    📭 هیچ کالایی با این فیلترها یافت نشد
                </td>
            </tr>

        <?php else: ?>

            <?php $i = 1; ?>

            <?php foreach ($kalas as $k): ?>

                <tr>

                    <td><?= fa_number($i++) ?></td>

                    <?php foreach ($selectedColumns as $col): ?>

                        <?php if (isset($availableColumns[$col])): ?>

                            <?php
                            $value = $k[$col] ?? '-';

                            if ($col == 'created_at' && $value != '-') {
                                $value = fa_number(str_replace('-', '/', $value));
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
    <title>گزارشات کالا</title>
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
            <h1>📊 گزارشات کالا</h1>
        </div>

        <div class="filter-card">
            <h2>🔍 فیلترها</h2>
            <form method="post" id="filterform">
            <div class="columns-box">

                <div class="checkbox-grid">

<label><input type="checkbox" name="columns[]" value="computer_code" checked> کد رایانه</label>

<label><input type="checkbox" name="columns[]" value="property_code" checked> کد اموال</label>

<label><input type="checkbox" name="columns[]" value="name" checked> نام کالا</label>

<label><input type="checkbox" name="columns[]" value="department_name" checked> بخش</label>

<label><input type="checkbox" name="columns[]" value="receiver_name" checked> تحویل گیرنده</label>

<label><input type="checkbox" name="columns[]" value="quantity" checked> تعداد</label>

<label><input type="checkbox" name="columns[]" value="brand_name" checked> برند</label>

<label><input type="checkbox" name="columns[]" value="serial_number" checked> سریال</label>

<label><input type="checkbox" name="columns[]" value="created_at" checked> تاریخ ثبت</label>

</div>
            </div>
            
            <div class="filter-row">
<div class="filter-group">
    <label>بخش</label>
    <select name="department_id">
        <option value="">همه</option>
        <?php foreach($departments as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $department_id==$d['id']?'selected':'' ?>>
                <?= htmlspecialchars($d['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="filter-group">
    <label>برند</label>
    <select name="brand_id">
        <option value="">همه</option>
        <?php
        foreach($brands as $b):
        ?>
            <option value="<?= $b['id'] ?>" <?= $brand_id==$b['id']?'selected':'' ?>>
                <?= htmlspecialchars($b['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
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
                <?php if (empty($kalas)): ?>
                    <tr>
                        <td colspan="<?= count($selectedColumns)+1 ?>">📭 هیچ کالایی با این فیلترها یافت نشد</td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1; ?>
                    <?php foreach ($kalas as $kala): ?>
                        <tr>
                            <td><?= fa_number($i++) ?></td>

                            <?php foreach ($selectedColumns as $col): ?>
                                <?php if(isset($availableColumns[$col])): ?>

                                    <?php
                                    $value = $kala[$col] ?? '-';

                                    if ($col == 'created_at' && $value != '-') {
                                        $value = fa_number(str_replace('-', '/', $value));
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
    url: "admin_kalarep.php",
    printUrl: "assets/print_report.php",
    table: ".reports-table",
    filterInfo: true,
    type: "kala"
 };
    </script>
</body>
</html>