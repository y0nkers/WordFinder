<?php
session_start();
if ($_SESSION['login'] != 'admin' || $_SESSION['password'] != '4bd68659613c4f414ce81071566f10c1') {
    $_SESSION['error'] = "Неправильный логин или пароль";
    header('Location: login.php');
}
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    // session started more than 30 minutes ago
    session_regenerate_id(true); // change session ID for the current session and invalidate old session ID
    unset($_SESSION['created']);
    unset($_SESSION['login']);
    unset($_SESSION['password']);
    header('Location: login.php');
}

$id = $_GET["id"];
$name = $_GET["name"];
$language = $_GET["language"];
if (!isset($id) || !isset($name)) header('Location: panel.php');

$title = 'Word Finder - Информация о словаре "' . $name . '"';
require '../header.php';
?>

<main class="container-fluid container-xl pt-5">
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

        <!-- Добавить слова -->
        <div class="modal fade" id="addWordsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addWordsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="addWordsModalLabel">Добавить слова</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addWordsForm">
                            <div class="form-group mb-3">
                                <label class="form-check-label">Режим добавления</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="addWordsType" id="addFromFileRadio" value="addFromFile" checked required>
                                    <label class="form-check-label" for="addFromFileRadio">
                                        Загрузка из файла
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="addWordsType" id="addFromTextareaRadio" value="addFromText">
                                    <label class="form-check-label" for="addFromTextareaRadio">
                                        Добавление из поля ввода
                                    </label>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="addWordsFile">Загрузите файл со словами</label>
                                <span id="addWordsInputTooltip" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Текстовый файл должен содержать на каждой строке только одно слово длиной не более 32 букв (остальные буквы будут обрезаны)."><i class="fas fa-question-circle"></i></span>
                                <span id="addWordsTextareaTooltip" class="d-none" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Каждое слово должно быть на отдельной строке. Слова длиной более 32 букв будут обрезаны."><i class="fas fa-question-circle"></i></span>
                                <input id="addWordsFile" type="file" name="addWordsFile" accept="text/plain" required>
                                <textarea class="form-control d-none" id="addWordsTextarea" rows="3" disabled required></textarea>
                            </div>
                            <div class="modal-footer pb-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отменить</button>
                                <button type="submit" class="btn btn-primary">Добавить</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Удалить слова -->
        <div class="modal fade" id="deleteWordsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteWordsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="deleteWordsModalLabel">Удалить слова</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="deleteWordsForm">
                            <div class="form-group mb-3">
                                <label for="deleteWordsInput" class="form-label">Введите слова для удаления</label>
                                <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Каждое слово должно быть на отдельной строке. Слова длиной более 32 букв будут обрезаны."><i class="fas fa-question-circle"></i></span>
                                <textarea class="form-control" id="deleteWordsInput" rows="3" required></textarea>
                            </div>
                            <div class="modal-footer pb-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                <button type="submit" class="btn btn-primary">Удалить</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt-3">
            <div class="row justify-content-center pb-2">
                <div class="col-md-6">
                    <h3 class="text-center">Информация о словаре "<?php echo $name; ?>"</h3>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Форма поиска -->
                    <div class="card border border-dark">
                        <div class="card-header bg-dark text-white">Поиск слов</div>
                        <div class="card-body rounded-3 field-bg">
                            <form id="search-form">
                                <div class="row align-items-center mb-3">
                                    <div class="form-group col-sm-6">
                                        <label>Режим поиска:</label><br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mode" id="mode-normal" value="normal" checked>
                                            <label class="form-check-label" for="mode-normal">Обычный</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mode" id="mode-extended" value="extended">
                                            <label class="form-check-label" for="mode-extended">Расширенный</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3" id="normal-mode-parameters">
                                    <div class="form-group">
                                        <label for="mask">Маска слова:</label>
                                        <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="? - любая буква; * - любые несколько букв. Пример: А???? - слова из 5 букв на А."><i class="fas fa-question-circle"></i></span>
                                        <input type="text" class="form-control" id="mask" name="mask" placeholder="Введите маску" maxlength="32" required>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3" id="extended-mode-parameters-1">
                                    <div class="form-group col-sm-6">
                                        <label for="start">Начало слова:</label>
                                        <input type="text" class="form-control" id="start" name="start" placeholder="Введите начало слова" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label for="end">Конец слова:</label>
                                        <input type="text" class="form-control" id="end" name="end" placeholder="Введите конец слова" required>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3" id="extended-mode-parameters-2">
                                    <div class="form-group col-sm-5">
                                        <label for="length">Длина слова:</label>
                                        <input type="number" class="form-control" id="length" name="length" placeholder="Введите длину слова" min="2" max="32">
                                    </div>
                                    <div class="form-group col-sm-7">
                                        <label for="contains">Обязательное буквосочетание:</label>
                                        <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Указанное буквосочетание содержится в слове в указанном поряде(БЛ - яБЛоко) или в определённых позициях (Б?О - яБлОко)"><i class="fas fa-question-circle"></i></span>
                                        <input type="text" class="form-control" id="contains" name="contains" placeholder="Введите обязательное буквосочетание">
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3" id="extended-mode-parameters-3">
                                    <div class="form-group col-sm-6">
                                        <label for="include">Обязательные буквы:</label>
                                        <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Будут выведены только те слова, которые содержат введённые буквы"><i class="fas fa-question-circle"></i></span>
                                        <input type="text" class="form-control" id="include" name="include" placeholder="Введите обязательные буквы">
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label for="exclude">Исключённые буквы:</label>
                                        <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Будут выведены только те слова, которые не содержат введённые буквы"><i class="fas fa-question-circle"></i></span>
                                        <input type="text" class="form-control" id="exclude" name="exclude" placeholder="Введите буквы, которые надо исключить">
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3">
                                    <h5 class="card-title">Дополнительные настройки</h5>
                                    <div class="form-floating col-sm-6">
                                        <select class="form-select" name="limit" id="limit" aria-label="Word limit per page">
                                            <option value="20" selected>20</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                        <label for="limit">Лимит слов на странице: </label>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input class="form-check-input" type="checkbox" id="compound-words-checkbox" checked>
                                        <label class="form-check-label" for="compound-words-checkbox">Искать составные слова</label>
                                    </div>
                                </div>
                                <div class="row justify-content-end">
                                    <div class="col-auto">
                                        <button type="button" id="resetForm" class="btn btn-danger">Сбросить</button>
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-primary">Найти</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <select class="form-select" id="sortSelect" aria-label="Sort select">
                            <option value="sort-word" selected>алфавиту</option>
                            <option value="sort-length">длине</option>
                        </select>
                        <label for="sortSelect">Сортировать по: </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="sortRadio" value="sortASC" id="sortASC" checked>
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
                </div>
            </div>
        </div>


        <!-- Результаты поиска -->
        <div id="results-container" class="container mt-3 d-none">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <!-- Кнопки вызова диалоговых окон -->
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWordsModal">Добавить слова</button>
                        <button type="button" class="btn btn-primary ml-auto" data-bs-toggle="modal" data-bs-target="#deleteWordsModal">Удалить слова</button>
                    </div>
                    <!-- Контейнер для вывода результатов запроса -->
                    <div id="search-results"></div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require '../footer.php'; ?>
