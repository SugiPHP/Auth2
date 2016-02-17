<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Token;

use SugiPHP\Auth2\User\UserInterface;
use SugiPHP\Auth2\Gateway\LoginGatewayInterface;

class UserToken implements TokenInterface
{
    /**
     * @var Instance of LoginGatewayInterface
     */
    private $gateway;

    public function __construct(LoginGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @see TokenInterface::generateToken()
     */
    public function generateToken($id)
    {
        return $this->createUserBasedToken($id);
    }

    /**
     * @see TokenInterface::fetchToken()
     */
    public function fetchToken($token)
    {
        $decoded = base64_decode($token);
        if (!$id = strstr($decoded, ".", true)) {
            return false;
        }

        return ($token === $this->createUserBasedToken($id)) ? $id : false;
    }

    /**
     * @see TokenInterface::invalidateToken()
     */
    public function invalidateToken($token)
    {
        // There is no need to invalidate the token, because it is based on user state and user's password
        // User state is changed on activations, and passwords are changed on each pass change
        return ;
    }

    private function createUserBasedToken($id)
    {
        // get the user data from the DB
        if (!$user = $this->gateway->getById($id)) {
            return null;
        }

        $code = $user->getId() . $user->getPassword() . $user->getEmail() . $user->getState();

        // SHA-512 produces 128 chars
        // base64_encode for the SHA-512 produces 172 chars, 171 without "=".
        $code = trim(base64_encode($user->getId().".".hash("sha512", $code)), "=");

        return $code;
    }
}
