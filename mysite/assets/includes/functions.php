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
function canManageTickets() { return hasPermission('tickets_view'); }
function canEditTickets() { return hasPermission('tickets_edit'); }
function canDeleteTickets() { return hasPermission('tickets_delete'); }
function canViewTickets() { return canManageTickets() || canEditTickets() || canDeleteTickets(); }

// ============================================
// دسترسی‌های فعالیت
// ============================================
function canManageActivities() { return hasPermission('activities_view'); }
function canEditActivities() { return hasPermission('activities_edit'); }
function canDeleteActivities() { return hasPermission('activities_delete'); }
function canViewActivities() { return canManageActivities() || canEditActivities() || canDeleteActivities(); }

// ============================================
// بخش‌ها
// ============================================
function canManageDepartments() { return hasPermission('departments_view'); }
function canEditDepartments() { return hasPermission('departments_edit'); }
function canDeleteDepartments() { return hasPermission('departments_delete'); }
function canViewDepartments() { return canManageDepartments() || canEditDepartments() || canDeleteDepartments(); }

// ============================================
// موضوعات
// ============================================
function canManageTopics() { return hasPermission('topics_view'); }
function canEditTopics() { return hasPermission('topics_edit'); }
function canDeleteTopics() { return hasPermission('topics_delete'); }
function canViewTopics() { return canManageTopics() || canEditTopics() || canDeleteTopics(); }

// ============================================
// برندها
// ============================================
function canManageBrands() { return hasPermission('brands_view'); }
function canEditBrands() { return hasPermission('brands_edit'); }
function canDeleteBrands() { return hasPermission('brands_delete'); }
function canViewBrands() { return canManageBrands() || canEditBrands() || canDeleteBrands(); }

// ============================================
// دسترسی‌های مدل‌ها
// ============================================
function canManageModels() { return hasPermission('models_view'); }
function canEditModels() { return hasPermission('models_edit'); }
function canDeleteModels() { return hasPermission('models_delete'); }
function canViewModels() { return canManageModels() || canEditModels() || canDeleteModels(); }

// ============================================
// کالاها
// ============================================
function canManageProducts() { return hasPermission('products_view'); }
function canEditProducts() { return hasPermission('products_edit'); }
function canDeleteProducts() { return hasPermission('products_delete'); }
function canViewProducts() { return canManageProducts() || canEditProducts() || canDeleteProducts(); }

// ============================================
// دسترسی‌های اشخاص
// ============================================
function canManagePersons() { return hasPermission('persons_view'); }
function canEditPersons() { return hasPermission('persons_edit'); }
function canDeletePersons() { return hasPermission('persons_delete'); }
function canViewPersons() { return canManagePersons() || canEditPersons() || canDeletePersons(); }

// ============================================
// کاربران
// ============================================
function canManageUsers() { return hasPermission('users_view'); }
function canEditUsers() { return hasPermission('users_edit'); }
function canDeleteUsers() { return hasPermission('users_delete'); }
function canViewUsers() { return canManageUsers() || canEditUsers() || canDeleteUsers(); }




// ============================================
// دسترسی‌های خدمات
// ============================================
// دسترسی مشاهده خدمات (برای دیدن صفحه و جدول)
function canViewServices() { return hasPermission('services_view'); }
// دسترسی ویرایش خدمات
function canEditServices() { return hasPermission('services_edit'); }
// دسترسی حذف خدمات
function canDeleteServices() { return hasPermission('services_delete'); }
// تابع کمکی برای بررسی هرگونه دسترسی به خدمات (اختیاری)
function hasAnyServiceAccess() {
    return canViewServices() || canEditServices() || canDeleteServices();
}

// ============================================
// دسترسی‌های فاکتور
// ============================================
function canViewInvoices() { return hasPermission('invoices_view'); }
function canEditInvoices() { return hasPermission('invoices_edit'); }
function canDeleteInvoices() { return hasPermission('invoices_delete'); }

// ============================================
// دسترسی‌های کالا
// ============================================
function canViewkala() { return hasPermission('kala_view'); }
function canEditkala() { return hasPermission('kala_edit'); }
function canDeletekala() { return hasPermission('kala_delete'); }

// ============================================
// دسترسی‌های پرینتر
// ============================================
function canViewPrinters() { return hasPermission('printers_view'); }
function canEditPrinters() { return hasPermission('printers_edit'); }
function canDeletePrinters() { return hasPermission('printers_delete'); }

// ============================================
// دسترسی‌های سیستم
// ============================================
function canViewSystems() { return hasPermission('systems_view'); }
function canEditSystems() { return hasPermission('systems_edit'); }
function canDeleteSystems() { return hasPermission('systems_delete'); }

// ============================================
// دسترسی‌های گزارشات
// ============================================
function canViewReports() {return hasPermission('reports_view');}

// ============================================
// دسترسی پشتیبان‌گیری
// ============================================
function canViewBackup() {
    return hasPermission('backup_view');
}
?>