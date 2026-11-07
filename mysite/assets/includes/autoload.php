<?php

// ============================================
// فایل تنظیمات اصلی
// ============================================

require_once __DIR__ . '/../../config/config.php';


// ============================================
// اتصال دیتابیس
// ============================================

require_once __DIR__ . '/db.php';


// ============================================
// فایل‌های پایه پروژه
// ============================================

require_once __DIR__ . '/jdf.php';
require_once __DIR__ . '/functions.php';


// ============================================
// بارگذاری فایل‌های PHP عمومی
// ============================================

foreach ($php_common as $file) {

    $phpPath = ASSETS_PATH . 'includes' .
        DIRECTORY_SEPARATOR .
        str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);

    if (is_file($phpPath)) {

        require_once $phpPath;

    } else {

        error_log('PHP common file not found: ' . $phpPath);

    }
}


// ============================================
// بارگذاری فایل‌های PHP اختصاصی صفحه
// ============================================

if (isset($php_pages[$current_page])) {

    foreach ($php_pages[$current_page] as $file) {

        $phpPath = ASSETS_PATH . 'includes' .
            DIRECTORY_SEPARATOR .
            str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);

        if (is_file($phpPath)) {

            require_once $phpPath;

        } else {

            error_log('PHP page file not found: ' . $phpPath);

        }
    }
}