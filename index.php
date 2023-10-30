<?php
require_once 'config.php';
require_once 'dbFunctions.php';
require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Создаем логгер
$log = new Logger('mylogger');
$log->pushHandler(new StreamHandler('log.txt', Logger::WARNING));

// Путь к файлу messages.txt
$messages_file = 'messages.txt';

// Проверяем наличие файла messages.txt
if (!file_exists($messages_file)) {
    // Если файл отсутствует, записываем сообщение об этом в лог
    $log->error('Файл messages.txt не найден');
} elseif (isset($_POST['message'])) {
    // Если файл существует и есть сообщение из формы, записываем сообщение в файл messages.txt
    file_put_contents($messages_file, $_POST['message'] . PHP_EOL, FILE_APPEND);
}

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$login = $_SESSION['user'];

// Подключение к базе данных
$db = connectDbPdo();
// Запрос на получение роли пользователя по логину
$stmt = $db->prepare('SELECT ROLE_ID FROM users WHERE LOGIN = :login');
$stmt->bindParam(':login', $login);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo 'Текст виден всем авторизованным пользователям<br/>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.<br/><br/>';

    if ((int)$_SESSION['ROLE_ID'] === 4) {
        echo '<img src="/img/for-vk-users.jpg"><br/><br/>';
    }

    echo '<form action="" method="post">
        <textarea name="message" rows="4" cols="30" required></textarea><br/><br/>
        <input type="submit" value="Сохранить">
    </form>';
}
?>
