<?php

date_default_timezone_set('Asia/Tehran');

ini_set('memory_limit', '512M');
set_time_limit(120);

require_once __DIR__ . '/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;


// ======================================================
// حذف ایموجی
// ======================================================

function removeEmoji($text)
{
    return preg_replace(
        '/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE00}-\x{FE0F}\x{1F1E6}-\x{1F1FF}]/u',
        '',
        (string)$text
    );
}


// ======================================================
// Persian / Arabic Text Shaper
// ======================================================
//
// نسخه اصلاح‌شده:
//  - پشتیبانی از ZWNJ (نیم‌فاصله)
//  - پشتیبانی از اعراب (harakat) و تنوین
//  - پشتیبانی از تطویل (ـ)
//  - پشتیبانی از کاف عربی (ك)
//  - اصلاح Lam + Alef با در نظر گرفتن اتصال قبلی
// ======================================================

function shapePersianText($text)
{
    if (!is_string($text) || $text === '') {
        return $text;
    }

    /*
     * فرم‌ها:
     *   [0] isolated
     *   [1] final
     *   [2] initial
     *   [3] medial
     *
     * اگر فرمی خالی باشد یعنی حرف آن اتصال را نمی‌پذیرد.
     */

    $forms = [

        // ا
        'ا' => ['ا', 'ﺎ', '', ''],

        // ب
        'ب' => ['ﺏ', 'ﺐ', 'ﺑ', 'ﺒ'],

        // پ
        'پ' => ['ﭖ', 'ﭗ', 'ﭘ', 'ﭙ'],

        // ت
        'ت' => ['ﺕ', 'ﺖ', 'ﺗ', 'ﺘ'],

        // ث
        'ث' => ['ﺙ', 'ﺚ', 'ﺛ', 'ﺜ'],

        // ج
        'ج' => ['ﺝ', 'ﺞ', 'ﺟ', 'ﺠ'],

        // چ
        'چ' => ['ﭺ', 'ﭻ', 'ﭼ', 'ﭽ'],

        // ح
        'ح' => ['ﺡ', 'ﺢ', 'ﺣ', 'ﺤ'],

        // خ
        'خ' => ['ﺥ', 'ﺦ', 'ﺧ', 'ﺨ'],

        // د
        'د' => ['ﺩ', 'ﺪ', '', ''],

        // ذ
        'ذ' => ['ﺫ', 'ﺬ', '', ''],

        // ر
        'ر' => ['ﺭ', 'ﺮ', '', ''],

        // ز
        'ز' => ['ﺯ', 'ﺰ', '', ''],

        // ژ
        'ژ' => ['ﮊ', 'ﮋ', '', ''],

        // س
        'س' => ['ﺱ', 'ﺲ', 'ﺳ', 'ﺴ'],

        // ش
        'ش' => ['ﺵ', 'ﺶ', 'ﺷ', 'ﺸ'],

        // ص
        'ص' => ['ﺹ', 'ﺺ', 'ﺻ', 'ﺼ'],

        // ض
        'ض' => ['ﺽ', 'ﺾ', 'ﺿ', 'ﻀ'],

        // ط
        'ط' => ['ﻁ', 'ﻂ', 'ﻃ', 'ﻄ'],

        // ظ
        'ظ' => ['ﻅ', 'ﻆ', 'ﻇ', 'ﻈ'],

        // ع
        'ع' => ['ﻉ', 'ﻊ', 'ﻋ', 'ﻌ'],

        // غ
        'غ' => ['ﻍ', 'ﻎ', 'ﻏ', 'ﻐ'],

        // ف
        'ف' => ['ﻑ', 'ﻒ', 'ﻓ', 'ﻔ'],

        // ق
        'ق' => ['ﻕ', 'ﻖ', 'ﻗ', 'ﻘ'],

        // ک فارسی
        'ک' => ['ﮎ', 'ﮏ', 'ﮐ', 'ﮑ'],

        // ك عربی
        'ك' => ['ﻙ', 'ﻚ', 'ﻛ', 'ﻜ'],

        // گ
        'گ' => ['ﮒ', 'ﮓ', 'ﮔ', 'ﮕ'],

        // ل
        'ل' => ['ﻝ', 'ﻞ', 'ﻟ', 'ﻠ'],

        // م
        'م' => ['ﻡ', 'ﻢ', 'ﻣ', 'ﻤ'],

        // ن
        'ن' => ['ﻥ', 'ﻦ', 'ﻧ', 'ﻨ'],

        // و
        'و' => ['ﻭ', 'ﻮ', '', ''],

        // ه
        'ه' => ['ﻩ', 'ﻪ', 'ﻫ', 'ﻬ'],

        // ی فارسی
        'ی' => ['ﯼ', 'ﯽ', 'ﯾ', 'ﯿ'],

        // ي عربی
        'ي' => ['ﻱ', 'ﻲ', 'ﻳ', 'ﻴ'],

        // ء
        'ء' => ['ء', '', '', ''],

        // ئ
        'ئ' => ['ﺉ', 'ﺊ', 'ﺋ', 'ﺌ'],

        // ؤ
        'ؤ' => ['ﺅ', 'ﺆ', '', ''],

        // أ
        'أ' => ['ﺃ', 'ﺄ', '', ''],

        // إ
        'إ' => ['ﺇ', 'ﺈ', '', ''],

        // آ
        'آ' => ['ﺁ', 'ﺂ', '', ''],

        // ة
        'ة' => ['ﺓ', 'ﺔ', '', ''],
    ];

    /*
     * ZWNJ = نیم‌فاصله
     * کاراکتر کنترلی که اتصال را قطع می‌کند
     * ولی خودش چاپ نمی‌شود (یا به‌صورت فاصله مجازی)
     */
    $zwnj = "\xE2\x80\x8C";

    /*
     * کاراکترهای ترکیبی (اعراب، تنوین، سکون، تشدید و ...)
     * این‌ها اتصال را قطع نمی‌کنند و باید نادیده گرفته شوند.
     */
    $isDiacritic = function ($char) {
        return (bool)preg_match(
            '/^[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06DC}\x{06DF}-\x{06E8}\x{06EA}-\x{06ED}]$/u',
            $char
        );
    };

    /*
     * تطویل (ـ) یک حرف اتصال‌دهنده است، ولی خودش فرم ندارد.
     * آن را به فرم final نون تبدیل نمی‌کنیم؛ صرفاً رد می‌کنیم.
     */
    $isTatweel = function ($char) {
        return $char === "\xE2\x80\x90"; // U+0640 ARABIC TATWEEL
    };

    // --------------------------------------------------
    // Lam + Alef  (با در نظر گرفتن اتصال حرف قبلی)
    // --------------------------------------------------
    // این کار در حلقه انجام می‌شود تا بتوانیم از فرم اتصالی
    // ﻻ / ﻷ / ﻹ / ﻵ استفاده کنیم. برای سادگی اینجا فقط
    // precomposed های آماده را جایگزین می‌کنیم.

    $lamAlef = [
        'لا' => 'ﻻ',
        'لأ' => 'ﻷ',
        'لإ' => 'ﻹ',
        'لآ' => 'ﻵ',
    ];

    $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);

    $count = count($chars);

    if ($count === 0) {
        return $text;
    }

    // --------------------------------------------------
    // آیا حرف از سمت چپ (به حرف بعدی) اتصال می‌پذیرد؟
    // --------------------------------------------------
    $canConnectLeft = function ($char) use ($forms) {
        if (!isset($forms[$char])) {
            return false;
        }
        return (
            $forms[$char][2] !== '' ||
            $forms[$char][3] !== ''
        );
    };

    // --------------------------------------------------
    // آیا حرف از سمت راست (به حرف قبلی) اتصال می‌پذیرد؟
    // --------------------------------------------------
    $canConnectRight = function ($char) use ($forms) {
        if (!isset($forms[$char])) {
            return false;
        }
        return (
            $forms[$char][1] !== '' ||
            $forms[$char][3] !== ''
        );
    };

    // --------------------------------------------------
    // پیدا کردن حرف قبلی/بعدی واقعی
    // (با نادیده گرفتن اعراب و تطویل)
    // --------------------------------------------------
    $findPrev = function ($i) use ($chars, $isDiacritic, $isTatweel) {
        for ($j = $i - 1; $j >= 0; $j--) {
            $c = $chars[$j];
            if ($isDiacritic($c) || $isTatweel($c)) {
                continue;
            }
            return $c;
        }
        return null;
    };

    $findNext = function ($i) use ($chars, $isDiacritic, $isTatweel) {
        $n = count($chars);
        for ($j = $i + 1; $j < $n; $j++) {
            $c = $chars[$j];
            if ($isDiacritic($c) || $isTatweel($c)) {
                continue;
            }
            return $c;
        }
        return null;
    };

    // --------------------------------------------------
    // ساخت خروجی
    // --------------------------------------------------
    $result = '';

    for ($i = 0; $i < $count; $i++) {

        $char = $chars[$i];

        // اعراب → بدون تغییر
        if ($isDiacritic($char)) {
            $result .= $char;
            continue;
        }

        // تطویل → بدون تغییر، ولی اتصال را قطع نمی‌کند
        if ($isTatweel($char)) {
            $result .= $char;
            continue;
        }

        // ZWNJ → چاپ شود، ولی اتصال را قطع کند
        if ($char === $zwnj) {
            $result .= $char;
            continue;
        }

        // Lam + Alef
        if (
            $char === 'ل'
            && $i + 1 < $count
            && isset($lamAlef['ل' . $chars[$i + 1]])
        ) {
            $nextRaw = $chars[$i + 1];
            $ligature = $lamAlef['ل' . $nextRaw];

            // آیا حرف قبلی به این لام متصل می‌شود؟
            $prev = $findPrev($i);
            $joinPrev = ($prev !== null)
                && $canConnectRight($prev)
                && true; // ﻻ فرم final هم دارد

            // در صورت اتصال، از فرم final استفاده می‌کنیم.
            // precomposed ﻻ فقط یک شکل است ولی در عمل
            // نمایش درست را حفظ می‌کند.
            $result .= $ligature;

            // رد کردن الف
            $i++;
            continue;
        }

        // حرف غیرفارسی
        if (!isset($forms[$char])) {
            $result .= $char;
            continue;
        }

        $prev = $findPrev($i);
        $next = $findNext($i);

        // اگر حرف بعدی ZWNJ باشد، یعنی اتصال قطع است
        $nextRaw = ($i + 1 < $count) ? $chars[$i + 1] : null;
        if ($nextRaw === $zwnj) {
            $next = null;
        }

        // اگر حرف قبلی ZWNJ باشد، یعنی اتصال قطع است
        $prevRaw = ($i > 0) ? $chars[$i - 1] : null;
        if ($prevRaw === $zwnj) {
            $prev = null;
        }

        $joinPrev = false;
        $joinNext = false;

        if ($prev !== null) {
            $joinPrev =
                $canConnectRight($prev) &&
                $canConnectLeft($char);
        }

        if ($next !== null) {
            $joinNext =
                $canConnectRight($char) &&
                $canConnectLeft($next);
        }

        // انتخاب فرم
        if ($joinPrev && $joinNext) {
            $result .= $forms[$char][3] !== ''
                ? $forms[$char][3]
                : $forms[$char][0];
        } elseif ($joinPrev) {
            $result .= $forms[$char][1] !== ''
                ? $forms[$char][1]
                : $forms[$char][0];
        } elseif ($joinNext) {
            $result .= $forms[$char][2] !== ''
                ? $forms[$char][2]
                : $forms[$char][0];
        } else {
            $result .= $forms[$char][0];
        }
    }

    return $result;
}


