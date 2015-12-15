<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\TokenGenerator;

interface TokenGeneratorInterface
{
    /**
     * Generates a unique token than can be used for account activations, forgot password, CSRF, etc.
     *
     * @return string The generated token
     */
    public function generateToken();
}
