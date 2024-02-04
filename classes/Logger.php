<?php

require_once $_SERVER["DOCUMENT_ROOT"] . 'config.php';

/**
 * Logger - A class dedicated to managing application logs.
 *
 * This logging system provides capabilities for recording different levels of messages,
 * including debug, information, warnings, errors, and critical issues. It supports multiple
 * output formats and destinations, enabling effective monitoring and troubleshooting.
 * 
 * PHP version 7.4 or higher
 *
 * @category   Utilities
 * @package    solenox-crm
 * @subpackage Logger
 * @author     Vasiliy Kravchuk <hellendedman@internet.ru>
 * @license    http://www.opensource.org/licenses/MIT MIT License
 * @link       http://j2me.xyz
 * @since      Class available since RC 1.1.18
 */
class Logger
{
    private $db;

    /**
     * Logger constructor.
     *
     * @param Database $db The database connection instance.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Logs an action into the database.
     *
     * @param string $ip The IP address of the user.
     * @param string $action The action performed by the user.
     * @return bool Returns `true` if the action was successfully logged, `false` otherwise.
     */
    public function logActivity($ip, $action)
    {
        $query = "INSERT INTO user_activity (ip, action, timestamp) VALUES (?, ?, NOW())";
        $params = [$ip, $action];
        return $this->executeQuery($query, $params);
    }

    /**
     * Retrieves all user activity logs from the 'user_activity' table.
     *
     * This method executes a SELECT query on the 'user_activity' table. It fetches all records
     * and returns them as an array of associative arrays. Each associative array corresponds to a
     * row from the table with key-value pairs mapping column names to their respective values.
     *
     * If the query fails to execute, the method terminates the script and outputs an error message.
     *
     * @return array An array containing all user activity log records.
     */
    public function getActivityLog()
    {
        $query = "SELECT * FROM user_activity ORDER BY id DESC LIMIT 15000";
        $result = $this->db->getConnection()->query($query);

        if (!$result) {
            die("Query failed: " . $this->db->getConnection()->error);
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Execute a SQL query with optional parameter binding.
     *
     * @param string $query The SQL query to be executed.
     * @param array  $params An array of binding parameters for the SQL query completion.
     * @return bool Returns `true` if the query is successful, `false` if an error occurs.
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
            return $stmt->affected_rows > 0;
        }

        return false;
    }
}
