$(document).ready(function () {
    // Обработчик отправки данных формы на сервер
    $("#search-form").submit(function (event) {
        event.preventDefault(); // Отменяем стандартное поведение формы

        // Получаем введённые данные с полей
        let mode = $("input[name='mode']:checked").val();
        let data = [];
        data['mode'] = mode;
        if (mode === 'normal') {
            let mask = $("#mask").val();
            data = [mask];
        } else if (mode === 'extended') {
            let length = $("#length").val(), start = $("#start").val(), end = $("#end").val(),
                contains = $("#contains").val(), include = $("#include").val(), exclude = $("#exclude").val();
            data = [length, start, end, contains, include, exclude];
            if (hasDuplicates(include, exclude)) {
                alert('Буквы в полях "Обязательные буквы" и "Исключённые буквы" должны различаться!');
                return;
            }
        }

        // Отправляем AJAX-запрос на сервер
        $.ajax({
            url: 'core/search.php',
            method: 'GET',
            dataType: 'json',
            contentType: false,
            cache: false,
            data: {mode: mode, data: JSON.stringify(data)},
            success: function (response) {
                // Выводим результаты запроса
                if (response.status === false) {
                    alert(response.message);
                } else {
                    console.log(response.query);
                    $("#search-results").html(response.message).attr("data-query", response.query);
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

    $(document).on("click", ".pagination a", function (e) {
        let page = $(this).attr("data-page");
        let query = $("#search-results").attr("data-query");

        $.ajax({
            url: 'core/search.php',
            method: 'GET',
            dataType: 'json',
            contentType: false,
            cache: false,
            data: {page: page, query: query},
            success: function (response) {
                // Выводим результаты запроса
                if (response.status === false) {
                    alert(response.message);
                } else {
                    console.log(response.query);
                    $("#search-results").html(response.message).attr("data-query", response.query);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText); // выводим ответ сервера
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });

        e.preventDefault();
    });
});

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
