<?php
session_start();
require_once 'config/config.php';
require_once 'db.php';
require_once 'assets/jdf.php';
require_once 'functions.php';

date_default_timezone_set('Asia/Tehran');

// مدیریت پیام‌ها
$successMessage = '';
$errorMessage = '';

if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// بررسی لاگین
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isAdmin() || !canViewInvoices()) {
    header('Location: requests.php');
    exit;
}

$db = getDB();

// ============================================
// پردازش فرم حذف
// ============================================

if (isset($_POST['delete_invoice']) && canDeleteInvoices()) {
    $invoice_id = filter_var($_POST['invoice_id'], FILTER_VALIDATE_INT);

    if ($invoice_id) {
        $deleteStmt = $db->prepare("DELETE FROM invoices WHERE id = :id");
        $deleteStmt->execute([':id' => $invoice_id]);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $invoice_id]);
            exit;
        }
    }
    header('Location: admin_invoices.php');
    exit;
}

// ============================================
// پردازش فرم ویرایش
// ============================================

if (isset($_POST['edit_invoice']) && canEditInvoices()) {
    $invoice_id = filter_var($_POST['invoice_id'], FILTER_VALIDATE_INT);
    $company_name = htmlspecialchars(trim($_POST['company_name']));
    $invoice_number = htmlspecialchars(trim($_POST['invoice_number']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $amount = str_replace(',', '', $_POST['amount']);
    $amount = (int)$amount;
    $description = htmlspecialchars(trim($_POST['description']));

    // تاریخ فاکتور
    $invoice_date = null;
    if (!empty($_POST['year']) && !empty($_POST['month']) && !empty($_POST['day'])) {
        $year = (int)$_POST['year'];
        $month = (int)$_POST['month'];
        $day = (int)$_POST['day'];
        $timestamp = jmktime(0, 0, 0, $month, $day, $year);
        $invoice_date = date('Y-m-d', $timestamp);
    }

    $updateStmt = $db->prepare("UPDATE invoices SET company_name = :company_name, invoice_number = :invoice_number, subject = :subject, amount = :amount, invoice_date = :invoice_date, description = :description WHERE id = :id");
    $updateStmt->execute([
        ':company_name' => $company_name,
        ':invoice_number' => $invoice_number,
        ':subject' => $subject,
        ':amount' => $amount,
        ':invoice_date' => $invoice_date,
        ':description' => $description,
        ':id' => $invoice_id
    ]);

    $_SESSION['success_message'] = "✅ فاکتور با موفقیت ویرایش شد";
    header('Location: admin_invoices.php');
    exit;
}
// گرفتن لیست موضوعات
$topics = $db->query("SELECT id, name FROM topics ORDER BY name ASC")->fetchAll();

// ============================================
// پردازش فرم افزودن فاکتور جدید
// ============================================

if (isset($_POST['add_invoice']) && canEditInvoices()) {
    $company_name = htmlspecialchars(trim($_POST['company_name']));
    $invoice_number = htmlspecialchars(trim($_POST['invoice_number']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $amount = str_replace(',', '', $_POST['amount']);
    $amount = (int)$amount;
    $description = htmlspecialchars(trim($_POST['description']));
    $created_at = faToEn($_POST['created_at'] ?? '');



    $insertStmt = $db->prepare("INSERT INTO invoices (company_name, invoice_number, subject, amount,  description, created_at, created_by) 
VALUES (:company_name, :invoice_number, :subject, :amount,  :description, :created_at, :created_by)");

    if ($insertStmt->execute([
        ':company_name' => $company_name,
        ':invoice_number' => $invoice_number,
        ':subject' => $subject,
        ':amount' => $amount,
        ':description' => $description,
        ':created_at' => $created_at,
        ':created_by' => $_SESSION['user_id']
    ])) {
        $_SESSION['success_message'] = "✅ فاکتور با موفقیت اضافه شد";
    } else {
        $_SESSION['error_message'] = "❌ خطا در افزودن فاکتور";
    }

    header('Location: admin_invoices.php');
    exit;
}
$isAjax         = isset($_POST['ajax']);
$company_name   = $_POST['company_name'] ?? '';
$invoice_number = $_POST['invoice_number'] ?? '';
$subject        = $_POST['subject'] ?? '';
$date_from      = $_POST['date_from'] ?? '';
$date_to        = $_POST['date_to'] ?? '';

// ============================================
// گرفتن لیست فاکتورها با فیلتر
// ============================================

$where = [];
$params = [];

if (isset($_GET['company_name']) && !empty($_GET['company_name'])) {
    $where[] = "company_name LIKE :company_name";
    $params[':company_name'] = '%' . $_GET['company_name'] . '%';
}
if (isset($_GET['invoice_number']) && !empty($_GET['invoice_number'])) {
    $where[] = "invoice_number LIKE :invoice_number";
    $params[':invoice_number'] = '%' . $_GET['invoice_number'] . '%';
}
if (isset($_GET['subject']) && !empty($_GET['subject'])) {
    $where[] = "subject LIKE :subject";
    $params[':subject'] = '%' . $_GET['subject'] . '%';
}
if ($date_from != '') {
    $where[] = "i.created_at >= :from";
    $params[':from'] = $date_from;
}

if ($date_to != '') {
    $where[] = "i.created_at <= :to";
    $params[':to'] = $date_to;
}

$sql = "
SELECT
    i.*,
    u.username AS creator_name
FROM invoices i
LEFT JOIN users u ON i.created_by = u.id
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY i.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);

$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($isAjax) {

ob_start();
?>
<?php if(empty($invoices)): ?>

    <tr>
        <td colspan="8">موردی یافت نشد</td>
    </tr>

<?php else: ?>

    <?php $row_num=1; foreach($invoices as $invoice): ?>

        <tr id="invoice_<?php echo $invoice['id']; ?>">
            <td><?php echo fa_number($row_num); ?></td>
            <td><?php echo htmlspecialchars($invoice['company_name']); ?></td>
            <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
            <td><?php echo htmlspecialchars($invoice['subject'] ?? '-'); ?></td>
            <td><?php echo fa_number(number_format($invoice['amount'], 0)) . ' ریال'; ?></td>
            <td class="description-cell"><?php echo nl2br(htmlspecialchars($invoice['description'] ?? '-')); ?></td>
            <td class="date"><?php echo fa_number(htmlspecialchars($invoice['created_at'])); ?></td>
            <td><?php echo htmlspecialchars($invoice['creator_name'] ?? '-'); ?></td>
            <td class="action-buttons">
                <?php if (canEditInvoices()): ?>
                    <button class="edit-btn" onclick='openEditModal(<?php echo json_encode($invoice); ?>)'>✏️ویرایش</button>
                <?php endif; ?>
                <?php if (canDeleteInvoices()): ?>
                    <button class="delete-btn" onclick="confirmDelete(<?php echo $invoice['id']; ?>, '<?php echo htmlspecialchars($invoice['company_name']); ?>')">🗑️حذف</button>
                <?php endif; ?>
            </td>
        </tr>

    <?php $row_num++; endforeach; ?>

<?php endif; ?>
    <?php
    $table = ob_get_clean();

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => true,
        'table' => $table
    ], JSON_UNESCAPED_UNICODE);

    exit;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت فاکتورها</title>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/persian-date.min.js"></script>
    <link rel="stylesheet" href="assets/styles/persian-datepicker.min.css">
    <script src="assets/js/persian-datepicker.min.js"></script>
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
            <h1>🧾 مدیریت فاکتورها</h1>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- فرم ثبت فاکتور -->
        <?php if (canEditInvoices()): ?>
            <div class="add-card">
                <h2>➕ ثبت فاکتور جدید</h2>
                <form method="post" class="invoices-form">
                    <div class="form-row">
                        <div class="company_name">
                            <label>نام شرکت *</label>
                            <input type="text" name="company_name" required>
                        </div>
                        <div class="invoice_number">
                            <label>شماره فاکتور *</label>
                            <input type="text" name="invoice_number" required>
                        </div>
                        <div class="subject">
                            <label>موضوع فاکتور *</label>
                            <select name="subject" required>
                                <option value="">-- انتخاب کنید --</option>
                                <?php foreach ($topics as $topic): ?>
                                    <option value="<?php echo htmlspecialchars($topic['name']); ?>">
                                        <?php echo htmlspecialchars($topic['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="amount">
                            <label>مبلغ فاکتور *</label>
                            <input type="text" id="amount" name="amount" step="0.01" required>
                        </div>

                    </div>

                    <div class="form-row">
                        <div class="date-group">
                            <label>تاریخ فاکتور</label>
                            <input type="text" id="add_date" name="created_at" class="form-control" >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                        <label>توضیحات</label>
                        <textarea name="description" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="add_invoice" class="btn-add">💾 ذخیره فاکتور</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- بخش جستجو -->
        <div class="search-card">
            <h2>🔍 جستجوی فاکتورها</h2>
            <div class="search-form">
                <div class="search-row">
                    <div class="company_name">
                        <label>نام شرکت</label>
                        <input type="text" id="search_company_name" value="<?php echo htmlspecialchars($_GET['company_name'] ?? ''); ?>">
                    </div>
                    <div class="invoice_number">
                        <label>شماره فاکتور</label>
                        <input type="text" id="search_invoice_number" value="<?php echo htmlspecialchars($_GET['invoice_number'] ?? ''); ?>">
                    </div>
                    <div class="subject">
                        <label>موضوع فاکتور</label>
                        <select id="search_subject" class="search-subject-select">
                            <option value="">-- همه --</option>
                            <?php foreach ($topics as $topic): ?>
                                <option value="<?php echo htmlspecialchars($topic['name']); ?>" <?php echo (isset($_GET['subject']) && $_GET['subject'] == $topic['name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($topic['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="search-row">
                    <div class="date-group">
                        <label>انتخاب سریع</label>
                        <select id="quick_date_select">
                            <option value="">-- انتخاب کنید --</option>
                            <option value="today">📅 روز جاری</option>
                            <option value="week">📅 هفته جاری</option>
                            <option value="month">📅 ماه جاری</option>
                            <option value="year">📅 سال جاری</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <label>از تاریخ </label>
                        <input type="text" id="date_from" name="date_from" class="form-control" placeholder="انتخاب کنید">
                    </div>
                    <div class="search-group">
                        <label>تا تاریخ </label>
                        <input type="text" id="date_to" name="date_to" class="form-control" placeholder="انتخاب کنید" >
                    </div>
                    </div>

                <div class="search-row">
                    <div class="search-group search-actions">
                        <button type="button" id="search_btn" class="btn-search">🔍 جستجو</button>
                        <button type="button" id="reset_btn" class="btn-reset-search">🗑️ پاک کردن</button>
                    </div>
                </div>

            </div>
        </div>

        <!-- جدول فاکتورها -->
        <div class="invoices-table data-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>نام شرکت</th>
                    <th>شماره فاکتور</th>
                    <th>موضوع</th>
                    <th>مبلغ</th>
                    <th>توضیحات</th>
                    <th>تاریخ</th>
                    <th>ثبت کننده</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($invoices)): ?>
                    <tr><td colspan="10" style="text-align: center; padding: 40px;">🧾 هیچ فاکتوری ثبت نشده است</td></tr>
                <?php else: ?>
                    <?php $row_num = 1; foreach ($invoices as $invoice): ?>
                        <tr id="invoice_<?php echo $invoice['id']; ?>">
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo htmlspecialchars($invoice['company_name']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['subject'] ?? '-'); ?></td>
                            <td><?php echo fa_number(number_format($invoice['amount'], 0)) . ' ریال'; ?></td>
                            <td class="description-cell"><?php echo nl2br(htmlspecialchars($invoice['description'] ?? '-')); ?></td>
                            <td class="date"><?php echo fa_number(htmlspecialchars($invoice['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($invoice['creator_name'] ?? '-'); ?></td>
                            <td class="action-buttons">
                                <?php if (canEditInvoices()): ?>
                                    <button class="edit-btn" onclick='openEditModal(<?php echo json_encode($invoice); ?>)'>✏️ویرایش</button>
                                <?php endif; ?>
                                <?php if (canDeleteInvoices()): ?>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $invoice['id']; ?>, '<?php echo htmlspecialchars($invoice['company_name']); ?>')">🗑️حذف</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php $row_num++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- مودال ویرایش فاکتور -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش فاکتور</h3>
        <form method="post">
            <input type="hidden" name="invoice_id" id="edit_invoice_id">

            <div class="form-row">

                <div class="form-group">
                    <label>نام شرکت *</label>
                    <input type="text" name="company_name" id="edit_company_name" required>
                </div>

                <div class="form-group">
                    <label>شماره فاکتور *</label>
                    <input type="text" name="invoice_number" id="edit_invoice_number" required>
                </div>

                <div class="subject">
                    <label>موضوع فاکتور *</label>
                    <select name="subject" id="edit_subject" required>
                        <option value="">-- انتخاب کنید --</option>
                        <?php foreach ($topics as $topic): ?>
                            <option value="<?php echo htmlspecialchars($topic['name']); ?>">
                                <?php echo htmlspecialchars($topic['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>مبلغ فاکتور *</label>
                    <input type="text" name="amount" id="edit_amount" step="0.01" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>تاریخ فاکتور</label>
                    <input type="text" id="edit_date" name="created_at" class="form-control" placeholder="انتخاب کنید">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>توضیحات</label>
                    <textarea name="description" id="edit_description" rows="3"></textarea>
            </div>
            </div>
            <div class="modal-buttons">
                <button type="submit" name="edit_invoice" class="btn-add">💾 ذخیره</button>
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>