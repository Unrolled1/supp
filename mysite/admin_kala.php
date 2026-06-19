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

if (!isAdmin() || !canViewProducts()) {
    header('Location: admin.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';

// گرفتن لیست برندها
$brands = $db->query("SELECT id, name FROM brands ORDER BY name ASC")->fetchAll();

// گرفتن لیست بخش‌ها
$departments = $db->query("SELECT id, name FROM departments WHERE status = 'active' ORDER BY name ASC")->fetchAll();

// گرفتن لیست اشخاص (تحویل گیرنده)
$persons = $db->query("SELECT id, name FROM persons ORDER BY name ASC")->fetchAll();

// ============================================
// پردازش فرم حذف
// ============================================

if (isset($_POST['delete_kala']) && canDeleteProducts()) {
    $kala_id = filter_var($_POST['kala_id'], FILTER_VALIDATE_INT);

    if ($kala_id) {
        $deleteStmt = $db->prepare("DELETE FROM kala WHERE id = :id");
        $deleteStmt->execute([':id' => $kala_id]);

        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'id' => $kala_id
            ]);
            exit;
        }
    }

    header('Location: admin_kala.php');
    exit;
}

// ============================================
// پردازش فرم ویرایش
// ============================================

if (isset($_POST['edit_kala']) && canEditProducts()) {
    $kala_id = filter_var($_POST['kala_id'], FILTER_VALIDATE_INT);
    $computer_code = htmlspecialchars(trim($_POST['computer_code']));
    $property_code = htmlspecialchars(trim($_POST['property_code']));
    $name = htmlspecialchars(trim($_POST['name']));
    $department_id = !empty($_POST['department_id']) ? filter_var($_POST['department_id'], FILTER_VALIDATE_INT) : null;
    $receiver_person_id = !empty($_POST['receiver_person_id']) ? filter_var($_POST['receiver_person_id'], FILTER_VALIDATE_INT) : null;
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
    $brand_id = !empty($_POST['brand_id']) ? filter_var($_POST['brand_id'], FILTER_VALIDATE_INT) : null;
    $serial_number = htmlspecialchars(trim($_POST['serial_number']));

    $updateStmt = $db->prepare("UPDATE kala SET computer_code = :computer_code, property_code = :property_code, name = :name, department_id = :department_id, receiver_person_id = :receiver_person_id, quantity = :quantity, brand_id = :brand_id, serial_number = :serial_number WHERE id = :id");

    if ($updateStmt->execute([
        ':computer_code' => $computer_code,
        ':property_code' => $property_code,
        ':name' => $name,
        ':department_id' => $department_id,
        ':receiver_person_id' => $receiver_person_id,
        ':quantity' => $quantity,
        ':brand_id' => $brand_id,
        ':serial_number' => $serial_number,
        ':id' => $kala_id
    ])) {
        $_SESSION['success_message'] = "✅ کالا با موفقیت ویرایش شد";
    } else {
        $_SESSION['error_message'] = "❌ خطا در ویرایش کالا";
    }
    header('Location: admin_kala.php');
    exit;
}

// ============================================
// پردازش فرم افزودن کالا
// ============================================

