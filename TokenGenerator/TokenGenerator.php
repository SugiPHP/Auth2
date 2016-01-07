<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\TokenGenerator;

class TokenGenerator implements TokenGeneratorInterface
{
    private $tokenLength = 128;

    /**
     * @see TokenGeneratorInterface::generateToken()
     */
    public function generateToken()
    {
        $len = $this->getTokenLenght();

        // make it random
        if (function_exists("random_bytes")) {
            $code = random_bytes($len / 8);
        } else {
            $code = mt_rand() . uniqid(mt_rand(), true) . microtime(true) . mt_rand();
        }

        // SHA-512 produces 128 chars
        // base64_encode for the sha-512 produces 172 chars, 171 without "=".
        $code = trim(base64_encode(hash("sha512", $code)), "=");
        // extract only part of it
        $code = substr($code, mt_rand(0, strlen($code) - $len - 1), $len);

        return $code;
    }

    public function setTokenLenght($tokenLength)
    {
        $this->tokenLength = $tokenLength;
    }

    public function getTokenLenght()
    {
        return $this->tokenLength;
    }
}
