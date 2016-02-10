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
     * @return mixed Returns FALSE if the token not found or user's ID
     */
    public function findToken($token);

    /**
     * Stores a token in the DB
     *
     * @param string $token
     * @param integer $userId
     */
    public function storeToken($token, $userId);

    /**
     * Deletes a token from the database
     *
     * @param string $token
     */
    public function deleteToken($token);

    /**
     * Deletes all tokens for a particular user. This can be triggered on password changes,
     * on user blocking, on user requests, etc.
     *
     * @param integer $userId
     */
    public function deleteUserTokens($userId);
}
