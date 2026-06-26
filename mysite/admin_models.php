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

if (!isAdmin() || !canViewModels()) {
    header('Location: requests.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';


// ============================================
// افزودن مدل جدید
// ============================================

if (isset($_POST['add_model']) && canEditModels()) {
    $name = htmlspecialchars($_POST['name']);
    $brand_id = !empty($_POST['brand_id']) ? filter_var($_POST['brand_id'], FILTER_VALIDATE_INT) : null;

    if (empty($name)) {
        $errorMessage = "❌ نام مدل الزامی است";
    } else {
        $insertStmt = $db->prepare("INSERT INTO models (name, brand_id, created_at) VALUES (:name, :brand_id, :created_at)");
        $jalaliDate = now();
        if ($insertStmt->execute([':name' => $name, ':brand_id' => $brand_id, ':created_at' => $jalaliDate])) {
            $successMessage = "✅ مدل با موفقیت اضافه شد";
        } else {
            $errorMessage = "❌ خطا در افزودن مدل";
        }
    }
    header('Location: admin_models.php');
    exit;
}

// ============================================
// پردازش AJAX - ویرایش فعالیت
// ============================================

if (isset($_POST['edit_model'])) {
    $model_id = filter_var($_POST['model_id'], FILTER_VALIDATE_INT);
    $name = htmlspecialchars(trim($_POST['name']));

    if (empty($name)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'نام مدل الزامی است']);
        exit;
    }

    $updateStmt = $db->prepare("UPDATE models SET name = :name WHERE id = :id");
    $success = $updateStmt->execute([':name' => $name, ':id' => $model_id]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'id' => $model_id,
        'name' => $name,
        'message' => $success ? 'مدل با موفقیت ویرایش شد' : 'خطا در ویرایش مدل'
    ]);
    exit;
}

// حذف فعالیت با AJAX
if (isset($_POST['delete_model'])) {
    $model_id = filter_var($_POST['model_id'], FILTER_VALIDATE_INT);

    if ($model_id) {
        $deleteStmt = $db->prepare("DELETE FROM models WHERE id = :id");
        $success = $deleteStmt->execute([':id' => $model_id]);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'id' => $model_id,
            'message' => $success ? 'مدل با موفقیت حذف شد' : 'خطا در حذف مدل'
        ]);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'شناسه نامعتبر']);
    exit;
}

// ============================================
// گرفتن لیست مدل‌ها با اطلاعات برند
// ============================================

$models = $db->query("
    SELECT m.*, b.name as brand_name 
    FROM models m
    LEFT JOIN brands b ON m.brand_id = b.id
    ORDER BY b.name, m.name
")->fetchAll();

// ============================================
// گرفتن لیست برندها برای سلکت
// ============================================

$brands = $db->query("SELECT id, name FROM brands ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعریف مدل‌ها</title>
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
            <h1>📦 تعریف مدل‌ها</h1>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- فرم افزودن مدل -->
        <!-- ============================================ -->
        <?php if (canEditModels()): ?>
            <div class="add-card">
                <h2>➕ افزودن مدل جدید</h2>
                <form method="post" class="form-row">

                    <div class="modelname-group">
                        <label>نام مدل</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="brandname-group">
                        <label>برند</label>
                        <select name="brand_id">
                            <option value="">-- بدون برند --</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                        <button type="submit" name="add_model" class="btn-add">➕ ثبت</button>

                </form>
            </div>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- جدول مدل‌ها -->
        <!-- ============================================ -->
        <div class="models-table data-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>نام مدل</th>
                    <th>برند</th>
                    <th>تاریخ ثبت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($models)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px;">📦 هیچ مدلی ثبت نشده است</td>
                    </tr>
                <?php else: ?>
                    <?php $row_num = 1; foreach ($models as $model): ?>
                        <tr id="model_<?php echo $model['id']; ?>">
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo htmlspecialchars($model['name']); ?></td>
                            <td><?php echo htmlspecialchars($model['brand_name'] ?? '-'); ?></td>
                            <td class="date-ltr"><?php echo fa_number(htmlspecialchars($model['created_at'])); ?></td>
                            <td class="action-buttons">
                                <?php if (canEditModels()): ?>
                                    <button class="edit-btn" onclick='openEditModal(<?php echo $model['id']; ?>)'>✏️ویرایش</button>
                                <?php endif; ?>
                                <?php if (canDeleteModels()): ?>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $model['id']; ?>,
                                            '<?php echo htmlspecialchars($model['name']); ?>')">🗑️حذف</button>
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

<!-- ============================================ -->
<!-- مودال ویرایش مدل -->
<!-- ============================================ -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش مدل</h3>
        <form id="editForm">
            <input type="hidden" name="model_id" id="edit_model_id">
            <input type="hidden" name="edit_topic" value="1">

            <div class="form-row">

                <div class="form-group">
                    <label>نام مدل</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>

                <div class="form-group">
                    <label>برند</label>
                    <select name="brand_id" id="edit_brand_id">
                        <option value="">-- بدون برند --</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
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