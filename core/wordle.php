<?php
if ($_SERVER["REQUEST_METHOD"] != "GET") die();

require_once "../class/DbConnect.php";

$language = $_GET["language"];
$length = $_GET["length"];
$dbConnect = new DbConnect("user", "");
$pdo = $dbConnect->getPDO();

try {
    $stmt = $pdo->prepare("SELECT id FROM `dictionaries` WHERE language = :language ORDER BY RAND() LIMIT 1");
    $stmt->bindParam(':language', $language);
    $stmt->execute();
} catch (PDOException $e) {
    errorHandler("Ошибка при поиске словаря.");
}

if ($stmt->rowCount() <= 0) {
    errorHandler("Нет словарей с указанным языком");
}

$row = $stmt->fetch(PDO::FETCH_ASSOC);
$dictionary = "dictionary_" . $row["id"];

try {
    $stmt = $pdo->prepare("SELECT word FROM $dictionary WHERE CHAR_LENGTH(word) = :length ORDER BY RAND() LIMIT 1");
    $stmt->bindParam(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
} catch (PDOException $e) {
    errorHandler("Ошибка при поиске слова");
}

if ($stmt->rowCount() <= 0) {
    errorHandler("Отсутствуют слова указанной длины");
}

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$response = [
    "status" => true,
    "word" => $row["word"]
];
echo json_encode($response);

$dbConnect->closeConnection();

/**
 * Обработчик ошибок при выполнении запроса к БД
 * @param $message string сообщение, описывающее ошибку
 * @return void
 */
function errorHandler(string $message): void
{
    $response = [
        "status" => false,
        "message" => $message
    ];

    echo json_encode($response);
    die();
}
