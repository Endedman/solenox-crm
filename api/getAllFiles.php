<?php
$envDir = '/var/www/html/jstore/';
require_once $envDir . 'config.php';
require_once JSTORE_DIR . "classes/Database.php";
require_once JSTORE_DIR . "classes/User.php";
require_once JSTORE_DIR . "classes/NetCat.php";

$db = new Database();
$user = new User($db);
$netcat = new NetCat($db);
$apiToken = $_SERVER["HTTP_X_API_TOKEN"]; // Retrieve the token from the request headers
if (strpos($_SERVER['SCRIPT_FILENAME'], 'api_router.php') === false) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Access denied']);
    exit();
}
if (!$user->isValidToken($apiToken)) { // Implement this method in your User class
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}
// Example usage: /api/user_query.php?action=getAllUsers
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'getAllFiles') {
        $files = $netcat->getFiles();
        echo json_encode($files);
    } else {
        echo json_encode(['error' => 'Invalid action']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
