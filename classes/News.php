<?php

/**
 * Handles the creation, retrieval, and management of news posts.
 *
 * This class provides functionalities to add new news posts to the database,
 * retrieve all news posts or a specific post by its ID, and manage these posts.
 * It integrates with a database to store and query news post data.
 *
 * PHP version 7.4
 *
 * @category   ContentManagement
 * @package    solenox=crm
 * @subpackage News
 * @author     Vasiliy Kravchuk <hellendedman@internet.ru>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://j2me.xyz
 * @since      File available since RC 1.1.18
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

    private function executeQuery($query, $params = []): mixed
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
     * Creates a new news post.
     *
     * @param string $title The title of the news post.
     * @param string $content The content of the news post.
     * @param string $author The author of the news post (usually you).
     * @return bool|array Returns `true` if the news post was successfully created, otherwise an error message or an empty array.
     */

    public function createNews($title, $content, $author)
    {
        // Проверка на дублирование новости
        $checkQuery = "SELECT * FROM news WHERE text = ?";
        $checkParams = [$content];
        $existingNews = $this->executeQuery($checkQuery, $checkParams);

        if (!empty($existingNews)) {
            // Возвращаем ошибку, если новость уже существует
            return false;
            ;
        }

        // Если новости с таким текстом не существует, продолжаем вставку
        $query = "INSERT INTO news (title, text, date, author_id) VALUES (?, ?, NOW(), ?)";
        $params = [$title, $content, $author];
        return $this->executeQuery($query, $params);
        // error_log(print_r($existingNews, true)); // Добавьте эту строку для записи результатов запроса в лог
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
        return $this->executeQuery("SELECT * FROM news");
    }

    /**
     * Fetches a single news post by ID.
     *
     * @param int $newsId The ID of the news post.
     * @return array|null Associative array containing the news post data or null if not found.
     */
    public function getNewsById($newsId)
    {
        $query = "SELECT n.*, u.username FROM news n INNER JOIN users u ON n.author_id = u.id WHERE n.id = ?";
        $params = [$newsId];
        $stmt = $this->db->getConnection()->prepare($query);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", ...$params); // 'i' denotes the parameter type 'integer'

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $stmt->close();
            return $result->num_rows ? $result->fetch_assoc() : null;
        } else {
            $stmt->close();
            return null;
        }
    }
}
