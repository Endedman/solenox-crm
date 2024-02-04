<?php
$envDir = '/var/www/html/jstore/';
require_once $envDir . 'config.php';
try {
    $defaultLanguage = 'ru_RU';
    $language = isset($_SESSION['language']) ? $_SESSION['language'] : $defaultLanguage;
    $langFile = JSTORE_MOBILE_LANGUAGES_DIR . $language . ".json";

    if (file_exists($langFile)) {
        error_log($_SESSION['language']);
        $lang = json_decode(file_get_contents($langFile), true);
    } else {
        die("Language file not found");
    }

    require_once JSTORE_MOBILE_DIR . 'classes/ListingsManager.php';
    require_once JSTORE_MOBILE_DIR . 'classes/UserManager.php';
    $userId = $_SESSION['user_id'];

    $db = new Database();
    $listingsManager = new ListingsManager();
    $userManager = new UserManager($db);
    $itemId = $_GET['id'];
    $item = $listingsManager->getItemById($itemId);
    $userInfo = $userManager->getUserProfileById($_SESSION['user_id']);
    $screenshots = $listingsManager->getScreenshotsByItemId($itemId);
} catch (DatabaseException $e) {
    // handle database error
    error_log($e->getMessage());
    die('Database error occurred');
} catch (Exception $e) {
    // handle other errors
    error_log($e->getMessage());
    echo "    <script>
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
    <title>Mobile page</title>
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
            <a href="#" data-role="button" data-rel="back" data-icon="arrow-l" data-transition="slide">Back</a>
            <a href="#mypanel" data-role="button" data-inline="true" data-icon="bars" data-iconpos="notext">Menu</a>

            <h1>
                <?php echo $item['title']; ?>
            </h1>
        </div><!-- /header -->

        <div data-role="content">
            <ul data-role="listview" data-inset="true">
                <li data-role="list-divider">
                    <?= $lang['screenshots']; ?>
                </li>
                <li>
                    <?php
                    foreach ($screenshots as $screenshot) {
                        echo '<img src../' . $uploadDir . $screenshot['file_url'] . '" alt="Screenshot" style="width: 100%; height: auto;" />';
                    }
                    ?>
                </li>
                <li data-role="list-divider">
                    <?= $lang['description']; ?>
                </li>
                <li class="ui-bar">
                    <?php echo $item['description']; ?>
                </li>

                <li data-role="list-divider">
                    <?= $lang['file_details']; ?>
                </li>
                <li>ID:
                    <?php echo $item['id']; ?>
                </li>
                <li>Name:
                    <?php echo $item['title']; ?>
                </li>
            </ul>

            <button id="wishlist-button" data-role="button" data-inline="true"
                data-item-id="<?php echo $item['id']; ?>">
                <?php echo $listingsManager->isInWishlist($userId, $item['id']) ? 'Удалить из списка желаний' : 'Добавить в список желаний'; ?>
            </button>
        </div>
        <script>
            $(document).ready(function () {
                $("a").removeClass("ui-link");
            });
        </script>



        <div data-role="panel" id="mypanel" data-display="overlay" data-position="right">
            <!-- Menu -->
            <ul data-role="listview">
                <?php if (isset($_COOKIE['username']) && isset($_SESSION['user_id'])): ?>
                    <li><a href="/mobile/profile" data-transition="pop">
                            <?= $lang['profile']; ?> //
                            <?= $userInfo['balance']; ?>
                            <?= $lang['tokens']; ?>
                        </a></li>
                <?php else: ?>
                    <li><a href="/mobile/login" data-transition="pop">
                            <?= $lang['login']; ?>
                        </a></li>
                    <li><a href="/mobile/register" data-transition="pop">
                            <?= $lang['register']; ?>
                        </a></li>
                <?php endif; ?>

                <!-- Кнопки для смены языка -->
                <li><a href="/mobile/top" data-transition="pop">
                        <?= $lang['top']; ?>
                    </a></li>
                <li><a href="/mobile/listings" data-transition="pop">
                        <?= $lang['listings']; ?>
                    </a></li>
                <li><a href="#" class="language" data-lang="en_US">English</a></li>
                <li><a href="#" class="language" data-lang="ru_RU">Русский</a></li>
                <li><a href="#" class="language" data-lang="es_ES">Espanol</a></li>
                <ul data-role="listview">
                    <li><a href="#" class="themeChanger" data-theme="a">Default Theme (a)</a></li>
                    <li><a href="#" class="themeChanger" data-theme="b">Black Theme (b)</a></li>
                </ul>
                <!-- Вы можете добавить больше языков, если понадобится -->
            </ul>

        </div>
        <div data-role="footer">
            <a href="#dialog" data-rel="dialog" data-role="table" data-corners="false">
                <?= JSTORE_BRAND_NAME ?>
            </a>
        </div><!-- /footer -->
    </div><!-- /page -->
    <div data-role="dialog" id="dialog">
        <?php require_once JSTORE_MOBILE_DIR . 'addons/sysmon.php'; ?>
    </div>
    <script src="/<?= JSTORE_WEB_MOBILE_SHORT_DIR ?>script.js"></script>

</body>

</html>