<?php
session_start();
// Отключаем кэширование на клиенте для изображения капчи
header('Expires: Wed, 1 Jan 1997 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // Для совместимости с HTTP/1.0
require_once 'config.php';
require_once JSTORE_DIR . "/classes/Captcha.php";
$captcha = new Captcha();
$captcha->generateCaptcha();
//echo $_SERVER['captcha'];
