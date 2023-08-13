<?php
header('Content-type: application/json');

require $_SERVER['DOCUMENT_ROOT'] . "classes/Rcon.php";
require $_SERVER['DOCUMENT_ROOT'] . "libs/ColorizePHPParser/mccolors_helper.php";
require 'config.php';

$host = $rconHost;
$port = $rconPort;
$password = $rconPassword;
$timeout = 3;

$response = array();
$rcon = new Rcon($host, $port, $password, $timeout);

if(!isset($_POST['cmd'])){
  $response['status'] = 'error';
  $response['error'] = 'Empty command';
}
else{
  if ($rcon->connect()){
    $rcon->send_command($_POST['cmd']);
    $response['status'] = 'success';
    $response['command'] = $_POST['cmd'];
    $response['response'] = mccolors($rcon->get_response());
    echo $return_string;
  }
  else{
    $response['status'] = 'error';
    $response['error'] = 'RCON connection error';
  }
}

echo json_encode($response);
?>
