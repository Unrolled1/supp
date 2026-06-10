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

if (!isAdmin() || !canManageUsers()) {
    header('Location: admin.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';

// گرفتن لیست دسترسی‌ها
$allPermissions = $db->query("SELECT DISTINCT permission_key FROM permissions WHERE role = 'admin' ORDER BY permission_key")->fetchAll();

$permissionLabels = [
    'tickets_manage' => 'مدیریت درخواست ها',
    'tickets_edit' => 'ويرايش درخواست ها',
    'tickets_delete' => 'حذف درخواست ها',

    'activities_manage' => 'مدیریت خدمات',
    'activities_edit' => 'ويرايش خدمات',
    'activities_delete' => 'حذف خدمات',

    'departments_manage' => 'مدیریت بخش ها',
    'departments_edit' => 'ويرايش بخش ها',
    'departments_delete' => 'حذف بخش ها',

    'topics_manage' => 'مدیریت موضوعات',
    'topics_edit' => 'ويرايش موضوعات',
    'topics_delete' => 'حذف موضوعات',

    'brands_manage' => 'مدیریت برندها',
    'brands_edit' => 'ويرايش برندها',
    'brands_delete' => 'حذف برندها',

    'models_manage' => 'مدیریت مدل‌ها',
    'models_edit' => 'ويرايش مدل‌ها',
    'models_delete' => 'حذف مدل‌ها',

    'products_manage' => 'مدیریت کالاها',
    'products_edit' => 'ويرايش کالاها',
    'products_delete' => 'حذف کالاها',

    'persons_manage' => 'مدیریت اشخاص',
    'persons_edit' => 'ويرايش اشخاص',
    'persons_delete' => 'حذف اشخاص',

    'users_manage' => 'مدیریت کاربران',
    'users_edit' => 'ويرايش کاربران',
    'users_delete' => 'حذف کاربران',

    'services_manage' => 'ثبت خدمات',
    'services_view' => ' مشاهده خدمات',
    'services_edit' => 'ويرايش خدمات',
    'services_delete' => 'حذف خدمات',

    // دسترسی‌های فاکتور
    'invoices_view' => ' مشاهده فاکتورها',
    'invoices_manage' => 'مدیریت فاکتورها',
    'invoices_edit' => ' ویرایش فاکتور',
    'invoices_delete' => ' حذف فاکتور',

    // دسترسی‌های کالا
    'goods_view' => ' مشاهده کالاها',
    'goods_manage' => 'مدیریت کالاها',
    'goods_edit' => ' ویرایش کالا',
    'goods_delete' => ' حذف کالا',

    // دسترسی‌های پرینتر
    'printers_view' => ' مشاهده پرینترها',
    'printers_manage' => 'مدیریت پرینترها',
    'printers_edit' => ' ویرایش پرینتر',
    'printers_delete' => ' حذف پرینتر',

    // دسترسی‌های سیستم
    'systems_view' => ' مشاهده سیستم‌ها',
    'systems_manage' => 'مدیریت سیستم‌ها',
    'systems_edit' => ' ویرایش سیستم',
    'systems_delete' => ' حذف سیستم',

    'reports_view' => ' مشاهده گزارشات',
];

// حذف کاربر
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    if ($user_id == $_SESSION['user_id']) {
        $errorMessage = "❌ نمی‌توانید خودتان را حذف کنید!";
    } else {
        $updateTickets = $db->prepare("UPDATE tickets SET user_id = NULL WHERE user_id = :user_id");
        $updateTickets->execute([':user_id' => $user_id]);

        $deleteStmt = $db->prepare("DELETE FROM users WHERE id = :id");
        if ($deleteStmt->execute([':id' => $user_id])) {
            $delPermStmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = :user_id");
            $delPermStmt->execute([':user_id' => $user_id]);
            $successMessage = "✅ کاربر با موفقیت حذف شد";
        } else {
            $errorMessage = "❌ خطا در حذف کاربر";
        }
    }
    header('Location: admin_users.php');
    exit;
}

// ویرایش کاربر
if (isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);
    $fullname = htmlspecialchars($_POST['fullname']);
    $role = $_POST['role'];

    if ($user_id == $_SESSION['user_id']) {
        $stmt = $db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $currentRole = $stmt->fetchColumn();
        $role = $currentRole;
    }

    $checkStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id != :id");
    $checkStmt->execute([':username' => $username, ':id' => $user_id]);
    $exists = $checkStmt->fetchColumn();

    if ($exists) {
        $errorMessage = "❌ این نام کاربری قبلاً توسط کاربر دیگری استفاده شده است";
    } else {
        $updateStmt = $db->prepare("UPDATE users SET username = :username, fullname = :fullname, role = :role WHERE id = :id");
        if ($updateStmt->execute([':username' => $username, ':fullname' => $fullname, ':role' => $role, ':id' => $user_id])) {

            $delPermStmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = :user_id");
            $delPermStmt->execute([':user_id' => $user_id]);

            if ($role === 'admin') {
                $allPermissions = $db->query("SELECT DISTINCT permission_key FROM permissions WHERE role = 'admin' ORDER BY permission_key")->fetchAll();
                $insertPermStmt = $db->prepare("INSERT INTO user_permissions (user_id, permission_key, permission_value) VALUES (:user_id, :permission_key, :permission_value)");
                foreach ($allPermissions as $perm) {
                    $permKey = $perm['permission_key'];
                    $value = isset($_POST['perm_' . $permKey]) ? 1 : 0;
                    $insertPermStmt->execute([':user_id' => $user_id, ':permission_key' => $permKey, ':permission_value' => $value]);
                }
            }

            $successMessage = "✅ کاربر با موفقیت ویرایش شد";
        } else {
            $errorMessage = "❌ خطا در ویرایش کاربر";
        }
    }
    header('Location: admin_users.php');
    exit;
}

