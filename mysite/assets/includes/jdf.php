<?php
// تنظیم منطقه زمانی ایران
date_default_timezone_set('Asia/Tehran');

/* ============================================
   کتابخانه تبدیل تاریخ شمسی
   ============================================ */

function gregorian_to_jalali($g_y, $g_m, $g_d)
{
    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

    $gy = $g_y - 1600;
    $gm = $g_m - 1;
    $gd = $g_d - 1;

    $g_day_no = 365 * $gy + floor(($gy + 3) / 4) - floor(($gy + 99) / 100) + floor(($gy + 399) / 400);

    for ($i = 0; $i < $gm; ++$i)
        $g_day_no += $g_days_in_month[$i];
    if ($gm > 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)))
        $g_day_no++;
    $g_day_no += $gd;

    $j_day_no = $g_day_no - 79;

    $j_np = floor($j_day_no / 12053);
    $j_day_no %= 12053;

    $jy = 979 + 33 * $j_np + 4 * floor($j_day_no / 1461);
    $j_day_no %= 1461;

    if ($j_day_no >= 366) {
        $jy += floor(($j_day_no - 1) / 365);
        $j_day_no = ($j_day_no - 1) % 365;
    }

    for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i)
        $j_day_no -= $j_days_in_month[$i];
    $jm = $i + 1;
    $jd = $j_day_no + 1;

    return array($jy, $jm, $jd);
}

function jdate($format, $timestamp = null)
{
    if ($timestamp === null) {
        $timestamp = time();
    }

    $hours = date("H", $timestamp);
    $minutes = date("i", $timestamp);
    $seconds = date("s", $timestamp);

    $gregorian_year = date("Y", $timestamp);
    $gregorian_month = date("m", $timestamp);
    $gregorian_day = date("d", $timestamp);

    list($jalali_year, $jalali_month, $jalali_day) = gregorian_to_jalali($gregorian_year, $gregorian_month, $gregorian_day);

    $output = '';
    $length = strlen($format);
    for ($i = 0; $i < $length; $i++) {
        $char = $format[$i];
        switch ($char) {
            case 'Y': $output .= $jalali_year; break;
            case 'y': $output .= substr($jalali_year, -2); break;
            case 'm': $output .= sprintf("%02d", $jalali_month); break;
            case 'n': $output .= $jalali_month; break;
            case 'd': $output .= sprintf("%02d", $jalali_day); break;
            case 'j': $output .= $jalali_day; break;
            case 'H': $output .= sprintf("%02d", $hours); break;
            case 'i': $output .= sprintf("%02d", $minutes); break;
            case 's': $output .= sprintf("%02d", $seconds); break;
            default: $output .= $char;
        }
    }
    return $output;
}

function now()
{
    return jdate('Y/m/d');
}

// تبدیل اعداد انگلیسی به فارسی
function fa_number($number) {
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($english, $persian, $number);
}
function faToEn($str) {
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $en = ['0','1','2','3','4','5','6','7','8','9'];

    return str_replace($fa, $en, $str);
}
// ============================================
// تبدیل تاریخ شمسی به تایم‌استامپ (برای ذخیره در دیتابیس)
// ============================================

function jmktime($hour, $minute, $second, $month, $day, $year) {
    // ابتدا تاریخ شمسی را به میلادی تبدیل می‌کنیم
    $gregorian = jalali_to_gregorian($year, $month, $day);

    // سپس تایم‌استامپ میلادی را ایجاد می‌کنیم
    $timestamp = mktime($hour, $minute, $second, $gregorian[1], $gregorian[2], $gregorian[0]);

    return $timestamp;
}

// ============================================
// تبدیل تاریخ شمسی به میلادی
// ============================================

function jalali_to_gregorian($jy, $jm, $jd) {
    $jy = (int)$jy;
    $jm = (int)$jm;
    $jd = (int)$jd;

    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

    // محاسبه کبیسه بودن سال شمسی
    $leap = ($jy % 33 == 1 || $jy % 33 == 5 || $jy % 33 == 9 ||
        $jy % 33 == 13 || $jy % 33 == 17 || $jy % 33 == 22 ||
        $jy % 33 == 26 || $jy % 33 == 30);

    if ($leap) {
        $j_days_in_month[11] = 30;
    }

    $jy -= 979;
    $j_day_no = 365 * $jy + floor($jy / 33) * 8 + floor((($jy % 33) + 3) / 4);
    for ($i = 0; $i < $jm - 1; ++$i) {
        $j_day_no += $j_days_in_month[$i];
    }

    $j_day_no += $jd;

    $g_day_no = $j_day_no + 79;

    $gy = 1600 + 400 * floor($g_day_no / 146097);
    $g_day_no = $g_day_no % 146097;

    $leap = true;
    if ($g_day_no >= 36525) {
        $g_day_no--;
        $gy += 100 * floor($g_day_no / 36524);
        $g_day_no = $g_day_no % 36524;
        if ($g_day_no >= 365) {
            $g_day_no++;
        } else {
            $leap = false;
        }
    }

    $gy += 4 * floor($g_day_no / 1461);
    $g_day_no %= 1461;

    if ($g_day_no >= 366) {
        $leap = true;
        $g_day_no--;
        $gy += floor($g_day_no / 365);
        $g_day_no = $g_day_no % 365;
    } else {
        $leap = false;
    }

    $g_days_in_month = array(31, 28 + ($leap ? 1 : 0), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    for ($i = 0; $i < 12 && $g_day_no >= $g_days_in_month[$i]; ++$i) {
        $g_day_no -= $g_days_in_month[$i];
    }
    $gm = $i + 1;
    $gd = $g_day_no + 1;

    return array($gy, $gm, $gd);
}
?>