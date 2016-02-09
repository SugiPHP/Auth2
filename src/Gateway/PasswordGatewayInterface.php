<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Gateway;

interface PasswordGatewayInterface extends RegistrationGatewayInterface
{
    /**
     * Changes user password hash
     *
     * @param integer $userId
     * @param string $passwordHash
     *
     * @return boolean Success
     */
    public function updatePassword($userId, $passwordHash);
}
