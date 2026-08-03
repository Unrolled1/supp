<?php

function getReportData($type,$db,$filters){
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

function getTicketReport($db,$filters){

    $department_id = $filters['department_id'] ?? '';
    $status        = $filters['status'] ?? '';
    $date_from = $filters['date_from'] ?? '';
    $date_to   = $filters['date_to'] ?? '';

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

    $display_date_from = $date_from;
 $display_date_to   = $date_to;

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
        'filterInfo'   => $filterText
    ];
}

function getServiceReport($db, $filters){

    $department_id  = $filters['department_id'] ?? '';
    $service_name   = $filters['service_name'] ?? '';
    $date_from      = $filters['date_from'] ?? '';
    $date_to        = $filters['date_to'] ?? '';
 
    $selectedColumns = $filters['columns'] ?? [
        'service_name',
        'department_name',
        'brand_name',
        'receiver_name',
        'serial_number',
        'computer_code',
        'created_at'
    ];

    $availableColumns = [
        'service_name'    => 'فعالیت',
        'department_name' => 'بخش',
        'brand_name'      => 'برند',
        'receiver_name'   => 'تحویل گیرنده',
        'serial_number'   => 'سریال',
        'computer_code'   => 'کد رایانه',
        'created_at'      => 'تاریخ ثبت'
    ];

    $whereConditions = [];
    $params = [];

    if (!empty($filters['service_name'])) {
        $whereConditions[] = "sr.service_name = :service_name";
        $params[':service_name'] = $filters['service_name'];
    }

    if (!empty($filters['department_id'])) {
        $whereConditions[] = "sr.department_id = :department_id";
        $params[':department_id'] = $filters['department_id'];
    }

    if (!empty($filters['date_from'])) {
        $whereConditions[] = "sr.created_at >= :date_from";
        $params[':date_from'] = $filters['date_from'];
    }

    if (!empty($filters['date_to'])) {
        $whereConditions[] = "sr.created_at <= :date_to";
        $params[':date_to'] = $filters['date_to'];
    }

    $whereSql = $whereConditions
        ? "WHERE " . implode(" AND ", $whereConditions)
        : "";

    $sql = "
        SELECT
            sr.*,
            d.name AS department_name,
            b.name AS brand_name,
            rp.name AS receiver_name,
            u.fullname AS creator_name
        FROM service_requests sr
        LEFT JOIN departments d ON d.id = sr.department_id
        LEFT JOIN brands b ON b.id = sr.brand_id
        LEFT JOIN persons rp ON rp.id = sr.receiver_person_id
        LEFT JOIN users u ON u.id = sr.created_by
        $whereSql
        ORDER BY sr.created_at DESC
    ";

 $stmt = $db->prepare($sql);
 $stmt->execute($params);
 $services = $stmt->fetchAll();

    $department_name = '';

 if (!empty($department_id)) {
    $stmt = $db->prepare("SELECT name FROM departments WHERE id = ?");
    $stmt->execute([$department_id]);
    $department_name = $stmt->fetchColumn();
 }
 $display_date_from = !empty($date_from) ? fa_number($date_from) : '';
 $display_date_to   = !empty($date_to) ? fa_number($date_to) : '';
 // ساخت اطلاعات فیلترها برای نمایش
 $filterItems = [];

 if (!empty($service_name))
    $filterItems[] = "<span>فعالیت:</span> " . htmlspecialchars($service_name);

 if (!empty($department_name))
    $filterItems[] = "<span>بخش:</span> " . htmlspecialchars($department_name);

 if (!empty($display_date_from))
    $filterItems[] = "<span>از تاریخ:</span> " . htmlspecialchars($display_date_from);

 if (!empty($display_date_to))
    $filterItems[] = "<span>تا تاریخ:</span> " . htmlspecialchars($display_date_to);

 if (!empty($filterItems)) {
    $filterText = "🔍 فیلترهای اعمال شده: " . implode(" | ", $filterItems);
 } else {
    $filterText = "📋 نمایش همه فعالیت‌ها";
 }

    $headers = ['ردیف'];

 foreach ($selectedColumns as $col) {
    if (isset($availableColumns[$col])) {
        $headers[] = $availableColumns[$col];
    }
 }

 $rows = [];

 $i = 1;

 foreach ($services as $s) {

    $row = [fa_number($i++)];

    foreach ($selectedColumns as $col) {

        $value = $s[$col] ?? '-';

        if ($col == 'created_at' && $value != '-') {
            $value = fa_number($value);
        }

        $row[] = htmlspecialchars((string)$value);
    }

    $rows[] = $row;
 }

 return [
    'pageTitle'    => 'گزارش فعالیت',
    'tableHeaders' => $headers,
    'tableRows'    => $rows,
    'filterInfo'   => $filterText
 ];
}

