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
                    window.location.reload();
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
                    window.location.reload();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText); // выводим ответ сервера
                console.log('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    })

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
        // TODO
        console.log("halo");
    })

    $("#deleteWordsForm").submit(function (event) {
        event.preventDefault();
        // TODO
        let words = $("#deleteWordsInput").val().trim();
        let lines = words.split('\n');
        console.log(lines);
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