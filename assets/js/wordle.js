let NUMBER_OF_GUESSES = 6; // Количество попыток
let LETTERS_IN_WORD = 5; // Количество букв в слове
const COLORS = Object.freeze({ // Цвета ячеек с буквами
    ABSENT: '#a4aec4', // Буквы нет в слове
    PRESENT: '#f3c237', // Буква на другом месте
    CORRECT: '#79b851' // Буква на правильном месте
});

let guessesRemaining = NUMBER_OF_GUESSES; // Количество оставшихся попыток
let currentGuess = []; // Текущий ответ
let currentLetter = -1; // Индекс текущей буквы
let answer = ""; // Загаданное слово

let surrenderButton = $("#btn-surrender");

$(document).ready(function () {
    // bootstrap toast сообщения
    let toastElList = [].slice.call(document.querySelectorAll('.toast'));
    let toastList = toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl)
    });

    let guideModal = new bootstrap.Modal(document.getElementById("guideModal"));
    let settingsModal = new bootstrap.Modal(document.getElementById("settingsModal"));

    surrenderButton.click(function (){
        guessesRemaining = 0;
        showLoseScreen();
    });

    $("#btn-settings").click(function () {
        settingsModal.show();
    });

    $("#btn-help").click(function () {
        guideModal.show();
    });

    $("#settingsForm").submit(function (event) {
        event.preventDefault(); // Отменяем стандартное поведение формы
        NUMBER_OF_GUESSES = parseInt($("#numberOfGuesses").find(':selected').val());
        LETTERS_IN_WORD = parseInt($("#lettersInWord").find(':selected').val());
        language = $("#select-language").find(':selected').val();
        patternBase = languages[language].regexp;
        newGame();
        console.log(NUMBER_OF_GUESSES);
        console.log(LETTERS_IN_WORD);
        console.log(language);
    });

    // Нажатие на кнопку клавиатуры
    document.addEventListener("keyup", (e) => {
        if (guessesRemaining === 0) return;

        let pattern = makePattern(patternBase, "[", "]", "gi");
        let pressedKey = String(e.key);
        if (pressedKey === "Backspace" && currentLetter !== -1) deleteLetter();
        else if (pressedKey === "Enter") checkGuess();
        else if (pressedKey.length === 1 && pressedKey.match(pattern)) insertLetter(pressedKey);
    });

    // Нажатие на кнопку клавиатуры на сайте
    document.getElementById("keyboard").addEventListener("click", (e) => {
        const target = e.target;
        if (target.dataset.key === undefined) return;
        let key = target.dataset.key;
        // Перенаправляем событие на keyup
        document.dispatchEvent(new KeyboardEvent("keyup", { key: key }));
    });

    $("#button-lose, #button-win").click(function (event) {
        if ($(event.target).attr('id')==='button-lose') $("#toast-lose").toast("hide");
        else $("#toast-win").toast("hide");
        newGame();
    });

    initGame();
});

function requestWord() {
    loadingMessage.text("Выбираем слово. Пожалуйста, подождите...");

    $.ajax({
        url: 'core/wordle.php',
        method: 'GET',
        dataType: 'json',
        contentType: false,
        cache: false,
        data: {language: language, length: LETTERS_IN_WORD},
        success: function (response) {
            // Выводим результаты запроса
            if (response.status === false) {
                alert(response.message);
            } else {
                answer = response.word;
                console.log(answer);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText); // выводим ответ сервера
            console.log('Error: ' + textStatus + ' - ' + errorThrown);
        }
    });
}

// Инициализация игры
function initGame() {
    requestWord();

    // Создание html-элемента игры
    let board = document.getElementById("game-board");

    for (let i = 0; i < NUMBER_OF_GUESSES; i++) {
        let row = document.createElement("div");
        row.className = "game-row";

        for (let j = 0; j < LETTERS_IN_WORD; j++) {
            let letter = document.createElement("div");
            letter.className = "letter";
            row.appendChild(letter);
        }

        board.appendChild(row);
    }
}

// Новая игра
function newGame() {
    surrenderButton.prop('disabled', true);
    guessesRemaining = NUMBER_OF_GUESSES;
    currentGuess = [];
    currentLetter = -1;

    $("#game-board").empty();
    for (const elem of document.getElementsByClassName("keyboard-button")) {
        elem.removeAttribute("style");
    }
    initGame();
}

