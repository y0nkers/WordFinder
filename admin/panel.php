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

require_once "../class/DbConnect.php";
$dbConnect = new DbConnect("admin", "wordfinder");

$dictionaries = [];
$stmt = $dbConnect->getPDO()->query("SELECT * FROM `dictionaries`");
while ($row = $stmt->fetch()) $dictionaries[] = $row;
$dbConnect->closeConnection();

/**
 * Вывод таблицы-справочника о всех имеющихся словарях
 * @param array $data массив с результатами запроса
 * @return void
 */
function print_dictionaries(array $data): void
{
    $table_data = "";
    for ($i = 0; $i < count($data); $i++) {
        $table_data .= "<tr>";
        $table_data .= "<td>" . $data[$i]["name"] . "<i class='fa-solid fa-pen ps-2' onclick='updateDictionary(\"" . $data[$i]["name"] . "\")'></i></td>";
        $table_data .= "<td class='dictionary-language text-center'>" . $data[$i]["language"] . "</td>";
        $table_data .= "<td class='text-center'>" . $data[$i]["count"] . " " . get_noun($data[$i]["count"], 'слово', 'слова', 'слов') . "</td>";
        $table_data .= "<td><a href='dictionary?id=" . $data[$i]["id"] . "&name=" . $data[$i]["name"] . "&language=" . $data[$i]["language"] . "' title='Редактировать'><i class='fa-solid fa-search'></i></a></td>";
        $table_data .= "<td><i class='fa-solid fa-trash' style='color: red' onclick='deleteDictionary(\"" . $data[$i]["name"] . "\")' title='Удалить'></i></td>";
        $table_data .= "</tr>";
    }
    echo $table_data;
}

// Окончание слова в зависимости от количества
function get_noun($number, $one, $two, $five) {
    $n = abs($number);
    $n %= 100;
    if ($n >= 5 && $n <= 20) return $five;
    $n %= 10;
    if ($n === 1) return $one;
    if ($n >= 2 && $n <= 4) return $two;
    return $five;
}

/**
 * Создание списка всех доступных словарей
 * @param array $data массив с результатами запроса
 * @return void
 */
function print_select_options(array $data): void
{
    $select_html = "";
    foreach ($data as $item) {
        $select_html .= '<option value="' . $item["id"] . '" data-language="'. $item["language"] . '">' . $item["name"] . ' [' . $item["language"] . ']' . '</option>';
    }
    echo $select_html;
}

$title = "Word Finder - Админ-панель";
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

        <!-- Добавить словарь -->
        <div class="modal fade" id="addDictionaryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addDictionaryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="addDictionaryModalLabel">Добавить словарь</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addDictionaryForm">
                            <div class="form-group mb-3">
                                <label for="addDictionaryName">Название словаря:</label>
                                <input type="text" class="form-control" id="addDictionaryName" name="addDictionaryName" placeholder="Будет отображаться у пользователя при выборе" maxlength="32" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="select-language">Язык словаря: </label>
                                <select class="form-select" name="select-language" id="select-language" aria-label="Select dictionary's language" required>
                                    <option disabled selected>Пожалуйста, подождите</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="addDictionaryWords">Загрузите файл со словами</label>
                                <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Текстовый файл должен содержать на каждой строке только одно слово длиной не более 32 букв (остальные буквы будут обрезаны)."><i class="fas fa-question-circle"></i></span>
                                <input type="file" id="addDictionaryWords" name="addDictionaryWords" accept="text/plain" required>
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
                                <label for="select-add">Выберите словарь:</label>
                                <select id="selectAddWords" class="form-select" multiple name="select-add" aria-label="Select dictionary" required>
                                    <?php print_select_options($dictionaries); ?>
                                </select>
                            </div>
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
                                <label for="select-delete">Выберите словарь:</label>
                                <select id="selectDeleteWords" class="form-select" multiple name="select-delete" aria-label="Select dictionary" required>
                                    <?php print_select_options($dictionaries); ?>
                                </select>
                            </div>
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
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h3 class="text-center">Список доступных словарей</h3>
                    <table class="table table-bordered table-hover table-striped table-secondary border border-dark">
                        <thead>
                            <tr class="table-dark text-center">
                                <th class="col-6" style="width: 50%;">Название</th>
                                <th class="col-2" style="width: 25%;">Язык</th>
                                <th class="col-2" style="width: 25%;">Количество слов</th>
                                <th class="col-1"></th>
                                <th class="col-1"></th>
                            </tr>
                        </thead>
                        <?php print_dictionaries($dictionaries); ?>
                    </table>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDictionaryModal">Добавить словарь</button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require '../footer.php'; ?>