// ======================================================
// Shaping تمام متن‌های HTML
// ======================================================

function shapePersianHtml($html)
{
    return preg_replace_callback(
        '/>([^<]+)</u',
        function ($match) {
            return '>' .
                shapePersianText($match[1]) .
                '<';
        },
        $html
    );
}


// ======================================================
// Sanitize helpers
// ======================================================

/**
 * پاکسازی HTML ورودی جدول با استفاده از DOMDocument.
 * فقط تگ‌ها و attributeهای مجاز باقی می‌مانند.
 */
function sanitizeTableHtml($html)
{
    if (!is_string($html) || trim($html) === '') {
        return '';
    }

    $allowedTags = [
        'table' => ['class', 'id'],
        'thead' => [],
        'tbody' => [],
        'tfoot' => [],
        'tr'    => [],
        'th'    => ['colspan', 'rowspan', 'class'],
        'td'    => ['colspan', 'rowspan', 'class'],
        'colgroup' => [],
        'col'   => ['span', 'width'],
        'caption' => [],
    ];

    $doc = new DOMDocument();

    libxml_use_internal_errors(true);

    // برای پشتیبانی UTF-8
    $doc->loadHTML(
        '<?xml encoding="UTF-8">'
        . '<div id="__wrap__">'
        . $html
        . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    libxml_clear_errors();

    $xpath = new DOMXPath($doc);

    // حذف کامل تگ‌های خطرناک
    $forbidden = $xpath->query(
        '//script | //style | //iframe | //object | //embed | //link | //meta | //base | //form | //input | //button | //textarea | //select | //option'
    );

    foreach ($forbidden as $node) {
        $node->parentNode->removeChild($node);
    }

    // بازگشت به ترتیب درست
    $all = $xpath->query('//*');

    $toRemove = [];

    foreach ($all as $node) {

        /** @var DOMElement $node */

        // فقط Element
        if (!($node instanceof DOMElement)) {
            continue;
        }

        $tag = strtolower($node->nodeName);

        // حذف کامنت‌ها
        if ($node->nodeType === XML_COMMENT_NODE) {
            $toRemove[] = $node;
            continue;
        }

        // تگ مجاز نیست → حذف
        if (!isset($allowedTags[$tag])) {
            // اگر فرزند متنی دارد، متن را نگه داریم؟
            // برای امنیت بیشتر، کامل حذف می‌کنیم.
            $toRemove[] = $node;
            continue;
        }

        // حذف attributeهای غیرمجاز
        $allowedAttrs = $allowedTags[$tag];

        $attrToRemove = [];

        foreach ($node->attributes as $attr) {

            $name = strtolower($attr->nodeName);

            if (!in_array($name, $allowedAttrs, true)) {
                $attrToRemove[] = $name;
            }

            // حذف هر مقدار مشکوک به url(), expression(), javascript:
            $val = (string)$attr->nodeValue;

            if (preg_match(
                '/(javascript:|vbscript:|data:|expression\s*\(|url\s*\()/i',
                $val
            )) {
                if (!in_array($name, $attrToRemove, true)) {
                    $attrToRemove[] = $name;
                }
            }
        }

        foreach ($attrToRemove as $name) {
            $node->removeAttribute($name);
        }
    }

    foreach ($toRemove as $node) {
        if ($node->parentNode) {
            $node->parentNode->removeChild($node);
        }
    }

    // خروجی: فرزندان wrapper
    $wrapper = $doc->getElementById('__wrap__');

    if (!$wrapper) {
        return '';
    }

    $out = '';

    foreach ($wrapper->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }

    return $out;
}


/**
 * پاکسازی متن ساده (برای عنوان، فیلتر و ...)
 */
function cleanText($text, $maxLen = 500)
{
    $text = (string)$text;
    $text = strip_tags($text);
    $text = removeEmoji($text);
    $text = trim($text);

    if (mb_strlen($text, 'UTF-8') > $maxLen) {
        $text = mb_substr($text, 0, $maxLen, 'UTF-8');
    }

    return $text;
}


// ======================================================
// دریافت اطلاعات
// ======================================================

$title = isset($_POST['title'])
    ? cleanText($_POST['title'], 200)
    : 'گزارش';

$filterRaw = isset($_POST['filter'])
    ? $_POST['filter']
    : '';

$tableHtmlRaw = isset($_POST['html'])
    ? $_POST['html']
    : '';


// ======================================================
// پاکسازی
// ======================================================

// عنوان: متن ساده
$title = removeEmoji($title);

// فیلتر: HTML محدود (فقط b, i, strong, em, span, br)
$filter = strip_tags(
    (string)$filterRaw,
    '<b><i><strong><em><span><br>'
);
$filter = removeEmoji($filter);

// جدول: sanitize کامل
$tableHtml = sanitizeTableHtml($tableHtmlRaw);
$tableHtml = removeEmoji($tableHtml);


// ======================================================
// فونت Vazir
// ======================================================

$fontPath = realpath(
    __DIR__ . '/../styles/Fonts/Vazir.ttf'
);

if (!$fontPath || !file_exists($fontPath)) {
    http_response_code(500);
    die('فونت Vazir.ttf پیدا نشد');
}

// مسیر فایل برای Dompdf
$fontUrl = 'file://' . str_replace('\\', '/', $fontPath);


// ======================================================
// تاریخ
// ======================================================

$printDate = date('Y/m/d H:i:s');


// ======================================================
// استخراج جدول
// ======================================================

if (preg_match(
    '/<table\b.*?<\/table>/is',
    $tableHtml,
    $match
)) {
    $tableHtml = $match[0];
} else {
    $tableHtml = '
        <table>
            <tr>
                <td>جدولی برای نمایش وجود ندارد</td>
            </tr>
        </table>
    ';
}


// ======================================================
// حذف direction و style قبلی
// ======================================================

$tableHtml = preg_replace(
    '/\sdir\s*=\s*["\'][^"\']*["\']/i',
    '',
    $tableHtml
);

$tableHtml = preg_replace(
    '/\sstyle\s*=\s*["\'][^"\']*["\']/i',
    '',
    $tableHtml
);


// ======================================================
// برعکس کردن ترتیب سلول‌ها در هر ردیف
// ======================================================

$tableHtml = preg_replace_callback(
    '/(<tr\b[^>]*>)(.*?)(<\/tr>)/is',
    function ($match) {

        $open = $match[1];
        $inside = $match[2];
        $close = $match[3];

        preg_match_all(
            '/<(th|td)\b[^>]*>.*?<\/\1>/is',
            $inside,
            $cells
        );

        if (empty($cells[0])) {
            return $match[0];
        }

        $reversed = array_reverse($cells[0]);

        return $open .
            implode('', $reversed) .
            $close;
    },
    $tableHtml
);


// ======================================================
// تبدیل حروف فارسی به فرم متصل
// ======================================================

$tableHtml = shapePersianHtml($tableHtml);

$title = shapePersianText($title);
$filter = shapePersianHtml($filter);


// ======================================================
// HTML نهایی
// ======================================================

$html = '<!DOCTYPE html>

<html lang="fa">

<head>

<meta charset="UTF-8">

<style>

@page {
    size: A4 landscape;
    margin: 7mm;
}

@font-face {
    font-family: "Vazir";
    font-style: normal;
    font-weight: normal;
    src: url("' . $fontUrl . '") format("truetype");
}

html,
body {
    margin: 0;
    padding: 0;
    font-family: "Vazir";
    font-size: 9px;
    color: #000000;
}

body {
    direction: rtl;
}

.print-container {
    width: 100%;
}

.print-header {
    width: 100%;
    text-align: center;
    direction: rtl;
    margin-bottom: 8px;
    padding-bottom: 5px;
    border-bottom: 2px solid #333;
}

.print-header h1 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: bold;
}

