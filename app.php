<!DOCTYPE html>
<html>

<head>
    <title>View App</title>
    <link rel="stylesheet" href="/static/css/app-styles.css">
</head>

<body>
    <div class="container">
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

        if (isset($_GET['id'])) {
            $appId = $_GET['id'];
            $app = $netCat->getAppById($appId);

            if ($app) {
                $screenshots = $netCat->getScreenshotsByAppId($appId);

                ?>
                <div class="table">
                    <div class="row">
                        <div class="cell"><img src="<?php echo $app['icon_url']; ?>" alt="App Icon"></div>
                        <div class="cell">
                            <p><strong>
                                    <?php echo $app['filename_humanreadable']; ?>
                            </strong>
                            </p>
                            <p><strong>Author:</strong>
                                <?php echo $app['author']; ?>
                            </p>
                            <?php $fileSizeFormatted = $netCat->formatBytes($app['file_size']);
                            echo "<p><strong>Size:</strong> {$fileSizeFormatted}</p>"; ?>
                            <p><strong>Description</strong>
                                <?php echo $app['description']; ?>
                            </p>
                            <?php

                            $isAudio = in_array(strtolower(pathinfo($app['filename'], PATHINFO_EXTENSION)), ['mp3', 'flac', 'wav', 'm4a']);
                            $downloadLink = JSTORE_WEB_UPLOAD_DIR . "{$app['filename']}";

                            if ($isAudio) {
                                echo "<p><a href='{$downloadLink}' onclick='playMusic(event)'>Play</a></p><p><a href='{$downloadLink}' download>Download</a></p>";
                            } else {
                                echo "<p><a href='{$downloadLink}' download>Download</a></p>";
                            }

                            function displayFileItem($filename)
                            {
                                $indentationLevel = substr_count($filename, '/');
                                $fileIcon = getFileIcon($filename);
                                echo "<li class='file-item' style='margin-left: " . ($indentationLevel * 20) . "px;'>{$fileIcon} {$filename}</li>";
                            }

                            function getFileIcon($filename)
                            {
                                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                $icons = [
                                    'jpg' => '🖼️',
                                    'png' => '🖼️',
                                    'gif' => '🖼️',
                                    'txt' => '📄',
                                    'pdf' => '📕',
                                    // Добавьте больше иконок для файлов, если это необходимо
                                ];
                                return isset($icons[$ext]) ? $icons[$ext] : '📦'; // Иконка по умолчанию
                            }

                            // Проверка, является ли файл ZIP или RAR архивом
                            $fileExtension = strtolower(pathinfo($app['filename'], PATHINFO_EXTENSION));
                            if ($fileExtension === 'zip' || $fileExtension === 'rar' || $fileExtension === 'jar') {
                                echo "<h2>Содержимое архива:</h2>";
                                echo "<ul class='archive-contents' style='display: block;'>";

                                if ($fileExtension === 'zip' || $fileExtension === 'jar') {
                                    $zip = new ZipArchive();
                                    if ($zip->open(JSTORE_WEB_UPLOAD_SHORT_DIR . "{$app['filename']}") === TRUE) {
                                        for ($i = 0; $i < $zip->numFiles; $i++) {
                                            $stat = $zip->statIndex($i);
                                            $filename = $stat['name'];
                                            displayFileItem($filename);
                                        }
                                        $zip->close();
                                    } else {
                                        echo "<li>Не удалось открыть ZIP файл.</li>";
                                    }
                                } elseif ($fileExtension === 'rar') {
                                    $rar = RarArchive::open(JSTORE_WEB_UPLOAD_SHORT_DIR . "{$app['filename']}");
                                    if ($rar !== FALSE) {
                                        $entries = $rar->getEntries();
                                        foreach ($entries as $entry) {
                                            $filename = $entry->getName();
                                            displayFileItem($filename);
                                        }
                                        $rar->close();
                                    } else {
                                        echo "<li>Не удалось открыть RAR файл.</li>";
                                    }
                                }

                                echo "</ul>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="table">
                    <?php
                    if (!empty($screenshots)) {
                        echo "<h2>Screenshots</h2>";
                        echo "<div class='table'>";

                        foreach ($screenshots as $screenshot) {
                            echo "<div class='row'>";
                            echo "<div class='cell'><img width=200px src='" . JSTORE_WEB_UPLOAD_SHORT_DIR . "{$screenshot['file_url']}' alt='Screenshot'></div>";
                            echo "</div>";
                        }

                        echo "</div>";
                    }
            } else {
                echo "App not found.";
            }
        }
        ?>
        </div>
</body>

</html>
