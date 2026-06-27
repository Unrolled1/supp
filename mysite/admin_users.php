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

if (!isAdmin() || !canManageUsers()) {
    header('Location: requests.php');
    exit;
}

$db = getDB();
$successMessage = '';
$errorMessage = '';

// گرفتن لیست دسترسی‌ها
$allPermissions = $db->query("SELECT DISTINCT permission_key FROM permissions WHERE role = 'admin' ORDER BY permission_key")->fetchAll();

$permissionLabels = [
    'tickets_view' => 'مدیریت درخواست ها',
    'tickets_edit' => 'ويرايش درخواست ها',
    'tickets_delete' => 'حذف درخواست ها',

    'activities_view' => 'مدیریت فعالیت',
    'activities_edit' => 'ويرايش فعالیت',
    'activities_delete' => 'حذف فعالیت',

    'departments_view' => 'مدیریت بخش ها',
    'departments_edit' => 'ويرايش بخش ها',
    'departments_delete' => 'حذف بخش ها',

    'topics_view' => 'مدیریت موضوعات',
    'topics_edit' => 'ويرايش موضوعات',
    'topics_delete' => 'حذف موضوعات',

    'brands_view' => 'مدیریت برندها',
    'brands_edit' => 'ويرايش برندها',
    'brands_delete' => 'حذف برندها',

    'models_view' => 'مدیریت مدل‌ها',
    'models_edit' => 'ويرايش مدل‌ها',
    'models_delete' => 'حذف مدل‌ها',

    'products_view' => 'مدیریت کالاها',
    'products_edit' => 'ويرايش کالاها',
    'products_delete' => 'حذف کالاها',

    'persons_view' => 'مدیریت اشخاص',
    'persons_edit' => 'ويرايش اشخاص',
    'persons_delete' => 'حذف اشخاص',

    'users_view' => 'مدیریت کاربران',
    'users_edit' => 'ويرايش کاربران',
    'users_delete' => 'حذف کاربران',


    'services_view' => ' مشاهده فعالیت',
    'services_edit' => 'ويرايش فعالیت',
    'services_delete' => 'حذف فعالیت',

    // دسترسی‌های فاکتور
    'invoices_view' => ' مشاهده فاکتورها',
    'invoices_edit' => ' ویرایش فاکتور',
    'invoices_delete' => ' حذف فاکتور',

    // دسترسی‌های کالا
    'kala_view' => ' مشاهده کالاها',
    'kala_edit' => ' ویرایش کالا',
    'kala_delete' => ' حذف کالا',

    // دسترسی‌های پرینتر
    'printers_view' => ' مشاهده پرینترها',
    'printers_edit' => ' ویرایش پرینتر',
    'printers_delete' => ' حذف پرینتر',

    // دسترسی‌های سیستم
    'systems_view' => ' مشاهده سیستم‌ها',
    'systems_edit' => ' ویرایش سیستم',
    'systems_delete' => ' حذف سیستم',

    'reports_view' => ' مشاهده گزارشات',
];

// ============================================
// تعریف درخت دسترسی‌ها بر اساس منوها
// ============================================

