<?php
if ($_SERVER["REQUEST_METHOD"] != "POST") die();
session_start();
require_once "../class/DbConnect.php";

$type = $_POST['type'];

if ($type == "register") {
    $login = $_POST['login'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (!preg_match("/^[a-zA-Z0-9]{4,16}$/", $login)) errorHandler("Проверьте правильность ввода поля: Логин");
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) errorHandler("Проверьте правильность ввода поля: Email");
    if (!preg_match("/^[a-zA-Z0-9]{8,32}$/", $password)) errorHandler("Проверьте правильность ввода поля: Пароль");

    if ($password != $password_confirm) errorHandler("Введённые пароли не совпадают!");

    $dbConnect = new DbConnect("admin", "wordfinder");
    $pdo = $dbConnect->getPDO();

    try {
        $password = md5($password);
        $apiKey = hash('sha256', $login . time());
        $stmt = $pdo->prepare("INSERT INTO `users` (`login`, `email`, `password`, `api_key`) VALUES (:login, :email, :password, :apikey)");
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':apikey', $apiKey);
        $stmt->execute();
    } catch (PDOException $e) {
        errorHandler("Ошибка при регистрации. Возможно, указанный логин уже используется.");
    }

} else if ($type == "login") {
    $login = $_POST['login'];
    $password = $_POST['password'];

    if (!preg_match("/^[a-zA-Z0-9]{4,16}$/", $login)) errorHandler("Проверьте правильность ввода поля: Логин");
    if (!preg_match("/^[a-zA-Z0-9]{8,32}$/", $password)) errorHandler("Проверьте правильность ввода поля: Пароль");

    $dbConnect = new DbConnect("admin", "wordfinder");
    $pdo = $dbConnect->getPDO();

    try {
        $password = md5($password);
        $stmt = $pdo->prepare("SELECT id, login, email, api_key FROM users WHERE login = :login AND password = :password");
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
    } catch (PDOException $e) {
        errorHandler("Ошибка при поиске указанного профиля.");
    }

    if ($stmt->rowCount() <= 0) errorHandler("Неверный логин или пароль!");

    $user = $stmt->fetch();

    $_SESSION["user"] = [
        "id" => (int)$user["id"]
    ];
} else if ($type == "forget") {
    $email = $_POST['email'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) errorHandler("Проверьте правильность ввода поля: Email");

    $dbConnect = new DbConnect("admin", "wordfinder");
    $pdo = $dbConnect->getPDO();

    try {
        $stmt = $pdo->prepare("SELECT email FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    } catch (PDOException $e) {
        errorHandler("Ошибка при поиске указанного профиля.");
    }

    if ($stmt->rowCount() <= 0) errorHandler("Пользователь с указанным email не найден!");

    $user = $stmt->fetch();
    $email = $user["email"];
    $reset_key = md5($email . time());

    $headers = "MIME-Version: 1.0\r\n" .
        "Content-type: text/html; charset=utf-8\r\n" .
        "To: <$email>\r\nFrom: admin@wordfinder.com" .
        "\r\nX-Priority: 1\r\n";

    $message = '<html><head><title>Восстановление пароля</title></head>' .
        'body><p>Для восстановления пароля от аккаунта на Word Finder перейдите по <a href="https://wordfinder/index.php?key=' . $reset_key . '">ссылке</a></p></body>' .
        '</html>';

    try {
        $stmt = $pdo->prepare("UPDATE users SET reset_key = :key WHERE email = :email");
        $stmt->bindParam(':key', $reset_key);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    } catch (PDOException $e) {
        errorHandler("Ошибка при восстановлении пароля.");
    }

    if (!mail($email, "Восстановление пароля на Word Finder", $message, $headers)) errorHandler("Произошла ошибка при отправке письма.");
} else if ($type == "newpassword") {
    $password = $_POST['password'];

    if (!preg_match("/^[a-zA-Z0-9]{8,32}$/", $password)) errorHandler("Проверьте правильность ввода поля: Пароль");

    $dbConnect = new DbConnect("admin", "wordfinder");
    $pdo = $dbConnect->getPDO();

    try {
        $password = md5($password);
        $stmt = $pdo->prepare("UPDATE users SET reset_key = NULL, password = :password WHERE login = :login");
        $stmt->bindParam(':login', $_SESSION['newpasslogin']);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
    } catch (PDOException $e) {
        errorHandler("Ошибка при обновлении пароля.");
    }
}

$response = [
    "status" => true
];
echo json_encode($response);

function errorHandler(string $message): void
{
    $response = [
        "status" => false,
        "message" => $message
    ];

    echo json_encode($response);
    die();
}
