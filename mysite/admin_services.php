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

if (!isAdmin() || !canViewServices()) {
    header('Location: requests.php');
    exit;
}

$db = getDB();

// گرفتن لیست‌ها برای فرم
$departments = $db->query("SELECT id, name FROM departments WHERE status = 'active' ORDER BY name ASC")->fetchAll();
$brands = $db->query("SELECT id, name FROM brands ORDER BY name ASC")->fetchAll();
$persons = $db->query("SELECT id, name FROM persons ORDER BY name ASC")->fetchAll();
$activities = $db->query("SELECT id, name FROM activities ORDER BY name ASC")->fetchAll();

// ============================================
// پردازش فرم حذف
// ============================================

if (isset($_POST['delete_service']) && canDeleteServices()) {
    $service_id = filter_var($_POST['service_id'], FILTER_VALIDATE_INT);

    if ($service_id) {
        $deleteStmt = $db->prepare("DELETE FROM service_requests WHERE id = :id");
        $deleteStmt->execute([':id' => $service_id]);

        // اگر درخواست AJAX باشه
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $service_id]);
            exit;
        }
    }
    header('Location: admin_services.php');
    exit;
}

// ============================================
// پردازش فرم ویرایش
// ============================================

if (isset($_POST['edit_service']) && canEditServices()) {
    $service_id = filter_var($_POST['service_id'], FILTER_VALIDATE_INT);
    $service_name = htmlspecialchars(trim($_POST['service_name']));
    $department_id = !empty($_POST['department_id']) ? filter_var($_POST['department_id'], FILTER_VALIDATE_INT) : null;
    $brand_id = !empty($_POST['brand_id']) ? filter_var($_POST['brand_id'], FILTER_VALIDATE_INT) : null;
    $receiver_person_id = !empty($_POST['receiver_person_id']) ? filter_var($_POST['receiver_person_id'], FILTER_VALIDATE_INT) : null;
    $serial_number = htmlspecialchars(trim($_POST['serial_number']));
    $computer_code = htmlspecialchars(trim($_POST['computer_code']));
    $description = htmlspecialchars(trim($_POST['description']));

    // تاریخ سرویس
    $service_date = null;
    if (!empty($_POST['year']) && !empty($_POST['month']) && !empty($_POST['day'])) {
        $year = (int)$_POST['year'];
        $month = (int)$_POST['month'];
        $day = (int)$_POST['day'];
        $timestamp = jmktime(0, 0, 0, $month, $day, $year);
        $service_date = date('Y-m-d', $timestamp);
    }

    $updateStmt = $db->prepare("UPDATE service_requests SET service_name = :service_name, department_id = :department_id, brand_id = :brand_id, receiver_person_id = :receiver_person_id, serial_number = :serial_number, service_date = :service_date, computer_code = :computer_code, description = :description WHERE id = :id");
    $updateStmt->execute([
        ':service_name' => $service_name, ':department_id' => $department_id,
        ':brand_id' => $brand_id, ':receiver_person_id' => $receiver_person_id,
        ':serial_number' => $serial_number, ':service_date' => $service_date,
        ':computer_code' => $computer_code, ':description' => $description, 'id' => $service_id
    ]);

    $_SESSION['success_message'] = "✅ سرویس با موفقیت ویرایش شد";
    header('Location: admin_services.php');
    exit;
}

// ============================================
// پردازش فرم افزودن سرویس جدید
// ============================================

