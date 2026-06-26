<?php
session_start();
require_once 'config/config.php';
require_once 'db.php';
require_once 'assets/jdf.php';
require_once 'functions.php';

date_default_timezone_set('Asia/Tehran');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';

if (isset($_POST['add_department']) && canEditDepartments()) {
    $name = htmlspecialchars($_POST['name']);
    $stmt = $db->prepare("INSERT INTO departments (name,  created_at, status) VALUES (:name,  :created_at, 'active')");
    $stmt->execute([':name' => $name,  ':created_at' => now()]);
    $successMessage = "✅ بخش اضافه شد";
    header('Location: admin_departments.php');
    exit;
}

// ============================================
// پردازش AJAX - ویرایش بخش
// ============================================

if (isset($_POST['edit_departments'])) {
    $departments_id = filter_var($_POST['departments_id'], FILTER_VALIDATE_INT);
    $name = htmlspecialchars(trim($_POST['name']));

    if (empty($name)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'نام بخش الزامی است']);
        exit;
    }

    $updateStmt = $db->prepare("UPDATE departments SET name = :name WHERE id = :id");
    $success = $updateStmt->execute([':name' => $name, ':id' => $departments_id]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'id' => $departments_id,
        'name' => $name,
        'message' => $success ? 'بخش با موفقیت ویرایش شد' : 'خطا در ویرایش بخش'
    ]);
    exit;
}



// حذف بخش با AJAX
if (isset($_POST['delete_departments'])) {
    $departments_id = filter_var($_POST['departments_id'], FILTER_VALIDATE_INT);

    if ($departments_id) {
        $deleteStmt = $db->prepare("DELETE FROM departments WHERE id = :id");
        $success = $deleteStmt->execute([':id' => $departments_id]);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'id' => $departments_id,
            'message' => $success ? 'فعالیت با موفقیت حذف شد' : 'خطا در حذف فعالیت'
        ]);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'شناسه نامعتبر']);
    exit;
}



$departments = $db->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعریف بخش‌</title>
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
        <div class="main-title"><h1>🏥 تعریف بخش</h1></div>
        <?php if($successMessage): ?><div class="alert alert-success"><?php echo $successMessage; ?></div><?php endif; ?>

        <?php if(canEditDepartments()): ?>
            <div class="add-card"><h2>➕ افزودن بخش جدید</h2>
                <form method="post" class="form-row">
                    <div class="department-group">
                        <label>نام بخش</label>
                        <input type="text" name="name" required>
                        </div>

                        <button type="submit" name="add_department" class="btn-add">➕ ثبت</button>

                </form>
            </div>
        <?php endif; ?>

        <div class="departments-table data-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>نام بخش</th>
                    <th>تاریخ ثبت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($departments)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px;">
                            🏥 هیچ بخشی ثبت نشده است
                            </td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($departments as $department): ?>
                        <tr id="department_<?php echo $department['id']; ?>">
                            <td><?php echo fa_number($i); ?></td>
                            <td class="dept-name"><?php echo htmlspecialchars($department['name']); ?></td>
                            <td class="date-ltr"><?php echo fa_number(htmlspecialchars($department['created_at'])); ?></td>
                            <td class="action-buttons">
                                <?php if (canEditDepartments()): ?>
                                    <button class="edit-btn" onclick='openEditModal(<?php echo $department['id']; ?>)'>✏️ ویرایش</button>
                                <?php endif; ?>
                                <?php if (canDeleteDepartments()): ?>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $department['id']; ?>, '<?php echo htmlspecialchars($department['name']); ?>')">🗑️ حذف</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش بخش</h3>
        <form id="editForm">
            <input type="hidden" name="departments_id" id="edit_department_id">
            <input type="hidden" name="edit_departments" value="1">
            <div class="form-row">
                <div class="form-group">
                <label>نام بخش</label>
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

</body>
</html>