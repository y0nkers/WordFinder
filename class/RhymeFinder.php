<?php
require_once "Finder.php";

// Класс для нахождения слов-рифм к указанному слову
class RhymeFinder extends Finder
{
    private string $_language; // Выбранный язык поиска
    private string $_word; // Слово, к которому нужно найти рифму
    private string $_end; // Окончание слова

    function __construct(DbConnect $connect, string $language, string $word)
    {
        $this->_connect = $connect;
        $this->_language = $language;
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
    protected function getDictionaries(): array
    {
        $dictionaries = array();
        $stmt = $this->_connect->getPDO()->query("SELECT `id` FROM `dictionaries` WHERE `language` = '" . $this->_language . "'");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $dictionaries[] = $row["id"];
        return $dictionaries;
    }

    // Подготовка строки запроса
    protected function constructQuery(array $dictionaries): string
    {
        $query = "";
        $count = count($dictionaries);
        foreach ($dictionaries as $index => $dictionary) {
            $query .= "SELECT `word` FROM " . "dictionary_" . $dictionary . " WHERE word LIKE '%$this->_end' AND word != '$this->_word'";
            if ($index != $count - 1) $query .= " UNION ";
        }
        return $query;
    }
}