if (isset($_POST['add_service']) && canEditServices()) {
    $service_name = htmlspecialchars(trim($_POST['service_name']));
    $department_id = !empty($_POST['department_id']) ? filter_var($_POST['department_id'], FILTER_VALIDATE_INT) : null;
    $brand_id = !empty($_POST['brand_id']) ? filter_var($_POST['brand_id'], FILTER_VALIDATE_INT) : null;
    $receiver_person_id = !empty($_POST['receiver_person_id']) ? filter_var($_POST['receiver_person_id'], FILTER_VALIDATE_INT) : null;
    $serial_number = htmlspecialchars(trim($_POST['serial_number']));
    $computer_code = htmlspecialchars(trim($_POST['computer_code']));
    $description = htmlspecialchars(trim($_POST['description']));
    $jalaliDate = jdate('Y-m-d');


    $service_date = null;
    if (!empty($_POST['year']) && !empty($_POST['month']) && !empty($_POST['day'])) {
        $year = (int)$_POST['year'];
        $month = (int)$_POST['month'];
        $day = (int)$_POST['day'];
        $timestamp = jmktime(0, 0, 0, $month, $day, $year);
        $service_date = date('Y-m-d', $timestamp);
    }

    $insertStmt = $db->prepare("INSERT INTO service_requests 
    (service_name, department_id, brand_id, receiver_person_id, serial_number, service_date, computer_code, description, created_at, created_by) 
VALUES (:service_name, :department_id, :brand_id, :receiver_person_id, :serial_number, :service_date, :computer_code, :description, :created_at, :created_by)");

    if ($insertStmt->execute([
        ':service_name' => $service_name, ':department_id' => $department_id,
        ':brand_id' => $brand_id, ':receiver_person_id' => $receiver_person_id,
        ':serial_number' => $serial_number, ':service_date' => $service_date,
        ':computer_code' => $computer_code, ':description' => $description,
        ':created_at' => $jalaliDate, ':created_by' => $_SESSION['user_id']
    ])) {
        // دریافت سرویس جدید اضافه شده
        $newId = $db->lastInsertId();
        $stmt = $db->prepare("
            SELECT s.*, 
                   d.name as department_name, 
                   b.name as brand_name, 
                   p.name as receiver_name
            FROM service_requests s
            LEFT JOIN departments d ON s.department_id = d.id
            LEFT JOIN brands b ON s.brand_id = b.id
            LEFT JOIN persons p ON s.receiver_person_id = p.id
            WHERE s.id = :id
        ");
        $stmt->execute([':id' => $newId]);
        $newService = $stmt->fetch(PDO::FETCH_ASSOC);

        // اضافه کردن تاریخ شمسی
        if (!empty($newService['service_date']) && $newService['service_date'] != '0000-00-00') {
            $parts = explode('-', $newService['service_date']);
            if (count($parts) == 3) {
                list($jy, $jm, $jd) = gregorian_to_jalali($parts[0], $parts[1], $parts[2]);
                $newService['service_date_jalali'] = sprintf("%04d-%02d-%02d", $jy, $jm, $jd);
                $newService['service_date_year'] = $jy;
                $newService['service_date_month'] = $jm;
                $newService['service_date_day'] = $jd;
            }
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'service' => $newService]);
            exit;
        }
    }

    header('Location: admin_services.php');
    exit;
}

// ============================================
// گرفتن لیست سرویس‌ها
// ============================================

