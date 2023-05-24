<?php

class QueryConstructor
{
    // Массив id таблиц, в которых нужно производить поиск
    private array $_dictionaries;
    // Выбранный язык поиска
    private string $_language;
    // Режим поиска (normal/extended)
    private string $_mode;
    // Массив с параметрами поиска
    private array $_data;
    // Искать ли составные слова
    private bool $_compound_words;

    public function __construct(array $dictionaries, string $language, string $mode, array $data, bool $compound_words)
    {
        $this->_dictionaries = $dictionaries;
        $this->_language = $language;
        $this->_mode = $mode;
        $this->_data = $data;
        $this->_compound_words = $compound_words;

    }

    // Подготовка строки запроса
    public function constructQuery(): string
    {
        $condition = $this->constructConditionPart();
        $query = "";
        $count = count($this->_dictionaries);
        // Выполняем SELECT запрос для каждого указанного словаря
        foreach ($this->_dictionaries as $index => $dictionary) {
            $query .= "SELECT `word` FROM " . "dictionary_" . $dictionary . $condition;
            if ($index != $count - 1) $query .= " UNION ";
        }

        return $query;
    }

    // Подготовка condition части запроса (WHERE ... )
    private function constructConditionPart(): string
    {
        $base = $this->getPatternBase("/../languages.json");
        $query = " WHERE ";
        if ($this->_mode == "normal") {
            $mask = $this->_data["mask"];
            $pattern = $this->makePattern($base, "^[", "?*]+$", "i");
            $this->validateField("Маска слова", $mask, $pattern); // '/^[a-zA-Z?*]+$/i'
            $mask = str_replace('?', '_', $mask);
            $mask = str_replace('*', '%', $mask);
            $query .= "word LIKE '$mask'";
            if (!$this->_compound_words) $query .= " AND word NOT REGEXP '[-]'";
        } else if ($this->_mode == "extended") {
            $start = ''; $end = ''; $contains = ''; $include = ''; $exclude = '';
            if (array_key_exists("length", $this->_data)) {
                $length = $this->_data["length"];
                if (!empty($length) && ($length < 2 || $length > 32)) {
                    $error = [
                        "status" => false,
                        "message" => "Длина слова должна быть не меньше 2 и не больше 32!"
                    ];
                    echo json_encode($error);
                    die();
                }
            }

            $pattern = $this->makePattern($base, "^[", "?]+$", "i");
            if (array_key_exists("start", $this->_data)) {
                $start = $this->_data["start"];
                $this->validateField("Начало слова", $start, $pattern); // '/^[a-zA-Z?]+$/u'
                $start = str_replace('?', '_', $start);
            }

            if (array_key_exists("end", $this->_data)) {
                $end = $this->_data["end"];
                $this->validateField("Конец слова", $end, $pattern); // '/^[a-zA-Z?]+$/u'
                $end = str_replace('?', '_', $end);
            }

            if (array_key_exists("contains", $this->_data)) {
                $contains = $this->_data["contains"];
                $this->validateField("Обязательное сочетание", $contains, $pattern); // '/^[a-zA-Z?]+$/u'
                $contains = str_replace('?', '_', $contains);
            }

            if (array_key_exists("include", $this->_data)) {
                $pattern = $this->makePattern($base, "^[", "]+$", "i");
                $include = $this->_data["include"];
                $this->validateField("Обязательные буквы", $include, $pattern); // '/^[a-zA-Z]+$/u'
            }

            if (array_key_exists("exclude", $this->_data)) {
                $pattern = $this->makePattern($base, "^[", "-]+$", "i");
                $exclude = $this->_data["exclude"];
                $this->validateField("Исключённые буквы", $exclude, $pattern); // '/^[a-zA-Z-]+$/u'
            }

            if (!empty($length)) $query .= "CHAR_LENGTH(word) = $length AND ";
            $query .= "word LIKE '$start%$contains%$end' ";
            if (!empty($include)) {
                $include_array = mb_str_split($include);
                $include_array = array_map(function ($letter) {
                    return "AND word LIKE '%$letter%'";
                }, $include_array);
                $query .= implode(' ', $include_array);
            }
            if (!$this->_compound_words) $exclude .= '-';
            if (!empty($exclude)) $query .= " AND word NOT REGEXP '[$exclude]'";
        } else if ($this->_mode == "regexp") {
            $regexp = $this->_data["regexp"];
            $pattern = '/^[' . $base . '\[\]\-.*+?^${}()|\\\d]' . '/i'; // base + regexp meta symbols + digits
            $this->validateField("Регулярное выражение", $regexp, $pattern);
            $query .= " word REGEXP '$regexp'";
        }
        return $query;
    }

    // Получение из json шаблона для текущего языка поиска
    private function getPatternBase(string $path): string
    {
        $json = file_get_contents(__DIR__ . $path);
        $data = json_decode($json, true);
        return $data[$this->_language]["regexp"];
    }

    // Полный шаблон regexp с флагами
    private function makePattern(string $base, string $prefix, string $postfix, string $flags): string
    {
        return '/' . $prefix . $base . $postfix . '/' . $flags;
    }

    // Проверка поля на корректность введённых данных
    private function validateField(string $field, string $data, string $pattern): void
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
