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

if (!isAdmin() || !canViewModels()) {
    header('Location: admin.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';

// حذف مدل
if (isset($_POST['delete_model']) && canDeleteModels()) {
    $model_id = $_POST['model_id'];

    $deleteStmt = $db->prepare("DELETE FROM models WHERE id = :id");
    if ($deleteStmt->execute([':id' => $model_id])) {
        $successMessage = "✅ مدل با موفقیت حذف شد";
    } else {
        $errorMessage = "❌ خطا در حذف مدل";
    }
    header('Location: admin_models.php');
    exit;
}

// ویرایش مدل
if (isset($_POST['edit_model']) && canEditModels()) {
    $model_id = $_POST['model_id'];
    $name = htmlspecialchars($_POST['name']);

    if (empty($name)) {
        $errorMessage = "❌ نام مدل الزامی است";
    } else {
        $updateStmt = $db->prepare("UPDATE models SET name = :name WHERE id = :id");
        if ($updateStmt->execute([':name' => $name, ':id' => $model_id])) {
            $successMessage = "✅ مدل با موفقیت ویرایش شد";
        } else {
            $errorMessage = "❌ خطا در ویرایش مدل";
        }
    }
    header('Location: admin_models.php');
    exit;
}

// افزودن مدل جدید
if (isset($_POST['add_model']) && canEditModels()) {
    $name = htmlspecialchars($_POST['name']);

    if (empty($name)) {
        $errorMessage = "❌ نام مدل الزامی است";
    } else {
        $insertStmt = $db->prepare("INSERT INTO models (name, created_at) VALUES (:name, :created_at)");
        $jalaliDate = now();
        if ($insertStmt->execute([':name' => $name, ':created_at' => $jalaliDate])) {
            $successMessage = "✅ مدل با موفقیت اضافه شد";
        } else {
            $errorMessage = "❌ خطا در افزودن مدل";
        }
    }
    header('Location: admin_models.php');
    exit;
}

// گرفتن لیست مدل‌ها
$models = $db->query("SELECT * FROM models ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت مدل‌ها - پنل ادمین</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/admin-models.css">
    <link rel="stylesheet" href="styles/sidebar.css">
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
            <h1>📦 مدیریت مدل‌ها</h1>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <?php if (canEditModels()): ?>
            <div class="add-card">
                <h2>➕ افزودن مدل جدید</h2>
                <form method="post" class="form-inline">
                    <div class="form-group-inline">
                        <label>نام مدل</label>
                        <input type="text" name="name" required>
                    </div>
                    <button type="submit" name="add_model" class="btn-add">➕ افزودن</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="models-table data-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>نام مدل</th>
                    <th>تاریخ ثبت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($models)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px;">📦 هیچ مدلی ثبت نشده است</td>
                    </tr>
                <?php else: ?>
                    <?php $row_num = 1; foreach ($models as $model): ?>
                        <tr>
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo htmlspecialchars($model['name']); ?></td>
                            <td class="date"><?php echo fa_number(htmlspecialchars($model['created_at'])); ?></td>
                            <td class="action-buttons">
                                <?php if (canEditModels()): ?>
                                    <button class="edit-btn" onclick='openEditModal(<?php echo $model['id']; ?>, "<?php echo htmlspecialchars($model['name']); ?>")'>✏️ ویرایش</button>
                                <?php endif; ?>
                                <?php if (canDeleteModels()): ?>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $model['id']; ?>, '<?php echo htmlspecialchars($model['name']); ?>')">🗑️ حذف</button>
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

<!-- مودال ویرایش مدل -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش مدل</h3>
        <form method="post">
            <input type="hidden" name="model_id" id="edit_model_id">
            <label>نام مدل</label>
            <input type="text" name="name" id="edit_name" required>
            <div class="modal-buttons">
                <button type="submit" name="edit_model" class="modal-save">💾 ذخیره</button>
                <button type="button" class="modal-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name) {
        document.getElementById('edit_model_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('editModal').style.display = 'flex';
    }

    function confirmDelete(id, name) {
        if (confirm('آیا از حذف مدل "' + name + '" مطمئن هستید؟')) {
            var form = document.createElement('form');
            form.method = 'post';
            form.innerHTML = '<input type="hidden" name="delete_model" value="1"><input type="hidden" name="model_id" value="' + id + '">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }

    function updateClock() {
        fetch('get_time.php').then(r=>r.json()).then(d=>{
            var c = document.getElementById('liveClock');
            if (c) c.innerHTML = '📅 ' + d.datetime;
        }).catch(e=>console.log(e));
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
</body>
</html>