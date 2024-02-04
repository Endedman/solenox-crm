<?php
$envDir = '/var/www/html/jstore/';
require_once $envDir . 'config.php';
require_once JSTORE_MOBILE_DIR . 'classes/CaptchaManager.php';  // Подлючаем класс
$captcha = new Captcha();
$captcha->generate();
