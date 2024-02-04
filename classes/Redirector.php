<?php

/**
 * Redirector class for managing redirect links within an application.
 *
 * Provides functionalities to add, fetch, and manage redirect links stored in a database.
 * This class is part of the application's URL redirection feature, allowing dynamic management
 * of redirect destinations.
 * 
 * PHP version 7.4
 * 
 * @category   URLManagement
 * @package    solenox-crm
 * @subpackage Redirector
 * @author     Vasiliy Kravchuk <hellendedman@internet.ru>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://j2me.xyz
 * @since      File available since RC 1.1.18
 */
class Redirector
{
    private $db;

    /**
     * Constructor for the Redirector class.
     *
     * @param Database $db An instance of the database class.
     */
    public function __construct($db)
    {
        $this->db = $db;
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

    private function fetchAll($query, $params = [])
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare($query);
        if ($params) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Fetches a single row from the database based on the provided query and parameters.
     *
     * @param string $query The SQL query to execute.
     * @param array $params An array of parameters to bind to the query.
     * @return array|bool Associative array containing the fetched row or false if not found.
     */
    private function fetchSingle($query, $params = [])
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

            if ($result->num_rows === 1) {
                return $result->fetch_assoc();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function getRedirectLink($id)
    {
        $query = "SELECT link FROM redir_links WHERE id = ?";
        $params = [$id];

        $result = $this->fetchSingle($query, $params);

        return $result !== false ? $result['link'] : false;
    }

    public function addRedirectLink($link, $name)
    {
        $query = "INSERT INTO redir_links (link, name, date) VALUES (?, ?, NOW())";
        $params = [$link, $name];

        $result = $this->executeQuery($query, $params);

        // Returns true if link was added successfully, otherwise returns false
        return $result !== false ? $result > 0 : false;
    }

    public function getAllRedirects()
    {
        $query = "SELECT * FROM redir_links";

        // We do not need to specify parameters, so we proceed querying without them
        $result = $this->fetchAll($query);

        return $result;
    }
}