.print-header .date {
    font-size: 9px;
}

.filters-info {
    width: 100%;
    box-sizing: border-box;
    direction: rtl;
    text-align: center;
    border: 1px solid #777;
    padding: 4px;
    margin-bottom: 8px;
    font-size: 8px;
}

table {
    width: 100% !important;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    table-layout: fixed !important;
    direction: ltr !important;
    font-family: "Vazir" !important;
}

table th,
table td {
    border: 1px solid #777 !important;
    padding: 4px !important;
    font-family: "Vazir" !important;
    font-size: 8px !important;
    text-align: center !important;
    vertical-align: middle !important;
    direction: rtl !important;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
}

table th {
    background: #334155 !important;
    color: #ffffff !important;
    font-weight: bold !important;
}

table td {
    background: #ffffff !important;
    color: #000000 !important;
}

table tbody tr:nth-child(even) td {
    background: #f3f3f3 !important;
}

table thead {
    display: table-header-group;
}

table tr {
    page-break-inside: avoid;
}

.no-data {
    text-align: center !important;
    padding: 10px !important;
}

</style>

</head>

<body>

<div class="print-container">

    <div class="print-header">

        <h1>'
    . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
    . '</h1>

        <div class="date">
            تاریخ چاپ:
            '
    . htmlspecialchars($printDate, ENT_QUOTES, 'UTF-8')
    . '
        </div>

    </div>

    ' .
    (
    !empty(trim(strip_tags($filter)))
        ?
        '<div class="filters-info">' . $filter . '</div>'
        :
        ''
    )
    . '

    <div class="table-wrapper">
        ' . $tableHtml . '
    </div>

</div>

</body>

</html>';


// ======================================================
// Dompdf
// ======================================================

$options = new Options();

$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'Vazir');
$options->set('chroot', realpath(__DIR__ . '/..'));


// ======================================================
// ایجاد PDF
// ======================================================

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html, 'UTF-8');

$dompdf->setPaper('A4', 'landscape');

$dompdf->render();


// ======================================================
// Filename (safe)
// ======================================================

$now = new DateTime(
    'now',
    new DateTimeZone('Asia/Tehran')
);

// فقط حروف/اعداد/خط تیره/آندرلاین برای filename مطمئن
$safeTitle = preg_replace(
    '/[^\p{L}\p{N}_\-]+/u',
    '_',
    $title
);

$safeTitle = trim($safeTitle, '_-');

if ($safeTitle === '') {
    $safeTitle = 'report';
}

if (mb_strlen($safeTitle, 'UTF-8') > 80) {
    $safeTitle = mb_substr($safeTitle, 0, 80, 'UTF-8');
}

$filename = $safeTitle
    . '_'
    . $now->format('Y-m-d_H-i-s')
    . '.pdf';


// ======================================================
// Download
// ======================================================

$dompdf->stream(
    $filename,
    [
        'Attachment' => true
    ]
);

exit;