<?php
require_once 'config.php';
require_once 'dbFunctions.php';
require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Создаем логгер
$log = new Logger('mylogger');
$log->pushHandler(new StreamHandler('log.txt', Logger::WARNING));

// Файл login.php (продолжение)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF-токена
    session_start();
    if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
        die('Invalid CSRF token');
    }

    // Получение данных из формы входа
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Подключение к базе данных
    $db = connectDbPdo();
    // Проверка правильности пароля
    $stmt = $db->prepare('SELECT * FROM users WHERE LOGIN = :login');
    $stmt->bindParam(':login', $login);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Записываем сообщение об ошибке в лог
        $log->error('Неверные данные при попытке авторизации', array('login' => $login));
        die('Неверные данные');
    }

    // Проверка чекбокса "Запомнить меня"
    if (isset($_POST['remember_me'])) {
        // Создание и сохранение токена в cookie
        $token = 'MEGASECRET';
        setcookie('remember_token', $token, time() + 3600); // Например, на 1 час
    }

    // Проверка правильности пароля
    $hashedPassword = $user['PASSWORD'];
    $salt = 'MEGASECRET';
    $hashedPasswordWithSalt = md5($password . $salt);
    if ($hashedPassword !== $hashedPasswordWithSalt) {
        // Записываем сообщение об ошибке в лог
        $log->error('Неверный пароль при попытке авторизации', array('login' => $login));
        die('Invalid password');
    }

    $_SESSION['user'] = $login;
    $_SESSION['role'] = $user['ROLE_ID'];

    header("Location: index.php");
    exit();
} else {
    // Генерация CSRF-токена
    session_start();
    $csrfToken = hash('gost-crypto', random_int(0, 999999));
    $_SESSION['CSRF'] = $csrfToken;
}

if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Вход</title>
    <? require_once '_bootstrap.php'; ?>
</head>
<body class="text-center">
<h1>Вход</h1>
<form method="POST" class="text-left" action="login.php">
    <label for="login">Логин:</label>
    <input type="text" name="login" required><br>

    <label for="password">Пароль:</label>
    <input type="password" name="password" required><br>

    <label for="remember_me">Запомнить меня:</label>
    <input type="checkbox" name="remember_me"><br>

    <input type="hidden" name="CSRF" value="<?php echo $csrfToken; ?>">

    <input class="btn btn-info" type="submit" value="Войти">
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
    'client_secret' => $clientSecret,
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
