<?php
require_once $_SERVER["DOCUMENT_ROOT"] . 'config.php';
require_once JSTORE_DIR . 'classes/Database.php';

class AppManager
{
    private $db;
    public $db_error = false;

    /**
     * AppManager constructor.
     */
    public function __construct()
    {
        $this->db = new Database();
        $this->db_error = $this->db->error;

    }

    /**
     * Retrieves all categories from the database.
     *
     * @return array An associative array of category records.
     */
    public function getCategories()
    {
        $connection = $this->db->getConnection();
        $result = $connection->query('SELECT * FROM categories');

        if ($result === false) {
            die('Error in query: ' . $this->db->getConnection()->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Retrieves all applications associated with a specific category ID.
     *
     * @param int $categoryId Category ID to filter applications.
     * @return array An associative array of applications for the specified category.
     */
    public function getAppsByCategory($categoryId)
    {
        $connection = $this->db->getConnection();
        $stmt = $connection->prepare('SELECT * FROM file_versions WHERE category_id = ?');
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result === false) {
            die('Error in query: ' . $this->db->getConnection()->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Retrieves a single application's details based on application ID.
     *
     * @param int $appId Application ID to fetch details.
     * @return array|null An associative array containing the application details or null if not found.
     */
    public function getApp($appId)
    {
        $connection = $this->db->getConnection();
        $stmt = $connection->prepare('SELECT * FROM file_versions WHERE id = ?');
        $stmt->bind_param('i', $appId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result === false) {
            die('Error in query: ' . $this->db->getConnection()->error);
        }

        return $result->fetch_assoc();
    }

    /**
     * Retrieves details of a category by its ID.
     *
     * @param int $categoryId ID of the category to retrieve.
     * @return array|null An associative array containing category details or null if not found.
     */
    public function getCategory($categoryId)
    {
        $connection = $this->db->getConnection();
        $stmt = $connection->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result === false) {
            die('Error in query: ' . $this->db->getConnection()->error);
        }

        return $result->fetch_assoc();
    }

    /**
     * Retrieves all subcategories from the database.
     *
     * @return array An associative array of subcategory records.
     */
    public function getSubcategories()
    {
        $connection = $this->db->getConnection();
        $result = $connection->query('SELECT * FROM subcategory');

        if ($result === false) {
            die('Error in query: ' . $this->db->getConnection()->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Retrieves all subcategories associated with a specific category ID.
     *
     * @param int $categoryId ID of the category to which subcategories are related.
     * @return array An associative array of subcategories for the specified category.
     */
    public function getSubcategory($categoryId)
    {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("SELECT * FROM subcategory WHERE id = ?");
        $stmt->bind_param("i", $categoryId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $subcategories = $result->fetch_all(MYSQLI_ASSOC);
            return $subcategories;
        } else {
            return "Failed to retrieve subcategories: " . $stmt->error;
        }
    }

    /**
     * Retrieves all screenshots associated with an application ID.
     *
     * @param int $appId Application ID to fetch related screenshots.
     * @return array An associative array of screenshot records for the specified application.
     */
    public function getScreenshotsByAppId($appId)
    {
        $connection = $this->db->getConnection();
        $stmt = $connection->prepare('SELECT * FROM screenshots WHERE app_id = ?');
        $stmt->bind_param('i', $appId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result === false) {
            die('Error in query: ' . $this->db->getConnection()->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Retrieves all files (file versions) associated with a given subcategory ID.
     *
     * @param int $subcategoryId ID of the subcategory to fetch files for.
     * @return array An associative array of file records for the specified subcategory.
     */
    public function getFilesBySubcategories($subcategoryId)
    {
        $connection = $this->db->getConnection();

        // Получить все файлы (версии файлов), связанные с данной подкатегорией
        $stmt = $connection->prepare('SELECT * FROM file_versions WHERE subcategory_id = ?');

        $stmt->bind_param('i', $subcategoryId);

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            die('Error in query: ' . $connection->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

