<?php
if ($_SERVER["REQUEST_METHOD"] != "GET") die();

require_once "connect.php";
/** @var PDO $connect */
require_once "class/QueryConstructor.php";
require_once "class/Paginator.php";

$query = "";
if (!isset($_GET["query"])) {
    $dictionaries = $_GET["dictionaries"];
    $mode = $_GET["mode"];
    $data = json_decode($_GET['data'], true);
    $compound_words = json_decode($_GET["compound_words"]);
    $constructor = new QueryConstructor($dictionaries, $mode, $data, $compound_words);
    $query = $constructor->constructQuery();
} else $query = $_GET["query"];

// Дополнительные настройки: страница поиска, лимит слов на странице, количество ссылок
$page = (isset($_GET["page"]) && $_GET["page"] > 0) ? $_GET["page"] : 1;
$limit = (isset($_GET["limit"]) && ($_GET["limit"] >= 20 && $_GET["limit"] <= 100)) ? $_GET["limit"] : 20;
$links = (isset($_GET['links'])) ? $_GET['links'] : 4;

if (isset($_GET["sort_type"]) && isset($_GET["sort_order"])) {
    if ($_GET["sort_type"] == "sort-word") $query .= " ORDER BY word ";
    else if ($_GET["sort_type"] == "sort-length") $query .= " ORDER BY CHAR_LENGTH(word) ";
    if ($_GET["sort_order"] == "sortASC") $query .= "ASC";
    else if ($_GET["sort_order"] == "sortDESC") $query .= "DESC";
}

// Выполняем запрос и обрабатываем результат
$paginator = new Paginator($connect, $query, $links);
$result = $paginator->getData($limit, $page);

$html_string = constructHTML($result, $paginator);
$response = [
    "status" => true,
    "message" => $html_string,
    "query" => $query
];
echo json_encode($response);

// Закрываем соединение с базой данных
$connect = null;

// Создать html строку на основе результатов запроса
function constructHTML($result, Paginator $paginator): string
{
    $html_string = "<div class='container mt-3'>";
    $html_string .= "<h2>Результаты поиска:</h2>";
    if ($result->total > 0) {
        $html_string .= "<table class='table table-striped'>";
        $html_string .= "<thead><tr><th></th></tr></thead>";
        $html_string .= "<tbody>";
        for ($i = 0; $i < count($result->data); $i++) $html_string .= "<tr><td>" . (($result->page - 1) * $result->limit + $i + 1) . ". " . $result->data[$i]["word"] . "</td></tr>";
        $html_string .= "</tbody></table></div>";
        $html_string .= $paginator->createLinks();
    } else {
        $html_string .= "<p>Не найдены подходящие результаты для указанного запроса.</p>";
        $html_string .= "</div>";
    }
    return $html_string;
}
