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
?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <title>Админ панель</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        if you see this you're cool
    </body>
</html>
