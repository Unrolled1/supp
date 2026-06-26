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

if (!isAdmin() || !canViewBrands()) {
    header('Location: requests.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';


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


// ============================================
// پردازش AJAX - ویرایش فعالیت
// ============================================

if (isset($_POST['edit_brands'])) {
    $brand_id = filter_var($_POST['brand_id'], FILTER_VALIDATE_INT);
    $name = htmlspecialchars(trim($_POST['name']));

    if (empty($name)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'نام برند الزامی است']);
        exit;
    }

    $updateStmt = $db->prepare("UPDATE brands SET name = :name WHERE id = :id");
    $success = $updateStmt->execute([':name' => $name, ':id' => $brand_id]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'id' => $brand_id,
        'name' => $name,
        'message' => $success ? 'برند با موفقیت ویرایش شد' : 'خطا در ویرایش برند'
    ]);
    exit;
}



// حذف فعالیت با AJAX
if (isset($_POST['delete_brands'])) {
    $brand_id = filter_var($_POST['brand_id'], FILTER_VALIDATE_INT);

    if ($brand_id) {
        $deleteStmt = $db->prepare("DELETE FROM brands WHERE id = :id");
        $success = $deleteStmt->execute([':id' => $brand_id]);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'id' => $brand_id,
            'message' => $success ? 'برند با موفقیت حذف شد' : 'خطا در حذف برند'
        ]);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'شناسه نامعتبر']);
    exit;
}

$brands = $db->query("SELECT * FROM brands ORDER BY id desc ")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعریف برندها</title>
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
            <h1>🏷️ تعریف برندها</h1>
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
                <form method="post" class="form-group">

                        <div class="brandname-group">
                        <label>نام برند</label>
                        <input type="text" name="name" required>
                        </div>

                        <button type="submit" name="add_brand" class="btn-add">➕ ثبت </button>

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
                        <tr id="brand_<?php echo $brand['id']; ?>">
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo htmlspecialchars($brand['name']); ?></td>
                            <td class="date-ltr"><?php echo fa_number(htmlspecialchars($brand['created_at'])); ?></td>
                            <td class="action-buttons">
                                <?php if (canEditBrands()): ?>
                                    <button class="edit-btn" onclick='openEditModal(<?php echo $brand['id']; ?>)'> ✏️ ویرایش</button>
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
        <form id="editForm">
            <input type="hidden" name="brand_id" id="edit_brand_id">
            <input type="hidden" name="edit_brands" value="1">

            <div class="form-row">
                <div class="form-group">
            <label>نام برند</label>
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
<script src="assets/js/alljs.js"></script>
<script src="assets/js/sweetalert2.min.js"></script>
<script src="assets/js/admin-brands.js"></script>
</body>
</html>