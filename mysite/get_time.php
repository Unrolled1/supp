<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// مسیر فایل jdf.php (همون پوشه)
require_once __DIR__ . '/assets/jdf.php';

$response = [
    'datetime' => fa_number(now()),
    'date' => fa_number(jdate('Y-m-d')),
    'time' => fa_number(jdate('H:i:s'))
];

echo json_encode($response);
?>