<?php
require_once 'vendor/autoload.php';
require_once 'classes/Database.php'; // Adjust the path as needed
require_once 'classes/News.php'; // Adjust the path as needed

$db = new Database();
$newsClass = new News($db);

$newsId = $_GET['news_id'] ?? null;
$newsItem = null;
if ($newsId) {
    $newsItem = $newsClass->getNewsById($newsId);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>
        <?= $newsItem ? htmlspecialchars($newsItem['title']) : 'News Article'; ?>
    </title>
    <link rel="stylesheet" href="https://unpkg.com/98.css" />
    <link rel="stylesheet" type="text/css" href="/static/css/style.css">

    <style>
        .window {
            width: calc(100% - 100px);
            /* Увеличение окна */
            max-width: 1024px;
            /* Максимальная ширина окна */
            margin: 48px auto;
            /* Увеличение отступов сверху и снизу */
        }

        .title-bar-text {
            font-size: 1.5rem;
            /* Увеличение размера заголовка */
        }
    </style>
</head>

<body>
    <div style="min-height:100vh">
        <div class="window" style="width: 960px; margin: 0 auto">
            <div class="title-bar">
                <div class="title-bar-text">
                    <?= $newsItem ? htmlspecialchars($newsItem['title']) : 'News Article'; ?>
                </div>
            </div>
            <div class="toolbar">
                <!-- Toolbar content if needed -->
            </div>
            <div class="window-body">
                <?php if ($newsItem): ?>
                    <p class="title">
                        <?= htmlspecialchars($newsItem['title']) ?>
                    </p>
                    <p>
                        <?= $newsItem['text'] ?>
                    </p>
                    <p class="status-bar-field">Written by
                        <?= htmlspecialchars($newsItem['username']) ?> on
                        <?= htmlspecialchars($newsItem['date']) ?>
                    </p>
                <?php else: ?>
                    <p>News article not found.</p>
                <?php endif; ?>
            </div>
            <div class="status-bar">
                <p class="status-bar-field">Last updated:
                    <?= date('Y-m-d H:i:s') ?>
                </p>
            </div>
        </div>
    </div>
    <div class="footer" style="align-items: center;">
        <button style="
    display: flex;
    align-items: center;
    height: 25px;
    width: 40px;
    margin: 0 0 0 2px;
    text-align: left;
    font-weight: bold;
"><img src="/static/img/png/windows-0.png" alt="off" style="width: 16px;" />&nbsp;&nbsp;Start</button>
    </div>
</body>

</html>