<?php
require_once "core/connect.php";
/** @var PDO $connect */

$dictionaries = [];
$stmt = $connect->query("SELECT * FROM `dictionaries`");
while ($row = $stmt->fetch()) $dictionaries[] = $row;

/**
 * Создание списка всех доступных словарей
 * @param array $data массив с результатами запроса
 * @param string $name название списка
 * @return void
 */
function print_select(array $data, string $name): void
{
    $select_html = '<div class="form-group mb-3"><label for="' . $name . "[]" . '">Выберите словари для поиска: </label><select class="form-select" multiple name="' . $name . "[]" . '" aria-label="Select dictionary" required>';
    foreach ($data as $item) {
        $select_html .= '<option value="' . $item["id"] . '" data-language="'. $item["language"] . '">' . $item["name"] . ' [' . $item["language"] . ', слов: ' . $item["count"] . ']' . '</option>';
    }
    $select_html .= '</select></div>';
    echo $select_html;
}

?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Word Finder</title>
        <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    </head>
    <body>
        <div id="loading">
            <div class="container-fluid bg-light border border-dark rounded-3">
                <div class="d-flex justify-content-center mt-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Загрузка...</span>
                    </div>
                </div>
                <p class="h3 mb-4 font-italic">Пожалуйста, подождите...</p>
            </div>
        </div>

        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Поиск слов</div>
                        <div class="card-body">
                            <form id="search-form">
                                <?php print_select($dictionaries, "dictionaries"); ?>
                                <div class="form-group mb-3">
                                    <label>Режим поиска:</label><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="mode" id="mode-normal" value="normal" onchange="checkForMode(this)" checked>
                                        <label class="form-check-label" for="mode-normal">Обычный</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="mode" id="mode-extended" value="extended" onchange="checkForMode(this)">
                                        <label class="form-check-label" for="mode-extended">Расширенный</label>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="mask">Маска слова:</label>
                                    <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="? - любая буква; * - любые несколько букв. Пример: А???? - слова из 5 букв на А."><i class="fas fa-question-circle"></i></span>
                                    <input type="text" class="form-control" id="mask" name="mask" placeholder="Введите маску" maxlength="32" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="length">Длина слова:</label>
                                    <input type="number" class="form-control" id="length" name="length" placeholder="Введите длину слова" min="2" max="32">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="start">Начало слова:</label>
                                    <input type="text" class="form-control" id="start" name="start" placeholder="Введите начало слова" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="end">Конец слова:</label>
                                    <input type="text" class="form-control" id="end" name="end" placeholder="Введите конец слова" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="contains">Обязательное буквосочетание:</label>
                                    <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Указанное буквосочетание содержится в слове в указанном поряде(БЛ - яБЛоко) или в определённых позициях (Б?О - яБлОко)"><i class="fas fa-question-circle"></i></span>
                                    <input type="text" class="form-control" id="contains" name="contains" placeholder="Введите обязательное буквосочетание">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="include">Обязательные буквы:</label>
                                    <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Будут выведены только те слова, которые содержат введённые буквы"><i class="fas fa-question-circle"></i></span>
                                    <input type="text" class="form-control" id="include" name="include" placeholder="Введите обязательные буквы">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="exclude">Исключённые буквы:</label>
                                    <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Будут выведены только те слова, которые не содержат введённые буквы"><i class="fas fa-question-circle"></i></span>
                                    <input type="text" class="form-control" id="exclude" name="exclude" placeholder="Введите буквы, которые надо исключить">
                                </div>
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="limit" aria-label="Word limit per page">
                                        <option value="20" selected>20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <label for="limit">Лимит слов на странице: </label>
                                </div>
                                <div class="form-group form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="compound-words-checkbox" checked>
                                    <label class="form-check-label" for="compound-words-checkbox">Искать составные слова</label>
                                </div>
                                <button type="submit" class="btn btn-primary">Найти</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="results-container" class="container mt-5 d-none">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="form-floating">
                        <select class="form-select" id="sortSelect" aria-label="Sort select">
                            <option value="sort-word" selected>алфавиту</option>
                            <option value="sort-length">длине</option>
                        </select>
                        <label for="sortSelect">Сортировать по: </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="sortRadio" value="sortASC" id="sortASC">
                        <label class="form-check-label" for="sortASC">
                            Сортировать по возрастанию
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="sortRadio" value="sortDESC" id="sortDESC">
                        <label class="form-check-label" for="sortDESC">
                            Сортировать по убыванию
                        </label>
                    </div>
                    <!-- Контейнер для вывода результатов запроса -->
                    <div id="search-results"></div>
                </div>
            </div>
        </div>

        <!-- Bootstrap and jQuery scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
        <script type="text/javascript" src="assets/js/main.js"></script>

    </body>
</html>