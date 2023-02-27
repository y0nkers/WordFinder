<?php

class Paginator
{
    private $_connect;
    private $_limit;
    private $_page;
    private $_query;
    private $_total;

    public function __construct($connect, $query)
    {
        $this->_connect = $connect;
        $this->_query = $query;

        $result = $this->_connect->query($this->_query);
        $this->_total = $result->num_rows;
    }

    // Выполнить запрос к БД и вернуть результат
    public function getData($limit = 10, $page = 1): stdClass
    {
        $this->_limit = $limit;
        $this->_page = $page;

        $query = $this->_query . " LIMIT " . (($this->_page - 1) * $this->_limit) . ", $this->_limit";

        $query_result = $this->_connect->query($query);

        $results = [];
        if ($query_result->num_rows > 0)
            while ($row = $query_result->fetch_assoc()) $results[] = $row;

        $result = new stdClass();
        $result->page = $this->_page;
        $result->limit = $this->_limit;
        $result->total = $this->_total;
        $result->data = $results;

        return $result;
    }

    // Создание ссылок для перехода между страницами
    // $links - количество ссылок в каждую сторону от текущей
    public function createLinks($links): string
    {
        $pages_count = ceil($this->_total / $this->_limit);

        $first_page = (($this->_page - $links) > 0) ? $this->_page - $links : 1;
        $last_page = (($this->_page + $links) < $pages_count) ? $this->_page + $links : $pages_count;

        $html = '<ul class="pagination pagination-sm">';

        // Ссылка на прошлую страницу
        $class = ($this->_page == 1) ? "disabled" : "";
        $html .= '<li class="page-item ' . $class . '"><a class="page-link" href="javascript:;" data-page="' . ($this->_page - 1) . '">&laquo;</a></li>';

        // Если далеко от первой страницы, то создаём ссылку на первую страницу и многоточие
        if ($first_page > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="javascript:;" data-page="1">1</a></li>';
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        // Все остальные ссылки
        for ($i = $first_page; $i <= $last_page; $i++) {
            $class = ($this->_page == $i) ? "active" : "";
            $html .= '<li class="page-item ' . $class . '"><a class="page-link" href="javascript:;" data-page="' . $i . '">' . $i . '</a></li>';
        }

        // Если далеко от последней страницы, то создаём ссылку на многоточие и последнюю страницу
        if ($last_page < $pages_count) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            $html .= '<li class="page-item"><a class="page-link" href="javascript:;" data-page="' . $pages_count . '">' . $pages_count . '</a></li>';
        }

        // Ссылка на следующую страницу
        $class = ($this->_page == $pages_count) ? "disabled" : "";
        $html .= '<li class="page-item ' . $class . '"><a class="page-link" href="javascript:;" data-page="' . ($this->_page + 1) . '">&raquo;</a></li>';
        $html .= '</ul>';

        return $html;
    }
}