$services = $db->query("
    SELECT s.*, 
           d.name as department_name, 
           b.name as brand_name, 
           p.name as receiver_name,
           u.username as creator_name
    FROM service_requests s
    LEFT JOIN departments d ON s.department_id = d.id
    LEFT JOIN brands b ON s.brand_id = b.id
    LEFT JOIN persons p ON s.receiver_person_id = p.id
    LEFT JOIN users u ON s.created_by = u.id
    ORDER BY s.id DESC
")->fetchAll();


// اضافه کردن تاریخ شمسی برای نمایش
foreach ($services as $key => $service) {
    if (!empty($service['service_date']) && $service['service_date'] != '0000-00-00') {
        $parts = explode('-', $service['service_date']);
        if (count($parts) == 3) {
            list($jy, $jm, $jd) = gregorian_to_jalali($parts[0], $parts[1], $parts[2]);
            $services[$key]['service_date_jalali'] = sprintf("%04d-%02d-%02d", $jy, $jm, $jd);
            $services[$key]['service_date_year'] = $jy;
            $services[$key]['service_date_month'] = $jm;
            $services[$key]['service_date_day'] = $jd;
        }
    } else {
        $services[$key]['service_date_jalali'] = '-';
        $services[$key]['service_date_year'] = '';
        $services[$key]['service_date_month'] = '';
        $services[$key]['service_date_day'] = '';
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ثبت فعالیت</title>
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
            <h1>🔧 ثبت فعالیت</h1>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- فرم ثبت سرویس -->
        <?php if (canEditServices()): ?>
            <div class="add-card">
                <h2>➕ ثبت فعالیت جدید</h2>
                <form method="post" class="services-form" >
                    <div class="form-row">
                        <div class="form-group">
                            <label>فعالیت *</label>
                            <select name="service_name" required>
                                <option value="">-- انتخاب کنید --</option>
                                <?php foreach ($activities as $activity): ?>
                                    <option value="<?php echo htmlspecialchars($activity['name']); ?>">
                                        <?php echo htmlspecialchars($activity['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>بخش</label>
                            <select name="department_id">
                                <option value="">-- انتخاب --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>برند</label>
                            <select name="brand_id">
                                <option value="">-- انتخاب --</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>تحویل گیرنده</label>
                            <select name="receiver_person_id">
                                <option value="">-- انتخاب --</option>
                                <?php foreach ($persons as $person): ?>
                                    <option value="<?php echo $person['id']; ?>"><?php echo htmlspecialchars($person['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>سریال</label>
                            <input type="text" name="serial_number">
                        </div>

                        <div class="form-group">
                            <label>کد رایانه</label>
                            <input type="text" name="computer_code">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>تاریخ </label>
                            <div id="service_date_container"></div>
                        </div>
                    </div>
                    <div class="form-row">
                    <div class="form-group">
                        <label>توضیحات</label>
                        <textarea name="description" rows="3"></textarea>
                    </div>
                    </div>
                    <button type="submit" name="add_service" class="btn-add">💾 ذخیره فعالیت</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- بخش جستجو -->
        <div class="search-card">
            <h2>🔍 جستجوی فعالیت</h2>
            <div class="search-form">
                <div class="search-row">
                    <div class="service-name-group">
                        <label>فعالیت</label>
                        <select id="search_name">
                            <option value="">-- همه --</option>
                            <?php foreach ($activities as $activity): ?>
                                <option value="<?php echo htmlspecialchars($activity['name']); ?>" <?php echo (isset($_GET['name']) && $_GET['name'] == $activity['name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($activity['name']); ?>
                                </option>
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
                    <div class="receiver-group">
                        <label>تحویل گیرنده</label>
                        <select name="receiver_person_id" id="edit_receiver_person_id">
                            <option value="">-- انتخاب --</option>
                            <?php foreach ($persons as $person): ?>
                                <option value="<?php echo $person['id']; ?>"><?php echo htmlspecialchars($person['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="computer-group">
                        <label>کد رایانه</label>
                        <input type="text" name="computer_code">
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

        <!-- جدول خدمات -->
        <div class="services-table data-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>فعالیت</th>
                    <th>بخش</th>
                    <th>برند</th>
                    <th>تحویل گیرنده</th>
                    <th>سریال</th>
                    <th>تاریخ فعالیت</th>
                    <th>کد رایانه</th>
                    <th>توضیحات</th>
                    <th>تاریخ ثبت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($services)): ?>
                    <tr><td colspan="12">🔧 هیچ سرویسی ثبت نشده است</td></tr>
                <?php else: ?>
                    <?php $row_num = 1; foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                            <td><?php echo htmlspecialchars($service['department_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($service['brand_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($service['receiver_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($service['serial_number'] ?? '-'); ?></td>
                            <td class="date">
                                <?php
                                $date = $service['service_date_jalali'] ?? $service['service_date'] ?? '-';
                                echo fa_number($date);
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($service['computer_code'] ?? '-'); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($service['description'] ?? '-')); ?></td>

                            <td><?php echo fa_number(htmlspecialchars($service['created_at'])); ?></td>
                            <td>
                                <?php if (canEditServices()): ?>
                                    <button class="edit-btn" onclick='openEditModal(<?php echo json_encode($service); ?>)'>✏️ویرایش</button>
                                <?php endif; ?>

                                <?php if (canDeleteServices()): ?>
                                    <button
                                            class="delete-btn"
                                            data-id="<?php echo $service['id']; ?>"
                                            onclick="confirmDelete(<?php echo $service['id']; ?>,
                                                    '<?php echo htmlspecialchars($service['service_name']); ?>')">
                                        🗑️حذف
                                    </button>
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

<!-- مودال ویرایش سرویس -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش فعالیت</h3>
        <form method="post">
            <input type="hidden" name="service_id" id="edit_service_id">
            <div class="form-row">
            <div class="form-group">
                <label>فعالیت *</label>
                <select name="service_name" id="edit_service_name" required>
                    <option value="">-- انتخاب کنید --</option>
                    <?php foreach ($activities as $activity): ?>
                        <option value="<?php echo htmlspecialchars($activity['name']); ?>">
                            <?php echo htmlspecialchars($activity['name']); ?></option>
                    <?php endforeach; ?>
                </select>
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
                <div class="form-group">
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
                <div class="receiver-group">
                    <label>تحویل گیرنده</label>
                    <select name="receiver_person_id" id="edit_receiver_person_id">
                        <option value="">-- انتخاب --</option>
                        <?php foreach ($persons as $person): ?>
                            <option value="<?php echo $person['id']; ?>"><?php echo htmlspecialchars($person['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="serial-group">
                    <label>سریال</label>
                    <input type="text" name="serial_number" id="edit_serial_number">
                </div>
                <div class="computer-group">
                    <label>کد رایانه</label>
                    <input type="text" name="computer_code" id="edit_computer_code">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group-group">
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
                <button type="submit" name="edit_service" class="btn-add">💾 ذخیره</button>
                <button type="button" onclick="closeModal('editModal')" class="btn-cancel">لغو</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/alljs.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/admin-services.js?v=<?php echo time(); ?>"></script>

</body>
</html>