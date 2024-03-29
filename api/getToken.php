<?php
$envDir = '/var/www/html/jstore/';
require_once $envDir . 'config.php';
require_once JSTORE_DIR . "classes/Database.php";
require_once JSTORE_DIR . "classes/User.php";
require_once JSTORE_DIR . "classes/NetCat.php";
// Retrieve the submitted username and password
$username = $_GET['username'] ?? '';
$password = $_GET['password'] ?? '';

$db = new Database();
$user = new User($db);

// Check if the provided username and password are valid
$userInfo = $user->getUserByUsernameAndPassword($username, $password);

if ($userInfo) {
    // Username and password are valid, retrieve the token from the user info
    $token = $userInfo['token'];

    // Return the token in the API response
    echo json_encode(['success' => true, 'token' => $token]);
} else {
    // Invalid username or password
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
}
