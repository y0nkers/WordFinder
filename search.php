<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mask = $_POST["mask"];
//    $length = $_POST["length"];
//    $start = $_POST["start"];
//    $end = $_POST["end"];

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

    // Строим SQL-запрос на поиск слов
    $sql = "SELECT * FROM words WHERE word LIKE '$mask'";

//    if (!empty($length)) {
//        $sql .= " AND LENGTH(word) = $length";
//    }
//
//    if (!empty($start)) {
//        $sql .= " AND word LIKE '$start%'";
//    }
//
//    if (!empty($end)) {
//        $sql .= " AND word LIKE '%$end'";
//    }

    // Выполняем запрос и обрабатываем результат
    $result = $conn->query($sql);

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

