<?php
session_start();
require_once 'config/config.php';
require_once 'db.php';
require_once 'assets/jdf.php';
require_once 'functions.php';

date_default_timezone_set('Asia/Tehran');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isAdmin()) {
    header('Location: requests.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';


if (isset($_POST['add_activity'])&& canEditActivities()) {
    $name = htmlspecialchars($_POST['name']);

    if (empty($name)) {
        $errorMessage = "❌ نام فعالیت الزامی است";
    } else {
        $insertStmt = $db->prepare("INSERT INTO activities (name, created_at) VALUES (:name, :created_at)");
        $jalaliDate = now();
        if ($insertStmt->execute([':name' => $name, ':created_at' => $jalaliDate])) {
            $successMessage = "✅ فعالیت با موفقیت اضافه شد";
        } else {
            $errorMessage = "❌ خطا در افزودن فعالیت";
        }
    }
    header('Location: admin_activities.php');
    exit;
}

// ============================================
// پردازش AJAX - ویرایش فعالیت
// ============================================

if (isset($_POST['edit_activity'])&& canEditActivities()) {
    $activity_id = filter_var($_POST['activity_id'], FILTER_VALIDATE_INT);
    $name = htmlspecialchars(trim($_POST['name']));

    if (empty($name)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'نام فعالیت الزامی است']);
        exit;
    }

    $updateStmt = $db->prepare("UPDATE activities SET name = :name WHERE id = :id");
    $success = $updateStmt->execute([':name' => $name, ':id' => $activity_id]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'id' => $activity_id,
        'name' => $name,
        'message' => $success ? 'فعالیت با موفقیت ویرایش شد' : 'خطا در ویرایش فعالیت'
    ]);
    exit;
}

// حذف فعالیت با AJAX
if (isset($_POST['delete_activity'])&& canEditActivities()) {
    $activity_id = filter_var($_POST['activity_id'], FILTER_VALIDATE_INT);

    if ($activity_id) {
        $deleteStmt = $db->prepare("DELETE FROM activities WHERE id = :id");
        $success = $deleteStmt->execute([':id' => $activity_id]);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'id' => $activity_id,
            'message' => $success ? 'فعالیت با موفقیت حذف شد' : 'خطا در حذف فعالیت'
        ]);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'شناسه نامعتبر']);
    exit;
}


// گرفتن لیست فعالیت‌ها
$activities = $db->query("SELECT * FROM activities ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعریف فعالیت</title>
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
            <h1>📋 تعریف فعالیت</h1>
        </div>


        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- افزودن فعالیت جدید -->
        <div class="add-card">
            <h2>➕ افزودن فعالیت جدید</h2>

            <form method="post" class="form-row">
                <div class="activityname-group">
                    <label>نام فعالیت</label>
                    <input type="text" name="name" required>
                </div>
                <button type="submit" name="add_activity" class="btn-add">➕ ثبت</button>
            </form>

        </div>

        <!-- جدول فعالیت‌ها -->
        <div class="activities-table data-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>نام فعالیت</th>
                    <th>تاریخ ثبت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($activities)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px;">📋 هیچ فعالیتی ثبت نشده است</td>
                    </tr>
                <?php else: ?>

                    <?php $row_num = 1; foreach ($activities as $activity): ?>
                        <tr id="activity_<?php echo $activity['id']; ?>">
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo htmlspecialchars($activity['name']); ?></td>
                            <td class="date"><?php echo fa_number(htmlspecialchars($activity['created_at'])); ?></td>
                            <td class="action-buttons">

                                <?php if (canEditActivities()): ?>
                                <button class="edit-btn" onclick='openEditModal(<?php echo $activity['id']; ?>)'>✏️ ویرایش</button>
                                <?php endif; ?>

                                <?php if (canDeleteActivities()): ?>
                                <button class="delete-btn" onclick="confirmDelete(<?php echo $activity['id']; ?>, '<?php echo htmlspecialchars($activity['name']); ?>')">🗑️ حذف</button>
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

<!-- مودال ویرایش فعالیت -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش فعالیت</h3>
        <form id="editForm">
            <input type="hidden" name="activity_id" id="edit_activity_id">
            <input type="hidden" name="edit_activity_ajax" value="1">

            <div class="form-row">
                <div class="form-group">
                    <label>نام فعالیت</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn-add" onclick="saveEdit()">💾 ذخیره</button>
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
        </form>
    </div>
</div>
<script src="assets/js/alljs.js"></script>
<script src="assets/js/sweetalert2.min.js"></script>
<script src="assets/js/admin-activities.js"></script>
</body>
</html>