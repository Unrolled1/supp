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
$status = $_POST['status'] ?? '';
// تاریخ شمسی از لیست‌ها
$from_year = $_POST['from_year'] ?? '';
$from_month = $_POST['from_month'] ?? '';
$from_day = $_POST['from_day'] ?? '';
$to_year = $_POST['to_year'] ?? '';
$to_month = $_POST['to_month'] ?? '';
$to_day = $_POST['to_day'] ?? '';


// اگر reset باشد، همه پارامترها را خالی کن
if ($isReset) {
    $department_id = '';
    $status = '';
    $from_day = '';
    $from_month = '';
    $from_year = '';
    $to_day = '';
    $to_month = '';
    $to_year = '';
}

// ساخت تاریخ کامل برای کوئری
$date_from = '';
$date_to = '';

if (!empty($from_year) && !empty($from_month) && !empty($from_day)) {
    $date_from = sprintf("%04d-%02d-%02d", $from_year, $from_month, $from_day);
}
if (!empty($to_year) && !empty($to_month) && !empty($to_day)) {
    $date_to = sprintf("%04d-%02d-%02d", $to_year, $to_month, $to_day);
}

// ساخت کوئری شرطی
$whereConditions = [];
$params = [];

if (!empty($department_id)) {
    $whereConditions[] = "t.department_id = :department_id";
    $params[':department_id'] = $department_id;
}
if (!empty($status)) {
    $whereConditions[] = "t.status = :status";
    $params[':status'] = $status;
}
if (!empty($date_from)) {
    $whereConditions[] = "t.created_at >= :date_from";
    $params[':date_from'] = $date_from;
}
if (!empty($date_to)) {
    $whereConditions[] = "t.created_at <= :date_to";
    $params[':date_to'] = $date_to;
}

$whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// گرفتن تیکت‌ها
$sql = "
    SELECT t.*, d.name as department_name, u.username, u.fullname as fullname
    FROM tickets t
    LEFT JOIN departments d ON t.department_id = d.id
    LEFT JOIN users u ON t.user_id = u.id
    $whereSql
    ORDER BY t.created_at DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

// آمار
$total = count($tickets);
$reviewCount = 0;
$answeredCount = 0;
$closedCount = 0;

foreach ($tickets as $t) {
    switch($t['status']) {
        case 'در حال بررسی': $reviewCount++; break;
        case 'پاسخ داده شده': $answeredCount++; break;
        case 'بسته شده': $closedCount++; break;
    }
}

$departments = $db->query("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC")->fetchAll();
$department_name = '';

if (!empty($department_id)) {
    $stmt = $db->prepare("SELECT name FROM departments WHERE id = ?");
    $stmt->execute([$department_id]);
    $department_name = $stmt->fetchColumn();
}
// ساخت تاریخ نمایشی
$display_date_from = '';
$display_date_to = '';
if (!empty($from_year) && !empty($from_month) && !empty($from_day)) {
    $display_date_from = fa_number($from_year) . '/' . fa_number($from_month) . '/' . fa_number($from_day);
}
if (!empty($to_year) && !empty($to_month) && !empty($to_day)) {
    $display_date_to = fa_number($to_year) . '/' . fa_number($to_month) . '/' . fa_number($to_day);
}

// ساخت اطلاعات فیلترها برای نمایش
$filterText = '';
$filters = [];
if (!empty($department_name)) $filters[] = "<span>بخش:</span> " . htmlspecialchars($department_name);
if (!empty($status)) $filters[] = "<span>وضعیت:</span> " . htmlspecialchars($status);
if (!empty($display_date_from)) $filters[] = "<span>از تاریخ:</span> " . htmlspecialchars($display_date_from);
if (!empty($display_date_to)) $filters[] = "<span>تا تاریخ:</span> " . htmlspecialchars($display_date_to);

if (!empty($filters)) {
    $filterText = "🔍 فیلترهای اعمال شده: " . implode(" | ", $filters);
} else {
    $filterText = "📋 نمایش همه تیکت‌ها";
}

