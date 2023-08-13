<?php

/**
 * Class News
 * Represents a news management system.
 */

class News
{
    private $db;

    /**
     * News constructor.
     *
     * @param Database $db The database connection instance.
     */
    
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Execute a SQL query with optional parameter binding.
     *
     * @param string $query The SQL query to be executed.
     * @param array  $params An array of binding parameters for the SQL query completion.
     * @return array|null Returns an array of rows if the query is successful, or `null` if an error occurs.
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
            $data = [];
            
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            return $data;
        } else {
            return [];
        }
    }

     /**
     * Creates a new news post.
     *
     * @param string $title The title of the news post.
     * @param string $content The content of the news post.
     * @param string $author The author of the news post (usually you).
     * @return bool|array Returns `true` if the news post was successfully created, otherwise an error message or an empty array.
     */
    
    public function createNews($title, $content, $author)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("INSERT INTO news (title, text, date, author_id) VALUES (?, ?, NOW(), ?)");
        $stmt->bind_param("ssi", $title, $content, $author);
        return $stmt->execute();
    }

    /**
     * Retrieve all news posts with associated authors.
     *
     * @return array An array of news posts, each containing details about the post and its author.
     */
    
    public function getAllNews()
    {
        $query = "SELECT n.*, u.username FROM news n INNER JOIN users u ON n.author_id = u.id ORDER BY n.id DESC";
        return $this->executeQuery($query);
    }

    /**
     * Retrieve all news posts for API usage.
     *
     * @return array An array of news posts, each containing details about the post.
     */
    
    public function getAllNewsApi()
    {
        $query = "SELECT * FROM news";
        return $this->executeQuery($query);
    }

    // Другие методы класса...
}
?>
