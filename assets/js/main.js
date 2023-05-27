$(document).ready(function () {
    loading.show();
    loadingMessage.text("Загрузка доступных словарей...");
    findDictionaries(language);
    loading.hide();

    let guideModal = new bootstrap.Modal(document.getElementById("guideModal"));

    $("#openGuide").click(function () {
        guideModal.show();
    });

    // Обработчик отправки данных формы на сервер
    $("#search-form").submit(function (event) {
        event.preventDefault(); // Отменяем стандартное поведение формы

        let userid = -1;
        if (this.dataset.id) userid = this.dataset.id;

        let language = $("#select-language").val();

        let dictionaries = $("#select-dictionaries").val();
        if (!(Array.isArray(dictionaries) && dictionaries.length)) {
            alert("Для поиска необходимо выбрать хотя бы один словарь!");
            return;
        }

        // Получаем введённые данные с полей
        let mode = $("input[name='mode']:checked").val();
        if (!(mode === "normal" || mode === "extended" || mode === "regexp")) {
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
            data: {userid: userid, dictionaries: dictionaries, language: language, mode: mode, data: JSON.stringify(data), limit: limit, compound_words: compound_words},
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

    $("#resetForm").click(function () {
        clearSearchForm();
    });

    $("input[name=length]").on('input', function () {
        $(this).val($(this).val().replace(/[^0-9]/i, ''));
    });

    $("input[type=radio][name=mode]").change(function () {
        checkForMode(this);
    });

    // Переход между страницами результатов запроса
    $(document).on("click", ".pagination a", function (e) {
        let page = $(this).attr("data-page");
        let query = $("#search-results").attr("data-query");
        let limit = $("#limit").find(':selected').val();

        // Тип сортировки: по длине или по алфавиту
        let sortType = $("#sortSelect").find(":selected").val();
        // Порядок сортировки: по возрастанию или по убыванию
        let sortOrder = $("input[type=radio][name=sortRadio]:checked").val();

        $.ajax({
            url: 'core/search.php',
            method: 'GET',
            dataType: 'json',
            contentType: false,
            cache: false,
            data: {page: page, query: query, limit: limit, sort_type: sortType, sort_order: sortOrder},
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

    // Смена языка поиска
    $("#select-language").change(function () {
        loading.show();
        language = $(this).find(':selected').val();
        patternBase = languages[language].regexp;
        loadingMessage.text("Загрузка доступных словарей...");
        findDictionaries(language);
        clearSearchForm();
        loading.hide();
    });

    checkForMode(document.getElementById("mode-normal"));

    loadLanguages().then(_ => {
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        if (urlParams.size > 0) parseParams(urlParams);
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

// Очистка всех полей формы после смены языка
function clearSearchForm() {
    $("#mask").val("");
    $("#regexp").val("");
    $("#length").val("");
    $("#start").val("");
    $("#end").val("");
    $("#contains").val("");
    $("#include").val("");
    $("#exclude").val("");
}

// Обработчик смены режима поиска
function checkForMode(obj) {
    let mode = obj.value;
    if (mode === 'normal') {
        $("#mask").prop('disabled', false);
        $("#regexp").prop('disabled', true);
        $("#start").prop('disabled', true);
        $("#end").prop('disabled', true);

        $("#normal-mode-parameters").show();
        $("#regexp-mode-parameters").hide();
        $("#extended-mode-parameters-1").hide();
        $("#extended-mode-parameters-2").hide();
        $("#extended-mode-parameters-3").hide();
    } else if (mode === 'extended') {
        $("#mask").prop('disabled', true);
        $("#regexp").prop('disabled', true);
        $("#start").prop('disabled', false);
        $("#end").prop('disabled', false);

        $("#normal-mode-parameters").hide();
        $("#regexp-mode-parameters").hide();
        $("#extended-mode-parameters-1").show();
        $("#extended-mode-parameters-2").show();
        $("#extended-mode-parameters-3").show();
    } else if (mode === 'regexp') {
        $("#mask").prop('disabled', true);
        $("#regexp").prop('disabled', false);
        $("#start").prop('disabled', true);
        $("#end").prop('disabled', true);

        $("#normal-mode-parameters").hide();
        $("#regexp-mode-parameters").show();
        $("#extended-mode-parameters-1").hide();
        $("#extended-mode-parameters-2").hide();
        $("#extended-mode-parameters-3").hide();
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
    let data = {};
    if (mode === 'normal') {
        let mask = $("#mask").val();
        let pattern = makePattern(patternBase, "^[", "?*]+$", "i"); // /^[a-zA-Z?*]+$/i;
        if (!validateField("Маска слова", mask, pattern)) return;

        data = {"mask": mask};
    } else if (mode === 'extended') {
        let length = $("#length").val(), start = $("#start").val(), end = $("#end").val(),
            contains = $("#contains").val(), include = $("#include").val(), exclude = $("#exclude").val();

        if ((length !== "") && length < 2 || length > 32) {
            alert("Длина слова указана неверно!");
            return;
        }

        let pattern = makePattern(patternBase, "^[", "?]*$", "i"); // /^[a-zA-Z?]*$/i;
        if (!validateField("Начало слова", start, pattern)) return;
        if (!validateField("Конец слова", end, pattern)) return;
        if (!validateField("Обязательное буквосочетание", contains, pattern)) return;

        pattern = makePattern(patternBase, "^[", "]*$", "i"); // /^[a-zA-Z]*$/i;
        if (!validateField("Обязательные буквы", include, pattern)) return;
        if (!validateField("Исключённые буквы", exclude, pattern)) return;

        if (hasDuplicates(include, exclude)) {
            alert('Буквы в полях "Обязательные буквы" и "Исключённые буквы" должны различаться!');
            return;
        }

        if (length) data["length"] = length;
        if (start) data["start"] = start;
        if (end) data["end"] = end;
        if (contains) data["contains"] = contains;
        if (include) data["include"] = include;
        if (exclude) data["exclude"] = exclude;
    } else if (mode === 'regexp') {
        let regexp = $("#regexp").val();

        data = {"regexp": regexp};
    }
    return data;
}

// Вставка в поля ввода параметров из строки запроса
function parseParams(urlParams) {
    // Установка режима поиска
    let mode = "mode-" + urlParams.get("mode");
    $('#' + mode).prop("checked", true);
    checkForMode(document.getElementById(mode));

    // Установка языка поиска
    let language = urlParams.get("language");
    $("#select-language").val(language).change();

    // Искать или нет составные слова
    let compound_words = urlParams.get("compound_words");
    console.log(compound_words);
    if (compound_words !== 1) $("#compound-words-checkbox").prop("checked", false);

    // Вставляем в поля имеющиеся в строке параметры в зависимости от режима
    switch (mode) {
        case "mode-normal":
            $("#mask").val(urlParams.get("mask"));
            break;
        case "mode-extended":
            if (urlParams.has("start")) $("#start").val(urlParams.get("start"));
            if (urlParams.has("end")) $("#end").val(urlParams.get("end"));
            if (urlParams.has("length")) $("#length").val(urlParams.get("length"));
            if (urlParams.has("contains")) $("#contains").val(urlParams.get("contains"));
            if (urlParams.has("include")) $("#include").val(urlParams.get("include"));
            if (urlParams.has("exclude")) $("#exclude").val(urlParams.get("exclude"));
            break;
        case "mode-regexp":
            $("#regexp").val(urlParams.get("regexp"));
            break;
    }
}
