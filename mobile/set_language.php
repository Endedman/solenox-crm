<?php
$envDir = '/var/www/html/jstore/';
require_once $envDir . 'config.php';
if (isset($_POST['language'])) {
    $_SESSION['language'] = $_POST['language'];
}
