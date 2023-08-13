<?php
session_start();
require_once "classes/Database.php";
$db = new Database();
require_once "classes/User.php";
$user = new User($db);

if (!empty($_SESSION["user_id"])) {
    $userId = $_SESSION["user_id"];
} else {
    $userId = 0;
}
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Получите новый ранг, например, через вызов методов для подсчета файлов, новостей и т.д.
		$messageCount = $user->countUserMessages($userId);
        $newsCount = $user->countUserNews($userId);
        $fileCount = $user->countUserFiles($userId);
        $changesCountD = $user->getUserManualRankChangeDecrease($userId);
        $changesCountI = $user->getUserManualRankChangeIncrease($userId);

        echo $messageCount."\n";
        echo $newsCount."\n";
        echo $fileCount."\n";
        echo $changesCountD."\n";
        echo $changesCountI."\n";
        echo $userId."\n";
        // Calculate the new rank based on the formula
            
		// Вычислите новый ранг с учетом новых требований
        $newRank = ($fileCount * 2) + ($newsCount * 4) + ($messageCount * 1) + ($changesCountI * 2) + ($changesCountD * 5) ;

        echo $newRank;
	    // Вызовите метод для перерасчета ранга
	    $user->updateUserRankViaApi($userId, $newRank);
	        echo "ok";
	    exit();
}

// ... остальной код отображения профиля ...
?>
