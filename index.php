<?php

require_once "class/DbConnect.php";
$dbConnect = new DbConnect("user", "");

$dictionaries = [];
$stmt = $dbConnect->getPDO()->query("SELECT * FROM `dictionaries`");
while ($row = $stmt->fetch()) $dictionaries[] = $row;
$dbConnect->closeConnection();

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

        <!-- Открыть инструкцию по применению -->
        <div class="modal fade" id="guideModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="guideModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="guideModalLabel">Как пользоваться Word Finder</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Данная инструкция поможет Вам разобраться в интерфейсе сайта и приступить к поиску.</p>
                        <h4>1. Выберите режим поиска слов</h4>
                        <p>Word Finder позволяет Вам производить поиск в двух режимах: обычном и расширенном. В обычном режиме из полей ввода доступна только маска слова, а в расширенном режиме такие параметры, как начало и конец слова, длина слова, обязательное буквосочетания, обязательные и исключённые буквы.</p>
                        <h4>2. Выберите язык поиска слов</h4>
                        <p>Вы можете искать слова на разных языках, поддерживаемых системой.</p>
                        <h4>3. Выберите словари для поиска</h4>
                        <p>Для поиска необходимо выбрать как минимум один из словарей, имеющихся в системе. Вы можете выбрать несколько словарей, чтобы увеличить количество слов поиска.</p>
                        <h4>4. Введите нужные для поиска параметры</h4>
                        <p>В обычном режиме поиска потребуется только маска слова. В расширенном режиме необходимо ввести начало и конец слова, а остальные параметры - по желанию. Если Вам непонятно, за что отвечает какой-либо параметр - наведите на вопросительный знак возле названия этого параметра.</p>
                        <h4>(Дополнительно) Укажите дополнительные настройки</h4>
                        <p>Вы можете выбрать количество найденных слов на странице (20, 50 или 100), а также указать, нужно ли искать составные слова.</p>
                        <h4>5. Нажмите на кнопку "Найти"</h4>
                        <p>Через небольшой промежуток времени система выведет результаты Вашего поиска или их отсутствие. Также Вы можете отсортировать результаты по алфавиту или длине слов. Для просмотра следующей страницы результатов используйте навигацию ниже результатов запроса.</p>
                        <div class="text-center">
                            <img src="assets/img/guide.png" class="img-fluid" alt="Инструкция по применению">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="p-3 mb-3 field-bg rounded-3">
                        <h1>Добро пожаловать на Word Finder!</h1>
                        <p><a class="link-success fw-bold" href="/">Word Finder</a> - сайт, который поможет отыскать слова, которые Вы ищете.</p>
                        <h3>Нужна помощь с поиском слов?</h3>
                        <p>21 век подарил нам много новых понятий, заимствованных слов, внес существенные коррективы в лексический состав языка. К сожалению, человеческая память обладает свойством забывать слова, которые мы не используем в повседневной речи.</p>
                        <p>Сканворды – отличный способ скоротать время в долгой поездке, неплохой вариант для проведения рабочего перерыва. Но всё знать невозможно – бывают сложные технические, географические и другие термины, которые вызывают затруднения.</p>
                        <p>В таких случаях может пригодится <a class="link-success fw-bold" href="/">Word Finder</a>, который поможет отыскать нужные слова по маске или другим параметрам.</p>
                        <h3>Наши услуги</h3>
                        <p>Что мы можем предложить нашим пользователям?</p>
                        <ul>
                            <li>Множество параметров. Вы можете производить поиск только с помощью маски (обычный режим) или введя нужные Вам параметры (расширенный режим), такие как длина слова, начало и конец слова и т.д.</li>
                            <li>Эргономичный интерфейс. В интерфейсе сайта довольно просто разобраться и можно сразу приступать к поиску. Но если это вызывает у Вас трудности, вы можете ознакомиться с <a id="openGuide" class="link-success fw-bold fs-5" href="javascript:void(0);">инструкцией по применению.</a></li>
                            <li>Быстрое получение ответа. Система не заставит вас долго ждать вывода результатов Вашего поиска.</li>
                        </ul>
                    </div>
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
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mode" id="mode-regexp" value="regexp">
                                            <label class="form-check-label" for="mode-regexp">RegExp</label>
                                            <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Поиск для продвинутых пользователей на основе регулярных выражений"><i class="fas fa-question-circle"></i></span>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label for="select-language">Язык поиска: </label>
                                        <select class="form-select" name="select-language" id="select-language" aria-label="Select dictionary's language" required>
                                            <option disabled selected>Пожалуйста, подождите</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3">
                                    <div class="form-group">
                                        <label for="dictionaries[]">Выберите словари для поиска:</label>
                                        <select id="select-dictionaries" class="form-select" multiple name="dictionaries[]" data-placeholder="Выберите словари" aria-label="Select dictionary" required>
                                            <?php print_select_options($dictionaries); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3" id="normal-mode-parameters">
                                    <div class="form-group">
                                        <label for="mask">Маска слова:</label>
                                        <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="? - любая буква; * - любые несколько букв. Пример: А???? - слова из 5 букв на А."><i class="fas fa-question-circle"></i></span>
                                        <input type="text" class="form-control" id="mask" name="mask" placeholder="Введите маску" maxlength="32" required>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3" id="regexp-mode-parameters">
                                    <div class="form-group">
                                        <label for="regexp">Регулярное выражение:</label>
                                        <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="? - любая буква; * - любые несколько букв. Пример: А???? - слова из 5 букв на А."><i class="fas fa-question-circle"></i></span>
                                        <input type="text" class="form-control" id="regexp" name="regexp" placeholder="Введите регулярное выражение" required>
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
                    <!-- Контейнер для вывода результатов запроса -->
                    <div id="search-results"></div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/footer.php' ?>