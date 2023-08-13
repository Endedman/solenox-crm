<!DOCTYPE html>
<html>
<head>
    <title>View Categories</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    
    $requiredRole = -1; // Замените на необходимую роль

    // Check user's permission
    if (!$user->userHasPermission($userId, $requiredRole)) {
        echo "You do not have permission to view categories.";
    } else {
        // Fetch and display categories from the database
        $categories = $netCat->getCategories();
        foreach ($categories as $category) {
            echo "<li><a href='#' class='category-link' data-category-id='{$category["id"]}'>{$category['name']}</a></li>";
        }
    }
    ?>

    <script>
        $(document).ready(function () {
            $('.category-link').click(function () {
                var categoryId = $(this).data('category-id');
                $.ajax({
                    url: 'content.php',
                    method: 'GET',
                    data: { category_id: categoryId },
                    success: function (data) {
                        $('#category-content').html(data);
                    }
                });
            });
        });
    </script>
</body>
</html>
