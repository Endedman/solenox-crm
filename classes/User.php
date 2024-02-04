<?php
require_once JSTORE_DIR . "PHPMailer/PHPMailer.php";
require_once JSTORE_DIR . "PHPMailer/SMTP.php";
require_once JSTORE_DIR . "PHPMailer/Exception.php";
require_once JSTORE_DIR . 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * User management class for handling user-specific actions.
 *
 * This class includes functionalities for user operations such as
 * registration, deletion, and authentication. It utilizes PHPMailer
 * for email-related functionalities.
 *
 * PHP version 7.4
 *
 * @category   CMS
 * @package    solenox-crm
 * @subpackage UserManagement
 * @author     Vasiliy Kravchuk <hellendedman@internet.ru>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://j2me.xyz
 * @since      File available since RC 1.1.18
 */
class User
{
    private $db;

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

    /**
     * Fetches a single field value from the database based on the provided query and parameters.
     *
     * @param string $query The SQL query to execute.
     * @param array $params An array of parameters to bind to the query.
     * @return mixed|bool The fetched field value or false if not found.
     */
    private function fetchSingleField($query, $params = [])
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare($query);
        $fieldValue = false; // Initialize $fieldValue to ensure it is always set

        if ($stmt) {
            if (!empty($params)) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $stmt->bind_result($fieldValue);
            $stmt->fetch();
            $stmt->close();

            return $fieldValue;
        } else {
            return false;
        }
    }

    /**
     * Retrieves the user's TOTP secret from the database.
     *
     * @param int $userId The unique identifier for the user.
     * @return string|null The user's TOTP secret if found, or null if not found.
     */
    public function getSecretByUserId($userId)
    {
        $query = "SELECT user_totp_secret FROM users WHERE id = ?";
        $params = [$userId];
        $result = $this->fetchSingle($query, $params);

        // Проверяем, была ли возвращена строка с данными
        if ($result !== false) {
            return $result["user_totp_secret"];
        } else {
            return null; // или false, в зависимости от того, как вы хотите обрабатывать эту ситуацию
        }
    }

    /**
     * Performs user login based on provided username or email and password.
     *
     * @param string $usernameOrEmail The username or email of the user trying to log in.
     * @param string $password The password of the user.
     * @return array|string Returns an array with login success information, an error message, or a ban reason.
     */
    public function login($usernameOrEmail, $password)
    {
        if (empty($usernameOrEmail) || empty($password)) {
            return "Please provide both a username or email and password.";
        }

        // Добавляем в запрос выборку полей `blocked` и `block_reason`
        $query = "SELECT id, username, email, password, blocked, block_reason FROM users WHERE username = ? OR email = ?";
        $params = [$usernameOrEmail, $usernameOrEmail];
        $userData = $this->fetchSingle($query, $params);

        // Проверяем, вернулся ли результат и правильный ли пароль
        if ($userData !== false && password_verify($password, $userData["password"])) {
            // Проверяем, не заблокирован ли пользователь
            if ($userData['blocked'] == 1) {
                // Если пользователь заблокирован, возвращаем причину бана
                return [
                    "success" => false,
                    "blocked" => true,
                    "message" => $userData["block_reason"] ? "You have been banned. Reason: " . $userData["block_reason"] . " Mistake? t.me/neonarod" : "You have been banned, no reason provided. If no reason is found in DBA, write at support: t.me/neonarod"
                ];
            }
            // Если пользователь не заблокирован, возвращаем успешное сообщение о входе
            return [
                "success" => true,
                "user" => [
                    "id" => $userData["id"],
                    "username" => $userData["username"],
                    "email" => $userData["email"]
                ]
            ];
        } else {
            // Если данные не найдены или пароль неверный
            return "Username or email not found or incorrect password.";
        }
    }

    /**
     * Registers a new user with the provided information.
     *
     * @param string $username The desired username.
     * @param string $email The email address of the user.
     * @param string $password The password for the user.
     * @param string $confirmPassword The confirmation of the password.
     * @param int $role The role level of the user.
     * @return string Returns a message indicating the result of the registration.
     */
    public function register($username, $email, $password, $confirmPassword, $role)
    {
        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            return ["success" => false, "message" => "Please provide all required fields."];
        }

        if ($password !== $confirmPassword) {
            return ["success" => false, "message" => "Passwords do not match."];
        }

        if ($this->usernameOrEmailExists($username, $email)) {
            return ["success" => false, "message" => "Username or email already exists."];
        }

        $authenticator = new PHPGangsta_GoogleAuthenticator();
        $secret = $authenticator->createSecret();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (username, email, password, role, user_totp_secret) VALUES (?, ?, ?, ?, ?)";
        $params = [$username, $email, $hashedPassword, $role, $secret];
        $success = $this->executeQuery($query, $params);

        if ($success) {
            // Успешная регистрация
            return [
                "success" => true,
                "message" => "Registration successful!",
                "secret" => $secret,
                "email" => $email
            ];
        } else {
            // Неудачная попытка регистрации
            return ["success" => false, "message" => "Registration failed."];
        }
    }

    /**
     * Regenerates and updates the TOTP secret for a user by their unique identifier.
     *
     * This method first generates a new TOTP secret using PHPGangsta_GoogleAuthenticator and then updates
     * the user record in the database with the new secret.
     *
     * @param int $userId The unique identifier of the user for whom the TOTP secret is to be regenerated.
     * @return bool True if the secret was successfully updated, False otherwise.
     */
    public function regenerateUserTOTPbyId($userId)
    {
        // // Сначала проверим, существует ли пользователь с таким ID
        // $existsQuery = "SELECT id FROM users WHERE id = ?";
        // $existsResult = $this->fetchSingle($existsQuery, [$userId]);

        // if (!$existsResult) {
        // // Пользователь не найден
        //     return false;
        // }

        // Генерация нового TOTP-секрета
        $authenticator = new PHPGangsta_GoogleAuthenticator();
        $newSecret = $authenticator->createSecret();

        // Запрос на обновление TOTP-секрета в базе данных
        $updateQuery = "UPDATE users SET user_totp_secret = ? WHERE id = ?";
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare($updateQuery);

        if (!$stmt) {
            return false; // Неудача при подготовке запроса
        }

        $stmt->bind_param("si", $newSecret, $userId);

        if ($stmt->execute()) {
            return true; // Успех: секрет обновлён
        } else {
            return false; // Неудача при выполнении запроса
        }
    }

    /**
     * Checks if the user with the given user ID has the required role.
     *
     * @param int $userId The ID of the user.
     * @param int $requiredRole The required role level.
     * @return bool Returns true if the user has the required role, false otherwise.
     */
    public function userHasPermission($userId, $requiredRole)
    {
        $query = "SELECT role FROM users WHERE id = ?";
        $params = [$userId];
        $userRole = $this->fetchSingleField($query, $params);

        return $userRole !== false && $userRole >= $requiredRole;
    }

    /**
     * Checks if a username or email already exists in the database.
     *
     * @param string $username The username to check.
     * @param string $email The email to check.
     * @return bool Returns true if the username or email exists, false otherwise.
     */
    private function usernameOrEmailExists($username, $email)
    {
        $query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $params = [$username, $email];
        $result = $this->fetchSingle($query, $params);
        return $result !== false;
    }

    /**
     * Changes the password for a user.
     *
     * @param int $userId The ID of the user.
     * @param string $oldPassword The old password.
     * @param string $newPassword The new password.
     * @param string $confirmPassword The confirmation of the new password.
     * @return string Returns a message indicating the result of the password change.
     */
    public function changePassword($userId, $oldPassword, $newPassword, $confirmPassword)
    {
        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            return "Please provide all required fields.";
        }

        if ($newPassword !== $confirmPassword) {
            return "New passwords do not match.";
        }

        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            return "User not found.";
        }

        $userData = $result->fetch_assoc();

        if (!password_verify($oldPassword, $userData["password"])) {
            return "Incorrect old password.";
        }

        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedNewPassword, $userId);

        if ($stmt->execute()) {
            return "Password changed successfully!";
        } else {
            return "Password change failed.";
        }
    }

    /**
     * Updates the avatar for a user.
     *
     * @param int $userId The ID of the user.
     * @param string $avatarPath The new avatar path.
     * @return string Returns a message indicating the result of the avatar update.
     */
    public function updateAvatar($userId, $avatarPath)
    {
        if (empty($avatarPath)) {
            return "Please upload a new avatar image.";
        }

        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            return "User not found.";
        }

        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->bind_param("si", $avatarPath, $userId);

        if ($stmt->execute()) {
            return "Avatar updated successfully!";
        } else {
            return "Avatar update failed.";
        }
    }

    /**
     * Changes the email address for a user and sends a verification email.
     *
     * @param int $userId The ID of the user.
     * @param string $newEmail The new email address.
     * @return string Returns a message indicating the result of the email change request.
     */
    public function changeEmail($userId, $newEmail)
    {
        if (empty($newEmail)) {
            return "Please provide a new email.";
        }

        $conn = $this->db->getConnection();
        $verificationToken = $this->generateVerificationToken();

        $stmt = $conn->prepare("UPDATE users SET new_email = ?, email_verification_token = ? WHERE id = ?");
        $stmt->bind_param("ssi", $newEmail, $verificationToken, $userId);

        if ($stmt->execute()) {
            // Send verification email to the user with the verification token
            if ($this->sendVerificationEmail($newEmail, $verificationToken)) {
                return "Email change request sent. Please check your email for verification.";
            } else {
                return "Email change request sent, but verification email could not be sent.";
            }
        } else {
            return "Email change request failed: " . $stmt->error;
        }
    }

    /**
     * Generates a random verification token.
     *
     * @return string Returns a random verification token.
     */
    private function generateVerificationToken()
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Sends a verification email to the recipient with the verification token.
     *
     * @param string $recipientEmail The email address of the recipient.
     * @param string $verificationToken The verification token to include in the email.
     * @return bool Returns true if the email was sent successfully, false otherwise.
     */
    private function sendVerificationEmail($recipientEmail, $verificationToken)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();

            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = ADMIN_MAIL; // Replace with your Gmail email
            $mail->Password = ADMIN_APPPASSWORD; // Replace with your Gmail password or App Password
            $mail->SMTPSecure = MAIL_PROTO;
            $mail->Port = MAIL_PORT;
            $mail->CharSet = MAIL_ENCODING;

            $mail->setFrom(ADMIN_MAIL, 'JStore Verifier'); // Replace with your details
            $mail->addAddress($recipientEmail);

            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            // Загрузите HTML-шаблон из файла или переменной
            $htmlTemplate = JSTORE_DIR . '/admin/letter.html';
            $htmlContent = file_get_contents($htmlTemplate);

            // Замените плейсхолдер токеном
            $htmlContent = str_replace('{{verificationToken}}', $verificationToken, $htmlContent);

            // Установите HTML-содержимое письма
            $mail->Body = $htmlContent;            // Send the email and capture the result
            $sendResult = $mail->send();
            return $sendResult;
        } catch (Exception $e) {
            error_log('Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
            return false;
        }
    }
    /**
     * Verifies an email using a token and updates the user's email address if verified.
     *
     * @param string $newEmail The user's new email address pending verification.
     * @param string $token The verification token.
     * @return bool True on success, false on failure or if verification fails.
     */
    public function verifyEmail($newEmail, $token)
    {
        // Начинаем транзакцию
        $this->db->getConnection()->begin_transaction();

        try {
            // Проверяем токен и получаем идентификатор пользователя для `new_email` и `token`
            $query = "SELECT `id` FROM `users` WHERE `new_email` = ? AND `email_verification_token` = ?";
            $user_data = $this->executeQuery($query, [$newEmail, $token]);

            // Если пользователь найден и токен верный
            if (!empty($user_data)) {
                // Обновляем email пользователя на основе найденного идентификатора
                $user_id = $user_data[0]['id'];
                $update_query = "UPDATE `users` SET `email` = ?, `new_email` = NULL, 
                                `email_verification_token` = NULL WHERE `id` = ?";
                $update_result = $this->executeQuery($update_query, [$newEmail, $user_id]);

                // Если обновление прошло успешно
                if ($update_result) {
                    // Завершаем транзакцию
                    $this->db->getConnection()->commit();
                    return true;
                } else {
                    // Откат транзакции в случае ошибки при обновлении
                    $this->db->getConnection()->rollback();
                    return false;
                }
            } else {
                // Откат транзакции, если токен не соответствует или пользователь не найден
                $this->db->getConnection()->rollback();
                return false;
            }
        } catch (Exception $e) {
            // Откат транзакции в случае любой другой ошибки
            $this->db->getConnection()->rollback();
            error_log('Ошибка при верификации email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves the rank of a user.
     *
     * @param int $userId The ID of the user.
     * @return int Returns the user's rank or 0 if the user is not found.
     */
    public function getUserRank($userId)
    {
        $query = "SELECT rank FROM users WHERE id = ?";
        $params = [$userId];
        $result = $this->fetchSingleField($query, $params);

        return ($result !== false) ? $result : 0; // Default rank if user not found
    }

    /**
     * Counts the number of news articles authored by a user.
     *
     * @param int $userId The ID of the user.
     * @return int Returns the count of news articles authored by the user or 0 if none found.
     */
    public function countUserNews($userId)
    {
        $query = "SELECT COUNT(*) FROM news WHERE author_id = ?";
        $params = [$userId];
        $count = $this->fetchSingleField($query, $params);

        return ($count !== false) ? $count : 0;
    }

    /**
     * Counts the number of chat messages sent by a user.
     *
     * @param int $userId The ID of the user.
     * @return int Returns the count of chat messages sent by the user or 0 if none found.
     */
    public function countUserMessages($userId)
    {
        $query = "SELECT COUNT(*) FROM chat_messages WHERE user_id = ?";
        $params = [$userId];
        $count = $this->fetchSingleField($query, $params);

        return ($count !== false) ? $count : 0;
    }

    /**
     * Counts the number of files uploaded by a user.
     *
     * @param int $userId The ID of the user.
     * @return int Returns the count of files uploaded by the user or 0 if none found.
     */
    public function countUserFiles($userId)
    {
        $query = "SELECT COUNT(*) FROM file_versions WHERE uploaded_by = ?";
        $params = [$userId];
        $count = $this->fetchSingleField($query, $params);

        return ($count !== false) ? $count : 0;
    }

    /**
     * Gets the count of manual rank increases for a user.
     *
     * @param int $userId The ID of the user.
     * @return int Returns the count of manual rank increases or 0 if none found.
     */
    public function getUserManualRankChangeIncrease($userId)
    {
        $query = "SELECT COUNT(*) FROM rank_changes WHERE user_id = ? AND change_type = ? AND changed_by IS NOT NULL";
        $params = [$userId, "increase"];
        $count = $this->fetchSingleField($query, $params);

        return ($count !== false) ? $count : 0;
    }

    /**
     * Gets the count of manual rank decreases for a user.
     *
     * @param int $userId The ID of the user.
     * @return int Returns the count of manual rank decreases or 0 if none found.
     */
    public function getUserManualRankChangeDecrease($userId)
    {
        $query = "SELECT COUNT(*) FROM rank_changes WHERE user_id = ? AND change_type = ? AND changed_by IS NOT NULL";
        $params = [$userId, "decrease"];
        $count = $this->fetchSingleField($query, $params);

        return ($count !== false) ? $count : 0;
    }

    /**
     * Determines if a user has had any manual rank changes.
     *
     * This method checks if there are any records of manual rank changes for a specific user.
     * It queries the 'rank_changes' table for any entries where 'user_id' matches the given user ID
     * and where changes were made manually (changed_by is not NULL).
     *
     * @param int $userId The ID of the user to check for manual rank changes.
     * @return bool Returns true if manual rank changes are present, otherwise false.
     */
    public function hasManualRankChange($userId)
    {
        $query = "SELECT id FROM rank_changes WHERE user_id = ? AND changed_by IS NOT NULL";
        $params = [$userId];
        $result = $this->executeQuery($query, $params);

        return count($result) > 0;
    }

    /**
     * Updates the rank of a user.
     *
     * This method updates a user's rank based on different conditions. If the rank change is manual 
     * (by another user's input), related changes are recorded in the database. If the change is automatic 
     * (without input from another user), the rank is calculated based on the number of messages, news, 
     * and files the user has contributed.
     *
     * @param int $userIdToUpdate The ID of the user whose rank is to be updated.
     * @param int $newRank The new rank for the user.
     * @param int|null $changedByUserId The ID of the user who made the change, or null if the changes are automatic.
     * @return bool Returns true if the rank update is successful, otherwise false.
     */
    public function updateUserRank($userIdToUpdate, $newRank, $changedByUserId = null)
    {
        $conn = $this->db->getConnection();

        if ($newRank < -1024) {
            return false; // Invalid rank
        }

        if ($changedByUserId !== null) {
            if (!$this->hasRankChange($userIdToUpdate, $changedByUserId)) {
                $changeType = ($newRank > $this->getUserRank($userIdToUpdate)) ? 'increase' : 'decrease';
                $this->insertRankChange($userIdToUpdate, $changedByUserId, $changeType);
            }
        } elseif ($changedByUserId === null && !$this->hasManualRankChange($userIdToUpdate)) {
            $messageCount = $this->countUserMessages($userIdToUpdate);
            $newsCount = $this->countUserNews($userIdToUpdate);
            $fileCount = $this->countUserFiles($userIdToUpdate);

            $calculatedRank = ($fileCount * 2) + ($newsCount * 4) + ($messageCount * 1);

            return $this->updateUserRankValue($userIdToUpdate, $calculatedRank);
        }

        return true;
    }

    /**
     * Checks if there has been a previous rank change by a specific user.
     *
     * This method verifies if there has been a previous rank change for a user that was initiated 
     * by another specific user.
     *
     * @param int $userIdToUpdate The ID of the user to check for rank changes.
     * @param int $changedByUserId The ID of the user who may have initiated the change.
     * @return bool Returns true if a previous rank change is recorded, otherwise false.
     */
    private function hasRankChange($userIdToUpdate, $changedByUserId)
    {
        $query = "SELECT id FROM rank_changes WHERE user_id = ? AND changed_by = ?";
        $params = [$userIdToUpdate, $changedByUserId];
        $result = $this->fetchSingle($query, $params);

        return $result !== false;
    }

    /**
     * Inserts a rank change record.
     *
     * This method inserts a new record into the 'rank_changes' table, recording a rank change event 
     * for a user including who made the change and whether it was an increase or decrease.
     *
     * @param int $userIdToUpdate The ID of the user whose rank is changing.
     * @param int $changedByUserId The ID of the user who initiated the change.
     * @param string $changeType The type of change, 'increase' or 'decrease'.
     * @return mixed The result of the query execution.
     */
    private function insertRankChange($userIdToUpdate, $changedByUserId, $changeType)
    {
        $query = "INSERT INTO rank_changes (user_id, changed_by, change_type, change_date) VALUES (?, ?, ?, NOW())";
        $params = [$userIdToUpdate, $changedByUserId, $changeType];

        return $this->executeQuery($query, $params);
    }

    /**
     * Updates the rank value of a user in the 'users' table.
     *
     * This method updates the actual rank value for a user in the 'users' table.
     *
     * @param int $userIdToUpdate The ID of the user whose rank value needs updating.
     * @param int $newRank The new rank to be set.
     * @return mixed The result of the update query execution.
     */
    private function updateUserRankValue($userIdToUpdate, $newRank)
    {
        $query = "UPDATE users SET rank = ? WHERE id = ?";
        $params = [$newRank, $userIdToUpdate];

        return $this->executeQuery($query, $params);
    }

    /**
     * Update the user's rank via API.
     *
     * @param int $userId The ID of the user.
     * @param int $newRank The new rank to set for the user.
     * @return void
     */
    public function updateUserRankViaApi($userId, $newRank): bool
    {
        $query = "UPDATE users SET rank = ? WHERE id = ?";
        $params = [$newRank, $userId];
        return $this->executeQuery($query, $params);
    }

    /**
     * Get user information by username and password.
     *
     * @param string $username The username of the user.
     * @param string $password The password of the user.
     * @return array|null Returns the user's data if found and authenticated, or null if not found or password is incorrect.
     */
    public function getUserByUsernameAndPassword($username, $password)
    {
        $query = "SELECT * FROM users WHERE username = ?";
        $params = [$username];
        $user = $this->fetchSingle($query, $params);
        return ($user !== false && password_verify($password, $user['password'])) ? $user : null;
    }

    /**
     * Get username by user ID.
     *
     * @param int $userId The ID of the user.
     * @return string|null Returns the username of the user if found, or null if not found.
     */
    public function getUsernameById($userId)
    {
        $query = "SELECT username FROM users WHERE id = ?";
        $params = [$userId];
        return $this->fetchSingleField($query, $params);
    }

    /**
     * Retrieves the password for a user based on their user ID.
     *
     * This method selects the password field from the 'users' table for a given user ID.
     * It uses a prepared statement to prevent SQL injection attacks. 
     * The method assumes that the database contains a table named 'users' with at least two columns: 'id' and 'password'. 
     * The 'password' is expected to be stored in a secure manner, ideally hashed. 
     * It is critical that this method is used responsibly and securely, following best practices for password management.
     *
     * @param int $userId The unique identifier of the user whose password is being retrieved.
     * @return mixed Returns the password associated with the provided user ID or false if no user is found.
     */
    public function getPassById($userId)
    {
        $query = "SELECT password FROM users WHERE id = ?";
        $params = [$userId];
        return $this->fetchSingleField($query, $params);
    }

    /**
     * Get user profile by user ID.
     *
     * @param int $userId The ID of the user.
     * @return array|false Returns an associative array containing user profile data if found, or false if not found.
     */
    public function getUserProfileById($userId)
    {
        $query = "SELECT id, username, email, rank, role, avatar FROM users WHERE id = ?";
        $params = [$userId];
        return $this->fetchSingle($query, $params);
    }

    /**
     * Get user profile by username.
     *
     * @param string $username The username of the user.
     * @return array|false Returns an associative array containing user profile data if found, or false if not found.
     */
    public function getUserProfileByUsername($username)
    {
        $query = "SELECT id, username, email, rank FROM users WHERE username = ?";
        $params = [$username];
        return $this->fetchSingle($query, $params);
    }

    /**
     * Update the token for a user.
     *
     * @param int $userId The ID of the user.
     * @param string $token The token to update.
     * @return bool Returns true if the token was updated successfully, or false otherwise.
     */
    public function updateToken($userId, $token)
    {
        $query = "UPDATE users SET token = ? WHERE id = ?";
        $params = [$token, $userId];
        return $this->executeQuery($query, $params);
    }

    /**
     * Check if a token is valid for a user.
     *
     * @param string $token The token to check.
     * @return bool Returns true if the token is valid for a user, or false otherwise.
     */
    public function isValidToken($token)
    {
        $query = "SELECT id FROM users WHERE token = ?";
        $params = [$token];
        return $this->fetchSingle($query, $params) !== false;
    }

    /**
     * Get all users from the database.
     *
     * @return array Returns an array containing user data for all users.
     */
    public function getAllUsers()
    {
        return $this->executeQuery("SELECT id, username, email, rank, user_totp_secret, role, avatar FROM users");
    }

    /**
     * Update user information.
     *
     * @param int $userId The ID of the user.
     * @param array $newData An associative array containing the new user data.
     * @return bool Returns true if the update was successful, or false otherwise.
     */
    public function updateUser($userId, $newData)
    {
        $query = "UPDATE users SET username = ?, email = ?, rank = ?, role = ? WHERE id = ?";
        $params = [$newData['username'], $newData['email'], $newData['rank'], $newData['role'], $userId];
        return $this->executeQuery($query, $params);
    }

    /**
     * Block a user.
     *
     * @param int $userId The ID of the user.
     * @param string $reason The reason for blocking the user.
     * @return bool Returns true if the block was successful, or false otherwise.
     */
    public function blockUser($userId, $reason)
    {
        $query = "UPDATE users SET blocked = 1, block_reason = ? WHERE id = ?";
        $params = [$reason, $userId];
        return $this->executeQuery($query, $params);
    }

    /**
     * Delete a user.
     *
     * @param int $userId The ID of the user.
     * @return bool Returns true if the deletion was successful, or false otherwise.
     */
    public function deleteUser($userId)
    {
        // First, delete related records. The order is important to maintain referential integrity.
        $relatedTables = [
            'chat_messages',
            'user_activity',
            'rank_changes',
            'news'
        ];

        foreach ($relatedTables as $table) {
            $query = "DELETE FROM {$table} WHERE user_id = ?";
            $params = [$userId];
            $success = $this->executeQuery($query, $params);

            if (!$success) {
                // If an error occurs when trying to delete related records, cease execution.
                return false;
            }
        }

        // Now, delete the user from the users table.
        $query = "DELETE FROM users WHERE id = ?";
        $params = [$userId];
        return $this->executeQuery($query, $params);
    }

    /**
     * Map numeric rank to text representation.
     *
     * @param int $rank The numeric rank value.
     * @return string Returns the corresponding text representation of the rank.
     */
    public function mapRankToText($rank)
    {
        if ($rank >= 1 && $rank < 10) {
            return "Novice";
        } elseif ($rank >= 10 && $rank < 100) {
            return "Active user";
        } elseif ($rank >= 100) {
            return "Administrator";
        } else {
            return "Unknown (zero-ranked or negative)";
        }
    }
    /**
     * Map user role to its textual description.
     *
     * @param int $role The role of the user.
     * @return string Returns the text description of the role.
     */
    public function mapRoleToText($role)
    {
        switch ($role) {
            case -1:
                return "<b><i style='color: gray'>Guest </b></i>";
            case 0:
                return "<b><i style='color: lightgray;'>User </b></i>";
            case 1:
                return "<b><i style='color: royalblue;'>Curator </b></i>";
            case 2:
                return "<b><i style='color: lightseagreen;'>Moderator </b></i>";
            case 3:
                return "<b><i style='color: crimson;'>Owner </b></i>";
            default:
                return "<b><i>Unknown role</b></i>";
        }
    }
}
