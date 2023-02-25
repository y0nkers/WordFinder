<?php

class QueryPreparer
{
    private $_mode;
    private $_data;

    public function __construct($mode, $data)
    {
        $this->_mode = $mode;
        $this->_data = $data;
    }

    // Подготовка строки запроса
    public function prepareQuery(): int|string
    {
        $query = "SELECT * FROM words WHERE ";

        if ($this->_mode == "normal") {
            $mask = $this->_data[0];
            $this->validateField("Маска слова", $mask, '/^[А-яёЁ?*]+$/u');
            $mask = str_replace('?', '_', $mask);
            $mask = str_replace('*', '%', $mask);
            $query .= "word LIKE '$mask'";
        } else if ($this->_mode == "extended") {
            $length = $this->_data[0];
            if ($length < 2 || $length > 32) {
                $error = [
                    "status" => false,
                    "message" => "Длина слова должна быть не меньше 2 и не больше 32!"
                ];
                echo json_encode($error);
                die();
            }

            $start = $this->_data[1];
            $this->validateField("Начало слова", $start, '/^[А-яёЁ?]+$/u');
            $start = str_replace('?', '_', $start);

            $end = $this->_data[2];
            $this->validateField("Конец слова", $end, '/^[А-яёЁ?]+$/u');
            $end = str_replace('?', '_', $end);

            $contains = $this->_data[3];
            $this->validateField("Обязательное сочетание", $contains, '/^[А-яёЁ?]+$/u');
            $contains = str_replace('?', '_', $contains);

            $include = $this->_data[4];
            $this->validateField("Обязательные буквы", $include, '/^[А-яёЁ]+$/u');

            $exclude = $this->_data[5];
            $this->validateField("Исключённые буквы", $exclude, '/^[А-яёЁ]+$/u');

            if (!empty($length)) $query .= "CHAR_LENGTH(word) = $length AND ";
            $query .= "word LIKE '$start%$contains%$end' ";
            if (!empty($include)) {
                $include_array = mb_str_split($include);
                $include_array = array_map(function ($letter) {
                    return "AND word LIKE '%$letter%'";
                }, $include_array);
                $query .= implode(' ', $include_array);
            }
            if (!empty($exclude)) $query .= " AND word NOT REGEXP '[$exclude]'";
        }
        return $query;
    }

    // Проверка поля на корректность введённых данных
    private function validateField($field, $data, $pattern): void
    {
        if (!empty($data) && !preg_match($pattern, $data)) {
            $error = [
                "status" => false,
                "message" => "Проверьте правильность ввода поля: " . $field
            ];
            echo json_encode($error);
            die();
        }
    }
}