<?php

function getReportData($type,$db,$filters)
{
    switch($type){

        case 'ticket':
            return getTicketReport($db,$filters);

        case 'service':
            return getServiceReport($db,$filters);

        case 'system':
            return getSystemReport($db,$filters);

        case 'printer':
            return getPrinterReport($db,$filters);

        case 'invoice':
            return getInvoiceReport($db,$filters);

        case 'kala':
            return getKalaReport($db,$filters);

        default:
            return false;
    }
}

function getTicketReport($db,$filters)
{
    $department_id = $filters['department_id'] ?? '';
    $status        = $filters['status'] ?? '';
    $date_from = $filters['date_from'] ?? '';
    $date_to   = $filters['date_to'] ?? '';

// ساخت تاریخ کامل برای کوئری
    $date_from = '';
    $date_to = '';

    if (!empty($from_year) && !empty($from_month) && !empty($from_day)) {
        $date_from = sprintf("%04d-%02d-%02d", $from_year, $from_month, $from_day);
    }
    if (!empty($to_year) && !empty($to_month) && !empty($to_day)) {
        $date_to = sprintf("%04d-%02d-%02d", $to_year, $to_month, $to_day);
    }

// ساخت کوئری شرطی
    $whereConditions = [];
    $params = [];

    if (!empty($department_id)) {
        $whereConditions[] = "t.department_id = :department_id";
        $params[':department_id'] = $department_id;
    }
    if (!empty($status)) {
        $whereConditions[] = "t.status = :status";
        $params[':status'] = $status;
    }
    if (!empty($date_from)) {
        $whereConditions[] = "t.created_at >= :date_from";
        $params[':date_from'] = $date_from;
    }
    if (!empty($date_to)) {
        $whereConditions[] = "t.created_at <= :date_to";
        $params[':date_to'] = $date_to;
    }

    $whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// گرفتن تیکت‌ها
    $sql = "
    SELECT t.*, d.name as department_name, u.username, u.fullname as fullname
    FROM tickets t
    LEFT JOIN departments d ON t.department_id = d.id
    LEFT JOIN users u ON t.user_id = u.id
    $whereSql
    ORDER BY t.created_at DESC
";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll();

// آمار
    $total = count($tickets);
    $reviewCount = 0;
    $answeredCount = 0;
    $closedCount = 0;

    foreach ($tickets as $t) {
        switch($t['status']) {
            case 'در حال بررسی': $reviewCount++; break;
            case 'پاسخ داده شده': $answeredCount++; break;
            case 'بسته شده': $closedCount++; break;
        }
    }

    $department_name = '';

    if (!empty($department_id)) {
        $stmt = $db->prepare("SELECT name FROM departments WHERE id = ?");
        $stmt->execute([$department_id]);
        $department_name = $stmt->fetchColumn();
    }
// ساخت تاریخ نمایشی
    $display_date_from = '';
    $display_date_to = '';
    if (!empty($from_year) && !empty($from_month) && !empty($from_day)) {
        $display_date_from = fa_number($from_year) . '/' . fa_number($from_month) . '/' . fa_number($from_day);
    }
    if (!empty($to_year) && !empty($to_month) && !empty($to_day)) {
        $display_date_to = fa_number($to_year) . '/' . fa_number($to_month) . '/' . fa_number($to_day);
    }

// ساخت اطلاعات فیلترها برای نمایش
    $filterItems = [];
    if (!empty($department_name)) $filters[] = "<span>بخش:</span> " . htmlspecialchars($department_name);
    if (!empty($status)) $filters[] = "<span>وضعیت:</span> " . htmlspecialchars($status);
    if (!empty($display_date_from)) $filters[] = "<span>از تاریخ:</span> " . htmlspecialchars($display_date_from);
    if (!empty($display_date_to)) $filters[] = "<span>تا تاریخ:</span> " . htmlspecialchars($display_date_to);

    if (!empty($filters)) {
        $filterText = "🔍 فیلترهای اعمال شده: " . implode(" | ", $filters);
    } else {
        $filterText = "📋 نمایش همه تیکت‌ها";
    }

    $headers = [
        'ردیف',
        'کد پیگیری',
        'بخش',
        'نام و نام خانوادگی',
        'کاربر',
        'موضوع',
        'وضعیت',
        'تاریخ ثبت'
    ];

    $rows = [];

    $i = 1;

    foreach ($tickets as $t) {

        $rows[] = [
            fa_number($i++),
            fa_number($t['tracking_code']),
            htmlspecialchars($t['department_name'] ?? '-'),
            htmlspecialchars($t['fullname']),
            htmlspecialchars($t['username'] ?? '-'),
            htmlspecialchars($t['subject']),
            htmlspecialchars($t['status']),
            fa_number($t['created_at'])
        ];

    }

    $stats = [
        'total' => $total,
        'review' => $reviewCount,
        'answered' => $answeredCount,
        'closed' => $closedCount,

        'labels' => [
            'total' => '📌 کل تیکت‌ها',
            'review' => '🔄 در حال بررسی',
            'answered' => '✅ پاسخ داده شده',
            'closed' => '🔒 بسته شده'
        ]
    ];

    return [
        'pageTitle'    => 'گزارش درخواست‌ها',
        'tableHeaders' => $headers,
        'tableRows'    => $rows,
        'stats'        => $stats,
        'filterInfo'   => $filterText,
        'autoPrint'    => true
    ];
}
?>