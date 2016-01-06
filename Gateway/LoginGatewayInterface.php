<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Gateway;

interface LoginGatewayInterface
{
    /**
     * @param int $id
     *
     * @return mixed Returns FALSE if the user is not found or a User
     */
    public function getById($id);

    /**
     * @param string $email
     *
     * @return mixed Returns FALSE if the user email not found or a User
     */
    public function getByEmail($email);

    /**
     * @param string $username
     *
     * @return mixed Returns FALSE if the username not found or a User
     */
    public function getByUsername($username);
}
