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

if (!isAdmin() || !canViewPersons()) {
    header('Location: requests.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';

// افزودن شخص جدید
if (isset($_POST['add_person']) && canEditPersons()) {
    $name = htmlspecialchars($_POST['name']);

    if (empty($name)) {
        $errorMessage = "❌ نام شخص الزامی است";
    } else {
        $insertStmt = $db->prepare("INSERT INTO persons (name, created_at) VALUES (:name, :created_at)");
        $jalaliDate = now();
        if ($insertStmt->execute([':name' => $name, ':created_at' => $jalaliDate])) {
            $successMessage = "✅ شخص با موفقیت اضافه شد";
        } else {
            $errorMessage = "❌ خطا در افزودن شخص";
        }
    }
    header('Location: admin_persons.php');
    exit;
}

// ویرایش شخص
if (isset($_POST['edit_person']) && canEditPersons()) {
    $person_id = filter_var($_POST['person_id'], FILTER_VALIDATE_INT);
    $name = htmlspecialchars($_POST['name']);

     if (empty($name)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'نام شخص الزامی است']);
        exit;
    }
        $updateStmt = $db->prepare("UPDATE persons SET name = :name WHERE id = :id");
        $success=$updateStmt->execute([':name' => $name, ':id' => $person_id]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'id' => $person_id,
        'name' => $name,
        'message' => $success ? 'شخص با موفقیت ویرایش شد' : 'خطا در ویرایش شخص'
    ]);
    exit;
}

// حذف شخص
if (isset($_POST['delete_person'])) {
    $person_id = filter_var($_POST['person_id'], FILTER_VALIDATE_INT);
if($person_id)
{
    $deleteStmt = $db->prepare("DELETE FROM persons WHERE id = :id");
    $success=$deleteStmt->execute([':id' => $person_id]);
    header('Content-Type: application/json');
    echo json_encode([
            'success' => $success,
        'id' => $person_id,
        'message' => $success ? 'شخص با موفقیت حذف شد' : 'خطا در حذف شخص']);
    exit;
}
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'شناسه نامعتبر']);
    exit;
}

// گرفتن لیست اشخاص
$persons = $db->query("SELECT * FROM persons ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعریف اشخاص</title>
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
                <span class="clock-display" id="liveClock"> <?php echo fa_number(now()); ?></span>
                <a href="logout.php" class="logout-btn-sidebar">🚪 خروج</a>
            </div>
        </div>

        <div class="main-title">
            <h1>👥 مدیریت اشخاص</h1>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <?php if (canEditPersons()): ?>
            <div class="add-card">
                <h2>➕ افزودن شخص جدید</h2>
                <form method="post" class="form-row">

                    <div class="personname-group">
                        <label>نام شخص</label>
                        <input type="text" name="name" required>
                    </div>

                    <button type="submit" name="add_person" class="btn-add">➕ ثبت</button>

                </form>
            </div>
        <?php endif; ?>

        <div class="persons-table data-table">
            <table>
                <thead>
            <tr>
                <th>ردیف</th>
                <th>نام شخص</th>
                <th>تاریخ ثبت</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($persons)): ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px;">👤 هیچ شخصی ثبت نشده است</td>
                </tr>
            <?php else: ?>
                <?php $row_num = 1; foreach ($persons as $person): ?>
                    <tr id="person_<?php echo $person['id']; ?>">
                        <td><?php echo fa_number($row_num); ?></td>
                        <td><?php echo htmlspecialchars($person['name']); ?></td>
                        <td class="date"><?php echo fa_number(htmlspecialchars($person['created_at'])); ?></td>
                        <td class="action-buttons">
                            <?php if (canEditPersons()): ?>
                                <button class="edit-btn" onclick='openEditModal(<?php echo $person['id']; ?>)'>✏️ ویرایش</button>
                            <?php endif; ?>
                            <?php if (canDeletePersons()): ?>
                                <button class="delete-btn" onclick="confirmDelete(<?php echo $person['id']; ?>, '<?php echo htmlspecialchars($person['name']); ?>')">🗑️ حذف</button>
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

<!-- مودال ویرایش شخص -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش شخص</h3>
        <form id="editForm">
            <input type="hidden" name="person_id" id="edit_person_id">
            <label>نام شخص</label>
            <input type="text" name="name" id="edit_name" required>

            <div class="modal-buttons">
                <button type="button"  class="btn-add" onclick="savepersonEdit()">💾 ذخیره</button>
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>