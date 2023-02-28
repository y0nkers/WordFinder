<?php
require_once "db_connect.php";
require_once "QueryPreparer.class.php";
require_once "Paginator.class.php";
/** @var $db */
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $query = "";
    if (!isset($_GET["query"])) {
        $mode = $_GET["mode"];
        $data = json_decode($_GET['data'], true);
        $prepaper = new QueryPreparer($mode, $data, $_GET["compound_words"]);
        $query = $prepaper->prepareQuery();
    } else $query = $_GET["query"];

    $page = (isset($_GET["page"]) && $_GET["page"] > 0) ? $_GET["page"] : 1;
    $limit = (isset($_GET["limit"]) && ($_GET["limit"] >= 20 && $_GET["limit"] <= 100)) ? $_GET["limit"] : 20;
    $links = (isset($_GET['links'])) ? $_GET['links'] : 4;

    if (isset($_GET["sort_type"]) && isset($_GET["sort_order"])) {
        if ($_GET["sort_type"] == "sort-word") $query .= " ORDER BY word " ;
        else if ($_GET["sort_type"] == "sort-length") $query .= " ORDER BY CHAR_LENGTH(word) ";
        if ($_GET["sort_order"] == "sortASC") $query .= "ASC";
        else if ($_GET["sort_order"] == "sortDESC") $query .= "DESC";
    }

    // Выполняем запрос и обрабатываем результат
    $paginator = new Paginator($db, $query);
    $result = $paginator->getData($limit, $page);

    $html_string = "<div class='container mt-3'>";
    $html_string .= "<h2>Результаты поиска:</h2>";
    if ($result->total > 0) {
        $html_string .= "<table class='table table-striped'>";
        $html_string .= "<thead><tr><th></th></tr></thead>";
        $html_string .= "<tbody>";
        for ($i = 0; $i < count($result->data); $i++) $html_string .= "<tr><td>" . (($result->page - 1) * $result->limit + $i + 1) . ". " . $result->data[$i]["word"] . "</td></tr>";
        $html_string .= "</tbody></table></div>";
        $html_string .= $paginator->createLinks($links);
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

    // Закрываем соединение с базой данных
    $db->close();
}
