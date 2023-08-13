<?php
/**
 * Class NetCat
 * This class provides functionality for managing categories, files, and their relationships.
 */
class NetCat
{
    private $db;

    /**
     * Constructor for the NetCat class.
     *
     * @param Database $database An instance of the database class.
     */
    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Create a new category.
     *
     * @param string $name The name of the category.
     * @param string $developer The developer of the category.
     * @param string $website The website of the category.
     * @param string $license The license of the category.
     * @param string $createdBy The creator of the category.
     * @return bool Returns true if the category was created successfully, otherwise false.
     */
    public function createCategory($name, $developer, $website, $license, $createdBy)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("INSERT INTO categories (name, developer, website, license, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $developer, $website, $license, $createdBy);
        return $stmt->execute();
    }    

    /**
     * Upload a file to a category.
     *
     * @param array $file The uploaded file data.
     * @param int $categoryId The ID of the category.
     * @param string $description The description of the file.
     * @param int $qualityMark The quality mark of the file.
     * @param int $uniquenessMark The uniqueness mark of the file.
     * @param string $interfaceLanguage The interface language of the file.
     * @param int $uploadedBy The ID of the user who uploaded the file.
     * @param string $fileNameHuman The human-readable filename.
     * @return bool Returns true if the file was uploaded successfully, otherwise false.
     */
    public function uploadFile($file, $categoryId, $description, $qualityMark, $uniquenessMark, $interfaceLanguage, $uploadedBy, $fileNameHuman)
    {
        $fileHash = hash_file('md5', $file['tmp_name']);
        $fileSize = filesize($file['tmp_name']);
        $filename = $file['name'];
        $uploadDir = '/home/users/client_1/www/snowbear/uploads/'; // Путь к директории для загрузки файлов

        // Генерируем уникальное имя файла
        $uniqueFilename = $fileHash . '_' . $filename;

        // Полный путь к файлу на сервере
        $filePath = $uploadDir . $uniqueFilename;

        // Перемещаем загруженный файл в нужную директорию
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("INSERT INTO file_versions (category_id, file_hash, filename, file_size, uploaded_by, description, quality_mark, uniqueness_mark, interface_language, filename_humanreadable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssisss", $categoryId, $fileHash, $uniqueFilename, $fileSize, $uploadedBy, $description, $qualityMark, $uniquenessMark, $interfaceLanguage, $fileNameHuman);
            
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            var_dump($file);
            return false; // Загрузка файла не удалась
        }
    }
    
    /**
     * Get categories and their associated subcategories and files.
     *
     * @param int|null $parentCategoryId The ID of the parent category (optional).
     * @param int|null $parentSubcategoryId The ID of the parent subcategory (optional).
     * @return array Returns an array containing categories, subcategories, and files.
     */
    public function getCategoriesAndFiles($parentCategoryId = null, $parentSubcategoryId = null) {
        $conn = $this->db->getConnection();
        $query = "SELECT c.id AS category_id, c.name AS category_name, 
                         s.id AS subcategory_id, s.name AS subcategory_name, 
                         f.id AS file_id, f.filename AS file_name, f.description AS file_description
                  FROM categories c
                  LEFT JOIN subcategories s ON c.id = s.category_id
                  LEFT JOIN file_versions f ON s.id = f.subcategory_id
                  WHERE (c.id = ? OR ? IS NULL) AND (s.id = ? OR ? IS NULL)
                  ORDER BY c.id, s.id, f.id";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $parentCategoryId, $parentCategoryId, $parentSubcategoryId, $parentSubcategoryId);
        $stmt->execute();
        $result = $stmt->get_result();

        $categories = array();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categoryID = $row['category_id'];
                $subcategoryID = $row['subcategory_id'];
                $fileID = $row['file_id'];

                if (!isset($categories[$categoryID])) {
                    $categories[$categoryID] = array(
                        'name' => $row['category_name'],
                        'subcategories' => array()
                    );
                }

                if ($subcategoryID !== null) {
                    if (!isset($categories[$categoryID]['subcategories'][$subcategoryID])) {
                        $categories[$categoryID]['subcategories'][$subcategoryID] = array(
                            'name' => $row['subcategory_name'],
                            'files' => array()
                        );
                    }

                    if ($fileID !== null) {
                        $categories[$categoryID]['subcategories'][$subcategoryID]['files'][] = array(
                            'name' => $row['file_name'],
                            'description' => $row['file_description']
                        );
                    }
                }
            }
        }

        return $categories;
    }

    /**
     * Get all categories.
     *
     * @return array Returns an array containing category data.
     */
    public function getCategories()
    {
        $conn = $this->db->getConnection();
        $result = $conn->query("SELECT * FROM categories");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get files associated with a specific category.
     *
     * @param int $categoryId The ID of the category.
     * @return array Returns an array containing file data.
     */
    public function getCategoryFiles($categoryId)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM file_versions WHERE category_id = ?");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>