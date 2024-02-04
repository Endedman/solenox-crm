<?php
// verify_email.php
require_once 'config.php';
require_once JSTORE_DIR . 'classes/Database.php';
require_once JSTORE_DIR . 'classes/User.php';

// Инициализация соединения с базой данных
$db = new Database();
$user = new User($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $token = $_POST['token'] ?? '';

    // Следует также добавить защиту от XSS-атаки, очищая введенные данные.
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $token = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');

    if ($user->verifyEmail($email, $token)) {
        echo "<p>Ваш Email был успешно подтвержден!</p>";
    } else {
        echo "<p>Не удалось подтвердить Email. Проверьте введенные данные и попробуйте снова.</p>";
    }
} else {
    // Если скрипт не был вызван методом POST, перенаправить пользователя обратно к форме верификации.
    header('Location: index.php');
    exit;
}