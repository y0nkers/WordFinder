$(document).ready(function () {
    // bootstrap тултипы (подсказки к полям ввода)
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    let file = false;
    $('input[name="words"]').change(function (e) {
        file = e.target.files[0];
    });

    $("#addDictionaryForm").submit(function (event) {
        event.preventDefault(); // Отменяем стандартное поведение формы

        let name = $("#name").val();
        let language = $("#language").val();

        let data = new FormData();
        data.append('type', "add");
        data.append('name', name);
        data.append('language', language);
        data.append('words', file);

        $.ajax({
            url: 'core/dictionary.php',
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
                    alert("Словарь добавлен");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText); // выводим ответ сервера
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    })

    $("#deleteDictionaryForm").submit(function (event) {
        event.preventDefault(); // Отменяем стандартное поведение формы

        let id = $("#id").val();

        let data = new FormData();
        data.append('type', "delete");
        data.append('id', id);

        $.ajax({
            url: 'core/dictionary.php',
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
                    alert("Словарь удалён");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText); // выводим ответ сервера
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    })
});

function validateFileType(input) {
    let fileName = input.value;
    let dotIndex = fileName.lastIndexOf(".") + 1;
    let fileExtension = fileName.substr(dotIndex).toLowerCase();

    if (!(fileExtension === "txt" || fileExtension === "text")) {
        alert("Расширение файла должно быть .txt!");
        input.value = "";
    }
}