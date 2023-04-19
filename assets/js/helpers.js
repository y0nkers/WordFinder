let language = "russian";
let patternBase = "а-яё";
let languages = null;

let loading = $("#loading"); // Элемент "загрузочный экран"
let loadingMessage = $("#loading-message"); // Сообщение в загрузочном экране

async function loadLanguages() {
    const response = await fetch('/languages.json');
    languages = await response.json();
}

$(document).ready(function () {
    loadingMessage.text("Загрузка доступных языков...");
    loadLanguages().then(_ => {
        console.log("Languages loaded!");
        let select = $("#select-language");
        select.empty();
        // Добавляем в select каждый язык из json файла
        $.each(languages, function (key, value) {
            select.append($("<option>", {
                value: key,
                text: value.name
            }));
        });
        loading.hide();
    });

    // bootstrap тултипы (подсказки к полям ввода)
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Добавляем загрузочный экран при отправке запроса
    $(document).ajaxSend(showLoadingScreen);

    // Убираем загрузочный экран при завершении запроса
    $(document).ajaxComplete(hideLoadingScreen);

    // Обработчик ввода в поле параметров
    $('input[type="text"]').on('input', function () {
        let text = $(this).val().toUpperCase();
        let input = $(this).attr('id');
        let pattern;
        switch (input) {
            case "mask":
                pattern = makePattern(patternBase, "[^", "?*]", "i"); // /[^a-zA-Z?*]/i;
                $(this).val(text.replace(pattern, ''));
                break;
            case "start":
            case "end":
            case "contains":
                pattern = makePattern(patternBase, "[^", "?]", "i"); // /[^a-zA-Z?]/i;
                $(this).val(text.replace(pattern, ''));
                break;
            case "include":
            case "exclude":
                // Проверка на повторяющиеся символы
                let char = text.slice(-1);
                if (text.length > 1 && text.indexOf(char) !== text.lastIndexOf(char)) {
                    $(this).val(text.slice(0, -1));
                    return false;
                }
                pattern = makePattern(patternBase, "[^", "]", "i"); // /[^a-zA-Z]/i;
                $(this).val(text.replace(pattern, ''));
                break;
            case "word_rhyme":
            case "word_anagram":
                pattern = makePattern(patternBase, "[^", "]", "i"); // /[^a-zA-Z]/i;
                $(this).val(text.replace(pattern, ''));
                break;
            default:
                break;
        }
    });
});

function showLoadingScreen() {
    loading.show();
    loadingMessage.text("Выполнение операции...");
    $('body').css('overflow', 'hidden'); // Запрещаем скроллинг страницы
    $('<div class="overlay"></div>').appendTo('body'); // Добавляем затемнение
}

function hideLoadingScreen() {
    loading.hide();
    $('body').css('overflow', 'auto'); // Разрешаем скроллинг страницы
    $('.overlay').remove(); // Убираем затемнение
}

// Проверка введённых в поле данных на корректность.
// pattern - шаблон, которому должны соответствовать данные
function validateField(field, data, pattern) {
    if (pattern.test(data)) return true;
    // Если данные не соответствуют шаблону
    alert("Проверьте правильность ввода поля: " + field);
    return false;
}

// Окончание слова в зависимости от количества
function getNoun(number, one, two, five) {
    let n = Math.abs(number);
    n %= 100;
    if (n >= 5 && n <= 20) return five;
    n %= 10;
    if (n === 1) return one;
    if (n >= 2 && n <= 4) return two;
    return five;
}

// Полный шаблон regexp с флагами
function makePattern(base, prefix, postfix, flags) {
    let pattern = prefix + base + postfix;
    return RegExp(pattern, flags);
}