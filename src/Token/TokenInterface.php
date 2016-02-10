<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Token;

use SugiPHP\Auth2\User\UserInterface;

/**
 * The Generated Token MUST be unique. No duplications are allowed even for different users.
 */
interface TokenInterface
{
    /**
     * Generates a unique token than can be used for account activations, forgot password, etc.
     *
     * @param UserInterface $user
     *
     * @return string The generated token
     */
    public function generateToken(UserInterface $user);

    /**
     * Fetch data hidden behind the token.
     *
     * @param string $token
     *
     * @return mixed FALSE or NULL if the token is not found or invalidated. Integer to represent UserID
     */
    public function fetchToken($token);

    /**
     * Invalidate a token
     *
     * @param string $token
     */
    public function invalidateToken($token);
}
