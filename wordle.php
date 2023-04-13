<?php
$title = "Word Finder - Wordle";
require __DIR__ . '/header.php';

?>

<main class="container-fluid container-xl">
    <div class="pt-5 pb-3">
        <div class="game-container mt-5">
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
                    <button class="keyboard-button keyboard-button-wide" data-key="Backspace"><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24" class="game-icon" data-testid="icon-backspace"><path fill="var(--color-tone-1)" d="M22 3H7c-.69 0-1.23.35-1.59.88L0 12l5.41 8.11c.36.53.9.89 1.59.89h15c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H7.07L2.4 12l4.66-7H22v14zm-11.59-2L14 13.41 17.59 17 19 15.59 15.41 12 19 8.41 17.59 7 14 10.59 10.41 7 9 8.41 12.59 12 9 15.59z"></path></svg></button>
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
