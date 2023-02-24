<?php
require_once "db_connect.php";
/** @var $db */
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    //echo "REQUEST VARIABLE: " .json_encode($_REQUEST);
    $mode = $_GET["mode"];
    $data = json_decode($_GET['data'], true);

    $query_string = prepareQuery($mode, $data);

    // Выполняем запрос и обрабатываем результат
    $query_result = $db->query($query_string);

    $html_string = "<div class='container mt-3'>";
    $html_string .= "<h2>Результаты поиска:</h2>";
    if ($query_result->num_rows > 0) {
        $html_string .= "<table class='table table-striped'>";
        $html_string .= "<thead><tr><th>Слово</th></tr></thead>";
        $html_string .= "<tbody>";
        while ($row = $query_result->fetch_assoc()) {
            $html_string .= "<tr><td>" . $row["word"] . "</td></tr>";
        }
        $html_string .=  "</tbody></table></div>";
    } else {
        $html_string .= "<p>Совпадения не найдены.</p>";
        $html_string .= "</div>";
    }

    $response = [
        "status" => true,
        "message" => $html_string,
        "query" => $query_string // TODO: for debug
    ];
    echo json_encode($response);

    // Закрываем соединение с базой данных
    $db->close();
}

// Подготовка строки запроса
function prepareQuery($mode, $data): int|string
{
    $query = "SELECT * FROM words WHERE ";

    if ($mode == "normal") {
        $mask = $data[0];
        validateField("Маска слова", $mask, '/^[А-яёЁ?*]+$/u');
        $mask = str_replace('?', '_', $mask);
        $mask = str_replace('*', '%', $mask);
        $query .= "word LIKE '$mask'";
    } else if ($mode == "extended") {
        $length = $data[0];
        if ($length < 2 || $length > 32) {
            $error = [
                "status" => false,
                "message" => "Длина слова должна быть не меньше 2 и не больше 32!"
            ];
            echo json_encode($error);
            die();
        }

        $start = $data[1];
        validateField("Начало слова", $start, '/^[А-яёЁ?]+$/u');
        $start = str_replace('?', '_', $start);

        $end = $data[2];
        validateField("Конец слова", $end, '/^[А-яёЁ?]+$/u');
        $end = str_replace('?', '_', $end);

        $contains = $data[3];
        validateField("Обязательное сочетание", $contains, '/^[А-яёЁ?]+$/u');
        $contains = str_replace('?', '_', $contains);

        $include = $data[4];
        validateField("Обязательные буквы", $include, '/^[А-яёЁ]+$/u');

        $exclude = $data[5];
        validateField("Исключённые буквы", $exclude, '/^[А-яёЁ]+$/u');

        if (!empty($length)) $query .= "CHAR_LENGTH(word) = $length AND ";
        $query .= "word LIKE '$start%$contains%$end' ";
        if (!empty($include)) {
            $include_array = mb_str_split($include);
            $include_array = array_map(function ($letter) {
                return "AND word LIKE '%$letter%'";
            }, $include_array);
            $query .= implode(' ', $include_array);
        }
        if (!empty($exclude)) $query .= " AND word NOT REGEXP '[$exclude]'";
    }
    return $query;
}

// Проверка поля на корректность введённых данных
function validateField($field, $data, $pattern): void
{
    if (!empty($data) && !preg_match($pattern, $data)) {
        $error = [
            "status" => false,
            "message" => "Проверьте правильность ввода поля: " . $field
        ];
        echo json_encode($error);
        die();
    }
}
