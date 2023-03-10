<?php

require_once "connect.php";
/** @var PDO $connect */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['type'];

    if ($type == 'add') {
        $words = $_FILES['words'];

        // Создаём запись о новом словаре
        try {
            $stmt = $connect->prepare("INSERT INTO `dictionaries` (`name`, `language`) VALUES (:name, :language)");
            $stmt->bindParam(':name', $_POST['name']);
            $stmt->bindParam(':language', $_POST['language']);
            $stmt->execute();
        } catch (PDOException $e) {
            errorHandler("Ошибка при добавлении словаря. Возможно, словарь с таким именем уже существует.");
        }

        // Создаём таблицу для словаря
        try {
            $id = $connect->lastInsertId();
            $dictionary = "dictionary_" . $id;
            $query = "CREATE TABLE " . $dictionary . "( `word` VARCHAR(32) NOT NULL , UNIQUE (`word`)) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";
            $connect->query($query);
        } catch (PDOException $e) {
            errorHandler("Ошибка при создании таблицы для словаря.");
        }

        // Загружаем слова в созданную таблицу
        try {
            $stmt = $connect->prepare("LOAD DATA INFILE :words IGNORE INTO TABLE $dictionary FIELDS TERMINATED BY ' ';");
            $stmt->bindParam(':words', $words['name']);
            $stmt->execute();
        } catch (PDOException $e) {
            errorHandler("Ошибка при добавлении слов. Проверьте загруженный файл.");
        }

        // Записываем количество добавленных слов в словарь
        try {
            $query = "SELECT COUNT(*) FROM " . $dictionary;
            $count = $connect->query($query)->fetchColumn();

            $stmt = $connect->prepare("UPDATE `dictionaries` SET `count` = :count WHERE id = :id;");
            $stmt->bindParam(':count', $count);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            errorHandler("Ошибка при обновлении счётчика слов в словаре.");
        }
    } else if ($type == 'delete') {
        $id = $_POST['id'];

        // Поиск записи о словаре с введённым id
        try {
            $stmt = $connect->prepare("SELECT id FROM `dictionaries` WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) errorHandler("Словарь с указанным id не найден!");
        } catch (PDOException $e) {
            errorHandler("Ошибка при поиске словаря.");
        }

        // Удаление записи о словаре
        try {
            $stmt = $connect->prepare("DELETE FROM `dictionaries` WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            errorHandler("Ошибка при удалении записи о словаре.");
        }

        // Удаление самого словаря
        try {
            $dictionary = "dictionary_" . $id;
            $connect->query("DROP TABLE " . $dictionary);
        } catch (PDOException $e) {
            errorHandler("Ошибка при удалении словаря. Словарь с таким именем не найден.");
        }
    }

    $response = [
        "status" => true
    ];
    echo json_encode($response);
}

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