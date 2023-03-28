let language = "russian";
let languages = null;
let allowedChars = null;

async function loadLanguages() {
    const response = await fetch('assets/js/languages.json');
    languages = await response.json();
}

$(document).ready(function () {
    // bootstrap тултипы (подсказки к полям ввода)
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    // Элемент "загрузочный экран"
    let loading = $("#loading");
    let loadingMessage = $("#loading-message");
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

        loadingMessage.text("Загрузка доступных словарей...");
        findDictionaries(language);
        loading.hide();

        allowedChars = languages[language].allowedChars;
    });

    // Добавляем загрузочный экран при отправке запроса
    $(document).ajaxSend(function () {
        loading.show();
        loadingMessage.text("Выполнение запроса...");
        $('body').css('overflow', 'hidden'); // Запрещаем скроллинг страницы
        $('<div class="overlay"></div>').appendTo('body'); // Добавляем затемнение
    });

    // Убираем загрузочный экран при завершении запроса
    $(document).ajaxComplete(function () {
        loading.hide();
        $('body').css('overflow', 'auto'); // Разрешаем скроллинг страницы
        $('.overlay').remove(); // Убираем затемнение
    });

    // Обработчик отправки данных формы на сервер
    $("#search-form").submit(function (event) {
        event.preventDefault(); // Отменяем стандартное поведение формы

        let dictionaries = $("#select-dictionaries").val();
        if (!(Array.isArray(dictionaries) && dictionaries.length)) {
            alert("Для поиска необходимо выбрать хотя бы один словарь!");
            return;
        }

        // Получаем введённые данные с полей
        let mode = $("input[name='mode']:checked").val();
        if (!(mode === "normal" || mode === "extended")) {
            alert("Выберите режим поиска слов!");
            return;
        }

        let data = validateData(mode);
        if (data == null) return;

        let limit = $("#limit").find(':selected').val();
        let compound_words = $("#compound-words-checkbox").is(':checked');

        // Отправляем AJAX-запрос на сервер
        $.ajax({
            url: 'core/search.php',
            method: 'GET',
            dataType: 'json',
            contentType: false,
            cache: false,
            data: {dictionaries: dictionaries, mode: mode, data: JSON.stringify(data), limit: limit, compound_words: compound_words},
            success: function (response) {
                // Выводим результаты запроса
                if (response.status === false) {
                    alert(response.message);
                } else {
                    console.log(response.query);
                    $("#search-results").html(response.message).attr("data-query", response.query);
                    $("#results-container").removeClass("d-none");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText); // выводим ответ сервера
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    // Обработчик ввода в поле параметров
    $('input[type="text"]').on('input', function () {
        let text = $(this).val().toUpperCase();
        let input = $(this).attr('id');
        switch (input) {
            case "mask":
                $(this).val(text.replace(/[^а-я?*]/gi, ''));
                break;
            case "start":
            case "end":
            case "contains":
                $(this).val(text.replace(/[^а-я?]/gi, ''));
                break;
            case "include":
            case "exclude":
                // Проверка на повторяющиеся символы
                let char = text.slice(-1);
                if (text.length > 1 && text.indexOf(char) !== text.lastIndexOf(char)) {
                    $(this).val(text.slice(0, -1));
                    return false;
                }
                $(this).val(text.replace(/[^а-я]/gi, ''));
                break;
            default:
                break;
        }
    });

    checkForMode(document.getElementById("mode-normal"));

    // Переход между страницами результатов запроса
    $(document).on("click", ".pagination a", function (e) {
        let page = $(this).attr("data-page");
        let query = $("#search-results").attr("data-query");
        let limit = $("#limit").find(':selected').val();

        $.ajax({
            url: 'core/search.php',
            method: 'GET',
            dataType: 'json',
            contentType: false,
            cache: false,
            data: {page: page, query: query, limit: limit},
            success: function (response) {
                // Выводим результаты запроса
                if (response.status === false) {
                    alert(response.message);
                } else {
                    console.log(response.query);
                    $("#search-results").html(response.message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText); // выводим ответ сервера
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });

        e.preventDefault();
    });

    $("#sortSelect").change(function () {
        $("input[type=radio][name=sortRadio]").prop("checked", false);
    });

    // Сортировка результатов запроса
    $("input[type=radio][name=sortRadio]").change(function () {
        let query = $("#search-results").attr("data-query");
        if (!query) {
            alert("Для проведения сортировки сначала сделайте поиск!");
            return;
        }

        // Тип сортировки: по длине или по алфавиту
        let sortType = $("#sortSelect").find(":selected").val();
        // Порядок сортировки: по возрастанию или по убыванию
        let sortOrder = $(this).val();
        let limit = $("#limit").find(':selected').val();

        $.ajax({
            url: 'core/search.php',
            method: 'GET',
            dataType: 'json',
            contentType: false,
            cache: false,
            data: {sort_type: sortType, sort_order: sortOrder, query: query, limit: limit},
            success: function (response) {
                // Выводим результаты запроса
                if (response.status === false) {
                    alert(response.message);
                } else {
                    console.log(response.query);
                    $("#search-results").html(response.message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText); // выводим ответ сервера
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $("#select-language").change(function () {
        loadingMessage.text("Загрузка доступных словарей...");
        loading.show()
        let language = $(this).find(':selected').val();
        findDictionaries(language);
        loading.hide();
    });
});

// Получение списка словарей определённого языка
function findDictionaries(language) {
    // Отправляем AJAX-запрос на сервер
    $.ajax({
        url: 'core/dictionary.php',
        method: 'GET',
        dataType: 'json',
        contentType: false,
        cache: false,
        data: {language: language},
        success: function (response) {
            let select = $("#select-dictionaries");
            select.empty();

            // Заполняем select найденными словарями
            if (response.status === true) {
                let dictionaries = JSON.parse(response.dictionaries);
                // Для каждого словаря создаём элемент option
                dictionaries.forEach(dictionary => {
                    select.append($("<option>", {
                        value: dictionary["id"],
                        text: dictionary["name"] + " (" + dictionary["count"] + " " + getNoun(dictionary["count"], 'слово', 'слова', 'слов') + ")"
                    }));
                });
            } else alert("В системе отсутствуют словари для выбранного языка поиска.");
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText); // выводим ответ сервера
            console.log('Error: ' + textStatus + ' - ' + errorThrown);
        }
    });
}

// Обработчик смены режима поиска
function checkForMode(obj) {
    let mode = obj.value;
    if (mode === 'normal') {
        // Включаем поле для ввода маски, блокируем остальные поля
        $('#mask').prop('disabled', false);
        $('#length').prop('disabled', true);
        $('#start').prop('disabled', true);
        $('#end').prop('disabled', true);
        $('#contains').prop('disabled', true);
        $('#include').prop('disabled', true);
        $('#exclude').prop('disabled', true);
    } else if (mode === 'extended') {
        // Включаем все поля, кроме поля для ввода маски
        $('#mask').prop('disabled', true);
        $('#length').prop('disabled', false);
        $('#start').prop('disabled', false);
        $('#end').prop('disabled', false);
        $('#contains').prop('disabled', false);
        $('#include').prop('disabled', false);
        $('#exclude').prop('disabled', false);
    }
}

// Проверка двух строк на наличие одинаковых символов
function hasDuplicates(str1, str2) {
    const set = new Set(str1);
    for (let i = 0; i < str2.length; i++) {
        if (set.has(str2[i])) {
            return true;
        }
    }
    return false;
}

// Валидация всех полей, введённых пользователем
function validateData(mode) {
    let data = [];
    // TODO: get pattern for input value from json/server based on current language
    if (mode === 'normal') {
        let mask = $("#mask").val();

        let pattern = /[^а-я?*]/gi;
        if (!validateField("Маска слова", mask, pattern)) return;

        data = [mask];
    } else if (mode === 'extended') {
        let length = $("#length").val(), start = $("#start").val(), end = $("#end").val(),
            contains = $("#contains").val(), include = $("#include").val(), exclude = $("#exclude").val();

        if (length < 2 || length > 32) {
            alert("Длина слова указана неверно!");
            return;
        }

        let pattern = /[^а-я?]/gi;
        if (!validateField("Начало слова", start, pattern)) return;
        if (!validateField("Конец слова", end, pattern)) return;
        if (!validateField("Обязательное буквосочетание", contains, pattern)) return;

        pattern = /[^а-я]/gi;
        if (!validateField("Обязательные буквы", include, pattern)) return;
        if (!validateField("Исключённые буквы", exclude, pattern)) return;

        if (hasDuplicates(include, exclude)) {
            alert('Буквы в полях "Обязательные буквы" и "Исключённые буквы" должны различаться!');
            return;
        }
        data = [length, start, end, contains, include, exclude];
    }
    return data;
}

// true - данные поля валидны, иначе false
function validateField(field, data, pattern) {
    if (pattern.test(data)) {
        alert("Проверьте правильность ввода поля: " + field);
        return false;
    }
    return true;
}

// Окончание слова в зависимости от количества
function getNoun(number, one, two, five) {
    let n = Math.abs(number);
    n %= 100;
    if (n >= 5 && n <= 20) {
        return five;
    }
    n %= 10;
    if (n === 1) {
        return one;
    }
    if (n >= 2 && n <= 4) {
        return two;
    }
    return five;
}