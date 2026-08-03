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

// تاریخ شمسی از لیست‌ها
$date_from = faToEn($_POST['date_from'] ?? '');
$date_to   = faToEn($_POST['date_to'] ?? '');

// ساخت کوئری شرطی
$whereConditions = [];
$params = [];

if (!empty($date_from)) {
    $whereConditions[] = "i.created_at >= :date_from";
    $params[':date_from'] = $date_from;
}

if (!empty($date_to)) {
    $whereConditions[] = "i.created_at <= :date_to";
    $params[':date_to'] = $date_to;
}

$selectedColumns = $_POST['columns'] ?? [
        'company_name',
        'invoice_number',
        'subject',
        'amount',
        'description',
        'created_at'
    ];
     $availableColumns = [
        'company_name'    => 'نام شرکت',
        'invoice_number' => 'شماره فاکتور',
        'subject'      => 'موضوع فاکتور',
        'amount'   => 'مبلغ فاکتور',
        'description'   => 'توضیحات',
        'created_at'      => 'تاریخ ثبت'
    ];

$whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// گرفتن تیکت‌ها
$sql = "
    SELECT
    i.*,
 u.fullname AS creator_name
FROM invoices i
LEFT JOIN users u
    ON u.id = i.created_by
    $whereSql
ORDER BY i.created_at DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$invoices = $stmt->fetchAll();


// ساخت تاریخ نمایشی
$display_date_from = !empty($date_from) ? fa_number($date_from) : '';
$display_date_to   = !empty($date_to) ? fa_number($date_to) : '';

// ساخت اطلاعات فیلترها برای نمایش
$filterText = '';
$filters = [];
if (!empty($company_name)) $filters[] = "<span>نام شرکت:</span> " . htmlspecialchars($company_name);
if (!empty($invoice_number)) $filters[] = "<span>شماره فاکتور:</span> " . htmlspecialchars($invoice_number);
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
        <?php if (empty($invoices)): ?>
            <tr>
                <td colspan="8" class="no-data">📭 هیچ تیکتی با این فیلترها یافت نشد</td>
            </tr>
        <?php else: ?>
            <?php $i = 1; ?>
            <?php foreach ($invoices as $invoice): ?>
                <tr>
                    <td><?= fa_number($i++) ?></td>
                    <?php foreach ($selectedColumns as $col): ?>
                        <?php if(isset($availableColumns[$col])): ?>
                            <?php
                            $value = $invoice[$col] ?? '-';
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
    <title>گزارشات فاکتور</title>
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
            <h1>📊 گزارشات فاکتور</h1>
        </div>

        <div class="filter-card">
            <h2>🔍 فیلترها</h2>
            <form method="post" id="filterform">
            <div class="columns-box">
                <div class="checkbox-grid">

                    <label><input type="checkbox" name="columns[]" value="company_name" checked> نام شرکت</label>
                    <label><input type="checkbox" name="columns[]" value="invoice_number" checked> شماره فاکتور</label>
                    <label><input type="checkbox" name="columns[]" value="subject" checked> موضوع</label>
                    <label><input type="checkbox" name="columns[]" value="amount" checked> مبلغ فاکتور</label>
                    <label><input type="checkbox" name="columns[]" value="description" checked> توضیحات</label>
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
                    <button type="button" class="btn-filter" >🔍 اعمال فیلتر</button>
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
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="8" class="text-center">📭 هیچ تیکتی با این فیلترها یافت نشد</td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1; ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?= fa_number($i++) ?></td>

                            <?php foreach ($selectedColumns as $col): ?>
                                <?php if(isset($availableColumns[$col])): ?>

                                    <?php
                                    $value = $invoice[$col] ?? '-';

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
 <script>
    window.reportConfig = {
    url: "admin_invoicerep.php",
    printUrl: "assets/print_report.php",
    table: ".reports-table",
    filterInfo: true,
    type: "invoice"
 };
    </script>
</body>
</html>