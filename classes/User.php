<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "PHPMailer/PHPMailer.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "PHPMailer/SMTP.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
            return "Please provide both a username or email and password.";
        }

        $query = "SELECT id, username, email, password FROM users WHERE username = ? OR email = ?";
        $params = [$usernameOrEmail, $usernameOrEmail];
        $userData = $this->fetchSingle($query, $params);

        if ($userData !== false && password_verify($password, $userData["password"])) {
            return [
                "success" => true,
                "user" => [
                    "id" => $userData["id"],
                    "username" => $userData["username"],
                    "email" => $userData["email"]
                ]
            ];
        } else {
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

        if ($this->executeQuery($query, $params)) {
            return "Registration successful!";
        } else {
            return "Registration failed.";
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
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'turkey666pig@gmail.com'; // Replace with your Gmail email
            $mail->Password = 'end569nnn05'; // Replace with your Gmail password or App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('turkey666pig@gmail.com', 'JStore Verifier'); // Replace with your details
            $mail->addAddress($recipientEmail);

            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $mail->Body = "Please verify your email using this token: $verificationToken";
            // Send the email and capture the result
            $sendResult = $mail->send();

            return $sendResult;
        } catch (Exception $e) {
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


    // public function hasManualRankChange($userId)
    // {
    //     $query = "SELECT id FROM rank_changes WHERE user_id = ? AND changed_by IS NOT NULL";
    //     $params = [$userId];
    //     $result = $this->executeQuery($query, $params);

    //     return count($result) > 0;
    // }



    // public function updateUserRank($userIdToUpdate, $newRank, $changedByUserId = null)
    // {
    //     $conn = $this->db->getConnection();

    //     if ($newRank < -1024) {
    //         return false; // Invalid rank
    //     }

    //     if ($changedByUserId !== null) {
    //         if (!$this->hasRankChange($userIdToUpdate, $changedByUserId)) {
    //             $changeType = ($newRank > $this->getUserRank($userIdToUpdate)) ? 'increase' : 'decrease';
    //             $this->insertRankChange($userIdToUpdate, $changedByUserId, $changeType);
    //         }
    //     } elseif ($changedByUserId === null && !$this->hasManualRankChange($userIdToUpdate)) {
    //         $messageCount = $this->countUserMessages($userIdToUpdate);
    //         $newsCount = $this->countUserNews($userIdToUpdate);
    //         $fileCount = $this->countUserFiles($userIdToUpdate);

    //         $calculatedRank = ($fileCount * 2) + ($newsCount * 4) + ($messageCount * 1);

    //         return $this->updateUserRankValue($userIdToUpdate, $calculatedRank);
    //     }

    //     return true;
    // }

    // private function hasRankChange($userIdToUpdate, $changedByUserId)
    // {
    //     $query = "SELECT id FROM rank_changes WHERE user_id = ? AND changed_by = ?";
    //     $params = [$userIdToUpdate, $changedByUserId];
    //     $result = $this->fetchSingle($query, $params);
        
    //     return $result !== false;
    // }

    // private function insertRankChange($userIdToUpdate, $changedByUserId, $changeType)
    // {
    //     $query = "INSERT INTO rank_changes (user_id, changed_by, change_type, change_date) VALUES (?, ?, ?, NOW())";
    //     $params = [$userIdToUpdate, $changedByUserId, $changeType];
        
    //     return $this->executeQuery($query, $params);
    // }

    // private function updateUserRankValue($userIdToUpdate, $newRank)
    // {
    //     $query = "UPDATE users SET rank = ? WHERE id = ?";
    //     $params = [$newRank, $userIdToUpdate];
        
    //     return $this->executeQuery($query, $params);
    // }

     /**
     * Update the user's rank via API.
     *
     * @param int $userId The ID of the user.
     * @param int $newRank The new rank to set for the user.
     * @return void
     */
    public function updateUserRankViaApi($userId, $newRank)
    {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("UPDATE users SET rank = ? WHERE id = ?");
        $stmt->bind_param("ii", $newRank, $userId);
        $stmt->execute();
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
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Get username by user ID.
     *
     * @param int $userId The ID of the user.
     * @return string|null Returns the username of the user if found, or null if not found.
     */
    public function getUsernameById($userId)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($username);
        $stmt->fetch();

        return $username;
    }

    /**
     * Get user profile by user ID.
     *
     * @param int $userId The ID of the user.
     * @return array|false Returns an associative array containing user profile data if found, or false if not found.
     */
    public function getUserProfileById($userId)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT id, username, email, rank FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return false;
        }
    }

    /**
     * Get user profile by username.
     *
     * @param string $username The username of the user.
     * @return array|false Returns an associative array containing user profile data if found, or false if not found.
     */
    public function getUserProfileByUsername($username)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT id, username, email, rank FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return false;
        }
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
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE users SET token = ? WHERE id = ?");
        $stmt->bind_param("si", $token, $userId);
        return $stmt->execute();
    }

    /**
     * Check if a token is valid for a user.
     *
     * @param string $token The token to check.
     * @return bool Returns true if the token is valid for a user, or false otherwise.
     */
    public function isValidToken($token)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT id FROM users WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    /**
     * Get all users from the database.
     *
     * @return array Returns an array containing user data for all users.
     */
    public function getAllUsers()
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT id, username, rank, role FROM users");

        if (!$stmt) {
            // Handle error if the query couldn't be prepared
            return [];
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }
}

/**
 * Map numeric rank to text representation.
 *
 * @param int $rank The numeric rank value.
 * @return string Returns the corresponding text representation of the rank.
 */
function mapRankToText($rank)
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
?>