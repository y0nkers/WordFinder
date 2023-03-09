<?php
session_start();
if ($_SESSION['login'] != 'admin' || $_SESSION['password'] != '4bd68659613c4f414ce81071566f10c1') {
    $_SESSION['error'] = "Неправильный логин или пароль";
    header('Location: login.php');
}
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    // session started more than 30 minutes ago
    session_regenerate_id(true); // change session ID for the current session and invalidate old session ID
    unset($_SESSION['created']);
    unset($_SESSION['login']);
    unset($_SESSION['password']);
    header('Location: login.php');
}

require_once "core/connect.php";
/** @var PDO $connect */
function print_dictionaries($data): void
{
    $table_html = <<<TABLE
    <div class="container mt-5"> 
    <div class="row justify-content-center">
    <div class="col-md-6">
    <table class="table table-striped">
    <thead>
    <tr>
    <th>ID словаря</th>
    <th>Название</th>
    <th>Язык</th>
    <th>Количество слов</th>
    </tr>
    </thead>
    TABLE;

    while ($row = $data->fetch()) {
        $table_html .= "<tr>";
        $table_html .= "<td>" . $row["id"] . "</td>";
        $table_html .= "<td>" . $row["name"] . "</td>";
        $table_html .= "<td>" . $row["language"] . "</td>";
        $table_html .= "<td>" . $row["count"] . "</td>";
        $table_html .= "</tr>";
    }

    $table_html .= "</table></div></div></div>";
    echo $table_html;
}

?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <title>Админ панель [Главная]</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    </head>
    <body>
        <!-- Кнопки вызова диалоговых окон -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDictionaryModal">
            Добавить словарь
        </button>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deleteDictionaryModal">
            Удалить словарь
        </button>

        <!-- Диалоговые окна -->
        <div class="modal fade" id="addDictionaryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addDictionaryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="addDictionaryModalLabel">Добавить словарь</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addDictionaryForm">
                            <div class="form-group mb-3">
                                <label for="name">Название словаря:</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Будет отображаться у пользователя при выборе" maxlength="32" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="language">Язык словаря:</label>
                                <input type="text" class="form-control" id="language" name="language" placeholder="Позволяет использовать несколько словарей одного языка" maxlength="32" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="words">Загрузите файл со словами</label>
                                <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Текстовый файл должен содержать на каждой строке только одно слово длиной не более 32 букв (остальные буквы будут обрезаны)."><i class="fas fa-question-circle"></i></span>
                                <input type="file" name="words" id="words" accept="text/plain" onchange="validateFileType(this)" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отменить</button>
                                <button type="submit" class="btn btn-primary">Добавить</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteDictionaryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteDictionaryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="deleteDictionaryModalLabel">Удалить словарь</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="deleteDictionaryForm">
                            <div class="form-group mb-3">
                                <label for="id">ID словаря:</label>
                                <input type="number" class="form-control" id="id" name="id" placeholder="Введите ID словаря для удаления" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                <button type="submit" class="btn btn-primary">Удалить</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $query = "SELECT * FROM `dictionaries`";
        $data = $connect->query($query);
        print_dictionaries($data);
        ?>

        <!-- Bootstrap and jQuery scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
        <script type="text/javascript" src="assets/js/main.js"></script>

    </body>
</html>
