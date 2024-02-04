<?php
session_start();
// wishlist.php
require_once $_SERVER["DOCUMENT_ROOT"] . 'config.php';
require_once JSTORE_MOBILE_DIR .'classes/ListingsManager.php';
$listingsManager = new ListingsManager();

if($_POST["action"] == "add") {
    $userId = $_SESSION['user_id'];
    $itemId = $_POST['itemId'];
    $listingsManager->addToWishList($userId, $itemId);
    echo "added";
} elseif ($_POST["action"] == "remove") {
    $userId = $_SESSION['user_id'];
    $itemId = $_POST['itemId'];
    $listingsManager->removeFromWishList($userId, $itemId);
    echo "removed";
}
