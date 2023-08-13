<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "classes/Database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "classes/User.php";

$db = new Database();
$user = new User($db);
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

    if ($action === 'getAllUsers') {
        $users = $user->getAllUsers();
        echo json_encode($users);
    } else {
        echo json_encode(['error' => 'Invalid action']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
