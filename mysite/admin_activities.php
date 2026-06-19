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

if (!isAdmin()) {
    header('Location: admin.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';

// حذف فعالیت
if (isset($_POST['delete_activity'])) {
    $activity_id = $_POST['activity_id'];

    $deleteStmt = $db->prepare("DELETE FROM activities WHERE id = :id");
    if ($deleteStmt->execute([':id' => $activity_id])) {
        $successMessage = "✅ فعالیت با موفقیت حذف شد";
    } else {
        $errorMessage = "❌ خطا در حذف فعالیت";
    }
    header('Location: admin_activities.php');
    exit;
}

// ویرایش فعالیت
if (isset($_POST['edit_activity'])) {
    $activity_id = $_POST['activity_id'];
    $name = htmlspecialchars($_POST['name']);

    if (empty($name)) {
        $errorMessage = "❌ نام فعالیت الزامی است";
    } else {
        $updateStmt = $db->prepare("UPDATE activities SET name = :name WHERE id = :id");
        if ($updateStmt->execute([':name' => $name, ':id' => $activity_id])) {
            $successMessage = "✅ فعالیت با موفقیت ویرایش شد";
        } else {
            $errorMessage = "❌ خطا در ویرایش فعالیت";
        }
    }
    header('Location: admin_activities.php');
    exit;
}

// افزودن فعالیت جدید
if (isset($_POST['add_activity'])) {
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

// گرفتن لیست فعالیت‌ها
$activities = $db->query("SELECT * FROM activities ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>خدمات - پنل ادمین</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/sidebar.css">
    <link rel="stylesheet" href="styles/admin-activities.css">
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
            <h1>📋 خدمات</h1>
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
            <form method="post" class="form-inline">
                <div class="form-group-inline">
                    <label>نام فعالیت</label>
                    <input type="text" name="name" required>
                </div>
                <button type="submit" name="add_activity" class="btn-add">➕ افزودن فعالیت</button>
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
                        <tr>
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo htmlspecialchars($activity['name']); ?></td>
                            <td class="date"><?php echo fa_number(htmlspecialchars($activity['created_at'])); ?></td>
                            <td class="action-buttons">
                                <button class="edit-btn" onclick='openEditModal(<?php echo $activity['id']; ?>, "<?php echo htmlspecialchars($activity['name']); ?>")'>✏️ ویرایش</button>
                                <button class="delete-btn" onclick="confirmDelete(<?php echo $activity['id']; ?>, '<?php echo htmlspecialchars($activity['name']); ?>')">🗑️ حذف</button>
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
        <form method="post">
            <input type="hidden" name="activity_id" id="edit_activity_id">
            <label>نام فعالیت</label>
            <input type="text" name="name" id="edit_name" required>
            <div class="modal-buttons">
                <button type="submit" name="edit_activity" class="modal-save">💾 ذخیره</button>
                <button type="button" class="modal-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name) {
        document.getElementById('edit_activity_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('editModal').style.display = 'flex';
    }

    function confirmDelete(id, name) {
        if (confirm('آیا از حذف فعالیت "' + name + '" مطمئن هستید؟')) {
            var form = document.createElement('form');
            form.method = 'post';
            form.innerHTML = '<input type="hidden" name="delete_activity" value="1"><input type="hidden" name="activity_id" value="' + id + '">';
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