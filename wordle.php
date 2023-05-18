<?php
$title = "Word Finder - Wordle";
require __DIR__ . '/header.php';

?>

<main class="container-fluid container-xl">
    <div class="pt-5 pb-3">
        <!-- Открыть инструкцию по применению -->
        <div class="modal fade" id="guideModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="guideModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content field-bg">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="guideModalLabel">WordFinder: Как играть в Wordle</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h1>Игра Wordle - разгадай слово</h1>
                        <p>Wordle – это словесная головоломка, в которой Вы должны угадать слово из 5 букв за шесть попыток или менее.</p>
                        <h2>Как играть в Wordle?</h2>
                        <h3>1. Введите первое слово</h3>
                        <p>Для начала просто введите любое слово из пяти букв, чтобы узнать, какие буквы соответствуют скрытому слову. Всего у вас будет 6 попыток отгадать спрятанное слово.</p>
                        <h3>2. Узнайте, какие буквы в загаданном слове</h3>
                        <p>Если буква отмечена зеленым цветом, значит, она есть в этом слове и находится в правильном месте. Если буква отмечена желтым цветом, это означает, что эта буква есть в скрытом слове, но не соответствует правильному месту в этом слове. Если буква отмечена серым, значит ее нет в скрытом слове.</p>
                        <h3>3. Попробуйте угадать спрятанное слово</h3>
                        <p>Теперь, если вы знаете несколько букв с точным расположением (зеленые) и несколько букв, которые входят в слово (желтые), вы можете попытаться разгадать загаданное слово и выиграть игру!</p>
                        <img src="assets/img/wordle-how-to.jpg" class="img-fluid rounded mx-auto d-block" alt="Как играть">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Понятно</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Открыть инструкцию по применению -->
        <div class="modal fade" id="settingsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content field-bg">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="settingsModalLabel">Настройки Wordle</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="settingsForm">
                        <div class="modal-body">
                            <div class="row align-items-center mb-3">
                                <div class="form-group col-sm-4">
                                    <label for="numberOfGuesses">Количество попыток: </label>
                                    <select class="form-select" name="numberOfGuesses" id="numberOfGuesses" aria-label="Number of guesses">
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6" selected>6</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="lettersInWord">Количество букв в слове: </label>
                                    <select class="form-select" name="lettersInWord" id="lettersInWord" aria-label="Number of letters in word">
                                        <option value="5" selected>5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="select-language">Язык поиска: </label>
                                    <select class="form-select" name="select-language" id="select-language" aria-label="Select dictionary's language" required>
                                        <option disabled selected>Пожалуйста, подождите</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Закрыть</button>
                            <button type="submit" class="btn btn-success" data-bs-dismiss="modal">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="game-container mt-5">
            <!-- Панель управления -->
            <div class="row mb-3">
                <div class="col">
                    <div class="d-flex justify-content-start">
                        <button id="btn-surrender" class="btn btn-danger" disabled>Сдаться</button>
                    </div>
                </div>
                <div class="col">
                    <div class="d-flex justify-content-end">
                        <button id="btn-settings" class="btn btn-secondary me-2"><i class="fas fa-cog" aria-hidden="true"></i></button>
                        <button id="btn-help" class="btn btn-secondary"><i class="fas fa-question" aria-hidden="true"></i></button>
                    </div>
                </div>
            </div>
            <!-- Toast победа -->
            <div class="toast-container position-absolute p-3 top-50 start-50 translate-middle">
                <div class="toast" id="toast-win" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
                    <div class="toast-header" style="background-color: #79b851">
                        <h5 class="fw-bold text-center text-black">Победа! 🏆</h5>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        <p class="fw-bold fs-5" id="message-win"></p>
                        <div class="d-flex justify-content-center">
                            <button type="button" id="button-win" class="btn btn-dark btn-lg text-center mx-auto">Новая игра</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Toast поражение -->
            <div class="toast-container position-absolute p-3 top-50 start-50 translate-middle">
                <div class="toast" id="toast-lose" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
                    <div class="toast-header bg-danger">
                        <h5 class="fw-bold text-center text-black">Поражение!</h5>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        <p class="fw-bold fs-5" id="message-lose"></p>
                        <div class="d-flex justify-content-center">
                            <button type="button" id="button-lose" class="btn btn-dark btn-lg text-center mx-auto">Новая игра</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Toast сообщение -->
            <div class="toast-container position-absolute p-3 top-0 start-50 translate-middle-x">
                <div class="toast align-items-center bg-warning" id="toast-info" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="1000">
                    <div class="d-flex justify-content-center">
                        <div class="toast-body">
                            <p class="fw-bold fs-6 pb-0 mb-0" id="message-info"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="game-board"></div>

            <div id="keyboard">
                <div class="keyboard-row">
                    <button class="keyboard-button" data-key="й">й</button>
                    <button class="keyboard-button" data-key="ц">ц</button>
                    <button class="keyboard-button" data-key="у">у</button>
                    <button class="keyboard-button" data-key="к">к</button>
                    <button class="keyboard-button" data-key="е">е</button>
                    <button class="keyboard-button" data-key="н">н</button>
                    <button class="keyboard-button" data-key="г">г</button>
                    <button class="keyboard-button" data-key="ш">ш</button>
                    <button class="keyboard-button" data-key="щ">щ</button>
                    <button class="keyboard-button" data-key="з">з</button>
                    <button class="keyboard-button" data-key="х">х</button>
                    <button class="keyboard-button" data-key="ъ">ъ</button>
                </div>
                <div class="keyboard-row">
                    <button class="keyboard-button" data-key="ф">ф</button>
                    <button class="keyboard-button" data-key="ы">ы</button>
                    <button class="keyboard-button" data-key="в">в</button>
                    <button class="keyboard-button" data-key="а">а</button>
                    <button class="keyboard-button" data-key="п">п</button>
                    <button class="keyboard-button" data-key="р">р</button>
                    <button class="keyboard-button" data-key="о">о</button>
                    <button class="keyboard-button" data-key="л">л</button>
                    <button class="keyboard-button" data-key="д">д</button>
                    <button class="keyboard-button" data-key="ж">ж</button>
                    <button class="keyboard-button" data-key="э">э</button>
                </div>
                <div class="keyboard-row">
                    <button class="keyboard-button keyboard-button-wide" data-key="Backspace"><svg xmlns="http://www.w3.org/2000/svg" data-key="Backspace" height="24" viewBox="0 0 24 24" width="24"><path data-key="Backspace" fill="var(--color-tone-1)" d="M22 3H7c-.69 0-1.23.35-1.59.88L0 12l5.41 8.11c.36.53.9.89 1.59.89h15c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H7.07L2.4 12l4.66-7H22v14zm-11.59-2L14 13.41 17.59 17 19 15.59 15.41 12 19 8.41 17.59 7 14 10.59 10.41 7 9 8.41 12.59 12 9 15.59z"></path></svg></button>
                    <button class="keyboard-button" data-key="я">я</button>
                    <button class="keyboard-button" data-key="ч">ч</button>
                    <button class="keyboard-button" data-key="с">с</button>
                    <button class="keyboard-button" data-key="м">м</button>
                    <button class="keyboard-button" data-key="и">и</button>
                    <button class="keyboard-button" data-key="т">т</button>
                    <button class="keyboard-button" data-key="ь">ь</button>
                    <button class="keyboard-button" data-key="б">б</button>
                    <button class="keyboard-button" data-key="ю">ю</button>
                    <button class="keyboard-button keyboard-button-wide" data-key="Enter">Enter</button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/footer.php' ?>
