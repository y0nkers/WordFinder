<?php
require_once "Finder.php";
require_once "Paginator.php";

// Класс для нахождения слов по указанным параметрам
class WordFinder extends Finder
{
    private Paginator $_paginator; // Класс для пагинации результатов
    private string $_query; // Строка запроса
    private int $_page; // Текущая страница
    private int $_limit; // Лимит слов на странице
    private int $_links; // Количество ссылок в каждую сторону от текущей

    function __construct(DbConnect $connect, string $query, int $page, int $limit, int $links)
    {
        $this->_connect = $connect;
        $this->_query = $query;
        $this->_page = $page;
        $this->_limit = $limit;
        $this->_links = $links;
    }

    // Добавление в запрос сортировкир результатом
    public function sort(string $sort_type, string $sort_order): void
    {
        if ($sort_type == "sort-word") $this->_query .= " ORDER BY word ";
        else if ($sort_type == "sort-length") $this->_query .= " ORDER BY CHAR_LENGTH(word) ";
        if ($sort_order == "sortASC") $this->_query .= "ASC";
        else if ($sort_order == "sortDESC") $this->_query .= "DESC";
    }

    // Основной метод поиска
    public function find(bool $admin = false): array
    {
        $total = $this->_connect->getPDO()->query($this->_query)->rowCount();
        $this->_paginator = new Paginator($this->_page, $this->_limit, $this->_links, $total);
        // Ограничиваем результат запроса в зависимости от текущей страницы и лимита слов на странице
        $query = $this->_query . " LIMIT " . (($this->_page - 1) * $this->_limit) . ", $this->_limit";
        $results = $this->executeQuery($query);
        if ($admin) $html = $this->adminHTML($results, $total);
        else $html = $this->constructHTML($results, $total);
        return [
            "status" => true,
            "message" => $html,
            "query" => $this->_query
        ];
    }

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
        $html_string = "<div class='container mt-3'><h2 class='text-center'>Результаты поиска:</h2>";
        if ($total > 0) {
            $html_string .= "<table class='table table-bordered table-hover table-striped table-secondary border border-dark'>";
            $html_string .= "<tbody>";
            for ($i = 0; $i < $count; $i++) $html_string .= "<tr><td>" . (($this->_page - 1) * $this->_limit + $i + 1) . ". " . $results[$i]["word"] . "</td></tr>";
            $html_string .= "</tbody></table></div>";
            $html_string .= $this->_paginator->createLinks();
        } else {
            $html_string .= "<div class='bg-danger text-white p-3 rounded text-center'>Не найдены подходящие результаты для указанного запроса.</div>";
            $html_string .= "</div>";
        }
        return $html_string;
    }

    private function adminHTML(array $results, int $total): string
    {
        $count = count($results);
        $html_string = "<div class='container mt-3'>";
        if ($total > 0) {
            $html_string .= "<table class='table table-bordered table-hover table-striped table-secondary border border-dark'>";
            $html_string .= "<thead><tr><th class='col-12'></th><th></th><th></th></tr></thead><tbody>";
            for ($i = 0; $i < $count; $i++)
            {
                $html_string .= "<tr><td>" . (($this->_page - 1) * $this->_limit + $i + 1) . ". " . $results[$i]["word"] . "</td>";
                $html_string .= "<td><i class='fa-solid fa-pen' onclick='updateWord(\"" . $results[$i]["word"] . "\")'></i></td>";
                $html_string .= "<td><i class='fa-solid fa-trash' style='color: red' onclick='deleteWord(\"" . $results[$i]["word"] . "\")'></i></td></tr>";
            }
            $html_string .= "</tbody></table></div>";
            $html_string .= $this->_paginator->createLinks();
        } else {
            $html_string .= "<div class='bg-danger text-white p-3 rounded text-center'>Не найдены подходящие результаты для указанного запроса.</div>";
            $html_string .= "</div>";
        }
        return $html_string;
    }

    // Заглушки
    protected function getDictionaries(): array { return []; }
    protected function constructQuery(array $dictionaries): string { return ""; }
}
