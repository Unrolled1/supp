
<?php
// ============================================
// مسیر اصلی پروژه
// ============================================

define('PROJECT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// ============================================
// تشخیص آدرس پایه سایت
// ============================================

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? 'https://'
    : 'http://';

$host = $_SERVER['HTTP_HOST'];

$scriptDir = dirname($_SERVER['SCRIPT_NAME']);

$baseFolders = [
    'activities',
    'backups',
    'brands',
    'dashboard',
    'departments',
    'invoice',
    'invoicerep',
    'kala',
    'kalarep',
    'login',
    'models',
    'persons',
    'printer',
    'printerrep',
    'products',
    'request',
    'servicerep',
    'services',
    'sidebar',
    'system',
    'systemrep',
    'ticketrep',
    'topics',
    'user_dashboard',
    'tickets',
    'users',
    'reports',

];

// اگر صفحه داخل یکی از پوشه‌های صفحات باشد، یک سطح به عقب برگرد
if (in_array(basename($scriptDir), $baseFolders, true)) {
    $scriptDir = dirname($scriptDir);
}

$basePath = rtrim($scriptDir, '/\\') . '/';

define('BASE_URL', $protocol . $host . $basePath);

// ============================================
// مسیر فیزیکی پوشه Assets
// ============================================

define('ASSETS_PATH', PROJECT_PATH . 'assets' . DIRECTORY_SEPARATOR);
define('CSS_PATH', ASSETS_PATH . 'styles' . DIRECTORY_SEPARATOR);
define('JS_PATH', ASSETS_PATH . 'js' . DIRECTORY_SEPARATOR);
// مسیر فایل‌های اصلی پروژه
define('SIDEBAR_PATH', PROJECT_PATH . 'sidebar' . DIRECTORY_SEPARATOR . 'sidebar.php');

// ============================================
// تشخیص نام صفحه فعلی
// ============================================

$current_page = basename($_SERVER['PHP_SELF'], '.php');

// ============================================
// فایل‌های عمومی CSS
// ============================================

$css_common = [
    'main.css',
    'sidebar.css',
    'sweetalert2.min.css',
    'persian-datepicker.min.css'
];

// ============================================
// فایل‌های عمومی JS
// ============================================

$js_common = [
    'jquery.min.js',
    'sweetalert2.min.js',
    'persian-date.min.js',
    'persian-datepicker.min.js',
    'alljs.js'
];

// ============================================
// فایل‌های اختصاصی CSS هر صفحه
// مسیرها نسبت به ریشه پروژه هستند
// ============================================

$css_pages = [
    'requests' => ['requests.css'],
    'admin_products' => ['admin-products.css'],
    'admin_models' => ['admin-models.css'],
    'admin_brands' => ['admin-brands.css'],
    'admin_users' => ['admin-users.css'],
    'admin_departments' => ['admin-departments.css'],
    'admin_invoices' => ['admin-invoices.css'],
    'admin_persons' => ['admin-persons.css'],
    'admin_printers' => ['admin-printers.css'],
    'admin_services' => ['admin-services.css'],
    'admin_systems' => ['system/admin-systems.css'],
    'admin_systemrep'=> ['systemrep/admin_systemrep.css'],
    'admin_topics' => ['admin-topics.css'],
    'admin_activities' => ['admin-activities.css'],
    'admin_kala' => ['admin-kala.css'],
    'dashboard' => ['dashboard/dashboard.css'],
    'login' => ['login/login.css'],
    'user_dashboard' => ['user-dashboard.css'],
    'user_delete' => ['user-delete.css'],
    'user_select' => ['user-select.css'],
    'backup' => ['backups/backup.css'],
    'admin_ticketrep'=> ['admin_ticketrep.css'],
    'admin_servicerep'=> ['admin_servicerep.css']



];

// ============================================
// فایل‌های اختصاصی JS هر صفحه
// مسیرها نسبت به ریشه پروژه هستند
// ============================================

$js_pages = [
    'requests' => ['requests.js'],
    'admin_persons' => ['admin-persons.js'],
    'admin_products' => ['admin-products.js'],
    'admin_models' => ['admin-models.js'],
    'admin_brands' => ['admin-brands.js'],
    'admin_users' => ['admin-users.js'],
    'admin_departments' => ['admin-departments.js'],
    'admin_invoices' => ['admin-invoices.js'],
    'admin_printers' => ['admin-printers.js'],
    'admin_services' => ['admin-services.js'],
    'admin_topics' => ['admin-topics.js'],
    'admin_activities' => ['activities/admin-activities.js'],
    'admin_servicerep' => ['admin-servicerep.js'],
    'admin_systems' => ['system/admin-systems.js'],
    'admin_systemrep'=> ['systemrep/admin-systemrep.js'],
    'admin_kala' => ['admin-kala.js'],
    'dashboard' => ['dashboard/dashboard.js'],
    'login' => ['login/login.js'],
    'user_dashboard' => ['user-dashboard.js'],
    'user_delete' => ['user-delete.js'],
    'user_select' => ['user-select.js'],
    'backup' => ['backup.js'],
    'admin_ticketrep'=> ['admin_ticketrep.js']

];

// ============================================
// دریافت نسخه فایل برای جلوگیری از کش قدیمی
// ============================================

function get_file_version($path)
{
    $full_path = ASSETS_PATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

    if (file_exists($full_path)) {
        return filemtime($full_path);
    }

    return time();
}

// ============================================
// بارگذاری CSS
// ============================================

function load_css()
{
    global $css_common, $css_pages, $current_page;

    // استایل‌های عمومی
    foreach ($css_common as $file) {
        $fullPath = CSS_PATH . $file;
        $version = file_exists($fullPath) ? filemtime($fullPath) : time();

        echo '<link rel="stylesheet" href="' .
            BASE_URL . 'assets/styles/' . $file . '?v=' . $version . '">' . PHP_EOL;
    }

    // استایل اختصاصی صفحه
    if (isset($css_pages[$current_page])) {
        foreach ($css_pages[$current_page] as $file) {
            $fullPath = PROJECT_PATH . str_replace(
                    ['/', '\\'],
                    DIRECTORY_SEPARATOR,
                    $file
                );

            $version = file_exists($fullPath) ? filemtime($fullPath) : time();

            echo '<link rel="stylesheet" href="' .
                BASE_URL . $file . '?v=' . $version . '">' . PHP_EOL;
        }
    }
}

// ============================================
// بارگذاری JS
// ============================================

function load_js()
{
    global $js_common, $js_pages, $current_page;

    // فایل‌های عمومی
    foreach ($js_common as $file) {

        $version = get_file_version('js/' . $file);

        echo '<script src="' .
            BASE_URL . 'assets/js/' . $file . '?v=' . $version . '"></script>' . "\n";
    }

    // فایل اختصاصی صفحه
    if (isset($js_pages[$current_page])) {

        foreach ($js_pages[$current_page] as $file) {

            $fullPath = PROJECT_PATH .
                str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);

            $version = file_exists($fullPath)
                ? filemtime($fullPath)
                : time();

            echo '<script src="' .
                BASE_URL . $file . '?v=' . $version . '"></script>' . "\n";
        }
    }
    $sidebarJs = PROJECT_PATH . 'sidebar/sidebar.js';

    if (file_exists($sidebarJs)) {
        echo '<script src="' .
            BASE_URL . 'sidebar/sidebar.js?v=' .
            filemtime($sidebarJs) . '"></script>' . "\n";
    }
}
function load_sidebar()
{
    if (file_exists(SIDEBAR_PATH)) {
        include SIDEBAR_PATH;
    } else {
        error_log('Sidebar file not found: ' . SIDEBAR_PATH);
    }
}
// ============================================
// بارگذاری همه فایل‌ها
// ============================================

function load_assets()
{
    load_css();
    load_js();
    load_sidebar();
}

?>
