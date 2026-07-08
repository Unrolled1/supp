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

// بررسی آیا پرینت خودکار فعال است
$autoPrint = isset($_GET['print']) && $_GET['print'] == '1';

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
    $params[':date_from'] = $date_from . ' 00:00:00';
}
if (!empty($date_to)) {
    $whereConditions[] = "t.created_at <= :date_to";
    $params[':date_to'] = $date_to . ' 23:59:59';
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
    $display_date_from = fa_number($from_year) . '/' . fa_number($from_month) . '/' . fa_number($from_day);
}
if (!empty($to_year) && !empty($to_month) && !empty($to_day)) {
    $display_date_to = fa_number($to_year) . '/' . fa_number($to_month) . '/' . fa_number($to_day);
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چاپ گزارش تیکت‌ها</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @media print {
            body {
                padding: 0px;
                background: white;
            }
            .no-print {
                display: none !important;
            }
            .stat-box {
                border: 1px solid #ccc !important;
                background: #f9f9f9 !important;
            }
            th {
                background: #667eea !important;
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-header h1 {
                color: #667eea !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        body {
            font-family: 'Vazir', 'Segoe UI', Tahoma, Arial, sans-serif;
            padding: 10px;
            background: #f0f2f5;
            direction: rtl;
        }

        .print-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
        }

        .print-header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }

        .print-header h1 {
            color: #667eea;
            font-size: 26px;
            margin-bottom: 8px;
        }

        .print-header .date {
            color: #888;
            font-size: 13px;
        }

        .filters-info {
            background: #f8f9fe;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 10px;
            font-size: 14px;
            border-right: 4px solid #667eea;
        }

        .filters-info span {
            font-weight: bold;
            color: #667eea;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 10px;
        }

        .stat-box {
            border: 1px solid #e8e8e8;
            border-radius: 12px;
            padding: 18px 20px;
            text-align: center;
            background: #fafafa;
            transition: all 0.3s;
        }

        .stat-num {
            font-size: 30px;
            font-weight: bold;
            color: #667eea;
            direction: ltr;
        }

        .stat-title {
            font-size: 13px;
            color: #888;
            margin-top: 5px;
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid #e8e8e8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th, td {
            border: 1px solid #e0e0e0;
            padding: 10px 12px;
            text-align: center;
        }

        th {
            background: #667eea;
            color: white;
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        tr:hover {
            background: #eef1ff;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 16px;
        }

        .print-footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #aaa;
        }

        /* دکمه‌های پرینت و بستن (فقط در صفحه نمایش) */
        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .action-buttons button {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Vazir', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print {
            background: linear-gradient(135deg, #667eea, #5a67d8);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-close {
            background: #f0f0f0;
            color: #555;
        }

        .btn-close:hover {
            background: #e0e0e0;
        }

        /* تذکر پرینت */
        .print-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 10px;
            text-align: center;
            font-size: 14px;
        }

        .print-notice strong {
            color: #d39e00;
        }
    </style>
</head>
<body>

<div class="print-container">

    <!-- دکمه‌های عملیاتی (فقط در صفحه نمایش) -->
    <div class="action-buttons no-print">
        <button class="btn-print" onclick="window.print();">
            🖨️ پرینت گزارش
        </button>
        <button class="btn-close" onclick="window.close();">
            ✖ بستن
        </button>
    </div>

    <!-- تذکر پرینت خودکار (فقط در صفحه نمایش) -->
    <?php if ($autoPrint): ?>
        <div class="print-notice no-print">
            <strong>⏳ در حال باز کردن پنجره پرینت...</strong>
            <br>
            اگر پنجره پرینت باز نشد، روی دکمه <strong>"🖨️ پرینت گزارش"</strong> کلیک کنید.
        </div>
    <?php endif; ?>

    <!-- هدر گزارش -->
    <div class="print-header">
        <h1>📊 گزارش تیکت‌های پشتیبانی</h1>
        <div class="date">
            تاریخ چاپ: <?php echo fa_number(now()); ?>
        </div>
    </div>

    <!-- اطلاعات فیلترها -->
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

    <!-- آمار -->
    <div class="stats">
        <div class="stat-box">
            <div class="stat-num"><?php echo fa_number($total); ?></div>
            <div class="stat-title">📌 کل تیکت‌ها</div>
        </div>
        <div class="stat-box">
            <div class="stat-num"><?php echo fa_number($reviewCount); ?></div>
            <div class="stat-title">🔄 در حال بررسی</div>
        </div>
        <div class="stat-box">
            <div class="stat-num"><?php echo fa_number($answeredCount); ?></div>
            <div class="stat-title">✅ پاسخ داده شده</div>
        </div>
        <div class="stat-box">
            <div class="stat-num"><?php echo fa_number($closedCount); ?></div>
            <div class="stat-title">🔒 بسته شده</div>
        </div>
    </div>

    <!-- جدول تیکت‌ها -->
    <div class="table-wrapper">
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
                        <td>
                            <?php
                            $statusClass = '';
                            if ($t['status'] == 'در حال بررسی') $statusClass = '🔄';
                            elseif ($t['status'] == 'پاسخ داده شده') $statusClass = '✅';
                            elseif ($t['status'] == 'بسته شده') $statusClass = '🔒';
                            echo $statusClass . ' ' . htmlspecialchars($t['status']);
                            ?>
                        </td>
                        <td><?php echo fa_number($t['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- فوتر -->
    <div class="print-footer">
        سیستم پشتیبانی بیمارستان | چاپ شده در <?php echo fa_number(now()); ?>
    </div>

</div>

<!-- اسکریپت پرینت خودکار -->
<?php if ($autoPrint): ?>
    <script>
        // وقتی صفحه کامل لود شد، پرینت را باز کن
        window.onload = function() {
            // کمی تاخیر برای اطمینان از لود کامل
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
<?php endif; ?>

</body>
</html>