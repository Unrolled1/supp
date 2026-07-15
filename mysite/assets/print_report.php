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
$type = $_GET['type'] ?? '';
$tableHeaders = [
    'ردیف',
    'کد پیگیری',
    'بخش',
    'نام و نام خانوادگی',
    'کاربر',
    'موضوع',
    'وضعیت',
    'تاریخ ثبت'
];
$tableRows = [];

$i = 1;

foreach ($rows as $row) {

    $tableRows[] = [
        fa_number($i++),
        fa_number($row['tracking_code']),
        htmlspecialchars($row['department_name'] ?? '-'),
        htmlspecialchars($row['fullname']),
        htmlspecialchars($row['username'] ?? '-'),
        htmlspecialchars($row['subject']),
        htmlspecialchars($row['status']),
        fa_number($row['created_at'])
    ];

}

switch ($type) {

    case 'ticket':
        $pageTitle = 'گزارش درخواست‌ها';
        break;

    case 'service':
        $pageTitle = 'گزارش ثبت فعالیت';
        break;

    case 'system':
        $pageTitle = 'گزارش سیستم‌ها';
        break;

    case 'printer':
        $pageTitle = 'گزارش پرینترها';
        break;

    case 'kala':
        $pageTitle = 'گزارش کالاها';
        break;

    case 'invoice':
        $pageTitle = 'گزارش فاکتورها';
        break;

    default:
        die('نوع گزارش نامعتبر است.');
}

?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چاپ گزارش </title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
        <h1>📊 <?php echo $pageTitle; ?></h1>
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
                <?php foreach ($tableHeaders as $header): ?>
                    <th><?= htmlspecialchars($header) ?></th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>

                <?php if (empty($tableRows)): ?>

                    <tr>
                        <td colspan="<?= count($tableHeaders) ?>" class="no-data">
                            📭 هیچ موردی یافت نشد
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($tableRows as $row): ?>

                        <tr>

                            <?php foreach ($row as $cell): ?>

                                <td><?= $cell ?></td>

                            <?php endforeach; ?>

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