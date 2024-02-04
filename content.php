<!DOCTYPE html>
<html>

<head>
    <title>View Files</title>
    <style>
        .help {
            display: none;
        }

        .help:hover {
            display: block;
        }

        .paginate {
            padding: 5px;
            background: blue;
        }

        .pagination {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <?php
    require_once 'config.php';
    require_once JSTORE_DIR . "/classes/Database.php";
    require_once JSTORE_DIR . "/classes/NetCat.php";
    require_once JSTORE_DIR . "/classes/User.php";
    require_once JSTORE_DIR . "/classes/Logger.php";
    $db = new Database();
    $netCat = new NetCat($db);
    $user = new User($db);
    $logger = new Logger($db);
    $userIp = $_SERVER['REMOTE_ADDR'];
    $userAction = "User loaded applications";
    $logger->logActivity($userIp, $userAction);

    if (!empty($_SESSION["user_id"])) {
        $userId = $_SESSION["user_id"];
    } else {
        $userId = 0;
    }

    $requiredRole = "-1";
    function generateListItem($file)
    {
        $isAudio = in_array(pathinfo($file['filename'], PATHINFO_EXTENSION), ['mp3', 'flac', 'wav', 'm4a']);

        if ($file['file_size'] > 1048576) {
            $sizecur = round(($file['file_size'] / 1024 / 1024), 2) . 'MB';
        } else if ($file['file_size'] > 1024) {
            $sizecur = round(($file['file_size'] / 1024), 2) . 'KB';
        } else {
            $sizecur = ($file['file_size']) . 'B';
        }
        $str = "<li><img src='{$file['icon_url']}' alt='File Icon'>";
        $str .= "<a href='app.php?id={$file['id']}' class='file-link' data-file-id='{$file['id']}'>";

        $str .= "{$file['filename_humanreadable']} | {$sizecur}</a>";

        if ($file['verified'] == '1') {
            $str .= "<div class='help'>It means that this file is verified by admin.</div>";
            $str .= "<img align='right' src='/static/img/png/certificate_seal.png' title='It means that this file is verified by admin.' />";
        }
        if ($file['passkey_blocked'] != NULL) {
            $str .= "<div class='help'>It means that this file is protected by admin. Password: {$file['passkey_blocked']}</div>";
            $str .= "<img align='right' src='/static/img/png/key_win-3.png' title='It means that this file is protected by admin. Password: {$file['passkey_blocked']}' />";
        }
        if ($file['virus_protect'] == '1') {
            $str .= "<div class='help'>It means that this file is secure.</div>";
            $str .= "<img align='right' src='/static/img/png/virus_protect.png' width='32' title='It means that this file is secure.' />";
        }

        $str .= "</li>";
        return $str;
    }

    if (!$user->userHasPermission($userId, $requiredRole)) {
        echo "You do not have permission to view files.";
    } else {
        if (isset($_GET['category_id'])) {
            $categoryID = $_GET['category_id'];
            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            $files = $netCat->getCategoryFiles($categoryID, $page);

            // разбивка файлов на страницы
            $total_files = $netCat->countFilesInCategory($categoryID);
            $perPage = 5;
            $total_pages = ceil($total_files / $perPage);

            if (empty($files)) {
                echo "No files in this category.";
            } else {
                // вывод файлов
                foreach ($files as $file) {
                    echo generateListItem($file);
                }

                echo "<div id='pagination'> <div class='pagination'>";
                if ($page > 1) {
                    echo "<span class='paginate'><a href='#' style='color: #fff;' class='page-link' data-page-number='" . ($page - 1) . "'>Previous</a></span>";
                }
                for ($i = 1; $i <= $total_pages; $i++) {
                    echo "<span class='paginate'><a href='#' style='color: #fff;' class='page-link' data-page-number='{$i}'>{$i}</a></span>";
                }
                if ($page < $total_pages) {
                    echo "<span class='paginate'><a href='#' style='color: #fff;' class='page-link' data-page-number='" . ($page + 1) . "'>Next</a></span>";
                }
                echo "</div></div>";
            }
        } else {
            echo "Please select a category.";
        }
    }
    ?>
</body>

</html>