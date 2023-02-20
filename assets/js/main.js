$(document).ready(function () {
    // Обработчик отправки данных формы на сервер
    $("form").submit(function (event) {
        event.preventDefault(); // Отменяем стандартное поведение формы

        // Получаем введенную маску слова
        let mode = $("input[name='mode']:checked").val();
        let data = [];
        data['mode'] = mode;
        if (mode === 'normal') {
            let mask = $("#mask").val();
            if (mask === '') {
                alert("Введите маску слова!");
                return;
            }
            data = [mask];
        } else if (mode === 'extended') {
            let length = $("#length").val(),
                start = $("#start").val(),
                end = $("#end").val(),
                contains = $("#contains").val(),
                exclude = $("#exclude").val();
            if (start === '' && end === '' && contains === '') {
                alert("Заполните хотя бы одно из основных полей: начало слова, конец слова или обязательное буквосочетание.");
                return;
            }
            data = [
                length,
                start,
                end,
                contains,
                exclude
            ];
        }

        // Отправляем AJAX-запрос на сервер
        $.post("search.php", {mode: mode, data: JSON.stringify(data)}, function (response) {
            // Выводим результаты запроса
            $("#search-results").html(response);
        });
    });

    // Обработчик ввода в поле маски
    $('input[type="text"]').on('input', function() {
        let text = $(this).val();
        let input = $(this).attr('id');
        switch (input) {
            case 'mask':
                $(this).val(text.replace(/[^а-я?*]/gi, ''));
                break;
            case 'contains':
                $(this).val(text.replace(/[^а-я?]/gi, ''));
                break;
            default:
                $(this).val(text.replace(/[^а-я]/gi, ''));
                break;
        }
    });

    checkForMode(document.getElementById("mode-normal"));
});

function checkForMode(obj) {
    let mode = obj.value;
    if (mode === 'normal') {
        // Включаем поле для ввода маски, блокируем остальные поля
        $('#mask').prop('disabled', false);
        $('#length').prop('disabled', true);
        $('#start').prop('disabled', true);
        $('#end').prop('disabled', true);
        $('#contains').prop('disabled', true);
        $('#exclude').prop('disabled', true);
    } else if (mode === 'extended') {
        // Включаем все поля, кроме поля для ввода маски
        $('#mask').prop('disabled', true);
        $('#length').prop('disabled', false);
        $('#start').prop('disabled', false);
        $('#end').prop('disabled', false);
        $('#contains').prop('disabled', false);
        $('#exclude').prop('disabled', false);
    }
}