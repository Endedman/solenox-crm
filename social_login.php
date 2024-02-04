<?php
require_once 'config.php';
require_once 'vendor/autoload.php';


$loginProvider = $_GET['partner'] ?? 'google';

// Загрузка конфигурации клиента в зависимости от провайдера
switch ($loginProvider) {
    case 'google':
        $client = new Google_Client();
        $client->setClientId(GOOGLE_CLIENT_ID);
        $client->setClientSecret(GOOGLE_SECRET);
        $client->setRedirectUri(GOOGLE_CALLBACK);
        $client->addScope("email");
        $client->addScope("profile");
        // Перенаправляем пользователя на URL аутентификации Google
        $auth_url = $client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        exit();

    case 'github':
        // GitHub URL аутентификации
        $auth_url = "https://github.com/login/oauth/authorize?client_id=" . GITHUB_CLIENT_ID . "&redirect_uri=" . GITHUB_CALLBACK . "&scope=user:email";
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        exit();

    default:
        echo "Unknown login provider.";
        exit();
}
