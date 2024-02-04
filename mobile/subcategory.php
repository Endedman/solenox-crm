<?php
$envDir = '/var/www/html/jstore/';
require_once $envDir . 'config.php';
try {
    $defaultLanguage = 'ru_RU';
    $language = isset($_SESSION['language']) ? $_SESSION['language'] : $defaultLanguage;
    $langFile = JSTORE_MOBILE_LANGUAGES_DIR . $language . ".json";
    if (file_exists($langFile)) {
        $lang = json_decode(file_get_contents($langFile), true);
    } else {
        die("Language file not found");
    }

    require_once JSTORE_MOBILE_DIR . 'classes/AppManager.php';
    $appManager = new AppManager();

    $subcategoryId = $_GET['id']; // Получаем ID подкатегории из URL
    $subcategory = $appManager->getSubcategory($subcategoryId);

    // Получим файлы/приложения, связанные с данной подкатегорией
    $files = $appManager->getFilesBySubcategories($subcategoryId);
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
                <?php echo $subcategory[0]['name']; ?>
            </h1>
        </div><!-- /header -->

        <div data-role="content">
            <h3>
                <?php echo $subcategory[0]['name']; ?> Files:
            </h3>
            <ul data-role="listview">
                <?php foreach ($files as $file): ?>
                    <li>
                        <a href="/<?= JSTORE_WEB_MOBILE_SHORT_DIR ?>app/<?= $file['id']; ?>" data-transition="pop">
                            <?php echo htmlspecialchars($file['filename_humanreadable']); ?>
                            <p>
                                <?php echo htmlspecialchars($file['description']); ?>
                            </p>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <!-- /content -->
        <script>
            $(document).ready(function () {
                $("a").removeClass("ui-link");
            });
        </script>



        <div data-role="panel" id="mypanel" data-display="overlay" data-position="right">
            <!-- Menu -->
            <ul data-role="listview">
                <?php if (isset($_COOKIE['username']) && isset($_SESSION['user_id'])): ?>
                    <li><a href="/<?= JSTORE_WEB_MOBILE_SHORT_DIR ?>profile" data-transition="pop">
                            <?= $lang['profile']; ?>
                        </a></li>
                <?php else: ?>
                    <li><a href="/<?= JSTORE_WEB_MOBILE_SHORT_DIR ?>login" data-transition="pop">
                            <?= $lang['login']; ?>
                        </a></li>
                    <li><a href="/<?= JSTORE_WEB_MOBILE_SHORT_DIR ?>register" data-transition="pop">
                            <?= $lang['register']; ?>
                        </a></li>
                <?php endif; ?>

                <!-- Кнопки для смены языка -->
                <li><a href="/<?= JSTORE_WEB_MOBILE_SHORT_DIR ?>top" data-transition="pop">
                        <?= $lang['top']; ?>
                    </a></li>
                <li><a href="<?= JSTORE_WEB_MOBILE_SHORT_DIR ?>/listings" data-transition="pop">
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
            <h4>
                <?= JSTORE_BRAND_NAME ?>
            </h4>
        </div><!-- /footer -->
    </div><!-- /page -->
    <div data-role="dialog" id="dialog">
        <?php require_once JSTORE_MOBILE_DIR . 'addons/sysmon.php'; ?>
    </div>
    <script src="/<?= JSTORE_WEB_MOBILE_SHORT_DIR ?>script.js"></script>

</body>

</html>