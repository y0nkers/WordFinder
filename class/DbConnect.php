<?php

class DbConnect
{
    private PDO $_connection;

    function __construct(string $username, string $password)
    {
        $host = "localhost";
        $dbname = "wordfinder";
        $charset = "utf8mb4";
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

        try {
            $this->_connection = new PDO($dsn, $username, $password);
            $this->_connection->exec("set names utf8mb4");
            $this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $error) {
            die("Ошибка подключения к базе данных: " . $error->getMessage());
        }
    }

    public function closeConnection(): void
    {
        unset($this->_connection);
    }

    public function getPDO(): PDO
    {
        return $this->_connection;
    }

}