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
     * @return string The generated token
     */
    public function generateToken(UserInterface $user);

    /**
     * Check a given token belongs to the user and is valid.
     *
     * @return boolean
     */
    public function checkToken(UserInterface $user, $token);
}
