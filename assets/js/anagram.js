$(document).ready(function () {
    // Обработчик нажатия кнопки формы
    $("#anagram-form").submit(function (event) {
        event.preventDefault(); // Отменяем стандартное поведение формы

        let action = $('input[name=action]:checked').val();
        if (!(action === "solve" || action === "make")) {
            alert("Выберите действие!");
            return;
        }
        handleAction(action);
    });

    // Смена языка поиска
    $("#select-language").change(function () {
        language = $(this).find(':selected').val();
        patternBase = languages[language].regexp;
        $("#word_anagram").val("");
    });

    $("input[type=radio][name=action]").change(function () {
        if (this.value === "solve") $("#anagram-btn").text("Решить");
        else $("#anagram-btn").text("Составить");
    });
});

function handleAction(action) {
    let input = $("#word_anagram").val();
    if (action === "solve") solveAnagram(input);
    else if (action === "make") makeAnagram(input);
}

// Решение анаграммы
function solveAnagram(anagram) {
    let language = $("#select-language").val();

    // Отправляем AJAX-запрос на сервер
    $.ajax({
        url: 'core/anagram.php',
        method: 'GET',
        dataType: 'json',
        contentType: false,
        cache: false,
        data: {language: language, word: anagram},
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
}

// Составление анаграммы
function makeAnagram(word) {
    String.prototype.shuffle = function () {
        let a = this.split(""),
            n = a.length;

        for(let i = n - 1; i > 0; i--) {
            let j = Math.floor(Math.random() * (i + 1));
            let tmp = a[i];
            a[i] = a[j];
            a[j] = tmp;
        }
        return a.join("");
    }

    $("#search-results").html("<div class='container mt-3'><div class='bg-dark text-white p-3 rounded'>" + word.shuffle() + "</div>");
    $("#results-container").removeClass("d-none");
}
