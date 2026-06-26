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


if (isset($_POST['add_topic']) && canEditTopics()) {
    $name = htmlspecialchars($_POST['name']);
    $stmt = $db->prepare("INSERT INTO topics (name,  created_at) VALUES (:name,  :created_at)");
    $stmt->execute([':name' => $name, ':created_at' => now()]);
    $successMessage = "✅ موضوع اضافه شد";
    header('Location: admin_topics.php');
    exit;
}

// ============================================
// پردازش AJAX - ویرایش فعالیت
// ============================================

if (isset($_POST['edit_topic'])) {
    $topic_id = filter_var($_POST['topic_id'], FILTER_VALIDATE_INT);
    $name = htmlspecialchars(trim($_POST['name']));

    if (empty($name)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'نام موضوع الزامی است']);
        exit;
    }

    $updateStmt = $db->prepare("UPDATE topics SET name = :name WHERE id = :id");
    $success = $updateStmt->execute([':name' => $name, ':id' => $topic_id]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'id' => $topic_id,
        'name' => $name,
        'message' => $success ? 'موضوع با موفقیت ویرایش شد' : 'خطا در ویرایش موضوع'
    ]);
    exit;
}

// حذف فعالیت با AJAX
if (isset($_POST['delete_topic'])) {
    $topic_id = filter_var($_POST['topic_id'], FILTER_VALIDATE_INT);

    if ($topic_id) {
        $deleteStmt = $db->prepare("DELETE FROM topics WHERE id = :id");
        $success = $deleteStmt->execute([':id' => $topic_id]);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'id' => $topic_id,
            'message' => $success ? 'موضوع با موفقیت حذف شد' : 'خطا در حذف موضوع'
        ]);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'شناسه نامعتبر']);
    exit;
}

$topics = $db->query("SELECT * FROM topics ORDER BY id DESC ")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعریف موضوعات</title>
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
            <h1>📋 تعریف موضوعات</h1>
        </div>
        <?php if($successMessage): ?>
            <div class="alert alert-success">
            <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if(canEditTopics()): ?>
            <div class="add-card">
                <h2>➕ افزودن موضوع جدید</h2>
                <form method="post" class="form-row">

                        <div class="topicname-group">
                        <label>نام موضوع</label>
                        <input type="text" name="name" required>
                    </div>

                    <button type="submit" name="add_topic" class="btn-add">➕ ثبت</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="topics-table data-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th
                    ><th>نام موضوع</th>
                    <th>تاریخ ثبت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($topics)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px;">
                            🏥 هیچ بخشی ثبت نشده است
                        </td>
                    </tr>
                <?php else: ?>
                <?php $i=1; foreach($topics as $t): ?>
                        <tr id="topic_<?php echo $t['id']; ?>">
                            <td><?php echo fa_number($i); ?></td>
                    <td><?php echo htmlspecialchars($t['name']); ?></td>
                        <td class="date-ltr"><?php echo fa_number(htmlspecialchars($t['created_at'])); ?></td>
                        <td class="action-buttons">
                        <?php if(canEditTopics()): ?>
                            <button class="edit-btn" onclick='openEditModal(<?php echo $t['id']; ?>)'>✏️ ویرایش</button>
                        <?php endif; ?>

                        <?php if(canDeleteTopics()): ?>
                        <button class="delete-btn" onclick="confirmDelete(<?php echo $t['id']; ?>,
                                '<?php echo htmlspecialchars($t['name']); ?>')">🗑️ حذف</button>
                        <?php endif; ?>
                    </td>
                    </tr>
                    <?php $i++;  ?>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش موضوع</h3>
        <form id="editForm">
            <input type="hidden" name="topic_id" id="edit_topic_id">
            <input type="hidden" name="edit_topic" value="1">

            <div class="form-row">
                <div class="form-group">
            <label>نام موضوع</label>
            <input type="text" name="name" id="edit_name" required>
                </div>
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn-add" onclick="savetopicsEdit()">💾 ذخیره</button>
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>