function getInvoiceReport($db,$filters){
    $date_from = faToEn($_POST['date_from'] ?? '');
    $date_to   = faToEn($_POST['date_to'] ?? '');

     
    // ساخت کوئری شرطی
    $whereConditions = [];
    $params = [];

    if (!empty($date_from)) {
        $whereConditions[] = "i.created_at >= :date_from";
        $params[':date_from'] = $date_from;
    }

    if (!empty($date_to)) {
        $whereConditions[] = "i.created_at <= :date_to";
        $params[':date_to'] = $date_to;
    }

    $selectedColumns = $_POST['columns'] ?? [
            'company_name',
            'invoice_number',
        'subject',
        'amount',
        'description',
        'created_at'
    ];
     $availableColumns = [
        'company_name'    => 'نام شرکت',
        'invoice_number' => 'شماره فاکتور',
        'subject'      => 'موضوع فاکتور',
        'amount'   => 'مبلغ فاکتور',
        'description'   => 'توضیحات',
        'created_at'      => 'تاریخ ثبت'
    ];

    $whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

    // گرفتن تیکت‌ها
    $sql = "
        SELECT
        i.*,
    u.fullname AS creator_name
    FROM invoices i
    LEFT JOIN users u
        ON u.id = i.created_by
        $whereSql
    ORDER BY i.created_at DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $invoices = $stmt->fetchAll();

    // ساخت تاریخ نمایشی
    $display_date_from = !empty($date_from) ? fa_number($date_from) : '';
    $display_date_to   = !empty($date_to) ? fa_number($date_to) : '';

    // ساخت اطلاعات فیلترها برای نمایش
    $filterText = '';
    $filters = [];
    if (!empty($company_name)) $filters[] = "<span>نام شرکت:</span> " . htmlspecialchars($company_name);
    if (!empty($invoice_number)) $filters[] = "<span>شماره فاکتور:</span> " . htmlspecialchars($invoice_number);
    if (!empty($display_date_from)) $filters[] = "<span>از تاریخ:</span> " . htmlspecialchars($display_date_from);
    if (!empty($display_date_to)) $filters[] = "<span>تا تاریخ:</span> " . htmlspecialchars($display_date_to);

    if (!empty($filterItems)) {
    $filterText = "🔍 فیلترهای اعمال شده: " . implode(" | ", $filterItems);
 } else {
    $filterText = "📋 نمایش همه فاکتور ها";
 }

    $headers = ['ردیف'];

 foreach ($selectedColumns as $col) {
    if (isset($availableColumns[$col])) {
        $headers[] = $availableColumns[$col];
    }
 }

 $rows = [];

 $i = 1;

 foreach ($invoices as $invoice) {

    $row = [fa_number($i++)];

    foreach ($selectedColumns as $col) {

        $value = $invoice[$col] ?? '-';

        if ($col == 'created_at' && $value != '-') {
            $value = fa_number($value);
        }

        $row[] = htmlspecialchars((string)$value);
    }

    $rows[] = $row;
 }

 return [
    'pageTitle'    => 'گزارش فاکتور',
    'tableHeaders' => $headers,
    'tableRows'    => $rows,
    'filterInfo'   => $filterText
 ];
 
}