// اگر درخواست Ajax است، فقط داده‌های JSON را برگردان
if ($isAjax) {
// ساخت HTML جدول
ob_start();

?>
<table>
    <thead>
    <tr>
        <th>#</th>
        <th>کد پیگیری</th>
        <th>بخش</th>
        <th>نام و نام خانوادگی</th>
        <th>کاربر</th>
        <th>موضوع</th>
        <th>وضعیت</th>
        <th>تاریخ ثبت</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($tickets)): ?>
        <tr>
            <td colspan="8" class="no-data">📭 هیچ تیکتی با این فیلترها یافت نشد</td>
        </tr>
    <?php else: ?>
        <?php $i = 1; ?>
        <?php foreach ($tickets as $t): ?>
            <tr>
                <td><?php echo fa_number($i++); ?></td>
                <td><?php echo fa_number($t['tracking_code']); ?></td>
                <td><?php echo htmlspecialchars($t['department_name'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($t['fullname']); ?></td>
                <td><?php echo htmlspecialchars($t['username'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($t['subject']); ?></td>
                <td><?php echo htmlspecialchars($t['status']); ?></td>
                <td><?php echo fa_number($t['created_at']); ?></td>
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
        'stats' => [
            'total' => fa_number($total),
            'review' => fa_number($reviewCount),
            'answered' => fa_number($answeredCount),
            'closed' => fa_number($closedCount)
        ],
        'filterInfo' => $filterText
    ]);
    exit;
}

?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>گزارشات تیکت‌ها</title>
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
            <h1>📊 گزارشات تیکت‌ها</h1>
        </div>

        <div class="filter-card">
            <h2>🔍 فیلترها</h2>
            <form method="post" id="filterform">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>بخش:</label>
                        <select name="department_id">
                            <option value="">همه بخش‌ها</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo $department_id == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>وضعیت:</label>
                        <select name="status">
                            <option value="">همه وضعیت‌ها</option>
                            <option value="در حال بررسی" <?php echo $status == 'در حال بررسی' ? 'selected' : ''; ?>>در حال بررسی</option>
                            <option value="پاسخ داده شده" <?php echo $status == 'پاسخ داده شده' ? 'selected' : ''; ?>>پاسخ داده شده</option>
                            <option value="بسته شده" <?php echo $status == 'بسته شده' ? 'selected' : ''; ?>>بسته شده</option>
                        </select>
                    </div>


                </div>
                <div class="filter-row">
                <!-- انتخابگر تاریخ سه سطحی -->

                <div class="search-group">
                    <label>از تاریخ </label>
                    <div id="search_date_from_container"></div>
                    <input type="hidden" id="search_date_from" value="<?php echo htmlspecialchars($_POST['date_from'] ?? ''); ?>">
                </div>

                <div class="search-group">
                    <label>تا تاریخ </label>
                    <div id="search_date_to_container"></div>
                    <input type="hidden" id="search_date_to" value="<?php echo htmlspecialchars($_POST['date_to'] ?? ''); ?>">
                </div>
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn-filter">🔍 اعمال فیلتر</button>
                    <button type="button" class="btn-reset">🗑️ پاک کردن فیلترها</button>
                    <button type="button" class="btn-pdf">🖨️ پرینت گزارش</button>
                </div>

            </form>
        </div>

        <!-- کارت آمار -->
        <div class="stats">
            <div class="stat-box">
                <div class="stat-num"><?php echo fa_number($total); ?></div>
                <div class="stat-title">کل تیکت‌ها</div>
            </div>
            <div class="stat-box review">
                <div class="stat-num"><?php echo fa_number($reviewCount); ?></div>
                <div class="stat-title">در حال بررسی</div>
            </div>
            <div class="stat-box answered">
                <div class="stat-num"><?php echo fa_number($answeredCount); ?></div>
                <div class="stat-title">پاسخ داده شده</div>
            </div>
            <div class="stat-box closed">
                <div class="stat-num"><?php echo fa_number($closedCount); ?></div>
                <div class="stat-title">بسته شده</div>
            </div>
        </div>

        <!-- جدول نتایج -->
        <div class="reports-table data-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>کد پیگیری</th>
                    <th>بخش</th>
                    <th>نام و نام خانوادگی</th>
                    <th>کاربر</th>
                    <th>موضوع</th>
                    <th>وضعیت</th>
                    <th>تاریخ ثبت</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="8" class="text-center">📭 هیچ تیکتی با این فیلترها یافت نشد</td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1; ?>
                    <?php foreach ($tickets as $t): ?>
                        <?php
                        $statusClass = '';
                        switch($t['status']) {
                            case 'در حال بررسی': $statusClass = 'status-review'; break;
                            case 'پاسخ داده شده': $statusClass = 'status-answered'; break;
                            case 'بسته شده': $statusClass = 'status-closed'; break;
                        }
                        ?>
                        <tr>
                            <td><?php echo fa_number($i++); ?></td>
                            <td><?php echo fa_number($t['tracking_code']); ?></td>
                            <td><?php echo htmlspecialchars($t['department_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($t['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($t['username'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($t['subject']); ?></td>
                            <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $t['status']; ?></span></td>
                            <td><?php echo fa_number($t['created_at']); ?></td>
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