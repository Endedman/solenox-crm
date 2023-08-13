<?php
session_start();
require_once "classes/Database.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST["message"];
    $userId = isset($_SESSION["user_id"]) ? intval($_SESSION["user_id"]) : 0;
    $parentMessageId = isset($_POST["parent"]) ? intval($_POST["parent"]) : null;

    $message = htmlspecialchars($message);

    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message, parent_message_id) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $userId, $message, $parentMessageId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Запрос на получение новых сообщений
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT cm.id, cm.message, u.username FROM chat_messages cm JOIN users u ON cm.user_id = u.id ORDER BY cm.timestamp DESC");
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode($messages);
}
?>
