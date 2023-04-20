let id, name;

async function useLanguages() {
    await loadLanguages();
}

$(document).ready(function () {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    id = urlParams.get('id');
    name = urlParams.get('name');
    language = urlParams.get('language');

    let query = "SELECT * FROM dictionary_" + id;
    // Отправляем AJAX-запрос на сервер
    $.ajax({
        url: '/core/search.php',
        method: 'GET',
        dataType: 'json',
        contentType: false,
        cache: false,
        data: {query: query, admin: true},
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

    // Обработчик загрузки файла в форме добавления слов
    $("#addWordsFile").change(validateFileType);

    // Изменения режима добавления слов в форме
    $("input[type=radio][name=addWordsType]").change(function () {
        let type = $(this).val();
        let textarea = $("#addWordsTextarea"),
            fileInput = $("#addWordsFile"),
            label = $("label[for='addWordsFile']"),
            tooltipInput = $("#addWordsInputTooltip"),
            tooltipTextarea = $("#addWordsTextareaTooltip");
        switch (type) {
            case "addFromFile":
                label.text("Загрузите файл со словами");
                fileInput.removeClass("d-none");
                fileInput.prop('disabled', false);
                tooltipInput.removeClass("d-none");

                textarea.addClass("d-none");
                textarea.prop('disabled', true);
                tooltipTextarea.addClass("d-none");
                break;
            case "addFromText":
                label.text("Введите слова для добавления");
                textarea.removeClass("d-none");
                textarea.prop('disabled', false);
                tooltipTextarea.removeClass("d-none");

                fileInput.addClass("d-none");
                fileInput.prop('disabled', true);
                tooltipInput.addClass("d-none");
                break;
            default:
                break;
        }
    });

    $("#addWordsForm").submit(function (event) {
        event.preventDefault();

        let mode = $("input[type=radio][name=addWordsType]:checked").val();
        let words;
        if (mode === "addFromFile") words = document.getElementById('addWordsFile').files[0];
        else if (mode === "addFromText") words = $("#addWordsTextarea").val().trim().split('\n');

        let data = new FormData();
        data.append('type', "add");
        data.append('id', id);
        data.append('mode', mode);
        data.append('words', words);

        $.ajax({
            url: 'core/words.php',
            type: 'POST',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            data: data,
            success: function (response) {
                // Выводим результаты запроса
                if (response.status === false) {
                    alert(response.message);
                } else {
                    alert("Добавлено новых слов: " + response.count);
                    window.location.reload();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText); // выводим ответ сервера
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    })

    $("#deleteWordsForm").submit(function (event) {
        event.preventDefault();

        let words = $("#deleteWordsInput").val().trim().split('\n');

        let data = new FormData();
        data.append('type', "delete");
        data.append('id', id);
        data.append('words', words);

        $.ajax({
            url: 'core/words.php',
            type: 'POST',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            data: data,
            success: function (response) {
                // Выводим результаты запроса
                if (response.status === false) {
                    alert(response.message);
                } else {
                    alert("Удалено слов: " + response.count);
                    window.location.reload();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText); // выводим ответ сервера
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    })

    // Обработчик отправки данных формы на сервер
    $("#search-form").submit(function (event) {
        event.preventDefault(); // Отменяем стандартное поведение формы

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
            url: '/core/search.php',
            method: 'GET',
            dataType: 'json',
            contentType: false,
            cache: false,
            data: {dictionaries: [id], language: language, mode: mode, data: JSON.stringify(data), limit: limit, compound_words: compound_words, admin: true},
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

    checkForMode(document.getElementById("mode-normal"));

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
            url: '/core/search.php',
            method: 'GET',
            dataType: 'json',
            contentType: false,
            cache: false,
            data: {page: page, query: query, limit: limit, sort_type: sortType, sort_order: sortOrder, admin: true},
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
            url: '/core/search.php',
            method: 'GET',
            dataType: 'json',
            contentType: false,
            cache: false,
            data: {sort_type: sortType, sort_order: sortOrder, query: query, limit: limit, admin: true},
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

    useLanguages().then(_ => {
        patternBase = languages[language].regexp;
    });
});

// Редактирование слова
function updateWord(word) {
    let newWord = prompt("Введите изменённое слово", word);
    if (newWord != null) {
        let row = $(event.target).closest('tr');
        let data = new FormData();
        data.append('type', "update");
        data.append('id', id);
        data.append('oldWord', word);
        data.append('newWord', newWord);

        $.ajax({
            url: 'core/words.php',
            type: 'POST',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            data: data,
            success: function (response) {
                // Выводим результаты запроса
                if (response.status === false) {
                    alert(response.message);
                } else {
                    alert("Слово отредактировано");
                    row.find('td:nth-child(2)').text(newWord);
                    row.find('td:nth-child(3) i').attr('onclick', 'updateWord("' + newWord + '")');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText); // выводим ответ сервера
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }
}

// Удаление слова
function deleteWord(word) {
    if (confirm('Удалить слово "' + word + '"?') === true) {
        let row = $(event.target).closest('tr');
        let data = new FormData();
        data.append('type', "delete");
        data.append('id', id);
        data.append('words', word);

        $.ajax({
            url: 'core/words.php',
            type: 'POST',
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            data: data,
            success: function (response) {
                // Выводим результаты запроса
                if (response.status === false) {
                    alert(response.message);
                } else {
                    row.remove();
                    alert("Слово удалено");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText); // выводим ответ сервера
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }
}

function validateFileType() {
    let fileName = this.value;
    let dotIndex = fileName.lastIndexOf(".") + 1;
    let fileExtension = fileName.substr(dotIndex).toLowerCase();

    if (!(fileExtension === "txt" || fileExtension === "text")) {
        alert("Расширение файла должно быть .txt!");
        this.value = "";
    }
}

// Очистка всех полей формы после смены языка
function clearSearchForm() {
    $("#mask").val("");
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
        $("#start").prop('disabled', true);
        $("#end").prop('disabled', true);

        $("#normal-mode-parameters").show();
        $("#extended-mode-parameters-1").hide();
        $("#extended-mode-parameters-2").hide();
        $("#extended-mode-parameters-3").hide();
    } else if (mode === 'extended') {
        $("#mask").prop('disabled', true);
        $("#start").prop('disabled', false);
        $("#end").prop('disabled', false);

        $("#normal-mode-parameters").hide();
        $("#extended-mode-parameters-1").show();
        $("#extended-mode-parameters-2").show();
        $("#extended-mode-parameters-3").show();
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
    if (mode === 'normal') {
        let mask = $("#mask").val();
        let pattern = makePattern(patternBase, "^[", "?*]+$", "i"); // /^[a-zA-Z?*]+$/i;
        if (!validateField("Маска слова", mask, pattern)) return;

        data = [mask];
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
        data = [length, start, end, contains, include, exclude];
    }
    return data;
}