$permissionTree = [
    // ============================================
    // گروه: تعاریف
    // ============================================
    [
        'name' => '📋 تعاریف',
        'key' => 'definitions',
        'children' => [
            // فعالیت
            [
                'name' => '🛠️ فعالیت',
                'key' => 'activities',
                'children' => [
                    ['name' => '👁️ مشاهده خدمات', 'key' => 'activities_view'],
                    ['name' => '✏️ ویرایش خدمات', 'key' => 'activities_edit'],
                    ['name' => '🗑️ حذف خدمات', 'key' => 'activities_delete'],
                ]
            ],
            //بخش
            [
                'name' => '🛠️ بخش',
                'key' => 'departments',
                'children' => [
                    ['name' => '👁️ مشاهده بخش', 'key' => 'departments_view'],
                    ['name' => '✏️ ویرایش بخش', 'key' => 'departments_edit'],
                    ['name' => '🗑️ حذف بخش', 'key' => 'departments_delete'],
                ]
            ],

            // موضوعات
            [
                'name' => '📝 موضوعات',
                'key' => 'topics',
                'children' => [
                    ['name' => '✏️ مشاهده موضوع', 'key' => 'topics_view'],
                    ['name' => '✏️ ویرایش موضوع', 'key' => 'topics_edit'],
                    ['name' => '🗑️ حذف موضوع', 'key' => 'topics_delete']
                ]
            ],

            // برندها
            [
                'name' => '🏷️ برندها',
                'key' => 'brands',
                'children' => [
                    ['name' => '✏️ مشاهده برند', 'key' => 'brands_view'],
                    ['name' => '✏️ ویرایش برند', 'key' => 'brands_edit'],
                    ['name' => '🗑️ حذف برند', 'key' => 'brands_delete']
                ]
            ],
            // مدل‌ها
            [
                'name' => '📱 مدل‌ها',
                'key' => 'models',
                'children' => [
                    ['name' => '✏️ مشاهده مدل', 'key' => 'models_view'],
                    ['name' => '✏️ ویرایش مدل', 'key' => 'models_edit'],
                    ['name' => '🗑️ حذف مدل', 'key' => 'models_delete']
                ]
            ],
            // کالاها
            [
                'name' => '📦 کالاها',
                'key' => 'products',
                'children' => [
                    ['name' => '✏️ مشاهده کالا', 'key' => 'products_view'],
                    ['name' => '✏️ ویرایش کالا', 'key' => 'products_edit'],
                    ['name' => '🗑️ حذف کالا', 'key' => 'products_delete']
                ]
            ],
            // اشخاص
            [
                'name' => '👤 اشخاص',
                'key' => 'persons',
                'children' => [
                    ['name' => '✏️ مشاهده شخص', 'key' => 'persons_view'],
                    ['name' => '✏️ ویرایش شخص', 'key' => 'persons_edit'],
                    ['name' => '🗑️ حذف شخص', 'key' => 'persons_delete']
                ]
            ],
        ]
    ],

    // ============================================
    // گروه: عملیات
    // ============================================
    [
        'name' => '⚙️ عملیات',
        'key' => 'operations',
        'children' => [
            // درخواست‌ها
            [
                'name' => '📩 درخواست‌ها',
                'key' => 'tickets',
                'children' => [
                    ['name' => '✏️ مشاهده درخواست', 'key' => 'tickets_view'],
                    ['name' => '✏️ ویرایش درخواست', 'key' => 'tickets_edit'],
                    ['name' => '🗑️ حذف درخواست', 'key' => 'tickets_delete']
                ]
            ],
            //ثبت فعالیت
            [
                'name' => '🧾 ثبت فعالیت',
                'key' => 'services',
                'children' => [
                    ['name' => '👁️ مشاهده فاکتورها', 'key' => 'invoices_view'],
                    ['name' => '✏️ ویرایش فاکتور', 'key' => 'invoices_edit'],
                    ['name' => '🗑️ حذف فاکتور', 'key' => 'invoices_delete']
                ]
            ],
            // فاکتورها
            [
                'name' => '🧾 فاکتورها',
                'key' => 'invoices',
                'children' => [
                    ['name' => '👁️ مشاهده فاکتورها', 'key' => 'invoices_view'],
                    ['name' => '✏️ ویرایش فاکتور', 'key' => 'invoices_edit'],
                    ['name' => '🗑️ حذف فاکتور', 'key' => 'invoices_delete']
                ]
            ],
            // کالاها
            [
                'name' => '📦 ثبت کالاها',
                'key' => 'goods',
                'children' => [
                    ['name' => '👁️ مشاهده کالاها', 'key' => 'kala_view'],
                    ['name' => '✏️ ویرایش کالا', 'key' => 'kala_edit'],
                    ['name' => '🗑️ حذف کالا', 'key' => 'kala_delete']
                ]
            ],
            // پرینترها
            [
                'name' => '🖨️ پرینترها',
                'key' => 'printers',
                'children' => [
                    ['name' => '👁️ مشاهده پرینترها', 'key' => 'printers_view'],
                    ['name' => '✏️ ویرایش پرینتر', 'key' => 'printers_edit'],
                    ['name' => '🗑️ حذف پرینتر', 'key' => 'printers_delete']
                ]
            ],
            // سیستم‌ها
            [
                'name' => '💻 سیستم‌ها',
                'key' => 'systems',
                'children' => [
                    ['name' => '👁️ مشاهده سیستم‌ها', 'key' => 'systems_view'],
                    ['name' => '✏️ ویرایش سیستم', 'key' => 'systems_edit'],
                    ['name' => '🗑️ حذف سیستم', 'key' => 'systems_delete']
                ]
            ],

        ]
    ],

    // ============================================
    // گروه: کاربران
    // ============================================
    [
        'name' => '👥 کاربران',
        'key' => 'users_management',
        'children' => [
            [
                'name' => '👤 مدیریت کاربران',
                'key' => 'users',
                'children' => [
                    ['name' => '✏️ مشاهده کاربر', 'key' => 'users_view'],
                    ['name' => '✏️ ویرایش کاربر', 'key' => 'users_edit'],
                    ['name' => '🗑️ حذف کاربر', 'key' => 'users_delete']
                ]
            ]
        ]
    ],

    // ============================================
    // گروه: گزارشات
    // ============================================
    [
        'name' => '📊 گزارشات',
        'key' => 'reports_section',
        'children' => [
            [
                'name' => '📈 مشاهده گزارشات',
                'key' => 'reports',
                'children' => [
                    ['name' => '👁️ مشاهده گزارشات', 'key' => 'reports_view']
                ]
            ]
        ]
    ]
];

