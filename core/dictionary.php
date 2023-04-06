<?php
if ($_SERVER["REQUEST_METHOD"] != "GET") die();

require_once "../class/DbConnect.php";

$dbConnect = new DbConnect("user", "");
$pdo = $dbConnect->getPDO();
$stmt = $pdo->prepare("SELECT `id`, `name`, `count` FROM `dictionaries` WHERE language = :language");
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

$dbConnect->closeConnection();