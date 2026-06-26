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

if (!isAdmin() ) {
    header('Location: requests.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';


// افزودن کالا جدید
if (isset($_POST['add_product']) && canEditProducts()) {
    $brand_id = $_POST['brand_id'] ?: null;
    $name = htmlspecialchars($_POST['name']);

    if (empty($name)) {
        $errorMessage = "❌ نام کالا الزامی است";
    } else {
        $insertStmt = $db->prepare("INSERT INTO products (brand_id, name, created_at) VALUES (:brand_id, :name, :created_at)");
        $jalaliDate = now();
        if ($insertStmt->execute([':brand_id' => $brand_id, ':name' => $name, ':created_at' => $jalaliDate])) {
            $successMessage = "✅ کالا با موفقیت اضافه شد";
        } else {
            $errorMessage = "❌ خطا در افزودن کالا";
        }
    }
    header('Location: admin_products.php');
    exit;
}


// ویرایش کالا
if (isset($_POST['edit_product'])) {
    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    $brand_id = $_POST['brand_id'] ?: null;
    $name = htmlspecialchars($_POST['name']);

    if (empty($name)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'نام کالا الزامی است']);
        exit;
    }

    $updateStmt = $db->prepare("UPDATE products SET brand_id = :brand_id, name = :name WHERE id = :id");
    $success=$updateStmt->execute([':brand_id' => $brand_id, ':name' => $name, ':id' => $product_id]);

        header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'id' => $product_id,
        'name' => $name,
        'message' => $success ? 'کالا با موفقیت ویرایش شد' : 'خطا در ویرایش کالا'
    ]);
    exit;
}

// حذف کالا
if (isset($_POST['delete_product'])) {
    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);

if ($product_id) {
    $deleteStmt = $db->prepare("DELETE FROM products WHERE id = :id");
    $success = $deleteStmt->execute([':id' => $product_id]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'id' => $product_id,
        'message' => $success ? 'کالا با موفقیت حذف شد' : 'خطا در حذف کالا'
    ]);
    exit;
}
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'شناسه نامعتبر']);
    exit;
}


// گرفتن لیست برندها
$brands = $db->query("SELECT id, name FROM brands ORDER BY name ASC")->fetchAll();

// گرفتن لیست کالاها با نام برند
$products = $db->query("
    SELECT p.*, b.name as brand_name 
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    ORDER BY p.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعریف کالاها</title>
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
            <h1>📦 تعریف کالاها</h1>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <?php if (canEditProducts()): ?>
            <div class="add-card">
                <h2>➕ افزودن کالا جدید</h2>
                <form method="post" class="form-row">

                    <div class="productname-group">
                        <label>نام کالا</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="brandname-group">
                        <label>برند</label>
                        <select name="brand_id">
                            <option value="">-- انتخاب برند --</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                        <button type="submit" name="add_product" class="btn-add">➕ ثبت </button>

                </form>
            </div>
        <?php endif; ?>

        <div class="products-table data-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>نام کالا</th>
                    <th>برند</th>
                    <th>تاریخ ثبت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px;">📦 هیچ کالایی ثبت نشده است</td>
                    </tr>
                <?php else: ?>
                    <?php $row_num = 1; ?>
                    <?php foreach ($products as $product): ?>
                        <tr id="product_<?php echo $product['id']; ?>">
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['brand_name'] ?? '-'); ?></td>
                            <td class="date"><?php echo fa_number(htmlspecialchars($product['created_at'])); ?></td>
                            <td class="action-buttons">
                                <?php if (canEditProducts()): ?>
                                    <button class="edit-btn" onclick='openEditModal(<?php echo $product['id']; ?>)'>✏️ ویرایش</button>
                                <?php endif; ?>

                                <?php if (canDeleteProducts()): ?>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $product['id']; ?>,
                                            '<?php echo htmlspecialchars($product['name']); ?>')">🗑️ حذف</button>
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

<!-- مودال ویرایش کالا -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش کالا</h3>
        <form id="editForm">

            <input type="hidden" name="product_id" id="edit_product_id">
            <input type="hidden" name="edit_product" value="1">

            <div class="form-row">
                <div class="form-group">
            <label>نام کالا</label>
            <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
            <label>برند</label>
            <select name="brand_id" id="edit_brand_id">
                <option value="">-- انتخاب برند --</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                <?php endforeach; ?>
            </select>
                </div>
            </div>

            <div class="modal-buttons">
                <button type="button"  class="btn-add" onclick="saveEdit()">💾 ذخیره</button>
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>