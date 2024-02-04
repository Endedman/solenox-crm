<?php
// post_handlers.php

require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/init.php";
if (isset($_SESSION["user_id"])) {
    $userId = $_SESSION["user_id"]; // Получите ID пользователя из сессии
} else {
    $userId = 0;
}
// Создаем массивы для дат и значений активности каждого действия
$activityData = [];
function handleUpload($netCat, $userId)
{
    $categoryID = $_POST["category"];
    $file = $_FILES["file"];
    $fileNameHuman = $_POST["filenamehuman"];
    $description = $_POST["description"];
    $qualityMark = $_POST["qualitymark"];
    $uniquenessMark = $_POST["uniquenessmark"];
    $interfaceLanguage = $_POST["interfacelanguage"];
    $uploadedBy = $_POST["uploadedby"];

    if (isset($_FILES["file"])) {
        $uploadResult = $netCat->uploadFile($_FILES["file"], $categoryID, $description, $qualityMark, $uniquenessMark, $interfaceLanguage, $uploadedBy, $fileNameHuman);

        if ($uploadResult && $uploadResult['status'] == 'success') {
            $_SESSION['fl_success_message'] = $uploadResult['message'];
            $query = "SELECT LAST_INSERT_ID() as id";
            $result = $netCat->fetchSingle($query);

            if ($result && isset($result['id'])) {
                $lastInsertedId = $result['id'];

                $screenshotFiles = $_FILES['screenshots'] ?? null;
                if ($screenshotFiles) {
                    foreach ($screenshotFiles['name'] as $index => $name) {
                        $file = [
                            'name' => $name,
                            'type' => $screenshotFiles['type'][$index],
                            'tmp_name' => $screenshotFiles['tmp_name'][$index],
                            'error' => $screenshotFiles['error'][$index],
                            'size' => $screenshotFiles['size'][$index],
                        ];

                        $newName = JSTORE_UPLOAD_SCREENSHOT_PREFIX . md5($name) . "." . pathinfo($name, PATHINFO_EXTENSION);
                        $uploadResult = $netCat->uploadScreenshot($file, $newName, $lastInsertedId);

                        // После загрузки скриншота получим ширину и высоту и создадим миниатюру
                        $dimensions = getimagesize(JSTORE_UPLOAD_DIR . $newName);
                        if ($dimensions) {
                            $netCat->createThumbnail($newName, min($dimensions[0], 640), min($dimensions[1], 480), JSTORE_UPLOAD_DIR);
                        }

                        if (!$uploadResult || $uploadResult['status'] !== 'success') {
                            $_SESSION['fl_error_message'] = $uploadResult['message'] . " Failed to upload screenshot.";
                        }
                    }
                }
            } else {
                // Ручная обработка ошибки, если не удалось получить ID
                $_SESSION['fl_error_message'] = "Failed to get last inserted ID.";
            }
        } else {
            $_SESSION['fl_error_message'] = $uploadResult['message'] . ' Malicious Count: ' . $uploadResult['malicious_count'];
        }
    }
}


function handleCreateCategory($netCat, $userId)
{
    $name = $_POST["name"];
    $developer = $_POST["developer"];
    $website = $_POST["website"];
    $license = $_POST["license"];
    $createdBy = $_POST["created_by"];

    $createResult = $netCat->createCategory($name, $developer, $website, $license, $createdBy);
    if ($createResult === true) {
        $_SESSION['ct_success_message'] = "Category created successfully.";
    } else {
        $_SESSION['ct_error_message'] = "Category creation failed: $createResult";
    }
}
function handleCreateRedirect($redirector, $userId)
{
    $name = $_POST["name"];
    $link = $_POST["link"];

    $createResult = $redirector->addRedirectLink($link, $name);
    if ($createResult === true) {
        $_SESSION['redir_success_message'] = "Redirect created successfully.";
    } else {
        $_SESSION['redir_error_message'] = "Redirect creation failed.";
    }
}

function handleAddNews($news, $userId)
{
    $title = $_POST["title"];
    $content = $_POST["content"];
    $author = $_POST["author"];

    $createResult = $news->createNews($title, $content, $author);
    if ($createResult) {
        $_SESSION['nw_success_message'] = "News created successfully.";
    } else {
        $_SESSION['nw_error_message'] = "Failed to create news.";
    }
}


function handleAction($rcon, $userId)
{
    $allowedActions = array("reboot", "reload");

    $action = $_POST["action"];

    if (in_array($action, $allowedActions)) {
        if ($rcon->connect()) {
            $command = "";
            $commands = array(
                "reboot" => "restart",
                "reload" => "reload confirm"
            );

            if (array_key_exists($action, $commands)) {
                $command = $commands[$action];
            }

            if (!empty($command)) {
                if ($rcon->send_command($command)) {
                    $result = $rcon->get_response();
                    $_SESSION['success_message'] = "Команда успешно отправлена: $command";
                } else {
                    $_SESSION['error_message'] = "Ошибка отправки команды: $command";
                }
            } else {
                $_SESSION['error_message'] = "Ошибка: неизвестное действие";
            }
        } else {
            $_SESSION['error_message'] = "Ошибка подключения к RCON серверу";
        }
    } else {
        $_SESSION['error_message'] = "Ошибка: недопустимое действие";
    }
}

function handleMgmtToken($user, $userId)
{
    $totpId = $_POST['changeToken'];
    $regenResult = $user->regenerateUserTOTPbyId($totpId);
    if ($regenResult) {
        $_SESSION['tk_success_message'] = "Regenerated!";
    } else {
        $_SESSION['tk_error_message'] = "Failed to create/modify TOTP. Maybe DBA is stopped/timeout exceeded.";
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["upload"])) {
        handleUpload($netCat, $userId);
    } elseif (isset($_POST["create_category"])) {
        handleCreateCategory($netCat, $userId);
    } elseif (isset($_POST["create_redirect"])) {
        handleCreateRedirect($redirector, $userId);
    } elseif (isset($_POST["add_news"])) {
        handleAddNews($news, $userId);
    } elseif (isset($_POST["regen_token"])) {
        handleMgmtToken($user, $userId);
    } elseif (isset($_POST["action"])) {
        handleAction($rcon, $userId);
    }
}