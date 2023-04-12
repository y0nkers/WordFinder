import { WORDS } from "./words.js";

const NUMBER_OF_GUESSES = 6;
const COLORS = Object.freeze({
    ABSENT: '#a4aec4',
    PRESENT: '#f3c237',
    CORRECT: '#79b851'
});

let guessesRemaining = NUMBER_OF_GUESSES;
let currentGuess = [];
let nextLetter = 0;
let answer = WORDS[Math.floor(Math.random() * WORDS.length)];

console.log(answer);

function initBoard() {
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

function deleteLetter() {
    let row = document.getElementsByClassName("game-row")[NUMBER_OF_GUESSES - guessesRemaining];
    let letter = row.children[nextLetter - 1];
    letter.textContent = "";
    letter.classList.remove("filled-letter");
    currentGuess.pop();
    nextLetter -= 1;
}

function checkGuess() {
    let row = document.getElementsByClassName("game-row")[NUMBER_OF_GUESSES - guessesRemaining];
    let guessString = "";
    let rightGuess = Array.from(answer);

    for (const val of currentGuess) {
        guessString += val;
    }

    if (guessString.length !== 5) {
        toastr.error("Not enough letters!");
        return;
    }

    if (!WORDS.includes(guessString)) {
        toastr.error("Word not in list!");
        return;
    }

    let letterColor = [COLORS.ABSENT, COLORS.ABSENT, COLORS.ABSENT, COLORS.ABSENT, COLORS.ABSENT];

    //check green
    for (let i = 0; i < 5; i++) {
        if (rightGuess[i] === currentGuess[i]) {
            letterColor[i] = COLORS.CORRECT;
            rightGuess[i] = "#";
        }
    }

    //check yellow
    //checking guess letters
    for (let i = 0; i < 5; i++) {
        if (letterColor[i] === COLORS.CORRECT) continue;

        //checking right letters
        for (let j = 0; j < 5; j++) {
            if (rightGuess[j] === currentGuess[i]) {
                letterColor[i] = COLORS.PRESENT;
                rightGuess[j] = "#";
            }
        }
    }

    for (let i = 0; i < 5; i++) {
        let letter = row.children[i];
        let delay = 250 * i;
        setTimeout(() => {
            animateCSS(letter, "flipInX");
            letter.style.backgroundColor = letterColor[i];
            colorKeyboard(guessString.charAt(i) + "", letterColor[i]);
        }, delay);
    }

    if (guessString === answer) {
        toastr.success("Победа! Вы угадали слово!");
        guessesRemaining = 0;
    } else {
        guessesRemaining -= 1;
        currentGuess = [];
        nextLetter = 0;

        if (guessesRemaining === 0) {
            toastr.error("Проигрыш! У вас закончились попытки!");
            toastr.info(`Загаданное слово: "${answer}"`);
        }
    }
}

function insertLetter(pressedKey) {
    if (nextLetter === 5) return;
    pressedKey = pressedKey.toLowerCase();

    let row = document.getElementsByClassName("game-row")[NUMBER_OF_GUESSES - guessesRemaining];
    let letter = row.children[nextLetter];
    animateCSS(letter, "pulse");
    letter.textContent = pressedKey;
    letter.classList.add("filled-letter");
    currentGuess.push(pressedKey);
    ++nextLetter;
}

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

$(document).ready(function () {
    document.addEventListener("keyup", (e) => {
        if (guessesRemaining === 0) return;

        let pressedKey = String(e.key);
        if (pressedKey === "Backspace" && nextLetter !== 0) deleteLetter();
        else if (pressedKey === "Enter") checkGuess();
        else if (pressedKey.length === 1 && pressedKey.match(/[а-я]/gi)) insertLetter(pressedKey);
    });

    document.getElementById("keyboard").addEventListener("click", (e) => {
        const target = e.target;
        if (!target.classList.contains("keyboard-button")) return;

        let key = target.dataset.key;
        document.dispatchEvent(new KeyboardEvent("keyup", { key: key }));
    });

    initBoard();
});