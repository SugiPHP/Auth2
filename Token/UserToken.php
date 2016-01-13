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

    private function createToken(UserInterface $user)
    {
        $code = $user->getId() . $user->getPassword() . $user->getEmail() . $user->getState();

        // SHA-512 produces 128 chars
        // base64_encode for the SHA-512 produces 172 chars, 171 without "=".
        $code = trim(base64_encode(hash("sha512", $code)), "=");

        return $code;
    }
}
