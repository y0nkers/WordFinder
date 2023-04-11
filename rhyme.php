<?php
$title = "Word Finder - поиск рифмы";
require __DIR__ . '/header.php';

?>

<main class="container-fluid container-xl">
    <div class="pt-5 pb-3">
        <!-- Загрузочный экран -->
        <div id="loading" style="display: none;">
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
                        <h1>Особенности рифмы</h1>
                        <p>Рифма - это звуковое сочетание, которое придает стихотворению ритмическую и музыкальную выразительность. Одной из особенностей рифмы является ее влияние на структуру и звучание стихотворения. Рифма может создавать сильный эффект, привлекая внимание читателя или слушателя и подчеркивая важность определенных слов или идей.</p>
                        <p>Современные тенденции стихосложения таковы, что все чаще поэты прибегают к поиску неточной, а временами и составной рифмы. Не всегда удается найти слово, похожее по звучанию. При этом надо еще и сохранить смысл в стихотворении.</p>
                        <h3>Поиск рифмы</h3>
                        <p>В таком случае <a class="link-success" href="/">Word Finder</a> придет вам на помощь. Вам достаточно ввести в указанное поле слово, к которому вы ищете рифму. Поиск рифмы - простой и удобный способ сделать Ваше творчество более гармоничным и убедительным.</p>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Форма поиска -->
                    <div class="card border border-dark">
                        <div class="card-header bg-dark text-white">Поиск рифмы</div>
                        <div class="card-body rounded-3 field-bg">
                            <form class="d-flex justify-content-start align-items-center flex-wrap flex-sm-nowrap" id="rhyme-form">
                                <label class="col-md-2 me-sm-2 mb-sm-0 form-label" for="word_rhyme">Введите слово</label>
                                <input class="form-control me-sm-2 mb-2 mb-sm-0" type="text" id="word_rhyme" name="word_rhyme" placeholder="Слово" required>
                                <button type="submit" class="btn btn-dark ms-auto">Найти</button>
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
