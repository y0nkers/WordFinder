<?php
if ($_SERVER["REQUEST_METHOD"] != "GET") die();

require_once "../class/DbConnect.php";
require_once "../class/QueryConstructor.php";
require_once "../class/WordFinder.php";

$dbConnect = new DbConnect("user", "");

$query = "";
if (!isset($_GET["query"])) {
    // Если это первый запрос, то создаём строку запроса по указанным параметрам
    $dictionaries = $_GET["dictionaries"];
    $language = $_GET["language"];
    $mode = $_GET["mode"];
    $data = json_decode($_GET['data'], true);
    $compound_words = json_decode($_GET["compound_words"]);
    $constructor = new QueryConstructor($dictionaries, $language, $mode, $data, $compound_words);
    $query = $constructor->constructQuery();
    unset($constructor);
} else $query = $_GET["query"]; // Иначе берём ранее созданную строку запроса

// Дополнительные настройки: страница поиска, лимит слов на странице, количество ссылок
$page = (isset($_GET["page"]) && $_GET["page"] > 0) ? $_GET["page"] : 1;
$limit = (isset($_GET["limit"]) && ($_GET["limit"] >= 20 && $_GET["limit"] <= 100)) ? $_GET["limit"] : 20;
$links = (isset($_GET['links'])) ? $_GET['links'] : 4;

$finder = new WordFinder($dbConnect, $query, $page, $limit, $links);
// Добавляем сортировку к запросу, если соответствующие параметры установлены
if (isset($_GET["sort_type"]) && isset($_GET["sort_order"]) && !empty($_GET["sort_order"])) $finder->sort($_GET["sort_type"], $_GET["sort_order"]);
$isAdmin = isset($_GET["admin"]) ? json_decode($_GET["admin"]) : false;
$response = $finder->find($isAdmin);
echo json_encode($response);

// Закрываем соединение с базой данных
$dbConnect->closeConnection();
