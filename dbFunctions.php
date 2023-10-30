<?php
// db_functions.php

// Функция для подключения к базе данных PDO
function connectDbPdo()
{
    // Подключение к базе данных
    $host = DB_HOST;
    $user = DB_USER;
    $password = DB_PASS;
    $database = DB_NAME;

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    }

    return $pdo;
}

?>
