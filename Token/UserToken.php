<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Token;

use SugiPHP\Auth2\User\UserInterface;

class UserToken implements TokenInterface
{
    /**
     * @see TokenInterface::generateToken()
     */
    public function generateToken(UserInterface $user)
    {
        return $this->createToken($user);
    }

    /**
     * @see TokenInterface::checkToken()
     */
    public function checkToken(UserInterface $user, $token)
    {
        return $token === $this->createToken($user);
    }

    /**
     * @see TokenInterface::invalidateToken()
     */
    public function invalidateToken(UserInterface $user, $token)
    {
        // There is no need to invalidate the token, because it is based on user state and user's password
        // User state is changed on activations, and passwords are changed on each pass change
        return ;
    }

    private function createToken(UserInterface $user)
    {
        $code = $user->getId() . $user->getPassword() . $user->getEmail() . $user->getState();

        // SHA-512 produces 128 chars
        // base64_encode for the SHA-512 produces 172 chars, 171 without "=".
        $code = trim(base64_encode(hash("sha512", $code)), "=");

        return $code;
    }
}
