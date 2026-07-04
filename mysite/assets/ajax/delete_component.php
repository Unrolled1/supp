<?php

require_once __DIR__ . "/../../db.php";
$db=getDB();
header("Content-Type: application/json; charset=utf-8");

$type = $_POST["type"] ?? "";
$id   = (int)($_POST["id"] ?? 0);

$config = [

    "cpu" => "cpus",
    "motherboard" => "motherboards",
    "power" => "powers",
    "gpu" => "gpus",
    "monitor" => "monitors",
    "ram" => "rams",
    "storage" => "storages"

];

if (!isset($config[$type])) {

    echo json_encode([
        "success"=>false,
        "message"=>"نوع نامعتبر"
    ]);

    exit;
}

$table = $config[$type];

$stmt = $db->prepare("DELETE FROM {$table} WHERE id=?");

$success = $stmt->execute([$id]);

echo json_encode([
    "success"=>$success
]);