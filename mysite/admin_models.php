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

// ============================================
// گرفتن لیست برندها برای سلکت
// ============================================

$brands = $db->query("SELECT id, name FROM brands ORDER BY name ASC")->fetchAll();

// ============================================
// حذف مدل
// ============================================

if (isset($_POST['delete_model']) && canDeleteModels()) {
    $model_id = $_POST['model_id'];

    // بررسی استفاده در جداول دیگر
    $tables = ['cpus', 'motherboards', 'rams', 'storages', 'powers', 'monitors', 'peripherals'];
    $used = false;
    foreach ($tables as $table) {
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE model_id = :model_id");
        $checkStmt->execute([':model_id' => $model_id]);
        if ($checkStmt->fetchColumn() > 0) {
            $used = true;
            break;
        }
    }

    if ($used) {
        $errorMessage = "❌ این مدل در قطعات استفاده شده است. ابتدا قطعات را تغییر دهید.";
    } else {
        $deleteStmt = $db->prepare("DELETE FROM models WHERE id = :id");
        if ($deleteStmt->execute([':id' => $model_id])) {
            $successMessage = "✅ مدل با موفقیت حذف شد";
        } else {
            $errorMessage = "❌ خطا در حذف مدل";
        }
    }
    header('Location: admin_models.php');
    exit;
}

// ============================================
// ویرایش مدل
// ============================================

if (isset($_POST['edit_model']) && canEditModels()) {
    $model_id = $_POST['model_id'];
    $name = htmlspecialchars($_POST['name']);
    $brand_id = !empty($_POST['brand_id']) ? filter_var($_POST['brand_id'], FILTER_VALIDATE_INT) : null;

    if (empty($name)) {
        $errorMessage = "❌ نام مدل الزامی است";
    } else {
        $updateStmt = $db->prepare("UPDATE models SET name = :name, brand_id = :brand_id WHERE id = :id");
        if ($updateStmt->execute([':name' => $name, ':brand_id' => $brand_id, ':id' => $model_id])) {
            $successMessage = "✅ مدل با موفقیت ویرایش شد";
        } else {
            $errorMessage = "❌ خطا در ویرایش مدل";
        }
    }
    header('Location: admin_models.php');
    exit;
}

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
// گرفتن لیست مدل‌ها با اطلاعات برند
// ============================================

$models = $db->query("
    SELECT m.*, b.name as brand_name 
    FROM models m
    LEFT JOIN brands b ON m.brand_id = b.id
    ORDER BY b.name, m.name
")->fetchAll();
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

        <!-- ============================================ -->
        <!-- فرم افزودن مدل -->
        <!-- ============================================ -->
        <?php if (canEditModels()): ?>
            <div class="add-card">
                <h2>➕ افزودن مدل جدید</h2>
                <form method="post" class="form-row">
                    <div class="form-group">
                        <label>نام مدل</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>برند</label>
                        <select name="brand_id">
                            <option value="">-- بدون برند --</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="add_model" class="btn-add">➕ افزودن</button>
                    </div>
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
                        <tr>
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo htmlspecialchars($model['name']); ?></td>
                            <td><?php echo htmlspecialchars($model['brand_name'] ?? '-'); ?></td>
                            <td class="date"><?php echo fa_number(htmlspecialchars($model['created_at'])); ?></td>
                            <td class="action-buttons">
                                <?php if (canEditModels()): ?>
                                    <button class="edit-btn" onclick='openEditModal(<?php echo json_encode($model); ?>)'>✏️</button>
                                <?php endif; ?>
                                <?php if (canDeleteModels()): ?>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $model['id']; ?>, '<?php echo htmlspecialchars($model['name']); ?>')">🗑️</button>
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
        <form method="post">
            <input type="hidden" name="model_id" id="edit_model_id">

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
                <button type="submit" name="edit_model" class="btn-add">💾 ذخیره</button>
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(model) {
        document.getElementById('edit_model_id').value = model.id;
        document.getElementById('edit_name').value = model.name || '';
        document.getElementById('edit_brand_id').value = model.brand_id || '';
        document.getElementById('editModal').style.display = 'flex';
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'آیا مطمئن هستید؟',
            text: 'مدل "' + name + '" حذف خواهد شد!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'بله، حذف شود',
            cancelButtonText: 'لغو',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = '<input type="hidden" name="delete_model" value="1"><input type="hidden" name="model_id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        });
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>