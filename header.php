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
        <header class="navbar navbar-expand-lg navbar-dark bg-dark mb-3 fixed-top">
            <nav class="container-fluid container-lg">
                <a class="navbar-brand" href="/">
                    <img src="/assets/img/favicon.ico" width="48" height="48" alt="Поиск слов">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
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
                    </ul>
                </div>
            </nav>
        </header>