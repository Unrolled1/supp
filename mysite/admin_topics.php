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

if (isset($_POST['add_topic']) && canEditTopics()) {
    $name = htmlspecialchars($_POST['name']);
    $stmt = $db->prepare("INSERT INTO topics (name, status, created_at) VALUES (:name, 'active', :created_at)");
    $stmt->execute([':name' => $name, ':created_at' => now()]);
    $successMessage = "✅ موضوع اضافه شد";
    header('Location: admin_topics.php');
    exit;
}

if (isset($_POST['edit_topic']) && canEditTopics()) {
    $id = $_POST['id'];
    $name = htmlspecialchars($_POST['name']);
    $stmt = $db->prepare("UPDATE topics SET name = :name WHERE id = :id");
    $stmt->execute([':name' => $name, ':id' => $id]);
    $successMessage = "✅ موضوع ویرایش شد";
    header('Location: admin_topics.php');
    exit;
}

if (isset($_POST['delete_topic']) && canDeleteTopics()) {
    $id = $_POST['id'];
    $stmt = $db->prepare("DELETE FROM topics WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $successMessage = "✅ موضوع حذف شد";
    header('Location: admin_topics.php');
    exit;
}

$topics = $db->query("SELECT * FROM topics ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت موضوعات</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/sidebar.css">
    <link rel="stylesheet" href="styles/admin-topics.css">
</head>
<body>
<div class="admin-wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="main-header">
            <div class="user-info"><span>👨‍💼</span><span class="user-name"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span></div>
            <div><span class="clock-display" id="liveClock"><?php echo fa_number(now()); ?>
                </span><a href="logout.php" class="logout-btn-sidebar">🚪 خروج</a></div>
        </div>
        <div class="main-title"><h1>📋 مدیریت موضوعات</h1></div>
        <?php if($successMessage): ?><div class="alert alert-success"><?php echo $successMessage; ?></div><?php endif; ?>

        <?php if(canEditTopics()): ?>
            <div class="add-card">
                <h2>➕ افزودن موضوع جدید</h2>
                <form method="post"><div class="form-group">
                        <label>نام موضوع</label>
                        <input type="text" name="name" required></div>
                    <button type="submit" name="add_topic" class="btn-add">➕ افزودن</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="topics-table data-table"><table><thead><tr><th>ردیف</th><th>نام موضوع</th><th>عملیات</th></tr></thead>
                <tbody><?php $i=1; foreach($topics as $t): ?>
                    <tr><td><?php echo fa_number($i); ?></td><td><?php echo htmlspecialchars($t['name']); ?></td>
                    <td class="action-buttons">
                        <?php if(canEditTopics()): ?><button class="edit-btn" onclick='openEditModal(<?php echo $t['id']; ?>,"<?php echo htmlspecialchars($t['name']); ?>")'>✏️ ویرایش</button><?php endif; ?>
                        <?php if(canDeleteTopics()): ?><button class="delete-btn" onclick="confirmDelete(<?php echo $t['id']; ?>,'<?php echo htmlspecialchars($t['name']); ?>')">🗑️ حذف</button><?php endif; ?>
                    </td></tr><?php $i++; endforeach; ?></tbody>
            </table></div>
    </div>
</div>
<div id="editModal" class="modal"><div class="modal-content"><h3>✏️ ویرایش موضوع</h3>
        <form method="post"><input type="hidden" name="id" id="edit_id"><label>نام موضوع</label><input type="text" name="name" id="edit_name" required>
            <div class="modal-buttons"><button type="submit" name="edit_topic" class="modal-save">💾 ذخیره</button><button type="button" class="modal-cancel" onclick="closeModal('editModal')">لغو</button></div></form></div></div>
<script>
    function openEditModal(id,n){document.getElementById('edit_id').value=id;document.getElementById('edit_name').value=n;document.getElementById('editModal').style.display='flex';}
    function closeModal(m){document.getElementById(m).style.display='none';}
    function confirmDelete(id,n){if(confirm('آیا از حذف موضوع "'+n+'" مطمئن هستید؟'))location.href='admin_topics.php?delete_topic=1&id='+id;}
    function updateClock(){fetch('get_time.php').then(r=>r.json()).then(d=>{let c=document.getElementById('liveClock');if(c)c.innerHTML='📅 '+d.datetime;});}
    setInterval(updateClock,1000);updateClock();
</script>
</body>
</html>