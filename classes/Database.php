<?php
/**
 * Class Database
 * Represents a database connection.
 */

class Database
{
    private $host = "localhost";
    private $username = "snowbear";
    private $password = "end56905";
    private $database = "snowbear_data";
    protected $conn;

    /**
     * Database constructor.
     * Establishes a database connection using the provided credentials.
     */
    
    public function __construct()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
    /**
     * Get the database connection instance.
     *
     * @return mysqli The mysqli instance representing the database connection.
     */
    
    public function getConnection()
    {
        return $this->conn;
    }
}
?>
