<?php

/**
 * Chat - Facilitates real-time messaging within an application.
 *
 * This class is designed to manage chat functionalities, including sending,
 * receiving, and displaying messages in real-time.
 *
 * PHP version 7.4
 *
 * @category   Messaging
 * @package    solenox-crm
 * @subpackage Chat
 * @author     Vasiliy Kravchuk <hellendedman@internet.ru>
 * @license    http://www.opensource.org/licenses/MIT MIT License
 * @link       http://j2me.xyz
 * @since      Class available since RC 1.1.18
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
     * Executes a prepared SQL query.
     *
     * @param string $query The SQL query to execute.
     * @param array $params An array of parameters to bind to the query.
     * @return array|bool An array of fetched data or false on failure.
     */
    private function executeQuery($query, $params = [])
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare($query);

        if ($stmt) {
            if (!empty($params)) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result === false) {
                // Запрос не возвращал результатов (например, INSERT, UPDATE или DELETE)
                return $stmt->affected_rows > 0;
            } else {
                // Запрос возвращал результаты (например, SELECT)
                $data = [];
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
                return $data;
            }
        } else {
            return false;
        }
    }

    /**
     * Retrieve chat messages along with user information.
     *
     * @return array An array containing chat message details along with associated user data.
     */
    public function getMessages()
    {
        $query = "SELECT cm.*, u.username FROM chat_messages cm INNER JOIN users u ON cm.user_id = u.id ORDER BY cm.id DESC";
        return $this->executeQuery($query);
    }

    /**
     * Retrieve chat messages for API use.
     *
     * @return array An array containing chat message details for API consumption.
     */
    public function getMessagesApi()
    {
        return $this->executeQuery("SELECT * FROM chat_messages");
    }
}