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
