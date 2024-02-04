<?php

/**
 * NetCat - A class for managing categories and files relationships.
 *
 * This class offers a comprehensive solution for handling categories and files,
 * ensuring seamless relationships between them. It serves as a part of a larger
 * content management system, facilitating category creation, file categorization,
 * and retrieving files under specific categories.
 * 
 * PHP version 7.4 or higher
 *
 * @category   ContentManagement
 * @package    solenox-crm
 * @subpackage NetCat
 * @author     Vasiliy Kravchuk <hellendedman@internet.ru>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://j2me.xyz
 * @since      File available since RC 1.1.18
 */
class NetCat
{
    private $db;

    /**
     * Constructor for the NetCat class.
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

    /**
     * Executes a SQL query and fetches all the resulting rows as an associative array.
     *
     * This method prepares and executes a SQL query using the provided parameters. It is designed
     * to return multiple rows of results, making it suitable for SELECT queries that retrieve data
     * from the database. It uses `MYSQLI_ASSOC` to fetch results as an associative array where each
     * key in the array corresponds to a column name in the result set.
     *
     * @param string $query The SQL query string to execute.
     * @param array $params Optional parameters to bind to the query for prepared statements.
     * @return array An associative array containing all result rows, or an empty array if no results.
     */
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
    public function fetchSingle($query, $params = [])
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
        // Проверка на дублирование категории
        $checkQuery = "SELECT * FROM categories WHERE name = ?";
        $checkParams = [$name];
        $existingCategory = $this->executeQuery($checkQuery, $checkParams);

        if (!empty($existingCategory)) {
            // Возвращаем ошибку, если категория уже существует
            return false;
        }

