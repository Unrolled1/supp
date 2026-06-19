<?php
session_start();
require_once 'db.php';
require_once 'assets/jdf.php';
require_once 'functions.php';

date_default_timezone_set('Asia/Tehran');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
if (!isAdmin() || !canViewReports()) {
    header('Location: admin.php');
    exit;
}

$db = getDB();

// دریافت پارامترهای فیلتر
$department_id = $_GET['department_id'] ?? '';
$status = $_GET['status'] ?? '';

// تاریخ شمسی از لیست‌ها
$from_year = $_GET['from_year'] ?? '';
$from_month = $_GET['from_month'] ?? '';
$from_day = $_GET['from_day'] ?? '';
$to_year = $_GET['to_year'] ?? '';
$to_month = $_GET['to_month'] ?? '';
$to_day = $_GET['to_day'] ?? '';

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
    SELECT t.*, d.name as department_name, u.username, u.fullname as user_fullname
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
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>گزارشات تیکت‌ها</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/sidebar.css">
    <link rel="stylesheet" href="styles/admin-reports.css">
    <style>
        .date-group {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .date-select {
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Vazir', Tahoma, sans-serif;
            background: white;
        }
        .date-range {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        .date-range-item {
            flex: 1;
            min-width: 200px;
        }
        .date-range-item label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 13px;
        }
    </style>
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
            <form method="get" class="filter-form">
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

                <!-- انتخابگر تاریخ سه سطحی -->
                <div class="date-range">
                    <div class="date-range-item">
                        <label>📅 از تاریخ:</label>
                        <div class="date-group">
                            <select name="from_day" class="date-select">
                                <option value="">روز</option>
                                <?php for($d = 1; $d <= 31; $d++): ?>
                                    <option value="<?php echo sprintf("%02d", $d); ?>" <?php echo $from_day == sprintf("%02d", $d) ? 'selected' : ''; ?>>
                                        <?php echo fa_number(sprintf("%02d", $d)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <select name="from_month" class="date-select">
                                <option value="">ماه</option>
                                <?php for($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo sprintf("%02d", $m); ?>" <?php echo $from_month == sprintf("%02d", $m) ? 'selected' : ''; ?>>
                                        <?php echo fa_number($m); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <select name="from_year" class="date-select">
                                <option value="">سال</option>
                                <?php for($y = 1400; $y <= 1405; $y++): ?>
                                    <option value="<?php echo $y; ?>" <?php echo $from_year == $y ? 'selected' : ''; ?>>
                                        <?php echo fa_number($y); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="date-range-item">
                        <label>📅 تا تاریخ:</label>
                        <div class="date-group">
                            <select name="to_day" class="date-select">
                                <option value="">روز</option>
                                <?php for($d = 1; $d <= 31; $d++): ?>
                                    <option value="<?php echo sprintf("%02d", $d); ?>" <?php echo $to_day == sprintf("%02d", $d) ? 'selected' : ''; ?>>
                                        <?php echo fa_number(sprintf("%02d", $d)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <select name="to_month" class="date-select">
                                <option value="">ماه</option>
                                <?php for($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo sprintf("%02d", $m); ?>" <?php echo $to_month == sprintf("%02d", $m) ? 'selected' : ''; ?>>
                                        <?php echo fa_number($m); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <select name="to_year" class="date-select">
                                <option value="">سال</option>
                                <?php for($y = 1400; $y <= 1405; $y++): ?>
                                    <option value="<?php echo $y; ?>" <?php echo $to_year == $y ? 'selected' : ''; ?>>
                                        <?php echo fa_number($y); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter">🔍 اعمال فیلتر</button>
                    <a href="admin_reports.php" class="btn-reset">🗑️ پاک کردن فیلترها</a>
                    <a href="#" onclick="openPrintWindow()" class="btn-pdf">🖨️ پرینت گزارش</a>
                </div>
            </form>
        </div>

        <!-- کارت آمار -->
        <div class="stats-card">
            <div class="stat-item">
                <div class="stat-num"><?php echo fa_number($total); ?></div>
                <div class="stat-title">کل تیکت‌ها</div>
            </div>
            <div class="stat-item review">
                <div class="stat-num"><?php echo fa_number($reviewCount); ?></div>
                <div class="stat-title">در حال بررسی</div>
            </div>
            <div class="stat-item answered">
                <div class="stat-num"><?php echo fa_number($answeredCount); ?></div>
                <div class="stat-title">پاسخ داده شده</div>
            </div>
            <div class="stat-item closed">
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

<script>
    function openPrintWindow() {
        const department_id = document.querySelector('select[name="department_id"]').value;
        const status = document.querySelector('select[name="status"]').value;
        const from_day = document.querySelector('select[name="from_day"]').value;
        const from_month = document.querySelector('select[name="from_month"]').value;
        const from_year = document.querySelector('select[name="from_year"]').value;
        const to_day = document.querySelector('select[name="to_day"]').value;
        const to_month = document.querySelector('select[name="to_month"]').value;
        const to_year = document.querySelector('select[name="to_year"]').value;

        let url = 'assets/print_report.php?';
        if (department_id) url += 'department_id=' + department_id + '&';
        if (status) url += 'status=' + encodeURIComponent(status) + '&';
        if (from_year && from_month && from_day) url += 'from_year=' + from_year + '&from_month=' + from_month + '&from_day=' + from_day + '&';
        if (to_year && to_month && to_day) url += 'to_year=' + to_year + '&to_month=' + to_month + '&to_day=' + to_day;
        if (url.endsWith('&')) url = url.slice(0, -1);
        window.open(url, '_blank');
    }

    function updateClock() {
        fetch('get_time.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('liveClock').innerHTML = '📅 ' + data.datetime;
            })
            .catch(error => console.log('خطا:', error));
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

</body>
</html>