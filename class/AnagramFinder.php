<?php

// Класс для нахождения анаграмм для указанного слова
class AnagramFinder
{
    private DbConnect $_connect; // Подключение к БД
    private string $_word; // Слово, для которого нужно найти анаграммы
    private array $_anagrams; // Анаграммы для указанного слова

    function __construct(DbConnect $connect, string $word)
    {
        $this->_connect = $connect;
        $this->_word = $word;
        $this->_anagrams = array();
    }

    // Основной метод поиска
    public function find(): array
    {
        $word = mb_strtolower($this->_word, 'utf-8'); // Приводим слово к нижнему регистру
        $letters = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY); // Разбиваем слово на буквы
        sort($letters); // Сортируем буквы в алфавитном порядке
        $this->generateAnagrams($letters, ""); // Вызываем рекурсивную функцию для генерации анаграмм

        $dictionaries = $this->getDictionaries();
        $query = $this->constructQuery($dictionaries);
        $results = $this->executeQuery($query);
        $html = $this->constructHTML($results);
        return [
            "status" => true,
            "message" => $html
        ];
    }

    // Генерация анаграмм для указанного слова
    private function generateAnagrams(array $letters, string $currentWord): void
    {
        if (count($letters) === 0) { // Базовый случай: если больше нет букв
            $this->_anagrams[] = $currentWord; // Добавляем найденную анаграмму в результаты
        } else {
            for ($i = 0; $i < count($letters); $i++) {
                if ($i > 0 && $letters[$i] === $letters[$i - 1]) {
                    continue; // Пропускаем повторяющиеся буквы для оптимизации
                }
                $letter = $letters[$i];
                $remainingLetters = $letters;
                array_splice($remainingLetters, $i, 1); // Удаляем текущую букву из оставшихся
                $this->generateAnagrams($remainingLetters, $currentWord . $letter); // Рекурсивно генерируем анаграммы
            }
        }
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
            $query .= "SELECT `word` FROM " . "dictionary_" . $dictionary . " WHERE word IN ('" . implode("','", $this->_anagrams) . "') AND word != '$this->_word'";
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
            $html .= "<div class='bg-dark text-white p-3 rounded'>";
            for ($i = 0; $i < $count; $i++) $html .= $results[$i]["word"] . " ";
        } else {
            $html .= "<div class='bg-danger text-white p-3 rounded text-center'>Не найдены подходящие результаты для указанного запроса.</div>";
        }
        $html .= "</div>";
        return $html;
    }
}