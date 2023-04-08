<?php

class Paginator
{
    private int $_page; // Текущая страница
    private int $_limit; // Лимит слов на странице
    private int $_links; // Количество ссылок в каждую сторону от текущей
    private int $_total; // Количество найденных по запросу записей

    public function __construct(int $page, int $limit, int $links, int $total)
    {
        $this->_page = $page;
        $this->_limit = $limit;
        $this->_links = $links;
        $this->_total = $total;
    }

    // Создание ссылок для перехода между страницами
    public function createLinks(): string
    {
        $pages_count = ceil($this->_total / $this->_limit);

        $first_page = (($this->_page - $this->_links) > 0) ? $this->_page - $this->_links : 1;
        $last_page = (($this->_page + $this->_links) < $pages_count) ? $this->_page + $this->_links : $pages_count;

        $html = '<ul class="justify-content-center pagination pagination-sm">';

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
