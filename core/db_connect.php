<?php
// Создаем подключение к базе данных

$host = "localhost";
$username = "root";
$password = "";
$dbname = "wordfinder";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

try {
    $db = new PDO($dsn, $username, $password);
    $db->exec("set names utf8mb4");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $error) {
    die("Ошибка подключения к базе данных: " . $error->getMessage());
}