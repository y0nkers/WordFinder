<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Создаем подключение к базе данных
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "wordfinder";
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Проверяем соединение с базой данных
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    $mode = $_POST["mode"];
    $data = json_decode($_POST['data'], true);
    $query = "SELECT * FROM words WHERE ";

    if ($mode == "normal") {
        $mask = $data[0];
        $mask = str_replace('?', '_', $mask);
        $mask = str_replace('*', '%', $mask);
        $query .= "word LIKE '$mask'";
    } else if ($mode == "extended") {
        $length = $data[0];
        $start = $data[1];
        $end = $data[2];
        $query .= "CHAR_LENGTH(word) = $length AND word LIKE '$start%' AND word LIKE '%$end'";
    }

    // Выполняем запрос и обрабатываем результат
    $result = $conn->query($query);

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
    $conn->close();
}
?>

