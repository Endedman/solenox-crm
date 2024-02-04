<?php
require_once $_SERVER["DOCUMENT_ROOT"] . 'config.php';

/**
 * Implements secure token generation functionality.
 *
 * This class provides methods to generate secure, random tokens which can be
 * used for various purposes, such as CSRF protection or unique identifiers
 * within the application. It ensures the tokens are cryptographically secure by
 * utilizing PHP's random_bytes function.
 *
 * PHP version 7.4
 *
 * @category   CMS
 * @package    solenox-crm
 * @subpackage Token
 * @author     Vasiliy Kravchuk <hellendedman@internet.ru>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://j2me.xyz
 * @since      File available since RC 1.1.18
 */
class Token
{
    /**
     * Generate a random token.
     *
     * @param int $length Length of the token in bytes (default is 32).
     * @return string The generated random token in hexadecimal format.
     * @throws Exception If random_bytes() encounters an error.
     */
    public static function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }
}