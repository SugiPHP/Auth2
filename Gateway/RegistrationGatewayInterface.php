<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Gateway;

interface RegistrationGatewayInterface extends LoginGatewayInterface
{
    /**
     * Adds a new record for a user.
     *
     * @param string $email
     * @param string $username
     * @param integer $state
     * @param mixed $passwordHash
     *
     * @return FALSE on error. Any other result will be returned in "data" key
     */
    public function add($email, $username, $state, $passwordHash);
}