if (isset($_POST['add_kala']) && canEditProducts()) {
    $computer_code = htmlspecialchars(trim($_POST['computer_code']));
    $property_code = htmlspecialchars(trim($_POST['property_code']));
    $name = htmlspecialchars(trim($_POST['name']));
    $department_id = !empty($_POST['department_id']) ? filter_var($_POST['department_id'], FILTER_VALIDATE_INT) : null;
    $receiver_person_id = !empty($_POST['receiver_person_id']) ? filter_var($_POST['receiver_person_id'], FILTER_VALIDATE_INT) : null;
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
    $brand_id = !empty($_POST['brand_id']) ? filter_var($_POST['brand_id'], FILTER_VALIDATE_INT) : null;
    $serial_number = htmlspecialchars(trim($_POST['serial_number']));
    $jalaliDate = jdate('Y-m-d');

    $insertStmt = $db->prepare("INSERT INTO kala (computer_code, property_code, name, department_id, receiver_person_id, quantity, brand_id, serial_number, created_at, created_by) 
VALUES (:computer_code, :property_code, :name, :department_id, :receiver_person_id, :quantity, :brand_id, :serial_number, :created_at, :created_by)");

    if ($insertStmt->execute([
        ':computer_code' => $computer_code,
        ':property_code' => $property_code,
        ':name' => $name,
        ':department_id' => $department_id,
        ':receiver_person_id' => $receiver_person_id,
        ':quantity' => $quantity,
        ':brand_id' => $brand_id,
        ':serial_number' => $serial_number,
        ':created_at' => $jalaliDate,
        ':created_by' => $_SESSION['user_id']
    ])) {
        $_SESSION['success_message'] = "✅ کالا با موفقیت اضافه شد";
    } else {
        $_SESSION['error_message'] = "❌ خطا در افزودن کالا";
    }
    header('Location: admin_kala.php');
    exit;
}

// ============================================
// گرفتن لیست کالاها با فیلتر
// ============================================

$where = [];
$params = [];

if (isset($_GET['name']) && !empty($_GET['name'])) {
    $where[] = "name LIKE :name";
    $params[':name'] = '%' . $_GET['name'] . '%';
}
if (isset($_GET['computer_code']) && !empty($_GET['computer_code'])) {
    $where[] = "computer_code LIKE :computer_code";
    $params[':computer_code'] = '%' . $_GET['computer_code'] . '%';
}
if (isset($_GET['property_code']) && !empty($_GET['property_code'])) {
    $where[] = "property_code LIKE :property_code";
    $params[':property_code'] = '%' . $_GET['property_code'] . '%';
}
if (isset($_GET['department']) && !empty($_GET['department'])) {
    $where[] = "department_id = :department";
    $params[':department'] = filter_var($_GET['department'], FILTER_VALIDATE_INT);
}
if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $where[] = "brand_id = :brand";
    $params[':brand'] = filter_var($_GET['brand'], FILTER_VALIDATE_INT);
}
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $where[] = "k.created_at >= :date_from";
    $params[':date_from'] = $_GET['date_from'];
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $where[] = "k.created_at <= :date_to";
    $params[':date_to'] = $_GET['date_to'];
}

$whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

