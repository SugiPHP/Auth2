<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Auth2\Gateway;

use SugiPHP\Auth2\User\UserInterface;

interface TokenGatewayInterface
{
    /**
     * Finds a token previously stored in the DB
     *
     * @param string $token
     *
     * @return mixed Returns FALSE if the token not found or User
     */
    public function findToken($token);

    /**
     * Stores a token in the DB
     *
     * @param string $token
     * @param UserInterface $user
     */
    public function storeToken($token, UserInterface $user);
}