// تغییر رمز عبور
if (isset($_POST['change_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];

    if (empty($new_password)) {
        $errorMessage = "❌ رمز عبور نمی‌تواند خالی باشد";
    } else {
        $updateStmt = $db->prepare("UPDATE users SET password = MD5(:password) WHERE id = :id");
        if ($updateStmt->execute([':password' => $new_password, ':id' => $user_id])) {
            $successMessage = "✅ رمز عبور با موفقیت تغییر کرد";
        } else {
            $errorMessage = "❌ خطا در تغییر رمز عبور";
        }
    }
    header('Location: admin_users.php');
    exit;
}

// افزودن کاربر جدید
if (isset($_POST['add_user'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $fullname = htmlspecialchars($_POST['fullname']);
    $role = $_POST['role'];

    if (empty($username) || empty($fullname)) {
        $errorMessage = "❌ نام کاربری و نام کامل الزامی هستند";
    } elseif (empty($password)) {
        $errorMessage = "❌ رمز عبور الزامی است";
    } else {
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $checkStmt->execute([':username' => $username]);
        $exists = $checkStmt->fetchColumn();

        if ($exists) {
            $errorMessage = "❌ این نام کاربری قبلاً ثبت شده است";
        } else {
            $insertStmt = $db->prepare("INSERT INTO users (username, password, fullname, role, created_at) VALUES (:username, MD5(:password), :fullname, :role, :created_at)");
            $jalaliDate = now();
            if ($insertStmt->execute([':username' => $username, ':password' => $password, ':fullname' => $fullname, ':role' => $role, ':created_at' => $jalaliDate])) {
                $user_id = $db->lastInsertId();

                // فقط برای ادمین‌ها و فقط دسترسی‌های tickets به صورت پیش‌فرض
                if ($role === 'admin') {
                    // دسترسی‌های پیش‌فرض فقط برای درخواست‌ها (tickets)
                    $defaultPermissions = [
                        'tickets_manage',
                        'tickets_edit',
                        'tickets_delete'
                    ];

                    $insertPermStmt = $db->prepare("INSERT INTO user_permissions (user_id, permission_key, permission_value) VALUES (:user_id, :permission_key, :permission_value)");
                    foreach ($defaultPermissions as $permKey) {
                        $insertPermStmt->execute([
                            ':user_id' => $user_id,
                            ':permission_key' => $permKey,
                            ':permission_value' => 1
                        ]);
                    }
                }

                $successMessage = "✅ کاربر جدید با موفقیت اضافه شد";
            } else {
                $errorMessage = "❌ خطا در افزودن کاربر: " . implode(", ", $insertStmt->errorInfo());
            }
        }
    }
    header('Location: admin_users.php');
    exit;
}

// گرفتن لیست کاربران
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

// گرفتن دسترسی‌های هر کاربر
$userPermissions = [];
foreach ($users as $user) {
    $stmt = $db->prepare("SELECT permission_key, permission_value FROM user_permissions WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user['id']]);
    $perms = $stmt->fetchAll();
    foreach ($perms as $perm) {
        $userPermissions[$user['id']][$perm['permission_key']] = $perm['permission_value'];
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت کاربران - پنل ادمین</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/sidebar.css">
    <link rel="stylesheet" href="styles/admin-users.css">
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
                <span class="clock-display" id="liveClock">📅 <?php echo fa_number(now()); ?></span>
                <a href="logout.php" class="logout-btn-sidebar">🚪 خروج</a>
            </div>
        </div>

        <div class="main-title">
            <h1>👥 مدیریت کاربران</h1>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- افزودن کاربر جدید -->
        <div class="add-card">
            <h2>➕ افزودن کاربر جدید</h2>
            <form method="post" class="form-row">
                <div class="form-group">
                    <label>نام کاربری</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>رمز عبور</label>
                    <input type="text" name="password" required>
                </div>
                <div class="form-group">
                    <label>نام کامل</label>
                    <input type="text" name="fullname" required>
                </div>
                <div class="form-group">
                    <label>نقش</label>
                    <select name="role">
                        <option value="user">کاربر عادی</option>
                        <option value="admin">ادمین</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" name="add_user" class="btn-add">➕ افزودن</button>
                </div>
            </form>
        </div>

        <!-- جدول کاربران -->
        <div class="users-table">
            <table>
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>نام کاربری</th>
                    <th>نام کامل</th>
                    <th>نقش</th>
                    <th>تاریخ ثبت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php $row_num = 1; foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo fa_number($row_num); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                        <td>
                            <span class="role-badge <?php echo $user['role'] == 'admin' ? 'role-admin' : 'role-user'; ?>">
                                <?php echo $user['role'] == 'admin' ? 'ادمین' : 'کاربر عادی'; ?>
                            </span>
                        </td>
                        <td class="date"><?php echo fa_number(htmlspecialchars($user['created_at'])); ?></td>
                        <td class="action-buttons">
                            <button class="edit-btn" onclick='openEditModal(<?php echo $user['id']; ?>, "<?php echo htmlspecialchars($user['username']); ?>", "<?php echo htmlspecialchars($user['fullname']); ?>", "<?php echo $user['role']; ?>", <?php echo isset($_SESSION['limited_admin']) && $_SESSION['limited_admin'] === true ? 'true' : 'false'; ?>)'>ویرایش</button>
                            <button class="password-btn" onclick="openPasswordModal(<?php echo $user['id']; ?>)">تغییر رمز</button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button class="delete-btn" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">حذف</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php $row_num++; endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- مودال ویرایش کاربر -->
<!-- مودال ویرایش کاربر -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ ویرایش کاربر</h3>
        <form method="post">
            <input type="hidden" name="user_id" id="edit_user_id">

            <!-- بخش ثابت (اینپوت‌ها) -->
            <div class="modal-fixed-fields">
                <div class="form-row">
                    <div class="form-group">
                        <label>نام کاربری</label>
                        <input type="text" name="username" id="edit_username" required>
                    </div>
                    <div class="form-group">
                        <label>نام کامل</label>
                        <input type="text" name="fullname" id="edit_fullname" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>نقش</label>
                        <select name="role" id="edit_role">
                            <option value="user">کاربر عادی</option>
                            <option value="admin">ادمین</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- بخش اسکرول دار (چک لیست دسترسی‌ها) -->
            <div id="edit_access_section" class="access-section" style="display: none;">
                <div class="access-title">🔐 دسترسی‌های مدیریتی:</div>
                <div class="access-scroll-area">
                    <div id="edit_permissions_grid" class="access-tree"></div>
                </div>
            </div>

            <div class="modal-buttons">
                <button type="submit" name="edit_user" class="modal-save">💾 ذخیره</button>
                <button type="button" class="modal-cancel" onclick="closeModal('editModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<!-- مودال تغییر رمز عبور -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <h3>تغییر رمز عبور</h3>
        <form method="post">
            <input type="hidden" name="user_id" id="password_user_id">
            <label>رمز عبور جدید</label>
            <input type="text" name="new_password" required>
            <div class="modal-buttons">
                <button type="submit" name="change_password" class="modal-save">ذخیره</button>
                <button type="button" class="modal-cancel" onclick="closeModal('passwordModal')">لغو</button>
            </div>
        </form>
    </div>
</div>

<script>
    var allPermissions = <?php echo json_encode(array_column($allPermissions, 'permission_key')); ?>;
    var permissionLabels = <?php echo json_encode($permissionLabels); ?>;
    var existingPermissions = <?php
        $permsData = [];
        foreach ($users as $user) {
            $permsData[$user['id']] = $userPermissions[$user['id']] ?? [];
        }
        echo json_encode($permsData);
        ?>;

    var permissionTree = [
        { name: 'مدیریت درخواست‌ها', key: 'tickets', children: [
                { name: '✏️ ویرایش درخواست', key: 'tickets_edit' },
                { name: '🗑️ حذف درخواست', key: 'tickets_delete' }
            ] },

        { name: 'تعریف خدمات', key: 'activities', children: [
                { name: '✏️ ویرایش خدمت', key: 'activities_edit' },
                { name: '🗑️ حذف خدمت', key: 'activities_delete' }
            ] },

        { name: 'تعریف بخش‌ها', key: 'departments', children: [
                { name: '✏️ ویرایش بخش', key: 'departments_edit' },
                { name: '🗑️ حذف بخش', key: 'departments_delete' }
            ] },

        { name: 'تعریف موضوع', key: 'topics', children: [
                { name: '✏️ ویرایش موضوع', key: 'topics_edit' },
                { name: '🗑️ حذف موضوع', key: 'topics_delete' }
            ] },

        { name: 'تعریف برندها', key: 'brands', children: [
                { name: '✏️ ویرایش برند', key: 'brands_edit' },
                { name: '🗑️ حذف برند', key: 'brands_delete' }
            ] },

        { name: 'تعریف مدل‌ها', key: 'models', children: [
                { name: '✏️ ویرایش مدل', key: 'models_edit' },
                { name: '🗑️ حذف مدل', key: 'models_delete' }
            ] },

        { name: 'تعریف کالاها', key: 'products', children: [
                { name: '✏️ ویرایش کالا', key: 'products_edit' },
                { name: '🗑️ حذف کالا', key: 'products_delete' }
            ] },

        { name: 'تعریف اشخاص', key: 'persons', children: [
                { name: '✏️ ویرایش شخص', key: 'persons_edit' },
                { name: '🗑️ حذف شخص', key: 'persons_delete' }
            ] },

        { name: 'مدیریت کاربران', key: 'users', children: [
                { name: '✏️ ویرایش کاربر', key: 'users_edit' },
                { name: '🗑️ حذف کاربر', key: 'users_delete' }
            ] },

        { name: 'ثبت خدمات', key: 'services', children: [
                { name: '👁️ مشاهده خدمات', key: 'services_view' },
                { name: '✏️ ویرایش خدمات', key: 'services_edit' },
                { name: '🗑️ حذف خدمات', key: 'services_delete' }
            ] },

        {name: 'مدیریت فاکتورها', key: 'invoices', children: [
                { name: '👁️ مشاهده فاکتورها', key: 'invoices_view' },
                { name: '✏️ ویرایش فاکتور', key: 'invoices_edit' },
                { name: '🗑️ حذف فاکتور', key: 'invoices_delete' }
            ]},

        {name: 'مدیریت کالاها', key: 'goods', children: [
                { name: '👁️ مشاهده کالاها', key: 'goods_view' },
                { name: '✏️ ویرایش کالا', key: 'goods_edit' },
                { name: '🗑️ حذف کالا', key: 'goods_delete' }
            ]},

        {name: 'مدیریت پرینترها', key: 'printers', children: [
                { name: '👁️ مشاهده پرینترها', key: 'printers_view' },
                { name: '✏️ ویرایش پرینتر', key: 'printers_edit' },
                { name: '🗑️ حذف پرینتر', key: 'printers_delete' }
            ]},

        {name: 'مدیریت سیستم‌ها', key: 'systems', children: [
                { name: '👁️ مشاهده سیستم‌ها', key: 'systems_view' },
                { name: '✏️ ویرایش سیستم', key: 'systems_edit' },
                { name: '🗑️ حذف سیستم', key: 'systems_delete' }
            ]},

        {name: 'گزارشات', key: 'reports', children: [
                { name: '👁️ مشاهده گزارشات', key: 'reports_view' }]
        },
    ];

    function buildPermissionTree(containerId, existingPermissions) {
        var container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';
        for (var i = 0; i < permissionTree.length; i++) {
            var node = permissionTree[i];
            var allChildrenChecked = true;
            var anyChildChecked = false;
            for (var j = 0; j < node.children.length; j++) {
                var childKey = node.children[j].key;
                if (existingPermissions[childKey] !== 1) allChildrenChecked = false;
                else anyChildChecked = true;
            }
            var mainChecked = allChildrenChecked;
            var mainIndeterminate = (!allChildrenChecked && anyChildChecked);
            var nodeHtml = '<div class="tree-node" data-parent-key="' + node.key + '">';
            nodeHtml += '<div class="tree-node-main">';
            nodeHtml += '<span class="tree-toggle" onclick="toggleTree(this)">▼</span>';
            nodeHtml += '<input type="checkbox" name="perm_' + node.key + '_manage" id="perm_' + node.key + '_manage" value="1" ' + (mainChecked ? 'checked' : '') + ' onchange="toggleChildren(this)">';
            nodeHtml += '<label for="perm_' + node.key + '_manage">' + node.name + '</label>';
            nodeHtml += '</div>';
            nodeHtml += '<div class="tree-children" id="children_' + node.key + '">';
            for (var j = 0; j < node.children.length; j++) {
                var child = node.children[j];
                var childChecked = (existingPermissions[child.key] === 1);
                nodeHtml += '<div class="tree-child" data-child-key="' + child.key + '">';
                nodeHtml += '<input type="checkbox" name="perm_' + child.key + '" id="perm_' + child.key + '" value="1" ' + (childChecked ? 'checked' : '') + ' onchange="updateParent(this)">';
                nodeHtml += '<label for="perm_' + child.key + '">' + child.name + '</label>';
                nodeHtml += '</div>';
            }
            nodeHtml += '</div></div>';
            container.innerHTML += nodeHtml;
            var mainCheckbox = document.getElementById('perm_' + node.key + '_manage');
            if (mainCheckbox && mainIndeterminate) mainCheckbox.indeterminate = true;
        }
    }

    function toggleTree(element) {
        var childrenDiv = element.parentElement.nextElementSibling;
        if (childrenDiv) {
            childrenDiv.classList.toggle('show');
            element.textContent = childrenDiv.classList.contains('show') ? '▼' : '▶';
        }
    }

    function toggleChildren(mainCheckbox) {
        var parentDiv = mainCheckbox.closest('.tree-node');
        var childrenDiv = parentDiv.querySelector('.tree-children');
        var childCheckboxes = childrenDiv.querySelectorAll('input[type="checkbox"]');
        for (var i = 0; i < childCheckboxes.length; i++) {
            childCheckboxes[i].checked = mainCheckbox.checked;
        }
    }

    function updateParent(childCheckbox) {
        var parentDiv = childCheckbox.closest('.tree-node');
        var mainCheckbox = parentDiv.querySelector('.tree-node-main input[type="checkbox"]');
        var childCheckboxes = parentDiv.querySelectorAll('.tree-children input[type="checkbox"]');
        var allChecked = true, anyChecked = false;
        for (var i = 0; i < childCheckboxes.length; i++) {
            if (!childCheckboxes[i].checked) allChecked = false;
            if (childCheckboxes[i].checked) anyChecked = true;
        }
        if (allChecked) {
            mainCheckbox.checked = true;
            mainCheckbox.indeterminate = false;
        } else if (anyChecked) {
            mainCheckbox.checked = false;
            mainCheckbox.indeterminate = true;
        } else {
            mainCheckbox.checked = false;
            mainCheckbox.indeterminate = false;
        }
    }

    function openEditModal(id, username, fullname, role, isLimitedAdmin) {
        var currentUserId = <?php echo $_SESSION['user_id']; ?>;
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_fullname').value = fullname;
        var roleSelectElem = document.getElementById('edit_role');
        roleSelectElem.value = role;
        if (id == currentUserId) {
            roleSelectElem.disabled = true;
            roleSelectElem.style.opacity = '0.6';
            roleSelectElem.style.cursor = 'not-allowed';
        } else {
            roleSelectElem.disabled = false;
            roleSelectElem.style.opacity = '1';
            roleSelectElem.style.cursor = 'pointer';
        }
        var accessSection = document.getElementById('edit_access_section');
        if (role === 'admin') {
            accessSection.style.display = 'block';
            var userPerms = existingPermissions[id] || {};
            buildPermissionTree('edit_permissions_grid', userPerms);
            var allToggles = document.querySelectorAll('#edit_permissions_grid .tree-toggle');
            for (var i = 0; i < allToggles.length; i++) {
                var childrenDiv = allToggles[i].parentElement.nextElementSibling;
                if (childrenDiv) {
                    childrenDiv.classList.add('show');
                    allToggles[i].textContent = '▼';
                }
            }
            var isSelf = (id == currentUserId);
            if (isSelf) {
                var allCheckboxes = accessSection.querySelectorAll('input[type="checkbox"]');
                for (var i = 0; i < allCheckboxes.length; i++) {
                    allCheckboxes[i].disabled = true;
                    allCheckboxes[i].checked = true;
                }
            }
        } else {
            accessSection.style.display = 'none';
        }
        document.getElementById('editModal').style.display = 'flex';
    }

    var editRoleSelect = document.getElementById('edit_role');
    if (editRoleSelect) {
        editRoleSelect.addEventListener('change', function() {
            var accessSection = document.getElementById('edit_access_section');
            var userId = document.getElementById('edit_user_id').value;
            var currentUserId = <?php echo $_SESSION['user_id']; ?>;
            if (this.value === 'admin') {
                accessSection.style.display = 'block';
                var userPerms = existingPermissions[userId] || {};
                buildPermissionTree('edit_permissions_grid', userPerms);
                var allToggles = document.querySelectorAll('#edit_permissions_grid .tree-toggle');
                for (var i = 0; i < allToggles.length; i++) {
                    var childrenDiv = allToggles[i].parentElement.nextElementSibling;
                    if (childrenDiv) {
                        childrenDiv.classList.add('show');
                        allToggles[i].textContent = '▼';
                    }
                }
                var isSelf = (userId == currentUserId);
                if (isSelf) {
                    var allCheckboxes = accessSection.querySelectorAll('input[type="checkbox"]');
                    for (var i = 0; i < allCheckboxes.length; i++) {
                        allCheckboxes[i].disabled = true;
                        allCheckboxes[i].checked = true;
                    }
                }
            } else {
                accessSection.style.display = 'none';
            }
        });
    }

    function openPasswordModal(id) {
        document.getElementById('password_user_id').value = id;
        document.getElementById('passwordModal').style.display = 'flex';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    function confirmDelete(id, username) {
        if (confirm('آیا از حذف کاربر "' + username + '" مطمئن هستید؟')) {
            var form = document.createElement('form');
            form.method = 'post';
            form.innerHTML = '<input type="hidden" name="delete_user" value="1"><input type="hidden" name="user_id" value="' + id + '">';
            document.body.appendChild(form);
            form.submit();
        }
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