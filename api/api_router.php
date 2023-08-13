<?php
// api/api_router.php

// Define the allowed API actions
$allowedActions = ['getAllUsers', 'getAllNews', 'getAllMessages', 'getToken'];

// Retrieve the requested action from the query string
$action = $_GET['action'] ?? '';

if (in_array($action, $allowedActions)) {
    require_once '../classes/Database.php';
    require_once '../classes/User.php';
    require_once "$action.php"; // Load the appropriate API handler
} else {
    http_response_code(400); // Bad request
    echo json_encode(['error' => 'Invalid action']);
}
?>
