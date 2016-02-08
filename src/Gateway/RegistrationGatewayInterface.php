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
     * @return mixed User or FALSE on error.
     */
    public function add($email, $username, $state, $passwordHash);

    /**
     * Changes user state.
     *
     * @param integer $id
     * @param integer $state
     */
    public function updateState($id, $state);
}
