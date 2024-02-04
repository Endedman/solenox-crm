<?php
$envDir = '/var/www/html/jstore/'; // Endpoint
require_once $envDir . 'config.php';
class DatabaseException extends Exception
{
}

/**
 * Database - Manages database connections and interactions.
 *
 * This class encapsulates the logic required to connect to and interact with a database,
 * offering an abstraction layer that simplifies database queries, data fetching, and transaction
 * management. It leverages PDO for secure and flexible database operations, supporting
 * a variety of database types.
 *
 * PHP version 7.4
 *
 * @category   DatabaseAccess
 * @package    solenox-crm
 * @subpackage Database
 * @author     Vasiliy Kravchuk <hellendedman@internet.ru>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://j2me.xyz
 * @since      Class available since RC 1.1.18
 */

class Database
{
    private $host = JSTORE_DBA_HOST;
    private $port = JSTORE_DBA_PORT;

    private $username = JSTORE_DBA_USER;
    private $password = JSTORE_DBA_PASS;
    private $database = JSTORE_DBA_DBSE;
    protected $conn;

    public function __construct()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database, $this->port);

        if ($this->conn->connect_error) {
            throw new DatabaseException('Ошибка подключения к базе данных: ' . addslashes($this->conn->connect_error));
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
