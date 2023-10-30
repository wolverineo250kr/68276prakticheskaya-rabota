<?php
require_once 'config.php';
require_once 'dbFunctions.php';

$params = [
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'code' => $_GET['code'],
    'redirect_uri' => 'http://zashchishchennaya.loc/index.php'
];

$url = 'https://oauth.vk.com/access_token?' . http_build_query($params);
$content = @file_get_contents($url);
if ($content === false) {
    $error = error_get_last();
    throw new Exception('HTTP request failed. Error: ' . $error['message']);
}

$response = json_decode($content);

// Если при получении токена произошла ошибка
if (isset($response->error)) {
    throw new Exception('При получении токена произошла ошибка. Error: ' . $response->error . '. Error description: ' . $response->error_description);
}

//А вот здесь выполняем код, если все прошло хорошо
$token = $response->access_token; // Токен
$expiresIn = $response->expires_in; // Время жизни токена
$userId = $response->user_id; // ID авторизовавшегося пользователя
$userEmail = $response->email;

// Сохраняем токен в сессии
$_SESSION['token'] = $token;

$db = connectDbPdo();
// Проверка правильности пароля
$stmt = $db->prepare('SELECT * FROM users WHERE EMAIL = :email');
$stmt->bindParam(':email', $userEmail);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $_SESSION['LOGIN'] = $user['LOGIN'];
    $_SESSION['ROLE_ID'] = 4;

    header("Location: index.php");
    exit();
} else {
    throw new Exception('Пользователь не найден');
}
?>