function getKalaReport($db, $filters)
 {
    $department_id = $filters['department_id'] ?? '';
    $brand_id      = $filters['brand_id'] ?? '';
    $date_from     = $filters['date_from'] ?? '';
    $date_to       = $filters['date_to'] ?? '';

    $selectedColumns = $filters['columns'] ?? [
        'computer_code',
        'property_code',
        'name',
        'department_name',
        'receiver_name',
        'quantity',
        'brand_name',
        'serial_number',
        'created_at'
    ];

    $availableColumns = [
        'computer_code'   => 'کد رایانه',
        'property_code'   => 'کد اموال',
        'name'            => 'نام کالا',
        'department_name' => 'بخش',
        'receiver_name'   => 'تحویل گیرنده',
        'quantity'        => 'تعداد',
        'brand_name'      => 'برند',
        'serial_number'   => 'سریال',
        'created_at'      => 'تاریخ ثبت'
    ];

    $whereConditions = [];
    $params = [];

    if (!empty($department_id)) {
        $whereConditions[] = "k.department_id = :department_id";
        $params[':department_id'] = $department_id;
    }

    if (!empty($brand_id)) {
        $whereConditions[] = "k.brand_id = :brand_id";
        $params[':brand_id'] = $brand_id;
    }

    if (!empty($date_from)) {
        $whereConditions[] = "k.created_at >= :date_from";
        $params[':date_from'] = $date_from;
    }

    if (!empty($date_to)) {
        $whereConditions[] = "k.created_at <= :date_to";
        $params[':date_to'] = $date_to;
    }

    $whereSql = !empty($whereConditions)
        ? "WHERE " . implode(" AND ", $whereConditions)
        : "";

    $sql = "
        SELECT
            k.*,
            d.name AS department_name,
            p.name AS receiver_name,
            b.name AS brand_name,
            u.fullname AS creator_name
        FROM kala k
        LEFT JOIN departments d
            ON d.id = k.department_id
        LEFT JOIN persons p
            ON p.id = k.receiver_person_id
        LEFT JOIN brands b
            ON b.id = k.brand_id
        LEFT JOIN users u
            ON u.id = k.created_by
        $whereSql
       ORDER BY k.created_at DESC, k.id DESC  ";


    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $kalas = $stmt->fetchAll();

    $department_name = '';
$brand_name = '';

if (!empty($department_id)) {
    $stmt = $db->prepare("SELECT name FROM departments WHERE id=?");
    $stmt->execute([$department_id]);
    $department_name = $stmt->fetchColumn();
}

if (!empty($brand_id)) {
    $stmt = $db->prepare("SELECT name FROM brands WHERE id=?");
    $stmt->execute([$brand_id]);
    $brand_name = $stmt->fetchColumn();
}
$display_date_from = !empty($date_from) ? fa_number($date_from) : '';
    $display_date_to   = !empty($date_to) ? fa_number($date_to) : '';
    $filterItems = [];

    if ($department_name)
    $filterItems[] = "<span>بخش:</span> ".htmlspecialchars($department_name);

    if ($brand_name)
        $filterItems[] = "<span>برند:</span> ".htmlspecialchars($brand_name);

    if ($display_date_from)
        $filterItems[] = "<span>از تاریخ:</span> ".$display_date_from;

    if ($display_date_to)
    $filterItems[] = "<span>تا تاریخ:</span> ".$display_date_to;

    $filterText = !empty($filterItems)
        ? "🔍 فیلترهای اعمال شده: " . implode(" | ", $filterItems)
        : "📋 نمایش همه کالاها";

    $headers = ['ردیف'];

    foreach ($selectedColumns as $col) {

    if (!isset($availableColumns[$col])) {
        continue;
    }

    $value = $kala[$col] ?? '-';

    if (in_array($col, ['created_at', 'quantity']) && $value != '-') {
        $value = fa_number($value);
    }

    $row[] = htmlspecialchars((string)$value);
}

    $rows = [];
    $i = 1;

    foreach ($kalas as $kala) {

        $row = [fa_number($i++)];

        foreach ($selectedColumns as $col) {

            $value = $kala[$col] ?? '-';

            if ($col == 'created_at' && $value != '-') {
                $value = fa_number($value);
            }

            if ($col == 'quantity' && $value != '-') {
                $value = fa_number($value);
            }

            $row[] = htmlspecialchars((string)$value);
        }

        $rows[] = $row;
    }

    return [
        'pageTitle'    => 'گزارش کالاها',
        'tableHeaders' => $headers,
        'tableRows'    => $rows,
        'filterInfo'   => $filterText
    ];
}

