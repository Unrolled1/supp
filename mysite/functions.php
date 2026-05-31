<?php
// اطمینان از وجود اتصال دیتابیس
if (!isset($db) || $db === null) {
    require_once __DIR__ . '/db.php';
    $db = getDB();
}

function getPermissions() {
    global $db;

    if (isset($_SESSION['limited_admin']) && isset($_SESSION['permissions'])) {
        return $_SESSION['permissions'];
    }

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        return [];
    }

    if (!isset($_SESSION['limited_admin'])) {
        return [];
    }

    if ($db === null) return [];

    try {
        $stmt = $db->prepare("SELECT permission_key, permission_value FROM user_permissions WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $results = $stmt->fetchAll();

        $permissions = [];
        foreach ($results as $row) {
            $permissions[$row['permission_key']] = $row['permission_value'] == 1;
        }
        return $permissions;
    } catch (PDOException $e) {
        return [];
    }
}

function hasPermission($permission_key) {
    // ادمین اصلی (کاربر شماره 1) همه دسترسی‌ها را دارد
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1) {
        return true;
    }

    // کاربر عادی هیچ دسترسی ندارد
    if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
        return false;
    }

    // اگر limited_admin === false، ادمین کامل است
    if (isset($_SESSION['limited_admin']) && $_SESSION['limited_admin'] === false) {
        return true;
    }

    // اگر limited_admin === true، دسترسی محدود دارد
    if (isset($_SESSION['limited_admin']) && $_SESSION['limited_admin'] === true) {
        $permissions = getPermissions();
        return isset($permissions[$permission_key]) && $permissions[$permission_key] === true;
    }

    // حالت پیش‌فرض (ادمین کامل)
    return true;
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

// ============================================
// درخواست‌ها
// ============================================
function canManageTickets() { return hasPermission('tickets_manage'); }
function canEditTickets() { return hasPermission('tickets_edit'); }
function canDeleteTickets() { return hasPermission('tickets_delete'); }
function canViewTickets() { return canManageTickets() || canEditTickets() || canDeleteTickets(); }

// ============================================
// کاربران
// ============================================
function canManageUsers() { return hasPermission('users_manage'); }
function canEditUsers() { return hasPermission('users_edit'); }
function canDeleteUsers() { return hasPermission('users_delete'); }
function canViewUsers() { return canManageUsers() || canEditUsers() || canDeleteUsers(); }

// ============================================
// بخش‌ها
// ============================================
function canManageDepartments() { return hasPermission('departments_manage'); }
function canEditDepartments() { return hasPermission('departments_edit'); }
function canDeleteDepartments() { return hasPermission('departments_delete'); }
function canViewDepartments() { return canManageDepartments() || canEditDepartments() || canDeleteDepartments(); }

// ============================================
// موضوعات
// ============================================
function canManageTopics() { return hasPermission('topics_manage'); }
function canEditTopics() { return hasPermission('topics_edit'); }
function canDeleteTopics() { return hasPermission('topics_delete'); }
function canViewTopics() { return canManageTopics() || canEditTopics() || canDeleteTopics(); }

// ============================================
// برندها
// ============================================
function canManageBrands() { return hasPermission('brands_manage'); }
function canEditBrands() { return hasPermission('brands_edit'); }
function canDeleteBrands() { return hasPermission('brands_delete'); }
function canViewBrands() { return canManageBrands() || canEditBrands() || canDeleteBrands(); }

// ============================================
// کالاها
// ============================================
function canManageProducts() { return hasPermission('products_manage'); }
function canEditProducts() { return hasPermission('products_edit'); }
function canDeleteProducts() { return hasPermission('products_delete'); }
function canViewProducts() { return canManageProducts() || canEditProducts() || canDeleteProducts(); }

// ============================================
// توابع کمکی برای سایدبار
// ============================================
function canAccessTicketsPage() { return canViewTickets(); }
function canAccessUsersPage() { return canViewUsers(); }
function canAccessDepartmentsPage() { return canViewDepartments(); }
function canAccessTopicsPage() { return canViewTopics(); }
?>