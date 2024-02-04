<?php
$envDir = '/var/www/html/jstore/';
require_once $envDir . 'config.php';
require_once JSTORE_DIR . "classes/Database.php";
require_once JSTORE_DIR . "classes/User.php";
$db = new Database();
$user = new User($db); // Создайте экземпляр класса User
$userId = $_SESSION['user_id']; // Получите ID пользователя из сессии
$requiredRole = 3;
if (!$user->userHasPermission($userId, $requiredRole)) {
    $result = "You do not have permission to use this feature.";
    echo $result;
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $action = $_POST['action'];
        $userId = $_POST['userId'];
        switch ($action) {
            case 'update':
                $newData = [
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'rank' => $_POST['rank'],
                    'role' => $_POST['role']
                ];
                $result = $user->updateUser($userId, $newData);
                echo $result ? "User updated successfully." : "Failed to update user.";
                break;

            case 'block':
                $reason = $_POST['reason'];
                $result = $user->blockUser($userId, $reason);
                echo $result ? "User blocked successfully." : "Failed to block user.";
                break;

            case 'delete':
                $result = $user->deleteUser($userId);
                echo $result ? "User deleted successfully." : "Failed to delete user.";
                break;

            default:
                echo "Invalid action.";
                break;
        }
    }
}
