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
    $jalaliDate = jdate('Y-m-d');

    // تاریخ فاکتور
    $invoice_date = null;
    if (!empty($_POST['year']) && !empty($_POST['month']) && !empty($_POST['day'])) {
        $year = (int)$_POST['year'];
        $month = (int)$_POST['month'];
        $day = (int)$_POST['day'];
        $timestamp = jmktime(0, 0, 0, $month, $day, $year);
        $invoice_date = date('Y-m-d', $timestamp);
    }

    $insertStmt = $db->prepare("INSERT INTO invoices (company_name, invoice_number, subject, amount, invoice_date, description, created_at, created_by) 
VALUES (:company_name, :invoice_number, :subject, :amount, :invoice_date, :description, :created_at, :created_by)");

    if ($insertStmt->execute([
        ':company_name' => $company_name,
        ':invoice_number' => $invoice_number,
        ':subject' => $subject,
        ':amount' => $amount,
        ':invoice_date' => $invoice_date,
        ':description' => $description,
        ':created_at' => $jalaliDate,
        ':created_by' => $_SESSION['user_id']
    ])) {
        $_SESSION['success_message'] = "✅ فاکتور با موفقیت اضافه شد";
    } else {
        $_SESSION['error_message'] = "❌ خطا در افزودن فاکتور";
    }

    header('Location: admin_invoices.php');
    exit;
}

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
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $where[] = "invoice_date >= :date_from";
    $params[':date_from'] = $_GET['date_from'];
}
if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $where[] = "invoice_date <= :date_to";
    $params[':date_to'] = $_GET['date_to'];
}

$whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

$invoices = $db->prepare("
    SELECT i.*, u.fullname as creator_name 
    FROM invoices i
    LEFT JOIN users u ON i.created_by = u.id
    $whereClause
    ORDER BY i.id DESC
");
$invoices->execute($params);
$invoices = $invoices->fetchAll();

// اضافه کردن تاریخ شمسی برای نمایش
foreach ($invoices as $key => $invoice) {
    if (!empty($invoice['invoice_date']) && $invoice['invoice_date'] != '0000-00-00') {
        $parts = explode('-', $invoice['invoice_date']);
        if (count($parts) == 3) {
            list($jy, $jm, $jd) = gregorian_to_jalali($parts[0], $parts[1], $parts[2]);
            $invoices[$key]['invoice_date_jalali'] = sprintf("%04d-%02d-%02d", $jy, $jm, $jd);
            $invoices[$key]['invoice_date_year'] = $jy;
            $invoices[$key]['invoice_date_month'] = $jm;
            $invoices[$key]['invoice_date_day'] = $jd;
        }
    } else {
        $invoices[$key]['invoice_date_jalali'] = '-';
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت فاکتورها</title>
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
                        <div class="form-group">
                            <label>تاریخ فاکتور</label>
                            <div id="invoice_date_container"></div>
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
                            <option value="this_week">📅 هفته جاری</option>
                            <option value="this_month">📅 ماه جاری</option>
                            <option value="this_year">📅 سال جاری</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <label>از تاریخ </label>
                        <div id="search_date_from_container"></div>
                        <input type="hidden" id="search_date_from" value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
                    </div>
                    <div class="search-group">
                        <label>تا تاریخ </label>
                        <div id="search_date_to_container"></div>
                        <input type="hidden" id="search_date_to" value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
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
                    <th>تاریخ فاکتور</th>
                    <th>توضیحات</th>
                    <th>تاریخ ثبت</th>
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
                            <td class="date"><?php echo fa_number($invoice['invoice_date_jalali'] ?? $invoice['invoice_date'] ?? '-'); ?></td>
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
                    <div id="edit_date_container"></div>
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
<script src="assets/js/alljs.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/admin-invoices.js?v=<?php echo time(); ?>"></script>
</body>
</html>