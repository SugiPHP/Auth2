<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Token;

use SugiPHP\Auth2\User\UserInterface;

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
     * Check a given token belongs to the user and is valid.
     *
     * @param UserInterface $user
     * @param string $token
     *
     * @return boolean
     */
    public function checkToken(UserInterface $user, $token);

    /**
     * Invalidate a token
     *
     * @param UserInterface $user
     * @param string $token
     */
    public function invalidateToken(UserInterface $user, $token);
}
