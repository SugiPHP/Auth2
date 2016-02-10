<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Token;

use SugiPHP\Auth2\User\UserInterface;
use SugiPHP\Auth2\Gateway\TokenGatewayInterface;

class RandomToken implements TokenInterface
{
    /**
     * @var integer Maximum token length. Needed to store the token in the database.
     */
    private $tokenLength = 128;

    /**
     * @var Instance of TokenGatewayInterface
     */
    private $gateway;

    public function __construct(TokenGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @see TokenInterface::generateToken()
     */
    public function generateToken(UserInterface $user)
    {
        $len = $this->getTokenLength();

        // make it random
        if (function_exists("random_bytes")) {
            $token = random_bytes($len / 8);
        } else {
            $token = mt_rand() . uniqid(mt_rand(), true) . microtime(true) . mt_rand();
        }

        // SHA-512 produces 128 chars
        // base64_encode for the sha-512 produces 172 chars, 171 without "=".
        $token = trim(base64_encode(hash("sha512", $token)), "=");
        // extract only part of it
        $token = substr($token, mt_rand(0, strlen($token) - $len - 1), $len);

        $this->gateway->storeToken($token, $user->getId());

        return $token;
    }

    /**
     * @see TokenInterface::fetchToken()
     */
    public function fetchToken($token)
    {
        $userId = $this->gateway->findToken($token);
        if (!$userId) {
            return false;
        }

        return $userId;
    }

    /**
     * @see TokenInterface::invalidateToken()
     */
    public function invalidateToken($token)
    {
        $this->gateway->deleteToken($token);
    }

    public function setTokenLength($tokenLength)
    {
        $this->tokenLength = $tokenLength;
    }

    public function getTokenLength()
    {
        return $this->tokenLength;
    }
}
