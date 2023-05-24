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

    // Если запрос от зарегистрированного пользователя, то добавляем запрос в историю поиска
    if ($_GET["userid"] != -1) {
        $search_string = "/?mode=$mode&language=$language&compound_words=$compound_words&" . http_build_query($data);
        user_history($dbConnect, $_GET["userid"], $search_string);
    }
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

// Запись запроса в историю поиска
function user_history(DbConnect $dbConnect, int $userid, string $search_string): void
{
    $pdo = $dbConnect->getPDO();
    try {
        $stmt = $pdo->prepare("INSERT INTO `users_history` (user_id, search_string) VALUES (:userid, :search)");
        $stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
        $stmt->bindParam(':search', $search_string);
        $stmt->execute();
    } catch (PDOException $e) {}

    $stmt = $pdo->prepare("SELECT COUNT(1) - 10 FROM `users_history` WHERE user_id = :userid");
    $stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM `users_history` WHERE user_id = :userid ORDER BY created_at LIMIT :count");
            $stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
            $stmt->bindParam(':count', $count, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {}
    }
}