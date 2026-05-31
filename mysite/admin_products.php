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

if (!isAdmin() ) {
    header('Location: admin.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';

// حذف کالا
if (isset($_POST['delete_product']) && canDeleteProducts()) {
    $product_id = $_POST['product_id'];

    $deleteStmt = $db->prepare("DELETE FROM products WHERE id = :id");
    if ($deleteStmt->execute([':id' => $product_id])) {
        $successMessage = "✅ کالا با موفقیت حذف شد";
    } else {
        $errorMessage = "❌ خطا در حذف کالا";
    }
    header('Location: admin_products.php');
    exit;
}

// ویرایش کالا
if (isset($_POST['edit_product']) && canEditProducts()) {
    $product_id = $_POST['product_id'];
    $brand_id = $_POST['brand_id'] ?: null;
    $name = htmlspecialchars($_POST['name']);

    if (empty($name)) {
        $errorMessage = "❌ نام کالا الزامی است";
    } else {
        $updateStmt = $db->prepare("UPDATE products SET brand_id = :brand_id, name = :name WHERE id = :id");
        if ($updateStmt->execute([':brand_id' => $brand_id, ':name' => $name, ':id' => $product_id])) {
            $successMessage = "✅ کالا با موفقیت ویرایش شد";
        } else {
            $errorMessage = "❌ خطا در ویرایش کالا";
        }
    }
    header('Location: admin_products.php');
    exit;
}

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
    <title>مدیریت کالاها - پنل ادمین</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/admin-products.css">
    <link rel="stylesheet" href="styles/admin-sidebar.css">
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
            <div style="display: flex; align-items: center; gap: 15px;">
                <span class="clock-display" id="liveClock"><?php echo fa_number(now()); ?></span>
                <a href="logout.php" class="logout-btn-sidebar">🚪 خروج</a>
            </div>
        </div>

        <div class="main-title">
            <h1>📦 مدیریت کالاها</h1>
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
                    <div class="form-group">
                        <label>نام کالا</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>برند</label>
                        <select name="brand_id">
                            <option value="">-- انتخاب برند --</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="add_product" class="btn-add">➕ افزودن کالا</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="products-table">
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
                        <tr>
                            <td><?php echo fa_number($row_num); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['brand_name'] ?? '-'); ?></td>
                            <td class="date"><?php echo fa_number(htmlspecialchars($product['created_at'])); ?>}</td>
                            <td class="action-buttons">
                                <?php if (canEditProducts()): ?>
                                    <button class="edit-btn" onclick='openEditModal(<?php echo $product['id']; ?>, <?php echo json_encode($product); ?>)'>✏️ ویرایش</button>
                                <?php endif; ?>
                                <?php if (canDeleteProducts()): ?>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">🗑️ حذف</button>
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
        <form method="post">
            <input type="hidden" name="product_id" id="edit_product_id">
            <label>نام کالا</label>
            <input type="text" name="name" id="edit_name" required>
            <label>برند</label>
            <select name="brand_id" id="edit_brand_id">
                <option value="">-- انتخاب برند --</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="modal-buttons">
                <button type="submit" name="edit_product" class="modal-save">💾 ذخیره</button>
                <button type="button" class="modal-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, product) {
        document.getElementById('edit_product_id').value = id;
        document.getElementById('edit_name').value = product.name;
        document.getElementById('edit_brand_id').value = product.brand_id || '';
        document.getElementById('editModal').style.display = 'flex';
    }

    function confirmDelete(id, name) {
        if (confirm('آیا از حذف کالا "' + name + '" مطمئن هستید؟')) {
            var form = document.createElement('form');
            form.method = 'post';
            form.innerHTML = '<input type="hidden" name="delete_product" value="1"><input type="hidden" name="product_id" value="' + id + '">';
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