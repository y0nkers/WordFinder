<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $title; ?></title>
        <link rel="icon" type="image/png" href="/assets/img/favicon.ico">

        <meta name="description" content="Word Finder - сайт для поиска слов по маске и дополнительным параметрам.">
        <meta property="og:description" content="Word Finder - сайт для поиска слов по маске и дополнительным параметрам.">
        <meta property="og:title" content="Word Finder - Поиск слов">
        <meta property="og:site_name" content="Word Finder - поиск слов">
        <meta property="og:type" content="website">

        <link rel="stylesheet" href="/assets/css/style.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    </head>
    <body>
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
                        <li class="nav-item">
                            <a class="nav-link" itemprop="url" href="/">Главная</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/">...</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>