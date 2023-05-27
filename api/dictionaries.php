<?php

// HTTP-заголовки
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

$api_key = $_SERVER['HTTP_X_API_KEY'] ?? '';

if (empty($api_key)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Отсутствует API ключ'));
    die();
}

require_once "../class/DbConnect.php";
$dbConnect = new DbConnect("user", "");
$pdo = $dbConnect->getPDO();

$stmt = $pdo->prepare("SELECT id FROM users WHERE api_key = :apikey");
$stmt->bindParam(":apikey", $api_key);
$stmt->execute();

if ($stmt->rowCount() <= 0) {
    http_response_code(400);
    echo json_encode(array('error' => 'Некорректный API ключ'));
    die();
}

$dictionaries = [];
$stmt = $pdo->query("SELECT * FROM `dictionaries`");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $dictionaries[] = $row;

http_response_code(200);
echo json_encode($dictionaries);