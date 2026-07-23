<?php
// ============================================
// فایل اصلی مدیریت همه چیز
// ============================================

// تشخیص مسیر پایه
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
define('BASE_URL', $protocol . $host . '/supp/mysite/');

// مسیر فیزیکی پوشه assets
define('ASSETS_PATH', __DIR__ . '/assets/');
define('CSS_PATH', ASSETS_PATH . 'styles/');
define('JS_PATH', ASSETS_PATH . 'js/');

// ============================================
// تشخیص نام صفحه فعلی
// ============================================
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// ============================================
// فایل‌های عمومی (همه صفحات)
// ============================================
$css_common = [
    'main.css',
    'sidebar.css',
    'sweetalert2.min.css',
    'persian-datepicker.min.css'
];

$js_common = [
    'jquery.min.js',
    'persian-date.min.js',
    'persian-datepicker.min.js',
    'alljs.js',
    'sweetalert2.min.js'
];

// ============================================
// فایل‌های اختصاصی هر صفحه
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
    'admin_systems' => ['admin-systems.css'],
    'admin_topics' => ['admin-topics.css'],
    'admin_activities' => ['admin-activities.css'],
    'admin_kala' => ['admin-kala.css'],
    'dashboard' => ['dashboard.css'],
    'login' => ['login.css'],
    'user_dashboard' => ['user-dashboard.css'],
    'user_delete' => ['user-delete.css'],
    'user_select' => ['user-select.css'],
    'backup' => ['backup.css'],

];

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
    'admin_systems' => ['admin-systems.js'],
    'admin_topics' => ['admin-topics.js'],
    'admin_activities' => ['admin-activities.js'],
    'admin_ticketrep' => ['admin-ticketrep.js'],
'admin_servicerep'=>['admin-servicerep.js'],
    'admin_kala' => ['admin-kala.js'],
    'dashboard' => ['dashboard.js'],
    'login' => ['login.js'],
    'user_dashboard' => ['user-dashboard.js'],
    'user_delete' => ['user-delete.js'],
    'user_select' => ['user-select.js'],
    'backup' => ['backup.js'],
];

// ============================================
// تابع کمکی برای گرفتن نسخه فایل
// ============================================
function get_file_version($path) {
    $full_path = __DIR__ . '/assets/' . $path;
    if (file_exists($full_path)) {
        return filemtime($full_path);
    }
    return time(); // اگر فایل وجود نداشت، زمان فعلی را برگردان
}

// ============================================
// تابع بارگذاری CSS
// ============================================
function load_css() {
    global $css_common, $css_pages, $current_page;

    // بارگذاری فایل‌های عمومی
    foreach ($css_common as $file) {
        $version = get_file_version('styles/' . $file);
        echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/styles/' . $file . '?v=' . $version . '">' . "\n";
    }

    // بارگذاری فایل‌های اختصاصی صفحه
    if (isset($css_pages[$current_page])) {
        foreach ($css_pages[$current_page] as $file) {
            $version = get_file_version('styles/' . $file);
            echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/styles/' . $file . '?v=' . $version . '">' . "\n";
        }
    }
}

// ============================================
// تابع بارگذاری JS
// ============================================
function load_js() {
    global $js_common, $js_pages, $current_page;

    // بارگذاری فایل‌های عمومی
    foreach ($js_common as $file) {
        $version = get_file_version('js/' . $file);
        echo '<script src="' . BASE_URL . 'assets/js/' . $file . '?v=' . $version . '"></script>' . "\n";
    }

    // بارگذاری فایل‌های اختصاصی صفحه
    if (isset($js_pages[$current_page])) {
        foreach ($js_pages[$current_page] as $file) {
            $version = get_file_version('js/' . $file);
            echo '<script src="' . BASE_URL . 'assets/js/' . $file . '?v=' . $version . '"></script>' . "\n";
        }
    }
}

// ============================================
// تابع برای همه چیز (CSS + JS)
// ============================================
function load_assets() {
    load_css();
    load_js();
}
?>