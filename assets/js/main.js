$(document).ready(function () {
    // Отправляем данные формы на сервер
    $("form").submit(function (event) {
        event.preventDefault(); // Отменяем стандартное поведение формы

        // Получаем введенную маску слова
        let mode = $("input[name='mode']:checked").val();
        var data = [];
        data['mode'] = mode;
        if (mode === 'normal') {
            data = [$("#mask").val()];
        } else if (mode === 'extended') {
            data = [
                $("#length").val(),
                $("#start").val(),
                $("#end").val()
            ];
        }

        // Отправляем AJAX-запрос на сервер
        $.post("search.php", {mode: mode, data: JSON.stringify(data)}, function (response) {
            // Выводим результаты запроса
            $("#search-results").html(response);
        });
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

        // $('#length').hide();
        // $('#start').hide();
        // $('#end').hide();
    } else if (mode === 'extended') {
        // Включаем все поля, кроме поля для ввода маски
        $('#mask').prop('disabled', true);
        $('#length').prop('disabled', false);
        $('#start').prop('disabled', false);
        $('#end').prop('disabled', false);

        // $('#length').show();
        // $('#start').show();
        // $('#end').show();
    }
}