function getPrinterReport($db, $filters)
{
    $department_id = $filters['department_id'] ?? '';
    $brand_id      = $filters['brand_id'] ?? '';
    $activity_id   = $filters['activity_id'] ?? '';
    $date_from     = $filters['date_from'] ?? '';
    $date_to       = $filters['date_to'] ?? '';

    $selectedColumns = $filters['columns'] ?? [
        'computer_code',
        'property_code',
        'activity_name',
        'department_name',
        'brand_name',
        'serial_number',
        'description',
        'created_at'
    ];

    $availableColumns = [
        'computer_code'  => 'کد رایانه',
        'property_code'  => 'کد اموال',
        'activity_name'  => 'فعالیت',
        'department_name'=> 'بخش',
        'brand_name'     => 'برند',
        'serial_number'  => 'سریال',
        'description'    => 'توضیحات',
        'created_at'     => 'تاریخ ثبت'
    ];

    $where = [];
    $params = [];

    if(!empty($department_id)){
        $where[] = "p.department_id = :department_id";
        $params[':department_id'] = $department_id;
    }

    if(!empty($brand_id)){
        $where[] = "p.brand_id = :brand_id";
        $params[':brand_id'] = $brand_id;
    }

    if(!empty($activity_id)){
        $where[] = "p.activity_id = :activity_id";
        $params[':activity_id'] = $activity_id;
    }

    if(!empty($date_from)){
        $where[] = "p.created_at >= :date_from";
        $params[':date_from'] = $date_from;
    }

    if(!empty($date_to)){
        $where[] = "p.created_at <= :date_to";
        $params[':date_to'] = $date_to;
    }

    $whereSql = $where ? "WHERE ".implode(" AND ",$where) : "";

    $sql = "
        SELECT
            p.*,
            d.name AS department_name,
            b.name AS brand_name,
            a.name AS activity_name,
            u.fullname AS creator_name
        FROM printers p
        LEFT JOIN departments d ON d.id = p.department_id
        LEFT JOIN brands b ON b.id = p.brand_id
        LEFT JOIN activities a ON a.id = p.activity_id
        LEFT JOIN users u ON u.id = p.created_by
        $whereSql
        ORDER BY p.created_at DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $printers = $stmt->fetchAll();

    $department_name = '';
    if(!empty($department_id)){
        $stmt = $db->prepare("SELECT name FROM departments WHERE id=?");
        $stmt->execute([$department_id]);
        $department_name = $stmt->fetchColumn();
    }

    $brand_name = '';
    if(!empty($brand_id)){
        $stmt = $db->prepare("SELECT name FROM brands WHERE id=?");
        $stmt->execute([$brand_id]);
        $brand_name = $stmt->fetchColumn();
    }

    $activity_name = '';
    if(!empty($activity_id)){
        $stmt = $db->prepare("SELECT name FROM activities WHERE id=?");
        $stmt->execute([$activity_id]);
        $activity_name = $stmt->fetchColumn();
    }

    $filterItems = [];

    if(!empty($activity_name))
        $filterItems[] = "<span>فعالیت:</span> ".htmlspecialchars($activity_name);

    if(!empty($department_name))
        $filterItems[] = "<span>بخش:</span> ".htmlspecialchars($department_name);

    if(!empty($brand_name))
        $filterItems[] = "<span>برند:</span> ".htmlspecialchars($brand_name);

    if(!empty($date_from))
        $filterItems[] = "<span>از تاریخ:</span> ".htmlspecialchars(fa_number($date_from));

    if(!empty($date_to))
        $filterItems[] = "<span>تا تاریخ:</span> ".htmlspecialchars(fa_number($date_to));

    $filterText = empty($filterItems)
        ? "📋 نمایش همه پرینترها"
        : "🔍 فیلترهای اعمال شده: ".implode(" | ",$filterItems);

    $headers = ['ردیف'];

    foreach($selectedColumns as $col){
        if(isset($availableColumns[$col])){
            $headers[] = $availableColumns[$col];
        }
    }

    $rows = [];
    $i = 1;

    foreach($printers as $printer){

        $row = [fa_number($i++)];

        foreach($selectedColumns as $col){

            $value = $printer[$col] ?? '-';

            if($col == 'created_at' && $value != '-'){
                $value = fa_number($value);
            }

            $row[] = htmlspecialchars((string)$value);
        }

        $rows[] = $row;
    }

    return [
        'pageTitle'    => 'گزارش پرینترها',
        'tableHeaders' => $headers,
        'tableRows'    => $rows,
        'filterInfo'   => $filterText,
        'autoPrint'    => false
    ];
}

