<?php
require_once $_SERVER["DOCUMENT_ROOT"] . 'config.php';
require_once JSTORE_DIR . 'classes/Database.php';

class ListingsManager
{
    private $db;
    public $db_error = false;

    /**
     * A class that manages listings' data retrieval and manipulation in the database.
     */
    public function __construct()
    {
        $this->db = new Database();
        $this->db_error = $this->db->error;

    }

    /**
     * Fetches the complete list of listings from the database.
     *
     * @return array An array of listings fetched from the database.
     */
    public function getItemsList()
    {
        $connection = $this->db->getConnection();
        $result = $connection->query('SELECT * FROM listings');

        if ($result === false) {
            die('Error in query: ' . $this->db->getConnection()->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Retrieves a single listing based on its unique ID.
     *
     * @param int $itemId The unique identifier of the listing.
     * @return array|null The listing data or null if the listing is not found.
     */
    public function getItemById($itemId)
    {
        $connection = $this->db->getConnection();
        $stmt = $connection->prepare('SELECT * FROM listings WHERE id = ?');
        $stmt->bind_param('i', $itemId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result === false) {
            die('Error in query: ' . $connection->error);
        }

        return $result->fetch_assoc();
    }

    /**
     * Fetches all items associated with a particular user ID.
     *
     * @param int $userId The unique identifier of the user.
     * @return array An array of items linked to the specified user.
     */
    public function getItemsByUserId($userId)
    {
        $connection = $this->db->getConnection();
        $stmt = $connection->prepare('SELECT * FROM items WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result === false) {
            die('Error in query: ' . $connection->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Retrieves all screenshots related to a specific listing ID.
     *
     * @param int $itemId The unique identifier of the listing.
     * @return array An array of screenshots associated with the listing.
     */
    public function getScreenshotsByItemId($itemId)
    {
        $connection = $this->db->getConnection();
        $stmt = $connection->prepare('SELECT * FROM listings_screenshots WHERE listing_id = ?');
        $stmt->bind_param('i', $itemId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result === false) {
            die('Error in query: ' . $connection->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Adds an item to the user's wishlist.
     *
     * @param int $userId The user's ID.
     * @param int $itemId The item's ID.
     */
    public function addToWishList($userId, $itemId)
    {
        $connection = $this->db->getConnection();
        $stmt = $connection->prepare('INSERT INTO wishlist (userId, itemId) VALUES (?, ?)');
        $stmt->bind_param('ii', $userId, $itemId);
        $stmt->execute();
    }

    /**
     * Checks if an item is present in the user's wishlist.
     *
     * @param int $userId The user's ID.
     * @param int $itemId The item's ID.
     * @return bool True if the item is in the wishlist, otherwise false.
     */
    public function isInWishlist($userId, $itemId)
    {
        $connection = $this->db->getConnection();
        $stmt = $connection->prepare('SELECT * FROM wishlist WHERE userId = ? AND itemId = ?');
        $stmt->bind_param('ii', $userId, $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    /**
     * Removes an item from the user's wishlist.
     *
     * @param int $userId The user's ID.
     * @param int $itemId The item's ID.
     */
    public function removeFromWishList($userId, $itemId)
    {
        $connection = $this->db->getConnection();
        $stmt = $connection->prepare('DELETE FROM wishlist WHERE userId = ? AND itemId = ?');
        $stmt->bind_param('ii', $userId, $itemId);
        $stmt->execute();
    }
}
