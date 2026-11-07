<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/jdf.php';
require_once __DIR__ . '/report_queries.php';

$db = getDB();

$type = $_POST['type'] ?? $_GET['type'] ?? '';
$data = getReportData($type, $db, $_POST);


if (!$data) {
    exit('نوع گزارش نامعتبر است');
}

extract($data);


require_once __DIR__ . '/report_template.php';
