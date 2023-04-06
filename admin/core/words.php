<?php
if ($_SERVER["REQUEST_METHOD"] != "POST") die();

require_once "../../class/DbConnect.php";

$type = $_POST['type'];
$id = $_POST['id'];
$dictionary = "dictionary_" . $id;
$count = 0;

$dbConnect = new DbConnect("admin", "wordfinder");
$pdo = $dbConnect->getPDO();

if ($type == 'add') {
    $mode = $_POST['mode'];
    if ($mode == 'addFromFile') { // Добавление из файла
        $words = $_FILES['words'];

        try {
            // Вставка слов в таблицу
            $stmt = $pdo->prepare("LOAD DATA INFILE :words IGNORE INTO TABLE $dictionary FIELDS TERMINATED BY '\r';");
            $stmt->bindParam(':words', $words["tmp_name"]);
            $stmt->execute();
            $count = $stmt->rowCount();

            // Обновление счётчика слов в справочнике
            $stmt = $pdo->prepare("UPDATE `dictionaries` SET `count` = `count` + :count WHERE id = :id;");
            $stmt->bindParam(':count', $count, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            errorHandler("Ошибка при добавлении слов. Проверьте загруженный файл.");
        }
    } else if ($mode == 'addFromText') { // Добавление из поля ввода
        $words = $_POST['words'];
        $words = explode(',', $words);

        $values = implode(',', array_map(function ($word) {
            return "('" . $word . "')";
        }, $words));

        try {
            // Вставка слов в таблицу
            $stmt = $pdo->prepare("INSERT IGNORE INTO $dictionary VALUES $values");
            $stmt->execute();
            $count = $stmt->rowCount();

            // Обновление счётчика слов в справочнике
            $stmt = $pdo->prepare("UPDATE `dictionaries` SET `count` = `count` + :count WHERE id = :id;");
            $stmt->bindParam(':count', $count, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            errorHandler("Ошибка при добавлении слов. Проверьте введённые слова.");
        }
    }
} else if ($type == 'delete') {
    $words = $_POST['words'];
    $words = explode(',', $words);

    $values = implode(',', array_map(function ($word) {
        return "'" . $word . "'";
    }, $words));

    try {
        // Удаление слов из таблицы
        $stmt = $pdo->prepare("DELETE FROM $dictionary WHERE `word` IN ($values)");
        $stmt->execute();
        $count = $stmt->rowCount();

        // Обновление счётчика слов в справочнике
        $stmt = $pdo->prepare("UPDATE `dictionaries` SET `count` = `count` - :count WHERE id = :id;");
        $stmt->bindParam(':count', $count, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        errorHandler("Ошибка при удалении слов. Проверьте введённые слова.");
    }
}

$response = [
    "status" => true,
    "count" => $count
];
echo json_encode($response);

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