<?php
require_once "connect.php";
/** @var PDO $connect */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['type'];

    // TODO: error handling

    if ($type == 'add') {
        $words = $_FILES['words'];

        $stmt = $connect->prepare("INSERT INTO `dictionaries` (`name`, `language`) VALUES (:name, :language)");
        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':language', $_POST['language']);
        $stmt->execute();
        $id = $connect->lastInsertId();

        $dictionary = "dictionary_" . $id;
        $query = "CREATE TABLE " . $dictionary . "( `word` VARCHAR(32) NOT NULL , UNIQUE (`word`)) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $connect->query($query);

        $stmt = $connect->prepare("LOAD DATA INFILE :words IGNORE INTO TABLE $dictionary FIELDS TERMINATED BY ' ';");
        $stmt->bindParam(':words', $words['name']);
        $stmt->execute();

        $query = "SELECT COUNT(*) FROM " . $dictionary;
        $count = $connect->query($query)->fetchColumn();

        $stmt = $connect->prepare("UPDATE `dictionaries` SET `count` = :count WHERE id = :id;");
        $stmt->bindParam(':count', $count);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $response = [
            "status" => true
        ];
        echo json_encode($response);

    } else if ($type == 'delete') {
        $id = $_POST['id'];

        $stmt = $connect->prepare("SELECT id FROM `dictionaries` WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $response = [
                "status" => false,
                "message" => "Словарь с указанным id не найден!"
            ];

            echo json_encode($response);
            die();
        }

        $stmt = $connect->prepare("DELETE FROM `dictionaries` WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $dictionary = "dictionary_" . $id;
        $connect->query("DROP TABLE " . $dictionary);

        $response = [
            "status" => true
        ];
        echo json_encode($response);
    }
}