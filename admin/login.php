<?php
if ($_SERVER["REQUEST_METHOD"] != "GET") die();

session_start();

if (isset($_SESSION['error'])) {
    echo "<script type='text/javascript'>alert('" . $_SESSION['error'] . "');</script>";
    unset($_SESSION['error']);
}

if (isset($_GET['login']) && isset($_GET['password'])) {
    $login = $_GET['login'];
    $password = md5($_GET['password']);

    $_SESSION['login'] = $login;
    $_SESSION['password'] = $password;
    header('Location: panel');
}
?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Вход в админ панель</title>
        <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
        <link rel="stylesheet" href="/assets/css/style.css">
        <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    </head>
    <body>

        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Вход в админ панель</div>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" id="login-form">
                                <div class="form-group mb-3">
                                    <label for="login">Логин:</label>
                                    <input type="text" class="form-control" id="login" name="login" placeholder="Введите логин" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="password">Пароль:</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Введите пароль" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Найти</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap and jQuery scripts -->
        <script src="/assets/js/bootstrap.bundle.min.js"></script>
        <script src="/assets/js/jquery-3.6.3.min.js"></script>
    </body>
</html>
