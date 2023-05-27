<?php

// HTTP-заголовки
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

$api_key = $_SERVER['HTTP_X_API_KEY'] ?? '';

if (empty($api_key)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Отсутствует API ключ'));
    die();
}

$body = (array) json_decode(file_get_contents('php://input'), TRUE);
if (empty($body)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Не был передан ни один параметр'));
    die();
}

require_once "../class/DbConnect.php";
$dbConnect = new DbConnect("user", "");
$pdo = $dbConnect->getPDO();

$stmt = $pdo->prepare("SELECT id FROM users WHERE api_key = :apikey");
$stmt->bindParam(":apikey", $api_key);
$stmt->execute();

if ($stmt->rowCount() <= 0) {
    http_response_code(400);
    echo json_encode(array('error' => 'Некорректный API ключ'));
    die();
}

$parsed_params = parseBodyParameters($body, $pdo);
require_once  "../class/QueryConstructor.php";
$constructor = new QueryConstructor($body['dictionaries'], $body['language'], $body['mode'], $parsed_params, $body['compound_words']);
$query = $constructor->constructQuery();
unset($constructor);
try {
    $stmt = $pdo->query($query);
} catch (PDOException $e) {
    sendError("Ошибка при выполнении запроса: " . $e);
}

http_response_code(200);
if ($stmt->rowCount() <= 0) echo json_encode(array('message' => 'Не найдены подходящие результаты для указанного запроса.'));
else {
    $response = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $response[] = $row;
    echo json_encode(array('words' => $response));
}

// Обработка переданных параметров и отправка результатов сформированного запроса
function parseBodyParameters(array $body, PDO $pdo): array
{
    // дефолтные проверки на существование параметров и соответствие необходимому типу
    $dictionaries = getParam($body['dictionaries'], "Словари поиска (dictionaries)");
    if (!is_array($dictionaries)) sendError("Словари поиска (dictionaries) должны передаваться в виде массива целых чисел");
    foreach ($dictionaries as $dictionary) if (!is_int($dictionary)) sendError("Словари поиска (dictionaries) должны передаваться в виде массива целых чисел");

    $language = getParam($body['language'], "Язык поиска (language)");
    if (!is_string($language)) sendError("Язык поиска (language) должен быть строкового типа");

    $compound_words = getParam($body['compound_words'], "Поиск составных слов (compound_words)");
    if (!is_bool($compound_words)) sendError('Параметр "Поиск составных слов" (compound_words) должен быть типа bool');

    $mode = getParam($body['mode'], "Режим поиска (mode)");
    if (!is_string($mode)) sendError("Режим поиска (mode) должен быть строкового типа");

    $parameters = getParam($body['parameters'], "Параметры поиска (parameters[])");
    if (!is_array($parameters)) sendError("Параметры поиска (parameters[]) должны передаваться в виде массива");

    $data_parsed = array();
    if ($mode == "normal") {
        $mask = getParam($parameters["mask"], "Маска слова (mask)");
        if (!is_string($mask)) sendError('Параметр "Маска слова" (mask) должен быть строкового типа');
        $data_parsed["mask"] = $mask;
    }
    else if ($mode == "extended") {
        $start = ''; $end = ''; $length = 0; $contains = ''; $include = ''; $exclude = '';
        if (isset($parameters['start'])) {
            $start = $parameters['start'];
            if (!is_string($start)) sendError('Параметр "Начало слова" (start) должен быть строкового типа');
        }
        if (isset($parameters['end'])) {
            $end = $parameters['end'];
            if (!is_string($end)) sendError('Параметр "Конец слова" (start) должен быть строкового типа');
        }
        if (isset($parameters['length'])) {
            $length = $parameters['length'];
            if (!is_int($length)) sendError('Параметр "Длина слова" (start) должен быть целочисленного типа');
        }
        if (isset($parameters['contains'])) {
            $contains = $parameters['contains'];
            if (!is_string($contains)) sendError('Параметр "Обязательное буквосочетание" (contains) должен быть строкового типа');
        }
        if (isset($parameters['include'])) {
            $include = $parameters['include'];
            if (!is_string($include)) sendError('Параметр "Обязательные буквы" (include) должен быть строкового типа');
        }
        if (isset($parameters['exclude'])) {
            $exclude = $parameters['exclude'];
            if (!is_string($exclude)) sendError('Параметр "Исключённые буквы" (exclude) должен быть строкового типа');
        }

        $data_parsed = array(
            'start' => $start,
            'end' => $end,
            'length' => $length,
            'contains' => $contains,
            'include' => $include,
            'exclude' => $exclude,
        );
    } else if ($mode == "regexp") {
        $regexp = getParam($parameters["regexp"], "Регулярное выражение (regexp)");
        if (!is_string($regexp)) sendError('Параметр "Регулярное выражение" (regexp) должен быть строкового типа');
        $data_parsed["regexp"] = $regexp;
    }
    else sendError("Некорректное значение режима поиска (mode)");

    // Проверка параметра language (язык поиска) на корректность
    $languages_json = file_get_contents("../languages.json");
    $languages = json_decode($languages_json, true);
    if (!isset($languages[$language])) sendError("Указан некорректный язык поиска (language)");

    // Проверяем, все ли введённые ID словарей есть в БД
    $placeholders = implode(',', array_fill(0, count($dictionaries), '?'));
    $stmt = $pdo->prepare("SELECT id, language FROM dictionaries WHERE id IN ($placeholders)");
    $stmt->execute($dictionaries);

    $foundDictionaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Если словарей нашлось меньше, чем было передано в запросе
    if (count($foundDictionaries) != count($dictionaries)) sendError("Один или несколько переданных словарей некорректны или отсутствуют в таблице словарей.");
    // Проверка на то, чтобы все словари были одного языка
    $foundLanguages = array_unique(array_column($foundDictionaries, 'language'));
    if (count($foundLanguages) != 1) sendError("Переданные словари имеют разные языки. Используйте словари только одного языка");

    return $data_parsed;
}

function getParam($param, $name) {
    if (empty($param)) sendError('Отсутствует необходимый параметр: ' . $name);
    return $param;
}

function sendError($error) {
    http_response_code(400);
    echo json_encode(array('error' => $error));
    die();
}