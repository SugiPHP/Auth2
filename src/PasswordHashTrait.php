<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2;

trait PasswordHashTrait
{
    /**
     * Compares a secret against a hash.
     *
     * @param string $secret Secret
     * @param string $hash Secret hash made with cryptSecret() method
     *
     * @return boolean
     */
    private function checkSecret($secret, $hash)
    {
        return password_verify($secret, $hash);
    }

    /**
     * Generates a password hash
     *
     * @param string $secret
     *
     * @return string
     */
    private function cryptSecret($secret)
    {
        return password_hash($secret, PASSWORD_BCRYPT);
    }
}
