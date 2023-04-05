<?php
if ($_SERVER["REQUEST_METHOD"] != "GET") die();

require_once "connect.php";
/** @var PDO $connect */

$word = $_GET["word"];
$length = strlen($word);
if ($length < 3) {
    $response = [
        "status" => false,
        "message" => "Не удалось найти рифму к слову " . $word
    ];
    echo json_encode($response);
    die();
}

if ($length == 3) $end = mb_substr($word, -2, null,'UTF-8');
else $end = mb_substr($word, -3, null,'UTF-8');

// 1. Найти все словари
$dictionaries = array();
$stmt = $connect->query("SELECT `id` FROM `dictionaries`");
while ($row = $stmt->fetch()) $dictionaries[] = $row["id"];

// 2. Сформировать строку запроса по всем словарям
$query = "";
$count = count($dictionaries);
foreach ($dictionaries as $index => $dictionary) {
    $query .= "SELECT * FROM " . "dictionary_" . $dictionary . " WHERE word LIKE '%$end' AND word != '$word'";
    if ($index != $count - 1) $query .= " UNION ";
}

// 3. Выполнить запрос, получить данные
$stmt = $connect->query($query);
$results = [];
$count = $stmt->rowCount();
if ($count > 0)
    while ($row = $stmt->fetch()) $results[] = $row;

// 4. Вернуть данные
$html_string = "<div class='container mt-3'>";
$html_string .= "<h2 class='text-center'>Результаты поиска:</h2>";
if ($count > 0) {
    $html_string .= "<div style='line-height: 1.4em;'>";
    for ($i = 0; $i < count($results); $i++) $html_string .= $results[$i]["word"] . " ";
    $html_string .= "</div>";
} else {
    $html_string .= "<p>Не найдены подходящие результаты для указанного запроса.</p>";
    $html_string .= "</div>";
}

$response = [
    "status" => true,
    "message" => $html_string,
    "query" => $query
];
echo json_encode($response);