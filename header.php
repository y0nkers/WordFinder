<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $title; ?></title>
        <link rel="icon" type="image/png" href="/assets/img/favicon.ico">

        <meta name="description" content="Word Finder - сайт для поиска слов по маске и дополнительным параметрам, рифмы и решения анаграмм.">
        <meta property="og:description" content="Word Finder - сайт для поиска слов по маске и дополнительным параметрам, рифмы и решения анаграмм.">
        <meta property="og:title" content="Word Finder - поиск слов, рифмы, решение анаграмм">
        <meta property="og:site_name" content="Word Finder - поиск слов, рифмы, решение анаграмм">
        <meta property="og:type" content="website">

        <link rel="stylesheet" href="/assets/css/style.css">
        <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
        <?php
        $file = basename($_SERVER['SCRIPT_FILENAME']);
        if ($file == "wordle.php") {
            echo '<link rel="stylesheet" href="/assets/css/wordle.css">';
            echo '<link rel="stylesheet" href="/assets/css/animate.min.css" />';
        }
        if ($file == "index.php" || $file == "panel.php" || $file == "dictionary.php" || $file == "wordle.php") echo '<link rel="stylesheet" href="/assets/css/fontawesome/fontawesome.min.css">';
        ?>
    </head>
    <body class="d-flex flex-column min-vh-100">
        <!-- Форма авторизации -->
        <div class="modal fade" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content col-sm-6 col-lg-4">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="loginModalLabel">Вход в Личный Кабинет</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="loginForm">
                            <div class="row align-items-center mb-3">
                                <div class="form-group col-sm-6">
                                    <label for="login"><b>Логин</b></label>
                                    <input type="text" class="form-control" id="login" name="login" placeholder="Введите логин" required>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="password"><b>Пароль</b></label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Введите пароль" required>
                                </div>
                            </div>
                            <div class="row align-items-center mb-3">
                                <p>Ещё не зарегистрированы? <a id="goRegister" href="javascript:void(0);">Зарегистрируйтесь</a></p>
                                <p>Забыли пароль? Нажмите <a id="goForget1" href="javascript:void(0);">здесь</a></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Закрыть</button>
                                <button type="submit" class="btn btn-success">Войти</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Форма регистрации -->
        <div class="modal fade" id="registerModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content col-sm-6 col-lg-4">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="registerModalLabel">Регистрация</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="registerForm">
                        <div class="modal-body">
                            <div class="row align-items-center mb-3">
                                <div class="form-group col-sm-6">
                                    <label for="login_register"><b>Логин</b></label>
                                    <input type="text" class="form-control" id="login_register" name="login_register" pattern="[a-zA-Z0-9]{4,16}" title="Логин должен содержать от 4 до 16 букв латинского алфавита и цифр" placeholder="Введите логин" required>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="email_register"><b>E-mail</b></label>
                                    <input type="email" class="form-control" id="email_register" name="email_register" placeholder="Введите ваш e-mail" required>
                                </div>
                            </div>
                            <div class="row align-items-center mb-3">
                                <div class="form-group col-sm-6">
                                    <label for="password_register"><b>Пароль</b></label>
                                    <input type="password" class="form-control" id="password_register" name="password_register" pattern="[a-zA-Z0-9]{8,32}" title="Пароль должен содержать не менее 8 символов" placeholder="Придумайте пароль" required>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="password_confirm_register"><b>Повторите пароль</b></label>
                                    <input type="password" class="form-control" id="password_confirm_register" name="password_confirm_register" pattern="[a-zA-Z0-9]{8,32}" title="Пароль должен содержать не менее 8 символов" placeholder="Повторите пароль" required>
                                </div>
                            </div>
                            <div class="row align-items-center mb-3">
                                <p>Уже зарегистрированы? <a id="goLogin" href="javascript:void(0);">Войти</a></p>
                                <p>Забыли пароль? Нажмите <a id="goForget2" href="javascript:void(0);"">здесь</a></p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Закрыть</button>
                            <button type="submit" class="btn btn-success">Зарегистрироваться</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Форма восстановления пароля -->
        <div class="modal fade" id="forgetModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="forgetModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content col-sm-6 col-lg-4">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="forgetModalLabel">Восстановление пароля</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="forgetForm">
                            <div class="row align-items-center mb-3">
                                <div class="form-group">
                                    <label for="email_forget"><b>E-mail для восстановления пароля</b></label>
                                    <input type="email" class="form-control" id="email_forget" name="email_forget" placeholder="Введите e-mail для восстановления пароля" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Закрыть</button>
                                <button type="submit" class="btn btn-success">Отправить ссылку</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Навигационная панель -->
        <header class="navbar navbar-expand-lg navbar-dark bg-dark mb-3 fixed-top">
            <nav class="container-fluid container-lg">
                <a class="navbar-brand" href="/">
                    <img src="/assets/img/favicon.ico" width="48" height="48" alt="Поиск слов">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarContent">
                    <ul class="navbar-nav mb-2 mb-lg-0">
                        <?php
                        if ($file == 'dictionary.php') echo '<li class="nav-item"><a class="nav-link" itemprop="url" href="/admin/panel">Админ-панель</a></li>'
                        ?>
                        <li class="nav-item">
                            <a class="nav-link link-success" itemprop="url" href="/">Поиск слов</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link link-success" itemprop="url" href="/rhyme">Рифмы</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link link-success" itemprop="url" href="/anagram">Анаграммы</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link link-success" itemprop="url" href="/wordle">Wordle</a>
                        </li>
                        <li class="nav-item">
                            <?php if (isset($_SESSION['user'])) : ?>
                                <a class="nav-link link-success" href="/profile.php">Личный кабинет</a>
                            <?php else : ?>
                                <a class="nav-link link-success" id="navbar-login" href="javascript:void(0);">Вход</a>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>