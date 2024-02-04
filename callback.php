<?php
require_once 'vendor/autoload.php';
require_once 'config.php';
$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_SECRET);
$client->setRedirectUri(GOOGLE_CALLBACK);
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();

    $email = $google_account_info->email;
    $nickname = strstr($email, '@', true);

    // Подключение к базе данных
    $mysqli = new mysqli(JSTORE_DBA_HOST, JSTORE_DBA_USER, JSTORE_DBA_PASS, JSTORE_DBA_DBSE); // to be rewritten

    // Проверяем, существует ли уже пользователь с таким email и не заблокирован ли он
    $stmt = $mysqli->prepare("SELECT id, username, blocked, block_reason FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if ($user['blocked']) {
            // Пользователь заблокирован, возвращаем сообщение об ошибке
            $_SESSION['error_message'] = "Account is blocked. Reason: " . $user['block_reason'];
            header('Location: /blocked.php'); // Редирект на страницу с сообщением о блокировке
            exit();
        } else {
            // Пользователь не заблокирован, устанавливаем данные сессии и cookie
            $_SESSION["user_id"] = $user['id'];
            setcookie("username", $user['username'], time() + 3600, "/");

            header('Location: /');
            exit();
        }
    } else {
        // Пользователь не найден, обрабатываем как нового пользователя
        $authenticator = new PHPGangsta_GoogleAuthenticator();
        $totpSecret = $authenticator->createSecret();
        $qrCodeUrl = $authenticator->getQRCodeGoogleUrl(JSTORE_TOTP_ORGANISATION_KEY, $email, $totpSecret);
        $randomPassword = bin2hex(random_bytes(6));

        $_SESSION['temp_data'] = [
            'success' => true,
            'message' => "Successfully authenticated via Google <-SSO Gateway-> J2ME.xyz",
            'email' => $email,
            'username' => $nickname,
            'password' => $randomPassword,
            'totpSecret' => $totpSecret,
            'qrCodeUrl' => $qrCodeUrl,
        ];

        // Логика для отображения данных нового пользователя отключена здесь,
        // так как будет показана форма ниже
    }

    $stmt->close();
    $mysqli->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $mysqli = new mysqli(JSTORE_DBA_HOST, JSTORE_DBA_USER, JSTORE_DBA_PASS, JSTORE_DBA_DBSE); // to be rewritten

    $tempData = $_SESSION['temp_data'];
    //unset($_SESSION['temp_data']);

    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $tempData['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $stmt = $mysqli->prepare("INSERT INTO users (email, username, password, user_totp_secret) VALUES (?, ?, ?, ?)");
        $hashedPassword = password_hash($tempData['password'], PASSWORD_DEFAULT);
        $stmt->bind_param("ssss", $tempData['email'], $tempData['username'], $hashedPassword, $tempData['totpSecret']);
        $stmt->execute();
        // Используем $mysqli->insert_id для получения ID последнего добавленного пользователя
        $_SESSION["user_id"] = $mysqli->insert_id;
        setcookie("username", $tempData['username'], time() + 3600, "/");
        header('Location: /');
    } else {
        echo 'Пользователь уже существует.';
    }

    $stmt->close();
    $mysqli->close();
} else if (isset($_SESSION['temp_data'])) {
    $tempData = $_SESSION['temp_data'];
    // Отображение формы с токеном и данными пользователей в стиле Windows 98
    // (Форма и CSS, как указано выше)
    ?>
        <!-- Стилизация в стиле Windows 98 -->
        <link rel="stylesheet" href="https://unpkg.com/98.css">

        <div class="window" style="width: 300px;">
            <div class="title-bar">
                <div class="title-bar-text">Confirm Registration</div>
            </div>
            <div class="window-body">
                <form action="callback.php" method="post" class="field-row" style="display:block">
                    <!-- Отображение email, username и TOTP-секрета в стилизованных полях ввода -->
                    <p class="field-row">Email: <input type="email" name="email"
                            value="<?= htmlspecialchars($tempData['email']); ?>" readonly></p>
                    <p class="field-row">Username: <input type="text" name="username"
                            value="<?= htmlspecialchars($tempData['username']); ?>" readonly></p>
                    <p class="field-row">Password: <input type="text" name="password"
                            value="<?= htmlspecialchars($tempData['password']); ?>" readonly></p>

                    <!-- Отображение QR-кода -->
                    <p><img src="<?= htmlspecialchars($tempData['qrCodeUrl']) ?>" alt="TOTP QR Code"></p>

                    <!-- Вывод TOTP-секрета -->
                    <p>Your TOTP Secret: <code><?= htmlspecialchars($tempData['totpSecret']); ?></code></p>

                    <div class="field-row" style="justify-content: center;">
                        <button type="submit" name="confirm">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    <?php
}
?>