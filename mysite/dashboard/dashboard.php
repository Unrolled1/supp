<?php
session_start();
require_once __DIR__ . '/../assets/includes/autoload.php';

date_default_timezone_set('Asia/Tehran');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isAdmin()) {
    header('Location: user_dashboard.php');
    exit;
}

$db = getDB();

// آمار کلی
$stats = [];

// تعداد درخواست‌ها
$stmt = $db->query("SELECT COUNT(*) as count FROM tickets");
$stats['tickets'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// تعداد درخواست‌های جدید
$stmt = $db->query("SELECT COUNT(*) as count FROM tickets WHERE status = 'جدید'");
$stats['new_tickets'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// تعداد کاربران
$stmt = $db->query("SELECT COUNT(*) as count FROM users");
$stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// تعداد کالاها
$stmt = $db->query("SELECT COUNT(*) as count FROM systems");
$stats['systems'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>داشبورد مدیریت</title>
    <?php load_assets(); ?>
</head>
<body>
<div class="admin-wrapper">

    <div class="main-content">
        <div class="main-header">
            <div class="user-info">
                <span>👨‍💼</span>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
            </div>
            <div>
                <span class="clock-display" id="liveClock"> <?php echo fa_number(now()); ?></span>
                <a href="<?= BASE_URL ?>login/logout.php" class="logout-btn-sidebar">🚪 خروج</a>
            </div>
        </div>

        <div class="main-title">
            <h1>📊 داشبورد مدیریت</h1>
        </div>

        <!-- آمار سریع -->
        <div class="stats-grid">
            <div class="stat-card stat-tickets">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo fa_number($stats['tickets']); ?></span>
                    <span class="stat-label">کل درخواست‌ها</span>
                </div>
            </div>
            <div class="stat-card stat-new">
                <div class="stat-icon">🆕</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo fa_number($stats['new_tickets']); ?></span>
                    <span class="stat-label">درخواست‌های جدید</span>
                </div>
            </div>
            <div class="stat-card stat-users">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo fa_number($stats['users']); ?></span>
                    <span class="stat-label">کاربران</span>
                </div>
            </div>
            <div class="stat-card stat-systems">
                <div class="stat-icon">💻</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo fa_number($stats['systems']); ?></span>
                    <span class="stat-label">سیستم‌ها</span>
                </div>
            </div>
        </div>

        <!-- دسترسی سریع -->
        <div class="quick-access">

            <!-- ردیف اول: تعاریف -->
            <div class="quick-row">
                <h2 class="quick-title">⚙️ تعاریف</h2>
                <div class="quick-grid">
                    <?php if (canViewActivities()): ?>
                        <a href="<?php echo BASE_URL; ?>activities/admin_activities.php" class="quick-card">
                            <span class="icon">📋</span>
                            <span class="label">فعالیت</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewDepartments()): ?>
                        <a href="<?php echo BASE_URL; ?>departments/admin_departments.php" class="quick-card">
                            <span class="icon">🏥</span>
                            <span class="label">بخش‌ها</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewTopics()): ?>
                        <a href="<?php echo BASE_URL; ?>topics/admin_topics.php" class="quick-card">
                            <span class="icon">📋</span>
                            <span class="label">موضوعات</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewBrands()): ?>
                        <a href="<?php echo BASE_URL; ?>brands/admin_brands.php" class="quick-card">
                            <span class="icon">🏷️</span>
                            <span class="label">برندها</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewModels()): ?>
                        <a href="<?php echo BASE_URL; ?>models/admin_models.php" class="quick-card">
                            <span class="icon">📦</span>
                            <span class="label">مدل‌ها</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewProducts()): ?>
                        <a href="<?php echo BASE_URL; ?>products/admin_products.php" class="quick-card">
                            <span class="icon">📦</span>
                            <span class="label">کالاها</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewPersons()): ?>
                        <a href="<?php echo BASE_URL; ?>persons/admin_persons.php" class="quick-card">
                            <span class="icon">👥</span>
                            <span class="label">اشخاص</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewUsers()): ?>
                        <a href="<?php echo BASE_URL; ?>users/admin_users.php" class="quick-card">
                            <span class="icon">👥</span>
                            <span class="label">کاربران</span>
                        </a>
                    <?php endif; ?>

                </div>
            </div>

            <!-- ردیف دوم: عملیات -->
            <div class="quick-row">
                <h2 class="quick-title">📊 عملیات</h2>
                <div class="quick-grid">
                    <?php if (canViewTickets()): ?>

                    <a href="<?php echo BASE_URL; ?>request/requests.php" class="quick-card">
                        <span class="icon">🔧</span>
                        <span class="label">درخواست ها</span>
                    </a>
                    <?php endif; ?>

                    <?php if (canViewServices()): ?>
                        <a href="<?php echo BASE_URL; ?>services/admin_services.php" class="quick-card">
                            <span class="icon">🔧</span>
                            <span class="label">ثبت فعالیت</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewInvoices()): ?>
                        <a href="<?php echo BASE_URL; ?>invoice/admin_invoices.php" class="quick-card">
                            <span class="icon">🧾</span>
                            <span class="label">ثبت فاکتور</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewkala()): ?>
                        <a href="<?php echo BASE_URL; ?>kala/admin_kala.php" class="quick-card">
                            <span class="icon">📦</span>
                            <span class="label">ثبت کالا</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewPrinters()): ?>
                        <a href="<?php echo BASE_URL; ?>printer/admin_printers.php" class="quick-card">
                            <span class="icon">🖨️</span>
                            <span class="label">ثبت پرینتر</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewSystems()): ?>
                        <a href="<?php echo BASE_URL; ?>system/admin_systems.php" class="quick-card">
                            <span class="icon">💻</span>
                            <span class="label">ثبت سیستم</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ردیف سوم: گزارش‌ها -->
            <div class="quick-row">
                <h2 class="quick-title">📈 گزارشات</h2>
                <div class="quick-grid">
                    <?php if (canViewReports()): ?>
                        <a href="<?php echo BASE_URL; ?>ticketrep/admin_ticketrep.php" class="quick-card">
                            <span class="icon">📋</span>
                            <span class="label">گزارش درخواست‌ها</span>
                        </a>

                        <a href="<?php echo BASE_URL; ?>servicerep/admin_servicerep.php" class="quick-card">
                            <span class="icon">📋</span>
                            <span class="label">گزارش فعالیت</span>
                        </a>

                        <a href="<?php echo BASE_URL; ?>invoicerep/admin_invoicerep.php" class="quick-card">
                            <span class="icon">📋</span>
                            <span class="label">گزارش فاکتور</span>
                        </a>

                        <a href="<?php echo BASE_URL; ?>kalarep/admin_kalarep.php" class="quick-card">
                            <span class="icon">📋</span>
                            <span class="label">گزارش کالا</span>
                        </a>

                        <a href="<?php echo BASE_URL; ?>printerrep/admin_printerrep.php" class="quick-card">
                            <span class="icon">📋</span>
                            <span class="label">گزارش پرینتر</span>
                        </a>

                        <a href="<?php echo BASE_URL; ?>systemrep/admin_systemrep.php" class="quick-card">
                            <span class="icon">📋</span>
                            <span class="label">گزارش سیستم</span>
                        </a>
                    <?php endif; ?>

                    <?php if (canViewBackup()): ?>
                    <a href="<?php echo BASE_URL; ?>backups/backup.php" class="quick-card">
                        <span class="icon">💾</span>
                        <span class="label">پشتیبان‌گیری</span>
                    </a>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<script>

</script>
</body>
</html>