<?php
if ($_SERVER["REQUEST_METHOD"] != "GET") die();

require_once "connect.php";
/** @var PDO $connect */

$stmt = $connect->prepare("SELECT `id`, `name`, `count` FROM `dictionaries` WHERE language = :language");
$stmt->bindParam(':language', $_GET['language']);
$stmt->execute();

$results = [];
if ($stmt->rowCount() <= 0) {
    $response = [
      "status" => false
    ];
    echo json_encode($response);
    die();
}

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $results[] = $row;

$response = [
    "status" => true,
    "dictionaries" => json_encode($results)
];
echo json_encode($response);