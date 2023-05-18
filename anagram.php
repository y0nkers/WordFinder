<?php
$title = "Word Finder - решение анаграмм";
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

        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="p-3 mb-3 field-bg rounded-3">
                        <h1>Решение анаграмм</h1>
                        <p>Сайт <a class="link-success fw-bold" href="/">Word Finder</a> поможет Вам решить анаграммы.</p>
                        <h3>Особенности анаграмм</h3>
                        <p>Анаграмма – литературный приём, состоящий в перестановке букв определённого слова, что в результате даёт другое слово. Польза анаграмм заключается в том, что их составление развивает комбинаторное мышление. Такое мышление очень важно в любой деятельности людей.</p>
                        <p>Данный раздел поможет тем, кто:</p>
                        <ul>
                            <li>Увлечён разгадыванием и составлением анаграмм.</li>
                            <li>Любит игры со словами.</li>
                            <li>Хочет найти слова из букв, составляющих какое-либо слово.</li>
                        </ul>
                        <p>Воспользоваться решением анаграмм на <a class="link-success fw-bold" href="/">Word Finder</a> очень легко. Вам достаточно ввести слово или заданные буквы в указанное поле и система выдаст решения.</p>
                        </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Форма поиска -->
                    <div class="card border border-dark">
                        <div class="card-header bg-dark text-white">Решение и составление анаграмм</div>
                        <div class="card-body rounded-3 field-bg">
                            <form id="anagram-form">
                                <div class="row align-items-center mb-3">
                                    <div class="form-group col-sm-6">
                                        <label>Выберите действие:</label><br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="action" id="action-solve" value="solve" checked>
                                            <label class="form-check-label" for="action-solve">Решить анаграмму</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="action" id="action-make" value="make">
                                            <label class="form-check-label" for="action-make">Составить анаграмму</label>
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
                                        <label class="me-sm-2 mb-sm-0 form-label" for="word_anagram">Введите слово или анаграмму</label>
                                        <div class="input-group">
                                            <input class="form-control me-2" type="text" id="word_anagram" name="word_anagram" placeholder="Слово/анаграмма" required>
                                            <button type="submit" id="anagram-btn" class="rounded btn btn-dark">Решить</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Результаты поиска -->
        <div id="results-container" class="container d-none">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Контейнер для вывода результатов запроса -->
                    <div class="mb-3" id="search-results"></div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/footer.php' ?>
