import { WORDS } from "./words.js";

const NUMBER_OF_GUESSES = 6; // Количество попыток
const COLORS = Object.freeze({ // Цвета ячеек с буквами
    ABSENT: '#a4aec4', // Буквы нет в слове
    PRESENT: '#f3c237', // Буква на другом месте
    CORRECT: '#79b851' // Буква на правильном месте
});

let guessesRemaining = NUMBER_OF_GUESSES; // Количество оставшихся попыток
let currentGuess = []; // Текущий ответ
let currentLetter = -1; // Индекс текущей буквы
let answer = ""; // Загаданное слово

$(document).ready(function () {
    // bootstrap toast сообщения
    let toastElList = [].slice.call(document.querySelectorAll('.toast'));
    let toastList = toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl)
    });

    //$("#toast-info").toast("show");

    // Нажатие на кнопку клавиатуры
    document.addEventListener("keyup", (e) => {
        if (guessesRemaining === 0) return;

        let pressedKey = String(e.key);
        if (pressedKey === "Backspace" && currentLetter !== -1) deleteLetter();
        else if (pressedKey === "Enter") checkGuess();
        else if (pressedKey.length === 1 && pressedKey.match(/[а-я]/gi)) insertLetter(pressedKey);
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

// Инициализация игры
function initGame() {
    answer = WORDS[Math.floor(Math.random() * WORDS.length)];
    console.log(answer);

    // Создание html-элемента игры
    let board = document.getElementById("game-board");

    for (let i = 0; i < NUMBER_OF_GUESSES; i++) {
        let row = document.createElement("div");
        row.className = "game-row";

        for (let j = 0; j < 5; j++) {
            let letter = document.createElement("div");
            letter.className = "letter";
            row.appendChild(letter);
        }

        board.appendChild(row);
    }
}

// Новая игра
function newGame() {
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

    if (guessString.length !== 5) {
        $("#message-info").text("Недостаточно букв");
        $("#toast-info").toast("show");
        return;
    }

    if (!WORDS.includes(guessString)) {
        $("#message-info").text("Слово не найдено");
        $("#toast-info").toast("show");
        return;
    }

    let letterColor = [COLORS.ABSENT, COLORS.ABSENT, COLORS.ABSENT, COLORS.ABSENT, COLORS.ABSENT];

    // Проверка на то, находится ли буква на правильном месте
    for (let i = 0; i < 5; i++) {
        if (rightGuess[i] === currentGuess[i]) {
            letterColor[i] = COLORS.CORRECT;
            rightGuess[i] = "#";
        }
    }

    // Проверка на то, есть ли введённая буква в загаданном слове
    for (let i = 0; i < 5; i++) {
        if (letterColor[i] === COLORS.CORRECT) continue;

        for (let j = 0; j < 5; j++) {
            if (rightGuess[j] === currentGuess[i]) {
                letterColor[i] = COLORS.PRESENT;
                rightGuess[j] = "#";
            }
        }
    }

    // Анимация введённого ответа
    for (let i = 0; i < 5; i++) {
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
            $("#message-lose").text("Загаданное слово: " + answer);
            $("#toast-lose").toast("show");
        }
    }
}

// Ввести в текущую ячейку нажатую кнопку
function insertLetter(pressedKey) {
    if (currentLetter === 4) return; // Больше ввести нельзя: уже введено 5 букв
    pressedKey = pressedKey.toLowerCase();

    let row = document.getElementsByClassName("game-row")[NUMBER_OF_GUESSES - guessesRemaining];
    let letter = row.children[currentLetter + 1];
    animateCSS(letter, "pulse");
    letter.textContent = pressedKey;
    letter.classList.add("filled-letter");
    currentGuess.push(pressedKey);
    ++currentLetter;
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
