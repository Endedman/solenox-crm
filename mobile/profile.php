<?php
$envDir = '/var/www/html/jstore/';
require_once $envDir . 'config.php';
try {
    require_once JSTORE_MOBILE_DIR . 'classes/UserManager.php';

    $db = new Database();
    $userManager = new UserManager($db);

    // Проверяем, залогинен ли пользователь
    if (!isset($_COOKIE['username']) || !isset($_SESSION['user_id'])) {
        header('Location: /' . JSTORE_WEB_MOBILE_SHORT_DIR . 'login');
        exit();
    }

    // Получаем информацию о пользователе
    $userInfo = $userManager->getUserProfileById($_SESSION['user_id']);
} catch (DatabaseException $e) {
    // handle database error, e.g. show a user-friendly message and log the error
    error_log($e->getMessage());
    die('Database error occurred');
} catch (Exception $e) {
    // handle other errors
    error_log($e->getMessage());
    echo "    <!-- Подключение jQuery UI -->
    <script src='https://code.jquery.com/jquery-1.9.1.min.js'></script>

    <script src='https://code.jquery.com/ui/1.12.1/jquery-ui.js'></script>
    
    <!-- Подключение CSS стилей jQuery UI -->
    <link rel='stylesheet' href='https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css'>
    <script>
    $(function() {
        $('<div>').dialog({
            modal: true,
            title: 'Oh, snap!',
            open: function() {
                $(this).html('" . addslashes($e->getMessage()) . "');
            },
            buttons: {
                Reload: function() {
                    $(this).dialog('close');
                }
            }
        });
    });
</script>";
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Профиль</title>

    <!-- Подключение CSS-файла jQuery Mobile 1.3.2 -->
    <link rel="stylesheet" href="https://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css">

    <!-- Подключение JS-файла jQuery 1.9.1 (необходимо для работы jQuery Mobile) -->
    <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>

    <!-- Подключение JS-файла jQuery Mobile 1.3.2 -->
    <script src="https://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

</head>

<body>
    <div data-role="page" data-theme="<?php if (isset($_COOKIE['theme'])) {
        echo $_COOKIE['theme'];
    } ?>">
        <div data-role="header">
            <h1>Профиль</h1>
        </div>

        <div data-role="main" class="ui-content">
            <div id="profile-content">
                <!-- Контент профиля -->
                <p>Ваш профиль</p>
                <ul data-role="listview" data-inset="true">
                    <li data-role="list-divider">Профиль</li>
                    <li class="ui-bar">
                        <div style="display: flex; justify-content: center; align-items: center; height: 200px">
                            <img src="<?php echo $userInfo['avatar']; ?>" height="100%"
                                style="max-width: 100%; object-fit: contain;" alt="User Avatar">
                        </div>
                    </li>
                    <li>Username:
                        <?= $userInfo['username'] ?>
                    </li>
                    <li>Email:
                        <?= $userInfo['email'] ?>
                    </li>
                    <li>Rank:
                        <?= $userInfo['rank'] ?>
                    </li>
                </ul>
            </div>

            <div id="logout-content" style="display: none;">
                <!-- Контент выхода -->
                <a href="/<?= JSTORE_WEB_MOBILE_SHORT_DIR ?>logout" data-inline="true">> Continue to logout</a>
            </div>

            <div id="iu-content" style="display: none;">
                <!-- Контент IU -->
                <a href="#uploadDialog" data-rel="dialog">> Continue to upload</a>
            </div>
        </div>

        <div data-role="footer">
            <div data-role="navbar">
                <ul>
                    <li><a href="#profile-content">Profile</a></li>
                    <li><a href="#logout-content">Logout</a></li>
                    <li><a href="#iu-content">IU</a></li>
                </ul>
            </div><!-- /navbar -->
            <a href="#dialog" data-rel="dialog" data-role="table" data-corners="false">
                <?= $copy ?>
            </a>
        </div><!-- /footer -->
    </div><!-- /page -->

    <div data-role="dialog" id="dialog">
        <?php require_once JSTORE_MOBILE_DIR . 'addons/sysmon.php'; ?>
    </div>

    <div data-role="dialog" id="uploadDialog">
        <?php require_once JSTORE_MOBILE_DIR . 'addons/fileform.php'; ?>
    </div>

    <script src="/<?= JSTORE_WEB_MOBILE_SHORT_DIR ?>script.js"></script>

</body>

</html>