<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>🏥 سامانه IMS</h2>
        <p>پنل مدیریت</p>
    </div>

    <ul class="sidebar-nav">

        <li>
            <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <span class="icon">📊</span>
                <span class="text">داشبورد</span>
            </a>
        </li>


        <!-- منوی تعاریف -->
        <?php
        if (canViewPersons() || canViewModels() || canViewUsers() || canViewDepartments()
            || canViewTopics() || canViewBrands() || canViewProducts() || canViewActivities()):
            // بررسی اینکه آیا صفحه فعلی در منوی تعاریف است
            $definitionsPages = ['admin_activities.php', 'admin_departments.php', 'admin_topics.php',
                'admin_brands.php', 'admin_models.php', 'admin_products.php',
                'admin_persons.php', 'admin_users.php'];
            $isDefinitionsActive = in_array($current_page, $definitionsPages);
            ?>

            <li class="has-submenu <?php echo $isDefinitionsActive ? 'open' : ''; ?>">

                <a href="javascript:void(0)" class="submenu-toggle">
                    <span class="icon">⚙️</span>
                    <span class="text">تعاریف</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="submenu">


                    <?php if (canViewActivities()): ?>
                        <li>
                            <a href="admin_activities.php" class="<?php echo $current_page == 'admin_activities.php' ? 'active' : ''; ?>">
                                <span class="icon">📋</span>
                                <span class="text">فعالیت</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewDepartments()): ?>
                        <li>
                            <a href="admin_departments.php" class="<?php echo $current_page == 'admin_departments.php' ? 'active' : ''; ?>">
                                <span class="icon">🏥</span>
                                <span class="text">بخش‌ها</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewTopics()): ?>
                        <li>
                            <a href="admin_topics.php" class="<?php echo $current_page == 'admin_topics.php' ? 'active' : ''; ?>">
                                <span class="icon">📋</span>
                                <span class="text">موضوعات</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewBrands()): ?>
                        <li>
                            <a href="admin_brands.php" class="<?php echo $current_page == 'admin_brands.php' ? 'active' : ''; ?>">
                                <span class="icon">🏷️</span>
                                <span class="text">برندها</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewModels()): ?>
                        <li>
                            <a href="admin_models.php" class="<?php echo $current_page == 'admin_models.php' ? 'active' : ''; ?>">
                                <span class="icon">📦</span>
                                <span class="text">مدل‌ها</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewProducts()): ?>
                        <li>
                            <a href="admin_products.php" class="<?php echo $current_page == 'admin_products.php' ? 'active' : ''; ?>">
                                <span class="icon">📦</span>
                                <span class="text">کالاها</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewPersons()): ?>
                        <li>
                            <a href="admin_persons.php" class="<?php echo $current_page == 'admin_persons.php' ? 'active' : ''; ?>">
                                <span class="icon">👥</span>
                                <span class="text">اشخاص</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewUsers()): ?>
                        <li>
                            <a href="admin_users.php" class="<?php echo $current_page == 'admin_users.php' ? 'active' : ''; ?>">
                                <span class="icon">👥</span>
                                <span class="text">کاربران</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>
        <?php endif; ?>

        <!-- منوی عملیات -->
        <?php
        if (canViewServices() || canViewInvoices() || canViewkala() || canViewPrinters() || canViewSystems()||canViewTickets()):

            $operationsPages = ['admin_services.php', 'admin_invoices.php', 'admin_kala.php',
                'admin_printers.php', 'admin_systems.php','requests.php'];
            $isOperationsActive = in_array($current_page, $operationsPages);

            ?>
            <li class="has-submenu <?php echo $isOperationsActive ? 'open' : ''; ?>">

                <a href="javascript:void(0)" class="submenu-toggle">
                    <span class="icon">📊</span>
                    <span class="text">عملیات</span>
                    <span class="arrow">▼</span>
                </a>

                <ul class="submenu">
                    <!-- پنل درخواست‌ها -->
                    <?php if (canViewTickets()): ?>
                        <li>
                            <a href="requests.php" class="<?php echo $current_page == 'requests.php' ? 'active' : ''; ?>">
                                <span class="icon">📋</span>
                                <span class="text">درخواست‌ها</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewServices()): ?>
                        <li>
                            <a href="admin_services.php" class="<?php echo $current_page == 'admin_services.php' ? 'active' : ''; ?>">
                                <span class="icon">🔧</span>
                                <span class="text">ثبت فعالیت</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewInvoices()): ?>
                        <li>
                            <a href="admin_invoices.php" class="<?php echo $current_page == 'admin_invoices.php' ? 'active' : ''; ?>">
                                <span class="icon">🧾</span>
                                <span class="text">ثبت فاکتور</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewkala()): ?>
                        <li>
                            <a href="admin_kala.php" class="<?php echo $current_page == 'admin_kala.php' ? 'active' : ''; ?>">
                                <span class="icon">📦</span>
                                <span class="text">ثبت کالا</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewPrinters()): ?>
                        <li>
                            <a href="admin_printers.php" class="<?php echo $current_page == 'admin_printers.php' ? 'active' : ''; ?>">
                                <span class="icon">🖨️</span>
                                <span class="text">ثبت پرینتر</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (canViewSystems()): ?>
                        <li>
                            <a href="admin_systems.php" class="<?php echo $current_page == 'admin_systems.php' ? 'active' : ''; ?>">
                                <span class="icon">💻</span>
                                <span class="text">ثبت سیستم</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>
        <?php endif; ?>

        <!-- منوی گزارش‌ها -->
        <?php if (canViewReports()):
            $operationsPages = ['admin_ticketrep.php', 'admin_servicerep.php', 'admin_invoicerep.php',
                'admin_kalarep.php', 'admin_printerrep.php','admin_systemrep.php'];
            $isOperationsActive = in_array($current_page, $operationsPages);
            ?>


            <li class="has-submenu <?php echo $isOperationsActive ? 'open' : ''; ?>" >
                <a href="javascript:void(0)" class="submenu-toggle">
                    <span class="icon">📊</span>
                    <span class="text">گزارشات</span>
                    <span class="arrow">▼</span>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="admin_ticketrep.php" class="<?php echo $current_page == 'admin_ticketrep.php' ? 'active' : ''; ?>">
                            <span class="icon">📋</span>
                            <span class="text">گزارش درخواست‌ها</span>
                        </a>
                    </li>

                    <li>
                        <a href="admin_servicerep.php" class="<?php echo $current_page == 'admin_servicerep.php' ? 'active' : ''; ?>">
                            <span class="icon">📋</span>
                            <span class="text">گزارش فعالیت</span>
                        </a>
                    </li>

                    <li>
                        <a href="admin_invoicerep.php" class="<?php echo $current_page == 'admin_invoicerep.php' ? 'active' : ''; ?>">
                            <span class="icon">📋</span>
                            <span class="text">گزارش فاکتور</span>
                        </a>
                    </li>

                    <li>
                        <a href="admin_kalarep.php" class="<?php echo $current_page == 'admin_kalarep.php' ? 'active' : ''; ?>">
                            <span class="icon">📋</span>
                            <span class="text">گزارش کالا</span>
                        </a>
                    </li>

                    <li>
                        <a href="admin_printerrep.php" class="<?php echo $current_page == 'admin_printerrep.php' ? 'active' : ''; ?>">
                            <span class="icon">📋</span>
                            <span class="text">گزارش پرینتر</span>
                        </a>
                    </li>

                    <li>
                        <a href="admin_systemrep.php" class="<?php echo $current_page == 'admin_systemrep.php' ? 'active' : ''; ?>">
                            <span class="icon">📋</span>
                            <span class="text">گزارش سیستم</span>
                        </a>
                    </li>

                </ul>
            </li>
        <?php endif; ?>

        <!-- پشتیبان گیری -->
        <?php if(canViewBackup()): ?>
        <li>
            <a href="backup.php" class="<?php echo $current_page == 'backup.php' ? 'active' : ''; ?>">
                <span class="icon">💾</span>
                <span class="text">پشتیبان گیری</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toggles = document.querySelectorAll('.submenu-toggle');

        toggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var parentLi = this.closest('.has-submenu');
                var submenu = parentLi.querySelector('.submenu');

                // بستن سایر منوها
                var allMenus = document.querySelectorAll('.has-submenu');
                allMenus.forEach(function(menu) {
                    if (menu !== parentLi) {
                        menu.classList.remove('open');
                        var otherSub = menu.querySelector('.submenu');
                        if (otherSub) {
                            otherSub.style.maxHeight = null;
                        }
                    }
                });

                // باز یا بسته کردن منو فعلی
                parentLi.classList.toggle('open');
                if (parentLi.classList.contains('open')) {
                    if (submenu) {
                        submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    }
                } else {
                    if (submenu) {
                        submenu.style.maxHeight = null;
                    }
                }
            });
        });

        // جلوگیری از بسته شدن منو وقتی روی لینک کلیک میشه
        var submenuLinks = document.querySelectorAll('.submenu a');
        submenuLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                var parentLi = this.closest('.has-submenu');
                if (parentLi) {
                    parentLi.classList.add('open');
                    var submenu = parentLi.querySelector('.submenu');
                    if (submenu) {
                        submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    }
                }
            });
        });
    });
</script>