// Окраска кнопок клавиатуры на сайте
function colorKeyboard(letter, color) {
    for (const elem of document.getElementsByClassName("keyboard-button")) {
        if (elem.textContent === letter) {
            let oldColor = elem.style.backgroundColor;
            if (oldColor === COLORS.CORRECT) return;
            if (oldColor === COLORS.PRESENT && color !== COLORS.CORRECT) return;

            elem.style.backgroundColor = color;
            break;
        }
    }
}

// Ввести в текущую ячейку нажатую кнопку
function insertLetter(pressedKey) {
    if (currentLetter === LETTERS_IN_WORD - 1) return; // Больше букв ввести нельзя
    pressedKey = pressedKey.toLowerCase();

    let row = document.getElementsByClassName("game-row")[NUMBER_OF_GUESSES - guessesRemaining];
    let letter = row.children[currentLetter + 1];
    animateCSS(letter, "pulse");
    letter.textContent = pressedKey;
    letter.classList.add("filled-letter");
    currentGuess.push(pressedKey);
    ++currentLetter;
}

// Удалить последнюю введённую букву из слова
function deleteLetter() {
    let row = document.getElementsByClassName("game-row")[NUMBER_OF_GUESSES - guessesRemaining];
    let letter = row.children[currentLetter];
    letter.textContent = "";
    letter.classList.remove("filled-letter");
    currentGuess.pop();
    --currentLetter;
}

// Проверка введённого слова
function checkGuess() {
    let row = document.getElementsByClassName("game-row")[NUMBER_OF_GUESSES - guessesRemaining]; // Текущий ряд
    let guessString = "";
    let rightGuess = Array.from(answer);

    for (const val of currentGuess) guessString += val;

    if (guessString.length !== LETTERS_IN_WORD) {
        $("#message-info").text("Недостаточно букв");
        $("#toast-info").toast("show");
        return;
    }

    if (surrenderButton.prop('disabled')) surrenderButton.prop('disabled', false);
    let letterColor = [COLORS.ABSENT, COLORS.ABSENT, COLORS.ABSENT, COLORS.ABSENT, COLORS.ABSENT];

    // Проверка на то, находится ли буква на правильном месте
    for (let i = 0; i < LETTERS_IN_WORD; i++) {
        if (rightGuess[i] === currentGuess[i]) {
            letterColor[i] = COLORS.CORRECT;
            rightGuess[i] = "#";
        }
    }

    // Проверка на то, есть ли введённая буква в загаданном слове
    for (let i = 0; i < LETTERS_IN_WORD; i++) {
        if (letterColor[i] === COLORS.CORRECT) continue;

        for (let j = 0; j < LETTERS_IN_WORD; j++) {
            if (rightGuess[j] === currentGuess[i]) {
                letterColor[i] = COLORS.PRESENT;
                rightGuess[j] = "#";
            }
        }
    }

    // Анимация введённого ответа
    for (let i = 0; i < LETTERS_IN_WORD; i++) {
        let letter = row.children[i];
        let delay = 250 * i;
        setTimeout(() => {
            animateCSS(letter, "flipInX");
            letter.style.backgroundColor = letterColor[i];
            colorKeyboard(guessString.charAt(i) + "", letterColor[i]);
        }, delay);
    }

    if (guessString === answer) { // Победа: ввод совпал с загаданным словом
        let guessCount = NUMBER_OF_GUESSES - guessesRemaining + 1;
        $("#message-win").text("Поздравляем! Вы отгадали слово за " + guessCount + " " + getNoun(guessCount, "попытку", "попытки", "попыток"));
        $("#toast-win").toast("show");
        guessesRemaining = 0;
    } else {
        guessesRemaining -= 1;
        currentGuess = [];
        currentLetter = -1;

        if (guessesRemaining === 0) { // Поражение: осталось 0 попыток
           showLoseScreen();
        }
    }
}

function showLoseScreen() {
    $("#message-lose").text("Загаданное слово: " + answer);
    $("#toast-lose").toast("show");
}

// Анимация букв
const animateCSS = (element, animation, prefix = "animate__") =>
    // We create a Promise and return it
    new Promise((resolve, reject) => {
        const animationName = `${prefix}${animation}`;
        // const node = document.querySelector(element);
        const node = element;
        node.style.setProperty("--animate-duration", "0.3s");

        node.classList.add(`${prefix}animated`, animationName);

        // When the animation ends, we clean the classes and resolve the Promise
        function handleAnimationEnd(event) {
            event.stopPropagation();
            node.classList.remove(`${prefix}animated`, animationName);
            resolve("Animation ended");
        }

        node.addEventListener("animationend", handleAnimationEnd, { once: true });
    });
