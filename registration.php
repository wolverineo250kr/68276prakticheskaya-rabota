<?php
require_once 'config.php';
require_once 'dbFunctions.php';
session_start();
// Файл registration.php
// zashchishchennaya-avtentifikatsiya-polzovatelya.loc
// Подключение к базе данных
$db = connectDbPdo();
// Обработка данных из формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Проверка, что логин не занят
    $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE login = :login');
    $stmt->bindParam(':login', $login);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo 'Логин уже занят';
    } else {
        // Хэширование пароля с солью
        $salt = 'MEGASECRET';
        $hashedPassword = md5($password . $salt);

        // Сохранение логина и хэшированного пароля в базе данных
        $stmt = $db->prepare('INSERT INTO users (LOGIN, PASSWORD) VALUES (:login, :password)');
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();

        echo 'Регистрация успешна';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Регистрация</title>
    <? require_once '_bootstrap.php'; ?>
</head>
<body class="text-center">
<h1>Регистрация</h1>
<form class="text-left"  method="POST" action="registration.php">
    <label for="login">Логин:</label>
    <input type="text" name="login" required><br>

    <label for="password">Пароль:</label>
    <input type="password" name="password" required><br>

    <input class="btn btn-info" type="submit" value="Зарегистрироваться">
</form>
<br/>
<?php

// Параметры приложения
$clientId = '1111111'; // ID приложения
$clientSecret = 'mysecret'; // Защищённый ключ
$redirectUri = 'http://zashchishchennaya.loc/vk_callback.php'; // Адрес, на который будет переадресован пользователь после прохождения авторизации

// Формируем ссылку для авторизации
$params = array(
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'v' => '5.126', // (обязательный параметр) версиb API https://vk.com/dev/versions

    // Права доступа приложения https://vk.com/dev/permissions
    // Если указать "offline", полученный access_token будет "вечным" (токен умрёт, если пользователь сменит свой пароль или удалит приложение).
    // Если не указать "offline", то полученный токен будет жить 12 часов.
    'scope' => 'email,photos,offline',
);

?>
<a href="http://oauth.vk.com/authorize?<?php echo http_build_query($params) ?>">Авторизоваться через VK..</a>
</body>
</html>