        // Если категории с таким именем не существует, продолжаем вставку
        $query = "INSERT INTO categories (name, developer, website, license, created_by) VALUES (?, ?, ?, ?, ?)";
        $params = [$name, $developer, $website, $license, $createdBy];
        return $this->executeQuery($query, $params);
    }

    /**
     * Sends a message with an inline keyboard to a specified Telegram channel.
     *
     * This method sends a Markdown-formatted text message to the Telegram channel associated with
     * the provided channel ID. It includes an inline keyboard with a 'Download' button that links to 
     * the provided URL. It utilizes Telegram's sendMessage API endpoint for bot integration.
     *
     * @param string $text The message text to be sent. It supports Markdown formatting.
     * @param string $url The URL to be attached to the 'Download' button in the inline keyboard.
     */
    public function sendMessageToTelegramChannel($text, $url)
    {
        $urlTelegram = "https://api.telegram.org/bot" . TELEGRAM_BOT_SECRET . "/sendMessage";
        $postFields = array(
            'chat_id' => TELEGRAM_CHANNEL_ID,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Download',
                            'url' => $url
                        ]
                    ]
                ]
            ]),
        );
        $ch = curl_init();
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                "Content-Type:multipart/form-data"
            )
        );
        curl_setopt($ch, CURLOPT_URL, $urlTelegram);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        $res = curl_exec($ch);
        if (curl_errno($ch)) {
            print curl_error($ch);
        }
        curl_close($ch);
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
    public function uploadFile($file, $categoryId, $description, $qualityMark, $uniquenessMark, $interfaceLanguage, $uploadedBy, $fileNameHuman): array
    {
        $virus_protect = 0;
        $malicious_count = -1;
        $fileHash = hash_file('md5', $file['tmp_name']);
        $fileSize = filesize($file['tmp_name']);
        $filename = $file['name'];
        $uniqueFilename = JSTORE_UPLOAD_BRANDFILEPREFIX . $fileHash . '_' . $filename; // anchor in CONFIG.PHP
        // API settings from VirusTotal
        $apiCheckUrl = 'https://www.virustotal.com/api/v3/files/' . $fileHash;
        // Prepare headers 
        $headers = array(
            'x-apikey: ' . VIRUSTOTAL_API_KEY
        );
        // Initialize cURL request to VirusTotal API
        $ch = curl_init($apiCheckUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // Execute the cURL request and get the response
        $response = curl_exec($ch);
        $response = json_decode($response, true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        // Check if the file exists in VirusTotal
        if ($httpCode == 404) {
            // File not found in VirusTotal. Upload the file to VirusTotal and check scan result
            $fileData = curl_file_create($file['tmp_name'], $file['type'], $file['name']);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.virustotal.com/api/v3/files');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array('file' => $fileData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result, true);
            if (isset($result['error'])) {
                error_log('[Error] Failed to upload file to VirusTotal: ' . $result['error']);
                return ['status' => 'error', 'message' => 'Failed: Error uploading file to VirusTotal', 'malicious_count' => $malicious_count];
            }
            $analysisUrl = $result['data']['links']['self'];
            $iterate = 0;
            do {
                sleep(10); // wait for 10 seconds before checking the analysis status
                $ch = curl_init($analysisUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $result = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($result, true);
                error_log('[DebugAfterReScan] ' . $result);

                $maliciousCount = $result['data']['attributes']['last_analysis_stats']['malicious'];
                $maliciousCountFiles = $result['data']['attributes']['stats']['malicious'];

                $pendingCount = $result['data']['attributes']['status'];
                $iterate++;

            } while ($pendingCount == 'queued' and $iterate <= 6);
            if ($maliciousCount > 0 or $maliciousCountFiles > 0) {
                return ['status' => 'error', 'message' => 'Failed: File is malicious', 'malicious_count' => $maliciousCount];
            }
        } else {
            $maliciousCount = $response['data']['attributes']['last_analysis_stats']['malicious'];
            if ($maliciousCount > 0) {
                return ['status' => 'error', 'message' => 'Failed: File is malicious', 'malicious_count' => $maliciousCount];
            }
        }
        $maliciousCount = $response['data']['attributes']['last_analysis_stats']['malicious'];
        // If no detection on VirusTotal, upload the file
        if ($maliciousCount == 0 and move_uploaded_file($file['tmp_name'], JSTORE_TEMP_UPLOAD_DIR . $uniqueFilename)) {
            // check if file is an image
            $mimeType = mime_content_type(JSTORE_TEMP_UPLOAD_DIR . $uniqueFilename);
            $icons = array(
                'pdf' => '/static/img/png/PROGM025.ICO',
                'doc' => '/static/img/png/MORIC010.ICO',
                'docx' => '/static/img/png/MORIC010.ICO',
                'tiff' => '/static/img/png/PROGM024.ICO',
                'xls' => '/static/img/png/PROGM020.ICO',
                'xlsx' => '/static/img/png/PROGM020.ICO',
                'bmp' => '/static/img/png/PROGM011.ICO',
                'ppt' => '/static/img/png/PROGM015.ICO',
                'pptx' => '/static/img/png/PROGM015.ICO',
                'com' => '/static/img/png/PROGM008.ICO',
                'jar' => '/static/img/png/PROGM001.ICO',
                'exe' => '/static/img/png/PROGM008.ICO',
                'txt' => '/static/img/png/NOTEP001.ICO',
                'wav' => '/static/img/png/MSREM001.ICO',
                'wma' => '/static/img/png/MSREM001.ICO',
                'mp3' => '/static/img/png/MSREM001.ICO',
                'oga' => '/static/img/png/MSREM001.ICO',
            );
            $extension = pathinfo(JSTORE_TEMP_UPLOAD_DIR . $uniqueFilename, PATHINFO_EXTENSION);

            if (preg_match("/^image\/.*/i", $mimeType)) {
                // файл является изображением, создаем thumbnail
                list($width, $height) = getimagesize(JSTORE_TEMP_UPLOAD_DIR . $uniqueFilename);
                $thumb = imagecreatetruecolor(32, 32);
                $source = imagecreatefromstring(file_get_contents(JSTORE_TEMP_UPLOAD_DIR . $uniqueFilename));
                if ($source !== false) {
                    // Изображение успешно создано, создаем миниатюру
                    imagecopyresized($thumb, $source, 0, 0, 0, 0, 32, 32, $width, $height);
                    $thumbFilePath = JSTORE_UPLOAD_DIR . JSTORE_UPLOAD_THUMB_PREFIX . $uniqueFilename;
                    imagejpeg($thumb, $thumbFilePath);
                    imagedestroy($thumb);
                    // Присваиваем путь к thumbnail для сохранения в БД
                    $iconUrl = JSTORE_UPLOAD_THUMBDIR_PREFIX . $uniqueFilename;
                } else {
                    return ['status' => 'error', 'message' => 'Fatal error: ThumbImage is corrupted.', 'malicious_count' => 0];

                }

            } else if (preg_match("/^video\/.*/i", $mimeType)) {
                // Path for the frame to be saved
                $framePath = JSTORE_UPLOAD_DIR . JSTORE_UPLOAD_THUMB_PREFIX . $uniqueFilename . '.jpg';
                // FFmpeg command to grab a frame at the 10 second mark of the video
                $command = "ffmpeg -i " . escapeshellarg(JSTORE_TEMP_UPLOAD_DIR . $uniqueFilename) . " -ss 00:00:10 -vframes 1 " . escapeshellarg($framePath);
                // Added error capturing
                $output = shell_exec($command . " 2>&1");
                if (preg_match("/error/i", $output)) {
                    error_log("Error: " . $output); // or log with error_log() in production
                    return ['status' => 'error', 'message' => 'Fatal error: Video is corrupted.', 'malicious_count' => 0];
                }
                $iconUrl = '/static/img/png/package-1.png'; // Задаем URL значку по умолчанию или оставляем его пустым
            } else if (array_key_exists($extension, $icons)) {
                $iconUrl = $icons[$extension];
            } else {
                // Используйте иконку по умолчанию, если расширение файла отсутствует в массиве иконок
                $iconUrl = '/static/img/png/package-1.png';
            }
            $virus_protect = 1;
            $query = "INSERT INTO file_versions (category_id, file_hash, filename, file_size, uploaded_by, description, quality_mark, uniqueness_mark, interface_language, filename_humanreadable, virus_protect, icon_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$categoryId, $fileHash, $uniqueFilename, $fileSize, $uploadedBy, $description, $qualityMark, $uniquenessMark, $interfaceLanguage, $fileNameHuman, $virus_protect, $iconUrl];
            if ($this->executeQuery($query, $params)) {
                if (rename(JSTORE_TEMP_UPLOAD_DIR . $uniqueFilename, JSTORE_UPLOAD_DIR . $uniqueFilename)) {
                } else {
                    return ['status' => 'error', 'message' => 'Error: File moving failed. Don`t worry - we are using SSHFS, check if file successfully uploaded manually. Thanks!', 'malicious_count' => 0];
                }
            } else {
                return ['status' => 'error', 'message' => 'Fatal error: DB is experiencing issues.', 'malicious_count' => 0];
            }
            $fileLink = JSTORE_WEB_UPLOAD_DIR . $uniqueFilename;
            $message = "_Успешная загрузка файла: _" . $fileNameHuman . "\n" .
                "Размер файла: " . $fileSize . " bytes\n" .
                "Категория: " . $categoryId . "\n" .
                "Описание: " . $description . "\n" .
                "Оценка качества: " . $qualityMark . "\n" .
                "Оценка уникальности: " . $uniquenessMark . "\n";
            $this->sendMessageToTelegramChannel($message, $fileLink);
            return ['status' => 'success', 'message' => 'File uploaded successfully', 'malicious_count' => 0];
        }
        return ['status' => 'error', 'message' => 'Fatal error: File upload failed', 'malicious_count' => 0];
    }

    /**
     * Get categories and their associated subcategories and files.
     *
     * @param int|null $parentCategoryId The ID of the parent category (optional).
     * @param int|null $parentSubcategoryId The ID of the parent subcategory (optional).
     * @return array Returns an array containing categories, subcategories, and files.
     */
    public function getCategoriesAndFiles($parentCategoryId = null, $parentSubcategoryId = null)
    {
        $query = "SELECT c.id AS category_id, c.name AS category_name, 
                         s.id AS subcategory_id, s.name AS subcategory_name, 
                         f.id AS file_id, f.filename AS file_name, f.description AS file_description
                  FROM categories c
                  LEFT JOIN subcategory s ON c.id = s.category_id
                  LEFT JOIN file_versions f ON s.id = f.subcategory_id
                  WHERE (c.id = ? OR ? IS NULL) AND (s.id = ? OR ? IS NULL)
                  ORDER BY c.id, s.id, f.id";
        $params = [$parentCategoryId, $parentCategoryId, $parentSubcategoryId, $parentSubcategoryId];
        return $this->fetchAll($query, $params);
    }
    /**
     * Edit a file.
     *
     * @param  int    $id   - The ID of the file to be edited
     * @param  string $name - The new name of the file
     * @return bool   Returns true on success or false on failure.
     */
    public function editFile($id, $name)
    {
        $query = "UPDATE file_versions SET name=? WHERE id=?";
        return $this->executeQuery($query, [$name, $id]);
    }

    /**
     * Delete a file from the database based on its ID.
     *
     * This method deletes a record from the 'file_versions' table where the 'id' matches the specified file ID.
     * It utilizes a prepared statement to execute the SQL query.
     *
     * @param int $id The ID of the file to be deleted.
     * @return bool Returns true on success or false on failure.
     */
    public function deleteFile($id)
    {
        $query = "DELETE FROM file_versions WHERE id=?";
        return $this->executeQuery($query, [$id]);
    }

    /**
     * Retrieves a list of categories from the database.
     *
     * This method selects all records from the 'categories' table. Replace 'categories' with the actual name
     * of your category table if different.
     *
     * @return array An array of category records, or an empty array if no records are found.
     */
    public function getCategories()
    {
        // Подразумевается, что вы имеете подключение к базе данных и установленную среду.
        $query = "SELECT * FROM categories"; // Примечание: Замените 'categories' на реальное имя вашей таблицы категорий
        return $this->executeQuery($query);
    }

    /**
     * Retrieves a list of supported languages from the database.
     *
     * This method selects all records from the 'languages' table. Replace 'languages' with the actual name
     * of your language table if different.
     *
     * @return array An array of language records, or an empty array if no records are found.
     */
    public function getLanguages()
    {
        // Подразумевается, что вы имеете подключение к базе данных и установленную среду.
        $query = "SELECT * FROM languages"; // Примечание: Замените 'categories' на реальное имя вашей таблицы категорий
        return $this->executeQuery($query);
    }

    /**
     * Retrieves various statistics from the database.
     *
     * This method fetches the total number of users, the total number of file versions, and the 
     * sum of all file sizes in the 'file_versions' table. It returns these statistics in an associative array.
     *
     * @return array An associative array containing user count, file count, and total file size occupied.
     */
    public function getStats()
    {
        // Подразумевается, что вы имеете подключение к базе данных и установленную среду.

        // Фетчим количество пользователей 
        $query = "SELECT COUNT(*) FROM users";
        $userCount = $this->executeQuery($query);

        // Фетчим количество файлов
        $query = "SELECT COUNT(*) FROM file_versions";
        $fileCount = $this->executeQuery($query);

        // Фетчим суммарное занятое место
        $query = "SELECT SUM(file_size) FROM file_versions";
        $totalSize = $this->executeQuery($query);

        // возвращаем статистику в виде ассоциативного массива 
        return [
            'userCount' => $userCount[0]["COUNT(*)"],
            'fileCount' => $fileCount[0]["COUNT(*)"],
            'totalSize' => $totalSize[0]["SUM(file_size)"],
        ];
    }

    /**
     * Adds a new screenshot record to the database.
     *
     * This method inserts a new record into the 'screenshots' table with the file URL and the associated
     * application ID. Returns true on successful insertion, false on failure.
     *
     * @param string $fileName The name of the screenshot file to be recorded.
     * @param int $appId The ID of the application that the screenshot belongs to.
     * @return bool Returns true on success or false on failure.
     */
    public function addScreenshotRecord($fileName, $appId)
    {
        $query = "INSERT INTO screenshots (file_url, app_id) VALUES (?, ?)";
        $params = [$fileName, $appId];

        try {
            $this->executeQuery($query, $params);
            return true;
        } catch (Exception $e) {
            error_log("Failed to insert screenshot record: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handles the process of uploading a screenshot to a predefined directory and creating a database record.
     *
     * This method attempts to move an uploaded file from a temporary directory to the target uploads directory.
     * Upon successful upload, it adds a record to the database. If the upload fails, it returns an error message.
     *
     * @param array $file An array containing file upload information (from the $_FILES superglobal).
     * @param string $newName The desired new name for the file.
     * @param int $uploadedBy The ID of the user associated with the upload.
     * @return array An associative array with the status and a message indicating success or error.
     */
    public function uploadScreenshot($file, $newName, $uploadedBy)
    {
        $targetFile = JSTORE_UPLOAD_DIR . $newName;
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            // Если файл успешно загружен в целевую директорию, можно добавить запись в БД.
            $this->addScreenshotRecord($newName, $uploadedBy);
            return [
                'status' => 'success',
                'message' => 'Screenshot uploaded successfully.',
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Failed to upload screenshot.',
            ];
        }
    }
    /**
     * Get all files.
     *
     * @return array Returns an array containing file data.
     */
    public function getFiles()
    {
        return $this->fetchAll("SELECT * FROM file_versions");
    }

    /**
     * Returns the total number of files in the given category.
     *
     * @param int $categoryID The ID of the category.
     * @return int|bool Returns the count of files in the category, or false on failure.
     */
    public function countFilesInCategory($categoryID)
    {
        $query = "SELECT COUNT(*) as total FROM file_versions WHERE category_id = ?";
        $params = [$categoryID];

        // $this->fetchSingle() should be a method in your class that accepts
        // a query string and parameters, executes the query, fetches the first row
        // of the result set, and returns this row. 
        $result = $this->fetchSingle($query, $params);

        return $result !== false ? $result['total'] : false;
    }

    /**
     * Get files associated with a specific category.
     *
     * @param int $categoryId The ID of the category.
     * @return array Returns an array containing file data.
     */
    public function getCategoryFiles($categoryId, $page = 1, $perPage = 5)
    {
        $offset = ($page - 1) * $perPage;
        $query = "SELECT * FROM file_versions WHERE category_id = ? LIMIT $perPage OFFSET $offset";
        //$query = "SELECT * FROM file_versions WHERE category_id = ?";
        $params = [$categoryId];
        return $this->fetchAll($query, $params);
    }
    /**
     * Retrieves the application details from the database based on the provided application ID.
     *
     * This method queries the 'file_versions' table to fetch all columns for a single row
     * where the 'id' column matches the specified application ID. If the application is not found,
     * null is returned.
     *
     * @param int $appId The unique identifier for the application.
     * @return array|null The application data as an associative array, or null if not found.
     */
    public function getAppById($appId)
    {
        $query = "SELECT * FROM file_versions WHERE id = ?";
        $app = $this->fetchSingle($query, [$appId]);

        return $app; // вернёт null, если запись не найдена
    }

    /**
     * Converts a filesize number into a human-readable format with specified precision.
     *
     * Calculates the filesize in bytes and converts it into the largest unit the bytes will fit into
     * (e.g., KB, MB, GB, etc.), formatted to a specified number of decimal places.
     *
     * @param int $size The filesize in bytes.
     * @param int $precision The number of decimal places to be used in the formatted result.
     * @return string The formatted filesize in human-readable form with unit suffix.
     */
    public function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    /**
     * Fetches all screenshots associated with a given application ID.
     *
     * This method retrieves all screenshot records from the 'screenshots' table that are
     * associated with the specified application ID.
     *
     * @param int $appId The unique identifier of the application whose screenshots are to be retrieved.
     * @return array The screenshots data as an array of associative arrays, empty if none are found.
     */
    public function getScreenshotsByAppId($appId)
    {
        $query = "SELECT * FROM screenshots WHERE app_id = ?";
        $params = [$appId];
        $screenshots = $this->executeQuery($query, $params);

        return $screenshots;
    }

    /**
     * Creates a thumbnail image from an original image file, fitting it within specified dimensions.
     *
     * The method takes an original image and resizes it to either the width or the height specified,
     * maintaining the original aspect ratio. It then saves the resized image to a specified directory for thumbnails.
     *
     * @param string $originalImage The original image file path relative to the specified upload directory.
     * @param int $width The desired width of the thumbnail image.
     * @param int $height The desired height of the thumbnail image.
     * @param string $thumbDirectory The directory path where the thumbnail image will be saved.
     * @return bool Returns true on success or false on failure.
     */
    public function createThumbnail($originalImage, $width, $height, $thumbDirectory)
    {
        // получить размеры изображения
        list($source_width, $source_height) = getimagesize(JSTORE_UPLOAD_DIR . $originalImage);

        // определить соотношение сторон
        $ratio = $source_width / $source_height;

        if ($width / $height > $ratio) {
            $new_width = $height * $ratio;
            $new_height = $height;
        } else {
            $new_width = $width;
            $new_height = $width / $ratio;
        }

        // Создать новое изображение
        $thumb = imagecreatetruecolor($new_width, $new_height);

        // Загрузить оригинальное изображение
        // Необходимо применить разные функции, в зависимости от типа файла
        $source = null;
        switch (pathinfo(JSTORE_UPLOAD_DIR . $originalImage, PATHINFO_EXTENSION)) {
            case 'jpeg':
            case 'jpg':
                $source = imagecreatefromjpeg(JSTORE_UPLOAD_DIR . $originalImage);
                break;
            case 'png':
                $source = imagecreatefrompng(JSTORE_UPLOAD_DIR . $originalImage);
                break;
            // продолжить для других типов файлов...
        }

        if (!$source) {
            return false;
        }

        // Изменение размера
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $new_width, $new_height, $source_width, $source_height);

        // Сохранить изображение
        $thumbFilePath = $thumbDirectory . JSTORE_UPLOAD_THUMB_PREFIX . pathinfo($originalImage)['filename'] . pathinfo($originalImage, PATHINFO_EXTENSION);
        imagejpeg($thumb, $thumbFilePath, 100);

        // Освободить память
        imagedestroy($thumb);
        imagedestroy($source);

        return true;
    }
}