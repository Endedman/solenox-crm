<?php
header('Content-type: application/json');
$envDir = '/var/www/html/jstore/';
require_once $envDir . 'config.php';
require JSTORE_DIR . "classes/Rcon.php";
require JSTORE_DIR . "libs/ColorizePHPParser/mccolors_helper.php";
$response = array();
$rcon = new Rcon(MINECRAFT_SRVMANAGER_HOST, MINECRAFT_SRVMANAGER_PORT, MINECRAFT_SRVMANAGER_PASS, 3);

if (!isset($_POST['cmd'])) {
  $response['status'] = 'error';
  $response['error'] = 'Empty command';
} else {
  if ($rcon->connect()) {
    $rcon->send_command($_POST['cmd']);
    $response['status'] = 'success';
    $response['command'] = $_POST['cmd'];
    $response['response'] = mccolors($rcon->get_response());
    echo $return_string;
  } else {
    $response['status'] = 'error';
    $response['error'] = 'RCON connection error';
  }
}

echo json_encode($response);