// ============================================
// حذف کاربر با Ajax
// ============================================

if (isset($_POST['delete_user'])) {
    header('Content-Type: application/json');

    $user_id = $_POST['user_id'];

    // جلوگیری از حذف خود کاربر
    if ($user_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => '❌ نمی‌توانید خودتان را حذف کنید!']);
        exit;
    }

    try {
        // به‌روزرسانی تیکت‌ها
        $updateTickets = $db->prepare("UPDATE tickets SET user_id = NULL WHERE user_id = :user_id");
        $updateTickets->execute([':user_id' => $user_id]);

        // حذف کاربر
        $deleteStmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $success = $deleteStmt->execute([':id' => $user_id]);

        if ($success) {
            // حذف دسترسی‌های کاربر
            $delPermStmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = :user_id");
            $delPermStmt->execute([':user_id' => $user_id]);

            echo json_encode([
                'success' => true,
                'id' => $user_id,
                'message' => 'کاربر با موفقیت حذف شد'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'خطا در حذف کاربر']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'خطای سرور: ' . $e->getMessage()]);
    }
    exit;
}
// ============================================
// ویرایش کاربر با Ajax
// ============================================

if (isset($_POST['edit_user'])) {
    header('Content-Type: application/json');

    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);
    $fullname = htmlspecialchars($_POST['fullname']);
    $role = $_POST['role'];
    $permissions = json_decode($_POST['permissions'], true);

    try {
        // بررسی نام کاربری تکراری
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id != :id");
        $checkStmt->execute([':username' => $username, ':id' => $user_id]);
        $exists = $checkStmt->fetchColumn();

        if ($exists) {
            echo json_encode(['success' => false, 'message' => 'این نام کاربری قبلاً استفاده شده است']);
            exit;
        }

        // بروزرسانی کاربر
        $updateStmt = $db->prepare("UPDATE users SET username = :username, fullname = :fullname, role = :role WHERE id = :id");
        $success = $updateStmt->execute([':username' => $username, ':fullname' => $fullname, ':role' => $role, ':id' => $user_id]);

        if ($success) {
            // حذف دسترسی‌های قبلی
            $delPermStmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = :user_id");
            $delPermStmt->execute([':user_id' => $user_id]);

            // ذخیره دسترسی‌های جدید
            if ($role === 'admin' && !empty($permissions)) {
                $insertPermStmt = $db->prepare("INSERT INTO user_permissions (user_id, permission_key, permission_value) VALUES (:user_id, :permission_key, :permission_value)");
                foreach ($permissions as $key => $value) {
                    $insertPermStmt->execute([':user_id' => $user_id, ':permission_key' => $key, ':permission_value' => $value]);
                }
            }

            echo json_encode([
                'success' => true,
                'id' => $user_id,
                'username' => $username,
                'fullname' => $fullname,
                'role' => $role
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'خطا در بروزرسانی کاربر']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'خطای سرور: ' . $e->getMessage()]);
    }
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
    <title>تعریف کاربران </title>
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
                    <button type="submit" name="add_user" class="btn-add">➕ ثبت</button>
                </div>
            </form>
        </div>

        <!-- جدول کاربران -->
        <div class="users-table data-table">
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
<div id="editModal" class="modal">
    <div class="edit-form">
        <h3>✏️ ویرایش کاربر</h3>
        <form method="post">
            <input type="hidden" name="user_id" id="edit_user_id">
            <input type="hidden" id="current_user_id" value="<?php echo $_SESSION['user_id']; ?>">

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
            <div id="edit_access_section" class="access-section" >
                <div class="access-title">🔐 دسترسی‌ها:</div>
                <div class="access-scroll-area">
                    <div id="edit_permissions_grid" class="access-tree"></div>
                </div>
            </div>

            <div class="modal-buttons">
                <button type="button"  class="btn-add" onclick="saveusersEdit()">💾 ذخیره</button>
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">لغو</button>
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
                <button type="submit" name="change_password" class="btn-add">ذخیره</button>
                <button type="button" class="btn-cancel" onclick="closeModal('passwordModal')">لغو</button>
            </div>
        </form>
    </div>
</div>
<script>
// داده‌های کاربران و دسترسی‌ها
window.userData = {
currentUserId: <?php echo json_encode($_SESSION['user_id']); ?>,
allPermissions: <?php echo json_encode(array_column($allPermissions, 'permission_key')); ?>,
permissionLabels: <?php echo json_encode($permissionLabels); ?>,
existingPermissions: <?php echo json_encode($userPermissions); ?>,
permissionTree: <?php echo json_encode($permissionTree); ?>
};
</script>


</body>
</html>