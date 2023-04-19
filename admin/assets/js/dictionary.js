let id, name;
$(document).ready(function () {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    id = urlParams.get('id');
    name = urlParams.get('name');
    console.log(id);
    console.log(name);

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
});

// Редактирование слова
function updateWord(word) {
    let newWord = prompt("Введите изменённое слово", word);
    if (newWord != null) {
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
                    window.location.reload();
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
                    alert("Слово удалено");
                    window.location.reload();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText); // выводим ответ сервера
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }
}
