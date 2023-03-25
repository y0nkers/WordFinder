<?php
$host = "localhost";
$username = "user";
$password = "";
$dbname = "wordfinder";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

try {
    $connect = new PDO($dsn, $username, $password);
    $connect->exec("set names utf8mb4");
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $error) {
    die("Ошибка подключения к базе данных: " . $error->getMessage());
}