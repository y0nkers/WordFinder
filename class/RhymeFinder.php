<?php

// Класс для нахождения слов-рифм к указанному слову
class RhymeFinder
{
    private DbConnect $_connect; // Подключение к БД
    private string $_word; // Слово, к которому нужно найти рифму
    private string $_end; // Окончание слова

    function __construct(DbConnect $connect, string $word)
    {
        $this->_connect = $connect;
        $this->_word = $word;
    }

    // Основной метод поиска
    public function find(): array
    {
        $length = strlen($this->_word);
        if ($length < 3) {
            return [
                "status" => false,
                "message" => "Не удалось найти рифму к слову " . $this->_word
            ];
        }
        if ($length == 3) $this->_end = mb_substr($this->_word, -2, null,'UTF-8');
        else $this->_end = mb_substr($this->_word, -3, null,'UTF-8');

        $dictionaries = $this->getDictionaries();
        $query = $this->constructQuery($dictionaries);
        $results = $this->executeQuery($query);
        $html = $this->constructHTML($results);
        return [
            "status" => true,
            "message" => $html
        ];
    }

    // Получение всех доступных словарей в системе
    private function getDictionaries(): array
    {
        $dictionaries = array();
        $stmt = $this->_connect->getPDO()->query("SELECT `id` FROM `dictionaries`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $dictionaries[] = $row["id"];
        return $dictionaries;
    }

    // Подготовка строки запроса
    private function constructQuery(array $dictionaries): string
    {
        $query = "";
        $count = count($dictionaries);
        foreach ($dictionaries as $index => $dictionary) {
            $query .= "SELECT `word` FROM " . "dictionary_" . $dictionary . " WHERE word LIKE '%$this->_end' AND word != '$this->_word'";
            if ($index != $count - 1) $query .= " UNION ";
        }
        return $query;
    }

    // Выполнение запроса и получение его результата
    private function executeQuery(string $query): array
    {
        $stmt = $this->_connect->getPDO()->query($query);
        $results = array();
        $count = $stmt->rowCount();
        if ($count > 0) while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $results[] = $row;
        return $results;
    }

    // Создание html элемента с результатами запроса
    private function constructHTML(array $results): string
    {
        $count = count($results);
        $html = "<div class='container mt-3'><h2 class='text-center'>Результаты поиска:</h2>";
        if ($count > 0) {
            $html .= "<div style='line-height: 1.4em;'>";
            for ($i = 0; $i < $count; $i++) $html .= $results[$i]["word"] . " ";
        } else {
            $html .= "<p>Не найдены подходящие результаты для указанного запроса.</p>";
        }
        $html .= "</div>";
        return $html;
    }
}
