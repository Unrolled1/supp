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

if (!isAdmin() || !canViewBrands()) {
    header('Location: admin.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';

// حذف برند
if (isset($_POST['delete_brand']) && canDeleteBrands()) {
    $brand_id = $_POST['brand_id'];

    $checkStmt = $db->prepare("SELECT COUNT(*) FROM products WHERE brand_id = :brand_id");
    $checkStmt->execute([':brand_id' => $brand_id]);
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        $errorMessage = "❌ این برند در کالاها استفاده شده است. ابتدا کالاها را تغییر دهید.";
    } else {
        $deleteStmt = $db->prepare("DELETE FROM brands WHERE id = :id");
        if ($deleteStmt->execute([':id' => $brand_id])) {
            $successMessage = "✅ برند با موفقیت حذف شد";
        } else {
            $errorMessage = "❌ خطا در حذف برند";
        }
    }
    header('Location: admin_brands.php');
    exit;
}

// ویرایش برند
if (isset($_POST['edit_brand']) && canEditBrands()) {
    $brand_id = $_POST['brand_id'];
    $name = htmlspecialchars($_POST['name']);

    if (empty($name)) {
        $errorMessage = "❌ نام برند الزامی است";
    } else {
        $updateStmt = $db->prepare("UPDATE brands SET name = :name WHERE id = :id");
        if ($updateStmt->execute([':name' => $name, ':id' => $brand_id])) {
            $successMessage = "✅ برند با موفقیت ویرایش شد";
        } else {
            $errorMessage = "❌ خطا در ویرایش برند";
        }
    }
    header('Location: admin_brands.php');
    exit;
}

// افزودن برند جدید
if (isset($_POST['add_brand']) && canEditBrands()) {
    $name = htmlspecialchars($_POST['name']);

    if (empty($name)) {
        $errorMessage = "❌ نام برند الزامی است";
    } else {
        $insertStmt = $db->prepare("INSERT INTO brands (name, created_at) VALUES (:name, :created_at)");
        $jalaliDate = now();
        if ($insertStmt->execute([':name' => $name, ':created_at' => $jalaliDate])) {
            $successMessage = "✅ برند با موفقیت اضافه شد";
        } else {
            $errorMessage = "❌ خطا در افزودن برند";
        }
    }
    header('Location: admin_brands.php');
    exit;
}

$brands = $db->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت برندها - پنل ادمین</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/sidebar.css">
    <link rel="stylesheet" href="styles/admin-brands.css">
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
            <h1>🏷️ مدیریت برندها</h1>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <?php if (canEditBrands()): ?>
            <div class="add-card">
                <h2>➕ افزودن برند جدید</h2>
                <form method="post" class="form-row">
                    <div class="form-group">
                        <label>نام برند</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="add_brand" class="btn-add">➕ افزودن </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="brands-table data-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>نام برند</th>
                    <th>تاریخ ثبت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($brands)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px;">🏷️ هیچ برندی ثبت نشده است</td>
                    </tr>
                <?php else: ?>
                    <?php $row_num = 1; foreach ($brands as $brand): ?>
                        <tr>
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo htmlspecialchars($brand['name']); ?></td>
                            <td class="date"><?php echo fa_number(htmlspecialchars($brand['created_at'])); ?></td>
                            <td class="action-buttons">
                                <?php if (canEditBrands()): ?>
                                    <button class="edit-btn" onclick='openEditModal(<?php echo $brand['id']; ?>, "<?php echo htmlspecialchars($brand['name']); ?>")'>✏️ ویرایش</button>
                                <?php endif; ?>
                                <?php if (canDeleteBrands()): ?>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $brand['id']; ?>, '<?php echo htmlspecialchars($brand['name']); ?>')">🗑️ حذف</button>
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

<!-- مودال ویرایش برند -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش برند</h3>
        <form method="post">
            <input type="hidden" name="brand_id" id="edit_brand_id">
            <label>نام برند</label>
            <input type="text" name="name" id="edit_name" required>
            <div class="modal-buttons">
                <button type="submit" name="edit_brand" class="modal-save">💾 ذخیره</button>
                <button type="button" class="modal-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name) {
        document.getElementById('edit_brand_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('editModal').style.display = 'flex';
    }

    function confirmDelete(id, name) {
        if (confirm('آیا از حذف برند "' + name + '" مطمئن هستید؟')) {
            var form = document.createElement('form');
            form.method = 'post';
            form.innerHTML = '<input type="hidden" name="delete_brand" value="1"><input type="hidden" name="brand_id" value="' + id + '">';
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