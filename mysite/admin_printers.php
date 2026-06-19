<?php
session_start();
require_once 'db.php';
require_once 'assets/jdf.php';
require_once 'functions.php';

date_default_timezone_set('Asia/Tehran');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isAdmin() || !canViewPrinters()) {
    header('Location: admin.php');
    exit;
}

$db = getDB();
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

$activities = $db->query("
    SELECT id,name
    FROM activities
    ORDER BY name
")->fetchAll();

$departments = $db->query("
    SELECT id, name
    FROM departments
    ORDER BY name
")->fetchAll();

$brands = $db->query("
    SELECT id, name
    FROM brands
    ORDER BY name
")->fetchAll();

// ============================================
// ثبت پرینتر جدید
// ============================================
if (isset($_POST['add_printer']) && canEditPrinters()) {

    $computer_code = htmlspecialchars(trim($_POST['computer_code']));
    $property_code = htmlspecialchars(trim($_POST['property_code']));

    $activity_id = !empty($_POST['activity_id'])
        ? filter_var($_POST['activity_id'], FILTER_VALIDATE_INT)
        : null;

    $department_id = !empty($_POST['department_id'])
        ? filter_var($_POST['department_id'], FILTER_VALIDATE_INT)
        : null;

    $brand_id = !empty($_POST['brand_id'])
        ? filter_var($_POST['brand_id'], FILTER_VALIDATE_INT)
        : null;

    $serial_number = htmlspecialchars(trim($_POST['serial_number']));
    $description = htmlspecialchars(trim($_POST['description']));

    $created_at = jdate('Y-m-d');

    $stmt = $db->prepare("
        INSERT INTO printers (
            computer_code,
            property_code,
            activity_id,
            department_id,
            brand_id,
            serial_number,
            description,
            created_at,
            created_by
        )
        VALUES (
            :computer_code,
            :property_code,
            :activity_id,
            :department_id,
            :brand_id,
            :serial_number,
            :description,
            :created_at,
            :created_by
        )
    ");

    if ($stmt->execute([
        ':computer_code' => $computer_code,
        ':property_code' => $property_code,
        ':activity_id' => $activity_id,
        ':department_id' => $department_id,
        ':brand_id' => $brand_id,
        ':serial_number' => $serial_number,
        ':description' => $description,
        ':created_at' => $created_at,
        ':created_by' => $_SESSION['user_id']
    ])) {

        $_SESSION['success_message'] = '✅ پرینتر با موفقیت ثبت شد';

    } else {

        $_SESSION['error_message'] = '❌ خطا در ثبت پرینتر';

    }

    header('Location: admin_printers.php');
    exit;
}
// ============================================
// ویرایش پرینتر
// ============================================
if (isset($_POST['edit_printer']) && canEditPrinters()) {

    $printer_id = filter_var($_POST['printer_id'], FILTER_VALIDATE_INT);

    $computer_code = htmlspecialchars(trim($_POST['computer_code']));
    $property_code = htmlspecialchars(trim($_POST['property_code']));

    $activity_id = !empty($_POST['activity_id'])
        ? filter_var($_POST['activity_id'], FILTER_VALIDATE_INT)
        : null;

    $department_id = !empty($_POST['department_id'])
        ? filter_var($_POST['department_id'], FILTER_VALIDATE_INT)
        : null;

    $brand_id = !empty($_POST['brand_id'])
        ? filter_var($_POST['brand_id'], FILTER_VALIDATE_INT)
        : null;

    $serial_number = htmlspecialchars(trim($_POST['serial_number']));
    $description = htmlspecialchars(trim($_POST['description']));

    $updateStmt = $db->prepare("
        UPDATE printers
        SET
            computer_code = :computer_code,
            property_code = :property_code,
            activity_id = :activity_id,
            department_id = :department_id,
            brand_id = :brand_id,
            serial_number = :serial_number,
            description = :description
        WHERE id = :id
    ");

    $updateStmt->execute([
        ':computer_code' => $computer_code,
        ':property_code' => $property_code,
        ':activity_id' => $activity_id,
        ':department_id' => $department_id,
        ':brand_id' => $brand_id,
        ':serial_number' => $serial_number,
        ':description' => $description,
        ':id' => $printer_id
    ]);

    $_SESSION['success_message'] =
        '✅ اطلاعات پرینتر با موفقیت ویرایش شد';

    header('Location: admin_printers.php');
    exit;
}
// ============================================
// حذف پرینتر
// ============================================
if (isset($_POST['delete_printer']) && canDeletePrinters()) {

    $printer_id = filter_var($_POST['printer_id'], FILTER_VALIDATE_INT);

    if ($printer_id) {

        $deleteStmt = $db->prepare("
            DELETE FROM printers
            WHERE id = :id
        ");

        $deleteStmt->execute([
            ':id' => $printer_id
        ]);

        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            ob_clean();

            header('Content-Type: application/json');

            echo json_encode([
                'success' => true,
                'id' => $printer_id
            ]);

            exit;
        }
    }

    header('Location: admin_printers.php');
    exit;
}
// ============================================
// گرفتن لیست پرینترها با فیلتر
// ============================================

$where = [];
$params = [];

if (isset($_GET['computer_code']) && !empty($_GET['computer_code'])) {
    $where[] = "p.computer_code LIKE :computer_code";
    $params[':computer_code'] = '%' . $_GET['computer_code'] . '%';
}
if (isset($_GET['property_code']) && !empty($_GET['property_code'])) {
    $where[] = "p.property_code LIKE :property_code";
    $params[':property_code'] = '%' . $_GET['property_code'] . '%';
}
if (isset($_GET['activity']) && !empty($_GET['activity'])) {
    $where[] = "p.activity_id = :activity";
    $params[':activity'] = filter_var($_GET['activity'], FILTER_VALIDATE_INT);
}
if (isset($_GET['department']) && !empty($_GET['department'])) {
    $where[] = "p.department_id = :department";
    $params[':department'] = filter_var($_GET['department'], FILTER_VALIDATE_INT);
}
if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $where[] = "p.brand_id = :brand";
    $params[':brand'] = filter_var($_GET['brand'], FILTER_VALIDATE_INT);
}
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $where[] = "p.created_at >= :date_from";
    $params[':date_from'] = $_GET['date_from'];
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $where[] = "p.created_at <= :date_to";
    $params[':date_to'] = $_GET['date_to'];
}

$whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

$printers = $db->prepare("
    SELECT
        p.*,
        a.name as activity_name,
        d.name AS department_name,
        b.name AS brand_name,
        u.fullname AS creator_name
    FROM printers p
    LEFT JOIN activities a ON p.activity_id = a.id
    LEFT JOIN departments d ON p.department_id = d.id
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN users u ON p.created_by = u.id
    $whereClause
    ORDER BY p.id DESC
");
$printers->execute($params);
$printers = $printers->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت پرینترها</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/admin-printers.css">
    <link rel="stylesheet" href="styles/sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <span class="clock-display" id="liveClock">📅 <?php echo fa_number(now()); ?></span>
                <a href="logout.php" class="logout-btn-sidebar">🚪 خروج</a>
            </div>
        </div>

        <div class="main-title">
            <h1>🖨️ مدیریت پرینترها</h1>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- فرم ثبت پرینتر -->
        <?php if (canEditPrinters()): ?>
            <div class="add-card">
                <h2>➕ ثبت پرینتر جدید</h2>
                <form method="post" class="printers-form">

                    <div class="form-row">
                        <div class="pc-id">
                            <label>کد رایانه</label>
                            <input type="text" name="computer_code">
                        </div>
                        <div class="property-id">
                            <label>کد اموال</label>
                            <input type="text" name="property_code">
                        </div>
                        <div class="services-group">
                            <label>نام فعالیت *</label>
                            <select name="activity_id" required>
                                <option value="">-- انتخاب کنید --</option>
                                <?php foreach ($activities as $activity): ?>
                                    <option value="<?php echo $activity['id']; ?>"><?php echo htmlspecialchars($activity['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="department-group">
                            <label>بخش</label>
                            <select name="department_id">
                                <option value="">-- انتخاب --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="brand-group">
                            <label>برند</label>
                            <select name="brand_id">
                                <option value="">-- انتخاب --</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="serial-group">
                            <label>سریال</label>
                            <input type="text" name="serial_number">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>تاریخ </label>
                            <div id="printer_date_container"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>توضیحات</label>
                        <textarea name="description" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="add_printer" class="btn-add">💾 ذخیره پرینتر</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- بخش جستجو -->
        <div class="search-card">
            <h2>🔍 جستجوی پرینترها</h2>
            <div class="search-form">
                <div class="search-row">
                    <div class="pc-id">
                        <label>کد رایانه</label>
                        <input type="text" id="search_computer_code" placeholder="جستجو بر اساس کد رایانه..." value="<?php echo htmlspecialchars($_GET['computer_code'] ?? ''); ?>">
                    </div>
                    <div class="property-id">
                        <label>کد اموال</label>
                        <input type="text" id="search_property_code" placeholder="جستجو بر اساس کد اموال..." value="<?php echo htmlspecialchars($_GET['property_code'] ?? ''); ?>">
                    </div>
                    <div class="services-group">
                        <label>نام فعالیت</label>
                        <select id="search_activity">
                            <option value="">همه فعالیت‌ها</option>
                            <?php foreach ($activities as $activity): ?>
                                <option value="<?php echo $activity['id']; ?>" <?php echo (isset($_GET['activity']) && $_GET['activity'] == $activity['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($activity['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="department-group">
                        <label>بخش</label>
                        <select id="search_department">
                            <option value="">همه بخش‌ها</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo (isset($_GET['department']) && $_GET['department'] == $dept['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="brand-group">
                        <label>برند</label>
                        <select id="search_brand">
                            <option value="">همه برندها</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>" <?php echo (isset($_GET['brand']) && $_GET['brand'] == $brand['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($brand['name']); ?></option>
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

                <div class="search-group search-actions">
                    <button type="button" id="search_btn" class="btn-search">🔍 جستجو</button>
                    <button type="button" id="reset_btn" class="btn-reset-search">🗑️ پاک کردن</button>
                </div>
            </div>
        </div>

        <!-- جدول پرینترها -->
        <div class="printers-table data-table">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>کد رایانه</th>
                    <th>کد اموال</th>
                    <th>نام فعالیت</th>
                    <th>بخش</th>
                    <th>برند</th>
                    <th>سریال</th>
                    <th>توضیحات</th>
                    <th>تاریخ ثبت</th>
                    <th>ثبت کننده</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($printers)): ?>
                    <tr><td colspan="11" style="text-align: center; padding: 40px;">🖨️ هیچ پرینتری ثبت نشده است</td></tr>
                <?php else: ?>
                    <?php $row_num = 1; foreach ($printers as $printer): ?>
                        <tr>
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo htmlspecialchars($printer['computer_code'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($printer['property_code'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($printer['activity_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($printer['department_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($printer['brand_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($printer['serial_number'] ?? '-'); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($printer['description'] ?? '-')); ?></td>
                            <td class="date"><?php echo fa_number(htmlspecialchars($printer['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($printer['creator_name'] ?? '-'); ?></td>
                            <td class="action-buttons">
                                <?php if (canEditPrinters()): ?>
                                    <button class="edit-btn" onclick='openEditModal(<?php echo json_encode($printer); ?>)'>✏️</button>
                                <?php endif; ?>
                                <?php if (canDeletePrinters()): ?>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $printer['id']; ?>, '<?php echo htmlspecialchars($printer['computer_code'] ?? 'پرینتر'); ?>')">🗑️</button>
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

<!-- مودال ویرایش پرینتر -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش پرینتر</h3>
        <form method="post">
            <input type="hidden" name="printer_id" id="edit_printer_id">

            <div class="form-row">
                <div class="pc-id">
                    <label>کد رایانه</label>
                    <input type="text" name="computer_code" id="edit_computer_code">
                </div>
                <div class="property-id">
                    <label>کد اموال</label>
                    <input type="text" name="property_code" id="edit_property_code">
                </div>
                <div class="services-group">
                    <label>نام فعالیت *</label>
                    <select name="activity_id" id="edit_activity_id" required>
                        <option value="">-- انتخاب کنید --</option>
                        <?php foreach ($activities as $activity): ?>
                            <option value="<?php echo $activity['id']; ?>"><?php echo htmlspecialchars($activity['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="department-group">
                    <label>بخش</label>
                    <select name="department_id" id="edit_department_id">
                        <option value="">-- انتخاب --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="brand-group">
                    <label>برند</label>
                    <select name="brand_id" id="edit_brand_id">
                        <option value="">-- انتخاب --</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="serial-group">
                    <label>سریال</label>
                    <input type="text" name="serial_number" id="edit_serial_number">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>تاریخ </label>
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
                <button type="submit" name="edit_printer" class="btn-add">💾 ذخیره</button>
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/alljs.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/admin-printers.js?v=<?php echo time(); ?>"></script>

</body>
</html>


