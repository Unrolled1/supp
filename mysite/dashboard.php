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
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/sidebar.css">
    <link rel="stylesheet" href="styles/dashboard.css">
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
                        <a href="admin_activities.php" class="quick-card">
                            <span class="icon">📋</span>
                            <span class="label">فعالیت</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewDepartments()): ?>
                        <a href="admin_departments.php" class="quick-card">
                            <span class="icon">🏥</span>
                            <span class="label">بخش‌ها</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewTopics()): ?>
                        <a href="admin_topics.php" class="quick-card">
                            <span class="icon">📋</span>
                            <span class="label">موضوعات</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewBrands()): ?>
                        <a href="admin_brands.php" class="quick-card">
                            <span class="icon">🏷️</span>
                            <span class="label">برندها</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewModels()): ?>
                        <a href="admin_models.php" class="quick-card">
                            <span class="icon">📦</span>
                            <span class="label">مدل‌ها</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewProducts()): ?>
                        <a href="admin_products.php" class="quick-card">
                            <span class="icon">📦</span>
                            <span class="label">کالاها</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewPersons()): ?>
                        <a href="admin_persons.php" class="quick-card">
                            <span class="icon">👥</span>
                            <span class="label">اشخاص</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewUsers()): ?>
                        <a href="admin_users.php" class="quick-card">
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
                    <?php if (canViewServices()): ?>
                        <a href="admin_services.php" class="quick-card">
                            <span class="icon">🔧</span>
                            <span class="label">ثبت فعالیت</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewInvoices()): ?>
                        <a href="admin_invoices.php" class="quick-card">
                            <span class="icon">🧾</span>
                            <span class="label">ثبت فاکتور</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewGoods()): ?>
                        <a href="admin_kala.php" class="quick-card">
                            <span class="icon">📦</span>
                            <span class="label">ثبت کالا</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewPrinters()): ?>
                        <a href="admin_printers.php" class="quick-card">
                            <span class="icon">🖨️</span>
                            <span class="label">ثبت پرینتر</span>
                        </a>
                    <?php endif; ?>
                    <?php if (canViewSystems()): ?>
                        <a href="admin_systems.php" class="quick-card">
                            <span class="icon">💻</span>
                            <span class="label">ثبت سیستم</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ردیف سوم: گزارشات -->
            <div class="quick-row">
                <h2 class="quick-title">📈 گزارشات</h2>
                <div class="quick-grid">
                    <?php if (canViewReports()): ?>
                        <a href="admin_reports.php" class="quick-card">
                            <span class="icon">📋</span>
                            <span class="label">گزارش درخواست‌ها</span>
                        </a>
                    <?php endif; ?>
                    <a href="backup.php" class="quick-card">
                        <span class="icon">💾</span>
                        <span class="label">پشتیبان‌گیری</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="assets/js/alljs.js"></script>
<script>

</script>
</body>
</html>