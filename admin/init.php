<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/NetCat.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/News.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/User.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Rcon.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Redirector.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Logger.php";
$db = new Database();
$netCat = new NetCat($db);
$user = new User($db);
$news = new News($db);
$redirector = new Redirector($db);
$logger = new Logger($db);
$rcon = new Rcon(MINECRAFT_SRVMANAGER_HOST, MINECRAFT_SRVMANAGER_PORT, MINECRAFT_SRVMANAGER_PASS, 5);
$userList = $user->getAllUsers();
$categories = $netCat->getCategories();
$languages = $netCat->getLanguages();
$activityLog = $logger->getActivityLog();
$logger = new Logger($db);
$userIp = $_SERVER['REMOTE_ADDR'];
$userAction = "User loaded admin-panel";
$logger->logActivity($userIp, $userAction);