<?php
require_once "core/connect.php";
/** @var PDO $connect */

$dictionaries = [];
$stmt = $connect->query("SELECT * FROM `dictionaries`");
while ($row = $stmt->fetch()) $dictionaries[] = $row;

/**
 * Создание списка всех доступных словарей
 * @param array $data массив с результатами запроса
 * @return void
 */

function print_select_options(array $data): void
{
    $select_html = "";
    foreach ($data as $item) {
        $select_html .= '<option value="' . $item["id"] . '" data-language="' . $item["language"] . '">' . $item["name"] . ' [' . $item["language"] . ', слов: ' . $item["count"] . ']' . '</option>';
    }
    echo $select_html;
}

$title = "Word Finder - главная";
require __DIR__ . '/header.php';
?>

<main class="container-fluid container-xl">
    <div class="pt-5 pb-3">
        <!-- Загрузочный экран -->
        <div id="loading">
            <div class="container-fluid bg-light border border-dark rounded-3">
                <div class="d-flex justify-content-center mt-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Загрузка...</span>
                    </div>
                </div>
                <p class="h3 mb-4 font-italic" id="loading-message"></p>
                <p class="h4 mb-4 font-italic">Пожалуйста, подождите...</p>
            </div>
        </div>

        <!-- Форма поиска -->
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Поиск слов</div>
                        <div class="card-body">
                            <form id="search-form">
                                <div class="form-group mb-3">
                                    <label for="select-language">Язык поиска: </label>
                                    <select class="form-select" name="select-language" id="select-language" aria-label="Select dictionary's language" required>
                                        <option disabled selected>Пожалуйста, подождите</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="dictionaries[]">Выберите словари для поиска:</label>
                                    <select id="select-dictionaries" class="form-select" multiple name="dictionaries[]" aria-label="Select dictionary" required>
                                        <?php print_select_options($dictionaries); ?>
                                    </select>
                                </div>
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
                                    <select class="form-select" name="limit" id="limit" aria-label="Word limit per page">
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

        <!-- Результаты поиска -->
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
    </div>
</main>

<?php require __DIR__ . '/footer.php' ?>