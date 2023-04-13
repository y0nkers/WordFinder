<?php

abstract class Finder
{
    protected DbConnect $_connect; // Подключение к БД

    // Основной метод поиска
    abstract public function find(): array;

    // Получение всех доступных словарей в системе
    abstract protected function getDictionaries(): array;

    // Подготовка строки запроса
    abstract protected function constructQuery(array $dictionaries): string;

    // Выполнение запроса и получение его результата
    protected function executeQuery(string $query): array
    {
        $stmt = $this->_connect->getPDO()->query($query);
        $results = array();
        $count = $stmt->rowCount();
        if ($count > 0) while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $results[] = $row;
        return $results;
    }

    // Создание html элемента с результатами запроса
    protected function constructHTML(array $results, int $total = 0): string
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
