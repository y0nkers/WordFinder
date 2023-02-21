<?php
require_once "db_connect.php";
/** @var $db */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mode = $_POST["mode"];
    $data = json_decode($_POST['data'], true);

    $result = prepareQuery($mode, $data);
    echo $result;
    if ($result == -1) return;

    // Выполняем запрос и обрабатываем результат
    $result = $db->query($result);

    if ($result->num_rows > 0) {
        echo "<div class='container mt-3'>";
        echo "<h2>Результаты поиска:</h2>";
        echo "<table class='table table-striped'>";
        echo "<thead><tr><th>Слово</th></tr></thead>";
        echo "<tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["word"] . "</td></tr>";
        }
        echo "</tbody></table></div>";
    } else {
        echo "<div class='container mt-3'>";
        echo "<h2>Результаты поиска:</h2>";
        echo "<p>Совпадения не найдены.</p>";
        echo "</div>";
    }

    // Закрываем соединение с базой данных
    $db->close();
}

function prepareQuery($mode, $data): int|string
{
    $query = "SELECT * FROM words WHERE ";

    if ($mode == "normal") {
        $mask = $data[0];
        if (!preg_match('/^[А-яёЁ?*]+$/u', $mask)) {
            echo "Проверьте правильность ввода маски!";
            return -1;
        }
        $mask = str_replace('?', '_', $mask);
        $mask = str_replace('*', '%', $mask);
        $query .= "word LIKE '$mask'";
    } else if ($mode == "extended") {
        $length = $data[0];
        if ($length < 2 && $length > 32) {
            echo "Длина слова должна быть не меньше 2 и не больше 32!";
            return -1;
        }

        $start = $data[1];
        if (!preg_match('/^[А-яёЁ?]+$/u', $start)) {
            echo "Проверьте правильность ввода начала слова!";
            return -1;
        }
        $start = str_replace('?', '_', $start);

        $end = $data[2];
        if (!preg_match('/^[А-яёЁ?]+$/u', $end)) {
            echo "Проверьте правильность ввода конца слова!";
            return -1;
        }
        $end = str_replace('?', '_', $end);

        $contains = $data[3];
        if (!empty($contains) && !preg_match('/^[А-яёЁ?]+$/u', $contains)) {
            echo "Проверьте правильность ввода обязательного буквосочетания!";
            return -1;
        }
        $contains = str_replace('?', '_', $contains);

        $exclude = $data[4];
        if (!empty($contains) && !preg_match('/^[А-яёЁ]+$/u', $exclude)) {
            echo "Проверьте правильность ввода исключённых букв!";
            return -1;
        }

        if (!empty($length)) $query .= "CHAR_LENGTH(word) = $length AND ";
        $query .= "word LIKE '$start%$contains%$end' ";
        if (!empty($exclude)) $query .= "AND word NOT REGEXP '[$exclude]'";
    }

    return $query;
}
?>