$kalas = $db->prepare("
    SELECT k.*, 
           d.name as department_name, 
           b.name as brand_name, 
           p.name as receiver_name,
           u.fullname as creator_name
    FROM kala k
    LEFT JOIN departments d ON k.department_id = d.id
    LEFT JOIN brands b ON k.brand_id = b.id
    LEFT JOIN persons p ON k.receiver_person_id = p.id
    LEFT JOIN users u ON k.created_by = u.id
    $whereClause
    ORDER BY k.id DESC
");
$kalas->execute($params);
$kalas = $kalas->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت کالاها</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/admin-kala.css">
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
            <h1>📦 مدیریت کالاها</h1>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- فرم ثبت کالا -->
        <?php if (canEditProducts()): ?>
            <div class="add-card">
                <h2>➕ ثبت کالا جدید</h2>
                <form method="post" class="kala-form">

                    <div class="form-row">
                        <div class="kala-name">
                            <label>نام کالا *</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="pc-id">
                            <label>کد رایانه</label>
                            <input type="text" name="computer_code">
                        </div>

                        <div class="property-id">
                            <label>کد اموال</label>
                            <input type="text" name="property_code">
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

                        <div class="amount-group">
                            <label>تعداد</label>
                            <input type="number" name="quantity" value="1" min="1">
                        </div>

                        <div class="serial-group">
                            <label>سریال</label>
                            <input type="text" name="serial_number">
                        </div>

                        <div class="receiver-group">
                            <label>تحویل گیرنده</label>
                            <select name="receiver_person_id">
                                <option value="">-- انتخاب --</option>
                                <?php foreach ($persons as $person): ?>
                                    <option value="<?php echo $person['id']; ?>"><?php echo htmlspecialchars($person['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                    <div class="date-select-group">
                      <label>تاریخ</label>
                        <div id="kala_date_container"></div>
                    </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>توضیحات</label>
                            <textarea name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="add_kala" class="btn-add">💾 ذخیره کالا</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- بخش جستجو -->
        <div class="search-card">
            <h2>🔍 جستجوی کالاها</h2>
            <div class="search-form">
                <div class="search-row">
                    <div class="kala-name">
                        <label>نام کالا</label>
                        <input type="text" id="search_name" placeholder="جستجو..." value="<?php echo htmlspecialchars($_GET['name'] ?? ''); ?>">
                    </div>
                    <div class="pc-id">
                        <label>کد رایانه</label>
                        <input type="text" id="search_computer_code" placeholder="کد رایانه..." value="<?php echo htmlspecialchars($_GET['computer_code'] ?? ''); ?>">
                    </div>
                    <div class="property-id">
                        <label>کد اموال</label>
                        <input type="text" id="search_property_code" placeholder="کد اموال..." value="<?php echo htmlspecialchars($_GET['property_code'] ?? ''); ?>">
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

        <!-- جدول کالاها -->
        <div class="kala-table data-table">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>نام کالا</th>
                    <th>کد رایانه</th>
                    <th>کد اموال</th>
                    <th>بخش</th>
                    <th>برند</th>
                    <th>تعداد</th>
                    <th>سریال</th>
                    <th>تحویل گیرنده</th>
                    <th>تاریخ ثبت</th>
                    <th>ثبت کننده</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($kalas)): ?>
                    <tr><td colspan="12" style="text-align: center; padding: 40px;">📦 هیچ کالایی ثبت نشده است</td></tr>
                <?php else: ?>
                    <?php $row_num = 1; foreach ($kalas as $kala): ?>
                        <tr>
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo htmlspecialchars($kala['name']); ?></td>
                            <td><?php echo htmlspecialchars($kala['computer_code'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($kala['property_code'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($kala['department_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($kala['brand_name'] ?? '-'); ?></td>
                            <td><?php echo fa_number($kala['quantity'] ?? '1'); ?></td>
                            <td><?php echo htmlspecialchars($kala['serial_number'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($kala['receiver_name'] ?? '-'); ?></td>
                            <td class="date"><?php echo fa_number(htmlspecialchars($kala['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($kala['creator_name'] ?? '-'); ?></td>
                            <td class="action-buttons">
                                <?php if (canEditProducts()): ?>
                                    <button class="edit-btn" onclick='openEditModal(<?php echo json_encode($kala); ?>)'>✏️</button>
                                <?php endif; ?>
                                <?php if (canDeleteProducts()): ?>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $kala['id']; ?>, '<?php echo htmlspecialchars($kala['name']); ?>')">🗑️</button>
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

<!-- مودال ویرایش کالا -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش کالا</h3>
        <form method="post">
            <input type="hidden" name="kala_id" id="edit_kala_id">

            <div class="form-row">
                <div class="kala-name">
                    <label>نام کالا *</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="pc-id">
                    <label>کد رایانه</label>
                    <input type="text" name="computer_code" id="edit_computer_code">
                </div>
                <div class="property-id">
                    <label>کد اموال</label>
                    <input type="text" name="property_code" id="edit_property_code">
                </div>

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
            </div>

            <div class="form-row">

                <div class="amount-group">
                    <label>تعداد</label>
                    <input type="number" name="quantity" id="edit_quantity" min="1">
                </div>

                <div class="serial-group">
                    <label>سریال</label>
                    <input type="text" name="serial_number" id="edit_serial_number">
                </div>
                <div class="receiver-group">
                    <label>تحویل گیرنده</label>
                    <select name="receiver_person_id" id="edit_receiver_person_id">
                        <option value="">-- انتخاب --</option>
                        <?php foreach ($persons as $person): ?>
                            <option value="<?php echo $person['id']; ?>"><?php echo htmlspecialchars($person['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>تاریخ</label>
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
                <button type="submit" name="edit_kala" class="btn-add">💾 ذخیره</button>
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/alljs.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/admin-kala.js?v=<?php echo time(); ?>"></script>

</body>
</html>