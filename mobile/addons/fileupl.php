<?php
require_once $_SERVER["DOCUMENT_ROOT"] . 'config.php';
require_once JSTORE_DIR . 'classes/Database.php';
require_once JSTORE_DIR . 'classes/NetCat.php';
$db = new Database();
$userManager = new NetCat($db);

$file = $_FILES['fileToUpload'];

$result = $userManager->uploadFile(
    $file,
    $_POST['categoryId'],
    $_POST['description'],
    $_POST['qualityMark'],
    $_POST['uniquenessMark'],
    $_POST['interfaceLanguage'],
    $_POST['uploadedBy'],
    $_POST['fileNameHuman']
);

echo json_encode($result);