function getSystemReport($db, $filters){
   $department_id = $filters['department_id'] ?? '';
$cpu_id        = $filters['cpu_id'] ?? '';
$date_from     = $filters['date_from'] ?? '';
$date_to       = $filters['date_to'] ?? '';

$selectedColumns = $_POST['columns'] ?? [
    'computer_code',
    'property_code',
    'name',
    'department_name',
    'cpu_name',
    'motherboard_name',
    'power_name',
    'monitor_name',
    'rams',
    'storages',
    'ip_addresses',
    'peripherals',
    'created_at'
];
    $availableColumns = [
    'computer_code'    => 'کد رایانه',
    'property_code'    => 'کد اموال',
    'name'             => 'نام سیستم',
    'department_name'  => 'بخش',
    'cpu_name'         => 'پردازنده',
    'motherboard_name' => 'مادربرد',
    'power_name'       => 'پاور',
    'monitor_name'     => 'مانیتور',
    'rams'             => 'رم‌ها',  
    'storages'         => 'هاردها', 
    'ip_addresses'     => 'IP',
    'peripherals'      => 'تجهیزات جانبی',
    'created_at'       => 'تاریخ ثبت'
];

    $whereConditions = [];
    $params = [];
    
    if (!empty($department_id)) {
    $whereConditions[] = "s.department_id = :department_id";
    $params[':department_id'] = $department_id;
    }

    if (!empty($date_from)) {
        $whereConditions[] = "s.created_at >= :date_from";
        $params[':date_from'] = $date_from;
    }

    if (!empty($date_to)) {
        $whereConditions[] = "s.created_at <= :date_to";
        $params[':date_to'] = $date_to;
    }

    $whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

$sql = "
    SELECT
        s.*,
        d.name AS department_name,
        cpu_b.name AS cpu_brand,
        cpu_m.name AS cpu_model,
        CONCAT(cpu_b.name,' ',cpu_m.name) AS cpu_name,
        CONCAT(mb_b.name,' ',mb_m.name) AS motherboard_name,
        CONCAT(pw_b.name,' ',pw_m.name) AS power_name,
        CONCAT(mn_b.name,' ',mn_m.name,
            IF(mn.property_code IS NOT NULL,
               CONCAT(' - ',mn.property_code),
               '')
        ) AS monitor_name,
        -- دریافت رم‌ها به صورت مستقیم
        (
            SELECT GROUP_CONCAT(
                CONCAT(
                    rb.name, ' ',
                    rm.name, ' (',
                    r.capacity, ' ',
                    r.type, ')'
                )
                SEPARATOR ' , '
            )
            FROM system_rams sr
            JOIN rams r ON sr.ram_id = r.id
            JOIN models rm ON r.model_id = rm.id
            JOIN brands rb ON rm.brand_id = rb.id
            WHERE sr.system_id = s.id
        ) AS rams,
        -- دریافت هاردها به صورت مستقیم
        (
            SELECT GROUP_CONCAT(
                CONCAT(
                    sb.name, ' ',
                    sm.name, ' (',
                    st.capacity, ' ',
                    st.type, ')'
                )
                SEPARATOR ' , '
            )
            FROM system_storages ss
            JOIN storages st ON ss.storage_id = st.id
            JOIN models sm ON st.model_id = sm.id
            JOIN brands sb ON sm.brand_id = sb.id
            WHERE ss.system_id = s.id
        ) AS storages,
 -- دریافت IPها با اطلاعات بیشتر
        (
            SELECT GROUP_CONCAT(
                CONCAT(
                    si.ip_address,
                    IF(si.network_type IS NOT NULL,
                       CONCAT(' (', si.network_type, ')'),
                       ''),
                    IF(si.description IS NOT NULL,
                       CONCAT(' - ', si.description),
                       '')
                )
                SEPARATOR ' , '
            )
            FROM system_ips si
            WHERE si.system_id = s.id
        ) AS ip_addresses,
        
        -- دریافت تجهیزات جانبی با اطلاعات کامل
        (
            SELECT GROUP_CONCAT(
                CONCAT(
                    pt.name,
                    ': ',
                    pb.name, ' ',
                    pm.name,
                    IF(p.property_code IS NOT NULL,
                       CONCAT(' (', p.property_code, ')'),
                       ''),
                    IF(p.connection_type IS NOT NULL,
                       CONCAT(' [', p.connection_type, ']'),
                       '')
                )
                SEPARATOR ' , '
            )
            FROM system_peripherals sp
            JOIN peripherals p ON sp.peripheral_id = p.id
            JOIN peripheral_types pt ON p.type_id = pt.id
            JOIN models pm ON p.model_id = pm.id
            JOIN brands pb ON pm.brand_id = pb.id
            WHERE sp.system_id = s.id
        ) AS peripherals
    FROM systems s

    LEFT JOIN departments d
        ON d.id = s.department_id

    LEFT JOIN cpus c
        ON c.id = s.cpu_id
    LEFT JOIN models cpu_m
        ON c.model_id = cpu_m.id
    LEFT JOIN brands cpu_b
        ON c.brand_id = cpu_b.id

    LEFT JOIN motherboards mb
        ON mb.id = s.motherboard_id
    LEFT JOIN models mb_m
        ON mb.model_id = mb_m.id
    LEFT JOIN brands mb_b
        ON mb.brand_id = mb_b.id

    LEFT JOIN powers pw
        ON pw.id = s.power_id
    LEFT JOIN models pw_m
        ON pw.model_id = pw_m.id
    LEFT JOIN brands pw_b
        ON pw.brand_id = pw_b.id

    LEFT JOIN monitors mn
        ON mn.id = s.monitor_id
    LEFT JOIN models mn_m
        ON mn.model_id = mn_m.id
    LEFT JOIN brands mn_b
        ON mn.brand_id = mn_b.id

    LEFT JOIN system_ips si
        ON si.system_id = s.id

    LEFT JOIN users u
        ON u.id = s.created_by

    LEFT JOIN system_peripherals sp
        ON sp.system_id = s.id

    LEFT JOIN peripherals p
        ON p.id = sp.peripheral_id

    LEFT JOIN peripheral_types pt
        ON pt.id = p.type_id

    LEFT JOIN brands pb
        ON pb.id = p.brand_id

    LEFT JOIN models pm
        ON pm.id = p.model_id

    $whereSql

    GROUP BY s.id
    ORDER BY s.created_at DESC
";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $systems = $stmt->fetchAll();


   $filterItems = [];

if(!empty($department_id)){
    $stmt = $db->prepare("SELECT name FROM departments WHERE id=?");
    $stmt->execute([$department_id]);
    $filterItems[] = "<span>بخش:</span> ".$stmt->fetchColumn();
}

if(!empty($date_from))
    $filterItems[] = "<span>از تاریخ:</span> ".fa_number($date_from);

if(!empty($date_to))
    $filterItems[] = "<span>تا تاریخ:</span> ".fa_number($date_to);

    $filterText = empty($filterItems)
        ? "📋 نمایش همه سیستم ها"
        : "🔍 فیلترهای اعمال شده: ".implode(" | ",$filterItems);

    $headers = ['ردیف'];

    foreach($selectedColumns as $col){
            if(isset($availableColumns[$col])){
                $headers[] = $availableColumns[$col];
            }
        }
        $rows = [];
        $i = 1;

    foreach($systems as $system){

            $row = [fa_number($i++)];

        foreach($selectedColumns as $col){

            $value = $system[$col] ?? '-';

            if($col == 'created_at' && $value != '-'){
                $value = fa_number($value);
            }

            $row[] = htmlspecialchars((string)$value);
        }

        $rows[] = $row;
    }
   return [
        'pageTitle'    => 'گزارش سیستم ها',
        'tableHeaders' => $headers,
        'tableRows'    => $rows,
        'filterInfo'   => $filterText,
        'autoPrint'    => false
    ]; 
}



?>