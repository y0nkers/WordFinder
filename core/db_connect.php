<?php
// Создаем подключение к базе данных

$server = "localhost";
$username = "root";
$password = "";
$database = "wordfinder";
$db = new mysqli($server, $username, $password, $database);
$db->set_charset('utf8');

// Проверяем соединение с базой данных
if ($db->connect_error) die("Ошибка подключения: " . $db->connect_error);
