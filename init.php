<?php
require_once "config.php";
require_once JSTORE_DIR . "classes/Database.php";
require_once JSTORE_DIR . "classes/User.php";
require_once JSTORE_DIR . "classes/News.php";
require_once JSTORE_DIR . "classes/Chat.php";
require_once JSTORE_DIR . "classes/Token.php";
require_once JSTORE_DIR . "classes/NetCat.php";
require_once JSTORE_DIR . "classes/Captcha.php";
require 'vendor/autoload.php';
$db = new Database();
$user = new User($db);
$news = new News($db);
$chat = new Chat($db);
$token = new Token();
$netCat = new NetCat($db);
$users = $user->getAllUsers();
$messages = $chat->getMessages();
$posts = $news->getAllNews();
$categories = $netCat->getCategoriesAndFiles();
$stats = $netCat->getStats();