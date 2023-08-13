<?php
/**
 * Class Chat
 * Represents a chat system.
 */
class Chat
{
    private $db;

    /**
     * Chat constructor.
     *
     * @param Database $database The database connection instance.
     */
    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Retrieve chat messages along with user information.
     *
     * @return array An array containing chat message details along with associated user data.
     */
    public function getMessages()
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT cm.*, u.username FROM chat_messages cm INNER JOIN users u ON cm.user_id = u.id ORDER BY cm.id DESC");

        if (!$stmt) {
            // Handle query error
            return [];
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

        return $messages;
    }

    /**
     * Retrieve chat messages for API use.
     *
     * @return array An array containing chat message details for API consumption.
     */
    public function getMessagesApi()
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM chat_messages");

        if (!$stmt) {
            // Handle query error
            return [];
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $chatm = [];
        while ($row = $result->fetch_assoc()) {
            $chatm[] = $row;
        }

        return $chatm;
    }
}
?>
