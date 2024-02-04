<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use League\OAuth2\Client\Provider\Github;
use PHPGangsta_GoogleAuthenticator;

$provider = new Github([
    'clientId' => GITHUB_CLIENT_ID,
    'clientSecret' => GITHUB_SECRET,
    'redirectUri' => GITHUB_CALLBACK,
]);

if (isset($_GET['code'])) {
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Получение данных пользователя от GitHub
    $user = $provider->getResourceOwner($token);
    $email = $user->getEmail(); // Получаем из GitHub объекта пользователя

    // Подключение к базе данных
    $mysqli = new mysqli(JSTORE_DBA_HOST, JSTORE_DBA_USER, JSTORE_DBA_PASS, JSTORE_DBA_DBSE); // to be rewritten

    // Генерация случайного пароля и TOTP-секрета
    $authenticator = new PHPGangsta_GoogleAuthenticator();
    $randomPassword = bin2hex(random_bytes(6)); // Для простоты будем использовать 12 символов
    $totpSecret = $authenticator->createSecret();
    $qrCodeUrl = $authenticator->getQRCodeGoogleUrl(JSTORE_TOTP_ORGANISATION_KEY, $email, $totpSecret);

    // Проверка существования пользователя
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Пользователь не существует, сохраняем данные для подтверждения регистрации
        $_SESSION['temp_data'] = [
            'success' => true,
            'email' => $user->getEmail(),
            'username' => $user->getNickname(),
            'password' => $randomPassword,
            'totpSecret' => $totpSecret,
            'qrCodeUrl' => $qrCodeUrl,
            'message' => 'Success'
        ];

    } else {
        // Пользователь существует
        $userData = $result->fetch_assoc();
        $_SESSION["user_id"] = $userData["id"];
        setcookie("username", $userData['username'], time() + 3600, "/");
        header('Location: /');
        exit();
    }
}

// Обработка подтверждения регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    // Регистрация и добавление в базу данных
    if (isset($_SESSION['temp_data'])) {
        $tempData = $_SESSION['temp_data'];
        $hashedPassword = password_hash($tempData['password'], PASSWORD_DEFAULT);
        $mysqli = new mysqli(JSTORE_DBA_HOST, JSTORE_DBA_USER, JSTORE_DBA_PASS, JSTORE_DBA_DBSE); // to be rewritten

        $stmt = $mysqli->prepare("INSERT INTO users (email, username, password, user_totp_secret) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $tempData['email'], $tempData['username'], $hashedPassword, $tempData['totpSecret']);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION["user_id"] = $mysqli->insert_id;
            setcookie("username", $userData['username'], time() + 3600, "/");

            unset($_SESSION['temp_data']);
            header('Location: /');
        } else {
            echo "Не удалось зарегистрировать пользователя.";
        }
        $stmt->close();
    }
    $mysqli->close();
    exit();
}

if (isset($_SESSION['temp_data'])): ?>
    <?php $tempData = $_SESSION['temp_data']; ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Confirm Registration</title>
        <!-- Подключение CSS для стиля Windows 98 -->
        <link rel="stylesheet" href="https://unpkg.com/98.css">
        <style>
            .window {
                max-width: 600px;
                margin: 0 auto;
            }

            .window-body {
                padding: 20px;
            }

            .form-row {
                margin-bottom: 10px;
            }

            input[type="text"],
            input[type="email"] {
                width: 100%;
            }
        </style>
    </head>

    <body>
        <div class="window">
            <div class="title-bar">
                <div class="title-bar-text">Confirm Registration</div>
            </div>
            <div class="window-body">
                <p>Please confirm your registration details:</p>
                <form action="github_callback.php" method="post">
                    <div class="form-row">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($tempData['email']); ?>"
                            readonly>
                    </div>
                    <div class="form-row">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username"
                            value="<?= htmlspecialchars($tempData['username']); ?>" readonly>
                    </div>
                    <div class="form-row">
                        <label for="password">Password:</label>
                        <input type="text" id="password" name="password"
                            value="<?= htmlspecialchars($tempData['password']); ?>" readonly>
                    </div>
                    <div class="form-row">
                        <label for="totpSecret">TOTP Secret:</label>
                        <input type="text" id="totpSecret" name="totpSecret"
                            value="<?= htmlspecialchars($tempData['totpSecret']); ?>" readonly>
                    </div>
                    <div class="form-row">
                        <img src="<?= htmlspecialchars($tempData['qrCodeUrl']) ?>" alt="TOTP QR Code">
                    </div>
                    <div class="form-row">
                        <button type="submit" name="confirm">Confirm Registration</button>
                    </div>
                </form>
            </div>
        </div>
    </body>

    </html>
<?php endif; ?>