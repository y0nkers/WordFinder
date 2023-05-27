<?php
session_start();
if (!$_SESSION['user']) {
    header('Location: index.php');
}

require_once "class/DbConnect.php";
$dbConnect = new DbConnect("user", "");
$pdo = $dbConnect->getPDO();

$stmt = $pdo->prepare("SELECT id, login, email, api_key FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user']['id'], PDO::PARAM_INT);
$stmt->execute();

$user = $stmt->fetch();
$_SESSION['user'] = [
    "id" => $user['id'],
    "login" => $user['login'],
    "email" => $user['email'],
    "api_key" => $user["api_key"]
];

$stmt = $pdo->prepare("SELECT search_string, created_at FROM `users_history` WHERE user_id = :userid");
$stmt->bindParam(':userid', $user['id'], PDO::PARAM_INT);
$stmt->execute();
$history = [];
while ($row = $stmt->fetch()) $history[] = $row;

$dbConnect->closeConnection();

function print_history(array $data): void
{
    $languages_json = file_get_contents(__DIR__ . "/languages.json");
    $languages = json_decode($languages_json, true);

    $html = "";
    foreach ($data as $row) {
        $search_string = $row["search_string"];
        parse_str(ltrim($search_string, '/?'), $params);
        $mode = ($params["mode"] == "normal") ? "Обычный" : (($params["mode"] == "extended") ? "Расширенный" : "RegExp");
        $language = $languages[$params["language"]]["name"];
        $html .= '<p><a href="' . $search_string . '">Запрос ' . $row["created_at"] . ' (Режим: ' . $mode . ', язык: ' . $language . ')</a></p>';
    }
    echo $html;
}

$title = "Word Finder - личный кабинет";
require __DIR__ . '/header.php';
?>

<main class="container-fluid container-xl">
    <div class="pt-5 pb-3">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border border-dark">
                        <div class="card-header bg-dark text-white">Личный кабинет</div>
                        <div class="card-body rounded-3 field-bg">
                            <div class="row align-items-center">
                                <div class="form-group col-sm-6">
                                    <span class="fs-4 fw-bold">Ваш логин:</span>
                                    <span class="fs-4"><?= $_SESSION['user']['login'] ?></span>
                                    <br>
                                    <span class="fs-4 fw-bold">Ваш email:</span>
                                    <span class="fs-4"><?= $_SESSION['user']['email'] ?></span>
                                </div>
                                <div class="form-group col-sm-6">
                                    <span class=" fs-4 fw-bold">Ваш API key: </span>
                                    <span class="fs-5"><?= $_SESSION['user']['api_key'] ?></span>
                                </div>
                            </div>
                            <div class="row align-items-center">
                                <a class="fs-4 fw-bold" href="/core/logout.php">Выйти</a>
                            </div>
                            <div class="row align-items-center mt-3">
                                <div class="form-group col-12">
                                    <h2 class='text-center'>История поиска:</h2>
                                    <div id="search-history"><?php print_history($history)?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


<?php require __DIR__ . '/footer.php' ?>
