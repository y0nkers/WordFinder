$(document).ready(function () {
    // Обработчик отправки данных формы на сервер
    $("#rhyme-form").submit(function (event) {
        event.preventDefault(); // Отменяем стандартное поведение формы

        let language = $("#select-language").val();
        let word = $("#word_rhyme").val();

        // Отправляем AJAX-запрос на сервер
        $.ajax({
            url: 'core/rhyme.php',
            method: 'GET',
            dataType: 'json',
            contentType: false,
            cache: false,
            data: {language: language, word: word},
            success: function (response) {
                // Выводим результаты запроса
                if (response.status === false) {
                    alert(response.message);
                } else {
                    //console.log(response.query);
                    $("#search-results").html(response.message);
                    $("#results-container").removeClass("d-none");
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
        language = $(this).find(':selected').val();
        patternBase = languages[language].regexp;
        $("#word_rhyme").val("");
    });

});