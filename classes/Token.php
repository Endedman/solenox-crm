<?php
/**
 * Class Token
 * Provides methods for generating secure tokens.
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
?>