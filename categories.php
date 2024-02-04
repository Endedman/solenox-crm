<!DOCTYPE html>
<html>

<head>
    <title>View Categories</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php
    require_once 'config.php';
    require_once JSTORE_DIR . "/classes/Database.php";
    require_once JSTORE_DIR . "/classes/NetCat.php";
    require_once JSTORE_DIR . "/classes/User.php";
    require_once JSTORE_DIR . "/classes/Logger.php";
    $db = new Database();
    $netCat = new NetCat($db);
    $user = new User($db);
    $logger = new Logger($db);
    $userIp = $_SERVER['REMOTE_ADDR'];
    $userAction = "User loaded categories";
    $logger->logActivity($userIp, $userAction);
    if (!empty($_SESSION["user_id"])) {
        $userId = $_SESSION["user_id"]; // Получите ID пользователя из сессии   
    } else {
        $userId = 0; // Получите ID пользователя из сессии
    }

    $requiredRole = -1; // Замените на необходимую роль
    
    // Check user's permission
    if (!$user->userHasPermission($userId, $requiredRole)) {
        echo "You do not have permission to view categories.";
    } else {
        // Fetch and display categories from the database
        $categories = $netCat->getCategories();
        foreach ($categories as $category) {
            echo "<li><img src='{$category['icon_url']}' width='32px' /><br><a href='/content.php?category_id={$category["id"]}' class='category-link' data-category-id='{$category["id"]}'>{$category['name']}</a></li>";
        }
    }
    ?>
</body>

</html>