<?php
if ($_SERVER["REQUEST_METHOD"] != "POST") die();

require_once "../../class/DbConnect.php";

$type = $_POST['type'];
$id = $_POST['id'];
$language = $_POST['language'];
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
        $base = getPatternBase($language, "/../../languages.json");
        $pattern = makePattern($base, "^[", "]+$", "i");
        // Проверка введённых слов на соответствие шаблону
        foreach ($words as $word) validateWord($word, $pattern);

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
} else if ($type == 'update') {
    $oldWord = $_POST['oldWord'];
    $newWord = $_POST['newWord'];

    try {
        $stmt = $pdo->prepare("UPDATE $dictionary SET `word` = :newWord WHERE `word` = :oldWord");
        $stmt->bindParam(':newWord', $newWord);
        $stmt->bindParam(':oldWord', $oldWord);
        $stmt->execute();
        if ($stmt->rowCount() == 0) errorHandler("Указанное слово не найдено.");
    } catch (PDOException $e) {
        errorHandler("Ошибка при редактировании слова. Возможно, такое слово уже имеется в словаре.");
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

// Проверка слова на корректность введённых данных
function validateWord(string $word, string $pattern): void
{
    if (!empty($word) && !preg_match($pattern, $word)) {
        $error = [
            "status" => false,
            "message" => "Проверьте правильность введённых слов."
        ];
        echo json_encode($error);
        die();
    }
}

// Получение из json шаблона для текущего языка поиска
function getPatternBase($language, string $path): string
{
    $json = file_get_contents(__DIR__ . $path);
    $data = json_decode($json, true);
    return $data[$language]["regexp"];
}

// Полный шаблон regexp с флагами
function makePattern(string $base, string $prefix, string $postfix, string $flags): string
{
    return '/' . $prefix . $base . $postfix . '/' . $flags;
}