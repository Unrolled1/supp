<?php

require_once __DIR__ . "/../../db.php";
$db=getDB();
header('Content-Type: application/json; charset=utf-8');

$type = $_GET['type'] ?? '';

$config = [

    'cpu' => [
        'table' => 'cpus'
    ],

    'motherboard' => [
        'table' => 'motherboards'
    ],

    'power' => [
        'table' => 'powers'
    ],

    'peripheral' => [
        'table' => 'peripherals'
    ],

    'monitor' => [
        'table' => 'monitors'
    ],

    'ram' => [
        'table' => 'rams'
    ],

    'storage' => [
        'table' => 'storages'
    ]

];

if (!isset($config[$type])) {
    echo json_encode([]);
    exit;
}
$table = $config[$type]['table'];
if ($type === 'peripheral') {

    $sql = "
    SELECT
        p.id,
        pt.name AS type_name,
        b.name AS brand_name,
        m.name AS model_name,
        p.property_code
    FROM peripherals p
    INNER JOIN models m
        ON p.model_id = m.id
    INNER JOIN brands b
        ON m.brand_id = b.id
    INNER JOIN peripheral_types pt
        ON p.type_id = pt.id
    ORDER BY pt.name, b.name, m.name
    ";

}
elseif ($type === 'ram') {

    $sql = "
    SELECT
        t.id,
        b.name AS brand_name,
        m.name AS model_name,
        t.type,
        t.capacity
    FROM rams t
    INNER JOIN models m ON t.model_id = m.id
    INNER JOIN brands b ON m.brand_id = b.id
    ORDER BY b.name, m.name
    ";

} elseif ($type === 'storage') {

    $sql = "
    SELECT
        t.id,
        b.name AS brand_name,
        m.name AS model_name,
        t.type,
        t.capacity
    FROM storages t
    INNER JOIN models m ON t.model_id = m.id
    INNER JOIN brands b ON m.brand_id = b.id
    ORDER BY b.name, m.name
    ";

} else {

    $sql = "
    SELECT
        t.id,
        b.name AS brand_name,
        m.name AS model_name
    FROM {$table} t
    INNER JOIN models m ON t.model_id = m.id
    INNER JOIN brands b ON m.brand_id = b.id
    ORDER BY b.name, m.name
    ";

}

$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows, JSON_UNESCAPED_UNICODE);