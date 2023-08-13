<!DOCTYPE html>
<html>
<head>
    <title>View Files</title>
</head>
<body>
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Database.php";
    $db = new Database();
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/NetCat.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/User.php";
    
    // Создаем объекты классов с передачей подключения к базе данных
    $netCat = new NetCat($db);
    $user = new User($db); 
    
    if (!empty($_SESSION["user_id"])) {
        $userId = $_SESSION["user_id"]; // Получите ID пользователя из сессии   
    } else {
        $userId = 0; // Получите ID пользователя из сессии
    } 
    
    // Check user's permission
    $requiredRole = "-1"; // Замените на ваше требуемое значение роли
    if (!$user->userHasPermission($userId, $requiredRole)) {
        echo "You do not have permission to view files.";
    } else {
        // Fetch and display files from the selected category
        if (isset($_GET['category_id'])) {
            $categoryID = $_GET['category_id'];
            $files = $netCat->getCategoryFiles($categoryID);

            foreach ($files as $file) {
                echo "<li><a href='http://snowbear-beta.j2me.xyz/uploads/{$file['filename']}'>{$file['filename_humanreadable']}</a></li>";
            }
        } else {
            echo "Please select a category.";
        }
    }
    ?>
</body>
</html>
