<?php
session_start();
require_once __DIR__.'/../db.php';
require_once __DIR__.'/../assets/jdf.php';
require_once 'report_queries.php';

$db = getDB();

$type = $_GET['type'] ?? '';

$data = getReportData($type, $db, $_POST);

if (!$data) {
    exit('نوع گزارش نامعتبر است');
}

extract($data);

require 'report_template.php';
