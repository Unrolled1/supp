<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../assets/jdf.php';

date_default_timezone_set('Asia/Tehran');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$db = getDB();

// دریافت پارامترها از GET
$department_id = $_GET['department_id'] ?? '';
$status = $_GET['status'] ?? '';

// دریافت تاریخ از پارامترهای جداگانه
$from_day = $_GET['from_day'] ?? '';
$from_month = $_GET['from_month'] ?? '';
$from_year = $_GET['from_year'] ?? '';
$to_day = $_GET['to_day'] ?? '';
$to_month = $_GET['to_month'] ?? '';
$to_year = $_GET['to_year'] ?? '';

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

// گرفتن نام بخش برای نمایش
$department_name = '';
if (!empty($department_id)) {
    $deptStmt = $db->prepare("SELECT name FROM departments WHERE id = :id");
    $deptStmt->execute([':id' => $department_id]);
    $department_name = $deptStmt->fetchColumn();
}

// ساخت تاریخ نمایشی برای فیلترها
$display_date_from = '';
$display_date_to = '';
if (!empty($from_year) && !empty($from_month) && !empty($from_day)) {
    $display_date_from = fa_number($from_year) . '-' . fa_number($from_month) . '-' . fa_number($from_day);
}
if (!empty($to_year) && !empty($to_month) && !empty($to_day)) {
    $display_date_to = fa_number($to_year) . '-' . fa_number($to_month) . '-' . fa_number($to_day);
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>چاپ گزارش تیکت‌ها</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Vazir', 'Segoe UI', Arial, sans-serif;
            padding: 30px;
            background: white;
            direction: rtl;
        }

        .print-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #667eea;
        }

        .print-header h1 {
            color: #667eea;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .print-header .date {
            color: #666;
            font-size: 12px;
        }

        .filters-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .filters-info span {
            font-weight: bold;
            color: #667eea;
        }

        .stats {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-box {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px 25px;
            text-align: center;
            flex: 1;
            min-width: 100px;
            background: #fafafa;
        }

        .stat-num {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-title {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px 8px;
            text-align: center;
            font-size: 12px;
        }

        th {
            background: #667eea;
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .print-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 11px;
            color: #888;
        }

        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-family: 'Vazir', sans-serif;
        }

        .btn-print:hover {
            background: #5a67d8;
        }

        @media print {
            .print-button {
                display: none;
            }
            body {
                padding: 15px;
            }
            .stat-box {
                border: 1px solid #ccc;
            }
        }
    </style>
</head>
<body>

<div class="print-button">
    <button class="btn-print" onclick="window.print();">🖨️ پرینت گزارش</button>
</div>

<div class="print-header">
    <h1>📊 گزارش تیکت‌های پشتیبانی</h1>
    <div class="date">تاریخ چاپ: <?php echo fa_number(now()); ?></div>
</div>

<div class="filters-info">
    <?php
    $filters = [];
    if (!empty($department_name)) $filters[] = "<span>بخش:</span> " . htmlspecialchars($department_name);
    if (!empty($status)) $filters[] = "<span>وضعیت:</span> " . htmlspecialchars($status);
    if (!empty($display_date_from)) $filters[] = "<span>از تاریخ:</span> " . htmlspecialchars($display_date_from);
    if (!empty($display_date_to)) $filters[] = "<span>تا تاریخ:</span> " . htmlspecialchars($display_date_to);

    if (!empty($filters)) {
        echo "🔍 فیلترهای اعمال شده: " . implode(" | ", $filters);
    } else {
        echo "📋 نمایش همه تیکت‌ها";
    }
    ?>
</div>

<div class="stats">
    <div class="stat-box">
        <div class="stat-num"><?php echo fa_number($total); ?></div>
        <div class="stat-title">کل تیکت‌ها</div>
    </div>
    <div class="stat-box">
        <div class="stat-num"><?php echo fa_number($reviewCount); ?></div>
        <div class="stat-title">در حال بررسی</div>
    </div>
    <div class="stat-box">
        <div class="stat-num"><?php echo fa_number($answeredCount); ?></div>
        <div class="stat-title">پاسخ داده شده</div>
    </div>
    <div class="stat-box">
        <div class="stat-num"><?php echo fa_number($closedCount); ?></div>
        <div class="stat-title">بسته شده</div>
    </div>
</div>

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
            <td colspan="8" style="text-align: center;">📭 هیچ تیکتی با این فیلترها یافت نشد</td>
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

<div class="print-footer">
    سیستم پشتیبانی بیمارستان | چاپ شده در <?php echo fa_number(now()); ?>
</div>

</body>
</html>