<?php
session_start();
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
    $description = htmlspecialchars($_POST['description']);
    $stmt = $db->prepare("INSERT INTO departments (name, description, created_at, status) VALUES (:name, :description, :created_at, 'active')");
    $stmt->execute([':name' => $name, ':description' => $description, ':created_at' => now()]);
    $successMessage = "✅ بخش اضافه شد";
    header('Location: admin_departments.php');
    exit;
}

if (isset($_POST['edit_department']) && canEditDepartments()) {
    $id = $_POST['id'];
    $name = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);
    $stmt = $db->prepare("UPDATE departments SET name = :name, description = :description WHERE id = :id");
    $stmt->execute([':name' => $name, ':description' => $description, ':id' => $id]);
    $successMessage = "✅ بخش ویرایش شد";
    header('Location: admin_departments.php');
    exit;
}

if (isset($_POST['delete_department']) && canDeleteDepartments()) {
    $id = $_POST['id'];
    $stmt = $db->prepare("DELETE FROM departments WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $successMessage = "✅ بخش حذف شد";
    header('Location: admin_departments.php');
    exit;
}

$departments = $db->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت بخش‌ها</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/sidebar.css">
    <link rel="stylesheet" href="styles/admin-departments.css">
</head>
<body>
<div class="admin-wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="main-header">
            <div class="user-info"><span>👨‍💼</span><span class="user-name"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span></div>
            <div><span class="clock-display" id="liveClock"><?php echo fa_number(now()); ?></span><a href="logout.php" class="logout-btn-sidebar">🚪 خروج</a></div>
        </div>
        <div class="main-title"><h1>🏥 مدیریت بخش‌ها</h1></div>
        <?php if($successMessage): ?><div class="alert alert-success"><?php echo $successMessage; ?></div><?php endif; ?>

        <?php if(canEditDepartments()): ?>
            <div class="add-card"><h2>➕ افزودن بخش جدید</h2>
                <form method="post" class="form-row"><div class="form-group"><label>نام بخش</label><input type="text" name="name" required></div>
                    <div class="form-group"><label>توضیحات</label><input type="text" name="description"></div>
                    <div class="form-group"><button type="submit" name="add_department" class="btn-add">➕ افزودن</button></div></form></div>
        <?php endif; ?>

        <div class="departments-table data-table">
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>نام بخش</th>
                    <th>توضیحات</th>
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
                    <?php $i = 1; foreach ($departments as $d): ?>
                        <tr>
                            <td><?php echo fa_number($i); ?></td>
                            <td><?php echo htmlspecialchars($d['name']); ?></td>
                            <td><?php echo htmlspecialchars($d['description'] ?? '-'); ?></td>
                            <td class="action-buttons">
                                <?php if (canEditDepartments()): ?>
                                    <button class="edit-btn" onclick='openEditModal(<?php echo $d['id']; ?>, "<?php echo htmlspecialchars($d['name']); ?>", "<?php echo htmlspecialchars($d['description']); ?>")'>✏️ ویرایش</button>
                                <?php endif; ?>
                                <?php if (canDeleteDepartments()): ?>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $d['id']; ?>, '<?php echo htmlspecialchars($d['name']); ?>')">🗑️ حذف</button>
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
<div id="editModal" class="modal"><div class="modal-content"><h3>✏️ ویرایش بخش</h3>
        <form method="post"><input type="hidden" name="id" id="edit_id"><label>نام بخش</label><input type="text" name="name" id="edit_name" required><label>توضیحات</label><input type="text" name="description" id="edit_description">
            <div class="modal-buttons"><button type="submit" name="edit_department" class="modal-save">💾 ذخیره</button><button type="button" class="modal-cancel" onclick="closeModal('editModal')">لغو</button></div></form></div></div>
<script>
    function openEditModal(id,n,d) {
        document.getElementById('edit_id').value=id;document.getElementById('edit_name').value=n;document.getElementById('edit_description').value=d||'';document.getElementById('editModal').style.display='flex';}
    function closeModal(m){document.getElementById(m).style.display='none';}
    function confirmDelete(id,n) {
        if (confirm('آیا از حذف بخش "' + n + '" مطمئن هستید؟')) {
            var form = document.createElement('form');
            form.method = 'post';
            form.innerHTML = '<input type="hidden" name="delete_department" value="1"><input type="hidden" name="id" value="' + id + '">';
            document.body.appendChild(form);
            form.submit();
        }
    }
    function updateClock(){fetch('get_time.php').then(r=>r.json()).then(d=>{let c=document.getElementById('liveClock');if(c)c.innerHTML='📅 '+d.datetime;});}
    setInterval(updateClock,1000);updateClock();
</script>
</body>
</html>