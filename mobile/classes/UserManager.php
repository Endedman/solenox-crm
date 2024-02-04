<?php
require_once $_SERVER["DOCUMENT_ROOT"] . 'config.php';
require_once JSTORE_DIR . 'classes/Database.php';

class UserManager
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
        $fieldValue = 0;
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
     * Performs user login based on provided username or email and password.
     *
     * @param string $usernameOrEmail The username or email of the user trying to log in.
     * @param string $password The password of the user.
     * @return array|string Returns an array with login success information or an error message.
     */
    public function login($usernameOrEmail, $password)
    {
        if (empty($usernameOrEmail) || empty($password)) {
            return ["success" => false, "message" => "Please provide both a username or email and password."];
        }

        $query = "SELECT id, username, email, password FROM users WHERE username = ? OR email = ?";
        $params = [$usernameOrEmail, $usernameOrEmail];
        $userData = $this->fetchSingle($query, $params);

        if ($userData !== false && password_verify($password, $userData["password"])) {
            return [
                "success" => true,
                "message" => "Logged in. <script>window.location.href='/" . JSTORE_WEB_MOBILE_SHORT_DIR . "';</script>",
                "user" => [
                    "id" => $userData["id"],
                    "username" => $userData["username"],
                    "email" => $userData["email"]
                ]
            ];
        } else {
            return ["success" => false, "message" => "Invalid credentials"];
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
        if ($_SESSION['captcha_code'] != $_POST['captcha']) {
            return "Invalid captcha!";
        } else {
            if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
                return "Please provide all required fields.";
            }

            if ($password !== $confirmPassword) {
                return "Passwords do not match.";
            }

            if ($this->usernameOrEmailExists($username, $email)) {
                return "Username or email already exists.";
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
            $params = [$username, $email, $hashedPassword, $role];

            $success = $this->executeQuery($query, $params);

            if ($success) {
                return "Registration successful!";
            } else {
                return "Registration failed.";
            }
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
     * Get user profile by user ID.
     *
     * @param int $userId The ID of the user.
     * @return array|false Returns an associative array containing user profile data if found, or false if not found.
     */
    public function getUserProfileById($userId)
    {
        $query = "SELECT id, username, email, rank, avatar, balance FROM users WHERE id = ?";
        $params = [$userId];
        return $this->fetchSingle($query, $params);
    }
    public function getTopUsersByRank($limit = 10)
    {
        if (empty($limit)) {
            return "Please provide a limit.";
        }

        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("SELECT username, rank FROM users ORDER BY rank DESC LIMIT ?");
        $stmt->bind_param("i", $limit);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $users = $result->fetch_all(MYSQLI_ASSOC);
            return $users;
        } else {
            return "Failed to retrieve top users: " . $stmt->error;
        }
    }

}