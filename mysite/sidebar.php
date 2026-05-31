
<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>🏥 سیستم پشتیبانی</h2>
        <p>پنل مدیریت</p>
    </div>
    <ul class="sidebar-nav">

        <!-- پنل درخواست‌ها -->
        <?php if (canViewTickets()): ?>
            <li>
                <a href="admin.php" class="<?php echo $current_page == 'admin.php' ? 'active' : ''; ?>">
                    <span class="icon">📋</span>
                    <span class="text">درخواست‌ها</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- منوی تعاریف -->
        <?php if (canViewUsers() || canViewDepartments() || canViewTopics() || canViewBrands() || canViewProducts()): ?>
            <li class="has-submenu">
                <a href="javascript:void(0)" class="submenu-toggle">
                    <span class="icon">⚙️</span>
                    <span class="text">تعاریف</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="submenu">
                    <?php if (canViewUsers()): ?>
                        <li>
                            <a href="admin_users.php" class="<?php echo $current_page == 'admin_users.php' ? 'active' : ''; ?>">
                                <span class="icon">👥</span>
                                <span class="text"> کاربران</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewDepartments()): ?>
                        <li>
                            <a href="admin_departments.php" class="<?php echo $current_page == 'admin_departments.php' ? 'active' : ''; ?>">
                                <span class="icon">🏥</span>
                                <span class="text"> بخش‌ها</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewTopics()): ?>
                        <li>
                            <a href="admin_topics.php" class="<?php echo $current_page == 'admin_topics.php' ? 'active' : ''; ?>">
                                <span class="icon">📋</span>
                                <span class="text"> موضوعات</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- منوهای جدید برندها و کالاها -->
                    <?php if (canViewBrands()): ?>
                        <li>
                            <a href="admin_brands.php" class="<?php echo $current_page == 'admin_brands.php' ? 'active' : ''; ?>">
                                <span class="icon">🏷️</span>
                                <span class="text"> برندها</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewProducts()): ?>
                        <li>
                            <a href="admin_products.php" class="<?php echo $current_page == 'admin_products.php' ? 'active' : ''; ?>">
                                <span class="icon">📦</span>
                                <span class="text"> کالاها</span>
                            </a>
                        </li>
                    <?php endif; ?>


                </ul>
            </li>
        <?php endif; ?>

    </ul>
</div>

<style>
    .sidebar {
        width: 280px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px 0;
        position: fixed;
        right: 0;
        top: 0;
        bottom: 0;
        box-shadow: -4px 0 15px rgba(0,0,0,0.1);
        z-index: 100;
        overflow-y: auto;
    }

    .sidebar-header {
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.2);
        margin-bottom: 20px;
    }

    .sidebar-header h2 {
        font-size: 20px;
        font-weight: bold;
        margin: 0;
    }

    .sidebar-header p {
        font-size: 12px;
        opacity: 0.7;
        margin-top: 5px;
    }

    .sidebar-nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-nav li {
        margin: 5px 0;
    }

    .sidebar-nav li a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 14px;
        font-weight: 500;
    }

    .sidebar-nav li a:hover {
        background: rgba(255,255,255,0.2);
        padding-right: 25px;
    }

    .sidebar-nav li a.active {
        background: rgba(255,255,255,0.25);
        border-right: 3px solid white;
    }

    .sidebar-nav .icon {
        font-size: 20px;
        width: 30px;
        text-align: center;
    }

    .sidebar-nav li.has-submenu {
        position: relative;
    }

    .sidebar-nav li.has-submenu > a {
        justify-content: space-between;
        cursor: pointer;
    }

    .sidebar-nav .submenu-toggle .arrow {
        font-size: 10px;
        transition: transform 0.3s ease;
    }

    .sidebar-nav li.has-submenu.open .submenu-toggle .arrow {
        transform: rotate(180deg);
    }

    .sidebar-nav .submenu {
        list-style: none;
        padding: 0;
        margin: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        background: rgba(0,0,0,0.2);
        border-radius: 10px;
    }

    .sidebar-nav li.has-submenu.open .submenu {
        max-height: 300px;
    }

    .sidebar-nav .submenu li a {
        padding: 10px 20px 10px 45px;
        font-size: 13px;
    }

    .main-content {
        flex: 1;
        margin-right: 280px;
        padding: 20px;
        background: transparent;
    }

    .main-header {
        background: white;
        border-radius: 15px;
        padding: 15px 25px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .user-name {
        font-weight: bold;
        color: #333;
    }

    .logout-btn-sidebar {
        background: #dc3545;
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        text-decoration: none;
        font-size: 13px;
        transition: 0.3s;
    }

    .logout-btn-sidebar:hover {
        background: #c82333;
    }

    .clock-display {
        font-size: 13px;
        background: rgba(0,0,0,0.05);
        padding: 5px 12px;
        border-radius: 20px;
        font-family: monospace;
        direction: ltr;
    }

    @media (max-width: 768px) {
        .sidebar { width: 70px; }
        .sidebar-header h2, .sidebar-header p, .sidebar-nav li a .text { display: none; }
        .sidebar-nav li a { justify-content: center; padding: 12px 0; }
        .sidebar-nav .icon { font-size: 24px; width: auto; }
        .main-content { margin-right: 70px; }
    }
</style>

<script>
    (function() {
        if (window.sidebarInitialized) return;
        window.sidebarInitialized = true;

        document.addEventListener('DOMContentLoaded', function() {
            var submenuToggles = document.querySelectorAll('.submenu-toggle');

            for (var i = 0; i < submenuToggles.length; i++) {
                (function(toggle) {
                    var parentLi = toggle.closest('.has-submenu');
                    var activeLink = parentLi.querySelector('.submenu a.active');
                    if (activeLink) {
                        parentLi.classList.add('open');
                    }

                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        parentLi.classList.toggle('open');
                    });
                })(submenuToggles[i]);
            }
        });
    })();
</script>